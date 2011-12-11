<?php

abstract class Pm_Conversation_Abstract extends Pmt_Autoparams implements Pm_I_Conversation, Pm_I_Refcontrol {

    protected $refs = array();

    protected $started = false;

    protected $tempDir = '';

    protected $tempLogFilename = false;

    protected $oldErrorLog = false;

    protected $oldLogErrors = false;

    protected $autoTrapErrors = false;
    
    protected $errorPushData = false;

    protected $jsId = 'transport';

    protected $responders = array();

    protected $residentResponders = array();

    protected $filters = array();

    protected $outbox = array();

    protected $requestData = false;

    protected $inbox = array();

    protected $startedResponders = array();

    protected $logOutMessages = false;

    protected $baseUrl = false;

    /**
     * @var Pm_I_WebFront
     */
    protected $webFront = false;

    /**
     * @var Ae_Js
     */
    protected $js = false;

    function __construct(array $options = array()) {
        parent::__construct($options);
        $this->js = new Ae_Js();
    }

    function setRequestData(array $data) {
        $this->requestData = $data;
        $this->parseRequest();
        $this->outbox = false;
    }

    function getResponse() {
        $res = false;

        if ($this->autoTrapErrors && $this->tempLogFilename) $this->processTempLog(false, true);

        if ($this->errorPushData) $res = array('errorPushData' => $this->errorPushData);
        else {
            if ($this->outbox === false) {
                $this->outbox = array();
                $this->startedResponders = array();
                $hadErrors = false;
                try {
                    foreach ($this->filters as $f) {
                            $this->startResponder($f);
                    }
                    foreach ($this->residentResponders as $resp) $resp->startQueue(true);
                } catch (Exception $e) {
                    $this->errorPushData = (string) $e;
                    $hadErrors = true;
                }
                if (!$hadErrors) {
                    foreach ($this->inbox as $key => $message) {
                        try {
                            $rcptId = $message->recipientId;
                            foreach ($this->filters as $f) $f->acceptMessage($message);
                            if (isset($this->responders[$rcptId])) {
                                $this->startResponder($this->responders[$rcptId]);
                                $this->responders[$rcptId]->acceptMessage($message);
                            } else {
                                Pm_Conversation::log("Unknown responder: ".$rcptId);
                            }
                        } catch (Exception $e) {
                            $this->errorPushData = (string) $e;
                            $hadErrors = true;
                            break;
                        }
                    }
                }
                if (!$hadErrors) {
                    $this->endAllResponders();
                    try {
                        foreach ($this->residentResponders as $resp) $resp->endQueue(true);
                    } catch (Exception $e) {
                        $this->errorPushData = (string) $e;
                        $hadErrors = true;
                    }
                }
            }
            $res = array('messages' => $this->outbox);
            if ($this->autoTrapErrors && $this->tempLogFilename) $this->processTempLog(false, true);
            if ($this->errorPushData) $res['errorPushData'] = $this->errorPushData;
        }
        return $res;
    }

    //  Pm_I_Conversation

    function registerResponder(Pm_I_Responder $responder) {
        //Pm_Conversation::log("Registering responder for ".$responder.", id is ".$responder->getResponderId());
        $this->responders[$responder->getResponderId()] = $responder;
        if ($responder->isResidentResponder()) $this->residentResponders[$responder->getResponderId()] = $responder;
        $responder->setConversation($this);
        if (!$this->isPageRender())
        $this->startedResponders[$responder->getResponderId()] = $responder;
        $this->refAdd($this, $responder);
    }

    function registerFilter(Pm_I_Responder $filter) {
        $this->filters[$filter->getResponderId()] = $filter;
    }

    function sendClientMessage(Pm_Message $message) {
        if ($message->hasSyncControl()) {
            foreach (array_keys($this->outbox) as $i) if ($this->outbox[$i]->syncMatch($message)) unset($this->outbox[$i]);
        }
        $this->outbox[] = $message;
    }

    function setJsId($jsId) {
        $this->jsId = $jsId;
    }

    function getJsId() {
        return $this->jsId;
    }

    //  Implementation functions

    protected function parseRequest() {
        $this->inbox = array();
        if (is_array($this->requestData)) {
            foreach ($this->requestData as $msgData) {
                if ($msg = $this->filterMessage($msgData)) $this->inbox[] = $msg;
            }
        }
    }

    /**
     * @param array $msgData
     * @return Pm_Message
     */
    protected function filterMessage(array $msgData) {
        $res = new Pm_Message();
        if (!$res->initFromUnfilteredData($msgData)) $res = false;
        return $res;
    }

    protected function startResponder(Pm_I_Responder $responder) {
        $id = $responder->getResponderId();
        if (!isset($this->startedResponders[$id])) {
            $this->startedResponders[$id] = true;
            $responder->startQueue();
        }
    }

    protected function endAllResponders() {
        foreach (array_keys($this->startedResponders) as $id) {
            if (isset($this->filters[$id])) $this->filters[$id]->endQueue();
            elseif (isset($this->responders[$id])) $this->responders[$id]->endQueue();
        }
    }

