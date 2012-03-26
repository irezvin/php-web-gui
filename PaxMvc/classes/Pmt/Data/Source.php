<?php

class Pmt_Data_Source extends Pmt_Base {

    const HOLD_NUMBER = 0;
    const HOLD_KEY = 1;
    
    protected $mapperClass = false;

    protected $ordering = array();
    
    protected $groupBy = array();
    
    /**
     * @var Ae_Model_Object
     */
    protected $currentRecord = false;

    protected $currentKey = false;
    
    protected $recordNo = false;
        
    protected $oldRecordNo = false;
    
    protected $recordsCount = false;
    
    protected $dirty = false;
    
    protected $isNew = false;
    
    protected $mapper = false;

    protected $where = array();

    protected $having = array();
    
    /**
     * A filter and a prototype combined.
     * @var array(fieldName => fieldValue);
     */
    protected $restrictions = array();
    
    protected $sqlDb = false;
    
    protected $aeDb = false;
    
    protected $recordDefaults = array();
    
    protected $isOpen = false;
    
    protected $extraJoins = false;
    
    protected $alias = 't';

    protected $readOnly = false;
    
    protected $distinct = false;

    protected $dontGroupOnCount = true;
    
    protected $monitoredMappers = array();
    
    protected $lastMtime = false;
    
    protected $groupSize = false;
    
    protected $forceRestrictionsOnStore = true;
    
    protected $navigated = false;
    
    protected $isCancel = false;
    
    /**
     * @var Pmt_Data_Source_Lister
     */
    protected $lister = false;
    
    function __construct(array $options = array()) {
        $this->updateLastMtime();
        parent::__construct($options);
        if (!$this->lister) $this->setLister(array('class' => 'Pmt_Data_Source_Lister_Memory'));
    }
    
    function setAlias($alias) {
        if ($alias !== ($oldAlias = $this->alias)) {
            $this->alias = $alias;
            $this->intReset();
        }
    }

    function getAlias($effective = false) {
        $res = $this->alias;
        if ($effective && !strlen($res)) $res = 't';
        return $res;
    }   
    
    function isOpen() {
        return $this->isOpen;
    }
    
    function close() {
        $this->intReset();
    }
    
    function open() {
        if (!$this->isOpen) $this->gotoFirst();
    }
    
    function hasInitializer() {
        return false;
    }
    
    function hasContainer() {
        return false;
    }
    
    function setMapperClass($mapperClass) {
        $this->mapperClass = $mapperClass;
        $this->mapper = false;
        $this->intReset();
    }

    function setExtraJoins($extraJoins) {
        $this->extraJoins = $extraJoins;
        $this->intReset();
    }

    function getExtraJoins() {
        return $this->extraJoins;
    }   
    
    function setWhere(array $where) {
        $this->where = $where;
        $this->intReset();
    }
    
    function setWherePart($key, $part) {
        $oldWhere = $this->where;
        $this->where[$key] = $part;
        if ($oldWhere !== $this->where) $this->intReset();
    }
    
    function deleteWherePart($key) {
        if ($this->getWherePart($key) !== false) {
            unset($this->where[$key]);
            $this->intReset();
        }
    }
    
    function getWherePart($key) {
        if (array_key_exists($key, $this->where)) $res = $this->where[$key];
            else $res = false;
        return $res;
    }
    
    function listWhereParts() {
        return array_keys($this->where);
    }
    
    function getWhere() {
        return $this->where;
    }
    
    
    function setHaving(array $having) {
        $this->having = $having;
        $this->intReset();
    }
    
    function setHavingPart($key, $part) {
        $oldHaving = $this->having;
        $this->having[$key] = $part;
        if ($oldHaving !== $this->having) $this->intReset();
    }
    
    function deleteHavingPart($key) {
        if ($this->getHavingPart($key) !== false) {
            unset($this->having[$key]);
            $this->intReset();
        }
    }
    
    function getHavingPart($key) {
        if (array_key_exists($key, $this->having)) $res = $this->having[$key];
            else $res = false;
        return $res;
    }
    
    function listHavingParts() {
        return array_keys($this->having);
    }
    
    function getHaving() {
        return $this->having;
    }
    
    
    function setRestrictions(array $restrictions) {
        $oldRestrictions = $this->restrictions;
        if ($restrictions != $oldRestrictions) {
            $this->restrictions = $restrictions;
            if ($this->isDirty() && $this->currentRecord) $this->updateCurrentRecord($restrictions);
                else {
                    $this->intReset();
                }
        }
    }
    
