<?php

class Pwg_Composite extends Pwg_Base implements Pwg_I_Control_Parent, Pwg_I_Observer {

    protected $controls = false;

    protected $controlPrototypes = array();

    protected $allowedChildrenClass = false;

    protected $defaultChildrenClass = false;
    
    protected $allowPassthroughEvents = false;
    
    protected $controlsCreated = false;
    
    protected $nextControlClassIgnore = false;

    //  Pwg_Composite

    protected function getControlPrototypes() {
        return $this->controlPrototypes;
    }

    protected function setControlPrototypes(array $proto) {
        $this->controlPrototypes = $proto;
    }

    protected function createControls() {
        $this->controls = array();
        foreach ($this->getControlPrototypes() as $id => $prototype) 
            if ($prototype !== false) {
                $this->createControl($prototype, is_numeric($id)? false : $id);
            }
        $this->controlsCreated = true;
        $this->doAfterControlsCreated();
        $this->resolveAssociations();
    }

    protected function resolveAssociations() {
        parent::resolveAssociations();
        if (!$this->isResolvingAssociations) {
            if (is_array($this->controls)) foreach ($this->controls as $con) $con->resolveAssociations();
        }
    }
    
    function createManyControls(array $prototypes, $dontResolveAssociations = false) {
    	$res = array();
    	foreach ($prototypes as $id => $prototype) {
    		$con = $this->createControl($prototype, is_numeric($id)? false : $id);
    		$res[$con->getId()] = $con;
    	}
    	if (!$dontResolveAssociations) $this->resolveAssociations();
    	return $res;
    }

    /**
     * @param string $id
     * @param array $prototype
     * @return Pwg_I_Control
     */
    function createControl(array $prototype, $id = false, $baseClass = false) {
        if ($baseClass === false) {
            if ($this->defaultChildrenClass !== false) $baseClass = $this->defaultChildrenClass;
            elseif ($this->allowedChildrenClass !== false) $baseClass = $this->allowedChildrenClass;
            else $baseClass = 'Pwg_Base';
        }

        if ($id !== false) {
            $prototype['id'] = $id;
        }
        elseif (isset($prototype['id'])) {
            $id = $prototype['id'];
        }
            
        if (!isset($prototype['class']) && strlen($id)) {
            foreach (Pwg_Base::getClassMap() as $px => $class) {
                if (!strncmp($id, $px, strlen($px))) { $prototype['class'] = $class; break; }
            }
        }
        $class = isset($prototype['class']) && strlen($prototype['class'])? $prototype['class'] : $baseClass;
        
        if ($class === 'Pwg_Base') trigger_error("Cannot retrieve class name to instantiate control ".$prototype['id'], E_USER_WARNING);
        
        $res = new $class ($prototype);
        if (!$res instanceof Pwg_I_Control) throw new Exception ('{$class} does not implement Pwg_I_Control');
        $this->addControl($res);
        return $res;
    }
    
    protected function ignoreNextControlClass() {
    	$this->nextControlClassIgnore = true;
    }
    
    function addControl(Pwg_I_Control $control) {
        if ($this->nextControlClassIgnore) {
        	$this->nextControlClassIgnore = false;
        } else {
	    	if (($this->allowedChildrenClass !== false) && (! $control instanceof $this->allowedChildrenClass)) {
	        	$c = get_class($control);
	            throw new Exception ("Only instances of '{$this->allowedChildrenClass}' class are allowed as children of {$this}, {$c} given");
	        }
        }
        $this->controls[$control->getId()] = $control;
        $control->setParent($this);
        $this->setConversationForChildren(array($control));
        return $control;
    }

    function deleteControl(Pwg_I_Control $control) {
        if (isset($this->controls[$id = $control->getId()]) && ($this->controls[$id] === $control)) {
            $control->destroy();
            // TODO: implement control deletion
        } else {
            throw new Exception("Can't delete child control, since '{$control}' is not child of the '{$this}'");
        }
    }

    //  Pwg_I_Control_Parent

    function listControls() {
        if ($this->controls === false) $this->createControls();
        return array_keys($this->controls);
    }

    /**
     * @param string $id
     * @return Pwg_I_Control
     */
    function getControl($id) {
        if (!in_array($id, $this->listControls())) {
            $res = false;
        } else $res = $this->controls[$id];
        return $res;
    }

    /**
     * @param string $responderId
     * @return Pwg_I_Control
     */
    function getControlByResponderId($responderId) {
        $res = false;
        if (!strlen($this->responderIdPrefix) || !strncmp($responderIdPrefix, $this->responderIdPrefix, strlen($this->responderIdPrefix))) {
            foreach($this->listControls() as $c) {
                $con = $this->getControl($c);
                if ($con->getResponderId() == $responderId) {
                    $res = $con;
                    break;
                } elseif ($con instanceof Pwg_I_Control_Parent) {
                    if ($res = $con->getControlByResponderId) break;
                    else $res = false;
                }
            }
        }
        return $res;
    }

