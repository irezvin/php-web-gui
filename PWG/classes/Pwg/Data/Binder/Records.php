<?php

class Pwg_Data_Binder_Records extends Pwg_Data_Binder {

    const IN_VIEW = 0;
    const BEFORE_VIEW = -1;
    const AFTER_VIEW = 1;
    
    const TRACK_OFFSET = 0;
    const TRACK_KEY = 1;
    
    protected $positionTracking = false;
    
    /**
     * @var Pwg_I_Control_RecordsDisplay
     */
    protected $dataControl = false;
    
    /**
     * @var Pwg_I_Control_Paginator
     */
    protected $paginator = false;
    
    protected $offset = false;
    
    protected $limit = false;
    
    protected $updateLevel = 0;
    
    protected $hasToUpdateRecords = false;
    
    // Note: partial refresh is not implemented yet with $offset and $limit support
    protected $fullRefreshOnOperations = true;
    
    protected $trackMode = self::TRACK_KEY;
    
//  Datasource related methods & events 
    
    function setDataControl(Pwg_I_Control_RecordsDisplay $v) {
    	$res = parent::setDataControl($v);
    	$this->refreshRecordPrototypeOfDataControl();
    	return $res;
    }
    
    function setDataSource(Pwg_Data_Source $v) {
        $n = substr(__FUNCTION__, 3); $n{0} = strtolower($n{0}); $ov = $this->$n; $this->$n = $v;
        if ($ov !== $v) {
            if ($v) {
                $this->intSetDataSource($v);
            }
        }
    }
    
    function handleDataSourceRefresh(Pwg_Data_Source $dataSource, $eventType, $params) {
        parent::handleDataSourceRefresh($dataSource, $eventType, $params);
        if ($this->dataControl) $this->updateDataControlRecords();          
    }

    function handleDataSourceCurrentRecord(Pwg_Data_Source $source, $eventType, $params = array()) {
        $r = $source->getCurrentRecord();
        if (!$r) $r = null;
        $this->internalSetRecord($r);
    }
    
    function handleDataSourceUpdateRecord(Pwg_Data_Source $source, $eventType, $params = array()) {
        $this->updateDataControlCaps();
    }
    
    function handleDataSourceStoreRecord(Pwg_Data_Source $dataSource, $eventType, $params) {
        if ($this->fullRefreshOnOperations) {
            $this->refreshView();
        } else {
            // TODO
            if ($this->dataControl) {
                $rec = $dataSource->getCurrentRecord();
                $recIndex = $params['index'];
                if ($dataSource->isNew()) {
                    $this->dataControl->addRecord($rec, $recIndex);
                } else {
                    $this->dataControl->updateRecord($rec, $recIndex);
                }
            }
        }
    }
    
    function handleDataSourceValidateRecord(Pwg_Data_Source $dataSource, $eventType, $params) {
//      if ($this->dataControl) {
//          $rec = $dataSource->getCurrentRecord();
//          $errors = $params['errors'];
//          $this->dataControl->setRecordErrors($rec, $errors);
//      }
    }
    
    function handleDataSourceDeleteRecord(Pwg_Data_Source $dataSource, $eventType, $params) {
        if ($this->fullRefreshOnOperations) {
            $this->refreshView();
        } elseif ($this->dataControl) {
            // TODO
            $rec = $dataSource->getCurrentRecord();
            $this->dataControl->deleteRecord($rec);
        }
    }
    
    protected function refreshView() {
        if ($this->dataControl) {
            $currRecord = $this->dataSource->getCurrentRecord();
            $this->updateDataControlRecords();
            if ($currRecord) $this->dataControl->setCurrentRecord($currRecord);
        }
    }
    
    protected function updateRecordInView($record, $newIndex) {

        // TODO
        
        // case A: new index is out of view
        
        $inView = $this->checkIndexInView($newIndex);
        
        if ($inView == self::IN_VIEW) {
            
        } else {
            // new index is out of view
            
            $this->deleteRecordFromView($record, $inView == self::BEFORE_VIEW? true : false);
            
        }
        
        $indexInControl = $this->dataControl->getRecordIndex($record);
        if (!$indexInControl !== false) {
            
        }
    }
    
    /**
     * Removes records from the window;
     * 
     *
     * @param Ae_Model_Object $record
     * @param bool $addFromHead Whether to add new records from the start or from the end of the list 
     */
    protected function deleteRecordFromView($record, $addInStart = false) {
        $this->dataControl->deleteRecord($record);
        
    }

    protected function checkIndexInView($indexInDataSource) {
        $index = $indexInDataSource;
        $offset = $this->offset !== false? $this->offset : 0;
        $limit = $this->limit !== false? $this->limit : $index + 1;
        if ($index < $offset) $res = self::BEFORE_VIEW;
        if ($index >= $limit) $res = self::AFTER_VIEW;
        else $res = self::IN_VIEW;
        return $res;
    }
    
