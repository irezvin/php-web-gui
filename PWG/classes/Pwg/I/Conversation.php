<?php

interface Pwg_I_Conversation {

//  Pwg_I_Conversation   
    
    function registerResponder(Pwg_I_Responder $responder);
    
    function registerFilter(Pwg_I_Responder $filter);
    
    function sendClientMessage(Pwg_Message $message);
    
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

    function setWebFront(Pwg_I_Web_Front $webFront);

    function releaseSession();
    
}

?>