    function getTempErrorLogName() {
        $res = false;
        if (isset($_SESSION)) {
            $res = '_errors_'.session_id().'.log';
            if (strlen($this->tempDir)) $res = $this->tempDir.'/'.$res;
        }
        return $res;
    }

    function setTempDir($tempDir) {
        $this->tempDir = $tempDir;
    }

    function getTempDir() {
        return $this->tempDir;
    }

    function enableTempLog() {
        if (($this->oldErrorLog === false) && strlen($tlf = $this->getTempErrorLogName())) {
            $this->oldErrorLog = ini_get('error_log');
            $this->oldLogErrors = ini_get('log_errors');
            $this->tempLogFilename = $tlf;
            ini_set('error_log', $tlf);
            ini_set('log_errors', 1);
        }
    }

    function disableTempLog() {
        if ($this->oldErrorLog !== false) {
            ini_set('error_log', $this->oldErrorLog);
            ini_set('log_errors', $this->oldLogErrors);
        }
    }

    function setAutoTrapErrors($autoTrapErrors) {
        $this->autoTrapErrors = $autoTrapErrors;
        if ($this->autoTrapErrors) $this->enableTempLog();
    }

    function getAutoTrapErrors() {
        return $this->autoTrapErrors;
    }

    function processTempLog($tempLogFilename = false, $moveToPermLog = true) {
        if ($tempLogFilename === false) $tempLogFilename = $this->tempLogFilename;
        if (strlen($tempLogFilename)) {
            if ($moveToPermLog === true) $moveToPermLog = $this->oldErrorLog;
            if (is_file($this->tempLogFilename) && filesize($this->tempLogFilename)) {
                if ($this->errorPushData === false) $this->errorPushData = ''; else $this->errorPushData .= "\n";
                $this->errorPushData .= file_get_contents($this->tempLogFilename);
                unlink($this->tempLogFilename);
                if (strlen($moveToPermLog)) {
                    $d = dirname($this->oldErrorLog);
                    if (is_dir($d) && is_writable($d) && ($f = fopen($this->oldErrorLog, 'a')) !== false) {
                        fwrite($f, "\n".$this->errorPushData);
                        fclose($f);
                    }
                }
            }
        }
    }

    function started() {
        return $this->started;
    }

    function start() {
        $this->started = true;
    }

    function __toString() {
        return "Pm_I_Conversation #".$this->jsId." (".count($this->responders)." responders)";
    }

    function isPageRender() {
        return !$this->hasToProcessWebRequest();
    }
    
    protected function initErrorHandler() {
        if ($this->autoTrapErrors) {
	        set_error_handler(array('Pm_Conversation_Abstract', 'errorHandler'), E_ALL);
		}
    }
    
    static function errorHandler($errno, $errstr, $errfile, $errline) {
        if ($errno & ini_get('error_reporting'))
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }
    
    function __sleep() {
        return array_diff(array_keys(get_object_vars($this)), array('requestData', 'inbox', 'outbox', 'errorPushData', 'webFront'));
    }

    function __wakeup() {
        $this->requestData = false;
        $this->inbox = array();
        $this->outbox = array();
        if ($this->autoTrapErrors) $this->initErrorHandler();
        if ($this->tempLogFilename) {
            ini_set('error_log', $this->tempLogFilename);
            ini_set('log_errors', 1);
        }
    }

    function setLogOutMessages($logOutMessages) {
        $this->logOutMessages = $logOutMessages;
    }

    function getLogOutMessages() {
        return $this->logOutMessages;
    }

    function setBaseUrl($baseUrl) {
        $this->baseUrl = $baseUrl;
    }

    function getBaseUrl() {
        return $this->baseUrl;
    }

    function notifyBeforeRender() {
    }

    function notifyPageRender() {
    }

    function notifyReset() {
    }

    function setSessionId($sessionId) {
    }

    function getAssetLibs() {
        return array(
            'transport.js', 
            'pax.css'
            );
    }

    function setWebFront(Pm_I_Web_Front $webFront) {
        $this->webFront = $webFront;
    }

    function releaseSession() {
    }
    
    //  +-------------- Pm_I_Refcontrol implementation ---------------+

    protected $refReg = array();

    protected function refGetSelfVars() {
        $res = array();
        foreach (array_keys(get_object_vars($this)) as $v) $res[$v] = & $this->$v;
        return $res;
    }

    function refHas($otherObject) { return Pm_Impl_Refcontrol::refHas($otherObject, $this->refReg); }

    function refAdd($otherObject) { return Pm_Impl_Refcontrol::refAdd($this, $otherObject, $this->refReg); }

    function refRemove($otherObject, $nonSymmetrical = false) { $v = $this->refGetSelfVars(); return Pm_Impl_Refcontrol::refRemove($this, $otherObject, $v, false, $nonSymmetrical); }

    function refNotifyDestroying() { return Pm_Impl_Refcontrol::refNotifyDestroying($this, $this->refReg); }

    //  +-------------------------------------------------------------+

}