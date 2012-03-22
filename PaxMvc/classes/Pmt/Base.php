<?php

abstract class Pmt_Base implements Pmt_I_Control, Pm_I_Refcontrol {
    
    protected $id = false;
    
    protected $responderId = false;
    
    protected $containerId = false;
    
    protected $containerIsBlock = true;
    
    protected $containerAttribs = array();
    
    /**
     * @var Pmt_I_Control_Parent
     */
    protected $parent = false;
    
    /**
     * @var Pmt_I_Controller
     */
    protected $controller = false;

    /**
     * @var Pmt_I_Control_DisplayParent
     */
    protected $displayParent = false;
    
    protected $displayOrder = false;
    
    protected $actualDisplayOrder = false;
    
    protected $observers = array();
    
    /**
     * @var Pm_I_Conversation
     */
    protected $conversation = false;

    protected $lockMessages = 0;

    protected $eventMap = array();

    protected $associations = array();
    
    protected $internalObservers = array();
    
    protected $messageQueue = array();
    
    protected $etc = false;
    
    protected $debug = false;
    
    private static $autoIds = array();
    
    private static $classMethods = array();
    
    private static $methodMap = array();
    
    private static $classMap = array(
        'btn' => 'Pmt_Button',
        'txt' => 'Pmt_Text',
        'lbl' => 'Pmt_Label',
        'lst' => 'Pmt_List',
        'cnr' => 'Pmt_Composite',
        'ctl' => 'Pmt_Controller',
        'ds' => 'Pmt_Data_Source',
        'dn' => 'Pmt_Data_Navigator',
        'bnds' => 'Pmt_Data_Binder_DataSource',
        'bnd' => 'Pmt_Data_Binder',
        'cb' => 'Pmt_Checkbox',
        'grp' => 'Pmt_Group',
        'pnl' => 'Pmt_Panel',
    );
    
    protected $isResolvingAssociations = false;
    
    protected $allowedDisplayParentClass = false;
    
    protected $allowedParentClass = false;
    
    protected $frontInitialized = false;
    
    protected $containerInitialized = false;
    
    protected $jsObjectInitialized = false;
    
    protected $delayedInitialize = false;
    
    protected $frontInitialization = false;
    
    /**
     * This variable shoud be public and it's added only to make it be serializeble by __sleep implementations that use get_class_vars()
     */ 
    var $_guardianSign = false;
    
//  Pmt_Base
    
    function __clone() {
        $this->id = Pmt_Base::nextAutoId(get_class($this));
        if ($this->container) $this->container = false;
        if ($this->parent) $this->parent = false;
        if ($this->conversation) $this->conversation = false;
        if ($this->controller instanceof Pmt_Controller) $this->controller->observeControl($this);
        $this->responderId = false;
        $this->observers = array();
    }
    
    static function factory(array $prototype = array(), $baseClass = 'Pmt_Base') {
        $className = $baseClass;
        if (isset($prototype['class'])) $className = $prototype['class'];
        $res = new $className ($prototype);
        if (!$res instanceof $baseClass) throw new Exception(get_class($res).' is not an instance of '.$baseClass);
        return $res;
    }
    
    function getAssetLibs() {
        return $this->doGetAssetLibs();
    }
    
    protected static function nextAutoId($className) {
        if (!isset(Pmt_Base::$autoIds[$className])) Pmt_Base::$autoIds[$className] = 0;
            else Pmt_Base::$autoIds[$className]++;
        return $className.Pmt_Base::$autoIds[$className];
    }
    
    protected function doListFirstInitializers() {
        return array('id', 'conversation');
    }
    
    function __construct(array $options = array()) {
        if ((!isset($options['id']) || !strlen($options['id'])) && ($this->id === false)) $options['id'] = Pmt_Base::nextAutoId(get_class($this));
        
        $optKeys = array_unique(array_intersect(array_keys($options), array_merge($this->doListFirstInitializers(), array_keys($options))));
        foreach ($optKeys as $n) {
            $opt = $options[$n];
            if (method_exists($this, $mn = 'set'.$n)) {
                $this->$mn($opt);
            }
        }
        $eMap = array();
        foreach ($options as $n => $opt) {
            if ($n{0} == '.') {
                $eMap[substr($n, 1)] = $opt;
            }
        }
        $this->eventMap = array_merge($eMap, $this->eventMap);
        $this->doOnInitialize($options);
    }
    
