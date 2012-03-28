<?php

/**
 * @deprecated
 * Use Ae_Js_Val instead
 */
class Pwg_Js_Val extends Ae_Js_Val {
    
    function toJson(Ae_Js $js, $indent = 0, $indentStep = 4, $newLines = true) {
        return self::toJs($js, $indent, $indentStep, $newLines);
    }
    
}