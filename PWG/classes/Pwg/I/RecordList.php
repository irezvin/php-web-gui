<?php

/**
 * triggers events Pwg_I_RecordList::openDetails, Pwg_I_RecordList::createRecord, Pwg_I_RecordList::deleteRecord
 */	

interface Pwg_I_RecordList extends Pwg_I_Observable {
	
	const evtOpenDetails = 'openDetails';
	const evtCreateRecord = 'createRecord';
	const evtDeleteRecord = 'deleteRecord';

}