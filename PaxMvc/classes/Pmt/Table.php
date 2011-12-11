<?php

class Pmt_Table extends Pmt_Controller implements Pmt_I_Control_RecordsDisplay {
    
    const evtRowDblClick = 'rowDblClick';
    
    protected $shownFieldsList = false;

    protected $rowClass = 'Pmt_Table_Row';
    
    protected $rows = array();
    
    protected $multiple = false;
    
    protected $selectedRows = array();
    
    //protected $rowset = false;
    
    protected $colset = false;
    
    protected $columnPrototypes = false;
    
    protected $dataSourceJsClass = 'YAHOO.util.DataSource';

    protected $responders = array();
    
    protected $selectedIndice = array();
    
    // Js support vars
    
    protected $caption = false;

    protected $currencyOptions = false;

    protected $currencySymbol = false;

    protected $dateOptions = false;

    protected $draggableColumns = false;

    protected $dynamicData = true;

    protected $formatRow = false;

    protected $generateRequest = false;

    protected $initialLoad = false;

    protected $initialRequest = false;

    protected $MSG_EMPTY = false;

    protected $MSG_ERROR = false;

    protected $MSG_LOADING = false;

    protected $MSG_SORTASC = false;

    protected $MSG_SORTDESC = false;

    protected $numberOptions = false;

    protected $paginator = false;

    protected $renderLoopSize = false;

    protected $selectionMode = false;

    protected $summary = false;
    
    /**
     * @var bool|array(colName, dir)
     */
    protected $sortMode = false;
    
    protected $scrollable = false;

    protected $width = false;

    protected $height = false;

    /**
     * @var Pmt_I_Record
     */
    protected $metadataProvider = false;

    protected $visible = true;
    
        
    function hasEditableColumns() {
        $res = false;
        $cs = $this->getColset();
        foreach ($cs->listControls() as $i) {
            $column = $cs->getControl($i);
            if ($column->getHasEditor()) {
                $res = true;
                break;
            }
        }
        return $res;
    }
    
    protected function setColumnPrototypes(array $pc = array()) {
        $this->columnPrototypes = $pc;
    }
    
    protected function getColumnPrototypes() {
        return is_array($this->columnPrototypes)? $this->columnPrototypes : array(); 
    }
    
    protected function getControlPrototypes() {
        $res = array(
//          'rowset' => array(
//              'class' => 'Pmt_Table_Recset',
//          ), 
            'colset' => array(
                'class' => 'Pmt_Table_Colset',
                'controlPrototypes' => $this->getColumnPrototypes(),
            ),
        );
        return $res;
    }
    
    function clearRows() {
        foreach ($this->rows as $row) $row->destroy();
        $this->rows = array();
        $this->sendMessage('setRows', array(new Ae_Js_Var('[]')));
        //foreach ($this->rows as $row) $row->destroy();
    }
    
    function setRecordRows(array $records = array()) {
        //trigger_error("Setting record rows // ".count($records), E_USER_NOTICE);
        $this->createDisplayParentImpl();
        $this->lockMessages();
        $this->clearRows();
        $this->unlockMessages();
        foreach ($records as $record) $this->rows[] = new Pmt_Table_Row($this, $record);
        $this->triggerEvent('onSendRows');
        $tmp = $this->lockMessages;
        $this->lockMessages = 0;
        $this->sendMessage('setRows', array($json = $this->getRowsJson()));
        $this->lockMessages = $tmp;
    }
    
    function getRowsJson($withKeys = false) {
        //return $this->rows;
        $data = array();
        foreach ($this->colset->listControls() as $i) {
            $col = $this->colset->getControl($i);
            if (!$col->getHidden()) {
                foreach ($col->getColData() as $k => $v) {
                    $data[$k][$i] = $v; 
                }
            }
        }
        foreach (array_keys($data) as $id) $data[$id] = array_merge(array('__aeUid' => $id), $data[$id]);
        if ($withKeys) $res = $data;
            else $res = array_values($data);
        return $res;
    }
    
