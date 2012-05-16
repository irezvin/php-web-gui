<?php

/**
 * @deprecated
 * Use Ac_Js_String instead
 */
class Pwg_Js_String extends Ac_Js_String {
    
    function toJson() {
        return self::toJs();
    }
}