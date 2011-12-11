<?php

interface Pmt_I_Control_Paginator {
    
    function observeOffsetChanged(Pm_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array());
    
    function observeLimitChanged(Pm_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array());
    
    function unobserveOffsetChanged(Pm_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array());
    
    function unobserveLimitChanged(Pm_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array());
    
    function getOffset();

    function getLimit();
    
}

?>