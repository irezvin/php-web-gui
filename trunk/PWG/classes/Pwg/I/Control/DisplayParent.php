<?php

interface Pwg_I_Control_DisplayParent {

    function getOrderedDisplayChildren();
    
    function findDisplayChild(Pwg_I_Control $child);
    
    function addDisplayChild(Pwg_I_Control $child);
    
    function removeDisplayChild(Pwg_I_Control $child);
    
    function updateDisplayChildPosition(Pwg_I_Control $child, $displayOrder);
    
    function initializeChildContainer(Pwg_I_Control $child);
        
    function notifyContainerInitialized();
    
}

?>