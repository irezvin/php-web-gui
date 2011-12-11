<?php

class Pmt_Debug {
    
    public static $logTag = false;
    
    static function logSql($database, $sql, $time, $numRows, $isHandleReturned) {
        if (strpos($sql, '/*SKIPLOG*/') === false) {
            Pm_Conversation::log(get_class($database), "\n    ".str_replace("\n", "\n    ", $sql)."\n", 'time='.$time, (int) $numRows, (int) $isHandleReturned);
        }        
    }
    
    static function registerHandlers() {
        Ae_Dispatcher::loadClass('Ae_Database');
        Ae_Callbacks::getInstance()->addHandler(CALLBACK_AE_DATABASE_AFTER_QUERY, array('Pmt_Debug', 'logSql'), 'Pmt_Debug.logSql');
        //var_dump(Ae_Callbacks::getInstance()->listCallbacks());
    }
    
}