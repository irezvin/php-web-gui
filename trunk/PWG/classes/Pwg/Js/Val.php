<?php

/**
 * @deprecated
 * Use Ac_Js_Val instead
 */
class Pwg_Js_Val extends Ac_Js_Val {
    
    function toJson(Ac_Js $js, $indent = 0, $indentStep = 4, $newLines = true) {
        return self::toJs($js, $indent, $indentStep, $newLines);
    }
    
}