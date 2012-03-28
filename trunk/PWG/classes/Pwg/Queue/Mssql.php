<?php

class Pwg_Queue_Mssql extends Pwg_Queue_Mysql implements Pwg_I_Queue {

	function addMessages(array $messages) {
	    foreach ($messages as $idx => $body) $this->addMessage($idx, $body);
	    return true;
	}
    
}