    protected function lockMessages() {
        $this->lockMessages++;
    }
    
    protected function unlockMessages() {
        if ($this->lockMessages > 0) {
            $this->lockMessages--;
        }
    }

    function setEventMap(array $eventMap) {
        $this->eventMap = $eventMap;
    }
    
    function getEventMap() {
        return $this->eventMap;
    }
    
    function setContainerAttribs(array $attribs) {$this->containerAttribs = $attribs; }
    
    function getContainerAttribs() {return $this->containerAttribs; }
    
    function setContainerIsBlock($v) {$n = substr(__FUNCTION__, 3); $n{0} = strtolower($n{0}); $this->$n = $v;}
    
    function getContainerIsBlock() {$n = substr(__FUNCTION__, 3); $n{0} = strtolower($n{0}); return $this->$n;}
    
    /**
     * @param string $id
     * @return Pmt_I_Control
     */
    function getControl($id) {
        return false;
    }
    
    function __toString() {
        return get_class($this).'#'.$this->getResponderId();
    }
    
    /**
     * @param string|array $path
     * @return Pmt_I_Control
     */
    function getControlByPath($path) {
    	//Pm_Conversation::log("$this Getting control by path", $path);
        $res = false;
        if (!is_array($path)) $path = explode("/", $path);
        if (count($path)) {
            $current = $this;
            if (!strlen($path[0])) {
                $current = $this->getRootControl();
                $path = array_slice($path, 1);
            }
            while ($current && count($path)) {
                $segment = $path[0];
                $path = array_slice($path, 1);
                if ($segment === '..') {
                    $current = $current->getParent();
                }
                elseif ($segment == '.') {} 
                elseif (strlen($segment) && $current instanceof Pmt_I_Control_Parent) $current = $current->getControl($segment);
                else break;
            }
            if (!count($path)) $res = $current;
        }
        //Pm_Conversation::log("Res is {$res}");
        return $res;
    }
    
    /**
     * Finds topmost control in creation hierarchy. If current control does not have parents, returns current one ($this). 
     * @return Ae_I_Control
     */
    protected function getRootControl() {
        $res = $this;
        while ($parent = $res->getParent()) $res = $parent;
        return $res;
    }
    
    protected function hasJsObject() {
        return true;
    }
    
    protected function getPassthroughParams($items = false, $withFalse = true, $withNull = true) {
        $res = array();
        foreach (($items !== false? $items : $this->doListPassthroughParams()) as $k => $p) {
            if (is_numeric($k)) $k = $p;
            if (method_exists($this, $getter = 'jsGet'.$k) || method_exists($this, $getter = 'get'.$k)) {
                $val = $this->$getter();
            } else $val = $this->$k;
            if (($withFalse || $val !== false) && ($withNull || $val !== null)) $res[$p] = $val;
        }
        return $res;
    }
    
    protected function eventNameToMethodName($eventName) {
        return 'triggerFrontend'.ucFirst($eventName);
    }
    
//  Pm_I_Responder  

    function isResidentResponder() {
        return false;
    }
    
    function getResponderId() {
        if (!strlen($this->responderId)) $this->updateResponderId();
        return $this->responderId;
    }
    
    function setConversation(Pm_I_Conversation $conversation) {
        $this->conversation = $conversation;
        $this->refAdd($conversation);
        if ($this->frontInitialized || $this->frontInitialization) {
            $this->sendDelayedMessages();
        }
//      if (!$this->delayedInitialize && $this->canInitializeFront() && (!$this->displayParent || $this->displayParent->isFrontInitialized())) {
//          Pm_Conversation::log($this . "'s Display Parent is " . $this->displayParent );
//          if ($this->displayParent) Pm_Conversation::log("DP front initialized: ".$this->displayParent->isFrontInitialized()); 
//          $this->initializeFront(); 
//      }
    }
    
    function startQueue() {
    }
    
    function acceptMessage(Pm_Message $message) {
        $methodName = $this->eventNameToMethodName($message->methodName);
        if (method_exists($this, $methodName)) {
            call_user_func_array(array($this, $methodName), array_values($message->params));
        }
    }
    
    function endQueue() {
        if (!$this->delayedInitialize && $this->canInitializeFront()) {
            $this->initializeFront();
        }
    }
    
    
    
//  Pmt_I_Control   
    
