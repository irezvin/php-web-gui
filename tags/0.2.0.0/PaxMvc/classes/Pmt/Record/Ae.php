<?php

class Pmt_Record_Ae extends Pmt_Record_Abstract {

    /**
     * @var Ae_Model_Data
     */
    protected $aeModelData = false;
    
    function __construct(Ae_Model_Data $aeModelData) {
        $this->aeModelData = $aeModelData;
    }
    
    /**
     * @return Ae_Model_Data
     */
    function getAeModelData() {
        return $this->aeModelData;
    }
    
    function listFields() {
        return $this->aeModelData->listOwnFields();
    }
    
    function getField($fieldName) {
        return $this->aeModelData->getField($fieldName);
    }
    
    function getData() {
        $res = array();
        foreach ($this->aeModelData->listOwnFields() as $f) $res[$f] = $this->aeModelData->getField($f);
        return $res;
    }
    
    protected function doUpdateData(array $data) {
        return $this->aeModelData->bind($data);
    }
    
    function getFieldInfo($fieldName) {
        return new Pmt_Record_Fieldinfo_Ae($this->aeModelData->getPropertyInfo($fieldName));
    }
    
    function getErrors() {
        return $this->aeModelData->getErrors(false, false);
    }
    
    function matches(Pmt_I_Record $otherRecord) {
        $res = null;
        if ($otherRecord === $this) $res = true; else {
            if ($otherRecord instanceof Pmt_Record_Ae) {
                $otherData = $otherRecord->getAeModelData();
                if ($this->aeModelData === $otherData) $res = true;
                elseif ($this->aeModelData instanceof Ae_Model_Object && $otherData instanceof Ae_Model_Object) {
                    if ($this->aeModelData->hasFullPrimaryKey() && $otherData->hasFullPrimaryKey()) {
                        $res = $this->aeModelData->matchesPk($otherData->getPrimaryKey());
                    }
                }
            }
        }
        if (is_null($res)) $res = parent::matches($otherRecord);
        return $res;
    }
    
//  function getUid() {
//      if ($this->uid === false) {
//          if ($this->aeModelData instanceof Ae_Model_Object && $this->aeModelData->hasFullPrimaryKey())
//              $this->uid = md5($this->aeModelData->getPrimaryKey());
//          else $this->uid = parent::getUid();
//      }
//      return $this->uid;
//  }
    
}

?>