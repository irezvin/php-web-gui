<?php

class Pwg_Data_Source_Abstract extends Pwg_Base {
	
    const HOLD_NUMBER = 0;
    const HOLD_KEY = 1;
	
    
    /**
     * @var Ae_Model_Data
     */
    protected $currentRecord = false;
    
    protected $currentKey = false;
    
    protected $recordNo = false;
    
    protected $dirty = false;
    
    protected $isNew = false;
    
    /**
     * A filter and a prototype combined.
     * @var array(fieldName => fieldValue);
     */
    protected $restrictions = array();
    
	
}