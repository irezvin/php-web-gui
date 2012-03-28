<?php

class Pwg_Yui_Rte extends Pwg_Element {
    
    protected $height = 300;

    protected $width = 500;

    protected $dompath = true;

    protected $animate = false;

    protected $isSimple = false;

    protected $text = false;
    
    protected $title = null;
    
    protected $disabled = false;
    
    protected $readOnly = false;
    
    protected $extraConfig = array();
    
    protected $toolbarTitle = null;

    protected $toolbarCollapsed = null;
    
    protected $canEditHtml = true;    
    
    protected $resizeable = true;

    /**
     * See invalidHTML property of YUI RTE description 
     * http://developer.yahoo.com/yui/docs/YAHOO.widget.SimpleEditor.html#property_invalidHTML
     */
    protected $invalidHtml = array(
        'form' => true, 
        'input' => true, 
        'button' => true, 
        'select' => true, 
        'link' => true, 
        'html' => true, 
        'body' => true, 
        'iframe' => true, 
        'script' => true, 
        'style' => true, 
        'textarea' => true
    );
    
    protected function doListPassthroughParams() {
        return array_merge(parent::doListPassthroughParams(), array(
            'isSimple', 
            'dompath', 
            'animate', 
            'text', 
            'width',
            'height', 
            'toolbarTitle',
            'toolbarCollapsed', 
            'disabled',
            'extraConfig',
            'canEditHtml',
            'resizeable',
            'invalidHtml'
        )); 
    }

    function setHeight($height) {
        if ($height !== ($oldHeight = $this->height)) {
            $this->height = $height;
            $this->sendMessage(__FUNCTION__, array($height), 1);
        }
    }

    function getHeight() {
        return $this->height;
    }

    function setWidth($width) {
        if ($width !== ($oldWidth = $this->width)) {
            $this->width = $width;
            $this->sendMessage(__FUNCTION__, array($width), 1);
        }
    }

    function getWidth() {
        return $this->width;
    }

    function setDompath($dompath) {
        if ($dompath !== ($oldDompath = $this->dompath)) {
            $this->dompath = $dompath;
            $this->sendMessage(__FUNCTION__, array($dompath), 1);
        }
    }

    function getDompath() {
        return $this->dompath;
    }

    function setAnimate($animate) {
        if ($animate !== ($oldAnimate = $this->animate)) {
            $this->animate = $animate;
            $this->sendMessage(__FUNCTION__, array($animate), 1);
        }
    }

    function getAnimate() {
        return $this->animate;
    }

    protected function setIsSimple($isSimple) {
        $this->isSimple = $isSimple;
    }

    function getIsSimple() {
        return $this->isSimple;
    }   
    
    function setDisabled($disabled) {
        $oldDisabled = $this->getDisabled();
        $this->disabled = $disabled;
        if ($this->getDisabled() !== $oldDisabled) {
            $this->sendMessage(__FUNCTION__, array($this->getDisabled()), 1);
        }
    }
    
    function getDisabled() {
        return $this->disabled || $this->readOnly;
    }
    
    function setReadOnly($readOnly) {
        $this->readOnly = $readOnly;
        $this->setDisabled($this->disabled);
    }
    
    function getReadOnly() {
        return $this->readOnly;
    }
    
    function setTitle($title) {
        if ($title !== ($oldTitle = $this->title)) {
            $this->title = $title;
            $this->sendMessage(__FUNCTION__, array($title), 1);
        }
    }

    function getTitle() {
        return $this->title;
    }    

    function setText($text) {
        if ($text !== ($oldText = $this->text)) {
            $this->text = $text;
            $this->sendMessage(__FUNCTION__, array($text), 1);
        }
    }
    
    function getText() {
        return $this->text;
    }   
    
