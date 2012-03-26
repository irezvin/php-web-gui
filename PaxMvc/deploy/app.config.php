<?php

$config = array(
    'webUrl' => 'http://nivzer2/paxMvc/',
    'useLocalSessionSavePath' => true,
    'useComet' => 1,
    'assetPlaceholders' => array('{YUI}' => 'http://nivzer2/yui/build'),
    'outputPrototype' => array('class' => 'Ae_Legacy_Output_Native', 'showOuterHtml' => true),
    'legacyDatabasePrototype' => array(
        'class' => 'Ae_Legacy_Database_Native',
        '__construct' => array('config' => array(
            'user' => 'irezvin',
            'password' => 'iiv80of3',
            'db' => 'paxmvc',
            'prefix' => 'pax_',
        )),
    ),
    
);