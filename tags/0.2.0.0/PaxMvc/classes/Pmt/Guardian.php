<?php

class Pmt_Guardian {

    protected $lastSignId = 0;
    
    protected $signs = array();
    
    protected $prefix = '1234GuardianSign';
    
    protected function __construct() {
    }
    
    /**
     * @return Pmt_Guardian
     */
    function getInstance() {
        if (!isset($GLOBALS['_Pmt_Guardian']) || ! ($GLOBALS['_Pmt_Guardian'] instanceof Pmt_Guardian)) 
            $GLOBALS['_Pmt_Guardian'] = new Pmt_Guardian;
        return $GLOBALS['_Pmt_Guardian'];
    }
    
    function setInstance(Pmt_Guardian $instance) {
        $GLOBALS['_Pmt_Guardian'] = & $instance;        
    }
    
    function signId2Sign($id) {
        return $this->prefix.$id;
    }
    
    function getNextSignId() {
        return $this->lastSignId++;
    }
    
    function shouldBeDeleted($obj, $signId = false) {
        if (!is_object($obj)) throw new Exception("\$obj should be an object");
        if (!isset($obj->_guardianSign)) {
            if ($signId === false)
                $signId = $this->getNextSignId();
            $obj->_guardianSign = $this->signId2Sign($signId);
        }
        return $obj;
    }
    
    function findSigns($strData) {
        $res = array();
        foreach ($this->signs as $sign) if (strpos($strData, $this->signId2Sign($sign)) !== false) $res[] = $sign;
        return $res;
    }
    
    function assertForSigns($strData) {
        if ($s = $this->findSigns($strData)) {
            throw new Exception ("Following one or more objects should be deleted, but they still are in serialized data: ".implode(", ", $s));
        }
    }
    
}

?>