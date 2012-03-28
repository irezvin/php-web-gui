<?php

interface Pwg_I_Responder {
    
//  Pwg_I_Responder  
    
    function getResponderId();
    
    function setConversation(Pwg_I_Conversation $conversation);
    
    function startQueue();
    
    function acceptMessage(Pwg_Message $message);
    
    function endQueue();

    /**
     * Should return true if this Responder should automatically receive 'startQueue' and 'endQueue' calls on each conversation  
     */
    function isResidentResponder();
    
    function pageRender(Pwg_I_Renderer $renderer);
    
}

?>