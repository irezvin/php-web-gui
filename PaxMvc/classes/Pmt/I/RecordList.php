<?php

/**
 * triggers events Pmt_I_RecordList::openDetails, Pmt_I_RecordList::createRecord, Pmt_I_RecordList::deleteRecord
 */	

interface Pmt_I_RecordList extends Pm_I_Observable {
	
	const evtOpenDetails = 'openDetails';
	const evtCreateRecord = 'createRecord';
	const evtDeleteRecord = 'deleteRecord';

}