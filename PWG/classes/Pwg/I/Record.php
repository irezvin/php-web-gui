<?php

interface Pwg_I_Record extends Pwg_I_Observable {

    function getUid();
    
    function listFields();
    
    function getField($fieldName);
    
    function getData();
    
    function setField($fieldName, $fieldValue);
    
    function updateData(array $data);
    
    /**
     * @return Pwg_I_Record_Field 
     */
    function getFieldInfo($fieldName);
    
    function getErrors();
    
    function matches(Pwg_I_Record $otherRecord);
    
}

?>