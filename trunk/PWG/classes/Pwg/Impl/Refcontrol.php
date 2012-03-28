<?php

final class Pwg_Impl_Refcontrol {

/*
 
    // Copy and paste this code into the concrete class:



//  +-------------- Pwg_I_Refcontrol implementation ---------------+

    protected $refReg = array();

    protected function refGetSelfVars() {
        $res = array();
        foreach (array_keys(get_object_vars($this)) as $v) $res[$v] = & $this->$v;
        return $res;
    }
    
    function refHas($otherObject) { return Pwg_Impl_Refcontrol::refHas($otherObject, $this->refReg); }
    
    function refAdd($otherObject) { return Pwg_Impl_Refcontrol::refAdd($this, $otherObject, $this->refReg); }
    
    function refRemove($otherObject) { $v = $this->refGetSelfVars(); return Pwg_Impl_Refcontrol::refRemove($this, $otherObject, $v, false); }

    function refNotifyDestroying() { return Pwg_Impl_Refcontrol::refNotifyDestroying($this, $this->refReg); }

//  +-------------------------------------------------------------+ 
 


 */ 
    
    static function refHas($otherObject, array & $refReg) {
        return array_search($otherObject, $refReg, true) !== false;
        foreach ($refReg as & $r) if ($r === $otherObject) {
//          var_dump("Have ".' ('.get_class($otherObject).')');
            return true;
        }
//      var_dump("Don't have ".' ('.get_class($otherObject).')');
        return false;
    }
    
    static function refAdd($thisObject, $otherObject, array & $refReg) {
        
//      var_dump("Adding ".' ('.get_class($otherObject).')');
        if (is_object($otherObject)) {
            if ($otherObject instanceof Pwg_I_Refcontrol) { 
                if (!self::refHas($otherObject, $refReg)) $refReg[] = $otherObject; 
                if (!$otherObject->refHas($thisObject)) $otherObject->refAdd($thisObject); 
//                $refReg[] = $otherObject;
//                if (!isset($thisObject->_rc__refLock)) $thisObject->_rc__refLock = 0;
//                $thisObject->_rc__refLock++;
//                if (!isset($otherObject->_rc__refLock)) $otherObject->_rc__refLock = 0;
//                if (!$otherObject->_rc__refLock) $otherObject->refAdd($thisObject);
//                $thisObject->_rc__refLock--;
            }
        }
    }
    
    static function refCleanArray($otherObject, & $array) {
        foreach (array_keys($array) as $k) {
            if ($array[$k] === $otherObject) {
                unset($array[$k]);
            } elseif (is_array($array[$k])) self::refCleanArray($otherObject, $array[$k]);
        }
    }
    
    static function refRemove($thisObject, $otherObject, array & $selfVars, $newValue = false, $nonSymmetrical = false) {
        foreach (array_keys($selfVars) as $varName) {
             if ($selfVars[$varName] === $otherObject) {
                $selfVars[$varName] = $newValue;
             } elseif (is_array($selfVars[$varName])) {
                self::refCleanArray($otherObject, $selfVars[$varName]);
             }
        }
        if (!$nonSymmetrical && ($otherObject instanceof Pwg_I_Refcontrol && $otherObject->refHas($thisObject)))
            $otherObject->refRemove($thisObject, true);
    }
    
    static function refNotifyDestroying($thisObject, array & $refReg) {
        foreach (array_keys($refReg) as $k) if (isset($refReg[$k])) {
            if ($refReg[$k] instanceof Pwg_I_Refcontrol) {
                $tmp = $refReg[$k];
                unset($refReg[$k]);
                $tmp->refRemove($thisObject, true);
            }
        }
    }
    
}

?>