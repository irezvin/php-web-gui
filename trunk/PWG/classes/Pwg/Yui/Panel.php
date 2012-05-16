<?php

class Pwg_Yui_Panel extends Pwg_Panel {
    
    protected $width = null;

    protected $height = null;

    protected $draggable = true;

    protected $close = false;

    protected $modal = false;

    protected $underlay = 'none';

    protected $fixedCenter = false;

    protected $header = '';

    protected $footer = '';

    protected $visible = false;
    
    protected $zIndex = null;
    
    protected $x = null;

    protected $y = null;
    
    protected $resizeable = null;
    
    protected $minWidth = null;

    protected $minHeight = null;
    
    protected $hideOnClose = true;
    
    protected $closeOnOutsideClick = false;
    
    protected $context = array();

    protected $focused = true;
    
    protected $autoSize = 'auto';
    
    protected $headerWrap = '';

    protected $footerWrap = '';

    protected $showAtCenter = false;
    
    /**
     * $headerWrap should contain {{content}} placeholder where header will be substituted.
     * @param string $headerWrap HTML that surrounds header content provides with setHeader() 
     */
    function setHeaderWrap($headerWrap) {
        if ($headerWrap !== ($oldHeaderWrap = $this->headerWrap)) {
            $this->headerWrap = $headerWrap;
            if (strlen($this->headerWrap) && (strpos($this->headerWrap, '{{content}}') === false)) {
                trigger_error("{{content}} placeholder not found in \$headerWrap string '{$headerWrap}'", E_USER_NOTICE);
            }
            $this->setHeader($this->header, true);
        }
    }

    function getHeaderWrap() {
        return $this->headerWrap;
    }

    function setFooterWrap($footerWrap) {
        if ($footerWrap !== ($oldFooterWrap = $this->footerWrap)) {
            $this->footerWrap = $footerWrap;
            if (strlen($this->footerWrap) && (strpos($this->footerWrap, '{{content}}') === false)) {
                trigger_error("{{content}} placeholder not found in \$footerWrap string '{$footerWrap}'", E_USER_NOTICE);
            }
            $this->setFooter($this->footer, true);
        }
    }

    function getFooterWrap() {
        return $this->footerWrap;
    }    
    
//  +---------------------- runtime and initializer accessors ------------------+     

    function setX($x) {
        if ($x !== ($oldX = $this->x)) {
            $this->x = $x;
            $this->sendMessage(__FUNCTION__, array($x), 1);
        }
    }

    function getX() {
        return $this->x;
    }

    function setY($y) {
        if ($y !== ($oldY = $this->y)) {
            $this->y = $y;
            $this->sendMessage(__FUNCTION__, array($y), 1);
        }
    }

    function getY() {
        return $this->y;
    }
        
    function setWidth($width) {
        if ($width !== ($oldWidth = $this->width)) {
            if ((int) $this->minWidth) $width = max($width, $this->minWidth);
            $this->width = $width;
            $this->sendMessage(__FUNCTION__, array($width), 1);
        }
    }

    function getWidth() {
        return $this->width;
    }

    function setHeight($height) {
        if ($height !== ($oldHeight = $this->height)) {
            if ((int) $this->minHeight) $height = max($height, $this->minHeight);
            $this->height = $height;
            $this->sendMessage(__FUNCTION__, array($height), 1);
        }
    }

    function getHeight() {
        return $this->height;
    }

    function setMinWidth($minWidth) {
        if ($minWidth !== ($oldMinWidth = $this->minWidth)) {
            $this->minWidth = $minWidth;
            $this->sendMessage(__FUNCTION__, $minWidth);
            if ((int) $this->minWidth) $width = max($this->width, $this->minWidth);
            if ($this->width !== $width) $this->setWidth($width); 
        }
    }

    function getMinWidth() {
        return $this->minWidth;
    }

    function setMinHeight($minHeight) {
        if ($minHeight !== ($oldMinHeight = $this->minHeight)) {
            $this->minHeight = $minHeight;
            $this->sendMessage(__FUNCTION__, $minHeight);
            if ((int) $this->minHeight) $height = max($this->height, $this->minHeight);
            if ($this->height !== $height) $this->setHeight($height); 
        }
    }

    function getMinHeight() {
        return $this->minHeight;
    }   
    
    function setDraggable($draggable) {
        if ($draggable !== ($oldDraggable = $this->draggable)) {
            $this->draggable = $draggable;
            $this->sendMessage(__FUNCTION__, array($draggable), 1);
        }
    }

    function getDraggable() {
        return $this->draggable;
    }

    protected function setClose($close) {
        $this->close = $close;
    }

    function getClose() {
        return $this->close;
    }

    protected function setModal($modal) {
        $this->modal = $modal;
    }

    function getModal() {
        return $this->modal;
    }

