<?php

class Pwg_Record_Ae extends Pwg_Record_Abstract {

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

    /**
     * @param string|Ae_I_Getter $fieldName
     */
    function getField($fieldName) {
        if (is_string($fieldName) && $this->aeModelData->hasProperty($fieldName)) {
            $res = $this->aeModelData->getField($fieldName);
        } else {
            $res = Ae_Autoparams::getObjectProperty($this->aeModelData, $fieldName);
        }
        return $res;
    }
    
    function getData() {
        $res = array();
        foreach ($this->aeModelData->listOwnFields() as $f) $res[$f] = $this->aeModelData->getField($f);
        return $res;
    }
    
    protected function doUpdateData(array $data) {
        return $this->aeModelData->bind($data);
    }
    
    /**
     * @param string|Ae_I_Getter $fieldName
     */
    function getFieldInfo($fieldName) {
        if (is_string($fieldName) && $this->aeModelData->hasProperty($fieldName)) {
            $res = new Pwg_Record_Fieldinfo_Ae($this->aeModelData->getPropertyInfo($fieldName, true));
        } else {
            $res = new Pwg_Record_Fieldinfo(array('name' => $fieldName, 'caption' => $fieldName));
        }
        return $res;
    }
    
    function getErrors() {
        return $this->aeModelData->getErrors(false, false);
    }
    
    function matches(Pwg_I_Record $otherRecord) {
        $res = null;
        if ($otherRecord === $this) $res = true; else {
            if ($otherRecord instanceof Pwg_Record_Ae) {
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