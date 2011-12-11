<?php

class Pmt_Table_Editor extends Pmt_Base {
    
    /**
     * @var Pmt_Table_Column
     */
    protected $column = false;
    
    protected $jsEditorClass = 'YAHOO.widget.TextboxCellEditor';

    protected $jsEditorOptions = array();
    
    protected function setColumn(Pmt_Table_Column $column) {
        $this->column = $column;
    }
    
    /**
     * @return Pmt_Table_Column
     */
    function getColumn() {
        return $this->column;
    }
    
    function toJs() {
        return new Ae_Js_Call($this->jsEditorClass, array($this->jsEditorOptions), true);
    }

    protected function setJsEditorOptions($jsEditorOptions) {
        $this->jsEditorOptions = $jsEditorOptions;
    }

    function getJsEditorOptions() {
        return $this->jsEditorOptions;
    }   
    
    protected function setJsEditorClass($jsEditorClass) {
        $this->jsEditorClass = $jsEditorClass;
    }

    function getJsEditorClass() {
        return $this->jsEditorClass;
    }
        
}

?>