    function doGetAssetLibs() {
        $res = array_merge(parent::doGetAssetLibs(), array(
            'widgets/yui/rte.js',
        ));
        if ($this->isSimple) {
            $extraLibs = array(
                '{YUI}/assets/skins/sam/skin.css',
                '{YUI}/yahoo/yahoo.js',
                '{YUI}/dom/dom.js',
                '{YUI}/event/event.js',
                '{YUI}/element/element.js',
                '{YUI}/container/container.js',
                '{YUI}/editor/simpleeditor.js',
            );
        } else {
            $extraLibs = array(
                '{YUI}/assets/skins/sam/skin.css',
                '{YUI}/yahoo/yahoo.js',
                '{YUI}/dom/dom.js',
                '{YUI}/event/event.js',
                '{YUI}/element/element.js',
                '{YUI}/container/container.js',
                '{YUI}/menu/menu.js',
                '{YUI}/button/button.js',
                '{YUI}/editor/editor.js',
            );
        }
        if ($this->resizeable) {
            $extraLibs[] = '{YUI}/animation/animation.js';
            $extraLibs[] = '{YUI}/dragdrop/dragdrop.js';
            $extraLibs[] = '{YUI}/resize/resize.js';
        }
        $res = array_merge($res, $extraLibs);
        
        return $res;
    }

    /**
     * Associative array with additional configuration options that are used to create YAHOO.Widget.Editor or YAHOO.Widget.SimpleEditor 
     */
    protected function setExtraConfig(array $extraConfig) {
        $this->extraConfig = $extraConfig;
    }

    function getExtraConfig() {
        return $this->extraConfig;
    }
    
    protected function doGetContainerBody() {
        $attr = $this->attribs;
        $attr['name'] = $this->getContainerId().'_textarea';
        return Ae_Util::mkElement('textarea', htmlspecialchars($this->text), $attr);
    }
    
    function triggerFrontendChange($newText) {
        if ($this->text !== $newText) {
            $this->text = $newText;
            $this->triggerEvent('change', array('text' => $newText));
        }
    }

    function setToolbarTitle($toolbarTitle) {
        if ($toolbarTitle !== ($oldToolbarTitle = $this->toolbarTitle)) {
            $this->toolbarTitle = $toolbarTitle;
            $this->sendMessage(__FUNCTION__, array($toolbarTitle), 1);
        }
    }

    function getToolbarTitle() {
        return $this->toolbarTitle;
    }

    function setToolbarCollapsed($toolbarCollapsed) {
        if ($toolbarCollapsed !== ($oldToolbarCollapsed = $this->toolbarCollapsed)) {
            $this->toolbarCollapsed = $toolbarCollapsed;
            $this->sendMessage(__FUNCTION__, array($toolbarCollapsed), 1);
        }
    }

    function getToolbarCollapsed() {
        return $this->toolbarCollapsed;
    }

    function triggerFrontendToolbarCollapsed($collapsed) {
        if ($collapsed === 'false') $collapsed = false; else $collapsed = true;
        if ($this->toolbarCollapsed !== $collapsed) {
            $this->toolbarCollapsed = $collapsed;
            $this->triggerEvent('toolbarCollapsed', array('toolbarCollapsed' => $collapsed));
        }
    }

    protected function setCanEditHtml($canEditHtml) {
        $this->canEditHtml = $canEditHtml;
    }

    function getCanEditHtml() {
        return $this->canEditHtml;
    }
    

    protected function setResizeable($resizeable) {
        $this->resizeable = $resizeable;
    }

    function getResizeable() {
        return $this->resizeable;
    }

    function triggerFrontendResize($width, $height) {
        if (is_numeric($width)) $this->width = $width;
        if (is_numeric($height)) $this->height = $height;
    }
    
    function setInvalidHtml(array $invalidHtml) {
        if ($invalidHtml != ($oldInvalidHtml = $this->invalidHtml)) {
            $this->invalidHtml = $invalidHtml;
            $this->sendMessage(__FUNCTION__, array($invalidHtml));
        }
    }

    function getInvalidHtml() {
        return $this->invalidHtml;
    }    
        
}

?>