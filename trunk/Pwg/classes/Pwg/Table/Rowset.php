<?php

class Pmt_Table_Recset extends Pmt_Composite_Display {

    protected $allowedDisplayChildrenClass = 'Pmt_Table_Rec';
    
    protected $allowedChildrenClass = 'Pmt_Table_Rec';
    
    /**
     * @return Pmt_Table
     */
    function getTable() {
        return $this->parent;
    }
    
    function hasJsObject() {
        return false;
    }
    
    function hasContainer() {
        return false;
    }
    
    /**
     * @param string $id
     * @return Pmt_Table_Rec
     */
    function getControl($id) {
        return parent::getControl($id);
    }

    /**
     * Adds new row that displays provided record
     *
     * @param Pmt_I_Record|null $record If null is given, $this->getTable()->createRecord() will be used to create blank record
     * @param int|false $displayOrder
     * @param array $rowOptions Extra options of Pmt_Table_Rec object
     */
    function addRecordRow(Pmt_I_Record $record = null, $displayOrder = false, array $rowOptions = array()) {
        if ($record  === null) $record = $this->getTable()->createRecord();
        $rowOptions = array('record' => $record);
        $res = $this->addControl(Pmt_Base::factory($rowOptions, 'Pmt_Table_Rec'));
        if ($displayOrder !== false) $res->setDisplayOrder($displayOrder);
        Pm_Conversation::log("Record added to the rowset:", $record->getData());
        return $res;
    }
    
    /**
     * Finds rows that display given record
     * @param Pmt_I_Record $record
     * @return array Pmt_Table_Rec instances
     */
    function locateRowsByRecord(Pmt_I_Record $record) {
        $res = array();
        foreach ($this->listControls() as $i) {
            $row = $this->getControl($i);
            if ($record->matches($row->getRecord())) $res[] = $row; 
        }
        return $res;
    }
    
    /**
     * @param Pmt_I_Record $record
     * @return Pmt_Table_Rec
     */
    function locateRowByRecord(Pmt_I_Record $record) {
        $rows = $this->locateRowsByRecord($record);
        if (count($rows)) $res = $rows[0];
            else $res = false;
        return $res;
    }
    
    function setRecordRows(array $records) {
        foreach ($this->listControls() as $i) {
            $this->removeDisplayChild($this->getControl($i));
            $this->getControl($i)->destroy();
        }
        foreach ($records as $record) {
            $this->addRecordRow($record);
        }
    }
    
    function getRowsByRecordIds(array $recordIds) {
        $res = array();
        foreach ($this->listControls() as $i) {
            $rw = $this->getControl($i);
            if (in_array($rw->getRecord()->getUid(), $recordIds)) $res[] = $rw;
        }
        return $res;
    }
    
}

?>