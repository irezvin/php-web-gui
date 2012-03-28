<?php

class Pwg_Yui_AutoComplete extends Pwg_Text {
    
    protected $defaultResponse = array();
    
    protected $response = false;
    
    protected $isRequest = false;
    
    protected $transactionId = false;
    
    protected $autoCompleteConfig = false;

    protected $autoCompleteProperties = false;

    protected $dataSourceConfig = false;

    protected $dataSource = false;
    
    protected $dataSourceProperties = false;
    
    protected $labelKey = false;
    
    protected $textKey = false;

    protected $containerAttribs = array(
        'class' => 'yui-ac',
    );
    
    function setResponse($nextResponse) {
        $this->response = $nextResponse;
    }
    
    function getResponse() {
        return $this->response;
    }

    function triggerFrontendDataRequest($transactionId, $request) {
        $evt = substr(__FUNCTION__, 15); $evt{0} = strtolower($evt{0}); 
        $this->response = false;
        $this->isRequest = true;
        $this->triggerEvent($evt, array('request' => $request));
        if ($this->isRequest = false);
        if (!is_array($this->response)) $this->response = $this->defaultResponse;
        $this->sendMessage('dataResponse', array($transactionId, $this->response));
    }
    
    function triggerFrontendItemSelected($text, $clientItem) {
        $this->text = $text;
        $evt = substr(__FUNCTION__, 15); $evt{0} = strtolower($evt{0}); 
        $this->triggerEvent($evt, array ('text' => $text, 'clientItem' => $clientItem));
    }
    
    function setDefaultResponse($defaultResponse) {
        $this->defaultResponse = $defaultResponse;
    }

    function getDefaultResponse() {
        return $this->defaultResponse;
    }

    protected function doGetAssetLibs() {
        return array_merge(parent::doGetAssetLibs(), array(
            '{YUI}/autocomplete/assets/skins/sam/autocomplete.css',
            '{YUI}/fonts/fonts.css',
            '{YUI}/yahoo/yahoo.js',
            '{YUI}/dom/dom.js',
            '{YUI}/event/event.js',
            '{YUI}/animation/animation.js',
            '{YUI}/datasource/datasource.js',
            '{YUI}/autocomplete/autocomplete.js',
            'widgets.js',
            'widgets/yui.js',
            'widgets/yui/dataSource.js',
        ));
    }

    protected function setAutoCompleteConfig($autoCompleteConfig) {
        $this->autoCompleteConfig = $autoCompleteConfig;
    }

    function getAutoCompleteConfig() {
        return $this->autoCompleteConfig;
    }

    protected function setAutoCompleteProperties($autoCompleteProperties) {
        $this->autoCompleteProperties = $autoCompleteProperties;
    }

    function getAutoCompleteProperties() {
        return $this->autoCompleteProperties;
    }

    protected function setDataSourceConfig($dataSourceConfig) {
        $this->dataSourceConfig = $dataSourceConfig;
    }

    function getDataSourceConfig() {
        return $this->dataSourceConfig;
    }

    protected function setDataSource($dataSource) {
        $this->dataSource = $dataSource;
    }

    function getDataSource() {
        return $this->dataSource;
    }   
    
    protected function doListPassthroughParams() {
        return array_merge(parent::doListPassthroughParams(), array(
            'autoCompleteConfig', 
            'autoCompleteProperties', 
            'dataSourceConfig', 
            'dataSource', 
            'dataSourceProperties',
            'labelKey',
            'textKey',
        )); 
    }

    protected function setDataSourceProperties($dataSourceProperties) {
        $this->dataSourceProperties = $dataSourceProperties;
    }

    function getDataSourceProperties() {
        return $this->dataSourceProperties;
    }
    
    function getContainerAttribs() {
        $res = parent::getContainerAttribs();
        if (!$this->containerIsBlock && (!isset($res['style']) || is_array($res['style']) && !isset($res['style']['display'])))
            $res['style']['display'] = 'inline-block';
        return $res; 
    }
    
    protected function setLabelKey($labelKey) {
        $this->labelKey = $labelKey;
    }

    function getLabelKey() {
        return $this->labelKey;
    }

    protected function setTextKey($textKey) {
        $this->textKey = $textKey;
    }

    function getTextKey() {
        return $this->textKey;
    }   
        
}

?>