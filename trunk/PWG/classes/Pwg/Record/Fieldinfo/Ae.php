<?php

class Pwg_Record_Fieldinfo_Ae implements Pwg_I_Record_Fieldinfo {
    
    /**
     * @var Ac_Model_Property
     */
    var $aeModelProperty = false;
    
    function __construct(Ac_Model_Property $aeModelProperty) {
        $this->aeModelProperty = $aeModelProperty;
    }
    
    function getName() {
        return $this->aeModelProperty->propName;
    }
    
    function getCaption() {
        return !is_null($this->aeModelProperty->caption)? $this->aeModelProperty->caption : $this->aeModelProperty->propName;
    }
    
}

?>