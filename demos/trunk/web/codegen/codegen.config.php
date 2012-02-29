<?php

require_once(dirname(__FILE__).'/../deploy.settings.php');

$config = array(
    'generator' => array(
        'php5' => true,
        'user' => _DEPLOY_DB_USER,
        'password' => _DEPLOY_DB_PASSWORD,
        'host' => _DEPLOY_DB_HOST,
        'inspectorClass' => 'Ae_Sql_Dbi_Inspector_MySql5',
        'clearOutputDir' => false,
        'overwriteLog' => true,
        'domainDefaults' => array(
            'defaultTitlePropName' => 'title',
            'defaultPublishedPropName' => 'published',
            'defaultOrderingPropName' => 'ordering',
        ),
        'otherDbOptions' => array(
            'charset' => 'utf8',
        ),
        'generatePmtFinders' => true,
    ),
    'domains.{APP_ID}' => array(
        'strategyClass' => 'Cg_Strategy',
        'appName' => '{APP_CLASS_PREFIX}',
        'dbName' => _DEPLOY_DB_NAME,
        'caption' => '{APP_CAPTION}',
        'josComId' => '{APP_JOS_COM_ID}',
        'tablePrefix' => _DEPLOY_DB_PREFIX,
        'replaceTablePrefixWith' => '#__',
        'subsystemPrefixes' => array(),
        'dontLinkSubsystems' => array(
            //array('content', 'image'),
        ),
        'autoTablesAll' => true,
        'autoTablesIgnore' => array(
        ),
/*        
        'autoTables' => array(
        ),
*/
        'defaultTitleColumn' => 'title',
        
        'dictionary' => array(
            'data' => array(
            ),
        ),

        'schemaExtras' => array(
            'tables' => array(
            ),
        ),

        'modelDefaults' => array(
            'generateMethodPlaceholders' => true,
            'noUi' => true,
            'tracksChanges' => true,
            'hasUniformPropertiesInfo' => true,
        ),
        
    ),
);

?>
