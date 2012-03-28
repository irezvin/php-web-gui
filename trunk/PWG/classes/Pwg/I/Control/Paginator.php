<?php

interface Pwg_I_Control_Paginator {
    
    function observeOffsetChanged(Pwg_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array());
    
    function observeLimitChanged(Pwg_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array());
    
    function unobserveOffsetChanged(Pwg_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array());
    
    function unobserveLimitChanged(Pwg_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array());
    
    function getOffset();

    function getLimit();
    
}

?>