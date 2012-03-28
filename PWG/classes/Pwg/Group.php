<?php

// TODO: move common DisplayParent functionality from Pmt_Composite_Display and Pmt_Group into aggregate class

class Pmt_Group extends Pmt_Element implements Pmt_I_Control_DisplayParent {
        
    protected $layoutStyle = 'vertical';
    
    protected $allowedLayoutStyles = array('horizontal', 'vertical', 'free');
    
    protected $allowedDisplayChildrenClass = false;
    
    /**
     * Aggregate that implements displayParent functionality
     * @var Pmt_Impl_DisplayParent
     */
    protected $idp = false;

    
    protected function doOnInitialize(array $options) {
        parent::doOnInitialize($options);
        $this->idp = new Pmt_Impl_DisplayParent(array(
            'allowedDisplayChildrenClass' => $this->allowedDisplayChildrenClass,
            'conversation' => $this->conversation? $this->conversation : null,
            'responderId' => $this->responderId,
            'container' => $this,
        ));
    }
    
    function setConversation(Pm_I_Conversation $conversation) {
        $res = parent::setConversation($conversation);
        if ($this->idp) {
            $this->idp->setConversation($conversation);
            $this->idp->setResponderId($this->responderId);
        }
        return $res;
    }       
    
//  Pmt_Group   

    function setLayoutStyle($v) {
        if (!in_array($v, $this->allowedLayoutStyles))
            throw new Exception("Wrong ".__FUNCTION__."() argument ('{$v}'); allowed styles are '".implode(', ', $this->allowedStyles)."'");
        
        $prop = substr(__FUNCTION__, 3); $prop{0} = strtolower($prop{0}); 
        if (!is_null($v)) $this->$prop = $v;
        $this->sendMessage(__FUNCTION__, array($this->$prop));
    }
    
    function getLayoutStyle() {
        $prop = substr(__FUNCTION__, 3); $prop{0} = strtolower($prop{0});
        return $this->$prop;
    }
    
//  Pmt_I_Control_DisplayParent 
    
    function getOrderedDisplayChildren() {
        return $this->idp->getOrderedDisplayChildren(); 
    }
    
    function findDisplayChild(Pmt_I_Control $child) {
        return $this->idp->findDisplayChild($child);
    }
        
    function addDisplayChild(Pmt_I_Control $child) {
        return $this->idp->addDisplayChild($child);
    }
    
    function removeDisplayChild(Pmt_I_Control $child) {
        return $this->idp->removeDisplayChild($child);
    }
    
    function updateDisplayChildPosition(Pmt_I_Control $child, $displayOrder) {
        return $this->idp->updateDisplayChildPosition($child, $displayOrder);
    }
    
    function initializeChildContainer(Pmt_I_Control $child) {
        return $this->idp->initializeChildContainer($child);
    }
    
    function notifyContainerInitialized() {
        if (!$this->containerInitialized) {
            parent::notifyContainerInitialized();
            $this->idp->notifyContainerInitialized();
        }
    }
    
//  Template methods    
    
    protected function hasJsObject() {
        return true;
    }
    
    function hasContainer() {
        return true;
    }
    
//  Implementation methods

    protected function doGetContainerBody() {
        $controls = array();
        ob_start();
        foreach ($this->getOrderedDisplayChildren() as $c) { if ($c->hasContainer()) $controls[] = $c; }
        $m = 'show'.ucFirst($this->layoutStyle);
        $this->$m($controls);
        return ob_get_clean(); 
    }
    
    protected function showHorizontal($controls) {
        $attribs = $this->attribs;
        if ($this->style) $attribs['style'] = $this->style;
?>
        <table <?php echo Ae_Util::mkAttribs($attribs); ?>>
            <tr>
<?php           foreach ($controls as $control) { ?>
                <td>
<?php               $control->showContainer(); ?> 
                </td>
<?php           } ?>                
            </tr>
        </table>
<?php
    }
    
    protected function showVertical($controls) {
        $attribs = $this->attribs;
        if ($this->style) $attribs['style'] = $this->style;
?>
        <div <?php echo Ae_Util::mkAttribs($attribs); ?>>
<?php           foreach ($controls as $control) { ?>
                <div>
<?php               $control->showContainer(); ?> 
                </div>
<?php           } ?>                
        </div>
<?php
    }
    
    protected function showFree($controls) {
        $attribs = $this->attribs;
        if ($this->style) $attribs['style'] = $this->style;
?>
        <div <?php echo Ae_Util::mkAttribs($attribs); ?>>
<?php           foreach ($controls as $control) { ?>
<?php               $control->showContainer(); ?> 
<?php           } ?>                
        </div>
<?php
    }
    
}

?>