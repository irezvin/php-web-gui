<?php

/**
 * @deprecated
 * Use Ae_Js_Code instead
 */
class Pwg_Js_Code extends Ae_Js_Code {

    
    function toJson(Ae_Js $js, $indent = 0, $indentStep = 4, $newLines = true) {
        return self::toJs($js, $indent, $indentStep, $newLines);
    }
    
}