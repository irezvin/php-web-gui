<?php

/**
 * @deprecated
 * Use Ae_Js_Call instead
 */
class Pwg_Js_Call extends Ae_Js_Call {
    
    function toJson(Ae_Js $js, $indent = 0, $indentStep = 4, $newLines = true) {
        return self::toJs($js, $indent, $indentStep, $newLines);
    }
    
}

?>