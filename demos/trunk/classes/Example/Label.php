<?php

class Example_Label extends Pwg_Controller_Aggregate {

    /**
     * @var Pmt_Label
     */
    var $lblReport = false;
    
    /**
     * @var Pmt_Label
     */
    var $lblCustomStyle = false;
    
    /**
     * @var Pmt_Label
     */
    var $lblHidden = false;
    
    /**
     * @var Pmt_Label
     */
    var $lblHover = false;
    
    function getTitle() {
        return 'Using labels';
    }
    
    function doOnGetControlPrototypes(& $prototypes) {
        Ac_Util::ms($prototypes, array(
            
            'lblSimple' => array(
                'html' => 'A simple label HTML'
            ),
            
            // {clickableDecl}
            'lblClickable' => array(
                'html' => '
                        A label with clikckable links: 
                        <a href="##link1">Link1</a> 
                        and 
                        <a href="##link2">Link2</a>',
                
                // this is necessary to handle all clicks 
                // on an elements with href starting from '##'
                'allowHrefClicks' => true, 
            ),
            // {/clickableDecl}
            
            'lblReport' => array(
                'style' => 'border: 1px solid silver; margin: 1em 0; padding: 1em',
                'visible' => false,
            ),
            
            'lblCustomStyle' => array(
                'html' => 'Click on the button below to toggle this label\' bold status', 
                'style' => 'font-weight: bold'
            ),
            
            'btnToggleBold' => array(
                'label' => 'Toggle BOLD on/off'
            ),
            
            'lblHidden' => array(
                'html' => 'This label can be hidden or shown'
            ),
            
            'btnToggleLabel' => array(
                'label' => 'Show/hide label'
            ),
            
            'lblHover' => array(
                'html' => 'Hover mouse over this label to trigger an event',
            ),
            
        ));
    }
    
    // {clickableHandler}
    function handleLblClickableClick(Pwg_Label $label, $eventType, array $params) {
        if ($params['href'] == '##link1') $message = 'Link 1 clicked';
            elseif ($params['href'] == '##link2') $message = 'Link 2 clicked';
            else $message = 'No A element clicked';
        $this->lblReport->setVisible(true);
        $this->lblReport->appendHtml($message.'<br />');
    }
    // {/clickableHandler}
    
    function handleBtnToggleBoldClick() {
        if (!strlen($this->lblCustomStyle->getStyle())) $this->lblCustomStyle->setStyle('font-weight: bold');
            else $this->lblCustomStyle->setStyle('');
    }
    
    function handleBtnToggleLabelClick() {
        $this->lblHidden->setVisible(!$this->lblHidden->getVisible()) ;
    }
    
    // {hoverHandler}
    function handleLblHoverMouseover() {
        $this->lblHover->setHtml('*** '.$this->lblHover->getHtml().' ***');
    }
    
    function handleLblHoverMouseout() {
        $this->lblHover->setHtml(trim($this->lblHover->getHtml(), ' *'));
    }
    // {/hoverHandler}
    
}