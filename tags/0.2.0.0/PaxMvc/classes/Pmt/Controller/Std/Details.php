<?php

class Pmt_Controller_Std_Details extends Pmt_Controller_MDI_Window {

	protected $storeEvent = 'recordStored';
	
	protected $primaryKey = false;

	protected $mapperClass = false;
	
	protected $createOnNoId = false;
	
	protected $closeOnCreateCancel = true;
	
	protected $cancelledRecordCreation = false;
	
	/**
	 * @var Pmt_Data_Source
	 */
	protected $dsDetails = false;

	/**
	 * @var Pmt_Data_Navigator
	 */
	protected $dnDetails = false;
	
	protected $scheduleClose = false;

	function setPrimaryKey($primaryKey) {
		$this->primaryKey = $primaryKey;
		if ($this->dsDetails && strlen($this->mapperClass)) {
			$m = Ae_Dispatcher::getMapper($this->mapperClass);
			$f = $m->listPkFields();
			if (is_array($primaryKey)) {
				for ($i = 0; $i < count($f); $i++) $restr[$f[$i]] = $primaryKey[$i];
			} else {
				$restr[$f[0]] = $primaryKey;
			}
			$this->dsDetails->setRestrictions($restr);
			if (!$this->dsDetails->isOpen()) $this->dsDetails->open();
		}
	}

	function getPrimaryKey() {
		return $this->primaryKey;
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
		if (!$this->primaryKey) $this->cancelledRecordCreation = true;
		if (!$this->primaryKey && $this->closeOnCreateCancel) {
			$this->scheduleClose = true;
		}
	}
	
	function handleDsDetailsOnCurrentRecord() {
		$rec = $this->dsDetails->getCurrentRecord();
		$this->primaryKey = $rec? $rec->getPrimaryKey() : false;
	}

	function handleDsDetailsOnAfterStoreRecord($dsDetails, $eventType, $params) {
		$this->triggerEvent($this->storeEvent, $params);
	}
	
	function handleDsDetailsAfterCurrentRecord() {
		Pm_Conversation::log($this->dsDetails->getRestrictions());
		if ($this->scheduleClose) {
			$this->scheduleClose = false;
			$this->closeWindow(array('cancelRecordCreation' => true));
		}
	}
	
	function isCancelledRecordCreation() {
		return $this->cancelledRecordCreation;
	} 

}