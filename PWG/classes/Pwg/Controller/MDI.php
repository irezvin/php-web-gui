<?php

class Pwg_Controller_MDI extends Pwg_Controller {

	/**
	 * @var array ('opener' => $Pwg_Controller, 'target' => $Pwg_Controller)
	 */
	protected $openers = array();
	
	/**
	 * @var array (mapperClass => array(uiRole1 => uiControllerClass, uiRole2 => uiControllerClass2), mapperClass2 => ...) 
	 */
	protected $uiClasses = array(
//		'Kd_Staff_Mapper' => array('list' => 'Kd_Ui_Edit_StaffList', 'details' => 'Kd_Ui_Edit_StaffDetails'),
//		'Kd_Position_Mapper' => array('list' => 'Kd_Ui_Edit_PositionList'),
//		'Kd_Plan_Mapper' => array('list' => 'Kd_Ui_Edit_PlanList', 'details' => 'Kd_Ui_Edit_PlanDetails'),
	);
	    
    /**
     * @var Pwg_Menu
     */
    protected $menu = false;
    
    protected $delegatePrototypes = array(
    );
 
    protected $windows = array();
    
    protected $controllers = array();

    protected $app = false;
    
    protected $defaultPrototypesApplied = false;
        
    protected function doOnInitialize(array $options) {
        if ($this->id === false) $this->id = get_class($this);
        parent::doOnInitialize($options);
    }
    
    protected function doAfterControlsCreated() {
        parent::doAfterControlsCreated();
    }

    protected function setApp(Pwg_Legacy_App $app) {
        $this->app = $app;
    }

    /**
     * @return Pwg_Legacy_App
     */
    function getApp() {
        return $this->app;
    }
    
    function getControlPrototypes() {
    	if (!$this->defaultPrototypesApplied) {
    		$this->defaultPrototypesApplied = true;
    		$this->controlPrototypes = Ac_Util::m($this->controlPrototypes, $this->getDefaultControlPrototypes());
    	}
    	return parent::getControlPrototypes();
    }
    
    function getDefaultControlPrototypes() {
        if (!$this->app) throw new Exception("\$options['app'] must be provided on instantiation");
    	
        $res = array(
            'menu' => array(
                    'class' => 'Pwg_Menu',
                    'isHorizontal' => true,
                    'position' => 'static',
                    'controlPrototypes' => array(
                        'system' => array('caption' => new Pwg_Lang_String('system'),
                            'controlPrototypes' => array(
                                'reset' => array('caption' => new Pwg_Lang_String('reload'), 'url' => $this->getWebFront()->getResetUrl()),
                                'exit' => array('caption' => new Pwg_Lang_String('exit'), 'disabled' => true),
                                'about' => array('caption' => new Pwg_Lang_String('about', array('suffix' => '...'))),
                            ),
                        ),
                        'service' => array(
                            'caption' => new Pwg_Lang_String('service'),
                            'controlPrototypes' => array(
                                'serviceBackups' => array('caption' => new Pwg_Lang_String('backups', array('suffix' => '...')),),
                            ),
                        ),
                        'window' => array(
                            'caption' => new Pwg_Lang_String('window'),
                            'controlPrototypes' => array(
                                array('caption' => new Pwg_Lang_String('close'), 'disabled' => true),
                                array('caption' => new Pwg_Lang_String('collapse'), 'disabled' => true),
                                array('caption' => new Pwg_Lang_String('expand'), 'disabled' => true),
                                array('caption' => new Pwg_Lang_String('list', array('suffix' => '...')), 'disabled' => true),                              
                            ),
                        ),
                    ),
                ),
        );
        return $res;
    }
    
