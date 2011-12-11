<?php

/**
 * Aggregate that implements "Display Parent" functionality of controls. 
 * It is in a way that host control's methods should just pass return values of an aggregate methods.
 * For usage examples, see Pmt_Composite_Display and Pmt_Group. 
 * It is important for host control to properly initialize the aggregate and set Conversation when it changes:
 *
 * <code>   
 *   protected function doOnInitialize(array $options) {
 *      parent::doOnInitialize($options);
 *      $this->idp = new Pmt_Impl_DisplayParent(array(
 *          'allowedDisplayChildrenClass' => $this->allowedDisplayChildrenClass,
 *          'conversation' => $this->conversation? $this->conversation : null, 
 *          'responderId' => $this->responderId,
 *          'container' => $this,
 *      ));
 *   }
 *  
 *  function setConversation(Pm_I_Conversation $conversation) {
 *      $res = parent::setConversation($conversation);
 *      if ($this->idp) {
 *          $this->idp->setConversation($conversation);
 *          $this->idp->setResponderId($this->responderId);
 *      }
 *      return $res;
 *  }       
 * </code>    
 *
 * <b>Important</b>: $container property should refer to the host control since all display children's $displayParent properties should reference the host control, 
 * not the implementation. 
 */
class Pmt_Impl_DisplayParent extends Pmt_Autoparams implements Pmt_I_Control_DisplayParent, Pm_I_Refcontrol  {
    
    protected $displayChildren = array();
    
    protected $hasToReorderDisplayChildren = false;
    
    protected $allowedDisplayChildrenClass = false;
    
    /**
     * Container that holds this object as an aggregate
     * @var Pmt_I_Control_DisplayParent
     */
    protected $container = false;
    
    /**
     * @var Pm_Conversation
     */
    protected $conversation = false;    
    
    protected $responderId = false;
    
    protected $lockMessages = false;
    
    protected $nextControlClassIgnore = false;
    
//  --------------- functions that are specified by the interface Pmt_I_Control_DisplayParent ---------------   
    
    function getOrderedDisplayChildren() {
        if ($this->hasToReorderDisplayChildren) $this->reorderDisplayChildren();
        return $this->displayChildren;
    }
    
    function findDisplayChild(Pmt_I_Control $child) {
        $res = false;
        foreach ($this->getOrderedDisplayChildren() as $index => $c) {
            if ($child === $c) { $res = $index; break; }
        }
        return $res;
    }
        
    
    function ignoreNextControlClass() {
    	$this->nextControlClassIgnore = true;
    }
    
    function addDisplayChild(Pmt_I_Control $child) {
        if ($this->findDisplayChild($child) === false) {
            $this->refAdd($child);
            if ($this->nextControlClassIgnore) {
            	$this->nextControlClassIgnore = false;
            } else {
	            if (strlen($this->allowedDisplayChildrenClass) && (! $child instanceof $this->allowedDisplayChildrenClass)) {
	                $c = get_class($child);
	                throw new Exception ("Only instances of '{$this->allowedDisplayChildrenClass}' class are allowed as display children of {$this}, {$c} given");
	            }
            }
            $order = $child->getDisplayOrder();
            if ($order === false) $order = $this->getLastDisplayChildOrder() + 1;
            $dc = $this->getOrderedDisplayChildren();
            $orders = array();
            foreach ($dc as $control) $orders[] = $control->getDisplayOrder();
            if (count($orders)) {
                if ($order < $orders[0]) {
                    $pos = 0;
                    $this->displayChildren = array_merge(array($child), $this->displayChildren);
                }
                elseif ($order >= $orders[count($orders) - 1]) {
                    $pos = count($orders);
                    $this->displayChildren[] = $child;
                }
                else {
                    $leftOrd = $orders[0];
                    for ($i = 1; $i < count($orders); $i++) {
                        $rightOrd = $orders[$i];
                        if (($order >= $leftOrd) && ($order < $rightOrd)) {
                            $pos = $i;
                            break;
                        }
                    }
                    array_splice($this->displayChildren, $pos, 0, array($child));
                }
            } else {
                $this->displayChildren = array($child);
            }
            $child->setDisplayParent($this->container);
        }
    }
    
    function removeDisplayChild(Pmt_I_Control $child) {
        $idx = $this->findDisplayChild($child);
        if ($idx !== false) {
            unset($this->displayChildren[$idx]);
            $child->setDisplayParent(null);
        }
    }
    
    protected function dbgList() {
        Pm_Conversation::log($this->container.'', implode(", ", $this->displayChildren));
    }
    
    function updateDisplayChildPosition(Pmt_I_Control $child, $displayOrder) {
        $c = $this->container->getController();
        if ($c) $c->logMessage($this->container.'', implode(", ", $this->displayChildren));
        $oi = $this->findDisplayChild($child);
        if ($oi !== false) unset($this->displayChildren[$oi]);
        array_splice($this->displayChildren, $displayOrder, 0, array($child));
        if ($displayOrder !== $oi) $this->doFrontendUpdateChildPosition($child, $oi, $displayOrder);
    }
    
