<?php

class Pmt_Data_Binder extends Pmt_Base implements Pm_I_Observer {
    
    /**
     * @var Pmt_Data_Source
     */
    protected $dataSource = false;
    
    /**
     * @var Pmt_I_Control
     */
    protected $recordPropertyName = false;
    
    /**
     * @var Pmt_I_Control
     */
    protected $dataControl = false;

    /**
     * @var Pmt_I_Control
     */
    protected $labelControl = false;
    
    protected $decorator = false;
    
    /**
     * @var Pmt_I_Control
     */
    protected $errorControl = false;
    
    protected $controlChangeEvents = array('change');
    
    protected $dataPropertyName = 'text';
    
    protected $labelPropertyName = 'caption';
    
    protected $errorPropertyName = 'caption';
    
    protected $dynamicPropInfo = false;
    
    protected $alwaysCheckRecord = false;   
    
    protected $controlsRefresh = true;
    
    protected $readOnlyPropertyName = 'readOnly';
    
    protected $updateControlAttribsFromProperty = false;

    /**
     * @var Ae_Model_Object
     */
    protected $currentRecord = false;
    
    /**
     * @var Ae_Model_Property
     */
    protected $propInfo = false;
    
    protected $allowNullValues = false;
    
    protected $lockUpdateRecord = 0;
    
    // ------------------ dataSource related methods -------------------
    
    function setDataSource(Pmt_Data_Source $v = null) {
        $n = substr(__FUNCTION__, 3); $n{0} = strtolower($n{0}); $ov = $this->$n; $this->$n = $v; $this->refAdd($v);
        if ($ov !== $v) {
            if ($ov) {
                $ov->unobserve('onCurrentRecord', $this, 'handleDataSourceCurrentRecord');
                $ov->unobserve('onInvalidRecord', $this, 'handleDataSourceInvalidRecord');
                $ov->unobserve('onReadOnlyStatusChange', $this, 'handleDataSourceReadOnlyStatusChange');
                $ov->unobserve('onUpdateRecord', $this, 'handleDataSourceOnUpdateRecord');
            }
            if ($v) {
                $this->intSetDataSource($v);
            }
        }
    }
    
    function getDataSource() {$n = substr(__FUNCTION__, 3); $n{0} = strtolower($n{0}); return $this->$n;}
    
    function setDataSourcePath($path) {$n = substr(__FUNCTION__, 3, -4); $n{0} = strtolower($n{0}); $this->associations[$n] = $path;}

    function setDynamicPropInfo($v) {
        $n = substr(__FUNCTION__, 3); $n{0} = strtolower($n{0}); 
        $ov = $this->$n;
        $this->$n = $v;
        if ($ov !== $v) $this->updatePropInfo();
    }
    
    function getDynamicPropInfo() {$n = substr(__FUNCTION__, 3); $n{0} = strtolower($n{0}); return $this->$n;}
    
    function setRecordPropertyName($v) {
        $n = substr(__FUNCTION__, 3); $n{0} = strtolower($n{0}); $this->$n = $v;
        $this->updatePropInfo();
    }

    function getRecordPropertyName() {$n = substr(__FUNCTION__, 3); $n{0} = strtolower($n{0}); return $this->$n;}
    
    // ------------------ dataControl related methods -------------------
    
    function setDataControl(Pmt_I_Control $v) {
        $n = substr(__FUNCTION__, 3); $n{0} = strtolower($n{0}); 
        $ov = $this->$n;
        $this->$n = $v;
        $this->refAdd($v);
        if ($ov !== $v) {
            if ($v) {
                $this->intSetDataControl();
            }
        }
    }
    
    /**
     * @return Pmt_I_Control
     */
    function getDataControl() {$n = substr(__FUNCTION__, 3); $n{0} = strtolower($n{0}); return $this->$n;}
    
    function setDataControlPath($path) {$n = substr(__FUNCTION__, 3, -4); $n{0} = strtolower($n{0}); $this->associations[$n] = $path;}

    function setDataPropertyName($v) {
        $n = substr(__FUNCTION__, 3); $n{0} = strtolower($n{0}); 
        $ov = $this->$n;
        $this->$n = $v;
        if ($ov !== $v) {
            if ($v) {
                $this->refreshDataControlFromPropInfo();
                $this->refreshDataControlFromData();
            }
        }
    }
    
    function getDataPropertyName() {$n = substr(__FUNCTION__, 3); $n{0} = strtolower($n{0}); return $this->$n;}

    function setReadOnlyPropertyName($readOnlyPropertyName) {
        if ($readOnlyPropertyName !== ($oldReadOnlyPropertyName = $this->readOnlyPropertyName)) {
            $this->readOnlyPropertyName = $readOnlyPropertyName;
            $this->refreshDataControlFromPropInfo();
            $this->updateReadOnlyStatus();
        }
    }

