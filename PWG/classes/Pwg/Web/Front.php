<?php

class Pwg_Web_Front extends Ac_Legacy_Controller implements Pwg_I_Web_Front {
    
    var $_defaultMethodName = 'default';
    
    /**
     *  Names of YUI js libs (i.e. 'editor', 'event') to load debug versions of.
     *  If this area isn't empty, logger will also be loaded
     *  @var array 
     */
    var $yuiDebugLibs = array();
    
    var $minifyYuiLibs = false;
    
    /**
     * @var bool
     * Whether to persist object tree in Alternative Php Cache (won't work without mod_apc)
     */
    var $useApc = false;
    
    var $defaultJsLibs = array(
    );
    
    var $jsAssetVars = array(
        '{BASEURL}' => false,
        '{SERVER}' => false,
        '{YUI}' => '{SERVER}/yui/build',
    );
    
    var $contentType = 'text/html; charset=Windows-1251';
    
    var $baseUrl = false;
    
    var $resetUrl = false;
    
    var $baseUrlSuffix = false;
    
    var $lang = 'english';

    var $configPath = 'app.config.php';
    
    var $id = 'paxApplication';
    
    /**
     * @var Pwg_PaxMvc
     */
    protected $application = null;
    
    /**
     * @var Pwg_Legacy_App
     */
    var $app = false;
    
    var $reset = false;
    
    var $loaded = false;
    
    var $serializedObjects = array();
    
    var $developerMode = false;
    
    var $bodyAttribs = array();
    
    var $conversationOptions = array();
    
    protected $controllers = array();
    
    var $showHtml = true;
    
    var $logOutMessages = false;
    
    var $showTopAndBottomHtmlInBody = true;

    /**
     * When dynamic control creation/destruction is beign used, check with Pwg_Guardian for presence of obsolete object signatures in the serialized data
     * (of course controls should report to the Guardian about their scheduled destruction)
     *
     * @var bool
     */
    var $useGuardian = false;
    
    var $registerDebugHandlers = null;
    
    /**
     * @var Pwg_I_Conversation
     */
    protected $conversation = false;

    /**
     * @var Ac_Js
     */
    protected $js = false;

    protected $cachedInitializers = array();
    
    protected $sessionSuffix = false;
    
    protected $topController = false;

    function setTopController($topController) {
        $this->topController = $topController;
    }

    function getTopController() {
        return $this->topController;
    }    

    function setSessionSuffix($sessionSuffix) {
        $this->sessionSuffix = $sessionSuffix;
    }

    function getSessionSuffix() {
        return $this->sessionSuffix;
    }
    
    function getUseComet() {
        $res = $this->application->getUseComet();
        return $res;
    }
    
    function __construct ($context = null, $options = array(), $instanceId = false) {
        
        if (func_num_args() == 1) parent::__construct($context);
        else parent::__construct($context, $options, $instanceId);
        
        $u = $this->_context->getUrl(array(), false);
        $u->query = array();
        $this->baseUrl =  $u.$this->baseUrlSuffix;
        
        if (isset($this->jsAssetVars['{SERVER}']) && $this->jsAssetVars['{SERVER}'] === false) {
            $this->jsAssetVars['{SERVER}'] = 'http://'.$_SERVER['HTTP_HOST'];
        }
        if (isset($this->jsAssetVars['{BASEURL}']) && $this->jsAssetVars['{BASEURL}'] === false) {
            $this->jsAssetVars['{BASEURL}'] = $this->baseUrl; 
        }
        
    }

    protected function invokeReset() {
        
        if (isset($_COOKIE['PHPSESSID'])) {
            $this->conversation->setSessionId($_COOKIE['PHPSESSID']);
            $this->conversation->notifyReset();
        }
        
        if (!isset($_SESSION)) session_start();
        
        if ($this->useApc && function_exists('apc_delete')) {
            if (isset($_SESSION[$this->getSessionStateVarName()])) {
                apc_delete($_SESSION[$this->getSessionStateVarName()]);
            }
        }
        
        session_destroy();
        header('location: '.$this->baseUrl);
    }
    
    function getReset() {
        return (bool) $this->_context->getData('reset');
    }
    
