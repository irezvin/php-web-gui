<?php

interface Pwg_I_Refcontrol {
    
    function refHas($otherObject);
    
    function refAdd($otherObject);
    
    function refRemove($otherObject, $nonSymmetrical = false);

    function refNotifyDestroying();
    
}

?>