    function triggerFormatRow(Pmt_Table_Row $row, Pmt_I_Record $record, array & $rowData) {
    	$this->triggerEvent('onFormatRow', array('row' => $row, 'record' => $record, 'rowData' => & $rowData));
    }
    
    /**
     * @return Pmt_Table_Colset
     */
    function getColset() {
        return $this->getControl('colset');
    }
    
//  /**
//   * @return Pmt_Table_Recset
//   */
//  function getRowset() {
//      return $this->getControl('rowset');
//  }
    
    // Js-object related functions
    
    function doGetAssetLibs() {
        return array_merge(parent::doGetAssetLibs(), array(
            'widgets.js',
            '{YUI}/fonts/fonts-min.css',
            '{YUI}/datatable/assets/skins/sam/datatable.css',
            '{YUI}/yahoo/yahoo.js',
            '{YUI}/dom/dom.js',
            '{YUI}/event/event.js',
            '{YUI}/dragdrop/dragdrop.js',
            '{YUI}/element/element.js',
            '{YUI}/datasource/datasource.js',
            '{YUI}/datatable/datatable.js',
            '{YUI}/menu/assets/skins/sam/menu.css',
            '{YUI}/container/container.js',
            '{YUI}/menu/menu.js',
            'widgets/yui/util.js',
            'widgets/table.js',
        ));
    }
    
    function doListPassthroughParams() {
        $res = array_merge(parent::doListPassthroughParams(), array(
            'columnDefs',
            'dataSource',
            'rowsJson' => 'rows',
            'configs',
            'initializerFn',
            'sortMode',
            'selectedIndice',
            'toggleableColumns',
        ));
        return $res;
    }
    
    function getToggleableColumns() {
        $res = array();
        foreach ($this->getColset()->listControls() as $i) {
            $col = $this->getColset()->getControl($i);
            if ($col->getCanToggle()) {
                $res[] = array('key' => $i, 'label' => $col->getLabel());
            }
        }
        return $res;
    }
    
    function getInitializerFn() {
        $res = false;
            ob_start();
?>
<?php       if ($this->hasEditableColumns()) { ?> 
            this.yuiTable.subscribe("cellClickEvent", this.yuiTable.onEventShowCellEditor);
<?php       } ?>
<?php
            $res = new Ae_Js_Var('function() { '.ob_get_clean().'}');
        return $res;
    }
    
    protected function getColumnDefs() {
        $res = array(); 
        $cs = $this->getColset();
        foreach ($cs->getOrderedDisplayChildren() as $column) {
            //if (!$column->getHidden())
                $res[] = $column->toJs();
        }
        return $res;
    }
    
    protected function getDataJson() {
        $res = array();
//      $cs = $this->getColset();
//      $fieldList = array();
//      foreach ($cs->listControls() as $i) {
//          $column = $cs->getControl($i);
//          $fieldList[$column->getId()] = $column->getFieldName();
//      }
//      
//      foreach ($this->rows as $row) {
//          $rec = $row->getRecord();
//          $aRow = array('__aeUid' => $rec->getUid());
//          foreach ($fieldList as $colId => $f) {
//              $aRow[$colId] = $rec->getField($f);
//          }
//          $res[] = $aRow;
//      }
//      
////        foreach ($this->getRowset()->listControls() as $i) {
////            $row = $this->getRowset()->getControl($i);
////            $rec = $row->getRecord();
////            $aRow = array('__aeUid' => $rec->getUid());
////            foreach ($fieldList as $colId => $f) {
////                $aRow[$colId] = $rec->getField($f);
////            }
////            $res[] = $aRow;
////        }
        return $res;
    }
    
    protected function getDataSource() {
        $dsParams = array($this->getDataJson(), array(
            'responseType' => new Ae_Js_Var('YAHOO.util.DataSource.TYPE_JSARRAY'),
            'responseSchema' => array('fields' => array_merge(array('__aeUid'), $this->getColset()->listControls())),
        ));
        $res = new Ae_Js_Call($this->dataSourceJsClass, $dsParams, "YAHOO.util.DataSource");
        return $res;
    }
    
