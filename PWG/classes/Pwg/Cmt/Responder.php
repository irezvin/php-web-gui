<?php

class Pwg_Cmt_Responder extends Pwg_Autoparams {
	
	const fixLen = 5000;
	const fixLen2 = 5000;
	const fixEol = '<br />';
	const contentType = 'text/javascript';
	const head = '------ [cometData] ------';
	
    protected $instanceId = false;
    protected $isXhr = '?';
    protected $pmJs = false;

    protected function setInstanceId($instanceId) {
        $this->instanceId = $instanceId;
    }

    function getInstanceId() {
    	if ($this->instanceId === false) {
    		if (isset($_REQUEST['cmtInstanceId'])) $this->instanceId = (int) $_REQUEST['cmtInstanceId'];
    	}
        return $this->instanceId;
        	
    }
    
    function setIsXhr($isXhr) {
    	$this->isXhr = (bool) $isXhr;
    }
    
    function getIsXhr() {
    	if ($this->isXhr === '?') {
    		$this->isXhr = isset($_REQUEST['cmtXhr']) && (int) $_REQUEST['cmtXhr'];
    	}
    	return $this->isXhr;
    }
    

    function getPmJs() {
        if ($this->pmJs === false) {
        	$this->pmJs = new Ae_Js();
        }
        return $this->pmJs;
    }

    function start() {
    	//ob_start();
    	//ob_implicit_flush(true);
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', false);
        if ($this->getIsXhr()) header('Content-Type: text/plain');
        //header('Transfer-Encoding: Chunked');
        header('Pragma: no-cache');
        echo str_repeat(chr(32), self::fixLen);
        flush();
    }
    
    function send($json) {
    	$call = 'Pwg_Comet.sendDataToInstance';
    	if (!$this->getIsXhr()) $call = 'parent.'.$call;
    	$j = new Ae_Js_Call($call, array($this->getInstanceId(), $json));
    	if (!$this->getIsXhr()) {
    		$s = Ae_Util::mkElement('script', $this->getPmJs()->toJs($j), array('type' => 'text/javascript')).self::fixEol;
			$len = mb_strlen($s,'utf-8');
			if ($len < self::fixLen2) {
				$s .= str_repeat(chr(32), self::fixLen2 - $len);
				$len = self::fixLen2;
			}
			echo $s;
    	} else {
    		$strJson = $this->getPmJs()->toJs($j);
			$len = mb_strlen($strJson,'utf-8');
			if ($len < self::fixLen2) {
				$strJson .= str_repeat(chr(32), self::fixLen2 - $len);
				$len = self::fixLen2;
			}
    		echo self::head.$len.'-'.$strJson;
    	}
    	flush();
    }
    
    function disconnect() {
    	$call = 'Pwg_Comet.disconnectInstance';
    	if (!$this->getIsXhr()) $call = 'parent.'.$call;
    	$j = new Ae_Js_Call($call, array($this->getInstanceId()));
		if (!$this->getIsXhr()) {
    		echo Ae_Util::mkElement('script', $this->getPmJs()->toJs($j), array('type' => 'text/javascript')).self::fixEol;
    	} else {
    		$strJson = $this->getPmJs()->toJs($j);
			$len = mb_strlen($strJson,'utf-8');
			if ($len < self::fixLen2) {
				$strJson .= str_repeat(chr(32), self::fixLen2 - $len);
				$len = self::fixLen2;
			}
    		echo self::head.$len.'-'.$strJson;
    	}
    	flush();
    	//ob_end_flush();
    }
	
}