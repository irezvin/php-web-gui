<?php

/**
 * Creates and contains controls.
 * Shows their template.
 * Handles their events. Automatically binds events "handle<ControlId><EventName>" to corresponding events of corresponding controls 
 */
class Pwg_Controller extends Pwg_Composite_Display implements Pwg_I_Controller {

    protected $delegatePrototypes = array();
    
    protected $delegates = false;

    /**
     * @var array (idOfMyControl => array(delegateId => idOfDelegateMember))
     */
    protected $delegateExterns = false;
    
    /**
     * @var Pwg_I_Web_Front 
     */
    protected $webFront = false;
    
    /**
     * @var Pwg_Thread
     */
    protected $thread = false;

    function sendLoopbackMessage($methodName, array $params = array()) {
        $this->sendMessage(__FUNCTION__, array($methodName, $params));
    }
    
    function execJavascript($javascript) {
    	$this->sendMessage(__FUNCTION__, array(new Ac_Js_Val($javascript)));
    }
    
//  Pwg_Composite
    
    function createControl(array $prototype, $id = false, $baseClass = 'Pwg_Base') {
        $res = parent::createControl($prototype, $id, $baseClass);
        $this->observeControl($res, false, false, true);
        return $res;
    }
    
//  Pwg_I_Control

    /**
     * Adds Control to the Controller's jurisdiction.
     * 
     * Automatically binds methods of Controller that have names such as hanlde<ControlId><EventName> to $control's events.
     * Binds manually-specified methods to given events.
     * Adds control to $this->controls[] array and calls sets it's setController() setter to $this.
     * Subscribes control to Conversation of this Controller 
     *
     * @param Pwg_I_Control $control 
     * @param array|bool $eventMap (eventName => methodName); if false, control's eventMap property will be used
     * @param bool $setAsParent Also set control's parent to this Controller
     * @param bool $autoMatch Whether to perform auto-matching of event names 
     * @return Pwg_I_Control
     */
    function observeControl(Pwg_I_Control $control, $eventMap = false, $setAsParent = false, $autoMatch = true) {
        if ($eventMap === false) $eventMap = $control->getEventMap();
        
        foreach ($eventMap as $event => $method) {
            if (is_array($method)) $methods = $method;
                else $methods = array($method);
            
            foreach ($methods as $method) {
                if (method_exists($this, $method)) {
                    $control->observe($event, $this, $method);
                } else {
                    foreach ($this->delegates as $d) {
                        if (method_exists($d, $method)) $control->observe($event, $d, $method);
                    }
                }
            }
        }
        
        $id = $control->getId();
        $idl = strlen($id);
        
        if (isset($this->delegateExterns[$id])) 
            foreach (array_keys($this->delegateExterns[$id]) as $delegateId) 
                $this->delegates[$delegateId]->addObservable($control, $id, $eventMap);  
        
        if ($autoMatch) foreach (Pwg_Base::getClassMethodsWithCache($this) as $m)
            if (!strncasecmp($m, $s = 'handle'.$id, $idl + 6)) { // matches handle<ControlId><EventName>?
                if (strlen($evt = substr($m, $idl + 6))) { // extract event name
                    $evt{0} = strtolower($evt{0});
                    $control->observe($evt, $this, $m);
                }
            }
            
        $control->setController($this);
        if ($setAsParent) $this->addControl($control);
    }
    
    protected function autoObserve (Pwg_I_Observable $observable, $idForAutoMatch = false, array $eventMap = array()) {
        foreach ($eventMap as $event => $method) if (method_exists($this, $method)) {
            $observable->observe($event, $this, $method);
        }
        
        if ($idForAutoMatch !== false) foreach (Pwg_Base::getClassMethodsWithCache($this) as $m)
            if (!strncmp($m, $s = 'handle'.ucfirst($idForAutoMatch), strlen($s))) { // matches handle<ControlId><EventName>?
                if (strlen($evt = substr($m, strlen($s)))) { // extract event name
                    $evt{0} = strtolower($evt{0});
                    $observable->observe($evt, $this, $m);
                }
            }
    }
    
    function addControl(Pwg_I_Control $control) {
        $res = parent::addControl($control);
        $id = $control->getId();
        if (isset($this->$id)) $this->$id = $control;
        return $res;
    }
    
    function handleEvent(Pwg_I_Observable $observable, $eventType, $params = array()) {
    }

    function showHeadElements() {
    }

    function hasJsObject() {
        return true; 
    }
    
    function logMessage($message) {
        $a = func_get_args();
        $m = array_merge(array($this->getResponderId()), $a);
        $this->sendMessage(__FUNCTION__, array($m));
    }
        