    /**
     * Generally, removes $srcRecord from $srcPosition and replaces it with $destRecord in $destPosition.
     * $srcRecord and $destRecord can be the same; both $srcRecord and $destRecord can be skipped. 
     *
     * @param Ae_Model_Object $srcRecord Can be NULL
     * @param Ae_Model_Object  $destRecord Can be NULL
     * @param int $srcPosition If FALSE, will be determined automatically
     * @param int $destPosition If FALSE, will be determined automatically
     */
    protected function transformRecordView($srcRecord, $destRecord, $srcPosition, $destPosition) {
        $strSrcRecord = $srcRecord instanceof Ae_Model_Object? '#'.$srcRecord->getPrimaryKey() : 'none';
        $strDestRecord = $destRecord instanceof Ae_Model_Object? '#'.$destRecord->getPrimaryKey() : 'none';
        if ($this->getController())
            $this->getController()->logMessage("Transforming record view: replacing {$strSrcRecord} in '{$srcPosition}' to {$strDestRecord} in '{$destPosition}'");
    }
    
    protected function intSetDataSource() {
        parent::intSetDataSource();
        $this->dataSource->observe('onStoreRecord', $this, 'handleDataSourceStoreRecord');
        $this->dataSource->observe('onValidateRecord', $this, 'handleDataSourceValidateRecord');
        $this->dataSource->observe('onDeleteRecord', $this, 'handleDataSourceDeleteRecord');
        $this->dataSource->observe('onUpdateRecord', $this, 'handleDataSourceUpdateRecord');
        
        $this->refreshRecordPrototypeOfDataControl();
        
        if ($this->dataControl) {
            $this->updateDataControlRecords();
        }
    }
    
    protected function refreshRecordPrototypeOfDataControl() {
    	if ($this->dataSource && ($m = $this->dataSource->getMapper()) && ($c = $this->dataControl)) {
    	    $prototype = & $m->factory();
    	    $prototype->bind($this->dataSource->getRestrictions());
    	    $c->setRecordPrototype($prototype);
    	}
    }
    
//  DataControl related methods & events ---
    
    protected function updateDataControlCaps() {
        
        $canMove = null;
        $canCreate = null;
        $canEdit = null;
        $canSave = null;
        $canCancel = null;
        $canDelete = null;
        
        if ($dc = $this->dataControl) {
            if ($ds = $this->dataSource) {
                $canMove = $ds->canMove();
                $canCreate = $ds->canCreate();
                $canEdit = $ds->canEdit();
                $canSave = $ds->canSave();
                $canDelete = $ds->canDelete();
                $canCancel = $ds->canCancel();
            } else {
                $canMove = false;
                $canCreate = false;
                $canEdit = false;
                $canSave = false;
                $canCancel = false;
                $canDelete = false;
            }
            $dc->setCurrentCaps($canMove, $canCreate, $canEdit, $canSave, $canCancel, $canDelete);
        }
        
    }
    
    protected function updateDataControlRecords() {
        if (!$this->updateLevel) {
            $this->hasToUpdateRecords = false;
            $records = array(); 
            if ($this->dataSource) {
                if (!$this->dataSource->isOpen()) {
                    $this->dataSource->open();
                }
                /*
                $myDataSource = clone $this->dataSource;
                if (!$this->offset) $myDataSource->gotoFirst();
                    else $myDataSource->setRecordNo($this->offset);
                while ((($this->limit === false) || (count($records) < $this->limit)) && ($r = $myDataSource->getCurrentRecord())) {
                    $records[] = $r;
                    $myDataSource->gotoNext();
                }
                */
                $records = $this->dataSource->getRecords($this->offset, $this->limit);
            } else {
            }
            if ($this->dataControl) {
                $this->dataControl->setRecords($records);
                $this->updateDataControlCaps();
                if ($this->dataSource && ($rec = $this->dataSource->getCurrentRecord())) {
                    //if ($this->getController()) $this->getController()->logMessage("Setting current record with ID ", $rec->getPrimaryKey());
                    $this->dataControl->setCurrentRecord($rec);
                }
            }
        } else {
            $this->hasToUpdateRecords = true;
        }
    }
    
    function beginUpdate() {
        $this->updateLevel++;
    }
    
    function endUpdate() {
        if ($this->updateLevel > 0) {
            $this->updateLevel--;
        }
        if (!$this->updateLevel && $this->hasToUpdateRecords) $this->updateDataControlRecords(); 
    }
    
