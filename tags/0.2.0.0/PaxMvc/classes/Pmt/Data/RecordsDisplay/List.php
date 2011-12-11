<?php

class Pmt_Data_RecordsDisplay_List extends Pmt_List implements Pmt_I_Control_RecordsDisplay {

    protected $optionClass = 'Pmt_Data_RecordsDisplay_List_Option';
    
    protected $displayField = 'title';
    
    protected $displayFormat = false;
    
    protected $inSelection = 0;
    
    /**
     * @param string $label
     * @param string $value
     * @param int $index
     * @return Pmt_Data_RecordsDisplay_List_Option
     */
    function addOption($label = false, $value = false, $index = false) {
        return parent::addOption($label, $value, $index);
    }

    function setDisplayField($v) {
        $ov = $this->displayField;
        $this->displayField = $v;
        if ($ov !== $v) $this->redrawOptions();
    }
    
    function getDisplayField() {
        return $this->displayField;
    }

    function setDisplayFormat($v) {
        $ov = $this->displayFormat;
        $this->displayFormat = $v;
        if ($ov !== $v) $this->redrawOptions();
    }
    
    function getDisplayFormat() {
        return $this->displayFormat;
    }
    
    /**
     * @return Pmt_Data_RecordsDisplay_List_Option
     */
    function locateOptionByRecord(Ae_Model_Object $record, $multiple = false) {
        $key = $record->getPrimaryKey();
        $found = array();
        foreach ($this->options as $opt) {
            if ($opt instanceof Pmt_Data_RecordsDisplay_List_Option && ($r = $opt->getRecord())) {
                if ($r->matchesPk($key)) {
                    $found[] = $opt;
                    if (!$multiple) break;
                }
            }
        }
        if ($multiple) {
            $res = $found;
        } else {
            if (count($found)) {
                $res = $found[0]; 
            } else {
                $res = false;
            }
        }
        return $res;
    }
    
//  Pmt_I_Control_RecordsDisplay    
    
    function setRecordPrototype(Ae_Model_Object $record = null) {
    	// prototype isn't used by this control.
    }
    
    function addRecord(Ae_Model_Object $record, $newIndex = false) {
        $opt = $this->addOption(false, false, $newIndex);
        $opt->setRecord($record);
    }
    
    function deleteRecord(Ae_Model_Object $record) {
        if (($opt = $this->locateOptionByRecord($record)) && (($k = $this->getOptionKey($opt)) !== false) ) $this->removeOption($k);
    }
    
    function updateRecord(Ae_Model_Object $record, $newIndex = false) {
        $o = $this->locateOptionByRecord($record, true);
        if ($o) {
            foreach ($o as $opt) {
                $opt->setRecord($record);
                if ($newIndex !== false) $opt->setIndex($newIndex);
            }
            $this->redrawOptions($o);
        }
    }

    function setRecords(array $records = array()) {
        $this->setOptions();
        foreach ($records as $rec) $this->addRecord($rec);
    }

    function setCurrentRecord(Ae_Model_Object $record = null) {
        if ($record && $opt = $this->locateOptionByRecord($record)) {
            $opt->setSelected(true);
        } else {
            $this->setSelectedOptionIndices(array());
        }
    }
    
    function setRecordErrors(Ae_Model_Object $record, array $errors = array()) {
        // skip it...
    }
    
    function getCurrentRecord() {
        $res = false;
        if ($this->selectedOptions) {
            $f = array_slice($this->selectedOptions, 0, 1, false);
            $res = $f[0]->getRecord();
        }
        return $res;
        //return $this->currentRecord;
    }
    
    function getRecordIndex(Ae_Model_Object $record) {
        $res = false;
        $pk = $record->getPrimaryKey();
        foreach ($this->listOptions() as $i) {
            $opt = $this->getOption($i);
            if (($r = $opt->getRecord()) && ($r->getPrimaryKey() == $pk)) {
                $res = $i;
                break;
            }
        }
        return $res;
    }
    
    function cancelCurrentAction() {
        // skip it...
    }
    
    function setCurrentCaps($canMove = null, $canNew = null, $canEdit = null, $canSave = null, $canCancel = null, $canDelete = null) {
        // skip it...
    }
    
    function getCurrentCaps() {
        return array();
    }
    
    function observeRecordSelected (Pm_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array()) {
        return $this->observe('onRecordSelected', $observer, $methodName, $extraParams);
    }

    function unobserveRecordSelected (Pm_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array()) {
        return $this->unobserve('onRecordSelected', $observer, $methodName, $extraParams);
    }
    
    function observeRecordEdited (Pm_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array()) {
        return $this->observe('onRecordEdited', $observer, $methodName, $extraParams);
    }
    
    function unobserveRecordEdited (Pm_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array()) {
        return $this->unobserve('onRecordEdited', $observer, $methodName, $extraParams);
    }
    
    function observeRecordCreated (Pm_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array()) {
        return $this->observe('onRecordCreated', $observer, $methodName, $extraParams);
    }
    
    function unobserveRecordCreated (Pm_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array()) {
        return $this->unobserve('onRecordCreated', $observer, $methodName, $extraParams);
    }

    function observeRecordRemoved (Pm_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array()) {
        return $this->observe('onRecordRemoved', $observer, $methodName, $extraParams);
    }
    
    function unobserveRecordRemoved (Pm_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array()) {
        return $this->unobserve('onRecordRemoved', $observer, $methodName, $extraParams);
    }
    
    protected function doOnOptionSelected(Pmt_List_Option $option) {
        if ($this->inSelection <= 0) {
            $this->inSelection++;
            $this->triggerEvent('onRecordSelected');
            $this->inSelection--;
        }
    }
    
    protected function doOnGetInitializer(Pm_Js_Initializer $i) {
        parent::doOnGetInitializer($i);
        $i->constructorName = 'Pmt_List';
    }
    
}

?>