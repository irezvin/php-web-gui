<?php

class Pwg_Impl_Observable {
    
    static function observe(& $observersArray, $eventType, Pwg_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array()) {
        if (isset($observersArray[$eventType])) foreach ($observersArray[$eventType] as $k => $o) {
        	if (!isset($o[0])) {
        		unset($observersArray[$eventType][$k]);
        		continue; 
        	}
            if ($o[0] === $observer && $o[1] == $extraParams && $o[2] == $methodName)  {
                return false;  
            }
        } else {
            $observersArray[$eventType] = array();
        }
        $observed = true;
        $observersArray[$eventType][] = array($observer, $extraParams, $methodName);
        return $observed;
    }
    
    static function unobserve(& $observersArray, $eventType, Pwg_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array(), $feEvents = array()) {
        $unobserved = false;
        if (isset($observersArray[$eventType])) foreach ($observersArray[$eventType] as $i => $o) {
            if (($o[0] === $observer) && ($o[1] == $extraParams) && ($o[2] == $methodName)) {
                unset($observersArray[$eventType][$i]);
                if (!count($observersArray[$eventType]) && in_array($eventType, $feEvents)) {
                    $unobserved = true;
                }
            }
        }
        return $unobserved;
    }
    
    static function triggerEvent(Pwg_I_Observable $observable, & $observersArray, $eventType, array $params = array()) {
        if (isset($observersArray[$eventType])) foreach ($observersArray[$eventType] as $i => $o) {
        	if (!isset($o[0])) { unset($o); continue; } 
            $p = array_merge($o[1], $params);
            $o[0]->{$o[2]} ($observable, $eventType, $p);
        }
    }
    
}

?>