    function executeDefault() {

        if (!Pwg_Conversation::getCurrentApplication()) Pwg_Conversation::setCurrentApplication($this->application);
        
        if ($this->topController) $this->registerController (Ac_Autoparams::factory($this->topController, 'Pwg_I_Controller'));

        
        if ($this->getUseComet()) {
            $this->conversationOptions = Ac_Util::m(array(
                'class' => 'Pwg_Conversation_Hybrid',
                'queuePrototype' => array(
                    'class' => 'Pwg_Queue_Mysql',
                    'db' => $this->application->getDb(),
                ), 
            ), $this->conversationOptions);
        }
        
        if (is_null($this->registerDebugHandlers) && defined('_DEPLOY_DEBUG_DB') && _DEPLOY_DEBUG_DB)
            $this->registerDebugHandlers = true;
        
        $this->doBeforeExec();
        
        if ($this->registerDebugHandlers) Pwg_Debug::registerHandlers();
        
        header('content-type: '.$this->contentType);
        
        // stupid hack
        if (isset($_REQUEST['messages']) && isset($_REQUEST['sid'])) {
        	$conv = $this->createConversation();
        	$conv->processWebRequest();
        	die();
        }
            
        $this->conversation = $this->createConversation();
        
        if ($this->getReset()) {
            
            $this->invokeReset();
            
        } else {

            if (!$this->conversation->hasToProcessWebRequest() && $this->showHtml) {
                if (isset($_COOKIE['PHPSESSID'])) {
                    $this->conversation->setSessionId($_COOKIE['PHPSESSID']);
                    Pwg_Conversation::log("session NotifyBeforeRender called");
                    $this->conversation->notifyBeforeRender();
                }
            }
            
            if (!isset($_SESSION)) session_start();
            Pwg_Conversation::log("----- #{$this->id} WebFront::exec() -----");
            
            ini_set('log_errors', 1);
            ini_set('error_log', Pwg_Conversation::getLogFilename());
            
            $vn = $this->getSessionStateVarName();
            $this->loaded = false;
            if (isset($_SESSION[$vn])) {
                if ($this->load($_SESSION[$vn])) {
                    $this->loaded = true;
                    $this->conversation->setSessionId(session_id());
                }
            }
            if (!$this->loaded) {
                $this->initialize();
                $this->conversation = $this->createConversation();
                foreach ($this->controllers as $c) $c->setConversation($this->conversation);
            }
        }
        
        $this->js = new Ac_Js();
        
        if ($this->conversation->hasToProcessWebRequest()) {
            ob_start();
            $this->conversation->processWebRequest();
            $this->_response->content = ob_get_clean();
            $this->_response->noHtml = true;
        } elseif ($this->showHtml) {
        	$this->conversation->notifyPageRender();
            $this->showHtml();
        }
        Pwg_Conversation::log("Saving session");
        $this->saveSessionData();
    }
    
    function getJsAssetVars() {
        $res = $this->application->getAssetPlaceholders();
        if (is_array($this->jsAssetVars)) $res = array_merge($res, $this->jsAssetVars);
        if (isset($_REQUEST['debugAssets']) && $_REQUEST['debugAssets']) {
            var_dump($res);
            die();
        }
        return $res;
    }
    
    function getJsOrCssUrl ($jsOrCssLinkWithPlaceholders) {
        $js = $jsOrCssLinkWithPlaceholders;
        for ($i = 0; $i < 3; $i++) $js = strtr($js, $this->getJsAssetVars());
        if ((substr($js, 0, 7) != 'http://') && (substr($js, 0, 8) !== 'https://')) $js = $this->getJsOrCssUrl ('{PAX}/'.ltrim($js, '/'));
        return $js;
    }
    
    function registerController(Pwg_I_Controller $controller) {
        $this->controllers[] = $controller;
        $controller->setWebFront($this);
    }
    
    function showHtml() {

        if (!$this->_response) $this->_response = new Ac_Controller_Response_Html;
        
        $jsl = $this->getInitiallyLoadedAssets();
        foreach ($this->controllers as $c) {
            $jsl = array_merge($jsl, $c->getAssetLibs());
        }
        $newJsl = array();
        $jsl = $this->applyHacksToAssetLibs($jsl);
        foreach ($jsl as & $l) {
            if (substr($l, 0, 1) !== '{' && (substr($l, 0, 7) != 'http://') && (substr($l, 0, 8) !== 'https://')) $l = '{PAX}/'.ltrim($l, '/');
        }
        $this->_response->addAssetLibs($jsl);

        ob_start();
        foreach($this->controllers as $c) {  $c->showHeadElements(); }
        $h = ob_get_clean();
        if (strlen(trim($h))) $this->_response->addHeadTag($h);
        
        if ($this->bodyAttribs) $this->_response->bodyAttribs = $this->bodyAttribs;
        
        ob_start();
        $this->showBody();
        $this->_response->content = ob_get_clean();
        $this->conversation->start();
        foreach ($this->controllers as $con) $con->notifyFrontInitialized();
        
        return;
        
?>
<<?php echo "?"; ?>xml version="1.0" encoding="windows-1251"<?php echo "?"; ?>><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"><html>
        <head>
<?php       $this->showHead(); ?>       
        </head>
        <body<?php if ($this->bodyAttribs) echo ' '.Ac_Util::mkAttribs($this->bodyAttribs); ?>>
<?php       $this->showBody(); ?>
        </body>
</html><?php
        $this->conversation->start();
        foreach ($this->controllers as $con) $con->notifyFrontInitialized();
    }
    
