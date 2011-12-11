<?php

interface Pmt_I_Control_Parent {
    
//  Pmt_I_Control_Parent
    
    function listControls();

    /**
     * @params string $id
     * @return Pmt_I_Control
     */
    function getControl($id);
    
    /**
     * @param string $responderId
     * @return Pmt_I_Control 
     */
    function getControlByResponderId($responderId);
    
    /**
     * @return string
     */
    function getControlsResponderIdPrefix();
    
    function addControl(Pmt_I_Control $control);

    function initializeChildObject(Pmt_I_Control $control);
    
}

?>