    /**
     * @return string
     */
    function getControlsResponderIdPrefix() {
        return $this->responderId;
    }

    // Overrides

    function setConversation(Pwg_I_Conversation $conversation) {
        parent::setConversation($conversation);
        $childControls = array();
        $this->listControls();
        if (is_array($this->controls)) $this->setConversationForChildren($this->controls);
    }

    protected function setConversationForChildren(array $controls) {
        if ($this->conversation)
            foreach ($controls as $control) $this->conversation->registerResponder($control);
    }

    /**
     * @return Pwg_Js_Initializer
     */
    function getInitializer() {
        $res = parent::getInitializer();
        $res->initializers = $this->getInitializers();
        return $res;
    }

    function hasJsObject() {
        return false;
    }

    function getInitializers() {
        $res = array();
        foreach ($this->listControls() as $l) {
            $control = $this->getControl($l);
            if ($i = $control->getInitializer()) $res[] = $i;
        }
        return $res;
    }

    function getAssetLibs() {
        $res = $this->doGetAssetLibs();
        foreach ($this->listControls() as $i) {
            $con = $this->getControl($i)->getAssetLibs();
            $res = array_merge($con, $res);
        }
        $res = array_unique($res);
        return $res;
    }
    
    
    //  ------------------- Passthrough events support -------------------
    
    function setAllowPassthroughEvents($allowPassthroughEvents) {
        $this->allowPassthroughEvents = $allowPassthroughEvents;
    }

    function getAllowPassthroughEvents() {
        return $this->allowPassthroughEvents;
    }
    
    /**
     * @param string $eventType Can be '__controlNameEvent' where '__' is double-underscore and controlName is name of control in listControlsWithPassthroughEvents() list and Event is event name of that control  
     */
    function observe($eventType, Pwg_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array()) {
        if ($this->allowPassthroughEvents && ($pe = $this->detectPassthroughEvent($eventType))) {
            if ($c = $this->getControl($pe[0])) {
                $c->observe($pe[1], $this, 'passEventThrough');
            } else {
                Pwg_Conversation::log("Warning: passthrough target for event '{$eventType}' not found by {$this} (searched for control '{$pe[0]}').");
            }
        } 
        parent::observe($eventType, $observer, $methodName, $extraParams);
    }
    
    function listControlsWithPassthroughEvents() {
        return $this->listControls();
    }
    
    protected function detectPassthroughEvent($eventType) {
        $control = false;
        $res = false;
        if (substr($eventType, 0, 2) == '__') {
            foreach ($this->listControlsWithPassthroughEvents() as $c) {
                $uc = ucfirst($c);
                $l = strlen($c) + 2;
                if (!strncmp($eventType, '__'.$uc, $l)) {
                    $eventType = substr($eventType, $l);
                    if (strlen($eventType)) {
                        $eventType{0} = strtolower($eventType{0});
                        $res = array($c, $eventType);
                    } 
                    break;
                }
            }
        }
        return $res;
    }
    
    function unobserve($eventType, Pwg_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array()) {
        if ($this->allowPassthroughEvents && ($pe = $this->detectPassthroughEvent($eventType))) {
            if ($c = $this->getControl($pe[0])) return $c->unobserve($pe[1], $this, 'passEventThrough');
                else Pwg_Conversation::log("Warning: passthrough target for event '{$eventType}' not found by {$this}.");
        } else 
            return parent::unobserve($eventType, $observer, $methodName, $extraParams);
    }
    
    function passEventThrough(Pwg_I_Control $control, $eventType, array $params = array()) {
        if (isset($params['__passthroughSource']) && $params['__passthroughSource'] instanceof Pwg_I_Control)
            $id = $params['__passthroughSource']->getId();
        else    
            $id = $control->getId();
            
        $params['__passthroughSource'] = $this;
        return Pwg_Impl_Observable::triggerEvent($control, $this->observers, '__'.ucfirst($id).ucfirst($eventType), $params);
    }
    
    //  ------------------- Template methods -------------------

    protected function doAfterControlsCreated() {
    }

    //  ------------------- Dynamic control creation support -----------------

    /**
     * @return Pwg_I_Web_Front
     */
    protected function getNearestWebFront() {
        $res = false;
        $p = $this;
        while ($p) {
            if ($p instanceof Pwg_I_Controller && ($wf = $p->getWebFront())) {
                $res = $wf;
                break;
            }
            $p = $p->getParent();
        }
        return $res;
    }
    