    function getId() {
        return $this->id;
    }
    
    function setId($id) {
        $this->id = $id;
        $this->updateResponderId();
    }

    function setParent(Pmt_I_Control_Parent $parent) {
        if (($oldParent = $this->parent) !== $parent) {
            
            //if ($oldParent) $oldParent->removeControl($this); 
            
            $this->refAdd($parent);
            $this->parent = $parent;
            $this->updateResponderId();
            $this->resolveAssociations();
            
            if ($parent) {
                
                if (strlen($this->allowedParentClass) && (! $displayParent instanceof $this->allowedParentClass)) {
                    $c = get_class($parent);
                    throw new Exception ("Only instances of '{$this->allowedDisplayParentClass}' class are allowed as parents of {$this}, {$c} given");
                }
                
            }
            
            if ($parent instanceof Pmt_I_Control_DisplayParent && $this->displayParent === false && !isset($this->associations['displayParent'])) {
                $this->setDisplayParent($parent);
            }
        }
    }
    
    /**
     * @return Pmt_I_Control_Parent
     */
    function getParent() {
        return $this->parent;
    }
    
    function setController(Pmt_I_Controller $controller) {
        $this->controller = $controller;
    }
    
    /**
     * @return Pmt_I_Controller
     */
    function getController() {
        return $this->controller;
    }
    
    /**
     * @return Pmt_I_Control_DisplayParent
     */
    function getDisplayParent() {
        return $this->displayParent;
    }
    
    function setDisplayParent(Pmt_I_Control_DisplayParent $displayParent = null) {
        if ($this->displayParent !== $displayParent) {
            $odp = $this->displayParent;
            $this->displayParent = $displayParent;
            if ($odp) $odp->removeDisplayChild($this);
            if ($displayParent) {
                
                if (strlen($this->allowedDisplayParentClass) && (! $displayParent instanceof $this->allowedDisplayParentClass)) {
                    $c = get_class($displayParent);
                    throw new Exception ("Only instances of '{$this->allowedDisplayParentClass}' class are allowed as display parents of {$this}, {$c} given");
                }
                
                
                $displayParent->addDisplayChild($this);
                
                if (!$this->delayedInitialize && $this->canInitializeFront()) {
                    $this->initializeFront(); 
                }
            }
        }
    }
    
    function setDisplayParentPath($path) {$n = substr(__FUNCTION__, 3, -4); $n{0} = strtolower($n{0}); $this->associations[$n] = $path;}
        
    function getDisplayOrder() {
//      if ($this->displayParent) {
//          $currOrder = $this->displayParent->findDisplayChild($this);
//          if ($currOrder !== false) $this->displayOrder = $currOrder;
//      }
        if ($this->displayOrder === false) $res = false;
            else $res = (int) $this->displayOrder; 
        return $res;
    }
    
    function getActualDisplayOrder() {
        if ($this->displayParent) {
            $actOrder = $this->displayParent->findDisplayChild($this);
            if ($actOrder !== false) $this->actualDisplayOrder = $actOrder;
        }
        return $this->actualDisplayOrder;
    }
    
    function setActualDisplayOrder($actualDisplayOrder) {
        if ($this->displayParent) {
            $dc = $this->displayParent->getOrderedDisplayChildren();
            $ado = $this->getActualDisplayOrder();
            $actualDisplayOrder = min(max(0, $actualDisplayOrder), count($dc));
            if (($ado !== $actualDisplayOrder) && count($dc)) {
                $displayOrderValues = array();
                foreach ($dc as $child) $displayOrderValues[] = $child->getDisplayOrder();
                $pv = $displayOrderValues[0];
                for ($i = 1; $i < count($dc); $i++) {
                    if ($dc[$i] <= $pv) $dc[$i] = $pv + 1;
                    $pv = $dc[$i];
                }
                array_splice($dc, $actualDisplayOrder, 0, array_splice($dc, $ado, 1, array()));
                $i = 0;
                foreach ($dc as $control) {
                    $control->setDisplayOrder($displayOrderValues[$i]);
                    $i++;
                }
            }
        }
    }
    
