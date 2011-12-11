<?php

interface Pmt_I_Calendar {

	const evtPageDateChange = 'pageDateChange';
	const evtSelectedValueChange = 'selectedValueChange';
	const evtClose = 'close';
	
    function setMultiple($multiple);
    function getMultiple();
    
    function setDateClasses($dateClasses);
    function getDateClasses();
	
	function setSelectedValue($selectedValue);
	function getSelectedValue();
	
	function setMinDate($minDate);
	function getMinDate();
	
	function setMaxDate($maxDate);
	function getMaxDate();
	
	function setPageDate($pageDate, $force = false);
	function getPageDate();
	
	function setPhpDateFormat($phpDateFormat);
	function getPhpDateFormat();
    
}