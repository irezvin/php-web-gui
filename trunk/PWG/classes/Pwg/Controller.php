<?php

/**
 * Creates and contains controls.
 * Shows their template.
 * Handles their events. Automatically binds events "handle<ControlId><EventName>" to corresponding events of corresponding controls 
 */
class Pmt_Controller extends Pmt_Composite_Display implements Pmt_I_Controller {

    protected $delegatePrototypes = array();
    
    protected $delegates = false;

    /**
     * @var array (idOfMyControl => array(delegateId => idOfDelegateMember))
     */
    protected $delegateExterns = false;
    
    /**
     * @var Pmt_I_Web_Front 
     */
    protected $webFront = false;
    
    /**
     * @var Pm_Thread
     */
    protected $thread = false;

    function sendLoopbackMessage($methodName, array $params = array()) {
        $this->sendMessage(__FUNCTION__, array($methodName, $params));
    }
    
    function execJavascript($javascript) {
    	$this->sendMessage(__FUNCTION__, array(new Ae_Js_Val($javascript)));
    }
    
//  Pmt_Composite
    
    function createControl(array $prototype, $id = false, $baseClass = 'Pmt_Base') {
        $res = parent::createControl($prototype, $id, $baseClass);
        $this->observeControl($res, false, false, true);
        return $res;
    }
    
//  Pmt_I_Control

    /**
     * Adds Control to the Controller's jurisdiction.
     * 
     * Automatically binds methods of Controller that have names such as hanlde<ControlId><EventName> to $control's events.
     * Binds manually-specified methods to given events.
     * Adds control to $this->controls[] array and calls sets it's setController() setter to $this.
     * Subscribes control to Conversation of this Controller 
     *
     * @param Pmt_I_Control $control 
     * @param array|bool $eventMap (eventName => methodName); if false, control's eventMap property will be used
     * @param bool $setAsParent Also set control's parent to this Controller
     * @param bool $autoMatch Whether to perform auto-matching of event names 
     * @return Pmt_I_Control
     */
    function observeControl(Pmt_I_Control $control, $eventMap = false, $setAsParent = false, $autoMatch = true) {
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
        
        if ($autoMatch) foreach (Pmt_Base::getClassMethodsWithCache($this) as $m)
            if (!strncasecmp($m, $s = 'handle'.$id, $idl + 6)) { // matches handle<ControlId><EventName>?
                if (strlen($evt = substr($m, $idl + 6))) { // extract event name
                    $evt{0} = strtolower($evt{0});
                    $control->observe($evt, $this, $m);
                }
            }
            
        $control->setController($this);
        if ($setAsParent) $this->addControl($control);
    }
    
    protected function autoObserve (Pm_I_Observable $observable, $idForAutoMatch = false, array $eventMap = array()) {
        foreach ($eventMap as $event => $method) if (method_exists($this, $method)) {
            $observable->observe($event, $this, $method);
        }
        
        if ($idForAutoMatch !== false) foreach (Pmt_Base::getClassMethodsWithCache($this) as $m)
            if (!strncmp($m, $s = 'handle'.ucfirst($idForAutoMatch), strlen($s))) { // matches handle<ControlId><EventName>?
                if (strlen($evt = substr($m, strlen($s)))) { // extract event name
                    $evt{0} = strtolower($evt{0});
                    $observable->observe($evt, $this, $m);
                }
            }
    }
    
    function addControl(Pmt_I_Control $control) {
        $res = parent::addControl($control);
        $id = $control->getId();
        if (isset($this->$id)) $this->$id = $control;
        return $res;
    }
    
    function handleEvent(Pm_I_Observable $observable, $eventType, $params = array()) {
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
    
    function setConversation(Pm_I_Conversation $conversation) {
        if ($this->conversation !== $conversation) {
            $this->conversation = $conversation;
            if (is_object($this->conversation))
                $this->conversation->registerResponder($this);
        }
        parent::setConversation($conversation);
    }

    function setWebFront(Pm_I_Web_Front $webFront) {
        $this->webFront = $webFront;
    }
    
    function setThread(Pm_Thread $thread) {
        $this->thread = $thread;    
    }
    
    /**
     * @return Pm_Thread
     */
    function getThread() {
        return $this->thread;
    }

    /**
     * @return Pm_I_Web_Front 
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
            $this->delegates[$id] = Pmt_Autoparams::factory($p, 'Pmt_Controller_Delegate');
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
     * @return Pmt_Controller_Delegate
     */
    protected function getDelegate($id) {
        if (in_array($id, $this->delegates)) $res = $this->delegates[$id];
            else $res = false;
        return $res;
    }
    

    protected function getControlPrototypes() {
        if ($this->delegates) {
            $res = array();
            Ae_Util::ms($res, $this->controlPrototypes);
            foreach ($this->delegates as $d) Ae_Util::ms($res, $d->getControlPrototypes(true));
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
        return 'Pmt_Controller';
    }
    
    protected function sendWindowControlMessage($action, array $params = array()) {
        $params['action'] = $action;
        return $this->triggerEvent(Pmt_I_MDIWindow::evtWindowControlMessage, $params);
    }
    
    
//    function canInitializeFront() {
//        $res = parent::canInitializeFront();
//        Pm_Conversation::log("$this", array(
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
//        Pm_Conversation::log("Res is ", $res);
//        return $res;
//    }
    
    
}

?>