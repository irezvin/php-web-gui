<?php

interface Pm_I_Conversation {

//  Pm_I_Conversation   
    
    function registerResponder(Pm_I_Responder $responder);
    
    function registerFilter(Pm_I_Responder $filter);
    
    function sendClientMessage(Pm_Message $message);
    
    function setJsId($jsId);
    
    function getJsId();
    
    /**
     * @return bool
     */
    function started();
    
    function start();
    
    function isPageRender();
    
    function hasToProcessWebRequest();
    
    function processWebRequest();
    
    function getInitJavascript();
    
    function getStartupJavascript();
    
    function notifyBeforeRender();
    
    function notifyPageRender();
    
    function notifyReset();
    
    function setSessionId($sessionId);
    
    function getAssetLibs();

    function setWebFront(Pm_I_Web_Front $webFront);

    function releaseSession();
    
}

?>