    /**
     * Creates and activates new panel containing a controller.
     * 
     * @return Pwg_Controller
     * 
     * @param string $controllerClass       Class of the controller
     * @param array $windowOptions          Properties of Pwg_Yui_Panel containing the controller (if we have to create it)
     * @param array $controllerOptions      Properties that will be assigned to created controller instance (if we have to create it)
     * @param bool|array $activateIfExists  FALSE, TRUE or array of controller options to search 
     *      FALSE = always create new window and controller; 
     *      TRUE = find and return controller of same class (or it's subclass) and activate it's window
     *      array = find controlller with specific properties (matching is done using Ac_Autoparams::findItems)  
     */
    function createWindowWithController($controllerClass, array $windowOptions = array(), array $controllerOptions = array(), $activateIfExists = false) {
        
        if ($activateIfExists) {
            $ww = array_values($this->findWindowsWithController($controllerClass, is_array($activateIfExists)? $activateIfExists : array()));
            if (count($ww)) {
                $ww[0]->focus();
                $c = $this->findControllerByWindow($ww[0]);
                return $c;
            }
        }
        
        if (isset($controllerOptions['class'])) $controllerClass = $controllerOptions['class']; 
        
        $idSfx = '_'.$controllerClass.'_'.count($this->windows);
        $winId = 'window'.$idSfx;
        $conId = 'controller'.$idSfx;

        $winOptions = Ac_Util::m($this->getWindowDefaults(), $windowOptions);
        $winOptions['id'] = $winId;
        $window = Pwg_Base::factory($winOptions, 'Pwg_Yui_Panel');
        $this->windows[$idSfx] = $window; 
        $this->addControl($window);
        
        $controllerOptions = Ac_Util::m(
            array(
                //'delayedInitialize' => true,
                'parent' => $this, 
                'displayParent' => $window,
                'webFront' => $this->getWebFront(),
            ),
            $controllerOptions
        );
        
        $controllerOptions['id'] = $conId;
        $controller = new $controllerClass($controllerOptions);
        $this->controllers[$idSfx] = $controller;
        $this->addControl($controller);
        $window->observe('close', $this, 'processWindowClose');
        $window->observe('focus', $this, 'processWindowFocus');
                
        $controller->observe(Pwg_I_MDIWindow::evtClose, $this, 'processControllerClose');
        $controller->observe(Pwg_I_MDIWindow::evtWindowControlMessage, $this, 'handleWindowControlMessage');
        $window->focus();
        if (method_exists($controller, 'updateHeader')) $controller->updateHeader();
        
        $this->doOnCreateWindow($window, $controller);
        
        return $controller;
    }
    
    function getWindowDefaults() {
    	return array(
                'visible' => true,
                //'width' => 800,
                //'height' => 300,
                'x' => 40 + 20 * count($this->windows),
                'y' => 60 + 20 * count($this->windows),
                'resizeable' => true,
                //'underlay' => 'shadow',
                'close' => true,
        );
    }
    
    protected function doOnCreateWindow(Pwg_Panel $window, Pwg_Controller $controller)  {
            
		if ($controller instanceof Pwg_I_RecordList) {
			$controller->observe(Pwg_I_RecordList::evtOpenDetails, $this, 'handlerChildOpenDetails');
			$controller->observe(Pwg_I_RecordList::evtCreateRecord, $this, 'handlerChildCreateRecord');
		}
		
    }
    
    /**
     * Searches Pwg_Yui_Panel instances that contain controllers with matching class and properties
     * @param string $controllerClass
     * @param array  $controllerProperties - specifiy matches for controller properties - as in Ac_Autoparams::findItems
     * @return array of Pwg_Yui_Panel
     */
    function findWindowsWithController($controllerClass, array $controllerProperties = array()) {
        $res = array();
        $controllers = Ac_Autoparams::findItems($this->controllers, $controllerProperties, false, true, $controllerClass);
        foreach (array_keys($controllers) as $idSfx) {
            if (isset($this->windows[$idSfx])) {
                $res[$idSfx] = $this->windows[$idSfx];
            }
        }
        return $res;
    }
    
    /**
     * @param Pwg_Controller $controller
     * @return Pwg_Yui_Panel
     */
    function findWindowByController(Pwg_Controller $controller) {
        foreach ($this->windows as $k => $w) {
            if ($controller->getDisplayParent() === $w) return $w;
        }
        return null;
    }
    
    function findControllerByWindow(Pwg_Yui_Panel $window) {
        foreach ($this->controllers as $k => $c) {
            if ($window === $c->getDisplayParent()) return $c;
        }
        return null;
    }

    /**
     * Template method that is called when window is about to close.
     * It can prevent detroying of window and controller by explicitly returning FALSE.
     * Either $window or $controller can be null, but not both.
     */
    protected function doOnWindowClose(Pwg_Yui_Panel $window = null, Pwg_Controller $controller = null) {
    }
    
    function processWindowClose(Pwg_Yui_Panel $window) {
        $c = $this->findControllerByWindow($window);
        if ($this->doOnWindowClose($window, $c) !== false) {
            if ($c) $c->destroy();
            $window->destroy();
        }
        $window->destroy();
    }
    
    function closeWindow(Pwg_Controller $controller) {
        $window = $this->findWindowByController($controller);
        if ($this->doOnWindowClose($window, $controller) !== false) {
            if ($window) {
                $window->destroy();
            }
            $controller->destroy();
        }
    }
    
    function processControllerClose(Pwg_Controller $controller) {
        $this->closeWindow($controller);
    }
    
    function handleMenu__System__AboutClick() {
        $this->createWindowWithController('Pwg_Controller_Std_About', array(
            'resizeable' => false,
            'closeOnOutsideClick' => true,
        ), array(), true);
    }
    
