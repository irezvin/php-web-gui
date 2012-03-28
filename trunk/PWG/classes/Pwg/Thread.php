<?php

class Pwg_Thread {
    
    /**
     * @var string
     */
    protected $id = false;
    
    /**
     * @var Pwg_Conversation
     */
    protected $conversation = false;
    
    /**
     * @var Pwg_Thread_Manager
     */
    protected $manager = null;
    
    protected $initialized = false;
    
    protected $controllers = array();
    
    function setManager(Pwg_Thread_Manager $manager = null) {
        if ($manager && $this->manager) throw new Exception("Can setManager() only once");
        $this->manager = $manager;
        $this->conversation = $this->manager->createConversationForThread($this);
        foreach ($this->controllers as $c) $c->setWebFront($this->manager);
    }
    
    /**
     * @return Pwg_Thread_Manager
     */
    function getManager() {
        return $this->manager;
    }
    
    /**
     * @return Pwg_Conversation
     */
    function getConversation() {
        return $this->conversation;
    }
    
    function registerController(Pwg_I_Controller $controller) {
        $controller->setThread($this);
        $this->controllers[] = $controller;
        if ($this->manager) $controller->setWebFront($this->manager);
    }
    
    function __sleep() {
        return array_diff(array_keys(get_object_vars($this)), array('manager'));
    }
    
    protected function initialize() {
        if (!$this->initialized) {
            foreach ($this->controllers as $c) $c->setConversation($this->conversation);
            $this->initialized = true;
        }
    }
    
    function getHtml() {
        if (!$this->initialized) $this->initialize();
        ob_start();
        foreach ($this->controllers as $c) if ($c->hasContainer()) {
            $c->showContainer();
        }
        $res = ob_get_clean();
        return $res;
    }
    
    function getResponseJson() {
        if (!$this->initialized) $this->initialize();
        $res = $this->conversation->getResponse();
        return $res;
    }
        
}

?>