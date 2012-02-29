<?php

class Pd_Application_Instance extends Ae_Application {
    
    protected $defaultAssetsPlaceholder = '{PD}';
 
    function getAppClassFile() {
        return __FILE__;
    }
    
    static function getInstance($id = null) {
        return Ae_Application::getInstance('Pd_Application_Instance', $id);
    }
    
}