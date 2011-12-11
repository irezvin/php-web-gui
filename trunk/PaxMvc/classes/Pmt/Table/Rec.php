<?php

class Pmt_Table_Rec extends Pmt_Base {

    /**
     * @var Pmt_I_Record
     */
    protected $record = false;
    
    function setRecord(Pmt_I_Record $record) {
        $this->record = $record;
        // TODO: replace record in the browser when it is replaced here
    }
    
    /**
     * @return Pmt_I_Record
     */
    function getRecord() {
        return $this->record;
    }
    
    /**
     * @return Pmt_Table_Recset
     */
    function getRowset() {
        return $this->parent;
    }
    
    /**
     * @return Pmt_Table
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