    function setDisplayOrder($displayOrder) {
        $old = $this->displayOrder;
        if ($old !== $displayOrder) {
            $this->displayOrder = $displayOrder;
            if ($this->displayParent) $this->displayParent->updateDisplayChildPosition($this, $this->displayOrder);
        }
    }
    
//  Pm_I_Observable 
    
    function observe($eventType, Pm_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array()) {
        if (Pmt_Impl_Observable::observe($this->observers, $eventType, $observer, $methodName, $extraParams)) {
            $this->frontendObserve($eventType);
        }
    }
    
    function unobserve($eventType, Pm_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array()) {
        if (Pmt_Impl_Observable::unobserve($this->observers, $eventType, $observer, $methodName, $extraParams, $this->doListFrontendEvents())) {
            $this->frontendUnobserve($eventType);    
        }
    }

//  Template methods of Pmt_Base

    protected function doGetAssetLibs() {
        return array();
    }
    
    protected function doOnInitialize(array $options) {
    }
    
    /**
     * Should return name of Javascript object constructor
     */
    protected function doGetConstructorName() {
        return get_class($this);
    }
    
    protected function doListPassthroughParams() {
        return array('containerId' => 'container'); 
    }
    
    protected function doOnGetInitializer(Pm_Js_Initializer $initializer) {
    }
    
    protected function doGetContainerBody() {
        return '';
    }
    
//  Implementation functions

    protected function sendMessage($methodName, $params = array(), $syncHash = false) {
        if (!$this->lockMessages) {
            $msg = new Pm_Message();
            $msg->recipientId = $this->getResponderId();
            $msg->methodName = $methodName;
            $msg->params = $params;
            if ($syncHash !== false) $msg->syncHash = $syncHash;
            $this->sendMessageObject($msg);
        }
    }
    
    protected function sendMessageObject(Pm_Message $message) {
        if ($this->conversation && $this->frontInitialized) {
            $this->conversation->sendClientMessage($message);
        } elseif (in_array($message->methodName, $this->doListDelayableMessages())) {
            $this->messageQueue[] = $message;
        }
    }
    
    protected function doListDelayableMessages() {
        return array();
    }
    
    protected function getMethodMap() {
        $c = get_class($this);
        if (!isset(Pmt_Base::$methodMap[$c])) {
            Pmt_Base::$methodMap[$c] = array();
            $fns = get_class_methods($c);
            foreach ($fns as $m) {
                $prms = array();
                if (substr($m, 0, 15) === 'triggerFrontend') {
                    $mtd = new ReflectionMethod($c, $m);
                    foreach ($mtd->getParameters() as $param) {
                        $n = $param->getName();
                        if ($param->isDefaultValueAvailable()) $dv = $param->getDefaultValue();
                            else $dv = null;
                        $prms[$n] = $dv;        
                    }
                    Pmt_Base::$methodMap[$c][$m] = $prms;
                }
            }
        }
        return Pmt_Base::$methodMap[$c];
    }
    
    protected function triggerEvent($eventType, $params = array()) {
        return Pmt_Impl_Observable::triggerEvent($this, $this->observers, $eventType, $params);
    }
    
    protected function doListFrontendEvents() {
        return array();
    }
    
    protected function frontendObserve($eventType) {
        $this->sendMessage(__FUNCTION__, array($eventType));
    }
    
    protected function frontendUnobserve($eventType) {
        $this->sendMessage(__FUNCTION__, array($eventType));
    }
    
    protected function updateResponderId() {
        if ($this->parent) $this->responderId = $this->parent->getControlsResponderIdPrefix().ucfirst($this->id);
            else $this->responderId = $this->id; 
    }
    
    protected function resolveAssociations() {
        if ($this->isResolvingAssociations === false) {
            $this->isResolvingAssociations = true;
            //Pm_Conversation::log($this.'->resolveAssociations()');
            foreach ($this->associations as $propName => $assocPath) {
                if (method_exists($this, $methodName = 'set'.$propName)) {
                    if (strlen($assocPath)) {
                        if ($control = $this->getControlByPath($assocPath)) {
                            $this->$methodName($control);
                            unset($this->associations[$propName]);
                        } else {
                            //Pm_Conversation::log("Warning: {$this} has missing association: $propName => $assocPath");
                        }
                    } else {
                        $this->$methodName(null);
                        unset($this->associations[$propName]);
                    }
                } else {
                }
            }
            $this->isResolvingAssociations = false;
        }
    }
    
