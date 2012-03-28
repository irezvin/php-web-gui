<?php

class Pwg_Queue_Mysql extends Pwg_Autoparams implements Pwg_I_Queue {
	
    protected $id = false;

    protected $sessId = false;
    
    protected $msgTableName = '#__cmt_messages';
    
    protected $ctrTableName = '#__cmt_counter';
    
    protected $sessMapName = '#__cmt_sessmap';
    
    /**
     * @var Ae_Sql_Db
     */
    protected $db = false;

    function getLastIndex() {
        if (!$this->id) $res = false; else {
            $res = $this->db->fetchValue("SELECT MAX(msgId) FROM {$this->msgTableName} WHERE id = ".$this->db->q($this->id));
            if (!$res) $res = 0;
        }
        return $res;
    }
    
	function setId($id) {
		if (!$this->db) $this->db = new Ae_Sql_Db_Ae();
		if ($id !== $this->sessId) {
			$this->sessId = $id;
			$intId = $this->db->fetchValue("SELECT id FROM {$this->sessMapName} WHERE sessId = ".$this->db->q($id));
			if (!$intId) {
				$this->db->query("INSERT INTO {$this->sessMapName} (sessId) VALUES (".$this->db->q($id).")");
				$intId = $this->db->getLastInsertId();
			}
			$this->id = $intId;
		}
	}
	
	function getId() {
		return $this->sessId;
	}	
	
	function reset() {
		$this->db->query("/*SKIPLOG*/DELETE FROM {$this->msgTableName} WHERE id = ".$this->db->q($this->id));
		$this->db->query("/*SKIPLOG*/DELETE FROM {$this->ctrTableName} WHERE id = ".$this->db->q($this->id));
	}
	
	function delete() {
		$this->reset();
		$this->db->query("DELETE FROM {$this->sessMapName} WHERE sessID = ".$this->db->q($this->sessId));
	}
	
	function addMessage($messageIndex, $messageBody) {
		$msgBody = serialize($messageBody);
//		$lastMsgId = (int) $this->db->fetchValue("SELECT lastMsgId FROM {$this->ctrTableName} WHERE id = ".$this->db->q($this->id));
//		if ($lastMsgId == 0) $this->db->query("INSERT INTO {$this->ctrTableName} (id, lastMsgId) VALUES (".$this->db->q($this->id).", ".$this->db->q($messageIndex).")");
//			elseif (($messageIndex - $lastMsgId) == 1) $this->db->query("UPDATE {$this->ctrTableName} SET lastMsgId = ".$this->db->q($messageIndex)." WHERE id = ".$this->db->q($this->id));
		$this->db->query("INSERT INTO {$this->msgTableName} (id, msgId, msgBody) VALUES (".$this->db->q(array($this->id, $messageIndex, $msgBody)).")");
		return $messageIndex;
	}
	
	protected function detectLastSeqIndex($indice) {
	    sort($indice);
	    $c = count($indice);
	    $lastIdx = $indice[0];
	    for ($i = 1; $i < $c; $i++) 
	        if (($indice[$i] - $lastIdx) == 1) $lastIdx = $indice[$i]; 
	        else break;
	    return $lastIdx;
	}
	
	function addMessages(array $messages) {
	    
//		$lastMsgId = (int) $this->db->fetchValue("SELECT lastMsgId FROM {$this->ctrTableName} WHERE id = ".$this->db->q($this->id));
//		if ($lastMsgId == 0) {
//		    // detect last sequential index
//            $lastIdx = $this->detectLastSeqIndex(array_keys($messages));		    
//		    $this->db->query("INSERT INTO {$this->ctrTableName} (id, lastMsgId) VALUES (".$this->db->q($this->id).", ".$this->db->q($lastIdx).")");
//		} else {
//		    $lastIdx = $this->detectLastSeqIndex(array_merge(array($lastMsgId), array_keys($messages)));
//		    if ($lastMsgId != $lastIdx) $this->db->query("UPDATE {$this->ctrTableName} SET lastMsgId = ".$this->db->q($lastIdx)." WHERE id = ".$this->db->q($this->id));
//	    }
//		
	    $arrInsert = array();
	    foreach ($messages as $idx => $body) $arrInsert[] = array('id' => $this->id, 'msgId' => $idx, 'msgBody' => serialize($body));
	    $res = $this->db->query($this->db->insertStatement($this->msgTableName, $arrInsert, true));
		return $res;
	}
	
