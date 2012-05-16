<?php

class Pwg_Element extends Pwg_Base implements Pwg_I_Observer {

    protected $containerDisplay = true;
    
    protected $visible = true;
    
    protected $attribs = array();
    
    protected $style = array();
    
    protected $className = false;

    /**
     * Inner elements are elements between container and an actual widget body, nested within each other.
     *  
     * $innerContainers can specify their number, tag names and attributes.
     * Number means N elements without attributes with same tag name as a container. (So if containerIsBlock set to true, they will be spans; otherwise div's will be used) 
     * Array can provide more details on inner elements.
     * - boolean true means 'same tag as container without any attributes' 
     * - string item means class name;
     * - array item means attributes.
     * - '_tagName' means tag name (by default tag name is the same as container tag.  
     * Example: 
     * <code>
     * 			$foo->setInnerContainers(3); // <div id='fooContainer'><div><div><div>...</div></div><div></div>
     * 			$bar->setInnerContainers('corners1', 'corners2', 'background'); // <div id='barContainer'><div class='corners1'><div class=''corners2'><div class='background'>...</div></div></div></div>
     * 			$baz->setInnerContainers(true, array('style' => 'border: 1px solid black'), array('_tagName' => 'span')); // <div id='bazContainer'><div><div style='border: 1px solid black'><span>...</span></div></div></div> 
     * </code>
     * 
     * @var int|array
     */
    protected $innerContainers = 0;
    
    function triggerFrontendMouseup() {
        $evt = substr(__FUNCTION__, 15); $evt{0} = strtolower($evt{0}); 
        $this->triggerEvent($evt);
    }
    
    function triggerFrontendMousedown() {
        $evt = substr(__FUNCTION__, 15); $evt{0} = strtolower($evt{0}); 
        $this->triggerEvent($evt);
    }
    
    function triggerFrontendClick() {
        $evt = substr(__FUNCTION__, 15); $evt{0} = strtolower($evt{0}); 
        $this->triggerEvent($evt);
    }
    
    function triggerFrontendDblclick() {
        $evt = substr(__FUNCTION__, 15); $evt{0} = strtolower($evt{0}); 
        $this->triggerEvent($evt);
    }
    
    function triggerFrontendMouseover() {
        $evt = substr(__FUNCTION__, 15); $evt{0} = strtolower($evt{0}); 
        $this->triggerEvent($evt);
    }
    
    function triggerFrontendMouseout() {
        $evt = substr(__FUNCTION__, 15); $evt{0} = strtolower($evt{0}); 
        $this->triggerEvent($evt);
    }
    
    function setVisible($value) {
        $value = (bool) $value;
        $prop = substr(__FUNCTION__, 3); $prop{0} = strtolower($prop{0}); 
        /*
        if (!is_null($value)) $this->$prop = $value;
        $this->sendMessage(__FUNCTION__, array($this->$prop));
        */
        $oldValue = $this->{$prop}; if ($value !== $this->$prop) {
            $this->$prop = $value; $this->sendMessage(__FUNCTION__, array($this->$prop));
        }
    }
    
    function getVisible() {
        $prop = substr(__FUNCTION__, 3); $prop{0} = strtolower($prop{0});
        return $this->$prop;
    }
    
    function setAttribs($value = null) {
    
        $prop = substr(__FUNCTION__, 3); $prop{0} = strtolower($prop{0}); 
        /*
        if (!is_null($value)) $this->$prop = $value;
        $this->sendMessage(__FUNCTION__, array($this->$prop));
        */
        if (is_null($value)) $value = $this->$prop; $oldValue = $this->{$prop}; if ($value !== $this->$prop) {
            $this->$prop = $value; $this->sendMessage(__FUNCTION__, array($this->$prop));
        }
    }
            
    function getAttribs() {
        $prop = substr(__FUNCTION__, 3); $prop{0} = strtolower($prop{0});
        return $this->$prop;
    }
    
    function setStyle($value = null) {
    
        $prop = substr(__FUNCTION__, 3); $prop{0} = strtolower($prop{0}); 
        /*
        if (!is_null($value)) $this->$prop = $value;
        $this->sendMessage(__FUNCTION__, array($this->$prop));
        */
        if (is_null($value)) $value = $this->$prop; $oldValue = $this->{$prop}; if ($value !== $this->$prop) {
            $this->$prop = $value; $this->sendMessage(__FUNCTION__, array($this->$prop));
        }
    }
            
    function getStyle() {
        $prop = substr(__FUNCTION__, 3); $prop{0} = strtolower($prop{0});
        return $this->$prop;
    }
    
    function setClassName($value = null) {
    
        $prop = substr(__FUNCTION__, 3); $prop{0} = strtolower($prop{0}); 
        /*
        if (!is_null($value)) $this->$prop = $value;
        $this->sendMessage(__FUNCTION__, array($this->$prop));
        */
        if (is_null($value)) $value = $this->$prop; $oldValue = $this->{$prop}; if ($value !== $this->$prop) {
            $this->$prop = $value; $this->sendMessage(__FUNCTION__, array($this->$prop));
        }
    }
            
    function getClassName() {
        $prop = substr(__FUNCTION__, 3); $prop{0} = strtolower($prop{0});
        return $this->$prop;
    }
    
//  Template methods of Pwg_Base

    protected function doListPassthroughParams() {
        return array_merge(parent::doListPassthroughParams(), array('visible', 'attribs', 'style', 'className', 'nInnerContainers')); 
    }
    
    protected function jsGetNInnerContainers() {
        if (is_array($this->innerContainers)) $res = count($this->innerContainers);
            else $res = (int) $this->innerContainers;
        return $res; 
    }
    
    protected function doGetAssetLibs() {
        return array('widgets.js');
    }

    protected function setInnerContainers($innerContainers) {
        if (!is_int($innerContainers) && !is_array($innerContainers))
            throw new Exception("\$innerContainers should be either an int or an array");
            
        if ($innerContainers !== ($oldInnerContainers = $this->innerContainers)) {
            $this->innerContainers = $innerContainers;
        }
    }

    function getInnerContainers() {
        return $this->innerContainers;
    }    
    
    protected function renderInnerContainers($innerBody) {
        if (!$this->innerContainers) $res = $innerBody;
        else {
            $defaultTagName = $this->containerIsBlock? 'div' : 'span';
            if (is_array($this->innerContainers)) $a = $this->innerContainers;
                else $a = array_fill(0, $this->innerContainers - 1, true);
            $res = $innerBody;
            foreach (array_reverse($a) as $e) {
                $t = $defaultTagName;
                if ($e === true) { $attribs = array(); }
                elseif (is_string($e)) { $attribs = array('class' => $e); }
                elseif (is_array($e)) { $attribs = $e; if (isset($e['_tagName'])) { $t = $e['_tagName']; unset($attribs['tagName']); } }
                $res = Ac_Util::mkElement($t, $res, $attribs);
            }
        }
        return $res; 
    }
    
    protected function doShowContainer() {
        $tagName = $this->containerIsBlock? 'div' : 'span';
        $attribs = $this->getContainerAttribs();
        $attribs['id'] = $this->getContainerId();
        
        echo Ac_Util::mkElement($tagName, $this->renderInnerContainers($this->doGetContainerBody()), $attribs);
    }
    
    function scrollIntoView() {
        $this->sendMessage(__FUNCTION__);
    }
    
}

?>