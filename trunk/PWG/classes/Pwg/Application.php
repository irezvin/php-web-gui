<?php

abstract class Pwg_Application extends Ac_Application {
    
    /**
     * @var Ac_Avancore
     */
    protected $avancore = false;

    /**
     * @var Pwg_Pwg
     */
    protected $pwg = false;
    
    protected $defaultControllerId = 'Pwg_Web_Front';

    protected function doOnInitialize() {
        parent::doOnInitialize();
        if (!$this->avancore) $this->avancore = Ac_Avancore::getInstance();
        $this->getPwg();
        $this->controllers['Pwg_Web_Front'] = array(
            'class' => 'Pwg_Web_Front',
        );
        if ($this->getUseLocalSessionSavePath()) {
            ini_set('session.save_path', $this->adapter->getVarTmpPath());
        }
    }
 
    /**
     * @return Pwg_Web
     */
    function getWebFront() {
        return $this->getController('Pwg_Web_Front');
    }
    
    function setAvancore(Ac_Avancore $avancore) {
        if ($this->avancore) throw new Exception("Can setAvancore() only once");
        $this->avancore = $avancore;
    }

    /**
     * @return Ac_Avancore
     */
    function getAvancore() {
        if (!$this->avancore) $this->avancore = Ac_Avancore::getInstance();
        return $this->avancore;
    }

    function setPwg(Pwg_Pwg $pwg) {
        if ($this->pwg !== false) throw new Exception("Can setPwg() only once!");
        $this->pwg = $pwg;
    }

    /**
     * @return Pwg_Pwg
     */
    function getPwg() {
        if (!$this->pwg) $this->pwg = Pwg_Pwg::getInstance();
        return $this->pwg;
    }
    
    function getDefaultAssetPlaceholders() {
        $res = array_merge($this->getPwg()->getAssetPlaceholders(), parent::getDefaultAssetPlaceholders());
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