    function handleWindowControlMessage(Pwg_Controller $src, $eventType, $params) {
        if (isset($params['action']) && strlen($action = $params['action'])) {
            if ($wnd = $this->findWindowByController($src)) {
                switch ($action) {
                    case Pwg_I_MDIWindow::wcmClose:
                        $this->processControllerClose($src, 'close', array());
                        break;
                        
                    case 'setHeader':
                    case Pwg_I_MDIWindow::wcmUpdateHeader: 
                        if (isset($params['value'])) 
                            $wnd->setHeader($params['value']); 
                        break;
                        
                    default: trigger_error("Unknown \$params['action'] value: '{$params['action']}'", E_USER_NOTICE);  
                }
            }
        }
    } 
    
    function processWindowFocus(Pwg_Yui_Panel $window) {
    }
    
    function handleMenu__Service__ServiceBackupsClick(Pwg_Menu_Item $menuItem) {
        $this->createWindowWithController('Pwg_Controller_Std_Backups', array(
            'modal' => true,
            'fixedCenter' => true,
            'width' => 882,
            'height' => 454,
        ),
        array(
        ),
        true);
    }

	function setOpener(Pwg_Controller $target, Pwg_Controller $opener) {
		foreach ($this->openers as $k => $v) {
			if (!isset($v['opener']) || !isset($v['target'])) unset($this->openers[$k]);
			elseif ($v['target'] == $target) {
				$v['opener'] = $opener;
				return;
			}
		}
		$this->openers[] = array('opener' => $opener, 'target' => $target);
	}
	
	/**
	 * @return Pwg_Controller
	 */
	function findOpener(Pwg_Controller $target) {
		foreach ($this->openers as $k => $v) {
			if (!isset($v['opener']) || !isset($v['target'])) unset($this->openers[$v]);
			elseif ($v['target'] == $target) return $v['opener'];
		}
		return null;
	}

	protected function doOnCreateDetailsWindow($detailsClass, & $controllerParams, & $windowParams) {
	}
	
	function handlerChildCreateRecord($controller, $eventType, $params) {
		if ($mc = Ac_Util::getArrayByPath($params, 'mapperClass', null)) {
			$detailsClass = $this->getUiInfo($mc, 'details');
			if ($detailsClass) {
				$controllerParams = (isset($params['controllerParams']) && is_array($params['controllerParams']))? $params['controllerParams'] : array();
				$windowParams = array();
				$controllerParams = Ac_Util::m(array(
					'createOnNoId' => true,
					'closeOnCreateCancel' => true,
				), $controllerParams);
				$this->doOnCreateDetailsWindow($detailsClass, $controllerParams, $windowParams);
				$det = $this->createWindowWithController($detailsClass, $windowParams, $controllerParams);
				$this->setOpener($det, isset($params['opener'])? $params['opener'] : $controller);
			}
		}
	}
	
	function handlerChildOpenDetails($controller, $eventType, $params) {
		if ($mc = Ac_Util::getArrayByPath($params, 'mapperClass', null)) {
			$detailsClass = $this->getUiInfo($mc, 'details');
			$windowParams = array();
			$controllerParams = (isset($params['controllerParams']) && is_array($params['controllerParams']))? $params['controllerParams'] : array();
			$controllerParams = Ac_Util::m(array('primaryKey' => $params['primaryKey']), $controllerParams);
			$this->doOnCreateDetailsWindow($detailsClass, $controllerParams, $windowParams);
			if ($detailsClass) {
			    Pwg_Conversation::log('Profiling to...', PAX_TMP_PATH);
//			    ini_set('xdebug.profiler_output_dir', PAX_TMP_PATH);
//			    ini_set('xdebug.profiler_output_name', 'create-'.$detailsClass.'-log.out');
//			    ini_set('xdebug.profiler_enable', 1);
//			    Pwg_Conversation::log(ini_get('xdebug.profiler_enable'));
				$det = $this->createWindowWithController($detailsClass, $windowParams, $controllerParams);
//				ini_set('xdebug.profiler_enable', 0);
				$this->setOpener($det, isset($params['opener'])? $params['opener'] : $controller);
			}
		}
	}
	
	function getUiInfo($mapperClass, $uiType = false) {
		$res = false;
		if (isset($this->uiClasses[$mapperClass])) $res = $this->uiClasses[$mapperClass];
		if (($uiType !== false) && is_array($res)) {
			 if (isset($res[$uiType])) $res = $res[$uiType];
			 	else $res = false;
		}
		return $res;
	}
	
	function getMapperClass($uiClass) {
		$res = false;
		foreach ($this->uiClasses as $mapperClass => $details) {
			foreach ($details as $key => $uiClass) {
				if (is_object($uiClass) && is_a($value, $uiClass) || $value == $uiClass) {
					$res = $mapperClass;
					break 2;
				}
			}
		}
		return $res;
	}
	
}

?>