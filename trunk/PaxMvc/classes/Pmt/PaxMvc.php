<?php

class Pmt_PaxMvc extends Ae_Application {

    /**
     * @var Ae_Avancore
     */
    protected $avancore = false;
    
    protected $defaultAssetsPlaceholder = '{PAX}';
    
    protected $defaultControllerId = 'Pmt_Web_Front';
    
    function getAppClassFile() {
        return __FILE__;
    }
    
    function __construct(array $options = array()) {
        parent::__construct($options);
    }
    
    static function getInstance($id = null) {
        return Ae_Application::getInstance('Pmt_PaxMvc', $id);
    }

    protected function doOnInitialize() {
        parent::doOnInitialize();
        if ($this->getUseLocalSessionSavePath()) {
            ini_set('session.save_path', $this->adapter->getVarTmpPath());
        }
        if (!$this->avancore) $this->avancore = Ae_Avancore::getInstance();
        $this->controllers['Pmt_Web_Front'] = array(
            'class' => 'Pmt_Web_Front',
        );
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
        return $this->avancore;
    }
    
    function getDefaultAssetPlaceholders() {
        $res = array_merge($this->getAvancore()->getAssetPlaceholders(), parent::getDefaultAssetPlaceholders());
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