    function getRestrictions() {
        return $this->restrictions;
    }
    
    function setRecordDefaults(array $defaults) {
        $this->recordDefaults = $defaults;
    }
    
    function getRecordDefaults() {
        return $this->recordDefaults;
    }
    
    function getMapperClass() {
        return $this->mapperClass;
    }
    
    function setMapper(Ae_Model_Mapper $mapper) {
        $this->mapper = $mapper;
        $this->mapperClass = $mapper->getId();
        $this->intReset();
    }
    
    /**
     * @return Ae_Model_Mapper
     */
    function getMapper() {
        if ($this->mapper === false && strlen($this->mapperClass)) $this->mapper = $this->getApplication()->getMapper($this->mapperClass);
        return $this->mapper;
    }
    
    /**
     * @param string|array $ordering
     */
    function setOrdering($ordering) {
        if (!is_array($ordering)) $ordering = array($ordering);
        $this->ordering = $ordering;
        $this->recordNo = false;
        $this->intReset();
    }
    
    function getOrdering() {
        return $this->ordering;
    }
    
//  DataSource: record retrieval and editing
    
    function getRecords($startIndex = false, $length = false) {
        return $this->lister->getRecords($startIndex, $length);
    }
    
    /**
     * @return Ae_Model_Object
     */
    function getCurrentRecord() {
        if ($this->currentRecord === false) {
            if ($this->currentKey !== false && $this->currentKey !== null) {
                $k = $this->currentKey;
                if (!is_array($k)) $k = array($k);
                $coll = & $this->createCollection();
                $coll->addWhere($this->getLegacyDb()->sqlKeysCriteria($k, $this->getMapper()->listPkFields(), $this->alias));
                if ($this->debug) Pm_Conversation::log($this->getResponderId(), $coll->getStatementTail());
                $this->currentRecord = $coll->getNext();
            } else {
                if ($this->recordNo !== false) $this->currentRecord = $this->getKeyByRecordNumber($this->recordNo, true);
            }
        }
        return $this->currentRecord;
    }
    
    function isDirty() {
        return $this->dirty;
    }
    
    function isNew() {
        return $this->isNew;
    }
    
    function updateCurrentRecord($values = array()) {
        if ($record = $this->getCurrentRecord()) {
            if ($this->restrictions && $this->forceRestrictionsOnStore) $values = array_merge($values, $this->restrictions);
            if ($this->debug) Pm_Conversation::log($values);
            $valuesToBind = array();
            $valuesToSet = array();
            foreach ($values as $k => $v) {
            	if (strpos($k, '[') !== false) {
            		$valuesToSet[$k] = $v;
            	} else {
            		$valuesToBind[$k] = $v;
            	}
            }
            if (count($valuesToBind)) $record->bind($valuesToBind);
            if (count($valuesToSet)) foreach ($valuesToSet as $k => $v) {
            	$record->setField($k, $v);
            }
            $this->dirty = true;
            $this->triggerEvent('onUpdateRecord');
            return true; 
        } else {
            return false;
        }
    }
    
    function createRecord(array $values = array()) {
        if ($this->canCreate()) {
            $this->isNew = true;
            $this->dirty = false;
            $this->currentKey = false;
            $this->oldRecordNo = $this->recordNo;
            $this->recordNo = false;
            $this->currentRecord = $this->getMapper()->factory();
            $defaults = array_merge($this->recordDefaults, $values, $this->restrictions);
            if ($defaults) $this->currentRecord->bind($defaults);
            $this->triggerEvent('onNewRecord');
            $this->triggerCurrentRecord();
            if ($this->debug) Pm_Conversation::log("Record created; values are ", $defaults);
            return true; 
        } else return false;
    }
    
    function cancel() {
        if ($this->canCancel()) {
            if ($this->isNew) {
                $this->navigated = false;
                $this->isCancel = true;
                $this->triggerEvent('onCancel');
                $this->isNew = false;
                $this->dirty = false;
                if (!$this->navigated) {
                    if ($this->oldRecordNo !== false) {
                        $this->setRecordNo($this->oldRecordNo);
                        $this->oldRecordNo = false;
                    } else {
                        $this->gotoFirst();
                    }
                }
                $this->navigated = false;
            } else {
                $this->navigated = false;
                $this->isCancel = true;
                $this->triggerEvent('onCancel');
                if (!$this->navigated) {
                    $this->dirty = false;
                    $this->currentKey = false;
                    $this->currentRecord = false;
                    $rn = $this->getRecordNo();
                    $this->recordNo = false;
                    $this->setRecordNo($rn);
                }
                $this->navigated = false;
                $this->isCancel = false;
            }
        }
    }
    
