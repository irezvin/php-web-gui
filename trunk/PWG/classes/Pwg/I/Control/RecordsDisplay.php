<?php

interface Pwg_I_Control_RecordsDisplay extends Pwg_I_Control {

//  function beginUpdate();
//  
//  function endUpdate();
    
//  Pwg_I_Control_RecordsDisplay    

	function setRecordPrototype(Ac_Model_Object $record = null);
	
    function addRecord(Ac_Model_Object $record, $newIndex = false);
    
    function deleteRecord(Ac_Model_Object $record);
    
    function updateRecord(Ac_Model_Object $record, $newIndex = false);

    function setRecords(array $records = array());

    function setCurrentRecord(Ac_Model_Object $record = null);
    
    function setRecordErrors(Ac_Model_Object $record, array $errors = array());
    
    /**
     * Returns selected record.
     * @return Ac_Model_Object
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
    
    function getRecordIndex(Ac_Model_Object $record);
    
    /**
     * Is triggered when user selects some record by the control and getCurrentRecord() changes
     */
    function observeRecordSelected (Pwg_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array());

    function unobserveRecordSelected (Pwg_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array());
    
    /**
     * Is triggered when user edits selected record
     */
    function observeRecordEdited (Pwg_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array());
    
    function unobserveRecordEdited (Pwg_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array());

    /**
     * Is triggered when user adds new record
     *
     */
    function observeRecordCreated (Pwg_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array());
    
    function unobserveRecordCreated (Pwg_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array());

    /**
     * Is triggered when user tries to remove a record
     */
    function observeRecordRemoved (Pwg_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array());
    
    function unobserveRecordRemoved (Pwg_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array());
    
}

?>