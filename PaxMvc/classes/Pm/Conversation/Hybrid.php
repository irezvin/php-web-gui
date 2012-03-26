<?php

class Pm_Conversation_Hybrid extends Pm_Conversation_Abstract {

    const resetMsg = '__reset__';
    const refreshMsg = '__refresh__';
    const releaseMsg = '__release__';
    
	/**
	 * @var Pm_I_Queue
	 */
    protected $queue = false;

    protected $queuePrototype = false;

    protected $numCycles = 300;

    protected $cycleDelay = 0.05;
    
    protected $keepAliveCycles = 100;

    protected $resetCyclesOnMessages = true;
    
    protected $sessionId = false;
    
    protected $resetFlag = false;
    
    protected $refreshFlag = false;
    
    protected $releaseFlag = false;
    
    protected $autosaveTimeout = 1.5;
    
    protected function setQueue(Pm_I_Queue $queue) {
        $this->queue = $queue;
    }

    function getQueue() {
    	if (!$this->queue) {
    		if (is_array($this->queuePrototype)) {
    			$this->queue = Pmt_Autoparams::factory($this->queuePrototype);
    			if ($this->sessionId) $this->queue->setId($this->sessionId);
    		}
    	}
        return $this->queue;
    }

    protected function setQueuePrototype(array $queuePrototype) {
        $this->queuePrototype = $queuePrototype;
        if (!$this->queuePrototype) $this->queuePrototype = false;
    }

    function getQueuePrototype() {
        return $this->queuePrototype;
    }	
	
    function hasToProcessWebRequest() {
    	return isset($_REQUEST['messages']) || isset($_REQUEST['comet']);
    }
    
    function notifyReset() {
		$queue = $this->getQueue();
        $queue->addMessage($queue->getLastIndex() + 1, self::resetMsg);
    }
    
    function processWebRequest() {
		$queue = $this->getQueue();
        ini_set('html_errors', 0);
        if (isset($_POST['messages'])) {
	        $msgs = $_POST['messages'];
	        if (!is_array($_POST['messages']) && !strlen($_POST['messages'])) {
	        	echo "1"; // check routine
	        } else {
	        	if (ini_get('magic_quotes_gpc')) $msgs = Ae_Util::stripSlashes($msgs);
	        	$mess = array();
	        	foreach ($msgs as $msg) {
	        		if (isset($msg['msgId']) && ($id = (int) $msg['msgId'])) {
	        			$mess[$id] = $msg; 
	        		}
	        	}
	        	$queue->addMessages($mess);
	        	echo "2"; // OK response -- messages accepted
		        $junk = ob_get_clean();
		        if (strlen(trim($junk))) Pm_Conversation::log("junk output: ".$junk);
	       }
        } elseif (isset($_REQUEST['comet'])) {
            while(ob_get_level()) ob_end_clean();
			$cmt = new Pm_Cmt_Responder();
			$cmt->start();
			
			$cycles = 0;
			$aliveCycles = 0;
			$numCycles = $this->numCycles;
			$delay = 1000000 * $this->cycleDelay;
			
			$modifiedTime = 0;
			ignore_user_abort(true);
            ini_set('max_execution_time', 0);
			do {
			    $cycles++;
				if ($msgs = $queue->getNextMessages(false)) {
					if ($this->resetCyclesOnMessages) $cycles = 0;
					$ids = array();
					$bodies = array();
					foreach ($msgs as $msg) {
						$bodies[] = $msg['body'];
						$ids[] = $msg['id'];
					}
					$this->setRequestData($bodies);
				    $response = $this->getResponse();
					$queue->deleteMessages($ids);
					$r = $this->js->toJs($response);
					$cmt->send($r);
					if ($this->resetFlag) {
					    // Quite a simple way to finish comet session
					    $cmt->disconnect();
					    $queue->delete();
					    die();
					} elseif ($this->refreshFlag) {
					    Pm_Conversation::log("session - RefreshFlag received");
					    $this->refreshFlag = false;
					    if ($this->webFront) {
					        Pm_Conversation::log("session - lets save");
					        $this->webFront->saveSessionData();
					        die();
					    }
					    break;
					} elseif ($this->releaseFlag) {
					    Pm_Conversation::log("session - ReleaseFlag received");
					    $this->releaseFlag = false;
					    $cmt->disconnect();
					    if ($this->webFront) {
					        $this->webFront->saveSessionData();
					        die();
					    }
					    break;
					} elseif ($this->autosaveTimeout) {
					    $modifiedTime = microtime(true);
					    Pm_Conversation::log("session modified ".($modifiedTime));
					}
					//Pm_Conversation::log(gettype($msgs));
				} else {
				    if ($modifiedTime && ((microtime(true) - $modifiedTime) > $this->autosaveTimeout)) {
					    if ($this->webFront) {
					        Pm_Conversation::log("session - lets save");
					        $this->webFront->saveSessionData(true);
				            $modifiedTime = 0;
				        }
				    }
				}
				usleep($delay);
			} while ($cycles < $numCycles);
			$cmt->disconnect();
        }
    }
    
