<?php


class Pwg_Controller_MDI_Window extends Pwg_Controller_Aggregate implements Pwg_I_MDIWindow {
    
    protected $windowHeader = false;

    protected $defaultWindowWidth = false;
    
    protected $defaultWindowHeight = false;
    
    function __construct(array $options = array()) {
    	if (!strncmp($this->windowHeader, 'lang:', 5)) $this->windowHeader = new Pwg_Lang_String(substr($this->windowHeader, 5));
    	elseif(!strncmp($this->windowHeader, 'lng:', 4)) $this->windowHeader = new Pwg_Lang_String(substr($this->windowHeader, 4));
    	
    	if ($this->windowHeader === false) $this->windowHeader = $this->doGetDefaultWindowHeader();
    	parent::__construct($options);
    }
    
    protected function doGetDefaultWindowHeader() {
    	return false;
    }
    
    function setWindowHeader($windowHeader) {
        if ($windowHeader !== ($oldWindowHeader = $this->windowHeader)) {
            $this->windowHeader = $windowHeader;
            $this->updateHeader();
        }
    }

    function getWindowHeader() {
        return $this->windowHeader;
    }

    function closeWindow(array $extraParams = array()) {
    	$this->sendWindowControlMessage(Pwg_I_MDIWindow::wcmClose, $extraParams);
    }
    
    function updateHeader() {
        if ($this->windowHeader !== false)
            $this->sendWindowControlMessage(Pwg_I_MDIWindow::wcmUpdateHeader, array('value' => $this->windowHeader));
    }

    /**
     * @return Pwg_Yui_Panel
     */
    function getWindow() {
    	if (($dp = $this->getDisplayParent()) instanceof Pwg_Yui_Panel) $res = $dp;
    		else $res = null;
    	return $res;
    }

    function setDisplayParent(Pwg_I_Control_DisplayParent $displayParent = null) {
    	$oldDisplayParent = $this->displayParent;
    	parent::setDisplayParent($displayParent);
    	if (($w = $this->getWindow())) {
    		$this->doOnConfigureWindow($w);
    	}
    }
    
    protected function setDefaultWindowWidth($defaultWindowWidth) {
        $this->defaultWindowWidth = $defaultWindowWidth;
    }

    function getDefaultWindowWidth() {
        return $this->defaultWindowWidth;
    }

    protected function setDefaultWindowHeight($defaultWindowHeight) {
        $this->defaultWindowHeight = $defaultWindowHeight;
    }

    function getDefaultWindowHeight() {
        return $this->defaultWindowHeight;
    }

    protected function doOnConfigureWindow(Pwg_Yui_Panel $window) {
    	if ($this->defaultWindowWidth !== false) $window->setWidth($this->defaultWindowWidth);
    	if ($this->defaultWindowHeight !== false) $window->setHeight($this->defaultWindowHeight);
    }
    
}