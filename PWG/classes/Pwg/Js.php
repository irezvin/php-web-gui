<?php

/**
 * @deprecated
 * Use Ac_Js
 */
class Pwg_Js extends Ac_Js {
    
    function toJson($value, $indent = 0, $indentStep = 4, $newLines = true, $withNumericKeys = false) {
        return Ac_Js::lrToJs($value, $indent, $indentStep, $newLines, $withNumericKeys, $this);
    }
    
    static protected function iToJson($value, $indent = 0, $indentStep = 4, $newLines = true, $withNumericKeys = false, Ac_Js $js) {
        return Ac_Js::iToJs($value, $indent, $indentStep, $newLines, $withNumericKeys, js);
    }
    
    /**
     * Less-readable version of iToJson
     */
    static protected function lrToJson($value, $indent = 0, $indentStep = 4, $newLines = true, $withNumericKeys = false, Ac_Js $js) {
        return Ac_Js::lrToJs($value, $indent, $indentStep, $newLines, $withNumericKeys, $js);
    }
    
}