    function deleteRecord() {
        $res = false;
        if ($this->canDelete()) {
            $rec = $this->getCurrentRecord();
            $pk = $rec->getPrimaryKey();
            $canDelete = true;
            $this->triggerEvent('onBeforeDeleteRecord', array('record' => & $rec, 'primaryKey' => $pk, 'canDelete' => & $canDelete));
            if ($canDelete) {
                $rec->delete();
                $this->lister->refresh();
                $this->recordsCount = false;
                $this->triggerEvent('onDeleteRecord', array('record' => $rec, 'primaryKey' => $pk));
                $this->intAdvance(0);
                if ($this->isEnd() && !$this->isStart()) $this->intAdvance(-1);
                $this->triggerCurrentRecord();
                $res = true;
            }
        }
        return $res;
    }
    
    function isRecordValid() {
        if ($rec = $this->getCurrentRecord()) $res = $rec->check();
            else $res = true;
        return $res;
    }
    
    function saveRecord() {
        $res = false;
        $this->log(
            "Trying to save; current record is " .($this->getCurrentRecord()? " present " : " not present ")
            ." and canSave() is ".($this->canSave()? "true" : "false")
            ." because isNew is ".($this->isNew? "true" : "false")
            ." and isDirty is ".($this->dirty? "true" : "false")
        );
            
        if (($rec = $this->getCurrentRecord()) && $this->canSave()) {
            
            $rec->mustRevalidate();
            
            $canProceed = true;
            $errors = array();
            $errorsToRecord = array();
            $this->triggerEvent('onBeforeStoreRecord', array('canProceed' => & $canProceed, 'errors' => & $errors, 'record' => & $rec, 'errorsToRecord' => & $errorsToRecord));
            Ae_Util::ms($errors, $err = $rec->getErrors());
            if ($errorsToRecord) Ae_Util::ms($rec->_errors, $errorsToRecord);
            Ae_Util::ms($errors, $errorsToRecord);
            $this->log("Errors are", $err, $rec->_errors);
            $this->log("Values are ", $rec->getDataFields());
            $this->triggerEvent('onValidateRecord', array($errors));
            if ($canProceed && !$errors) {
            	$this->log("No errors");
                if ($rec->store()) {
                    $res = true;
                    $this->lister->refresh();
                    if (($isNew = $this->isNew)) {
                    	$this->log("Record is new");
                        $k = $rec->getPrimaryKey();
                        $this->log("Pk is", $k);
                        $no = $this->getRecordNumberByKey($rec->getPrimaryKey());
                        $this->log("New number is ", $no);
                        $this->dirty = false;
                        $this->recordsCount = false;
                        
                        $tmp = $this->recordNo;
                        
                        $this->triggerEvent('onAfterStoreRecord', array('record' => $rec, 'isNew' => $isNew));
                        $this->triggerEvent('onStoreRecord', array('index' => $no, 'isNew' => $isNew, 'record' => $rec));
                        
                        $this->isNew = false;
                        
                        $this->oldRecordNo = false;
                        if ($this->recordNo === $tmp) $this->setRecordNo($no);
                    } else {

                        $this->dirty = false;
                        $this->currentKey = false;
                        $this->recordNo = false;
                        $this->triggerEvent('onAfterStoreRecord', array('record' => $rec, 'isNew' => $isNew));
                        $this->triggerEvent('onStoreRecord', array('record' => $rec, 'isNew' => $isNew));
                    }
                    $this->triggerCurrentRecord();
                    $this->updateLastMtime();
                } else {
                    $this->triggerEvent('onError');
                }
            } else {
                $this->triggerEvent('onInvalidRecord', array('errors' => $errors));
            }
        }
        return $res;
    }
    
    function updateLastMtime() {
        $this->lastMtime = time();
    }
    
//  DataSource: navigation

    function isLast() {
        return $this->getRecordNo() >= ($this->getRecordsCount() - 1);
    }
    
    function isFirst() {
        return !$this->getRecordNo();
    }
    