    protected function getConfigs() {
        $configsParams = array(     
            'caption',
            'currencyOptions',
            'currencySymbol',
            'dateOptions',
            'draggableColumns',
            'dynamicData',
            'formatRow',
            'initialLoad',
            'initialRequest',
            'MSG_EMPTY',
            'MSG_ERROR',
            'MSG_LOADING',
            'MSG_SORTASC',
            'MSG_SORTDESC',
            'numberOptions',
            'paginator',
            'renderLoopSize',
            'selectionMode',
            'sortedBy',
            'summary',
            'scrollable', 
            'width',
            'height'
        );
        return $this->getPassthroughParams($configsParams, false, false);
    }
    
    /**
     * @return Pmt_I_Record
     */
    function createRecord() {
        $data = array();
        return new Pmt_Record_Array($data);
    }
    
    protected function setDataSourceJsClass($dataSourceJsClass) {
        $this->dataSourceJsClass = $dataSourceJsClass;
    }

    function getDataSourceJsClass() {
        return $this->dataSourceJsClass;
    }   
    
    // Js support methods

    function setCaption($caption) {
        $this->caption = $caption;
    }

    function getCaption() {
        return $this->caption;
    }

    function setCurrencyOptions($currencyOptions) {
        $this->currencyOptions = $currencyOptions;
    }

    function getCurrencyOptions() {
        return $this->currencyOptions;
    }

    function setCurrencySymbol($currencySymbol) {
        $this->currencySymbol = $currencySymbol;
    }

    function getCurrencySymbol() {
        return $this->currencySymbol;
    }

    function setDateOptions($dateOptions) {
        $this->dateOptions = $dateOptions;
    }

    function getDateOptions() {
        return $this->dateOptions;
    }

    function setDraggableColumns($draggableColumns) {
        $this->draggableColumns = $draggableColumns;
    }

    function getDraggableColumns() {
        return $this->draggableColumns;
    }

    function setDynamicData($dynamicData) {
        $this->dynamicData = $dynamicData;
    }

    function getDynamicData() {
        return $this->dynamicData;
    }

    function setFormatRow($formatRow) {
        $this->formatRow = $formatRow;
    }

    function getFormatRow() {
        return $this->formatRow;
    }

    function setGenerateRequest($generateRequest) {
        $this->generateRequest = $generateRequest;
    }

    function getGenerateRequest() {
        return $this->generateRequest;
    }

    function setInitialLoad($initialLoad) {
        $this->initialLoad = $initialLoad;
    }

    function getInitialLoad() {
        return $this->initialLoad;
    }

    function setInitialRequest($initialRequest) {
        $this->initialRequest = $initialRequest;
    }

    function getInitialRequest() {
        return $this->initialRequest;
    }

    function setMSG_EMPTY($MSG_EMPTY) {
        $this->MSG_EMPTY = $MSG_EMPTY;
    }

    function getMSG_EMPTY() {
        return $this->MSG_EMPTY;
    }

    function setMSG_ERROR($MSG_ERROR) {
        $this->MSG_ERROR = $MSG_ERROR;
    }

    function getMSG_ERROR() {
        return $this->MSG_ERROR;
    }

    function setMSG_LOADING($MSG_LOADING) {
        $this->MSG_LOADING = $MSG_LOADING;
    }

    function getMSG_LOADING() {
        return $this->MSG_LOADING;
    }

    function setMSG_SORTASC($MSG_SORTASC) {
        $this->MSG_SORTASC = $MSG_SORTASC;
    }

    function getMSG_SORTASC() {
        return $this->MSG_SORTASC;
    }

    function setMSG_SORTDESC($MSG_SORTDESC) {
        $this->MSG_SORTDESC = $MSG_SORTDESC;
    }

