<?php

interface Pwg_I_Control extends Pwg_I_Responder, Pwg_I_Observable {
    
    function __construct(array $options = array());
    
    function setId($id);
    
    function getId();
    
    function setParent(Pwg_I_Control_Parent $parent);
    
    /**
     * @return Pwg_I_Control_Parent
     */
    function getParent();
    
    function setController(Pwg_I_Controller $controller);
    
    /**
     * @return Pwg_I_Controller
     */
    function getController();

    function setContainerId($containerHtmlId);
    
    function getContainerId();
    
    function hasContainer();
    
    function showContainer();
    
    /**
     * @return Pwg_Js_Initializer 
     */
    function getInitializer();
    
    /**
     * @return array
     */
    function getAssetLibs();
    
    /**
     * @return Pwg_I_Control_DisplayParent
     */
    function getDisplayParent();
    
    function setDisplayParent(Pwg_I_Control_DisplayParent $displayParent = null);
    
    function getDisplayOrder();
    
    function setDisplayOrder($displayOrder);
    
    /**
     * Whether the control has already initialized it's javascript (or other) front-end
     */
    function isFrontInitialized();
    
    /**
     * Initialzes control's frontend. Returns true if it was possible to accomplish.
     */
    function initializeFront();
    
    /**
     * Set's delayed initialization value if we don't want the control to immediately appear in the frontend 
     * @param bool $delayedInitialize
     */
    function setDelayedInitialize($delayedInitialize);
    
    function getDelayedInitialize();
    
    /**
     * Should be called by an application if it has initialized control's front-end by itself to let the control know that
     * it's frontend has already been created.
     */
    function notifyFrontInitialized();
    
    function notifyContainerInitialized();
    
    function notifyJsObjectInitialized();

    function destroy();
    
//  Pwg_I_Observable 
/*  
    function observe($eventType, Pwg_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array());
    
    function unobserve($eventType, Pwg_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array());
*/  
//  Pwg_I_Responder  
/*  
    function getResponderId();
    
    function setConversation(Pwg_I_Conversation $conversation);
    
    function startQueue();
    
    function acceptMessage(Pwg_Message $message);
    
    function endQueue();
*/  
}

?>