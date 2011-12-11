<?php

interface Pm_I_Controller extends Pm_I_Observer {

    function setWebFront(Pm_I_Web_Front $webFront);
    
    /**
     * @return Pmt_I_Web_Front
     */
    function getWebFront();
    
    function setThread(Pm_Thread $thread);
    
    /**
     * @return Pm_I_Thread
     */
    function getThread();

}

?>