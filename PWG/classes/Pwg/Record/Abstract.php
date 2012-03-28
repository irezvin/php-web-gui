<?php

abstract class Pwg_Record_Abstract implements Pwg_I_Record {

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
            Pwg_Impl_Observable::triggerEvent($this, $this->observers, 'change', $diff);
        }
    }
    
    protected abstract function doUpdateData(array $data);
    
    function __set($name, $value) {
        $this->setField($name, $value); 
    }
    
    function __get($name) {
        return $this->getField($name);
    }
    
//  Pwg_I_Observable 
    
    function observe($eventType, Pwg_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array()) {
        return Pwg_Impl_Observable::observe($this->observers, $eventType, $observer, $methodName, $extraParams);
    }
    
    function unobserve($eventType, Pwg_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array()) {
        return Pwg_Impl_Observable::unobserve($this->observers, $eventType, $observer, $methodName, $extraParams);    
    }

    function matches(Pwg_I_Record $otherRecord) {
        return $this === $otherRecord || $this->getData() == $otherRecord->getData();
    }
    
}

?>