    function setRequestData(array $data) {
        $d = array();
        foreach ($data as $k => $v) {
            if (is_string($v)) {
                if ($v === self::resetMsg) {
                    $this->resetFlag = true;
                    break;
                } elseif ($v === self::refreshMsg) {
                    $this->refreshFlag = true;
                    break;
                } elseif ($v === self::releaseMsg) {
                    $this->releaseFlag = true;
                    break;
                }                
            } else { 
                $d[$k] = $v;
            }
        }
        return parent::setRequestData($d);
    }
    
    
    function getResponse() {
        ob_start();
        $res = parent::getResponse();
		$junk = ob_get_clean();
		if (strlen(trim($junk))) Pm_Conversation::log("junk output: ".$junk);
        $processedIds = array();
        foreach ($this->inbox as $msg) $processedIds[] = $msg->msgId;
        $res['processedIds'] = $processedIds;
        return $res;
    }
    
    protected function setNumCycles($numCycles) {
        $this->numCycles = $numCycles;
    }

    function getNumCycles() {
        return $this->numCycles;
    }

    protected function setCycleDelay($cycleDelay) {
        $this->cycleDelay = $cycleDelay;
    }

    function getCycleDelay() {
        return $this->cycleDelay;
    }

    protected function setResetCyclesOnMessages($resetCyclesOnMessages) {
        $this->resetCyclesOnMessages = $resetCyclesOnMessages;
    }

    function getResetCyclesOnMessages() {
        return $this->resetCyclesOnMessages;
    }    
    
    function notifyBeforeRender() {
        if ($this->sessionId) {
            $this->getQueue()->addMessage($this->queue->getLastIndex() + 1, self::refreshMsg);
        }	
    }
    
    function notifyPageRender() {
        $this->getQueue()->reset();	
    }
    
    function setSessionId($sessionId) {
    	$this->sessionId = $sessionId;
    	if ($this->queue) $this->queue->setId($sessionId);
    }
    
    function getAssetLibs() {
        $res = array(
            'pax.css',
            'comet.js',
            'Protocol.js',
            'Protocol/Transport.js',
            'Protocol/CometTransport.js',
        );
        return $res;
    }
    
    function getInitJavascript() {
        ob_start();
        $initializer = new Ae_Js_Call('Pm_Protocol', array(array(
          'serverUrl' => $this->baseUrl,
          'transport' => new Ae_Js_Call('Pm_Protocol_CometTransport', array(
              array('sid' => session_id())
          ), true))     
        ), true);
?>
        window.<?php echo $this->jsId; ?> = <?php echo $initializer; ?>;
<?php 
        return ob_get_clean();
    }

    function getStartupJavascript() {
        ob_start();
?>
        window.<?php echo $this->jsId; ?>.broadcast('lazyInitialize');

<?php   if (($r = $this->getResponse()) && isset($r['messages'])) { ?>

        window.<?php echo $this->jsId; ?>.processInbox(<?php echo $this->js->toJs($r['messages'], 16); ?>);
                    
<?php   } ?>

<?php 
        return ob_get_clean();        
    }
    
    function getResetFlag() {
        return $this->resetFlag();
    }
    
    function releaseSession() {
        if ($this->sessionId) {
            $this->getQueue()->addMessage($this->queue->getLastIndex() + 1, self::releaseMsg);
        }	
    }
        
}

?>