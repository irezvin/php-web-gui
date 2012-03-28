<?php

class Pmt_Yui_Tree extends Pmt_Element implements Pmt_I_Control_DisplayParent {

    const evtChildClick = 'childClick';
    const evtChildDblClick = 'childDblClick';
    const evtChildExpand = 'childExpand';
    const evtChildCollapse = 'childCollapse';
    const evtChildCheckedChange = 'childCheckedChange';
    const evtChildToggleBranch = 'childToggleBranch';
    
    /**
     * @var Pmt_Yui_Tree_Node_Root
     */
	protected $rootNode = false;
	
	/**
	 * @var Pmt_Yui_Tree_Node
	 */	
	protected $insetPanelNode = false;
	
	/**
	 * @var Pmt_Yui_Tree_Node
	 */	
	protected $selectedNode = false;
	
    /**
     * Aggregate that implements displayParent functionality
     * @var Pmt_Impl_DisplayParent
     */
    protected $idp = false;
	
    protected $nodePrototypes = array();
    
    protected function doOnInitialize(array $options) {
        parent::doOnInitialize($options);
        $this->idp = new Pmt_Impl_DisplayParent(array(
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
    
    /**
     * @return Pmt_Yui_Tree_Root
     */
    function getRootNode() {
        if ($this->rootNode === false) {
        	$this->rootNode = new Pmt_Yui_Tree_Root(array('tree' => $this, 'nodePrototypes' => $this->nodePrototypes));
        }
        return $this->rootNode;
    }
    
    function findNodesByPattern(array $pattern = array(), $baseClass = 'Pmt_Struct_Tree_Node', $recursive = true, $strict = false) {
        return $this->getRootNode()->findNodesByPattern($pattern, $baseClass, $recursive, $strict);
    }    

    protected function setNodePrototypes(array $nodePrototypes) {
        $this->nodePrototypes = $nodePrototypes;
    }
    	
	protected function doGetAssetLibs() {
		return array_merge(parent::doGetAssetLibs(), array(
            '{YUI}/treeview/assets/skins/sam/treeview.css',
            '{YUI}/yahoo/yahoo.js',
            '{YUI}/dom/dom.js',
            '{YUI}/event/event.js',
            '{YUI}/element/element.js',
            '{YUI}/treeview/treeview.js',
            'widgets.js',
            'widgets/yui/util.js',
            'widgets/yui/treeNew.js',
		    'widgets/yui/tree/toggle.css',
		));
	}
	
	protected function doListPassthroughParams() {
		return array_merge(parent::doListPassthroughParams(), array(
			'rootNode' => 'nodePrototypes',
		    'insetPanelNode',
		    'selectedNode',
		));
	}
	
	function hasContainer() {
		return true;
	}
	
	protected function doGetConstructorName() {
		return 'Pmt_Yui_Tree_New';
	}

	
	// Messages and events
	
	
	function msgSetNodeProperty(Pmt_Yui_Tree_Node $node, $propName, $propValue) {
		$this->sendMessage(__FUNCTION__, $a = array($node->getIndexPath(), $propName, $propValue));
	}
	
	function msgExecuteNodeMethod(Pmt_Yui_Tree_Node $node, $methodName, array $args = array()) {
		$this->sendMessage(__FUNCTION__, array($node->getIndexPath(), $methodName, $args));
	}
	
	
	function msgAddNode(Pmt_Yui_Tree_Node $node, Pmt_Yui_Tree_Node $parent, $index) {
	    $this->sendMessage(__FUNCTION__, array($node->toJs(), $parent->getIndexPath(), $index));
	}
	
	function msgRemoveNode(Pmt_Yui_Tree_Node $node) {
		$this->sendMessage(__FUNCTION__, array($node->getIndexPath()));
	}
	
	function msgRemoveChild(Pmt_Yui_Tree_Node $node, $childIndex) {
	    $this->sendMessage(__FUNCTION__, array($node->getIndexPath(), $childIndex));
	}
	
	function msgDeleteNode(Pmt_Yui_Tree_Node $node) {
		$this->sendMessage(__FUNCTION__, array($node->getIndexPath()));
	}
	
	function msgMoveNode(Pmt_Yui_Tree_Node $node, $newIndex) {
		$this->sendMessage(__FUNCTION__, array($node->getIndexPath(), $newIndex));
	}
	
	function msgScrollNodeIntoView(Pmt_Yui_Tree_Node $node) {
		$this->sendMessage(__FUNCTION__, array($node->getIndexPath()));
	}
	
	//function msgScrollIntoView
	
	
	function notifyChildExpandChange(Pmt_Yui_Tree_Node $node, $isExpanded) {
	    $this->triggerEvent(
	        $isExpanded? self::evtChildExpand : self::evtChildCollapse, 
	        array('child' => $node, 'byUser' => false)
	    );
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


    protected function doGetContainerBody() {
        $controls = array();
        ob_start();
        foreach ($this->getOrderedDisplayChildren() as $c)
            $c->showContainer();
            
        $body = ob_get_clean();
        $res = Ae_Util::mkElement('div', $body, array('id' => $this->getContainerId().'_insetPanel'));
        return $res; 
    } 

    // InsetPanelNode property    
    
    function setInsetPanelNode(Pmt_Yui_Tree_Node $insetPanelNode = null) {
        if ($insetPanelNode !== ($oldInsetPanelNode = $this->insetPanelNode)) {
            $this->insetPanelNode = $insetPanelNode;
            $this->sendMessage(__FUNCTION__, array($this->jsGetInsetPanelNode()));
        }
    }

    function getInsetPanelNode() {
        return $this->insetPanelNode;
    }    
    
	protected function jsGetInsetPanelNode() {
		if ($this->insetPanelNode) return $this->insetPanelNode->getIndexPath();
			else return false;
	}

    // SelectedNode property    
	
    function setSelectedNode(Pmt_Yui_Tree_Node $selectedNode = null) {
        if ($selectedNode !== ($oldSelectedNode = $this->selectedNode)) {
            $this->selectedNode = $selectedNode;
            if ($selectedNode) $selectedNode->expandAncestors();
            $this->sendMessage(__FUNCTION__, array($this->jsGetSelectedNode()));
        }
    }

    /**
     * @return Pmt_Yui_Tree_Node
     */
    function getSelectedNode() {
        return $this->selectedNode;
    }    
	
	protected function jsGetSelectedNode() {
		if ($this->selectedNode) return $this->selectedNode->getIndexPath();
			else return false;
	}
	
	function clear() {
	    $this->sendMessage(__FUNCTION__);
	    $this->lockMessages();
	    $this->selectedNode = null;
	    $this->insetPanelNode = null;
	    $idx = array_reverse($this->getRootNode()->listNodes());
	    foreach($idx as $i) $this->getRootNode()->getNode($i)->destroy();
	    $this->unlockMessages();
	}
    
//  Events support

    function triggerFrontendNodeEvent($eventType, $uid, $checked = null) {
        $n = $this->getRootNode()->findNodeByUid($uid);
        if ($n) {
            if ($eventType === 'childCheckedChange') {
                $checked = (int) $checked;
                $this->lockMessages();
                $n->setChecked((bool) $checked);
                $this->unlockMessages();
            }
            if ($eventType === 'childClick') {
                $this->setSelectedNode($n);
            }
            if ($eventType === 'childExpand') {
                $n->setExpanded(true, true);
            }
            if ($eventType === 'childCollapse') {
                $n->setExpanded(false, true);
            }
            $this->triggerEvent($eventType, array('child' => $n, 'byUser' => true));
        }
    }
    
}
