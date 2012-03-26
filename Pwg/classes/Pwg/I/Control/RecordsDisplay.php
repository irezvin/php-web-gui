<?php

interface Pmt_I_Control_RecordsDisplay extends Pmt_I_Control {

//  function beginUpdate();
//  
//  function endUpdate();
    
//  Pmt_I_Control_RecordsDisplay    

	function setRecordPrototype(Ae_Model_Object $record = null);
	
    function addRecord(Ae_Model_Object $record, $newIndex = false);
    
    function deleteRecord(Ae_Model_Object $record);
    
    function updateRecord(Ae_Model_Object $record, $newIndex = false);

    function setRecords(array $records = array());

    function setCurrentRecord(Ae_Model_Object $record = null);
    
    function setRecordErrors(Ae_Model_Object $record, array $errors = array());
    
    /**
     * Returns selected record.
     * @return Ae_Model_Object
     */
    function getCurrentRecord();
    
    /**
     * Should be called if current action (move / new / edit / save / cancel / delete) should be cancelled.
     */
    function cancelCurrentAction();
    
    /**
     * Sets current navigation and editing capabilities of the control. Effective capabilities may be more restrictive, but never less.
     * True means true, false means false, null means 'ignore this parameter and leave it as is' 
     *
     * @param bool|null $canMove
     * @param bool|null $canCreate
     * @param bool|null $canEdit
     * @param bool|null $canSave
     * @param bool|null $canCancel
     * @param bool|null $canDelete
     */
    function setCurrentCaps($canMove = null, $canCreate = null, $canEdit = null, $canSave = null, $canCancel = null, $canDelete = null);
    
    function getCurrentCaps();
    
    function getRecordIndex(Ae_Model_Object $record);
    
    /**
     * Is triggered when user selects some record by the control and getCurrentRecord() changes
     */
    function observeRecordSelected (Pm_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array());

    function unobserveRecordSelected (Pm_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array());
    
    /**
     * Is triggered when user edits selected record
     */
    function observeRecordEdited (Pm_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array());
    
    function unobserveRecordEdited (Pm_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array());

    /**
     * Is triggered when user adds new record
     *
     */
    function observeRecordCreated (Pm_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array());
    
    function unobserveRecordCreated (Pm_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array());

    /**
     * Is triggered when user tries to remove a record
     */
    function observeRecordRemoved (Pm_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array());
    
    function unobserveRecordRemoved (Pm_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array());
    
}

?>