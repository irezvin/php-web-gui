<?php

class Pwg_Controller_Std_Details extends Pwg_Controller_MDI_Window {

	const evtStore = 'recordStored';
    
    const evtCancelCreation = 'cancelCreation';
	
	protected $primaryKey = false;

	protected $mapperClass = false;
	
	protected $createOnNoId = false;
	
	protected $closeOnCreateCancel = true;
	
	protected $cancelledRecordCreation = false;
	
	/**
	 * @var Pwg_Data_Source
	 */
	protected $dsDetails = false;

	/**
	 * @var Pwg_Data_Navigator
	 */
	protected $dnDetails = false;
	
	protected $scheduleClose = false;
    
    protected $cancelBeforeSetKey = false;
    
    protected $lockCancelEvent = false;

	function setPrimaryKey($primaryKey) {
		$this->primaryKey = $primaryKey;
		if ($this->dsDetails && strlen($this->mapperClass)) {
			$m = $this->getApplication()->getMapper($this->mapperClass);
			$f = $m->listPkFields();
		    $restr = array();
			if (is_array($primaryKey)) {
				for ($i = 0; $i < count($f); $i++) $restr[$f[$i]] = $primaryKey[$i];
			} else {
				$restr[$f[0]] = $primaryKey;
				if ($primaryKey === false) $restr = array();
    				else $restr[$f[0]] = $primaryKey;
			}
            if ($this->cancelBeforeSetKey && $this->dsDetails->canCancel()) {
                $this->lockCancelEvent = true;
                $this->dsDetails->cancel();
                $this->lockCancelEvent = false;
            }
			$this->dsDetails->setRestrictions($restr);
			if (!$this->dsDetails->isOpen()) $this->dsDetails->open();
			if (!$this->dsDetails->isOpen()) {
                $this->dsDetails->open();
            }
            Pwg_Conversation::log("Restr is ", $restr);
            if ($this->createOnNoId && !$restr) {
                Pwg_Conversation::log("Lets create; canCreate is ", $this->dsDetails->canCreate());
                $this->dsDetails->createRecord();
            }
		}
	}

	function getPrimaryKey() {
		return $this->primaryKey;
	}
	
    
    function setCancelBeforeSetKey($cancelBeforeSetKey) {
        $this->cancelBeforeSetKey = $cancelBeforeSetKey;
    }
    
    function getCancelBeforeSetKey() {
        return $this->cancelBeforeSetKey;
    }    

	protected function doOnGetControlPrototypes(& $prototypes) {
		Ae_Util::ms($prototypes, array(
			'dsDetails' => array(
				'debug' => true,
				'mapperClass' => $this->mapperClass,
				'limit' => 1,
			),
			'dnDetails' => array(
				'displayParentPath' => '../pnlLayout',
				'dataSourcePath' => '../dsDetails',
				'hasBtnFirst' => false,
				'hasBtnNext' => false,
				'hasBtnPrev' => false,
				'hasBtnLast' => false,
				'hasBtnDelete' => false,
				'hasBtnNew' => false,
				'hasIndexDisplay' => false,
			),
			'pnlLayout' => array(
				'template' => '
					{dnDetails}
					<br />
					{pnlFields}
				',
			),
			'pnlFields' => array(
				'displayParentPath' => '../pnlLayout',
				'template' => '
					<!-- pnlFields -->
				'
			),
		));
	}
	
	protected function setCreateOnNoId($value) {
		$this->createOnNoId = $value;
	}

	protected function setCloseOnCreateCancel($value) {
		$this->closeOnCreateCancel = $value;
	}

	protected function getRecordDefaults() {
		return array();
	}
	
	protected function doAfterControlsCreated() {
		parent::doAfterControlsCreated();
		if ($this->primaryKey) $this->setPrimaryKey($this->primaryKey);
		elseif ($this->createOnNoId) {
			$this->dsDetails->createRecord($this->getRecordDefaults());
		}
	}
	
	function handleDsDetailsOnCancel() {
		if ($this->lockCancelEvent) return;
        if (!$this->primaryKey) $this->cancelledRecordCreation = true;
		if (!$this->primaryKey && $this->closeOnCreateCancel) {
			$this->scheduleClose = true;
		}
        $params = array('scheduleClose' => & $this->scheduleClose);
        $this->triggerEvent(self::evtCancelCreation, $params);
	}
	
	function handleDsDetailsOnCurrentRecord() {
		$rec = $this->dsDetails->getCurrentRecord();
		$this->primaryKey = $rec? $rec->getPrimaryKey() : false;
	}

	function handleDsDetailsOnAfterStoreRecord($dsDetails, $eventType, $params) {
        if ($rec = $this->dsDetails->getCurrentRecord()) {
            $this->primaryKey = $rec->getPrimaryKey();
        }
        Pwg_Conversation::log("Store // PK is ", $this->primaryKey);
		$this->triggerEvent(self::evtStore, $params);
	}
	
	function handleDsDetailsAfterCurrentRecord() {
		if ($this->scheduleClose) {
			$this->scheduleClose = false;
			$this->closeWindow(array('cancelRecordCreation' => true));
		}
	}
	
	function isCancelledRecordCreation() {
		return $this->cancelledRecordCreation;
	} 

}