    function setUnderlay($underlay) {
        if (!in_array($underlay, array('shadow', 'matte', 'none'))) throw new Exception("Allowed \$underlay values are 'shadow'|'matte'|'none'");
        if ($underlay !== ($oldUnderlay = $this->underlay)) {
            $this->underlay = $underlay;
            $this->sendMessage(__FUNCTION__, array($underlay), 1);
        }
    }

    function getUnderlay() {
        return $this->underlay;
    }

    protected function setFixedCenter($fixedCenter) {
        if ($fixedCenter !== ($oldFixedCenter = $this->fixedCenter)) {
            $this->fixedCenter = $fixedCenter;
        }
    }

    function getFixedCenter() {
        return $this->fixedCenter;
    }

    function setHeader($header, $force = false) {
        if ($force || ($header !== ($oldHeader = $this->header))) {
            $this->header = $header;
            $this->sendMessage(__FUNCTION__, array($this->jsGetHeader()), 1);
        }
    }

    function getHeader() {
        return $this->header;
    }
    
    protected function jsGetHeader() {
        if (strlen($this->headerWrap)) $res = str_replace('{{content}}', $this->header, $this->headerWrap);
            else $res = $this->header;
        return $res;
    }

    function setFooter($footer, $force = false) {
        if ($force || ($footer !== ($oldFooter = $this->footer))) {
            $this->footer = $footer;
            $this->sendMessage(__FUNCTION__, array($this->jsGetFooter()), 1);
        }
    }

    function getFooter() {
        return $this->footer;
    }
    
    protected function jsGetFooter() {
        if (strlen($this->footerWrap)) $res = str_replace('{{content}}', $this->footer, $this->footerWrap);
            else $res = $this->footer;
        return $res;
    }
    
    function setVisible($visible) {
        $visible = (bool) $visible;
        if ($visible !== ($oldVisible = $this->visible)) {
            $this->visible = $visible;
            if (!$visible) $this->focused = false;
            $this->sendMessage(__FUNCTION__, array($visible), 1);
        }
    }

    function getVisible() {
        return $this->visible;
    }

    function setZIndex($zIndex) {
        if ($zIndex !== ($oldZIndex = $this->zIndex)) {
            $this->zIndex = $zIndex;
            $this->sendMessage(__FUNCTION__, array($zIndex), 1);
        }
    }

    function getZIndex() {
        return $this->zIndex;
    }    
    
    function focus() {
        $this->sendMessage(__FUNCTION__);
    }

    protected function setResizeable($resizeable) {
        $this->resizeable = $resizeable;
    }

    function getResizeable() {
        return $this->resizeable;
    }   

    /**
     * @param array|false $context See description of 'context' config property of YAHOO.Widget.Panel YUI API 
     * 
     * $context should be either FALSE or a numeric array with following items:
     * [contextElementOrId, overlayCorner, contextCorner, arrayOfTriggerEvents (optional)]
     * 
     * - overlayCorner and contextCorner should have one of values 'tl'|'tr'|'bl'|'br'
     * - contextElementOrId can be either a string or a Pwg_Base instance (it's container ID will be used)
     */
    function setContext($context) {
        if (true || ($context !== ($oldContext = $this->context))) { 
            // sometimes we have to re-align our control so it's better to generate outgoing message every time
            if ($this->context !== false && !is_array($this->context))
                throw new Exception("\$context should be either FALSE or array");
            $this->context = $context;
            $this->sendMessage(__FUNCTION__, array($this->jsGetContext()), 1);
        }
    }

    function getContext() {
        return $this->context;
    }    
    
    function clearContext() {
        $this->setContext(false);
    }
    
//  +---------------------- frontend event handlers -----------------------+

    function triggerFrontendClose() {
        $allowClose = true;
        if ($this->hideOnClose) {
            $this->visible = false;
            $this->focused = false;
        }
        $this->triggerEvent('close', array('allowClose' => & $allowClose));
        if ($this->hideOnClose) {
            if (!$allowClose) $this->setVisible(true);
        } else {
            if ($allowClose) $this->setVisible(false);
        }
    }
    
    function triggerFrontendResize($width, $height) {
        if (is_numeric($width) && is_numeric($height)) {
            $this->width = (int) $width;
            $this->height = (int) $height;
            $this->triggerEvent('resize', array('width' => $width, 'height' => $height));
        }
    }
    
    function triggerFrontendMove($x = false, $y = false) {
        if (is_numeric($x) && is_numeric($y)) {
            $this->x = (int) $x;
            $this->y = (int) $y;
            $this->triggerEvent('move', array('x' => $x, 'y' => $y));
        }
    }
    
    function triggerFrontendZIndexChange($zIndex) {
        if (is_numeric($zIndex)) {
            $this->zIndex = $zIndex;
            $this->triggerEvent('zIndexChange', array($zIndex));
        }
    }
    
    function triggerFrontendActivate() {
        $this->triggerEvent('focus');
    }
    
//  +---------------------- implementation methods ------------------+    
    
