<?php

class Pm_Thread_Manager implements Pm_I_Responder, Pm_I_Web_Front {
    
    protected $id = null;
    
    /**
     * @var Pm_I_Thread_Storage
     */
    protected $storage = null;
    
    /**
     * Threads that are currently in memory
     * @var array
     */
    protected $currentThreads = array();
    
    /**
     * @var Pm_I_Web_Front
     */
    protected $webFront = null;
    
    /**
     * @var Pm_I_Conversation
     */
    protected $conversation = null;
    
    protected $threadIds = false;

    function Pm_Thread_Manager(Pm_I_Thread_Storage $storage) {
        $this->setStorage($storage);
    }
    
    function listAllThreads() {
        if ($this->threads === false) $this->threadIds = $this->storage->listThreads($this->getId());
    }
    
    function listCurrentThreads() {
        return array_keys($currentThreads);
    }
    
    function setWebFront(Pm_I_Web_Front $front = null) {
        if ($front === $this) throw new Exception("Cannot use \$this as WebFront");
        $this->webFront = $front;
    }

    /**
     * @return Pm_I_Web_Front
     */
    function getWebFront() {
        return $this->webFront;
    }
    
    function getJsOrCssUrl($jsOrCssLinkWithPlaceholders) {
        if (!$this->webFront) throw new Exception("WebFront must be set before using this method");
        $res = $this->webFront->getJsOrCssUrl($jsOrCssLinkWithPlaceholders);
        return $res;
    }
    
    function getInitiallyLoadedAssets() {
        if (!$this->webFront) throw new Exception("WebFront must be set before using this method");
        $res = $this->webFront->getInitiallyLoadedAssets();
        return $res;
    }
    
    /**
     * @param $id
     * @return Pm_Thread
     */
    function getThread($id) {
        if (isset($this->currentThreads[$id])) $res = $this->currentThreads[$id];
        else {
            $threadData = $this->storage->loadData($this->getId(), $id);
            if (!strlen($threadData)) throw new Exception("No such thread: $id");
            $res = unserialize($threadData);
            if (!$res instanceof Pm_Thread) throw new Exception("Data of thread # $id is corrupted");
            if (($tId = $res->getId()) !== $id) throw new Exception("ID of requested thread (# {$id}) does not match with ID loaded thread (# {$tId})");
            $this->currentThreads[$tId] = $res;
        }
        return $res;
    }
    
    function deleteThread($id) {
        if ($this->currentThreads[$id]) {
            $this->currentThreads[$id]->setManager(null);
        }
    }
    
    function addThread(Pm_Thread $thread) {
        if (in_array($tId = $thread->getId(), $this->listAllThreads())) throw new Exception("Thread with # $tId is already registered");
        $thread->setManager($this);
        $this->currentThreads[$tId] = $thread;
    }
    
    function getResponderId() {
        return $this->getId();
    }
    
    function setConversation(Pm_I_Conversation $conversation) {
        $this->conversation = $conversation;
    }
    
    function startQueue() {
    }
    
    function acceptMessage(Pm_Message $message) {
        
    }
    
    function endQueue() {
        $this->saveCurrentThreads();
    }

    function isResidentResponder() {
        return false;
    }

    protected function setStorage(Pm_I_Thread_Storage $storage) {
        $this->storage = $storage;
    }

    /**
     * @return Pm_I_Thread_Storage
     */
    function getStorage() {
        return $this->storage;
    }   

    function setId($id) {
        if (strlen($this->id)) throw new Exception("Can setId() only once");
        $this->id = $id;
    }

    function getId() {
        if (!strlen($this->id)) $this->id = md5(microtime().rand());
        return $this->id;
    }    

    function saveCurrentThreads() {
        foreach ($this->currentThreads as $thread) $this->storage->saveData($this->getId(), $thread->getId(), serialize($thread));
    }
    
    function __sleep() {
        return array_diff(array_keys(get_object_vars($this)), array('threadIds', 'currentThreads'));
    }
    
    /**
     * @param Pm_Thread $thread
     * @return Pm_Conversation
     */
    function createConversationForThread(Pm_Thread $thread) {
        $conv = new Pm_Conversation();
        $conv->setTempDir(PAX_TMP_PATH);
        $conv->setAutoTrapErrors(true);
        $conv->setJsId($thread->getId());
        return $conv;
    }
    
}

?>