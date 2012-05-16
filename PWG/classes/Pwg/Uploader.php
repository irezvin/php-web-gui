<?php

class Pwg_Uploader extends Pwg_Label {
    
    /**
     * @var Ac_Upload_Controller
     */
    protected $uploadController = false;
    
    protected $uploadId = false;
    
    protected $newUploadId = false;
    
    protected $needRefresh = true;
    
    protected $readOnly = false;
    
    protected $disabled = false;
        
    protected function setup() {
        $this->triggerEvent('setup');
    }
    
    protected function getUploadParamName() {
        $res = 'upload_'.$this->getResponderId();
        return $res;
    }
    
    function getHtml() {
        if ($this->needRefresh) $this->refresh();
        return $this->html;
    }
    
    protected function refresh() {
        $this->needRefresh = false;
        $this->getUploadController();
        if ($this->uploadController) {
            $this->uploadController->showUploadItem = true;
            $this->uploadController->fileChangeFn = 'function(val) { window.opener.v_'.$this->getResponderId().'.fileChangeFn(val); }';
            $this->uploadController->paramName = $this->getUploadParamName();
            $this->uploadController->oldUploadId = $this->uploadId;
            $this->uploadController->newUploadId = $this->newUploadId;
            $this->uploadController->readOnly = $this->readOnly || $this->disabled;
            $this->uploadController->reset();
            $resp = & $this->uploadController->getResponse();
            $this->setHtml($resp->content);
            $this->triggerEvent('refresh', array('uploadId' => $this->uploadId));
        } else {
            $this->setHtml('No Upload Controller');
        }
        
    }
    
    function setUploadController(Ac_Upload_Controller $uploadController = null) {
        if ($uploadController !== ($oldUploadController = $this->uploadController)) {
            $this->uploadController = $uploadController;
            $this->needRefresh = true;
        }
    }

    /**
     * @return Ac_Upload_Controller
     */
    function getUploadController() {
        if ($this->uploadController === false) {
            $this->setup();
        }
        return $this->uploadController;
    }   

    function setUploadId($uploadId, $trigger = false) {
        if ($uploadId !== ($oldUploadId = $this->uploadId)) {
            $this->uploadId = $uploadId;
            $this->newUploadId = false;
            $this->refresh();
            if ($trigger) $this->triggerEvent('uploadIdChange', array('uploadId' => $uploadId));
        }
    }

    function getUploadId() {
        return $this->newUploadId? $this->newUploadId : $this->uploadId;
    }
    
    /**
     * @return Ac_Upload_Manager
     */
    function getUploadManager() {
        return $this->getUploadController()->getUploadManager();
    }
    
    function triggerFrontendFileChange($uploadId) {
        if ($this->newUploadId !== $uploadId) {
            $this->newUploadId = $uploadId;
            $this->needRefresh = true;
            $this->triggerEvent('uploadIdChange', array('uploadId' => $uploadId));
        }
    }
    
    protected function doGetAssetLibs() {
        return array_merge(parent::doGetAssetLibs(), array(
            'uploadFiles.js'
        ));
    }
    

    function setReadOnly($readOnly) {
        if ($readOnly !== ($oldReadOnly = $this->readOnly)) {
            $this->readOnly = $readOnly;
            if ($this->uploadController) $this->refresh();
        }
    }

    function getReadOnly() {
        return $this->readOnly;
    }

    protected function doListPassthroughParams() {
        return array_merge(parent::doListPassthroughParams(), array('readOnly')); 
    }

    function setDisabled($disabled) {
        if ($disabled !== ($oldDisabled = $this->disabled)) {
            $this->disabled = $disabled;
            if ($this->uploadController) $this->refresh();
        }
    }

    function getDisabled() {
        return $this->disabled;
    }
    
}

?>