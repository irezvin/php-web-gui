<?php

/**
 * @deprecated
 * Use Ae_Js_Var instead
 */
class Pm_Js_Var extends Ae_Js_Var {
    
    function toJson() {
        return self::toJs();
    }
    
}
