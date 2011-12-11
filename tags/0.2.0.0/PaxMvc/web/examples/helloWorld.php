<?php

require_once(dirname(__FILE__).'/bootstrap.php');

$app = Pmt_PaxMvc::getInstance();
$app->getWebFront()->setSessionSuffix(__FILE__);

class Pmt_Example_HelloWorld extends Pmt_Controller_Aggregate {

    var $timesClicked = 0;

    function doOnGetControlPrototypes(& $prototypes) {
        Ae_Util::ms($prototypes, array(
            'btnHello' => array('label' => 'Click me'),
        ));
    }
    
    function handleBtnHelloClick() {
        $this->timesClicked++;
        $this->getControl('btnHello')->setLabel('Hello, World! You have clicked me '.$this->timesClicked.' time(s)');
    }

}
$app->getWebFront()->setTopController('Pmt_Example_HelloWorld');
$app->processRequest();


