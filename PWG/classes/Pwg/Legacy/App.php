<?php

/**
 * @deprecated
 * Use Pwg_Application instead
 */
abstract class Pwg_Legacy_App extends Pwg_Autoparams implements Ac_I_Lang_ResourceProvider {
    
    protected $currentUserId = false;
    
    /**
     * @var Pwg_I_User
     */
    protected $currentUser = false;
    
    /**
     * @var Ac_Legacy_Database
     */
    protected $db = false;
    
    /**
     * @var Ac_Sql_Db_Ae
     */
    protected $sqlDb = false;
    
    protected $dispatcherLang = 'russian_utf8';
    
    protected $langStrings = array();
    
    /**
     * @return Ac_Legacy_Database
     */
    function getDb() {
        if ($this->db === false) {
            $disp = Ac_Dispatcher::getInstance();
            $this->db = & $disp->database;
        }
        return $this->db;
    }

    /**
     * @return Ac_Sql_Db_Ae
     */
    function getSqlDb() {
    	if ($this->sqlDb === false) $this->sqlDb = new Ac_Sql_Db_Ae($this->getDb());
    	return $this->sqlDb;
    }
    
    /**
     * @param string $userId
     * @return Pwg_I_User
     */
    abstract function getUserById($userId);
    
    function setCurrentUserId($currentUserId) {
        if ($currentUserId !== ($oldCurrentUserId = $this->currentUserId)) {
            $this->currentUserId = $currentUserId;
            if ($this->currentUser && ($this->currentUser->getId() !== $this->currentUserId)) $this->currentUser = false;
            if (strlen($currentUserId) && !$this->getCurrentUser()) throw new Exception("No such user id: '{$currentUserId}'");
        }
    }

    function getCurrentUserId() {
        if ($this->currentUserId === false) {
            if ($this->currentUser !== false) $this->currentUserId = false;
        }
        return $this->currentUserId;
    }

    function setCurrentUser(Pwg_I_User $currentUser = null) {
        if (!$currentUser) $currentUser = false;
        if ($currentUser !== ($oldCurrentUser = $this->currentUser)) {
            $this->currentUser = $currentUser;
            $this->currentUserId = $currentUser->getId();
        }
    }

    /**
     * @return Pwg_I_User
     */
    function getCurrentUser() {
        if ($this->currentUser === false) {
            if ($this->currentUserId !== false) $this->currentUser = $this->getUserById($this->currentUserId);
        }
        return $this->currentUser;
    }
    
    function getJsAssetVars() {
        return array(
            '{BASEURL}' => false,
            '{YUI}' => _DEPLOY_YUI_PATH,
            '{PAX}' => _DEPLOY_PAX_JS_PATH,
        );
    }

    /**
     * @return Ac_Image_Upload_Controller
     */
    function getImageUploadController() {
        return $this->createImageUploadController();
    }
    
