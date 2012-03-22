<?php

class Pmt_Backup extends Pmt_Autoparams {
    
    protected $dir = false;
    
    protected $prefix = false;

    protected $dateTime = false;
    
    protected $path = '.';
    
    protected $comment = false;
    
    protected $output = array();
    
    protected $lastResult = false;
    
    function setPath($path) {
        $this->path = $path;
    }
    
    function setDir($dir) {
        $this->dir = $dir;
    }
    
    function setPrefix($prefix) {
        $this->prefix = $prefix;
    }
    
    function setDateTime($dateTime) {
        $this->dateTime = $dateTime;
    }
    
    function getDateTime() {
        if ($this->dateTime === false) {
            if ($this->prefix !== false && $this->hasDir()) $this->dateTime = filectime($this->getDirName(true));
                else $this->dateTime = time();
        }
        return $this->dateTime;
    }
    
    function getReadableDateTime() {
        return date('Y-m-d H:i:s', $this->getDateTime());
    }
    
    function getPrefix() {
        if ($this->prefix === false) {
            $this->prefix = Ae_Util::date($this->getDateTime(), 'Y-m-d_H-i-s', false);
        }
        return $this->prefix;
    }
    
    function getDirName($withPath = false) {
        $res = $this->getPrefix();
        if ($withPath) $res = Ae_Util::addTrailingSlash($this->path).$res;
        return $res;
    }
    
    function getMySqlFilename($withPath = false) {
        $res = Ae_Util::addTrailingSlash($this->getDirName($withPath)).$this->getPrefix().'_mysql.sql.gz';
        return $res;
    }
    
    function getCommentFilename($withPath = false) {
        $res = Ae_Util::addTrailingSlash($this->getDirName($withPath)).$this->getPrefix().'_info.dat';
        return $res;
    }
    
    function hasDir() {
        $res = is_dir($this->getDirName(true));
        return $res;
    }
    
    function hasMySql() {
        $res = is_file($f = $this->getMySqlFilename(true)) && filesize($f);
        return $res;    
    }
    
    function getHasMySql() {
        return ($this->hasMySql()? new Pmt_Lang_String('yes') : '');    
    }
    
    function getSize() {
        $s = 0;
        if ($this->hasMySql()) $s += filesize($this->getMySqlFilename(true));
        if ($s < 1024*1024) $res = sprintf("%0.2f K", $s/1024); 
        else $res = sprintf("%0.2f M", $s/1024/1024);
        return $res;
    }
    
    function hasComment() {
        $res = is_file($f = $this->getCommentFilename(true)) && filesize($f);
        return $res;    
    }
    
    function setComment($comment) {
        $res = true;
        if ($comment !== ($oldComment = $this->comment)) {
            $this->comment = $comment;
            if ($this->hasDir() || $this->createDir()) {
                $res = file_put_contents($this->getCommentFilename(true), serialize($comment)) !== false;
            }
        }
        return $res;
    }
    
    function getComment() {
        if ($this->comment === false) {
            if ($this->hasComment()) $this->comment = unserialize(file_get_contents($this->getCommentFilename(true)));
            else $this->comment = null;
        }
        return $this->comment;
    }
    
    protected function createDir() {
        $res = mkdir($this->getDirName(true), 0777);
        return $res;
    }
    
    function createMySqlBackup() {
        if (!$this->hasDir()) $this->createDir();
        exec("mysqldump ".$this->getApplication()->getLegacyDatabase()->getMysqlArgs(true)." | gzip > ".escapeshellarg($this->getMySqlFilename(true)), $this->output);
        $res = $this->hasMySql();
        return $res;
    }
    
    function delete() {
        foreach (array(
            $this->getMySqlFilename(true),
            $this->getCommentFilename(true)
        ) as $fn) if (is_file($fn)) unlink($fn);
        if ($this->hasDir()) rmdir($this->getDirName(true));
    }
    
    function restoreMySqlBackup() {
        if ($this->hasMySql()) {
            $db = $this->getApplication()->getLegacyDatabase();
            exec("gzip -dc ".escapeshellarg($this->getMySqlFilename(true))." | mysql ".$db->getMysqlArgs(true), $this->output, $this->lastResult);
            $res = !$this->lastResult;
        } else {
            $res = false;
        }
        return $res;
    }
    
    function getLastResult() {
        return $this->lastResult;
    }
    
    function getOutput() {
        return $this->output;
    }
    
}
