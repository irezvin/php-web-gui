<?php

interface Pmt_I_Record extends Pm_I_Observable {

    function getUid();
    
    function listFields();
    
    function getField($fieldName);
    
    function getData();
    
    function setField($fieldName, $fieldValue);
    
    function updateData(array $data);
    
    /**
     * @return Pmt_I_Record_Field 
     */
    function getFieldInfo($fieldName);
    
    function getErrors();
    
    function matches(Pmt_I_Record $otherRecord);
    
}

?>