    function handleDataControlRecordCreated(Pwg_I_Control_RecordsDisplay $dataControl, $eventType, $params) {
        if ($this->dataSource) {
            if (isset($params['data'])) {
                $data = $params['data'];    
            } else {
                $rec = $dataControl->getCurrentRecord();
                $data = $rec->getDataFields();
            }
            $this->dataSource->createRecord($data);
        } else $dataControl->cancelCurrentAction();
    }
    
    function handleDataControlRecordEdited(Pwg_I_Control_RecordsDisplay $dataControl, $eventType, $params) {
        if ($this->dataSource && $this->dataSource->canEdit()) {
            if (isset($params['data'])) {
                $data = $params['data'];    
            } else {
                $rec = $dataControl->getCurrentRecord();
                $data = $rec->getDataFields();
            }
            $this->dataSource->updateCurrentRecord($data);
        } else $dataControl->cancelCurrentAction();
    }
    
    function handleDataControlRecordRemoved(Pwg_I_Control_RecordsDisplay $dataControl, $eventType, $params) {
        if ($this->dataSource && $this->dataSource->canDelete()) {
            $rec = $dataControl->getCurrentRecord();
            $rec->delete();
        } else $dataControl->cancelCurrentAction();
    }
    
    function handleDataControlRecordSelected(Pwg_I_Control_RecordsDisplay $dataControl, $eventType, $params) {
        if ($this->dataSource && $this->dataSource->canMove()) {
            $idx = false;
            if ($rec = $dataControl->getCurrentRecord()) {
                $idx = $this->dataSource->locateRecordByPrimaryKey($rec->getPrimaryKey());
            }
            if ($idx !== false) {
                $this->dataSource->setRecordNo($idx);
                $this->triggerEvent('recordSelected', array('index' => $idx));
            } else {
                $dataControl->cancelCurrentAction();
            }
        } else {
            $dataControl->cancelCurrentAction();
        }
    }
    
    /**
     * Overrides behaviour of parent function
     */
    protected function internalSetRecord(Ae_Model_Object $record = null) {
        $this->currentRecord = $record;
        if ($this->dynamicPropInfo) $this->updatePropInfo();
        if ($this->dataControl) $this->dataControl->setCurrentRecord($record);
    }
    
    /**
     * Overrides behavior of parent function
     */
    protected function intSetDataControl() {
        //if ($this->controlChangeEvents) $this->subscribeToControlChangeEvents($this->controlChangeEvents);
        $this->dataControl->observeRecordCreated($this, 'handleDataControlRecordCreated');
        $this->dataControl->observeRecordEdited($this, 'handleDataControlRecordEdited');
        $this->dataControl->observeRecordRemoved($this, 'handleDataControlRecordRemoved');
        $this->dataControl->observeRecordSelected($this, 'handleDataControlRecordSelected');
                
        $this->refreshDataControlFromPropInfo();
        //$this->refreshDataControlFromData();
        $this->updateDataControlRecords();
        $this->updateDataControlCaps();
    }

//  +--------------- paginator support methods ------------+    
    
    function setOffset($offset) {
        if ($offset !== ($oldOffset = $this->offset)) {
            $this->offset = $offset;
            $this->updateDataControlRecords();
        }
    }

    function getOffset() {
        return $this->offset;
    }

    function setLimit($limit) {
        if ($limit !== ($oldLimit = $this->limit)) {
            $this->limit = $limit;
            $this->updateDataControlRecords();
        }
    }

    function getLimit() {
        return $this->limit;
    }   
    
    function setPaginator(Pwg_I_Control_Paginator $paginator = null) {
        if ($paginator !== ($oldPaginator = $this->paginator)) {
            if ($oldPaginator) {
                $oldPaginator->unobserveLimitChanged($this, 'handlePaginatorChange');
                $oldPaginator->unobserveOffsetChanged($this, 'handlePaginatorChange');
            }
            if ($this->paginator = $paginator) {
                $paginator->observeLimitChanged($this, 'handlePaginatorChange');
                $paginator->observeOffsetChanged($this, 'handlePaginatorChange');
                $this->updateLimitsFromPaginator();
            }
        }
    }

    /**
     * @return Pwg_I_Control_Paginator
     */
    function getPaginator() {
        return $this->paginator;
    }

    function setPaginatorPath($paginatorPath) {
        $this->associations['paginator'] = $paginatorPath; 
    }

    protected function updateLimitsFromPaginator() {
        $this->beginUpdate();
        $this->setOffset($this->paginator->getOffset());
        $this->setLimit($this->paginator->getLimit());
        $this->endUpdate();
    }
    
    function handlePaginatorChange(Pwg_I_Control_Paginator $paginator, $eventType, $params) {
        $this->updateLimitsFromPaginator();
    }
    
}

?>