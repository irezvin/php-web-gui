<?php

class Pmt_Yui_Tree_Node_Html extends Pmt_Yui_Tree_Node {

	protected $html = false;
	
    protected $contentStyle = false;
	
    function setHtml($text) {
        if ($html !== ($oldHtml = $this->html)) {
            $this->html = $html;
            $this->sendProperty('html', $html);
        }
    }

    function getHtml() {
        return $this->html;
    }
    

    function setContentStyle($contentStyle) {
        if ($contentStyle !== ($oldContentStyle = $this->contentStyle)) {
            $this->contentStyle = $contentStyle;
            $this->sendProperty('contentStyle', $contentStyle);
        }
    }

    function getContentStyle() {
        return $this->contentStyle;
    }	
    
    
    protected function getJsNodeType() {
    	return new Ae_Js_Var('YAHOO.widget.HTMLNode');
    }
    
    protected function getJsConstructorArgs() {
    	$res = parent::getJsConstructorArgs();
    	foreach (array('html', 'contentStyle') as $p)
    		if (strlen($this->$p)) $res[$p] = $this->$p;
    	return $res;
    }
	
}