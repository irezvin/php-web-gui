<?php

interface Pmt_I_MDIWindow extends Pm_I_Observable {
	
	// events
	
	const evtWindowControlMessage = 'windowControlMessage';
	const evtGetWindow = 'getWindow';
	const evtClose = 'close';
	
	// window control messages
	
	const wcmClose = 'close';
	const wcmUpdateHeader = 'updateHeader';
	
}
