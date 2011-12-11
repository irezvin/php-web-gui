<?php

class Pmt_Yui_DataSource extends Pmt_Base {
    
    protected $defaultResponse = array();
    
    protected $response = false;
    
    protected $isRequest = false;
    
    protected $transactionId = false;
    
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
    

    function setDefaultResponse($defaultResponse) {
        $this->defaultResponse = $defaultResponse;
    }

    function getDefaultResponse() {
        return $this->defaultResponse;
    }   
    
    protected function doGetAssetLibs() {
        return array_merge(parent::doGetAssetLibs(), array(
            //'{YUI}/yahoo/yahoo-dom-event.js',
            '{YUI}/yahoo/yahoo.js',
            '{YUI}/dom/dom.js',
            '{YUI}/event/event.js',
            '{YUI}/element/element.js',
            '{YUI}/datasource/datasource.js',
            'widgets.js',
            'widgets/yui.js',
            'widgets/yui/dataSource.js',
        ));
    }
    
    
}

?>