    function showHead() {
?> 
<?php   $this->showJsLibs(); ?>
<?php
        foreach($this->controllers as $c) {  $c->showHeadElements(); }
    }
    
    function showTopHtml() {
        foreach (array_keys($this->controllers) as $k) {
            if ($initializer = $this->getInitializerOfController($k)) {
                $th = $initializer->getTopHtml();
                if (strlen($th)) echo $th . "\n";
            }
        }
    }
    
    function showBottomHtml() {
        foreach (array_keys($this->controllers) as $k) {
            if ($initializer = $this->getInitializerOfController($k)) {
                $bh = $initializer->getBottomHtml();
                if (strlen($bh)) echo $bh . "\n";
            }
        }
    }
    
    /**
     * @param $key
     * @return Pwg_Js_Initializer
     */
    protected function getInitializerOfController($key) {
        $res = false;
        if (isset($this->cachedInitializers[$key])) {
            $res = $this->cachedInitializers[$key];
        } else {
            $res = $this->cachedInitializers[$key] = $this->controllers[$key]->getInitializer();
        }
        return $res;
    }
    
    function showBody() {
        if ($this->showTopAndBottomHtmlInBody) $this->showTopHtml();
        
        if (!$this->conversation) $this->initialize();
        foreach ($this->controllers as $c) if ($c->hasContainer()) {
            $c->showContainer();
            echo "\n        ";
        } 
?>
        <script type='text/javascript'>
<?php 	echo $this->conversation->getInitJavascript(); ?>
<?php       
        foreach (array_keys($this->controllers) as $k) {
            if ($initializer = $this->getInitializerOfController($k)) echo $initializer->getInitScript($this->js, 12, true);
        }
?> 
<?php   echo $this->conversation->getStartupJavascript(); ?>
<?php   /* ?>
        window.<?php echo $this->getConversationJsId(); ?>.broadcast('lazyInitialize');
<?php   if ($r = $this->conversation->getResponse()) { ?>
        window.<?php echo $this->getConversationJsId(); ?>.processServerData(<?php echo $this->js->toJs($r, 16); ?>);
<?php   } ?>
<?php   */ ?> 
        </script> 
<?php       
        if ($this->showTopAndBottomHtmlInBody) $this->showBottomHtml();
    }       
    
    /**
     * @return Pwg_I_Conversation
     */
    protected function createConversation() {
        //var_dump($this->application->getAdapter()->getVarPath());
        
    	$options = Ac_Util::m(array(
    		'class' => 'Pwg_Conversation',
    		'tempDir' => $this->application->getAdapter()->getVarPath(),
    		'autoTrapErrors' => true,
    		'jsId' => $this->getConversationJsId(),
    		'baseUrl' => $this->baseUrl,
    		'logOutMessages' => $this->logOutMessages,
    	    'webFront' => $this,
    	
    		// another stupid hack
    		'sessionId' => isset($_REQUEST['sid'])? $_REQUEST['sid'] : session_id()
    	
    	), $this->conversationOptions);
    	$conversation = Pwg_Autoparams::factory($options, 'Pwg_Conversation_Abstract');
        return $conversation;
    }
    
    protected function getConversationJsId() {
        return $this->id.'Conversation';
    }
    
    protected function getSessionStateVarName() {
        $res = $this->id.'Var';
        if (strlen($this->sessionSuffix)) $res .= $this->sessionSuffix;
        return $res;
    }
    
    protected function initialize() {
    }
    
    protected function doAfterLoad($data, $exception) {
    }
    
    protected function getIncludeFilesTimes() {
        $a = array();
        foreach (get_required_files() as $f) {
            $ts = filemtime($f);
            $a[$f] = $ts;
        }
        return $a;
    }
    
    protected function applyDeveloperModeOnLoad($data, $exception, & $res, $afterLoadResult) {
        $res = true;
        $expired = false;
        if (isset($data['_dev_files_times'])) {
            $oldTimes = $data['_dev_files_times'];
            $fTimes = $this->getIncludeFilesTimes();
            foreach ($fTimes as $fn => $ft) {
                if (isset($oldTimes[$fn]) && $oldTimes[$fn] <> $ft) {
                    $expired = true;
                    break;
                }
            }
        }
        if ($expired) {
            $this->invokeReset();
            die();
            //$res = false;
        }
    }
    
    protected function applyDeveloperModeOnSave(& $data) {
        $data['_dev_files_times'] = $this->getIncludeFilesTimes();
    }
    
