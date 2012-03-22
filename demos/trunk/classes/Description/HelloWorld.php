<?php

class Description_HelloWorld extends Pd_Description {
    
    function getTitle() {
        return 'Hello, World!';
    }
    
    function showBrief() {
?>
        Minimalistic PWG application.
<?php
    }
    
    function showDescription() {
?>
        <p>This simple example shows minimal PWG application. It consists of single button and nothing else.
        Every click on the button increments a counter and changes button caption. To reset button counter and
        re-launch application, click <?php $this->showResetLink('this link'); ?>.</p>
        
        <?php $this->showCoolExampleBody(); ?>
        
        The button is defined by a prototype array: 
        <?php $this->showSource('init'); ?>
        
        <p>Type of the control is automatically determined by a name prefix (<em>btn</em> means the control by default 
        has class <em>Pmt_Button</em>, if not specified otherwise).</p>
        
        <p>The handler function is simple, its identifier is formed by the control name and 
        upper-cased event name (<em>btnHello</em> + <em>click</em> = <em>btnHelloClick</em>):</p>
        
        <?php $this->showSource('handler'); ?>
<?php
    }
    
}