<?php

class Pwg_Data_Binder_DataSource extends Pwg_Data_Binder {
    
    const SLAVE_MODE_NONE = 0;
    const SLAVE_MODE_BEFORE_SAVE = 1;
    const SLAVE_MODE_AFTER_SAVE = 2;

    protected $slaveMode = self::SLAVE_MODE_NONE;
    
    /**
     * @var array ('masterField' => 'detailField')
     */
    protected $relationMap = array();
    
    protected $oldRestrictions = false;
    
//  protected $disabled = false;
    
    /**
     * @var Pwg_Data_Source
     */
    protected $dataControl = false;

    protected $defaultRestrictions = array();
    
    /**
     * When master record is refreshed, details records will always be refreshed, even if restrictions aren't changed
     * @var mixed false|Pwg_Data_Source::HOLD_NUMBER|Pwg_Data_Source::HOLD_KEY  
     */
    protected $alwaysReloadDetails = false;
    
//  function setDisabled($v) {
//      $n = substr(__FUNCTION__, 3); $n{0} = strtolower($n{0}); 
//      $ov = $this->$n;
//      $this->$n = $v;
//      if ($ov !== $v) {
//          if ($v) {
//              if ($this->dataControl) $this->refreshDataControlFromData();
//          }
//      }
//  }
//  
//  function setDisabled() {$n = substr(__FUNCTION__, 3); $n{0} = strtolower($n{0}); return $this->$n;}
    
    function setDataSource(Pwg_Data_Source $dataSource = null) {
        if (($this->dataSource !== $dataSource) && ($this->slaveMode !== self::SLAVE_MODE_NONE)) {
            $this->disableSlaveMode();
            parent::setDataSource($dataSource);
            $this->enableSlaveMode();
        } else {
            parent::setDataSource($dataSource);
        }
    }
    
    function setRelationMap($v) {
        $n = substr(__FUNCTION__, 3); $n{0} = strtolower($n{0}); 
        $ov = $this->$n;
        $this->$n = $v;
        if ($ov !== $v) {
            if ($v) {
                if ($this->dataControl) $this->refreshDataControlFromData();
            }
        }
    }
    
    function getRelationMap() {$n = substr(__FUNCTION__, 3); $n{0} = strtolower($n{0}); return $this->$n;}
    
    function setDataControl(Pwg_Data_Source $v = null) {
        if ($this->dataControl !== $v) {
            if ($this->slaveMode !== self::SLAVE_MODE_NONE) $this->disableSlaveMode();
            parent::setDataControl($v);
            if ($this->slaveMode !== self::SLAVE_MODE_NONE) $this->enableSlaveMode();
        } else {
            parent::setDataControl($v);
        }
    }
    
    /**
     * @return Pwg_Data_Source
     */
    function getDataControl() {
        return parent::getDataControl();
    }
    
    protected function refreshDataControlFromData($value = null) {
        $restrictions = $this->defaultRestrictions;
        if ($this->currentRecord) {
            foreach ($this->relationMap as $srcVar => $destVar) {
                 $restrictions[$destVar] = $this->currentRecord->getField($srcVar);
            }
        }
        if ($this->dataControl) {
            if (($this->oldRestrictions != $restrictions) || ($this->alwaysReloadDetails !== false)) {
                $this->oldRestrictions = $restrictions;
                $this->dataControl->setRestrictions($restrictions);
                $this->triggerEvent('beforeRefreshDetails');
                if (!$this->dataControl->isOpen()) {
                    $this->dataControl->open();
                }
                elseif ($this->alwaysReloadDetails !== false) {
                    $this->dataControl->reload($this->alwaysReloadDetails);
                }
            } else {
            }
        }
    }

    function setSlaveMode($slaveMode) {
        if ($slaveMode !== ($oldSlaveMode = $this->slaveMode)) {
            if ($oldSlaveMode !== self::SLAVE_MODE_NONE) $this->disableSlaveMode();
            
            $this->slaveMode = $slaveMode;
            
            if ($this->slaveMode !== self::SLAVE_MODE_NONE) $this->enableSlaveMode();
        }
    }

    function getSlaveMode() {
        return $this->slaveMode;
    }
    
    protected function enableSlaveMode() {
        if ($this->dataControl && $this->dataSource) {
            $this->dataControl->observe('onUpdateRecord', $this, 'handleSlaveUpdate');
            $this->dataSource->observe('onBeforeStoreRecord', $this, 'beforeMasterSave');
            $this->dataSource->observe('onAfterStoreRecord', $this, 'afterMasterSave');
            $this->dataSource->observe('onCancel', $this, 'masterCancel');
        }
    }
    
    protected function disableSlaveMode() {
        if ($this->dataControl) {
            $this->dataControl->unobserve('onUpdateRecord', $this, 'handleSlaveUpdate');
        }
        if ($this->dataSource) {
            $this->dataSource->unobserve('onBeforeSave', $this, 'beforeMasterSave');
            $this->dataSource->unobserve('onAfterSave', $this, 'afterMasterSave');
            $this->dataSource->unobserve('onCancel', $this, 'masterCancel');
        }
    }

    function handleSlaveUpdate() {
        if ($this->dataSource) $this->dataSource->updateCurrentRecord();
    }
    
    function beforeMasterSave(Pwg_Data_Source $ds, $eventType, $params) {
        if (($this->slaveMode === self::SLAVE_MODE_BEFORE_SAVE) && $this->dataControl->isDirty()) {
            if (!$this->dataControl->saveRecord()) $params['canProceed'] = false;
        }
    }
    
    function afterMasterSave(Pwg_Data_Source $ds, $eventType, $params) {
        if (($this->slaveMode === self::SLAVE_MODE_AFTER_SAVE) && $this->dataControl->isDirty()) {
            if (!$this->dataControl->saveRecord()) $this->dataSource->updateCurrentRecord();
        }
    }    
    
    function masterCancel() {
        if ($this->dataControl->isDirty()) $this->dataControl->cancel();
    }

    function setDefaultRestrictions(array $defaultRestrictions = array()) {
        $this->defaultRestrictions = $defaultRestrictions;
    }

    function getDefaultRestrictions() {
        return $this->defaultRestrictions;
    }    

    function setAlwaysReloadDetails($alwaysReloadDetails) {
        if ($alwaysReloadDetails !== ($oldAlwaysReloadDetails = $this->alwaysReloadDetails)) {
            if (!in_array($alwaysReloadDetails, array(false, Pwg_Data_Source::HOLD_NUMBER, Pwg_Data_Source::HOLD_KEY), true))
                throw new Exception("Allowed values for \$alwaysReloadDetails are false, Pwg_Data_Source::HOLD_NUMBER, Pwg_Data_Source::HOLD_KEY");
            $this->alwaysReloadDetails = $alwaysReloadDetails;
        }
    }

    function getAlwaysReloadDetails() {
        return $this->alwaysReloadDetails;
    }    
    
}

?>