    function getMSG_SORTDESC() {
        return $this->MSG_SORTDESC;
    }

    function setNumberOptions($numberOptions) {
        $this->numberOptions = $numberOptions;
    }

    function getNumberOptions() {
        return $this->numberOptions;
    }

    function setPaginator($paginator) {
        $this->paginator = $paginator;
    }

    function getPaginator() {
        return $this->paginator;
    }

    function setRenderLoopSize($renderLoopSize) {
        $this->renderLoopSize = $renderLoopSize;
    }

    function getRenderLoopSize() {
        return $this->renderLoopSize;
    }

    function setSelectionMode($selectionMode) {
        $this->selectionMode = $selectionMode;
    }

    function getSelectionMode() {
        return $this->selectionMode;
    }

    function setSummary($summary) {
        $this->summary = $summary;
    }

    function getSummary() {
        return $this->summary;
    }   

//  Pmt_I_Control_RecordsDisplay    
    
    function setRecordPrototype(Ae_Model_Object $record = null) {
    	if (!$record) $this->setMetadataProvider(null);
    		else $this->setMetadataProvider(new Pmt_Record_Ae($record));
    }
    
    function addRecord(Ae_Model_Object $record, $newIndex = false) {
        // TODO
        
//      $this->controller->logMessage("add record", $newIndex, $record->getDataFields());
//      $this->getRowset()->addRecordRow(new Pmt_Record_Ae($record), $newIndex);
    }
    
    function deleteRecord(Ae_Model_Object $record) {
//      // TODO
//      $row = $this->getRowset()->locateRowsByRecord(new Pmt_Record_Ae($record));
//      foreach ($rows as $row) {
//          $this->getRowset()->deleteControl($row);
//      }
    }
    
    function updateRecord(Ae_Model_Object $record, $newIndex = false) {
//      // TODO
//      $rows = $this->getRowset()->locateRowsByRecord(new Pmt_Record_Ae($record));
//      if ($rows) {
//          foreach ($rows as $row) {
//              $row->setRecord($record);
//              if ($newIndex !== false) $row->setDisplayOrder($newIndex);
//          }
//      }
    }

    function setRecords(array $records = array()) {
        $pmRecords = array();
        foreach ($records as $rec) {
            $pmRecords[] = new Pmt_Record_Ae($rec);
        }
        $this->setRecordRows($pmRecords);
    }

    function getRecords() {
        $res = array();
        foreach ($this->rows as $row) {
            if ($rec = $this->getModelObject($row)) {
                $res[] = $rec;
            }
        }
        return $res;
    }
    
    function findRowByAeRecord(Ae_Model_Data $record, $many = false) {
        $res = array();
        $pmtRecord = new Pmt_Record_Ae($record);
        foreach ($this->listRows() as $i) {
            if ($this->getRow($i)->getRecord()->matches($pmtRecord)) {
                $res[] = $this->getRow($i);
                if (!$many) break;
            }
        }
        if (!$many) $res = count($res)? $res[0] : false;
        return $res;
    }
    
    /**
     * @param array(Pmt_Record) $selectedRecords
     */
    function setSelectedRecords($selectedRecords = array()) {
        $newSel = array();
        foreach ($selectedRecords as $rec) {
            $newSel[] = $rec->getUid();
        }
        if ((count($this->selectedIndice) != count($newSel)) || array_diff($this->selectedIndice, $newSel)) {
            $this->selectedIndice = $newSel;
            $this->sendMessage('selectRows', array($newSel), 1);
        }
        
//      $newSel = array();
//      foreach ($selectedRecords as $rec) {
//          $newSel[] = $rec->getUid();
//      }
//      if ($deselected = array_diff($this->selectedIndice, $newSel)) 
//          foreach ($this->getRowset()->getRowsByRecordIds($deselected) as $row) {
//              $row->setSelected(false);
//          }
//          
//      if ($selected = array_diff($newSel, $this->selectedIndice))
//          foreach ($this->getRowset()->getRowsByRecordIds($selected) as $row) {
//              $row->setSelected(true);
//          }
    }
    
