<?php

class Pwg_Table_Rec extends Pwg_Base {

    /**
     * @var Pwg_I_Record
     */
    protected $record = false;
    
    function setRecord(Pwg_I_Record $record) {
        $this->record = $record;
        // TODO: replace record in the browser when it is replaced here
    }
    
    /**
     * @return Pwg_I_Record
     */
    function getRecord() {
        return $this->record;
    }
    
    /**
     * @return Pwg_Table_Recset
     */
    function getRowset() {
        return $this->parent;
    }
    
    /**
     * @return Pwg_Table
     */
    function getTable() {
        $res = false;
        $rowset = $this->getRowset();
        if ($rowset !== false) $res = $rowset->getTable();
        return $res;
    }
    
    function hasJsObject() {
        return false;
    }
    
    function hasContainer() {
        return false;
    }

    function toJs() {
        return $this->record->getData(); 
    }

    function setSelected($selectedStatus) {
    }

}

?>