    static function getProperty($control, $propertyName, $defaultValue = null) {
        if (is_array($control)) {
            $res = array();
            foreach ($control as $k => $v) {
                $res[$k] = self::getProperty($v, $propertyName, $defaultValue);
            }
            return $res;
        } else {
            if (strlen($propertyName) && method_exists($control, $g = 'get'.$propertyName)) {
                $res = $control->$g();
            } else {
                $res = $defaultValue;
            }
            return $res;
        }
    }
    
    static function setProperty($control, $propertyName, $value) {
        if (is_array($control)) {
            $res = 0;
            foreach ($control as $c) {
                $res += (int) self::setProperty($c, $propertyName, $value);
            }
            return $res;
        } else {
            if (strlen($propertyName) && method_exists($control, $s = 'set'.$propertyName)) {
                $res = true;
                $control->$s($value);
            } else {
                $res = false;
            }
            return $res;
        }
    }
    
    protected static function getClassMap() {return Pmt_Base::$classMap;}
    
    protected static function setClassMap($v) {Pmt_Base::$classMap = $v;}
    
//  +------------------- Frontend Lifecycle Control -----------------+  
    
    /**
     * @return Pm_Js_Initializer 
     */
    function getInitializer() {
        $res = new Pm_Js_Initializer;
        if ($this->hasJsObject()) {
            $this->getContainerId();
            $res->constructorName = $this->doGetConstructorName();
            $res->constructorParams = array();
            $res->varName = 'window.v_'.$this->getResponderId();
            $res->constructorParams[0] = $this->getPassthroughParams();
            $res->constructorParams[0]['id'] = $this->getResponderId();
            if (strlen($this->getResponderId())) foreach(array_unique(array_merge(array_keys($this->observers), array_keys($this->internalObservers))) as $eventName) {
                if (method_exists($this, $this->eventNameToMethodName($eventName))) {
                    $res->afterScript[] = $res->varName.'.frontendObserve(\''.addslashes($eventName).'\')';
                }
            }
            if ($this->conversation)
                $res->afterScript[] = $this->conversation->getJsId().'.observe(v_'.$this->responderId.')';
        }
        $this->doOnGetInitializer($res);
        return $res;
    }

    function setContainerId($containerHtmlId) {
        $this->containerId = $containerHtmlId;
    }
    
    function getContainerId() {
        if ($this->containerId === false && $this->hasContainer()) $this->containerId = $this->getResponderId();
        return $this->containerId;
    }
    
    function hasContainer() {return true;}
    
    function showContainer() {
        if ($this->hasContainer()) $this->doShowContainer();
    }
    
    protected function doShowContainer() {
        $tagName = $this->containerIsBlock? 'div' : 'span';
        $attribs = $this->getContainerAttribs();
        $attribs['id'] = $this->getContainerId();
        echo Ae_Util::mkElement($tagName, $this->doGetContainerBody(), $attribs);
    }
    
    function pageRender(Pm_I_Renderer $renderer) {
        if (!$this->delayedInitialize && !$this->displayParent) {
            $renderer->renderAssets($this->getAssetLibs());
            if ($this->hasContainer()) {
                ob_start();
                $this->showContainer();
                $renderer->renderContainer(ob_get_clean());
            }
            if (!$this->parent && $this->hasJsObject())
                $renderer->renderInitializer($this->getInitializer());
        }
    }
    
    /**
     * Set's delayed initialization value if we don't want the control to immediately appear in the frontend 
     * @param bool $delayedInitialize
     */
    function setDelayedInitialize($delayedInitialize) {
        $this->delayedInitialize = $delayedInitialize;
    }
    
    function getDelayedInitialize() {
        return $this->delayedInitialize;
    }
    
    /**
     * Whether the control has already initialized it's javascript (or other) front-end
     */
    function isFrontInitialized() {
        return $this->frontInitialized;
    }
    
    
    /**
     * Initialzes control's frontend
     */
    function initializeFront() {
        $this->delayedInitialize = false;
        if ($this->canInitializeFront()) {
            $this->frontInitialization = true;
//          if (!$this->displayParent->isFrontInitialized() && $this->displayParent->canInitializeFront())
//              $this->displayParent->initializeFront();  
            if ($this->hasContainer() && !$this->containerInitialized) {
                $this->displayParent->initializeChildContainer($this);
            }
            if ($this->hasJsObject() && !$this->jsObjectInitialized) $this->parent->initializeChildObject($this);
            $this->frontInitialized = true;
            $this->frontInitialization = false;
            $this->sendDelayedMessages();
        }
    }
    
