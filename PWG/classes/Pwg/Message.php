<?php

class Pm_Message {
    
	var $msgId = false;
	
    var $syncHash = false;
    
    var $recipientId = false;
    
    var $threadId = false;
    
    var $methodName = 'default';
    
    var $params = array();
    
    function hasSyncControl() {
        return strlen($this->syncHash) && strlen($this->recipientId) && strlen($this->methodName);
    }
    
    function syncMatch(Pm_Message $other) {
        $res = ($this->threadId === $other->threadId)
            && ($this->recipientId === $other->recipientId)  
            && ($this->syncHash === $other->syncHash) 
            && ($this->methodName === $other->methodName);
        return $res; 
    }
    
    function initFromUnfilteredData(array $data) {
    	if (isset($data['msgId']) && ($v = (int) $data['msgId'])) $this->msgId = $v;
    	
        if (isset($data['recipientId']) && is_string($data['recipientId']) && strlen($data['recipientId'])) 
            $this->recipientId = $data['recipientId'];
        else 
            $this->recipientId - false; 
            
        if (isset($data['methodName']) && is_string($data['methodName']) && strlen($data['methodName']))
            $this->methodName = $data['methodName'];
        else $this->methodName = 'default';
            
        if (isset($data['params']) && is_array($data['params'])) 
            $this->params = $data['params'];
        else $this->params = array();
        
        if (isset($data['syncHash']) && is_string($data['syncHash']) && strlen($data['syncHash']))
            $this->syncHash = $data['syncHash'];
        else $this->syncHash = false;
            
        return strlen($this->recipientId) > 0;
    }
    
    function toJs() {
        $res = array();
        foreach (array('syncHash', 'recipientId', 'methodName', 'params', 'threadId') as $p) {
            $v = $this->$p;
            if ($v !== false) $res[$p] = $v;
        }
        return $res;
    }
    
}

?>