    protected function jsGetWidth() {
        if (!is_null($this->width)) return $this->width.'px';
            else return null;
    }
    
    protected function jsGetHeight() {
        if (!is_null($this->height)) return $this->height.'px';
            else return null;
    }
    
    protected function doGetConstructorName() {
        return 'Pwg_Yui_Panel';
    }
    
    protected function doGetAssetLibs() {
        $res = array(
            '{YUI}/fonts/fonts-min.css',
            '{YUI}/container/assets/skins/sam/container.css',
            '{YUI}/yahoo/yahoo.js',
            '{YUI}/dom/dom.js',
            '{YUI}/event/event.js',
            '{YUI}/dragdrop/dragdrop.js',
            '{YUI}/container/container.js',
            'widgets.js',
            'widgets/yui/panel.js',
        );
        if ($this->resizeable) $res = array_merge($res, array(
            '{YUI}/resize/assets/skins/sam/resize.css',
            '{YUI}/element/element.js',
            '{YUI}/resize/resize.js',
        ));
        return $res;
    }
    
    protected function doShowContainer() {
        $outerTagAttribs = array('id' => $this->getContainerId(), 'class' => $this->className, 'style' => 'display: none');
        $attribs = $this->getContainerAttribs();
        $innerTagName = $this->containerIsBlock? 'div' : 'span';
        
        echo '<div '.Ac_Util::mkAttribs($outerTagAttribs).'>';
        if (strlen($h = $this->jsGetHeader())) echo Ac_Util::mkElement('div', $h, array('class' => 'hd'));
        echo Ac_Util::mkElement('div', 
            Ac_Util::mkElement($innerTagName, $this->doGetContainerBody(), $attribs), 
        array('class' => 'bd'));
        if (strlen($f = $this->jsGetFooter())) echo Ac_Util::mkElement('div', $f, array('class' => 'ft'));
        echo "</div>";
    }
    
    protected function doListPassthroughParams() {
        return array_merge(parent::doListPassthroughParams(), array(
            'width', 
            'height', 
            'minWidth',
            'minHeight',
            'draggable', 
            'close',
            'modal', 
            'underlay', 
            'fixedCenter', 
            'header', 
            'footer', 
            'visible', 
            'zIndex', 
            'x', 
            'y', 
            'resizeable', 
            'hideOnClose',
            'context',
            'closeOnOutsideClick',
            'focused',
        	'autoSize',
            'showAtCenter',
        )); 
    }

    function setHideOnClose($hideOnClose) {
        if ($hideOnClose !== ($oldHideOnClose = $this->hideOnClose)) {
            $this->hideOnClose = $hideOnClose;
            $this->sendMessage(__FUNCTION__, array($hideOnClose), 1);
        }
    }

    function getHideOnClose() {
        return $this->hideOnClose;
    }    
    
    protected function jsGetContext() {
        if ($this->context === false) {
            $res = false;
        } elseif (is_array($this->context)) {
            $res = $this->context;
            if (isset($res[0]) && is_object($res[0])) {
                $base = $res[0];
                if ($base instanceof Pwg_Control_Path) {
                    $base = $this->getControlByPath($base->getPath());
                }
                if ($base instanceof Pwg_Base) $res[0] = $base->getContainerId();
            }
        }
        return $res;
    }
    
    function setCloseOnOutsideClick($closeOnOutsideClick) {
        if ($closeOnOutsideClick !== ($oldCloseOnOutsideClick = $this->closeOnOutsideClick)) {
            $this->closeOnOutsideClick = $closeOnOutsideClick;
            $this->sendMessage(__FUNCTION__, array($closeOnOutsideClick), 1);
        }
    }

    function getCloseOnOutsideClick() {
        return $this->closeOnOutsideClick;
    }

    function setFocused($focused) {
        if ($focused !== ($oldFocused = $this->focused)) {
            $this->focused = $focused;
            $this->sendMessage(__FUNCTION__, array($this->focused), 1);
        }
    }

    function getFocused() {
        return $this->visible && $this->focused;
    }

    function setAutoSize($autoSize) {
        if ($autoSize !== ($oldAutoSize = $this->autoSize)) {
            $this->autoSize = $autoSize;
            $this->sendMessage(__FUNCTION__, array($autoSize), 1);
        }
    }
    
    function applyAutoSize() {
        $this->sendMessage(__FUNCTION__, array(), 1);
    }

    function getAutoSize() {
    	if ($this->autoSize === 'auto') return !$this->width || !$this->height;
        else return $this->autoSize;
    }

    protected function setShowAtCenter($showAtCenter) {
        $this->showAtCenter = $showAtCenter;
    }

    function getShowAtCenter() {
        return $this->showAtCenter;
    }    
    
    function center() {
        $this->sendMessage(__FUNCTION__);
    }
    
    
}

?>