    function getChildDisplayOrder(Pmt_I_Control $child) {
        
    }
    
    function doGetContainerBody() {
        ob_start();
        foreach ($this->getOrderedDisplayChildren() as $child) {
            if ($child->hasContainer() && !$child->getDelayedInitialize()) {  
                $child->showContainer(); 
                echo "\n    ";
            } 
        }
        return ob_get_clean(); 
    }
    
    /**
     * @return Pmt_I_Control
     */
    protected function findNearestChildWithFrontBeforeGiven(Pmt_I_Control $child) {
        $lastChild = false;
        foreach ($this->getOrderedDisplayChildren() as $control) {
            if ($control === $child) break;
            if ($control->hasContainer() && $control->isFrontInitialized()) $lastChild = $control;
        }
        return $lastChild;
    }
    
    function initializeChildContainer(Pmt_I_Control $child) {
        // TODO
        if ($this->conversation && $this->container && strlen($this->responderId)) {
            // Determine ID of container after which we should insert container of our new control 
            $ncbg = $this->findNearestChildWithFrontBeforeGiven($child);
            if ($ncbg) $afterContainerId = $ncbg->getContainerId();
                else $afterContainerId = false;
            
            // Get container body
            ob_start();
            $child->showContainer();
            $cntBody = ob_get_clean();
            
            
            $msg = new Pm_Message();
            $msg->methodName = 'initializeChildContainer';
            $msg->recipientId = $this->responderId;
            
            // Compose and send message on behalf of our beloved host object 
            $msg->params = array(
                $cntBody,
                $afterContainerId,
                $child->getContainerId()
            );
            $this->conversation->sendClientMessage($msg);
            
            $child->notifyContainerInitialized();
            
        } else {
          Pm_Conversation::log(
              " has conversation? ".(!!$this->conversation)
              ."; has container? ".$this->container
              .", responder id is ".$this->responderId
          );
            throw new Exception ("Instance should be properly configured before calling initializeChildContainer()");
        }
    }

    function notifyContainerInitialized() {
        foreach ($this->displayChildren as $control) {
            if (!$control->getDelayedInitialize()) $control->notifyContainerInitialized();
        }
    }
    
//  --------------- functions that are not defined in Pmt_I_Control_DisplayParent ---------------   

    function setResponderId($responderId) {
        $this->responderId = $responderId;
    }

    function getResponderId() {
        return $this->responderId;
    }
        
    function setConversation(Pm_I_Conversation $conversation = null) {
        $this->conversation = $conversation;
    }

    function getConversation() {
        return $this->conversation;
    }   
    
    protected function doFrontendUpdateChildPosition(Pmt_I_Control $child, $oldIndex, $newIndex) {
    }
    
    protected function getLastDisplayChildOrder() {
        return count($this->displayChildren) - 1; 
    }
    
    protected function reorderDisplayChildren() {
        usort($this->displayChildren, array('Pmt_Impl_DisplayParent', 'sortByDisplayOrder'));
        return $this->displayChildren;
    }
    
    protected function setAllowedDisplayChildrenClass($allowedDisplayChildrenClass) {
        $this->allowedDisplayChildrenClass = $allowedDisplayChildrenClass;
    }

    function getAllowedDisplayChildrenClass() {
        return $this->allowedDisplayChildrenClass;
    }
    
    static function sortByDisplayOrder(Pmt_I_Control $child1, Pmt_I_Control $child2) {
        if (($o1 = $child1->getDisplayOrder()) < ($o2 = $child2->getDisplayOrder())) return -1;
        elseif ($o1 > $o2) return 1;
        return 0;
    }

    protected function setContainer($container) {
        $this->container = $container;
        $this->refAdd($container);
    }

    function getContainer() {
        return $this->container;
    }   
    

//  +-------------- Pm_I_Refcontrol implementation ---------------+

    protected $refReg = array();

    protected function refGetSelfVars() {
        $res = array();
        foreach (array_keys(get_object_vars($this)) as $v) $res[$v] = & $this->$v;
        return $res;
    }

    
    function refHas($otherObject) { return Pm_Impl_Refcontrol::refHas($otherObject, $this->refReg); }
    
    function refAdd($otherObject) { return Pm_Impl_Refcontrol::refAdd($this, $otherObject, $this->refReg); }
    
    function refRemove($otherObject, $nonSymmetrical = false) { $v = $this->refGetSelfVars(); return Pm_Impl_Refcontrol::refRemove($this, $otherObject, $v, false, $nonSymmetrical); }

    function refNotifyDestroying() { return Pm_Impl_Refcontrol::refNotifyDestroying($this, $this->refReg); }
    
//  +-------------------------------------------------------------+ 
    
}

?>