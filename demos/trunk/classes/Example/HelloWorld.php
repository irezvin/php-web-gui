<?php

class Example_HelloWorld extends Pwg_Controller_Aggregate {

    // {init}

    var $timesClicked = 0;

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
