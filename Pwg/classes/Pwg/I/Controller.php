<?php

interface Pwg_I_Controller extends Pwg_I_Observer, Pwg_I_Control_Parent {

  function setWebFront(Pwg_I_Web_Front $webFront);
  
  /**
   * @return Pwg_Controller
   */
  function getWebFront();
  
  function setThread(Pwg_Thread $thread);
  
  /**
   * @return Pwg_I_Thread
   */
  function getThread();
  
   
  function setConversation(Pwg_I_Conversation $conversation);
    
}

?>
