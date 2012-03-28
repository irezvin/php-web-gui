<?php

interface Pmt_I_Control_DisplayParent {

    function getOrderedDisplayChildren();
    
    function findDisplayChild(Pmt_I_Control $child);
    
    function addDisplayChild(Pmt_I_Control $child);
    
    function removeDisplayChild(Pmt_I_Control $child);
    
    function updateDisplayChildPosition(Pmt_I_Control $child, $displayOrder);
    
    function initializeChildContainer(Pmt_I_Control $child);
        
    function notifyContainerInitialized();
    
}

?>