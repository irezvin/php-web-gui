<?php

if (!class_exists('Pmt_Application', false)) require(dirname(__FILE__).'/Application.php');

class Pmt_PaxMvc extends Pmt_Application {
    
    protected $defaultAssetsPlaceholder = '{PAX}';
    
    protected function doOnInitialize() {
        parent::doOnInitialize();
        $this->paxMvc = $this;
    }
    
    function getAppClassFile() {
        return __FILE__;
    }
    
    static function getInstance($id = null) {
        return Ae_Application::getInstance('Pmt_PaxMvc', $id);
    }
    
    function setPaxMvc(Pmt_PaxMvc $paxMvc) {
        if ($paxMvc !== $this) throw new Exception("Pmt_PaxMvc::setPaxMvc accepts only it's own instance");
    }

    function getDefaultAssetPlaceholders() {
        $res = array_merge($this->getAvancore()->getAssetPlaceholders(), Ae_Application::getDefaultAssetPlaceholders());
        return $res;
    }

    /**
     * @return Pmt_PaxMvc
     */
    function getPaxMvc() {
        return $this;
    }
    
    
    
}