    /**
     * @return Ac_Image_Upload_Controller
     */
    function createImageUploadController() {
        $baseUrl = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];
        $uCtx = new Ac_Legacy_Controller_Context_Http(array(
            'baseUrl' => $baseUrl.'?c=img', 
        ));
        $uCtx->populate('request', 'imageUploader');
        $controllerOptions = array(
            'uploadCharset' => 'utf-8',
            'uploadManagerOptions' => $this->getUploadManagerOptions(),
            'templateExtraVars' => $this->getUploadControllerLangVars(),
        );
        $uc = new Ac_Image_Upload_Controller($uCtx, $controllerOptions, 'imageUploadController');
        return $uc;
    }
    
    /**
     * @return Ac_Upload_Controller
     */
    function getUploadController() {
        return $this->createUploadController();
    }
    
    function getUploadManagerOptions() {
        $res = array(
            'uploadsCacheDir' => _DEPLOY_OWN_PATH.'/../var/upCache',
            'storageOptions' => array(
                'storagePath' => _DEPLOY_OWN_PATH.'/../var/upStorage/',
                'class' => 'Nc_Upload_Storage',
            ),
            'lngDownloadLabel' => $this->getLangString('upload_download'),
            'lngViewLabel' => $this->getLangString('upload_view'),
            'showDownloadAndViewLinks' => false,
        );
        return $res;
    }
    
    protected function getUploadControllerLangVars() {
        $res = array();
        foreach ($this->getLangString() as $k => $v) {
            if (!strncmp($k, 'upload_', 7)) $res[substr($k, 7)] = $v;
        }
        return $res; 
    }
    
    function lng($string) {
        return $this->getLangString($string);
    }
    
    protected function doInitLangStrings() {
    	$this->langStrings = array(
            'upload_langFileToUpload' => 'Файл для загрузки',
            'upload_langFilename' => 'Имя файла',
            'upload_langMimeType' => 'Тип',
            'upload_langFilesize' => 'Размер',
            'upload_langUploadAnotherFile' => 'Загрузить файл',
            'upload_langUseThisFile' => 'Использовать этот файл',
            'upload_langReplaceFile' => 'Заменить файл',
            'upload_langUploadFile' => 'Загрузить',
            'upload_langCancel' => 'Отмена',
            'upload_langUploadFailed' => 'Не удалось загрузить файл',
            'upload_langNoUpload' => '',
            'upload_langUploadNewFile' => 'Загрузить новый файл',
            'upload_langDownloadFile' => 'Скачать файл',
        	'upload_download' => 'Скачать 1',
        	'upload_view' => 'Просмотр 2',
            
            'all' => '(Все)',
            'uncategorized' => '(Не классифицированные)',
            'directlyInCategory' => '(Непосредственно в этой категории)',
        
        	'system' => 'Система',
        	'reload' => 'Перезагрузка',
        	'exit' => 'Выход',
        	'about' => 'О программе',
        	'service' => 'Сервис',
        	'backups' => 'Резервные копии',
        
        	'window' => 'Окно',
        	'close' => 'Закрыть',
        	'expand' => 'Развернуть',
        	'list' => 'Список',
        	'collapse' => 'Свернуть',
    	
        
            'yes' => 'Да',
            'no' => 'Нет',
            'ok' => 'Ок',
        
            'create' => 'Создать',
            'delete' => 'Удалить',
            'next' => 'Вперед', 
            'prev' => 'Назад', 
            'first' => 'Первая',             
            'last' => 'Последняя', 
            'save' => 'Сохранить',
            'cancel' => 'Отмена',
            'refresh' => 'Обновить',
        
            'view' => 'Просмотр',
    	
        
        	'aboutText' => '<br />Здесь должен быть текст "о программе"</br />'
        );
    }
    
    function getLangString($string = false) {
    	if (!$this->langStrings) $this->doInitLangStrings();
    	if ($string === false) return $this->langStrings; 
    	return isset($this->langStrings[$string])? $this->langStrings[$string] : "Translate me: ".$string;
    }
    
    function registerLangStrings($noReplace = false) {
    	Pwg_Lang_Resource::getInstance()->addStrings($this->getLangString());
    }
    
	function getLangHash($langId) {
		return filemtime(__FILE__);
	}
	
	function getLangData($langId) {
		return $this->getLangString();
	}
    
    /**
     * @return Ac_Upload_Controller
     */
    function createUploadController() {
        $baseUrl = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];
        $uCtx = new Ac_Legacy_Controller_Context_Http(array(
            'baseUrl' => $baseUrl.'?c=file', 
        ));
        $uCtx->populate('request', 'uploader');
        $controllerOptions = array(
            'uploadCharset' => 'utf-8',
            'uploadManagerOptions' => $this->getUploadManagerOptions(),
            'templateExtraVars' => $this->getUploadControllerLangVars(),
        );
        $uc = new Ac_Upload_Controller($uCtx, $controllerOptions, 'uploadController');
        return $uc;
    }
    
    function instantiateDispatcher() {
        Ac_Dispatcher::instantiate(get_class($this).'_Dispatcher', false, $this->getDispatcherLang(), 'Ac_Legacy_Adapter_Native', 'Ac_Dispatcher', 
            array('configPath' => 'app.config.php'));       
    }

    function setDispatcherLang($dispatcherLang) {
        $this->dispatcherLang = $dispatcherLang;
    }

    function getDispatcherLang() {
        return $this->dispatcherLang;
    }
    
    function handleAeControllers() {
        $res = false;
        $this->instantiateDispatcher();
        if (isset($_REQUEST['c']) && ($_REQUEST['c'] == 'img')) {
            header('content-type: text/html; charset=utf8');
            $c = $this->createImageUploadController();
            $r = $c->getResponse();
            $o = new Ac_Output_Native(array('showOuterHtml' => true));
            $o->outputResponse($r);
            $res = true;
        } elseif(isset($_REQUEST['c']) && ($_REQUEST['c'] == 'file')) {
            header('content-type: text/html; charset=utf8');
            $c = $this->createUploadController();
            $r = $c->getResponse();
            $o = new Ac_Output_Native(array('showOuterHtml' => true));
            $o->outputResponse($r);
            $res = true;
        } 
        return $res;
    }

    function debugJson($args) {
        if (defined('_DEPLOY_DEBUG_JSON') && _DEPLOY_DEBUG_JSON) {
            echo "\n/*\n";
            foreach (func_get_args() as $a) {
                echo "\n";
                if (is_string($a)) echo $a;
                else var_dump($a);
            }
            echo "\n*/\n";
        }
    }
    
}

?>