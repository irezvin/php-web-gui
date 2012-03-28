<?php

class Pwg_Conversation extends Pwg_Conversation_Abstract {

    protected $useNewProtocol = true;
    
    protected static $currentApplication = null;
    
    /**
     * @return Ae_Application
     */
    static function getCurrentApplication() {
        return self::$currentApplication;
    }
    
    static function setCurrentApplication(Ae_Application $application) {
        self::$currentApplication = $application;
    }

    protected function setUseNewProtocol($useNewProtocol) {
        $this->useNewProtocol = $useNewProtocol;
    }

    function getUseNewProtocol() {
        return $this->useNewProtocol;
    }
    
    static function getLogPath() {
        $res = self::$currentApplication? self::$currentApplication->getAdapter()->getVarLogsPath() : PAX_TMP_PATH;
        return $res;
    }
    
    static function getLogFilename() {
        if (session_id()) {
            $res = self::getLogPath().'/sess_'.session_id().'.log'; 
        } else {
            $res = date('Y-m-d');
            if (isset($_SERVER['REMOTE_ADDR'])) $res .= '_'.$_SERVER['REMOTE_ADDR'];
            $res = self::getLogPath().'/'.$res.'.log';
        }
        return $res;
    }
    
    static function getLogEnabled() {
        $res = self::$currentApplication? self::$currentApplication->getAdapter()->getLogEnabled() : (!defined('PAX_LOG') || PAX_LOG);
        return $res;
    }
    
    static function log($message = false) {
        static $f = 0, $id = 0;
        if (!$id) $id = date('H-i-s');
        if (self::getLogEnabled()) {
            if (func_num_args() > 1) {
                $args = func_get_args();
                $s = '';
                foreach ($args as $arg) {
                    if (is_scalar($arg)) $s .= strlen($s)? '; '.$arg : $arg; else {
                        Pwg_Conversation::log($s);
                        $s = '';
                        Pwg_Conversation::log($arg);
                    }
                }
                if (strlen($s)) Pwg_Conversation::log($s);
            } else {
                if (defined('USE_FIREPHP') && USE_FIREPHP) {
                    FirePHP::getInstance(true)->log($message);
                }
                if (!is_string($message)) {
                    ob_start();
                    $he = ini_get('html_errors');
                    if ($he) {ini_set('html_errors', 0); }
                    var_dump($message);
                    if ($he) {ini_set('html_errors', 1); }
                    $message = ob_get_clean();
                }
                if (strlen($lfn = self::getLogFilename())) {
                    if (isset($_SERVER) && isset($_SERVER['REMOTE_ADDR'])) $message = $_SERVER['REMOTE_ADDR']."\t".$message;
                    if ($f || ($f = fopen($lfn, "a"))) {
                        fputs($f, "\n[{$id}] ".date("Y-m-d H:i:s")."\t".$message);
                        //fclose($f);
                    }
                }
            }
        }
    }
        
    function hasToProcessWebRequest() {
    	return isset($_POST['messages']);
    }
    
    function processWebRequest() {
        ob_start();
        ini_set('html_errors', 0);
        $msgs = $_POST['messages'];
        if (!is_array($_POST['messages']) && !strlen($_POST['messages'])) {
        	echo "1"; // check routine
        } else {
        	Pwg_Conversation::log("in: ".urldecode(http_build_query($_POST['messages'])));
        	if (ini_get('magic_quotes_gpc')) $msgs = Ae_Util::stripSlashes($msgs);
	        $this->setRequestData($msgs);
	        $response = $this->getResponse();
	        $r = $this->js->toJs($response);
	        if ($this->logOutMessages) Pwg_Conversation::log("out: ".$r);
	        $junk = ob_get_clean();
	        echo $r = $this->js->toJs($response);
	        if (strlen(trim($junk))) Pwg_Conversation::log("junk output: ".$junk);
       }
    }
    
    function getAssetLibs() {
        $res = parent::getAssetLibs();
        if ($this->useNewProtocol) {
            $res = array(
                'pax.css',
                'Protocol.js',
                'Protocol/Transport.js',
                'Protocol/AjaxTransport.js',
            );
        }
        return $res;
    }
    
    function getInitJavascript() {
		ob_start();
        if ($this->useNewProtocol) {
		    $initializer = new Ae_Js_Call('Pwg_Protocol', array(array(
		      'serverUrl' => $this->baseUrl,
		      'transport' => new Ae_Js_Call('Pwg_Protocol_AjaxTransport', array(
		      ), true))     
		    ), true);
?>
        window.<?php echo $this->jsId; ?> = <?php echo $initializer; ?>;
<?php 
		} else {
?>
		window.<?php echo $this->jsId; ?> = new Pwg_Transport(<?php echo $this->js->toJs($this->baseUrl); ?>);
<?php 
		}
		return ob_get_clean();
    }

    function getStartupJavascript() {
        ob_start();
?>
        window.<?php echo $this->jsId; ?>.broadcast('lazyInitialize');

<?php   if ($r = $this->getResponse()) { if (!isset($r['messages'])) $r['messages'] = array(); ?>
<?php       if ($this->useNewProtocol) { ?>

        window.<?php echo $this->jsId; ?>.processInbox(<?php echo $this->js->toJs($r['messages'], 16); ?>);
                    
<?php       } else { ?>

        window.<?php echo $this->jsId; ?>.processServerData(<?php echo $this->js->toJs($r, 16); ?>);
        
<?php       } ?>
<?php   } ?>

<?php 
        return ob_get_clean();        
    }
    
    
}

?>