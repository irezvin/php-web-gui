<?php

// TODO: move common DisplayParent functionality from Pwg_Composite_Display and Pwg_Group into aggregate class

class Pwg_Composite_Display extends Pwg_Composite implements Pwg_I_Control_DisplayParent {
        
    protected $displayChildren = array();
    
    protected $hasToReorderDisplayChildren = false;
    
    protected $allowedDisplayChildrenClass = false;
    
    /**
     * Aggregate that implements displayParent functionality
     * @var Pwg_Impl_DisplayParent
     */
    protected $idp = false;

    protected function createDisplayParentImpl() {
        if (!$this->idp) {
            $this->idp = new Pwg_Impl_DisplayParent(array(
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
    
    function setConversation(Pwg_I_Conversation $conversation) {
        if ($this->idp) {
            $this->idp->setConversation($conversation);
            $this->idp->setResponderId($this->getResponderId());
        }
        $res = parent::setConversation($conversation);
        return $res;
    }       
    
    function addControl(Pwg_I_Control $control) {
        parent::addControl($control);
        if (!$control->getDisplayParent()) $this->addDisplayChild($control);
    }
    
//  Pwg_I_Control_DisplayParent 
    
    
    function getOrderedDisplayChildren() {
        $this->listControls();
        return $this->idp->getOrderedDisplayChildren(); 
    }
    
    function findDisplayChild(Pwg_I_Control $child) {
        //$this->listControls();
        return $this->idp->findDisplayChild($child);
    }
        
    function addDisplayChild(Pwg_I_Control $child) {
        $this->listControls();
        return $this->idp->addDisplayChild($child);
    }
    
    function removeDisplayChild(Pwg_I_Control $child) {
        $this->listControls();
        return $this->idp->removeDisplayChild($child);
    }
    
    function updateDisplayChildPosition(Pwg_I_Control $child, $displayOrder) {
        $this->listControls();
        return $this->idp->updateDisplayChildPosition($child, $displayOrder);
    }
    
    protected function doGetContainerBody() {
        $this->listControls();
        return $this->idp->doGetContainerBody();
    }
    
    function deleteControl(Pwg_I_Control $control) {
        $this->listControls();
        $this->idp->removeDisplayChild($control);
        return parent::deleteControl($control);
    }

    function initializeChildContainer(Pwg_I_Control $child) {
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