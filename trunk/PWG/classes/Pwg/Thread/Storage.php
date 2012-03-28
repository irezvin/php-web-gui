<?php

class Pwg_Thread_Storage_File extends Pwg_Autoparams implements Pwg_I_Thread_Storage {
    
    protected $dirName = false;
    
    protected $prepDirName = false;
    
    function setDirName($dirName) {
        $this->dirName = $dirName;
        $this->prepDirName = is_dir($dirName)? $dirName . DIRECTORY_SEPARATOR : $dirName;
    }

    function getDirName() {
        if (!strlen($this->dirName)) {
            $dirName = '';
            if (defined('PAX_TMP_PATH')) $dirName = PAX_TMP_PATH; 
            elseif (class_exists('Ae_Dispatcher')) $dirName = Ae_Dispatcher::getInstance()->getCacheDir();
            if (strlen($dirName)) $this->setDirName($dirName);
        }
        return $this->dirName;
    }   
    
    function listThreads($managerId) {
        if ($this->dirName === false) $this->getDirName();
        $res = array();
        $r = glob(($base = $this->prepDirName.$managerId).'*', GLOB_MARK);
        foreach ($r as $fn) {
            if (substr($fn, -1) !== DIRECTORY_SEPARATOR) {
                $threadId = substr($fn, strlen($base));
            }
        }
        return $res;
    }
    
    protected function calcFilenameOfThread($managerId, $threadId) {
        if ($this->dirName === false) $this->getDirName();
        return $this->prepDirName.$managerId.$threadId;
    }
    
    function loadData($managerId, $threadId) {
        return file_get_contents($this->calcFilenameOfThread($managerId, $threadId));
    }

    function lock($managerId, $threadId) {
        // TODO: implement with flock
        return true;
    }
    
    function unlock($managerId, $threadId) {
        // TODO: implement
        return true;
    }
    
    function saveData($managerId, $threadId, $data) {
        $fn = $this->calcFilenameOfThread($managerId, $threadId);
        if (!is_string($data)) throw new Exception("\$data should be a string");
        return file_put_contents($fn, $data);
    }
    
    function deleteData($managerId, $threadId) {
        $fn = $this->calcFilenameOfThread($managerId, $threadId);
        return unlink($fn);
    }
    
} 

?>