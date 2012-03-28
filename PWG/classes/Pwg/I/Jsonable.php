<?php

interface Pwg_I_Jsonable extends Ae_I_Jsable {
    
    function toJson(Pwg_Js $js, $indent = 0, $indentStep = 4, $newLines = true);
    
}