    function initializeChildObject(Pwg_I_Control $child) {
        $assets = $child->getAssetLibs();
        $assetUrls = array();
        if (count($assets)) {
            $wf = $this->getNearestWebFront();
            $assets = $wf->applyHacksToAssetLibs($assets);
            foreach ($assets as $k => $v) {
                $assetUrls[] = $wf->getJsOrCssUrl($v);
            }
        }
        
        $ls = array();
        
        $child->notifyJsObjectInitialized();
        
        $i = $child->getInitializer();
        $js = new Ae_Js();
        $initFn = new Ae_Js_Var('function () {'.$i->getInitScript($js).'}');
        
        // TODO: fix double (at least!) call to initializeControl() of same super-parent (since without syncHash $child->getResponderId() it caused excessive calls to js intantiation methods) 
        $this->sendMessage('initializeControl', array($assetUrls, $initFn), $child->getResponderId());
    }

    function notifyFrontInitialized() {
        $this->frontInitialized = true;

        // We should notify all child controls without delayed initialization
        foreach ($this->listControls() as $i) {
            $c = $this->getControl($i);
            if (!$c->isFrontInitialized() && !$c->getDelayedInitialize()) $c->notifyFrontInitialized();
        }

    }
    
    protected function doOnDestroy() {
        foreach (array_keys($this->controls) as $i) {
            if (isset($this->controls[$i]) && ($this->controls[$i] instanceof Pwg_I_Control))
                $this->controls[$i]->destroy();
        }
        parent::doOnDestroy();
    }
    
    function notifyJsObjectInitialized() {
        parent::notifyJsObjectInitialized();
        foreach ($this->controls as $c) 
            if (!$c->getDelayedInitialize()) $c->notifyJsObjectInitialized();
    }
    
    /**
     * Locates children of current control with value of $propName matching $propValue 
     * 
     * @param array $nameVals   Array (name => val, name => val) mask of values to compare (if there are several pairs, all properties should match them)
     * @param string $baseClass Name of base class to filter results (if FALSE, results of all classes will be returned) 
     * @param $strict Use strict comparison of property values
     * @param $recursive Search in parents of composite controls (not only matching controls are being searched, but all composite children)
     * @return array
     * 
     * Pwg_Composite::getProperty static method is used retrieve property values (it uses getters with names like get<PropName>). 
     * @see Pwg_Composite::getProperty
     */
    function findChildrenByProperty($propName, $propValue, $baseClass = false, $strict = false, $recursive = true) {
        return $this->findChildrenByProperties(array($propName => $propValue), $baseClass, $strict, $recursive);
    }
    
    /**
     * Locates children of current control with property values that match ones provided in $nameVals. 
     * 
     * @param array $nameVals   Array (name => val, name => val) mask of values to compare (if there are several pairs, all properties should match them)
     * @param string $baseClass Name of base class to filter results (if FALSE, results of all classes will be returned) 
     * @param $strict Use strict comparison of property values
     * @param $recursive Search in parents of composite controls (not only matching controls are being searched, but all composite children)
     * @return array
     * 
     * Pwg_Composite::getProperty static method is used retrieve property values (it uses getters with names like get<PropName>). 
     * @see Pwg_Composite::getProperty
     */
    function findChildrenByProperties(array $nameVals, $baseClass = false, $strict = false, $recursive = true) {
        return self::findControlChildrenByProperties($this, $nameVals, $baseClass, $strict, $recursive);
    }
    
    /**
     * @param Pwg_I_Control_Parent $control Parent of controls to search in
     * @param array $nameVals   Array (name => val, name => val) mask of values to compare (if there are several pairs, all properties should match them)
     * @param string $baseClass Name of base class to filter results (if FALSE, results of all classes will be returned) 
     * @param $strict Use strict comparison of property values
     * @param $recursive Search in parents of composite controls (not only matching controls are being searched, but all composite children)
     * @return array
     * 
     * Pwg_Composite::getProperty static method is used retrieve property values (it uses getters with names like get<PropName>). 
     * @see Pwg_Composite::getProperty
     */
    static function findControlChildrenByProperties(Pwg_I_Control_Parent $control, array $nameVals, $baseClass = false, $strict = false, $recursive = true) {
        $res = array();
        foreach ($control->listControls() as $c) {
            $con = $control->getControl($c);
            if (($baseClass === false) || ($con instanceof $baseClass)) {
                $match = true;
                foreach ($nameVals as $k => $v) {
                    $val = Pwg_Base::getProperty($con, $k);
                    if ($strict && ($v !== $val) || !$strict && ($v != $val)) {
                        $match = false;
                        break;
                    }
                }
                if ($match) $res[] = $con;
            }
            if ($recursive && $con instanceof Pwg_I_Control_Parent) {
                $res = array_merge($res, self::findControlChildrenByProperties($con, $nameVals, $baseClass, $strict, $recursive));
            }
        }
        return $res;
    }

}
?>