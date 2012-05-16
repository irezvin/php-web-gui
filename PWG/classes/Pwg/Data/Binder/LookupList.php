<?php

class Pwg_Data_Binder_LookupList extends Pwg_Data_Binder {
    
    protected $listPropertyName = false;
    
    protected $disableIfNoValues = false;
    
    protected $valueList = false;
    
    protected $valuesGetter = false;
    
    protected $valuesProviderPrototype = false;
    
    protected $dummyCaption = false;
    
    protected $dummyValue = null;
    
    protected $clearAssocOnValueChange = true;
    
    /**
     * @var Ac_Model_Values
     */
    protected $valuesProvider = false;

    /**
     * @var array
     */
    protected $actualValues = false;
    
    /**
     * @return array
     */
    function getActualValues() {
        if ($this->actualValues === false || $this->dynamicPropInfo) {
            if (is_array($this->valueList)) {
                $this->actualValues = $this->valueList;
            } elseif ($this->valuesGetter && $this->currentRecord && is_callable($getter = array($this->currentRecord, $this->valuesGetter))) {
                $this->actualValues = call_user_func($getter);
            }
            elseif ($provider = $this->getValuesProvider()) {
                $this->actualValues = $provider->getValueList();
            } else {
                $this->triggerEvent('getValues', array('actualValues' => & $this->actualValues));
            }
        }
        return $this->actualValues;
    }
    
    function getActualDummyCaption() {
        $res = false;
        if ($this->dummyCaption === false) {
            if ($this->propInfo && isset($this->propInfo->dummyCaption) && ($this->propInfo->dummyCaption !== false))
                $res = $this->propInfo->dummyCaption;
        } else {
            $res = $this->dummyCaption;
            if ($res === true) { // 'auto'
                if ($this->propInfo && isset($this->propInfo->caption)) $res = '('.$this->propInfo->caption.')';
                else $res = '';
            }
        }
        return $res;
    }
    
    function getActualDummyValue() {
        $res = false;
        if ($this->dummyValue === false) {
            if ($this->propInfo && isset($this->propInfo->dummyValue) && ($this->propInfo->dummyValue !== false))
                $res = $this->propInfo->dummyValue;
        } else {
            $res = $this->dummyValue;
        }
        return $res;
    }
    
    function setListPropertyName($v) {
        $n = substr(__FUNCTION__, 3); $n{0} = strtolower($n{0}); $ov = $this->$n; $this->$n = $v;
        if (($ov !== $v) && ($this->dataControl)) { $this->refreshDataControlFromPropInfo(); }
    }
    
    function getListPropertyName() {$n = substr(__FUNCTION__, 3); $n{0} = strtolower($n{0}); return $this->$n;}
        
    function setDisableIfNoValues($v) {
        $n = substr(__FUNCTION__, 3); $n{0} = strtolower($n{0}); $ov = $this->$n; $this->$n = $v;
        if (($ov !== $v) && ($this->dataControl)) { $this->refreshDataControlFromPropInfo(); }
    }
    
    function getDisableIfNoValues() {$n = substr(__FUNCTION__, 3); $n{0} = strtolower($n{0}); return $this->$n;}
    
    function setValueList($v) {
        $n = substr(__FUNCTION__, 3); $n{0} = strtolower($n{0}); $ov = $this->$n; $this->$n = $v;
        if (($ov !== $v) && ($this->dataControl)) { 
            $tmp = $this->getControlValue();
            $this->refreshDataControlFromPropInfo();
            if (!$this->dynamicPropInfo) $this->refreshDataControlFromData();
            $this->setControlValue($tmp); 
        } else {
        }
    }
    
    function getValueList() {$n = substr(__FUNCTION__, 3); $n{0} = strtolower($n{0}); return $this->$n;}
    
    function setValuesGetter($v) {
        $n = substr(__FUNCTION__, 3); $n{0} = strtolower($n{0}); $ov = $this->$n; $this->$n = $v;
        if (($ov !== $v) && ($this->dataControl)) { $this->refreshDataControlFromPropInfo(); }
    }
    
    function getValuesGetter() {$n = substr(__FUNCTION__, 3); $n{0} = strtolower($n{0}); return $this->$n;}
    
    function setValuesProviderPrototype($v) {
        $n = substr(__FUNCTION__, 3); $n{0} = strtolower($n{0}); $ov = $this->$n; $this->$n = $v;
        if ($ov !== $v) {
            $this->actualValues = false;
            if ($this->valuesProvider) $this->valuesProvider = false; 
            if ($this->dataControl) $this->refreshDataControlFromPropInfo();
        }
    }
    
    function getValuesProviderPrototype() {$n = substr(__FUNCTION__, 3); $n{0} = strtolower($n{0}); return $this->$n;}

