<?php

interface Pwg_I_Queue {
	
    function setDb(Ae_Sql_Db $db);
    
	function setId($id);
	
	function getId();
	
	function reset();
	
	function delete();
	
	function addMessage($messageIndex, $messageBody);
	
	function addMessages(array $messages);
	
	function getNextMessages($maxLength = false, $delete = false);
	
	function deleteMessages($ids);
	
	function getLastIndex();
	
}