<?php

$config = array(
    'webUrl' => 'http://nivzer2/paxMvc/',
    'useLocalSessionSavePath' => true,
    'useComet' => 0,
    
   'legacyDatabasePrototype' => array(
        'class' => 'Ae_Legacy_Database_Native',
        '__construct' => array(array(
            'user' => 'irezvin',
            'password' => 'iiv80of3',
            'db' => 'paxmvc',
            'prefix' => 'pax_',
        )),
    ),
    
);