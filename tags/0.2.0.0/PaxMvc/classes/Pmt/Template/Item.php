<?php

class Pmt_Template_Item {

    protected static $templateItems = false;
    
    /**
     * @var Pmt_I_Control_DisplayParent
     */
    protected $display = false;
    
    var $text = false;

    function setDisplay($display) {
        $this->display = $display;
    }

    function getDisplay() {
        return $this->display;
    }   var $dataSource = false;
    
    protected function __construct($text, Pmt_I_Control_DisplayParent $display = null) {
        $this->text = $text;
        $this->dataSource = $dataSource;  
    }
    
    /**
     * @param unknown_type $snippet
     */
    static function factory($text, Pmt_I_Control_DisplayParent $display = null) {
        
    }
    
    static function getPattern() {
        
    }
    
    static function getClass($text) {
        
    }
    
    static function check($text) {
        return strlen($text) && substr($text, 0, 1) != '{' && substr($text, -1) != '}';
    }
    
}

?>