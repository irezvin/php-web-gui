<?php

interface Pm_I_Responder {
    
//  Pm_I_Responder  
    
    function getResponderId();
    
    function setConversation(Pm_I_Conversation $conversation);
    
    function startQueue();
    
    function acceptMessage(Pm_Message $message);
    
    function endQueue();

    /**
     * Should return true if this Responder should automatically receive 'startQueue' and 'endQueue' calls on each conversation  
     */
    function isResidentResponder();
    
    function pageRender(Pm_I_Renderer $renderer);
    
}

?>