    function setSelectedRows(array $rows = array()) {
        $newSel = array();
        foreach ($rows as $row) {
            $newSel[] = $row->getRecord()->getUid();
        }
        if ((count($this->selectedIndice) != count($newSel)) || array_diff($this->selectedIndice, $newSel)) {
            $this->selectedIndice = $newSel;
            $this->sendMessage('selectRows', array($newSel), 1);
        }
    }
    
    function setCurrentRecord(Ae_Model_Object $record = null) {
        $sr = array();
        if ($record) {
            $row = $this->findRowByAeRecord($record);
            if ($row) {
                $sr = array($row->getRecord());
            }
        }
        $this->setSelectedRecords($sr);
    }
    
    function setRecordErrors(Ae_Model_Object $record, array $errors = array()) {
        // skip it...
    }
    
    function cancelCurrentAction() {
        // skip it...
    }
    
    function setCurrentCaps($canMove = null, $canNew = null, $canEdit = null, $canSave = null, $canCancel = null, $canDelete = null) {
        // skip it...
    }
    
    function getCurrentCaps() {
        return array();
    }
    
    function getRecordIndex(Ae_Model_Object $record) {
        $res = false;
        $r = new Pmt_Record_Ae($record);
        foreach ($this->rows as $i => $row) {
            if ($row->matches($r)) {
                $res = $i;
                break;
            }
        }
        return $res;
    }   
    
    function observeRecordSelected (Pm_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array()) {
        return $this->observe('onRecordSelected', $observer, $methodName, $extraParams);
    }

    function unobserveRecordSelected (Pm_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array()) {
        return $this->unobserve('onRecordSelected', $observer, $methodName, $extraParams);
    }
    
    function observeRecordEdited (Pm_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array()) {
        return $this->observe('onRecordEdited', $observer, $methodName, $extraParams);
    }
    
    function unobserveRecordEdited (Pm_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array()) {
        return $this->unobserve('onRecordEdited', $observer, $methodName, $extraParams);
    }
    
    function observeRecordCreated (Pm_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array()) {
        return $this->observe('onRecordCreated', $observer, $methodName, $extraParams);
    }
    
    function unobserveRecordCreated (Pm_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array()) {
        return $this->unobserve('onRecordCreated', $observer, $methodName, $extraParams);
    }

    function observeRecordRemoved (Pm_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array()) {
        return $this->observe('onRecordRemoved', $observer, $methodName, $extraParams);
    }
    
    function unobserveRecordRemoved (Pm_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array()) {
        return $this->unobserve('onRecordRemoved', $observer, $methodName, $extraParams);
    }
    
    /**
     * @return bool
     */
    function started() {
        return $this->conversation && $this->conversation->started();
    }
    
    function start() {
    }
    
    function isPageRender() {
        return $this->conversation && $this->conversation->isPageRender();
    }
    
//  +------------------------------------ table rows support -------------------------------------+

    function setRows(array $rows = array()) {
        foreach(array_keys($this->rows) as $k) $this->removeRow($k);
        foreach($rows as $r) {
            $row = $this->addRow(isset($r['label'])? $r['label'] : false, isset($r['value'])? $r['value'] : false);
            if (isset($r['data'])) $row->setData($r['data']);
        }
    }
    
    /**
     * @param Pmt_I_Record $record
     * @return Pmt_Table_Row
     */
    function addRow(Pmt_I_Record $record = null) {
        $cl = $this->rowClass;
        $res = new $cl($this);
        if ($record !== false) $res->setRecord($record);
        if ($index !== false) $index = min($index, count($this->rows));
        if ($index !== false && ($index < count($this->rows))) {
            $headRows = array_slice($this->rows, 0, $index);
            $tailRows = array_slice($this->rows, $index);
            $headRows[] = $res;
            $this->rows = array_merge($headRows, $tailRows);
        } else {
            $this->rows[] = $res;
        }
        $this->sendMessage(__FUNCTION__, array($res, $index));
        return $res;
    }
    
