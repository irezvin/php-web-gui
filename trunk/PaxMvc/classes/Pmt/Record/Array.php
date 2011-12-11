<?php

class Pmt_Record_Array extends Pmt_Record_Abstract {

    protected $data = array();
    
    function __construct(array $data = array()) {
        $this->data = $data;
    }
    
    function listFields() {
        return array_keys($data);
    }
    
    function getField($fieldName) {
        return isset($this->data[$fieldName])? $this->data[$fieldName] : null;
    }
    
    function getData() {
        return $this->data;
    }
    
    protected function doUpdateData(array $data) {
        Ae_Util::ms($this->data, $data);
    }
    
    /**
     * @return Pmt_I_Record_Field 
     */
    function getFieldInfo($fieldName) {
        return new Pmt_Record_Fieldinfo(array('name' => $fieldName));
    }
    
    function getErrors() {
        return array();
    }
    
}

?>