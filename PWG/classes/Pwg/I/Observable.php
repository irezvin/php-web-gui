<?php

interface Pwg_I_Observable {

//  Pwg_I_Observable 
    
    function observe($eventType, Pwg_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array());
    
    function unobserve($eventType, Pwg_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array());
    
}

?>