    protected function load($data) {
        if ($this->useGuardian) Pwg_Guardian::getInstance();
        $res = false;
        $exception = false;
        try {
            if ($this->useApc && function_exists('apc_fetch')) {
                $varName = $data;
                $tmp = apc_fetch($varName);
                if (is_object($tmp)) {
                    $data = $tmp->getArrayCopy();
                } else {
                }
            } else {
                $data = unserialize($data);
            }
            foreach ($this->doListSerializedProperties() as $p) if (isset($data[$p])) $this->$p = $data[$p];
            $res = true;
        } catch (Exception $e) {
            $exception = $e;
        }
        if (($alRes = $this->doAfterLoad($data, $exception)) === false) $res = false;
        if ($this->developerMode && !$this->hasMessages()) $this->applyDeveloperModeOnLoad($data, $exception, $res, $alRes);
        return $res;
    }
    
    protected function save() {
        $data = array();
        foreach ($this->doListSerializedProperties() as $prop) $data[$prop] = $this->$prop;
        if ($this->developerMode && !$this->hasMessages()) $this->applyDeveloperModeOnSave($data);
        if ($this->useApc && function_exists('apc_store')) {
            $varName = session_id().$this->getSessionStateVarName();
            apc_store($varName, new ArrayObject($data));
            $_SESSION[$this->getSessionStateVarName()] = $varName;
        } else {
            $data = serialize($data);
            $_SESSION[$this->getSessionStateVarName()] = $data;
        }
        if ($this->useGuardian) {
            Pwg_Guardian::getInstance()->assertForSigns($data);
        }
    }
    
    protected function doListSerializedProperties() {
        return array('conversation', 'serializedObjects', 'controllers');
    }
    
    function applyHacksToAssetLibs($assetLibs) {
        $jsl = $assetLibs;
        $yJsl = array();
        foreach ($jsl as $js) {
                if (strpos($js, '{YUI}') !== false) {
                    foreach ($this->yuiDebugLibs as $lib)
                        $js = str_replace("/{$lib}.js", "/{$lib}-debug.js", $js);
                    if ($this->minifyYuiLibs) {
                        if (strpos($js, '-debug.js') === false) 
                            $js = str_replace(".js", "-min.js", $js);
                    }
                    $yJsl[] = $js;
                }
                else $newJsl[] = $js;
        }
        if (count($yJsl) && count($this->yuiDebugLibs)) $yJsl = array_unique(array_merge($yJsl, array(
            '{YUI}/dom/dom.js',
            '{YUI}/event/event.js',
            '{YUI}/logger/logger.js',
            '{YUI}/logger/assets/logger.css',
            '{YUI}/logger/assets/skins/sam/logger.css',
        )));
        
        if (count($yJsl) && count($this->yuiDebugLibs)) {
            $jsl[] = 'widgets/yui/initLogReader.js';
        }
        
        $jsl = array_merge($yJsl, $newJsl);
        $jsl = array_unique($jsl);
        
        return $jsl;
    }
    
    protected function showJsLibs() {
        $jsl = $this->getInitiallyLoadedAssets();
        foreach ($this->controllers as $c) {
            $jsl = array_merge($jsl, $c->getAssetLibs());
        }
        $newJsl = array();
        $jsl = $this->applyHacksToAssetLibs($jsl);
        
        
        
        foreach ($jsl as $js) {
            $js = $this->getJsOrCssUrl($js);
            if (substr($js, -4) == '.css') {
?>
            <link rel='stylesheet' type='text/css' href='<?php echo htmlspecialchars($js); ?>' />
<?php               
            } else {
?>
            <script type='text/javascript' src='<?php echo htmlspecialchars($js); ?>'> </script>
<?php
            }
        }
    }
    
    protected function doBeforeExec() {
    }

    function getInitiallyLoadedAssets() {
        $res = array();
        $res[] = '{AC}/util.js';
        $res[] = 'core.js';
        $res[] = 'uiDefaults.js';
        $res = array_merge($res, $this->defaultJsLibs);
        $res[] = '{YUI}/yahoo/yahoo.js';
        $res[] = '{YUI}/dom/dom.js';
        $res[] = '{YUI}/event/event.js';
        $res[] = '{YUI}/connection/connection.js';
        //$res[] = 'prototype.js';
        
        $res = array_merge($res, array_diff($this->conversation->getAssetLibs(), $res));
        
        //$res[] = '{YUI}/yahoo/yahoo.js';
        $res[] = '{YUI}/get/get.js';
        foreach ($this->controllers as $c) {
            $res = array_merge($res, $c->getAssetLibs());
        }
        return $res;
    }

    function getResetUrl() {
        return $this->resetUrl;
    }
    
    function setResetUrl($resetUrl) {
        $this->resetUrl = $resetUrl;
    }
    
    function saveSessionData($dontClose = false) {
        $this->save();
        session_write_close();
        if ($dontClose) @session_start();
    }
    
}
?>