    function setDummyCaption($v) {
        $n = substr(__FUNCTION__, 3); $n{0} = strtolower($n{0}); $ov = $this->$n; $this->$n = $v;
        if (($ov !== $v) && ($this->dataControl)) { $this->refreshDataControlFromPropInfo(); }
    }
    
    function getDummyCaption() {$n = substr(__FUNCTION__, 3); $n{0} = strtolower($n{0}); return $this->$n;}
    
    function setDummyValue($v) {
        $n = substr(__FUNCTION__, 3); $n{0} = strtolower($n{0}); $ov = $this->$n; $this->$n = $v;
        if (($ov !== $v) && ($this->dataControl)) { $this->refreshDataControlFromPropInfo(); }
    }
    
    function getDummyValue() {$n = substr(__FUNCTION__, 3); $n{0} = strtolower($n{0}); return $this->$n;}

    function handleControlChange(Pwg_I_Control $control, $eventType, $params = array()) {
        if (($this->getActualDummyCaption() !== false) && $this->dataControl instanceof Pwg_List && !array_diff($this->dataControl->getSelectedOptionIndices(), array(0))) {
            $v = $this->getActualDummyValue();
            if ($this->currentRecord && $this->dataPropertyName) {
                $uData = array();
                $uData[$this->recordPropertyName] = $v;
            }
        } else {
            if ($this->currentRecord && $this->dataPropertyName) {
                $uData = array();
                if ((($pVal = Pwg_Base::getProperty($control, $this->dataPropertyName, null)) !== null) || $this->allowNullValues) {
                    $uData[$this->recordPropertyName] = $pVal; 
                }
            }
            
        }
        if ($this->clearAssocOnValueChange) {
            if ($this->propInfo && isset($this->propInfo->objectPropertyName) && strlen($this->propInfo->objectPropertyName)) { 
                $uData[$this->propInfo->objectPropertyName] = false;
            }
        }
        $this->dataSource->updateCurrentRecord($uData);
        $v = Pwg_Base::getProperty($this->dataControl, $this->dataPropertyName, 'default');
    }
    
    /**
     * @return Ac_Model_Values
     */
    function getValuesProvider() {
        if ($this->valuesProvider === false) {
            if ($this->valuesProviderPrototype) {
                $this->valuesProvider = & Ac_Model_Values::factoryIndependent($this->valuesProviderPrototype);
            }
            elseif ($p = & $this->propInfo) {
                if (isset($p->values) && $p->values || isset($p->valueList) && is_array($p->valueList)) {
                    $this->valuesProvider = & Ac_Model_Values::factoryWithProperty($p);
                }
            }
            else $this->valuesProvider = null;
            if ($this->debug && $this->valuesProvider) 
                Pwg_Conversation::log("!!!! ".$this->getResponderId().'\' valuesProvider\' where is '.$this->valuesProvider->where);
        }
        return $this->valuesProvider;
    }
    
    function refreshListFromProvider($clearValuesProvider = false) {
        $this->actualValues = false;
        if ($clearValuesProvider) $this->valuesProvider = $this->valuesProviderPrototype = false;
        $prevValue = $this->getControlValue();
        $this->refreshValueList();
        $this->setControlValue($prevValue);
//      if ($this->getValuesProvider()) {
//          $this->actualValues = false;
//          $this->refreshValueList();
//      }
    }
    
    function handleDataSourceCurrentRecord(Pwg_Data_Source $source, $eventType, $params = array()) {
        parent::handleDataSourceCurrentRecord($source, $eventType, $params);
        if (!$this->dynamicPropInfo && ($this->valueList === false) && ($this->valuesGetter !== false) && ($this->currentRecord)) {
            $this->refreshValueList();
        }
    }
    
    protected function refreshValueList() {
        if ($this->dataControl && $this->listPropertyName) {
            if ($this->debug) Pwg_Conversation::log($this->id, "Refreshing value list");
            $items = array();
            if ($this->getActualDummyCaption() !== false) $items[$this->getActualDummyValue()] = $this->getActualDummyCaption();
            $av = $this->getActualValues();
            if (!is_array($av)) $av = array();
            $items = Ac_Util::m($items, $av, true);
            Pwg_Base::setProperty($this->dataControl, $this->listPropertyName, $items);
            if ($this->disableIfNoValues) Pwg_Base::setProperty($this->dataControl, 'disabled', !count($av)); 
        }
        
    }
    
    protected function refreshDataControlFromPropInfo() {
        if ($this->debug) Pwg_Conversation::log("!!!! --- refreshing data control from prop info ---");
        if ($this->dynamicPropInfo) $this->valuesProvider = false;
        $this->refreshValueList();
        parent::refreshDataControlFromPropInfo();
    }

    function setClearAssocOnValueChange($clearAssocOnValueChange) {
        $this->clearAssocOnValueChange = $clearAssocOnValueChange;
    }

    function getClearAssocOnValueChange() {
        return $this->clearAssocOnValueChange;
    }    
    
    
}

?>