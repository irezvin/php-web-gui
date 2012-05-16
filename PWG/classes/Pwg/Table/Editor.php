<?php

class Pwg_Table_Editor extends Pwg_Base {
    
    /**
     * @var Pwg_Table_Column
     */
    protected $column = false;
    
    protected $jsEditorClass = 'YAHOO.widget.TextboxCellEditor';

    protected $jsEditorOptions = array();
    
    protected function setColumn(Pwg_Table_Column $column) {
        $this->column = $column;
    }
    
    /**
     * @return Pwg_Table_Column
     */
    function getColumn() {
        return $this->column;
    }
    
    function toJs() {
        return new Ac_Js_Call($this->jsEditorClass, array($this->jsEditorOptions), true);
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