<?php

class Pwg_Controller_Std_About extends Pwg_Controller_MDI_Window {
	
    protected $aboutText = false;
    
    protected $windowHeader = false;
    
    protected $defaultWindowWidth = 370;
    
    protected $defaultWindowHeight = 175;
    
    function __construct(array $options = array()) {
    	$this->aboutText = new Pwg_Lang_String('aboutText');
    	if ($this->windowHeader === false) $this->windowHeader = $this->doGetDefaultWindowHeader();
    	parent::__construct($options);
    }
    
    protected function doGetDefaultWindowHeader() {
    	return new Pwg_Lang_String('about');
    }
    
    protected function setAboutText($aboutText) {
        $this->aboutText = $aboutText;
    }

    function getAboutText() {
        return $this->aboutText;
    }	
    
    protected function getControlPrototypes() {
        return Ae_Util::m(array(
        	'lblAboutText' => array(
        		'class' => 'Pwg_Label',
        		'containerIsBlock' => false,
        		'html' => $this->aboutText,
        		'displayParentPath' => '../pnlLayout',
        	), 
            'pnlLayout' => array(
                'class' => 'Pwg_Panel',
                'template' => '
                    <div style="text-align: center">
                        {lblAboutText}
                        <br />
                        <hr />
                        {btnClose}
                        <br />
                    </div> 
                '
            ),
            'btnClose' => array('label' => new Pwg_Lang_String('close'), 'containerIsBlock' => false, 'displayParentPath' => '../pnlLayout'),
        ), parent::getControlPrototypes());
    }
    
    function handleBtnCloseClick() {
        $this->triggerEvent('close');
    }
    

    protected function doOnConfigureWindow(Pwg_Yui_Panel $window) {
		parent::doOnConfigureWindow($window);    	
    }
    
    
}