	function getNextMessages($maxLength = false, $delete = false) {
		$sql = "/*SKIPLOG*/ SELECT msgId FROM {$this->msgTableName} WHERE id = ".$this->db->q($this->id)." ORDER BY msgId, counter";
		if ($maxLength !== false) $sql .= " LIMIT {$maxLength}";
		$ids = $this->db->fetchColumn($sql);
		$idsToLoad = array();
		if (count($ids)) {
			$lastNumber = $ids[0];
			$idsToLoad[] = $lastNumber;
			$c = count($ids);
			for ($i = 1; $i < $c; $i++) {
				if (($ids[$i] - $lastNumber) <= 1) {
					$lastNumber = $ids[$i];
					$idsToLoad[] = $lastNumber;
				} else break;
			}
		}
		$messages = false;
		if (count($idsToLoad)) {
			$messages = $this->db->fetchArray("SELECT msgBody, msgId, counter FROM {$this->msgTableName} WHERE id = ".$this->db->q($this->id)
				." AND msgId IN (".$this->db->q($idsToLoad).") ORDER BY msgId, counter", 'msgBody', 'counter');
			foreach (array_keys($messages) as $ctr) $messages[$ctr] = array('id' => $messages[$ctr]['msgId'], 'body' => unserialize($messages[$ctr]['msgBody']));
			if ($delete) $this->deleteMessages($idsToLoad);
		}
		return $messages;
		
	}
	
	function deleteMessages($ids) {
		if (!is_array($ids)) $ids = array($ids);
		$res = $this->db->Query("DELETE FROM {$this->msgTableName} WHERE id = ".$this->db->q($this->id)
				." AND msgId IN (".$this->db->q($ids).")");
		return $res;
	}
    
    
    function hasTables() {
    	$list = $this->db->fetchColumn('SHOW TABLES');
    	$res = (in_array($this->msgTableName, $list) && in_array($this->ctrTableName, $list) && in_array($this->sessMapName, $list));
    	return $res;
    }
    
    function createTables($dropOldTables = true) {
    	
    	if ($dropOldTables) {
    		$this->db->query('DROP TABLE IF EXISTS '.$this->db->n($this->msgTableName));
    		$this->db->query('DROP TABLE IF EXISTS '.$this->db->n($this->ctrTableName));
    		$this->db->query('DROP TABLE IF EXISTS '.$this->db->n($this->sessMapName));
    	}
    	
    	$this->db->query("
    		CREATE TABLE IF NOT EXISTS {$this->ctrTableName} (
  			`id` INTEGER UNSIGNED NOT NULL,
  			`lastMsgId` INTEGER UNSIGNED NOT NULL,
  			PRIMARY KEY (`id`)
			) ENGINE = InnoDB;
    	");
    	
    	$this->db->query("
			CREATE TABLE {$this->msgTableName} (
			  `id` INTEGER UNSIGNED NOT NULL,
			  `msgId` INTEGER UNSIGNED NOT NULL,
			  `msgBody` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
			  PRIMARY KEY (`id`, `msgId`),
			  INDEX `index_2`(`id`)
			)
			ENGINE = InnoDB;
    	");
    	
    	$this->db->query("
			CREATE TABLE {$this->sessMapName} (
			  `id` INTEGER UNSIGNED NOT NULL DEFAULT NULL AUTO_INCREMENT,
			  `sessId` VARCHAR(255) NOT NULL,
			  PRIMARY KEY (`id`),
			  UNIQUE INDEX `index_2`(`sessId`)
			)
			ENGINE = InnoDB;    	
    	");
    	
    }
    
    function setDb(Ae_Sql_Db $db) {
        if ($this->db) throw new Exception("Can setDb() only once");
        $this->db = $db;
    }

    /**
     * @return Ae_Sql_Db 
     */
    function getDb() {
        return $this->db;
    }    
    
}