<?php

class Pwg_Data_Field extends Pwg_Controller_Aggregate {
    
    protected $allowPassthroughEvents = true;
    
    /**
     * @var Pwg_Data_Binder
     */
    public $binder = false;
    
    /**
     * @var Pwg_Label
     */
    public $label = false;

    /**
     * @var Pwg_Label
     */
    public $error = false;

    /**
     * @var Pwg_I_Control
     */
    public $editor = false;
    
    /**
     * @var Pwg_Panel
     */
    public $panel = false;
    
    protected $dataSource = false;
    
    protected $recordPropertyName = true;
    
    protected $withLabel = true;
    
    protected $withError = true;
    
    protected $withBinder = true;
    
    protected $controlPrototypes = array(
    );

    function setId($id) {
    	$oldId = $this->getId();
    	parent::setId($id);
    	if (($oldId !== $this->getId()) && ($this->recordPropertyName === true)) {
    		$this->setRecordPropertyName($this->recordPropertyName, true);
    	}
    }
    
    protected function doOnGetControlPrototypes(& $prototypes) {
        $p = array(
            'editor' => array('class' => 'Pwg_Text', 'displayParentPath' => '../panel', 'containerIsBlock' => false),
            'label' => array('class' => 'Pwg_Label', 'displayParentPath' => '../panel', 'containerIsBlock' => false),
            'error' => array('class' => 'Pwg_Label', 'displayParentPath' => '../panel', 'className' => 'errorMsg'),
            'binder' => array('class' => 'Pwg_Data_Binder', 
                'errorControlPath' => '../error', 'errorPropertyName' => 'html', 
                'dataControlPath' => '../editor', 
                'labelControlPath' => '../label', 'labelPropertyName' => 'html',
                
            ),
            'panel' => array('class' => 'Pwg_Panel', 'template' => "{label} {editor} {error}"),
        );
        if (!$this->withLabel) {
            unset($p['label']);
            $p['binder']['labelControlPath'] = false;
            $p['panel']['template'] = str_replace('{label}', '', $p['panel']['template']);
        }
        if (!$this->withError) {
            unset($p['error']);
            $p['binder']['errorControlPath'] = false;
            $p['panel']['template'] = str_replace('{error}', '', $p['panel']['template']);
        }
        Ae_Util::ms($prototypes, $p);
    }
    
    protected function getControlPrototypes() {
        $res = parent::getControlPrototypes();
        if (!$this->withLabel) {
            unset($res['label']);
        }
        if (!$this->withError) {
            unset($res['error']);
        }
        if (!$this->withBinder) {
            unset($res['binder']);
        }
        return $res;
    }

    function setRecordPropertyName($recordPropertyName, $force = false) {
        if (($recordPropertyName !== ($oldRecordPropertyName = $this->recordPropertyName)) || $force) {
            $this->recordPropertyName = $recordPropertyName;
            $propName = $this->getUsedRecordPropertyName();
            if ($this->binder) $this->binder->setRecordPropertyName($propName);
            elseif ($this->withBinder) {
                $this->controlPrototypes['binder']['recordPropertyName'] = $propName;
            }
        }
    }

    function getRecordPropertyName() {
        if ($this->binder) return $this->binder->getRecordPropertyName();
            else return $this->recordPropertyName;
    }
    
    function getUsedRecordPropertyName() {
    	if ($this->recordPropertyName === true) {
    		$id = $this->getId();
    		$pos = false;
    		if (($pos = strpos($id, '_')) !== false) {
    			$foo = explode('_', $id);
    			if (count($foo) > 1) {
    				unset($foo[0]);
    				$res = Ae_Util::arrayToPath(array_values($foo));
    			}
    		} else {
	    		// skip prefix from id
    			if (preg_match('/^[a-z]+[A-Z]/', $id, $m)) {
	    			$res = substr($id, strlen($m[0]) - 1);
	    			$res{0} = strtolower($res[0]);
    			}
    		}
    	} elseif (strlen($this->recordPropertyName)) {
    		$res = $this->recordPropertyName;
    	} else $res = false;
    	return $res;
    }

    protected function setDataSourcePath($dataSourcePath) {
        //$this->associations['dataSource'] = $dataSourcePath;
        if (substr($dataSourcePath, 0, 1) !== '/') $dataSourcePath = '../'.$dataSourcePath;
        if ($this->withBinder) $this->controlPrototypes['binder']['dataSourcePath'] = $dataSourcePath;
    }

    function setDataSource($dataSource) {
        if ($dataSource !== ($oldDataSource = $this->dataSource)) {
            $this->dataSource = $dataSource;
            if ($this->binder) {
                $this->binder->setDataSource($dataSource);
            }
            elseif ($this->withBinder) {
                Ae_Util::unsetArrayByPath($this->controlPrototypes, array('binder', 'dataSourcePath'));
                $this->controlPrototypes['binder']['dataSource'] = $dataSource;
            }
        }
    }

    /**
     * @return Pwg_Data_Source
     */
    function getDataSource() {
        return $this->binder? $this->binder->getDataSource() : $this->dataSource;
    }
    
    protected function setWithLabel($withLabel) {
        $this->withLabel = $withLabel;
    }

    protected function setWithError($withError) {
        $this->withError = $withError;
    }    
    
    protected function setWithBinder($withBinder) {
        $this->withBinder = (bool) $withBinder;
    }

    function getWithBinder() {
        return $this->withBinder;
    }    
    
}

?>