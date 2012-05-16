<?php

if (!class_exists('Pwg_Application', false)) require(dirname(__FILE__).'/Application.php');

class Pwg_Pwg extends Pwg_Application {
    
    protected $defaultAssetsPlaceholder = '{PAX}';
    
    protected function doOnInitialize() {
        parent::doOnInitialize();
        $this->paxMvc = $this;
    }
    
    function getAppClassFile() {
        return __FILE__;
    }
    
    static function getInstance($id = null) {
        return Ac_Application::getInstance('Pwg_Pwg', $id);
    }
    
    function setPwg(Pwg_Pwg $pwg) {
        if ($paxMvc !== $this) throw new Exception("Pwg_Pwg::setPaxMvc accepts only it's own instance");
    }

    function getDefaultAssetPlaceholders() {
        $res = array_merge($this->getAvancore()->getAssetPlaceholders(), Ac_Application::getDefaultAssetPlaceholders());
        return $res;
    }

    /**
     * @return Pwg_Pwg
     */
    function getPwg() {
        return $this;
    }
    
    
    
}