    function getReadOnlyPropertyName() {
        return $this->readOnlyPropertyName;
    }   
    
    function setControlChangeEvents($v) {
        if (!is_array($v)) $v = ($v === false? array() : array($v));
        
        $n = substr(__FUNCTION__, 3); $n{0} = strtolower($n{0}); 
        $ov = $this->$n;
        $this->$n = $v;
        if ($ov !== $v) {
            if ($v) {
                if ($this->dataControl) {
                    $this->subscribeToControlChangeEvents($this->controlChangeEvents);
                    $this->refreshDataControlFromData();
                    $this->refreshErrorControlFromData();
                }
            }
        }
    }
    
    function getControlChangeEvents($v) {$n = substr(__FUNCTION__, 3); $n{0} = strtolower($n{0}); return $this->$n;}
    
    function setUpdateControlAttribsFromProperty($v) {
        $n = substr(__FUNCTION__, 3); $n{0} = strtolower($n{0}); 
        $ov = $this->$n;
        $this->$n = $v;
        if ($ov !== $v) {
            if ($v) {
                $this->refreshDataControlFromPropInfo();
            }
        }
    }
    
    function getUpdateControlAttribsFromProperty() {$n = substr(__FUNCTION__, 3); $n{0} = strtolower($n{0}); return $this->$n;}
    
    // ------------------ labelControl related methods -------------------
    
    function setLabelControl(Pmt_I_Control $v = null) {
        $n = substr(__FUNCTION__, 3); $n{0} = strtolower($n{0}); 
        $ov = $this->$n;
        $this->$n = $v;
        $this->refAdd($v);
        if ($ov !== $v) {
            if ($v) {
                $this->refreshLabelControlFromPropInfo();
            }
        }
    }
    
    /**
     * @return Pmt_I_Control
     */
    function getLabelControl() {$n = substr(__FUNCTION__, 3); $n{0} = strtolower($n{0}); return $this->$n;}
    
    function setLabelControlPath($path) {$n = substr(__FUNCTION__, 3, -4); $n{0} = strtolower($n{0}); $this->associations[$n] = $path;}
    
    function setLabelPropertyName($v) {
        $n = substr(__FUNCTION__, 3); $n{0} = strtolower($n{0}); 
        $ov = $this->$n;
        $this->$n = $v;
        if ($ov !== $v) {
            if ($v && $this->labelControl) {
                $this->refreshLabelControlFromPropInfo();
            }
        }
    }
    
    function getLabelPropertyName() {$n = substr(__FUNCTION__, 3); $n{0} = strtolower($n{0}); return $this->$n;}

    // ------------------ other methods ----------------------------------
    
    function hasContainer() {
        return false;
    }
    
    // ------------------ errorControl related methods -------------------
    
    function setErrorControl(Pmt_I_Control $v = null) {
        $n = substr(__FUNCTION__, 3); $n{0} = strtolower($n{0}); 
        $ov = $this->$n;
        $this->$n = $v;
        $this->refAdd($v);
        if ($ov !== $v) {
            if ($v) {
                $this->refreshErrorControlFromPropInfo();
            }
        }
    }
    
    /**
     * @return Pmt_I_Control
     */
    function getErrorControl() {$n = substr(__FUNCTION__, 3); $n{0} = strtolower($n{0}); return $this->$n;}
    
    function setErrorControlPath($path) {$n = substr(__FUNCTION__, 3, -4); $n{0} = strtolower($n{0}); $this->associations[$n] = $path;}
    
    function setErrorPropertyName($v) {
        $n = substr(__FUNCTION__, 3); $n{0} = strtolower($n{0}); 
        $ov = $this->$n;
        $this->$n = $v;
        if ($ov !== $v) {
            if ($v && $this->errorControl) {
                $this->refreshErrorControlFromData();
            }
        }
    }
    
    function getErrorPropertyName() {$n = substr(__FUNCTION__, 3); $n{0} = strtolower($n{0}); return $this->$n;}
    
//  Event Handlers

    function handleDataSourceInvalidRecord(Pmt_Data_Source $source, $eventType, $params = array()) {
        if ($this->controlsRefresh && $this->errorControl) $this->refreshErrorControlFromData();
    }

    function handleDataSourceCurrentRecord(Pmt_Data_Source $source, $eventType, $params = array()) {
        $this->refresh();
    }
    
    function handleDataSourceOnUpdateRecord(Pmt_Data_Source $source, $eventType, $params = array()) {
        if (!$this->lockUpdateRecord) $this->refresh();
    }
    
