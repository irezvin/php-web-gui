<?php

class Pwg_Data_RecordsDisplay_List_Option extends Pwg_List_Option {
    
    /**
     * @var Ac_Model_Object
     */
    protected $record = false;
    
    /**
     * @var Pwg_Data_RecordsDisplay_List
     */
    protected $list = false;
    
    function setRecord(Ac_Model_Object $record) {
        $this->record = $record;
    }
    
    /**
     * @return Ac_Model_Object
     */
    function getRecord() {
        return $this->record;       
    }

    function getLabel() {
        $res = false;
        if ($this->label === false) {
            if ($this->record) {
                $df = $this->list->getDisplayField();
                if (!is_array($df)) {
                    $df = array($df);
                }
                foreach ($df as $d => $f) $df[$d] = $this->record->getField($f);
                $fmt = $this->list->getDisplayFormat();
                if ($fmt !== false) {
                    $res = call_user_func_array('sprintf', array_merge(array($fmt), $df));
                } else {
                    $res = implode(", ", $df); 
                }
            }
        } else {
            $res = $this->label;
        }
        return $res;
    }
    
}

?>