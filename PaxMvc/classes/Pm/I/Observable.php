<?php

interface Pm_I_Observable {

//  Pm_I_Observable 
    
    function observe($eventType, Pm_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array());
    
    function unobserve($eventType, Pm_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array());
    
}

?>