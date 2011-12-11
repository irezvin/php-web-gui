<?php

abstract class Pmt_Record_Abstract implements Pmt_I_Record {

    protected $uid = false;
    
    protected $observers = array();

    function getUid() {
        if ($this->uid === false) $this->uid = md5(microtime().rand());
        return $this->uid;
    }
    
    final function setField($name, $value) {
        $this->updateData(array($name => $value));
    }
    
    protected function getDifferences($data) {
        $res = array();
        foreach (array_keys($data) as $field) {
            if (($val = $this->getField($field)) !== $data[$field]) $res[$field] = array('field' => $field, 'oldValue' => $val, 'newValue' => $data[$field]);
        }
        return $res;
    }
    
    final function updateData(array $data) {
        $diff = $this->getDifferences($data);
        if ($diff) {
            $this->doUpdateData($data);
            Pmt_Impl_Observable::triggerEvent($this, $this->observers, 'change', $diff);
        }
    }
    
    protected abstract function doUpdateData(array $data);
    
    function __set($name, $value) {
        $this->setField($name, $value); 
    }
    
    function __get($name) {
        return $this->getField($name);
    }
    
//  Pm_I_Observable 
    
    function observe($eventType, Pm_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array()) {
        return Pmt_Impl_Observable::observe($this->observers, $eventType, $observer, $methodName, $extraParams);
    }
    
    function unobserve($eventType, Pm_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array()) {
        return Pmt_Impl_Observable::unobserve($this->observers, $eventType, $observer, $methodName, $extraParams);    
    }

    function matches(Pmt_I_Record $otherRecord) {
        return $this === $otherRecord || $this->getData() == $otherRecord->getData();
    }
    
}

?>