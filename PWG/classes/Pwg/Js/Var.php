<?php

/**
 * @deprecated
 * Use Ac_Js_Var instead
 */
class Pwg_Js_Var extends Ac_Js_Var {
    
    function toJson() {
        return self::toJs();
    }
    
}
