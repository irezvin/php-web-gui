<?php

/**
 * @deprecated
 * Use Ae_Js_String instead
 */
class Pm_Js_String extends Ae_Js_String {
    
    function toJson() {
        return self::toJs();
    }
}