<?php

class Pd_Highlighter {

    protected static $instance = false;
    
    /**
     * @return Pd_Highlighter
     */
    function getInstance() {
        if (self::$instance === false) self::$instance = new Pd_Highlighter();
        return self::$instance;
    }
    
    function setInstance(Pd_Highlighter $instance) {
        self::$instance = $instance;
    }
    
    /**
     * @param string $content 
     * @return string
     */
    function highlight($content) {
        $a = is_array($content);
        if ($a) $content = implode("", $content);
        $content .= "\n?"."php>"; // Workaround for hyperlight last-line problem
        if (!class_exists('Hyperlight')) {
            require_once(dirname(__FILE__).'/../../vendor/hyperlight/hyperlight.php');
            ob_start();
            hyperlight($content, 'php');
            $res = ob_get_clean();
        }
        $res = array_slice(explode("\n", $res), 0, -1);
        if (!$a) $res = implode("\n", $res);
        return $res;
    }
    
}