    function getRowIndex(Pmt_Table_Row $row) {
        $k = $this->getRowKey($row);
        if ($k !== false) {
            $res = array_search($k, array_keys($this->rows));
        } else {
            $res = false;
        }
        return $res;
    }
    
    /**
     * @return Pmt_Table_Row
     */
    function getRowByIndex($index) {
        $res = false;
        $keys = array_keys($this->rows);
        if (isset($keys[$index])) $res = $this->rows[$keys[$index]];
        return $res;
    }
    
    /**
     * @return Pmt_Table_Row
     */
    function getRow($index) {
        return $this->getRowByIndex($index);
    }
    
    function notifyRowUpdated(Pmt_Table_Row $row) {
        if (($index = $this->getRowIndex($row)) !== false) {
            $this->sendMessage('rowUpdated', array($row, $index));
        }
    }
    
//  function notifyRowDeleted(Pmt_Table_Row $row) {
//      if (($index = $this->getRowIndex($row)) !== false) {
//          $this->sendMessage('rowDeleted', array($index));
//      }
//  }
    
    function getRowKey(Pmt_Table_Row $row) {
        $res = false;
        foreach ($this->rows as $k => $rw) {
            if ($row === $rw) {
                $res = $k; 
                break; 
            }
        }
        return $res;
    }
    
    function removeRow($k) {
        if (isset($this->rows[$k])) {
            $idx = $this->getRowIndex($this->rows[$k]);
            unset($this->rows[$k]);
            $this->sendMessage(__FUNCTION__, array($idx));
        } else {
            throw new Exception ("No such row: '{$k}'"); 
        }
    }
    
    function listRows() {
        return array_keys($this->rows);
    }

    protected function notifySelected(Pmt_Table_Row $row) {
        if (($index = $this->getRowIndex($row)) !== false) {
            $this->sendMessage('rowSelected', array($index));
        }
    }
    
    protected function notifyDeselected($row) {
        if (($index = $this->getRowIndex($row)) !== false) {
            $this->sendMessage('rowDeselected', array($index));
        }
    }
    
    function selectRow(Pmt_Table_Row $row) {
        if (!$this->isRowSelected($row)) {
            if (!$this->multiple) {
                foreach ($this->selectedRows as $row) $row->setSelected(false);
                $this->selectedRows = array();
            }
            $this->selectedRows[] = $row;
            $this->doOnRowSelected($row);
            $this->notifySelected($row);
        } else {
        }
    }
    
    function isRowSelected(Pmt_Table_Row $row) {
        $res = false;
        foreach (array_keys($this->selectedRows) as $k) 
            if ($this->selectedRows[$k] === $row) {
                $res = true;
            }
        return $res;
    }
    
    function deselectRow(Pmt_Table_Row $row) {
        foreach ($this->selectedRows as $k => $row) 
            if ($this->selectedRows[$k] === $row) {
                unset($this->selectedRows[$k]); 
                $this->notifyDeselected($row);
                break; 
            }
    }
    
    function listSelectedRows() {
        $res = array();
        foreach ($this->selectedRows as $row) {
            $ok = $this->getRowKey($row);
            if ($ok !== false) $res[] = $ok;
        }
        return $res; 
    }
    
    function getSelectedRowIndices() {
        $res = array();
        foreach ($this->selectedRows as $row) $res[] = $this->getRowIndex($row);
        return $res; 
    }
    
    function getFirstSelectedIndex() {
        $res = false;
        $i = $this->getSelectedRowIndices();
        if (count($i) > 0) $res = $i[0];
        return $res;
    }
    
    /**
     * @return Pmt_Table_Row
     */
    function getFirstSelectedRow() {
        if (strlen($i = $this->getFirstSelectedIndex())) {
            $res = $this->getRow($i);
        } else {
            $res = false;
        }
        return $res;
    }
    
