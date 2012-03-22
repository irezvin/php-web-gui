<?php

abstract class Pmt_Application extends Ae_Application {
    
    /**
     * @var Ae_Avancore
     */
    protected $avancore = false;

    /**
     * @var Pmt_PaxMvc
     */
    protected $paxMvc = false;
    
    protected $defaultControllerId = 'Pmt_Web_Front';

    protected function doOnInitialize() {
        parent::doOnInitialize();
        if (!$this->avancore) $this->avancore = Ae_Avancore::getInstance();
        $this->getPaxMvc();
        $this->controllers['Pmt_Web_Front'] = array(
            'class' => 'Pmt_Web_Front',
        );
        if ($this->getUseLocalSessionSavePath()) {
            ini_set('session.save_path', $this->adapter->getVarTmpPath());
        }
    }
 
    /**
     * @return Pmt_Web
     */
    function getWebFront() {
        return $this->getController('Pmt_Web_Front');
    }
    
    function setAvancore(Ae_Avancore $avancore) {
        if ($this->avancore) throw new Exception("Can setAvancore() only once");
        $this->avancore = $avancore;
    }

    /**
     * @return Ae_Avancore
     */
    function getAvancore() {
        if (!$this->avancore) $this->avancore = Ae_Avancore::getInstance();
        return $this->avancore;
    }

    function setPaxMvc(Pmt_PaxMvc $paxMvc) {
        if ($this->paxMvc !== false) throw new Exception("Can setPaxMvc() only once!");
        $this->paxMvc = $paxMvc;
    }

    /**
     * @return Pmt_PaxMvc
     */
    function getPaxMvc() {
        if (!$this->paxMvc) $this->paxMvc = Pmt_PaxMvc::getInstance();
        return $this->paxMvc;
    }
    
    function getDefaultAssetPlaceholders() {
        $res = array_merge($this->getPaxMvc()->getAssetPlaceholders(), parent::getDefaultAssetPlaceholders());
        return $res;
    }
    
    function getUseComet() {
        $res = $this->adapter->getConfigValue('useComet');
        if (is_null($res)) $res = defined('_DEPLOY_USE_COMET') && _DEPLOY_USE_COMET;
            else $res = (bool) $res;
        return $res;
    }
    
    function getUseLocalSessionSavePath() {
        return (bool) $this->adapter->getConfigValue('useLocalSessionSavePath', false);
    }
    
}