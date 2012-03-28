<?php

class Pwg_Control_Path {
    
    protected $path = false;
    
    function __construct($path) {
        $this->path = $path;
    }
    
    function getPath() {
        return $this->path;
    }
    
    function __toString() {
        return $this->path;
    }
    
}

?>