    function resetShownFieldsList() {
        $this->shownFieldsList = false;
    }
    
    function getShownFieldsList() {
        if ($this->shownFieldsList === false) {
            $cs = $this->getColset();
            $fieldList = array();
            foreach ($cs->listControls() as $i) {
                $column = $cs->getControl($i);
                if (!$column->getHidden()) {
                    $fieldList[$column->getId()] = $column->getFieldName();
                }
            }
            $this->shownFieldsList = $fieldList;
        }
        return $this->shownFieldsList;
    }   

    /**
     * @param string $uid
     * @param bool $findIndex Return index instead of the row
     * @return Pmt_Table_Row 
     */
    function findRowByUid($uid, $findIndex = false) {
        $res = false;
        foreach ($this->rows as $idx => $row) {
            if (($rec = $row->getRecord()) && ($rec->getUid() == $uid)) {
                $res = $findIndex? $idx : $row;
                break;
            }
        }
        return $res;
    }
    
    /**
     * @param array $data Mask (array(fieldName => value, fieldName2 => value2...))
     * @param bool $multiple Whether to return multiple values or keys
     * @param bool $findIndex Whether to return index or indice of found records 
     * @param bool $strict Use strict comparison of fields with values in $data 
     * @return Pmt_Table_Row
     */
    function findRowByData(array $data, $multiple = false, $findIndex = false, $strict = false) {
        if ($multiple) $res = array();
            else $res = false;
        foreach ($this->rows as $idx => $row) {
            if (($rec = $row->getRecord())) {
                $match = true;
                foreach ($data as $k => $v) {
                    $eq = $strict? ($rec->getField($k) === $v) : ($rec->getField($k) == $v);
                    if (!$eq) {
                        $match = false; 
                        break;
                    }
                }
                if ($match) {
                    if ($multiple) $res[] = $findIndex? $idx : $row;
                    else {
                        $res = $findIndex? $idx : $row;
                        break;
                    }
                } 
            }
        }
        return $res;
    }
    
    function triggerFrontendSelectionChange($recordUids) {
        $this->lockMessages();
        $sr = array();
        $lsr = false;
        foreach ($recordUids as $uid) {
            if (($row = $this->findRowByUid($uid))) $sr[] = $row;
        }
        $this->selectedRows = $sr;
        $this->triggerEvent('onRecordSelected');
        $this->unlockMessages();
    }
    
    protected function getModelObject(Pmt_Table_Row $row) {
        $res = false;
        if (($rec = $row->getRecord()) && $rec instanceof Pmt_Record_Ae && (($aeRec = $rec->getAeModelData()) instanceof Ae_Model_Object)) {
            $res = $aeRec;
        }
        return $res;
    }
    
    function getCurrentRecord() {
        $res = false;
        if ($this->selectedRows) {
            foreach ($this->selectedRows as $row) {
                if ($aeo = $this->getModelObject($row)) {
                    $res = $aeo;
                    break;
                }
            }
        }
        return $res;
    }
    
    function sendColumnMessage($columnKey, $callName, $params) {
        $params = array_merge(array($columnKey), $params);
        $this->sendMessage($callName.'Column', $params);
    }
    
    function triggerFrontendColumnSortRequest($columnKey, $sort) {
        Pm_Conversation::log("\n\nSort Request", $columnKey, $sort);
    	if ($column = $this->getColset()->getControl($columnKey)) {
            if ($column->getSortable()) {
                $setNewSort = false;
                $sortIsAsc = $sort == 'asc';
                $this->triggerEvent('columnSortRequest', array('column' => $column, 'sortIsAsc' => $sortIsAsc, 'setNewSort' => &$setNewSort));
                if ($setNewSort) {
                    $this->setSortMode(array($columnKey, $sortIsAsc));
                }
            }
        }
    }
    
