<?php

class Pmt_Table_Row implements Pm_I_RefControl {

    /**
     * @var Pmt_Table
     */
    protected $table = false;
    
    /**
     * @var Pmt_I_Record
     */
    protected $record = false;
    
    protected $index = false;
    
    function toJs() {
        $res = array();
        if (($rec = $this->record) && $this->table) {
            $res = array('__aeUid' => $rec->getUid(), '__trClass' => 'foo');
            foreach ($this->table->getShownFieldsList() as $colId => $f) {
                $res[$colId] = $rec->getField($f);
            }
            $this->table->triggerFormatRow($this, $rec, $res);
        }
        return $res;
    }
    
    function __construct(Pmt_Table $table, Pmt_I_Record $record = null) {
        $this->table = $table;
        $this->refAdd($table);
        if ($record !== false) {
            $this->record = $record;
        }
    }
    
    function setRecord(Pmt_I_Record $record) {
        $this->record = $record;
        if ($this->table) $this->table->notifyRowUpdated($this);
        $this->table->redrawRows(array($this));
    }
    
    /**
     * @return Pmt_I_Record
     */
    function getRecord() {
        return $this->record;
    }
    
    function getIndex() {
        if ($this->index === false) {
            $res = false;
            if ($this->table) $res = $this->table->getRowIndex($this); 
        } else {
            $res = $this->index;
        }
        return $res;
    }
    
    function setIndex($index) {
        $oi = $this->table->getRowIndex($this);
        if ($oi != $index) {
            $this->table->setMoveRow($this, $index);
        }
    }
    
    function setSelected($selected) {
        if ($this->table) {
            if ($selected) {
                $this->table->selectRow($this);
            } else {
                $this->table->deselectRow($this);
            }
        }
    }
    
    function getSelected() {
        $res = false;
        if ($this->table) {
            $res = $this->table->isRowSelected($this);
        }
        return $res;
    }
    
    function redraw() {
        return $this->table->redrawRows(array($this));
    }
    
//  +-------------- Pm_I_Refcontrol implementation ---------------+

    protected $refReg = array();

    protected function refGetSelfVars() {
        $res = array();
        foreach (array_keys(get_object_vars($this)) as $v) $res[$v] = & $this->$v;
        return $res;
    }
    
    function refHas($otherObject) { return Pm_Impl_Refcontrol::refHas($otherObject, $this->refReg); }
    
    function refAdd($otherObject) { return Pm_Impl_Refcontrol::refAdd($this, $otherObject, $this->refReg); }
    
    function refRemove($otherObject, $nonSymmetrical = false) { $v = $this->refGetSelfVars(); return Pm_Impl_Refcontrol::refRemove($this, $otherObject, $v, false, $nonSymmetrical); }

    function refNotifyDestroying() { return Pm_Impl_Refcontrol::refNotifyDestroying($this, $this->refReg); }

//  +-------------------------------------------------------------+ 
    
    function destroy() {
        $this->refNotifyDestroying();
    }
    
}

?>