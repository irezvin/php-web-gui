<?php

class Pmt_CommandReceiver extends Pmt_Base {

    const ORIGIN_API = 'api';
    const ORIGIN_HISTORY = 'history';
    const ORIGIN_INIT = 'init';
    const ORIGIN_ANCHOR = 'anchor';
    const ORIGIN_USER = 'user';
    
    protected $windowId = '';

    protected $windowGroupId = '';

    protected $treatAnchorsAsCommands = false;

    protected $commandPrefix = '';

    protected $anchorPrefix = '';

    protected $checkClicksInsideAnchors = false;

    protected $blankUrl = false;

    /**
     * Sets URL of blank document user for history
     * Defaults to <assets js dir>/widgets/commandReceiver/blank.html
     *  
     * @param string $blankUrl
     */
    protected function setBlankUrl($blankUrl) {
        $this->blankUrl = $blankUrl;
    }

    function getBlankUrl() {
        return $this->blankUrl;
    }
        
    function hasContainer() {
        return false;
    }
    
    function doGetAssetLibs() {
        return array_merge(parent::doGetAssetLibs(), array(
            '{YUI}/yahoo/yahoo.js',
            '{YUI}/dom/dom.js',
            '{YUI}/event/event.js',
            '{YUI}/history/history.js',
            'widgets/commandReceiver.js',
        ));
    }
    
    function doListPassthroughParams() {
        return array_merge(parent::doListPassthroughParams(), array(
            'windowId',
            'windowGroupId',
            'treatAnchorsAsCommands',
            'commandPrefix',
            'anchorPrefix',
            'checkClicksInsideAnchors',
        ));
    }

    function setWindowId($windowId) {
        if ($windowId !== ($oldWindowId = $this->windowId)) {
            $this->windowId = $windowId;
            $this->sendMessage(__FUNCTION__, array($windowId), 1);
        }
    }

    function getWindowId() {
        return $this->windowId;
    }

    protected function setWindowGroupId($windowGroupId) {
        $this->windowGroupId = $windowGroupId;
    }

    function getWindowGroupId() {
        return $this->windowGroupId;
    }

    protected function setTreatAnchorsAsCommands($treatAnchorsAsCommands) {
        $this->treatAnchorsAsCommands = $treatAnchorsAsCommands;
    }

    function getTreatAnchorsAsCommands() {
        return $this->treatAnchorsAsCommands;
    }
    
    function sendCommand($windowId, $command, $url) {
        $this->sendMessage(__FUNCTION__, array($windowId, $command, $url));
    }
    
    /**
     * @param string $origin One of self::ORIGIN_* constants
     */
    function triggerFrontendCommandReceived ($command, $origin) {
        if (!in_array($origin, array(
            self::ORIGIN_API,
            self::ORIGIN_HISTORY,
            self::ORIGIN_INIT,
            self::ORIGIN_ANCHOR,
            self::ORIGIN_USER
        ))) throw new Exception("Wrong \$origin value; must be one of Pmt_CommandReceiver::ORIGIN_* constants");
        $this->triggerEvent('commandReceived', array('command' => $command, 'origin' => $origin));
    }

    protected function setAnchorPrefix($anchorPrefix) {
        $this->anchorPrefix = $anchorPrefix;
    }

    function getAnchorPrefix() {
        return $this->anchorPrefix;
    }

    protected function setCheckClicksInsideAnchors($checkClicksInsideAnchors) {
        $this->checkClicksInsideAnchors = $checkClicksInsideAnchors;
    }

    function getCheckClicksInsideAnchors() {
        return $this->checkClicksInsideAnchors;
    }

    protected function doOnGetInitializer(Pm_Js_Initializer $initializer) {
        parent::doOnGetInitializer($initializer);
        $blankUrl = $this->blankUrl;
        if ($blankUrl === false) {        
            $wf = $this->getController()->getWebFront();
            if ($wf) {
                $blankUrl = $wf->getJsOrCssUrl("widgets/commandReceiver/blank.html");
            } else {
                $blankUrl = "blank.html";
            }
        }
        $initializer->topHtml['Pmt_CommandReceiver'] = '<iframe id="yuiHistoryFrame" src="'.htmlspecialchars($blankUrl).'" style="position:absolute; top:0; left:0; width:1px; height:1px; visibility:hidden;"></iframe><input id="yuiHistoryInput" type="hidden">';
    }
    
}

?>