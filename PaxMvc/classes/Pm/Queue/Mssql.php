<?php

class Pm_Queue_Mssql extends Pm_Queue_Mysql implements Pm_I_Queue {

	function addMessages(array $messages) {
	    foreach ($messages as $idx => $body) $this->addMessage($idx, $body);
	    return true;
	}
    
}