<?php

/**
 * @deprecated
 * Use Ac_Js_Call instead
 */
class Pwg_Js_Call extends Ac_Js_Call {
    
    function toJson(Ac_Js $js, $indent = 0, $indentStep = 4, $newLines = true) {
        return self::toJs($js, $indent, $indentStep, $newLines);
    }
    
}

?>