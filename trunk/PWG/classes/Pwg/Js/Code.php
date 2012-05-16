<?php

/**
 * @deprecated
 * Use Ac_Js_Code instead
 */
class Pwg_Js_Code extends Ac_Js_Code {

    
    function toJson(Ac_Js $js, $indent = 0, $indentStep = 4, $newLines = true) {
        return self::toJs($js, $indent, $indentStep, $newLines);
    }
    
}