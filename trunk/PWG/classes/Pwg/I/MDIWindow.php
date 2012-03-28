<?php

interface Pwg_I_MDIWindow extends Pwg_I_Observable {
	
	// events
	
	const evtWindowControlMessage = 'windowControlMessage';
	const evtGetWindow = 'getWindow';
	const evtClose = 'close';
	
	// window control messages
	
	const wcmClose = 'close';
	const wcmUpdateHeader = 'updateHeader';
	
}
