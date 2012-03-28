<?php

class Pwg_Backup_List extends Pwg_Autoparams {
    
    protected $backups = false;
    
    protected $path = '.';
    
    function setPath($path) {
        if ($path !== $this->path) {
            $this->path = $path;
            $this->backups = false;
        }
    }
    
    function refresh() {
        $this->backups = false;
    }
    
    function listBackups() {
        if ($this->backups === false) {
            $this->backups = array();
            if (is_dir($this->path)) {
                $d = opendir($this->path);
                while (($f = readdir($d)) !== false) {
                    $fn = Ae_Util::addTrailingSlash($this->path).$f;
                    if (($f !== '.') && ($f !== '..') && is_dir($fn)) $this->backups[$f] = false;
                    Pwg_Conversation::log($f);
                }
                closedir($d);
            }
            ksort($this->backups);
            $this->backups = array_reverse($this->backups, true);
        }
        return array_keys($this->backups);
    }
    
    /**
     * @return Pwg_Backup
     */
    function createBackup() {
        return new Pwg_Backup(array('path' => $this->path));
    }
    
    /**
     * @return Pwg_Backup
     */
    function getBackup($i) {
        if (in_array($i, $this->listBackups())) {
            if (!is_object($this->backups[$i])) {
                $this->backups[$i] = new Pwg_Backup(array('path' => $this->path, 'prefix' => $i, ));
            }
            $res = $this->backups[$i];
        } else $res = null;
        return $res;
    }
    
}

?>