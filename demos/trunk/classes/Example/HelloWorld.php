<?php

// {classdef}
class Example_HelloWorld extends Pmt_Controller_Aggregate {

    var $timesClicked = 0;

    // {init}
    function doOnGetControlPrototypes(& $prototypes) {
        Ae_Util::ms($prototypes, array(
            'btnHello' => array('label' => 'Click me'),
        ));
    }
    // {/init}
    
    // {handler}
    function handleBtnHelloClick() {
        $this->timesClicked++;
        $this->getControl('btnHello')->setLabel('Hello, World! You have clicked me '.$this->timesClicked.' time(s)');
    }
    // {/handler}

}

// {/classdef}