    function triggerFrontendColumnResize($columnKey, $newSize) {
        $this->lockMessages();
        if ($column = $this->getColset()->getControl($columnKey)) {
            $column->setWidth($newSize);
        } else {
        }
        $this->unlockMessages();
    }
    
    function triggerFrontendColumnReorder($columnKey, $newOrder) {
        $this->lockMessages();
        if ($column = $this->getColset()->getControl($columnKey)) {
            $column->setDisplayOrder($newOrder);
        } else {
        }
        $this->unlockMessages();
    }
    
    function triggerFrontendColumnToggle($columnKey, $visible) {
        if ($column = $this->getColset()->getControl($columnKey)) {
            $column->setHidden(!$visible);
        }
    }

    function setSortMode($sortMode) {
        if ($sortMode !== false && !is_array($sortMode)) throw new Exception("sortMode can be either FALSE or array(string \$colName, bool \$dirIsAsc) ");
        if ($sortMode !== ($oldSortMode = $this->sortMode)) {
            if ($sortMode) $params = array($sortMode[0], $sortMode[1]? 'asc' : 'desc');
                else $params = array(null, null);
            $column = $this->getColset()->getControl($sortMode[0]);
            
            if (!$column) throw new Exception("No such column: {$sortMode[0]}");
                elseif (!$column->getSortable()) throw new Exception("Column '{$sortMode[0]}' is not sortable");
                
            $this->sendMessage('setColumnSort', $params);
            $this->sortMode = $sortMode;
        }
    }

    function getSortMode() {
        return $this->sortMode;
    }    
    
    function jsGetSortedBy() {
        if ($this->sortMode) $res = array('key' => $this->sortMode[0], 'dir' => new Ae_Js_Var('YAHOO.widget.DataTable.CLASS_'.($this->sortMode[1]? 'ASC' : 'DESC')));
            else $res = null;
        return $res;
    }
    
    function getSortColumnId() {
        return $this->sortMode? $this->sortMode[0] : false;
    }
    
    function getSortIsAsc() {
        return $this->sortMode? $this->sortMode[1] : false;
    }
    
    function triggerDataCollect($colName) {
        $this->triggerEvent('onDataCollect', array('column' => $this->getColset()->getControl($colName)));
    }
    
    function triggerAfterDataCollect($colName, array & $values) {
        Pm_Conversation::log("$this afterDataCollect");
    	$this->triggerEvent('afterDataCollect', array('column' => $this->getColset()->getControl($colName), 'values' => & $values));
    }
    
    protected function setScrollable($scrollable) {
        $this->scrollable = $scrollable;
    }

    function getScrollable() {
        return $this->scrollable;
    }

    function setWidth($width) {
        if ($width !== ($oldWidth = $this->width)) {
            $this->width = $width;
        }
    }

    function getWidth() {
        return $this->width;
    }

    function setHeight($height) {
        if ($height !== ($oldHeight = $this->height)) {
            $this->height = $height;
        }
    }

    function getHeight() {
        return $this->height;
    }    

    protected function doGetConstructorName() {
        return 'Pmt_Table';
    }
    
    function notifyContainerInitialized() {
        parent::notifyContainerInitialized();
    }

    function setMetadataProvider(Pmt_I_Record $metadataProvider = null) {
        $this->metadataProvider = $metadataProvider;
    }
    
    /**
     * @return Pmt_I_Record
     */
    function getMetadataProvider() {
        return $this->metadataProvider;
    }
    
    function triggerFrontendRowDblClick($recordUid) {
        if (($row = $this->findRowByUid($recordUid))) {
            $this->triggerEvent(self::evtRowDblClick, array('row' => $row, 'recordUid' => $recordUid));
        }
    }    

    function setVisible($visible) {
        $visible = (bool) $visible;
        if ($visible !== ($oldVisible = $this->visible)) {
            $this->visible = $visible;
            $this->sendMessage(__FUNCTION__, array($visible));
        }
    }

    function getVisible() {
        return $this->visible;
    }
        
}

?>