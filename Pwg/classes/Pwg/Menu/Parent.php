<?php

abstract class Pmt_Menu_Parent extends Pmt_Composite_Display {
    
    protected $allowPassthroughEvents = true;
    
    protected $allowedChildrenClass = 'Pmt_Menu_Item';
    
    protected $defaultChildrenClass = 'Pmt_Menu_Item';
    
    protected $allowedDisplayChildrenClass = 'Pmt_Menu_Item';

    protected $disabled = false;

    protected $visible = true;
    
    protected $attribs = false;

    protected $className = null;

    protected $style = false;
    
    protected $observeChildClicks = false;
    
    function setDisabled($disabled) {
        if ($disabled !== ($oldDisabled = $this->disabled)) {
            $this->disabled = $disabled;
            $a = func_get_args();
            $this->sendMessage(__FUNCTION__, $a);
        }
    }

    function getDisabled() {
        return $this->disabled;
    }

    function setVisible($visible) {
        $visible = (bool) $visible;
        if ($visible !== ($oldVisible = $this->visible)) {
            $this->visible = $visible;
            $a = func_get_args();
            $this->sendMessage(__FUNCTION__, $a);
        }
    }

    function getVisible() {
        return $this->visible;
    }

    function setAttribs($attribs) {
        if ($attribs !== ($oldAttribs = $this->attribs)) {
            $this->attribs = $attribs;
            $a = func_get_args();
            $this->sendMessage(__FUNCTION__, $a);
        }
    }

    function getAttribs() {
        return $this->attribs;
    }

    function setClassName($className) {
        if ($className !== ($oldClassName = $this->className)) {
            $this->className = $className;
            $a = func_get_args();
            $this->sendMessage('setClassname', $a);
        }
    }

    function getClassName() {
        return $this->className;
    }

    function setStyle($style) {
        if ($style !== ($oldStyle = $this->style)) {
            $this->style = $style;
            $a = func_get_args();
            $this->sendMessage(__FUNCTION__, $a);
        }
    }

    function getStyle() {
        return $this->style;
    }
    
    /**
     * @param string $id
     * @return Pmt_Menu
     */
    function getControl($id) {
        return parent::getControl($id);
    }
    
    function doListPassthroughParams() {
        return array_merge(parent::doListPassthroughParams(), array(
            'disabled',
            'visible',
            'attribs',
            'className' => 'classname',
            'style',
            //'children'
        ));
    }

    function doGetAssetLibs() {
        return array_merge(parent::doGetAssetLibs(), array(
            '{YUI}/fonts/fonts.css',
            '{YUI}/menu/assets/skins/sam/menu.css',
            '{YUI}/yahoo/yahoo.js',
            '{YUI}/dom/dom.js',
            '{YUI}/event/event.js',
            '{YUI}/container/container.js',
            '{YUI}/menu/menu.js',
            'widgets.js',
            'widgets/yui/util.js',
            'widgets/yui/menu.js',
        )); 
    }
    
    function hasJsObject() { return true; }
    
    function notifyChildClick(Pmt_Menu_Item $child) {
        $this->triggerEvent('childClick', array('child' => $child));
    }
    
    function observe($eventType, Pm_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array()) {
        if ($eventType === 'childClick') $this->observeChildClicks = true;
        return parent::observe($eventType, $observer, $methodName, $extraParams);
    }
    
}

?>