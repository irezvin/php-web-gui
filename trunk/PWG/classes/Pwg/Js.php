<?php

/**
 * @deprecated
 * Use Ae_Js
 */
class Pwg_Js extends Ae_Js {
    
    function toJson($value, $indent = 0, $indentStep = 4, $newLines = true, $withNumericKeys = false) {
        return Ae_Js::lrToJs($value, $indent, $indentStep, $newLines, $withNumericKeys, $this);
    }
    
    static protected function iToJson($value, $indent = 0, $indentStep = 4, $newLines = true, $withNumericKeys = false, Ae_Js $js) {
        return Ae_Js::iToJs($value, $indent, $indentStep, $newLines, $withNumericKeys, js);
    }
    
    /**
     * Less-readable version of iToJson
     */
    static protected function lrToJson($value, $indent = 0, $indentStep = 4, $newLines = true, $withNumericKeys = false, Ae_Js $js) {
        return Ae_Js::lrToJs($value, $indent, $indentStep, $newLines, $withNumericKeys, $js);
    }
    
}