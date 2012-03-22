<?php

// TODO: move common DisplayParent functionality from Pmt_Composite_Display and Pmt_Group into aggregate class

class Pmt_Composite_Display extends Pmt_Composite implements Pmt_I_Control_DisplayParent {
        
    protected $displayChildren = array();
    
    protected $hasToReorderDisplayChildren = false;
    
    protected $allowedDisplayChildrenClass = false;
    
    /**
     * Aggregate that implements displayParent functionality
     * @var Pmt_Impl_DisplayParent
     */
    protected $idp = false;

    protected function createDisplayParentImpl() {
        if (!$this->idp) {
            $this->idp = new Pmt_Impl_DisplayParent(array(
                'allowedDisplayChildrenClass' => $this->allowedChildrenClass,
                'conversation' => $this->conversation? $this->conversation : null,
                'responderId' => $this->responderId,
                'container' => $this,
            ));
        }
    }
    
    protected function doOnInitialize(array $options) {
        parent::doOnInitialize($options);
        $this->createDisplayParentImpl();
    }
    
    function setConversation(Pm_I_Conversation $conversation) {
        if ($this->idp) {
            $this->idp->setConversation($conversation);
            $this->idp->setResponderId($this->getResponderId());
        }
        $res = parent::setConversation($conversation);
        return $res;
    }       
    
    function addControl(Pmt_I_Control $control) {
        parent::addControl($control);
        if (!$control->getDisplayParent()) $this->addDisplayChild($control);
    }
    
//  Pmt_I_Control_DisplayParent 
    
    
    function getOrderedDisplayChildren() {
        $this->listControls();
        return $this->idp->getOrderedDisplayChildren(); 
    }
    
    function findDisplayChild(Pmt_I_Control $child) {
        //$this->listControls();
        return $this->idp->findDisplayChild($child);
    }
        
    function addDisplayChild(Pmt_I_Control $child) {
        $this->listControls();
        return $this->idp->addDisplayChild($child);
    }
    
    function removeDisplayChild(Pmt_I_Control $child) {
        $this->listControls();
        return $this->idp->removeDisplayChild($child);
    }
    
    function updateDisplayChildPosition(Pmt_I_Control $child, $displayOrder) {
        $this->listControls();
        return $this->idp->updateDisplayChildPosition($child, $displayOrder);
    }
    
    protected function doGetContainerBody() {
        $this->listControls();
        return $this->idp->doGetContainerBody();
    }
    
    function deleteControl(Pmt_I_Control $control) {
        $this->listControls();
        $this->idp->removeDisplayChild($control);
        return parent::deleteControl($control);
    }

    function initializeChildContainer(Pmt_I_Control $child) {
        //$this->listControls();
        return $this->idp->initializeChildContainer($child);
    }
    
    function notifyContainerInitialized() {
        if (!$this->containerInitialized) {
            parent::notifyContainerInitialized();
            $this->listControls();
            $this->idp->notifyContainerInitialized();
        }
    }
    
    
}

?>