    function gotoNext() {
        if ($this->canMove()) {
            if ($this->getRecordNo() < ($this->getRecordsCount())) {
                $this->intAdvance(+1);
                $this->triggerCurrentRecord();
            }
        }
    }
    
    
    function gotoPrev() {
        if ($this->canMove()) {
            if ($this->getRecordNo() > -1) {
                $this->intAdvance(-1);
                $this->triggerCurrentRecord();
            }
        }
    }
    
    function gotoFirst() {
        if ($this->canMove()) {
            $this->intGotoPos(0);
            $this->triggerCurrentRecord();
        }
    }
    
    function refreshCurrent() {
        if ($this->canMove()) {
            if ($this->getRecordNo() < ($this->getRecordsCount())) {
                $this->intAdvance(0);
                $this->triggerCurrentRecord();
            }
        }
    }
    
    function gotoStart() {
        if ($this->canMove()) {
            $this->intGotoPos(-1);
            $this->triggerCurrentRecord();
        }
    }
    
    function gotoLast() {
        if ($this->canMove()) {
            $this->intGotoPos($this->getRecordsCount() - 1);
            $this->triggerCurrentRecord();
        }
    }
    
    function gotoEnd() {
        if ($this->canMove()) {
            $this->intGotoPos($this->getRecordsCount());
            $this->triggerCurrentRecord();
        }
    }
    
    
    function setRecordNo($number) {
        if ($this->canMove()) {
            if ($number < 0) $number = 0;
            elseif ($number >= $this->getRecordsCount()) $number = $this->getRecordsCount() - 1;
            $this->intGotoPos($number);
            $this->triggerCurrentRecord();
        }
    }
    
    function setKey($pk) {
        if ($this->canMove()) {
            $this->currentRecord = false;
            $this->recordNo = false;
            $this->dirty = false;
            $this->isNew = false;
            $this->currentKey = $pk;
            $this->triggerCurrentRecord();
        }
    }
    
    
//  DataSource: navigation info 
    
    function getRecordNo() {
        if ($this->recordNo === false) {
            if ($this->currentKey === false) {
                 if ($this->currentRecord && $this->currentRecord->isPersistent()) $this->currentKey = $this->currentRecord->getPrimaryKey();
            }
            if ($this->currentKey !== false) $this->recordNo = $this->getRecordNumberByKey($this->currentKey);
        }
        return $this->recordNo;
    }
    
    function getRecordsCount() {
        if ($this->recordsCount === false) {
            $coll = & $this->createCollection();
            if ($this->groupBy && !$this->having && $this->dontGroupOnCount) $coll->setGroupBy(false);
            $this->recordsCount = $coll->countRecords();
            if ($this->debug) Pm_Conversation::log($this->responderId, "Count is {$this->recordsCount}: ".$coll->getStatementTail());
        }
        return $this->recordsCount;
    }
    
    function isStart() {
        return $this->getRecordNo() < 0;
    }
    
    function isEnd() {
        return $this->getRecordNo() >= $this->getRecordsCount();
    }
    
//  DataSource: capabilites info    

    function canMove() {
        return $this->isCancel || !$this->isNew && !$this->dirty;
    }
    
    function canCreate() {
        return !$this->readOnly && $this->canMove();
    }
    
    function canEdit() {
        return !$this->readOnly && true;
    }
    
    function canSave() {
        return !$this->readOnly && ($this->isNew || $this->dirty);
    }
    
    function canCancel() {
        return !$this->readOnly && $this->isNew || $this->dirty;
    }
    
    function canDelete() {
        return !$this->readOnly && !$this->isNew && !$this->isStart() && !$this->isEnd();
    }
    
    function hasRecord() {
        return (bool) $this->getCurrentRecord();
    }
    
//  DataSource: implementation functions
    
    /**
     * @return Ae_Model_Collection
     */
    function createCollection() {
        $coll = new Ae_Model_Collection($this->mapperClass, false, $this->where? "(".implode(") AND (", $this->where).")" : '', false, $this->extraJoins);
        if ($this->having) $coll->setHaving($this->having);
        if ($this->restrictions) {
        	$hasFalse = false;
        	foreach ($this->restrictions as $columns => $value) if ($value === false) { $hasFalse = true; break; }
        	if ($hasFalse) $coll->addWhere('1 = 0');
        	else
            	$coll->addWhere($this->getLegacyDb()->sqlKeysCriteria(array(array_values($this->restrictions)), array_keys($this->restrictions), $this->alias), $coll->getAlias());
        }
        if (strlen($this->alias)) $coll->setAlias($this->alias);
        $coll->setOrder($this->getEffectiveOrdering());
        if ($this->distinct) $coll->setDistinct();
        if ($this->groupBy) $coll->setGroupBy($this->groupBy);
        return $coll;
    }
    
