<?php

class Pmt_Tree_Parent extends Pmt_Composite_Display {
    
    protected $className = false;
    
    protected $allowPassthroughEvents = true;
    
    protected $allowedChildrenClass = 'Pmt_Tree_Node';
    
    protected $defaultChildrenClass = 'Pmt_Tree_Node';
    
    protected $allowedDisplayChildrenClass = 'Pmt_Tree_Node';
    
    protected $observeChildClicks = false;
    
    protected $observeChildExpand = false;
    
    protected $observeChildCollapse = false;
    
    protected $observeChildCheckedChange = false;
    
    protected $observeChildBranchToggle = false;
    
    protected function doListPassthroughParams() {
        return array_merge(parent::doListPassthroughParams(), array('className'));
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
            'widgets/yui/tree.js',
        )); 
    }
    
    function setClassName($className) {
        if ($className !== ($oldClassName = $this->className)) {
            $this->className = $className;
            $this->sendMessage(__FUNCTION__, array($className), 1);
        }
    }

    function getClassName() {
        return $this->className;
    }

    function hasJsObject() {
        return true;
    }
    
    function notifyChildClick(Pmt_Tree_Node $child) {
        $this->triggerEvent('childClick', array('child' => $child));
    }
    
    function notifyChildDblClick(Pmt_Tree_Node $child) {
        $this->triggerEvent('childDblClick', array('child' => $child));
    }
    
    function notifyChildExpand(Pmt_Tree_Node $child) {
        $this->triggerEvent('childExpand', array('child' => $child));
    }
    
    function notifyChildCollapse(Pmt_Tree_Node $child) {
        $this->triggerEvent('childCollapse', array('child' => $child));
    }
    
    function notifyChildCheckedChange(Pmt_Tree_Node $child) {
        $this->triggerEvent('childCheckedChange', array('child' => $child));
    }
    
    function notifyChildToggleBranch(Pmt_Tree_Node $child) {
        $this->triggerEvent('childToggleBranch', array('child' => $child));
    }
    
    function observe($eventType, Pm_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array()) {
        if ($eventType === 'childClick' || $eventType === 'childDblClick') $this->observeChildClicks = true;
        if ($eventType === 'childExpand') $this->observeChildExpand = true;
        if ($eventType === 'childCollapse') $this->observeChildCollapse = true;
        if ($eventType === 'childCheckedChange') $this->observeChildCheckedChange = true;
        if ($eventType === 'childBranchToggle') $this->observeChildBranchToggle = true;
        return parent::observe($eventType, $observer, $methodName, $extraParams);
    }
    
    function clear() {
        foreach ($this->listControls() as $i) {
        	$c = $this->getControl($i);
        	if ($c->id !== 'insetPanel') $c->destroy();
        }
    }

}

?>