<?php

class Pwg_Debug {
    
    public static $logTag = false;
    
    static function logSql($database, $sql, $time, $numRows, $isHandleReturned) {
        if (strpos($sql, '/*SKIPLOG*/') === false) {
            Pwg_Conversation::log(get_class($database), "\n    ".str_replace("\n", "\n    ", $sql)."\n", 'time='.$time, (int) $numRows, (int) $isHandleReturned);
        }        
    }
    
    static function registerHandlers() {
        if (class_exists('Ac_Legacy_Database')) {
            Ac_Callbacks::getInstance()->addHandler(CALLBACK_AE_DATABASE_AFTER_QUERY, array('Pwg_Debug', 'logSql'), 'Pwg_Debug.logSql');
        }
    }
    
}