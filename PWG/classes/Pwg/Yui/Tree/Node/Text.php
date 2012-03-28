<?php

class Pwg_Yui_Tree_Node_Text extends Pwg_Yui_Tree_Node {

    protected $label = false;

    protected $labelStyle = false;

    protected $href = false;

    protected $title = false;

    protected $target = false;

    function setLabel($label) {
        if ($label !== ($oldLabel = $this->label)) {
            $this->label = $label;
            $this->sendProperty('label', $label);
        }
    }

    function getLabel() {
        return $this->label;
    }

    function setLabelStyle($labelStyle) {
        if ($labelStyle !== ($oldLabelStyle = $this->labelStyle)) {
            $this->labelStyle = $labelStyle;
            $this->sendProperty('labelStyle', $labelStyle);
        }
    }

    function getLabelStyle() {
        return $this->labelStyle;
    }

    function setHref($href) {
        if ($href !== ($oldHref = $this->href)) {
            $this->href = $href;
            $this->sendProperty('href', $href);
        }
    }

    function getHref() {
        return $this->href;
    }

    function setTitle($title) {
        if ($title !== ($oldTitle = $this->title)) {
            $this->title = $title;
            $this->sendProperty('title', $title);
        }
    }

    function getTitle() {
        return $this->title;
    }

    function setTarget($target) {
        if ($target !== ($oldTarget = $this->target)) {
            $this->target = $target;
            $this->sendProperty('target', $target);
        }
    }

    function getTarget() {
        return $this->target;
    }	
	
    protected function getJsNodeType() {
    	return new Ae_Js_Var('YAHOO.widget.TextNode');
    }
    
    protected function getJsConstructorArgs() {
    	$res = parent::getJsConstructorArgs();
    	foreach (array('label', 'labelStyle', 'href', 'title', 'target') as $p)
    		if (strlen($this->$p)) $res[$p] = $this->$p;
    	return $res;
    }
	
}