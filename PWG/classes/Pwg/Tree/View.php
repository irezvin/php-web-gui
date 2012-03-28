<?php

class Pwg_Tree_View extends Pwg_Tree_Parent {

	protected $insetPanel = false;
	
	/**
	 * @var Pwg_Tree_Node
	 */	
	protected $insetPanelNode = false;
	
//    protected function getControlPrototypes() {
//        $res = $this->controlPrototypes;
//        if (isset($res['insetPanel']) && !isset($res['insetPanel']['class'])) $res['insetPanel']['class'] = 'Pwg_Yui_Panel';
//        return $res;
//    }
    
    function createControl(array $prototype, $id = false, $baseClass = false) {
    	if ($id === 'insetPanel') {
    		$baseClass = 'Pwg_Panel';
			if (!$this->idp) $this->createDisplayParentImpl();
			$this->idp->ignoreNextControlClass();
			$this->ignoreNextControlClass();
			return $this->insetPanel = parent::createControl($prototype, $id, $baseClass); 
    	} else return parent::createControl($prototype, $id, $baseClass);
    }
	
    function getControl($id) {
        $res = parent::getControl($id);
        if ($res === false && $id === 'insetPanel') $res = $this->getInsetPanel();
        return $res;
    }
	
	function getInsetPanel() {
		if (!$this->insetPanel) {
			if ($this->controlsCreated) $this->insetPanel = $this->createControl(array(), 'insetPanel', 'Pwg_Panel');
		}
		return $this->insetPanel;
	}
	
	protected function doListPassthroughParams() {
		return array_merge(parent::doListPassthroughParams(), array(
			'insetPanelContainerId',
			'insetPanelNode'
		));
	}
	
	protected function jsGetInsetPanelContainerId() {
		if (isset($this->controls['insetPanel'])) {
			$res = $this->controls['insetPanel']->getContainerId();
		} else $res = false;
		return $res;
	}
	
    function hasContainer() {
        return true;
    }
    
    function getContainerAttribs() { 
        $res = parent::getContainerAttribs();
        if ($this->className !== false) 
            $res['className'] = $this->className;
        return $res;  
    }
    
    function notifyNodeDestroyed(Pwg_Tree_Node $node) {
    }

    function setInsetPanelNode(Pwg_Tree_Node $insetPanelNode = null) {
        if ($insetPanelNode !== ($oldInsetPanelNode = $this->insetPanelNode)) {
            $this->insetPanelNode = $insetPanelNode;
            $this->sendMessage(__FUNCTION__, array($this->jsGetInsetPanelNode()));
        }
    }

    function getInsetPanelNode() {
        return $this->insetPanelNode;
    }    
	
	protected function jsGetInsetPanelNode() {
		if ($this->insetPanelNode) return $this->insetPanelNode->getResponderId();
			else return false;
	}
    
}

?>