    function handleControlChange(Pmt_I_Control $control, $eventType, $params = array()) {
        if ($this->debug) {
            Pm_Conversation::log("$control change: $eventType");
        }
        if ($this->currentRecord && $this->dataPropertyName) {
            $uData = array();
            if ((($pVal = Pmt_Base::getProperty($control, $this->dataPropertyName, null)) !== null) || $this->allowNullValues) {
                $uData[$this->recordPropertyName] = $pVal;
                $this->lockUpdateRecord++; 
                $this->dataSource->updateCurrentRecord($uData);
                $this->lockUpdateRecord--;
            }
        }
    }
    
    function getControlValue($default = null) {
        $res = $default;
        if ($this->dataPropertyName && $this->dataControl) 
            $res = Pmt_Base::getProperty($this->dataControl, $this->dataPropertyName, $default);
        return $res;
    }
    
    function setControlValue($value) {
        $res = false;
        if ($this->dataPropertyName && $this->dataControl) 
            $res = Pmt_Base::setProperty($this->dataControl, $this->dataPropertyName, $value);
        return $res;
    }

    function handleEvent(Pm_I_Observable $source, $eventType, $params = array()) {
    }
    
    function refresh() {
        $r = $this->dataSource->getCurrentRecord();
        if (!$r) $r = null;
        $this->internalSetRecord($r);
    }
    
//  Implementation methods
    
    function getRecordPropertyValue() {
        $res = false;
        if ($this->currentRecord && strlen($this->recordPropertyName)) {
            $res = $this->currentRecord->getField($this->recordPropertyName, false);
        }
        if (($d = $this->getDecorator())) $res = $d->apply($res); 
        return $res;
    }
    
    protected function setRecordPropertyValue($value) {
        $res = false;
        if ($this->currentRecord && strlen($this->recordPropertyName)) {
            $res = $this->currentRecord->setField($this->recordPropertyName. $value);
        } 
        return $res;
    }
    
    protected function getRecordError() {
        $res = false;
        Pm_Conversation::log($this->recordPropertyName.' getRecordError()');
        if ($this->currentRecord && ($this->currentRecord->_checked || $this->alwaysCheckRecord) && strlen($this->recordPropertyName)) {
            $res = $this->currentRecord->getErrors($this->recordPropertyName, false);
        } 
        return $res;
    }
    
    protected function internalSetRecord(Ae_Model_Object $record = null) {
        if ($this->debug)
            Pm_Conversation::log("!!!!" . $this->getResponderId()." internalSetRecord: ".($record? (get_class($record)." #".$record->getPrimaryKey()) : "null"));
        
        $this->currentRecord = $record;
        if ($this->dynamicPropInfo) $this->updatePropInfo();
        else {
            if ($this->currentRecord) {
                if ($this->controlsRefresh) {
                    if ($this->dataControl) $this->setProperty($this->dataControl, 'disabled', false);
                    if ($this->labelControl) $this->setProperty($this->labelControl, 'disabled', false);
                    if ($this->dataControl) $this->refreshDataControlFromData();
                    if ($this->errorControl) $this->refreshErrorControlFromData();
                }
            } else {
                if ($this->controlsRefresh) {
                    $this->refreshErrorControlFromData('');
                    $this->refreshDataControlFromData('');
                    if ($this->dataControl) $this->setProperty($this->dataControl, 'disabled', true);
                    if ($this->labelControl) $this->setProperty($this->labelControl, 'disabled', true);
                    if ($this->errorControl) $this->setProperty($this->errorControl, 'visible', false);
                }
            }
        }
    }
    
    protected function refreshDataControlFromData($value = null) {
        if (func_num_args() == 0) $value = $this->getRecordPropertyValue();
        if ($this->debug) Pm_Conversation::log($this->getResponderId(). ": setting value of ". $this->dataControl->getResponderId()." to ".$value);
        Pmt_Base::setProperty($this->dataControl, $this->dataPropertyName, $value); 
    }
    
    protected function refreshErrorControlFromData($error = null) {
        Pm_Conversation::log("$this RefreshErrorControlFromData", $this->recordPropertyName);
        $n = func_num_args();       
        if (func_num_args() == 0) $error = $this->getRecordError();
        if (is_array($error)) $error = Ae_Util::implode_r("<br />", $error);
        if (strlen($error)) {
            Pmt_Base::setProperty($this->errorControl, $this->errorPropertyName, $error); 
            Pmt_Base::setProperty($this->errorControl, 'visible', true);
        } else {
            Pmt_Base::setProperty($this->errorControl, $this->errorPropertyName, '');
            Pmt_Base::setProperty($this->errorControl, 'visible', false);
        }
    }
    