    function canInitializeFront() {
        $res = 
            !$this->frontInitialized
            && !$this->frontInitialization 
            && (!$this->hasContainer() || $this->displayParent && ($this->displayParent->isFrontInitialized() || $this->displayParent->canInitializeFront())) 
            
            && $this->conversation  
            && !$this->conversation->isPageRender()
            
            && $this->parent;
             
        return $res;
    }
    
    function notifyFrontInitialized() {
        $this->frontInitialized = true;
    }
        
    
    function notifyContainerInitialized() {
        $this->containerInitialized = true;
    }
    
    function notifyJsObjectInitialized() {
        $this->jsObjectInitialized = true;
    }
    
    // +-------------- Pm_I_Refcontrol implementation ---------------+

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
    

    // +-------------------------------------------------------------+ 
    
    protected function refSetAssoc ($propName, $assocObject) {
        $this->$propName = $assocObject;
        if (is_object($assocObject)) $this->refAdd($assocObject);
    }
    
    /**
     * Performs cleanup functions and removes all references to this object so it won't be serialized with others upon session save
     */
    function destroy() {
        $this->sendMessage('destroy');
        $this->triggerEvent('destroy');
        $this->doOnDestroy();
        Pmt_Guardian::getInstance()->shouldBeDeleted($this, $this->id);
        $this->refNotifyDestroying();
    }
    
    protected function doOnDestroy() {
    }
    
    function setEtc($etc) {
        $this->etc = $etc;
    }

    function getEtc() {
        return $this->etc;
    }
    
    function log($args) {
        if ($this->debug) {
            $a = func_get_args();
            $a = array_merge(array($this->getResponderId()), $a);
            call_user_func_array(array('Pm_Conversation', 'log'), $a);
        }
    }
    
    function logMessage($message) {
        if (($c = $this->getController()) && $this->debug) {
            $a = func_get_args();
            $b = array_merge(array($this->getResponderId()), $a);
            call_user_func_array(array($c, 'logMessage'), $b);
        }
    }

    function setDebug($debug) {
        if ($debug !== ($oldDebug = $this->debug)) {
            $this->debug = $debug;
        }
    }

    function getDebug() {
        return $this->debug;
    }
    
    /**
     * @return array Names of memebers that should _not_ be serialized
     */    
    protected function doOnSleep() {
        return array();
    }
    
    function __sleep() {
        $sleepExclude = $this->doOnSleep();
        if (!is_array($sleepExclude)) $sleepExclude = array();
        $v = array_diff(array_keys(get_object_vars($this)), $sleepExclude);
        return $v; 
    }
    
    function getClassName() {
        return get_class($this);
    }
    
    protected function sendDelayedMessages() {
        if (count($this->messageQueue)) {
            $tmp = $this->messageQueue;
            $this->messageQueue = array();
            foreach ($tmp as $msg) $this->conversation->sendClientMessage($msg);
        }
    }
    
    static function getClassMethodsWithCache($objOrClass) {
        if (is_object($objOrClass)) $objOrClass = get_class($objOrClass);
        if (!isset(Pmt_Base::$classMethods[$objOrClass])) {
            Pmt_Base::$classMethods[$objOrClass] = get_class_methods($objOrClass);
        }
        return Pmt_Base::$classMethods[$objOrClass];
    }

    /**
     * Returns array of parents starting from immediate parent of current control and ending with topmost ('root') parent.
     * @param bool $rootIsFirst - whether to reverse the result (so topmost parent will be first in the result array)
     * @return array Array of Pmt_Base descendants 
     */
    function getAllParents($rootIsFirst = false) {
        $res = array();
        $curr = $this;
        while ($curr = $curr->getParent()) $res[] = $curr;
        if ($rootIsFirst) $res = array_reverse($res);
        return $res;
    }
    
    /**
     * @return Pmt_Application
     */
    function getApplication() {
        $res = null;
        $c = $this->getController();
        if ($c) {
            $wf = $c->getWebFront();
            $res = $c->getApplication();
        }
        return $res;
    }
    
}

?>