    protected function doOnSleep() {
        $this->mapper = false;
        $this->sqlDb = false;
        $this->legacyDb = false;
        $this->collection = false;
        return parent::doOnSleep();
    }
    
    function __clone() {
        parent::__clone();
        $this->collection = false;
    }
    
    protected function intReset() {
        $this->currentRecord = false;
        $this->currentKey = false;
        //$this->ordering = array();
        $this->recordsCount = false;
        $this->dirty = false;
        $this->isNew = false;
        $this->mapper = false;
        $this->isOpen = false;
        if ($this->lister) $this->lister->refresh();
    }
    
    function getEffectiveOrdering($od = false) {
        if ($od === false) $od = $this->getOrderingWithDirections($this->ordering, true);
        $res = $this->ordering;
        /*
         * We have to determine last ordering part' direction to figure out how to sort by PK.
         * Otherwise reverse ordering won't always result in reverse order of elements. 
         */
        $lastDir = true;
        if (($c = count($od[1]))) {
            $lastDir = $od[1][$c - 1];
        }
        $aeDb = $this->getLegacyDb();
        foreach ($this->getMapper()->listPkFields() as $f) {
            if (!in_array("{$this->alias}.".$f, $od[0]) && !in_array($aeDb->nameQuote($this->alias).'.'.$aeDb->nameQuote($f), $od[0]))
                $res[] = "{$this->alias}.". $f.($lastDir? ' ASC' : ' DESC');
        }
        return $res;
    }
    
    function getOrderingWithDirections($ordering, $separate = false) {
        $res = array();
        foreach ($ordering as $o) {
            if (strtolower(substr($o, -5)) == ' desc') {
                $r = array(substr($o, 0, -5), false);
            } elseif (strtolower(substr($o, -4)) == ' asc') {
                $r = array(substr($o, 0, -4), true);
            } else {
                $r = array($o, true);
            }
            $res[] = $r;
        }
        if ($separate) {
            $ords = array();
            $dirs = array();
            foreach ($res as $r) {
                $ords[] = $r[0];
                $dirs[] = $r[1];
            }
            $res = array($ords, $dirs);
        }
        return $res;
    }
    
    function locateRecordByPrimaryKey($primaryKey, $goto = false) {
        $res = $this->getRecordNumberByKey($primaryKey);
        if ($goto && ($res !== false)) $this->setRecordNo($res);
        return $res;
    }
    
    /**
     * Finds ordinal number of the record identified by primary key. Returns FALSE if record isn't found
     */
    protected function getRecordNumberByKey($pk) {
        $res = $this->lister->getRecordNumberByKey($pk);
        return $res;
    }
    
    protected function getKeyByRecordNumber($number, $returnWholeRecord = false) {
        return $this->lister->getKeyByRecordNumber($number, $returnWholeRecord);
    }
    
    protected function intAdvance($number = 1) {
        if (is_numeric($no = $this->getRecordNo())) $this->intGotoPos($no + $number);
    }
    
    protected function intGotoPos($newNumber) {
        $this->currentRecord = false;
        $this->currentKey = false;
        $this->recordNo = max(-1, $newNumber);
        $this->recordNo = min($this->recordNo, $this->getRecordsCount());
    }
    
    protected function intGotoKey($newKey) {
        $this->recordNo = false;
        $this->currentRecord = false;
        $this->dirty = false;
        $this->currentKey = $newKey;
    }
    
    protected function hasJsObject() {
        return false;
    }
    
    /**
     * @return Ae_Sql_Db
     */
    function getSqlDb() {
        if ($this->sqlDb === false) {
            $this->sqlDb = $this->getApplication()->getDb();
        }
        return $this->sqlDb;
    }
    
    /**
     * @return Ae_Legacy_Database
     */
    function getLegacyDb() {
        if ($this->legacyDb === false) {
            $this->legacyDb = $this->getApplication()->getLegacyDatabase();
        }
        return $this->legacyDb;
    }
    
    protected function intOpen() {
        $this->isOpen = true;
        $this->triggerEvent('onRefresh');
    }
    