    protected function refreshDataControlFromPropInfo() {
        if ($pi = $this->propInfo) {
            if ($this->dynamicPropInfo) $this->refreshDataControlFromData($pi->value);
            $this->updateReadOnlyStatus();          
            if (isset($pi->attribs) && is_array($pi->attribs) && $this->updateControlAttribsFromProperty) 
                Pmt_Base::setProperty($this->dataControl, 'attribs', $pi->attribs);
        } else {
            Pmt_Base::setProperty($this->dataControl, $this->dataPropertyName, '(no property)'); 
            Pmt_Base::setProperty($this->dataControl, 'disabled', false);
            $this->updateReadOnlyStatus();
        }
    }
    
    protected function refreshLabelControlFromPropInfo() {
        if ($pi = $this->propInfo) {
            Pmt_Base::setProperty($this->labelControl, $this->labelPropertyName, $pi->caption !== false? $pi->caption : $pi->name);
        } else {
            Pmt_Base::setProperty($this->labelControl, $this->labelPropertyName, '(no such property: '.$this->recordPropertyName.' in '.(is_object($this->currentRecord)? get_class($this->currentRecord) : gettype($this->currentRecord)).')');
        }
    }
    
    protected function refreshErrorControlFromPropInfo() {
        if ($pi = $this->propInfo) {
            if ($this->dynamicPropInfo) $this->refreshErrorControlFromData($pi->error);
                else $this->refreshErrorControlFromData(false);
        } else {
            $this->refreshErrorControlFromData(false);
        }
    }
    
    protected function updatePropInfo() {
        $this->propInfo = false;
        if (!$this->currentRecord && $this->dataSource && ($m = $this->dataSource->getMapper())) $this->currentRecord = $m->getPrototype();
        if (strlen($this->recordPropertyName) && $this->currentRecord && $this->currentRecord->hasProperty($this->recordPropertyName)) {
            $this->propInfo = $this->currentRecord->getPropertyInfo($this->recordPropertyName, !$this->dynamicPropInfo);
        }
        if ($this->controlsRefresh) {
            if ($this->dataControl) $this->refreshDataControlFromPropInfo();
            if ($this->labelControl) $this->refreshLabelControlFromPropInfo();
            if ($this->errorControl) $this->refreshErrorControlFromPropInfo();
        }
        return $this->propInfo;
    }
    
    protected function subscribeToControlChangeEvents($eventTypes) {
        foreach ($eventTypes as $eventType) $this->dataControl->observe($eventType, $this, 'handleControlChange');
    }
    
    protected function hasJsObject() {
        return false;
    }
    
    protected function intSetDataSource() {
        $v = $this->dataSource;
        $this->currentRecord = $v->getCurrentRecord();
        if (!$this->currentRecord && ($m = $v->getMapper())) $this->currentRecord = $m->getPrototype();
        $this->updatePropInfo();
        $this->updateReadOnlyStatus();
        $this->dataSource->observe('onCurrentRecord', $this, 'handleDataSourceCurrentRecord');
        $this->dataSource->observe('onInvalidRecord', $this, 'handleDataSourceInvalidRecord');
        $this->dataSource->observe('onReadOnlyStatusChange', $this, 'handleDataSourceReadOnlyStatusChange');
        $this->dataSource->observe('onUpdateRecord', $this, 'handleDataSourceOnUpdateRecord');
        $this->dataSource->observe('onRefresh', $this, 'handleDataSourceRefresh');
    }
    
    function handleDataSourceReadOnlyStatusChange() {
        $this->updateReadOnlyStatus();
    }
    
    function handleDataSourceRefresh(Pmt_Data_Source $dataSource, $eventType, $params) {
        $this->updateReadOnlyStatus();
    }
    
    protected function updateReadOnlyStatus() {
        if ($this->dataControl && strlen($this->readOnlyPropertyName) && $this->dataSource) {
            $pRo = $this->propInfo? $this->propInfo->readOnly : false;
            $ro = $this->dataSource->getReadOnly() || $pRo;
            Pmt_Base::setProperty($this->dataControl, $this->readOnlyPropertyName, $ro);
        } else {
        }
    }
    
    protected function intSetDataControl() {
        if ($this->controlChangeEvents) $this->subscribeToControlChangeEvents($this->controlChangeEvents);
        $this->refreshDataControlFromPropInfo();
        $this->refreshDataControlFromData();
    }

    function setAllowNullValues($allowNullValues) {
        $this->allowNullValues = $allowNullValues;
    }

    function getAllowNullValues() {
        return $this->allowNullValues;
    }

    function setAlwaysCheckRecord($alwaysCheckRecord) {
        $this->alwaysCheckRecord = $alwaysCheckRecord;
    }

    function getAlwaysCheckRecord() {
        return $this->alwaysCheckRecord;
    }

    function setDecorator($decorator) {
        $this->decorator = $decorator;
    }

    /**
     * @return Ae_I_Decorator
     */
    function getDecorator() {
        $this->decorator = Ae_Decorator::instantiate($this->decorator);
        return $this->decorator;
    }    
    
}

?>