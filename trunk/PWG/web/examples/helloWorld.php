<?php

require_once(dirname(__FILE__).'/bootstrap.php');

$app = Pwg_Pwg::getInstance();
$app->getWebFront()->setSessionSuffix(__FILE__);

class Pwg_Example_HelloWorld extends Pwg_Controller_Aggregate {

    var $timesClicked = 0;

    function doOnGetControlPrototypes(& $prototypes) {
        Ac_Util::ms($prototypes, array(
            'btnHello' => array('label' => 'Click me'),
        ));
    }
    
    function handleBtnHelloClick() {
        $this->timesClicked++;
        $this->getControl('btnHello')->setLabel('Hello, World! You have clicked me '.$this->timesClicked.' time(s)');
    }

}
$app->getWebFront()->setTopController('Pwg_Example_HelloWorld');
$app->processRequest();


