<?php

class Pwg_Controller_Delegate extends Pwg_Autoparams implements Pwg_I_Observer, Pwg_I_Refcontrol {
    
    /**
     * @var Pwg_Controller
     */
    protected $controller = false;
    
    protected $controlPrototypes = array();
    
    protected $externMap = false;
    
    protected $resultingExternMap = false;
    
    protected $externPrefix = false;
    
    protected function setController(Pwg_Controller $controller) {
        $this->controller = $controller;
        $this->addObservable($this->controller, 'Controller');
    }

    /**
     * @return Pwg_Controller
     */
    function getController() {
        return $this->controller;
    }   
    
    protected function setControlPrototypes(array $controlPrototypes) {
        $this->controlPrototypes = $controlPrototypes;
    }

    protected function doGetControlPrototypes() {
        return $this->controlPrototypes;
    }
    
    function getControlPrototypes($remappedForExternalController = false) {
        if ($remappedForExternalController) {
            $res = array();
            $externMap = $this->getExternMap();
            foreach ($this->doGetControlPrototypes() as $k => $v) {
                if (isset($this->externMap[$k])) $k = $this->externMap[$k];
                $res[$k] = $v;
            }
            return $res;
        } else 
            return $this->doGetControlPrototypes();
    }
    
    /**
     * @return array (internalControlId => externalControlId)
     *
     */
    function getExternMap() {
        if ($this->resultingExternMap === false) {
            $this->resultingExternMap = array();
            foreach (array_keys(Ae_Util::getClassVars(get_class($this))) as $vn) {
                $ext = $vn;
                if (strlen($this->externPrefix)) $ext = $this->externPrefix.ucfirst($ext);
                $this->resultingExternMap[$vn] = $ext;
            }
        }
        if (is_array($this->externMap)) Ae_Util::ms($this->resultingExternMap, $this->externMap);
        return $this->resultingExternMap;
    }

    function onInitialize() {
    }
    
    function onControlsCreated() {
    }
    
    function addObservable (Pwg_I_Observable $observable, $id, array $eventMap = array()) {
        $em = $this->getExternMap();
        if (isset($em[$id])) {
            $id = $em[$id];
            $this->$id = $observable;
        }
        
        foreach ($eventMap as $event => $methods) {
            if (!is_array($methods)) $methods = array($methods);
            foreach ($methods as $method)
                if (method_exists($this, $method)) {
                    $observable->observe($event, $this, $method);
                }
        }
        
        foreach (get_class_methods($this) as $m)
            if (!strncmp($m, $s = 'handle'.ucfirst($id), strlen($s))) { // matches handle<ControlId><EventName>?
                if (strlen($evt = substr($m, strlen($s)))) { // extract event name
                    $evt{0} = strtolower($evt{0});
                    $observable->observe($evt, $this, $m);
                }
            }
    }

    
//  +--------------- Pwg_I_Observer implementation ----------------+
    
    function handleEvent(Pwg_I_Observable $observable, $eventType, $params = array()) {
    }
    
    
//  +-------------- Pwg_I_Refcontrol implementation ---------------+

    protected $refReg = array();

    protected function refGetSelfVars() {
        $res = array();
        foreach (array_keys(get_object_vars($this)) as $v) $res[$v] = & $this->$v;
        return $res;
    }
    
    function refHas($otherObject) { return Pwg_Impl_Refcontrol::refHas($otherObject, $this->refReg); }
    
    function refAdd($otherObject) { return Pwg_Impl_Refcontrol::refAdd($this, $otherObject, $this->refReg); }
    
    function refRemove($otherObject, $nonSymmetrical = false) { $v = $this->refGetSelfVars(); return Pwg_Impl_Refcontrol::refRemove($this, $otherObject, $v, false, $nonSymmetrical); }

    function refNotifyDestroying() { return Pwg_Impl_Refcontrol::refNotifyDestroying($this, $this->refReg); }

//  +-------------------------------------------------------------+ 
    
}

?>