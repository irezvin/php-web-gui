<?php

class Pd_PaxDemos extends Pmt_Application {
    
    protected $defaultControllerId = 'Pd_Index';

    protected $defaultAssetsPlaceholder = '{PD}';
 
    function doOnInitialize() {
        parent::doOnInitialize();
        $this->controllers['Pd_Index'] = array(
            'class' => 'Pd_Index',
        );
    }
    
    function getAppClassFile() {
        return __FILE__;
    }
    
    static function getInstance($id = null) {
        return Ae_Application::getInstance('Pd_PaxDemos', $id);
    }
    
}