    protected function doGetAssetLibs() {
        return array('widgets.js', 'pax.css');
    }
    
    function setConversation(Pwg_I_Conversation $conversation) {
        if ($this->conversation !== $conversation) {
            $this->conversation = $conversation;
            if (is_object($this->conversation))
                $this->conversation->registerResponder($this);
        }
        parent::setConversation($conversation);
    }

    function setWebFront(Pwg_I_Web_Front $webFront) {
        $this->webFront = $webFront;
    }
    
    function setThread(Pwg_Thread $thread) {
        $this->thread = $thread;    
    }
    
    /**
     * @return Pwg_Thread
     */
    function getThread() {
        return $this->thread;
    }

    /**
     * @return Pwg_I_Web_Front 
     */
    function getWebFront() {
        $res = $this->webFront;
        if ($res && $this->parent) {
            $c = $this->getController();
            if ($c) $res = $c->getWebFront();
        }
        return $res;
    }
    
    protected function listDelegates() {
        if ($this->delegates === false) $this->initializeDelegates();
        return array_keys($this->delegates);  
    }
    
    protected function initializeDelegates() {
        $this->delegates = array();
        $this->delegateExterns = array();
        foreach ($this->delegatePrototypes as $id => $p) {
            $p['controller'] = $this;
            $this->delegates[$id] = Pwg_Autoparams::factory($p, 'Pwg_Controller_Delegate');
            $this->refAdd($this->delegates[$id]);
            $externMap = $this->delegates[$id]->getExternMap();
            foreach ($externMap as $delegateId => $myId) {
                if (!isset($this->delegateExterns[$myId])) $this->delegateExterns[$myId] = array();
                $this->delegateExterns[$myId][$id] = $delegateId;
            }
        }
    }
    
    /**
     * @param string $id
     * @return Pwg_Controller_Delegate
     */
    protected function getDelegate($id) {
        if (in_array($id, $this->delegates)) $res = $this->delegates[$id];
            else $res = false;
        return $res;
    }
    

    protected function getControlPrototypes() {
        if ($this->delegates) {
            $res = array();
            Ac_Util::ms($res, $this->controlPrototypes);
            foreach ($this->delegates as $d) Ac_Util::ms($res, $d->getControlPrototypes(true));
            return $res;
        } else return $this->controlPrototypes;
    }
    
    
    protected function createControls() {
        $this->controls = array();
        
        $this->initializeDelegates();
        foreach ($this->delegates as $d) $d->onInitialize();
        
        foreach ($this->getControlPrototypes() as $id => $prototype)
            $this->createControl($prototype, is_numeric($id)? false : $id);
        $this->controlsCreated = true;
        $this->doAfterControlsCreated();
        $this->resolveAssociations();
        
        foreach ($this->delegates as $d) $d->onControlsCreated();
    }
    
    function doOnSleep() {
        return array_merge(parent::doOnSleep(), array('thread'));
    }
    
    protected function doGetConstructorName() {
        return 'Pwg_Controller';
    }
    
    protected function sendWindowControlMessage($action, array $params = array()) {
        $params['action'] = $action;
        return $this->triggerEvent(Pwg_I_MDIWindow::evtWindowControlMessage, $params);
    }
    
    
//    function canInitializeFront() {
//        $res = parent::canInitializeFront();
//        Pwg_Conversation::log("$this", array(
//            '!frontInitialized' => !$this->frontInitialized,
//            '!$this->frontInitialization' => !$this->frontInitialization,
//            '!$this->hasContainer()' => !$this->hasContainer(),
//            '   || $this->displayParent' => (bool) $this->displayParent,
//            '      && $this->displayParent->isFrontInitialized()' => $this->displayParent && $this->displayParent->isFrontInitialized(), 
//            '         || $this->displayParent->canInitializeFront()' =>  $this->displayParent && $this->displayParent->canInitializeFront(),
//            '(!$this->hasContainer() || $this->displayParent && ($this->displayParent->isFrontInitialized() || $this->displayParent->canInitializeFront()))' =>
//                !$this->hasContainer() || $this->displayParent && ($this->displayParent->isFrontInitialized() || $this->displayParent->canInitializeFront()),
//            '$this->conversation' => (bool) $this->conversation,
//            '!$this->conversation->isPageRender()' => $this->conversation && !$this->conversation->isPageRender(),
//            '$this->parent' => (bool) $this->parent, 
//        ));
//        Pwg_Conversation::log("Res is ", $res);
//        return $res;
//    }
    
    
}

?>