    protected function triggerCurrentRecord() {
        if (!$this->isOpen) {
            $this->intOpen();
        }
        $this->navigated = true;
        $this->triggerEvent('onCurrentRecord');
        $this->triggerEvent('afterCurrentRecord');
    }
    
    function setReadOnly($readOnly) {
        if ($readOnly !== ($oldReadOnly = $this->readOnly)) {
            $this->readOnly = $readOnly;
            if ($this->debug) Pm_Conversation::log($this->getResponderId().' / '.$this->id.' - setting readOnly status to '.($readOnly? 'true' : 'false'));
            $this->triggerEvent('onReadOnlyStatusChange', array('oldReadOnly' => $oldReadOnly, 'readOnly' => $readOnly));
        }
    }

    function getReadOnly() {
        return !$this->isOpen || $this->readOnly;
    }    
    
    function reload($holdMode = Pmt_Data_Source::HOLD_NUMBER) {
        if ($holdMode === self::HOLD_NUMBER) {
            $hold = $this->getRecordNo();
        }
        elseif ($holdMode === self::HOLD_KEY) {
            $holdNo = $this->getRecordNo();
            if ($this->currentRecord) $holdKey = $this->currentRecord->getPrimaryKey();
                else $holdKey = $this->currentKey;
        }
        $this->close();
        if ($holdMode === self::HOLD_NUMBER) {
            $this->setRecordNo($hold);
        }
        elseif (($holdMode === self::HOLD_KEY)) {
            if ($holdKey !== false) $n = $this->getRecordNumberByKey($holdKey); else $n = $holdNo;
            $this->setRecordNo($n);
        }
        $this->open();
    }

    function setDistinct($distinct) {
        if ($distinct !== ($oldDistinct = $this->distinct)) {
            $this->distinct = $distinct;
            $this->intReset();
        }
    }

    function getDistinct() {
        return $this->distinct;
    }

    function setGroupBy($groupBy) {
        if ($groupBy !== ($oldGroupBy = $this->groupBy)) {
            $this->groupBy = $groupBy;
            $this->intReset();
        }
    }
    
    function getGroupBy() {
        return $this->groupBy;
    }

    function setDontGroupOnCount($dontGroupOnCount) {
        if ($dontGroupOnCount !== ($oldDontGroupOnCount = $this->dontGroupOnCount)) {
            $this->dontGroupOnCount = $dontGroupOnCount;
            $this->intReset();
        }
    }

    function getDontGroupOnCount() {
        return $this->dontGroupOnCount;
    }    

    function isResidentResponder() {
        return true;
    }
    
    function setMonitoredMappers(array $monitoredMappers) {
    	$this->monitoredMappers = $monitoredMappers;
    }

    function getMonitoredMappers() {
        return $this->monitoredMappers;
    }   

    function endQueue($asResidentResponder = false) {
    	if ($asResidentResponder) {
    	    $mt = $this->getMapper()->getLastUpdateTime();
    	    foreach ($this->monitoredMappers as $m) {
    	        if (($t = $this->getApplication()->getMapper($m)->getMtime()) > $mt) $mt = $t; 
    	    }
        	if ($this->lastMtime < ($mt)) {
        		$this->lastMtime = $mt;
        		if ($this->canMove()) $this->reload(self::HOLD_KEY);
        	}
    	}
    }

    function setGroupSize($groupSize) {
        if ($groupSize !== ($oldGroupSize = $this->groupSize)) {
            $this->groupSize = $groupSize;
            $this->triggerEvent('onGroupSizeChange', array('groupSize' => $this->groupSize));
        }
    }

    function getGroupSize() {
        return $this->groupSize;
    }

    function setForceRestrictionsOnStore($forceRestrictionsOnStore) {
        $this->forceRestrictionsOnStore = $forceRestrictionsOnStore;
    }

    function getForceRestrictionsOnStore() {
        return $this->forceRestrictionsOnStore;
    }    

    protected function setLister($lister) {
        if (!(is_null($lister) || is_array($lister) || $lister instanceof Pmt_Data_Source_Lister))
            throw new Exception("\$lister should be either Pmt_Data_Source_Lister instance or array (it's prototype)");
        if (is_null($lister)) $lister = array();
        if (is_array($lister)) {
            $lister = Pmt_Autoparams::factory($lister, 'Pmt_Data_Source_Lister');
        }
        $this->lister = $lister;
        $this->lister->setDataSource($this);
    }

    function getLister() {
        return $this->lister;
    }    
    
}

?>