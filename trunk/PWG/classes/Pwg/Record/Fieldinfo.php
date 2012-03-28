<?php

class Pwg_Record_Fieldinfo implements Pwg_I_Record_Fieldinfo {
    
    public $name = false;
    
    public $caption = false;
    
    function __construct($options = array()) {
        foreach (array_keys($options) as $k => $v) {
            $this->$k = $v;
        }
    }
    
    function getName() {
        return $this->name;
    }
    
    function getCaption() {
        return $this->caption !== false? $this->caption : $this->name;
    }
    
}

?>