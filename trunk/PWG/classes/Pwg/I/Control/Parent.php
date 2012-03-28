<?php

interface Pwg_I_Control_Parent {
    
//  Pwg_I_Control_Parent
    
    function listControls();

    /**
     * @params string $id
     * @return Pwg_I_Control
     */
    function getControl($id);
    
    /**
     * @param string $responderId
     * @return Pwg_I_Control 
     */
    function getControlByResponderId($responderId);
    
    /**
     * @return string
     */
    function getControlsResponderIdPrefix();
    
    function addControl(Pwg_I_Control $control);

    function initializeChildObject(Pwg_I_Control $control);
    
}

?>