<?php

/**
 *  Aggregate controller to reuse groups of controls
 */
class Pwg_Controller_Aggregate extends Pwg_Controller {
    
    protected $prototypesOverride = false;
    
    function listControlsWithPassthroughEvents() {
        return $this->listPublicControls();
    }
    
    function listPublicControls() {
        $myVars = array_keys(Ac_Util::getClassVars(get_class($this)));
        $parentVars = array_keys(Ac_Util::getClassVars('Pwg_Controller'));
        return array_diff($myVars, $parentVars);
    }

    function setPrototypesOverride($prototypesOverride) {
        $this->prototypesOverride = $prototypesOverride;
    }

    function getPrototypesOverride() {
        return $this->prototypesOverride;
    }
    
    protected function getControlPrototypes() {
        $prototypes = parent::getControlPrototypes();
        $this->doOnGetControlPrototypes($prototypes);
        if (is_array($this->prototypesOverride)) {
            Ac_Util::ms($prototypes, $this->prototypesOverride);
        }
        $this->triggerEvent('onGetControlPrototypes', array('prototypes' => & $prototypes));
        return $prototypes;
    }
    
    protected function doOnGetControlPrototypes(& $prototypes) {
    }
    
    protected function doGetConstructorName() {
        return 'Pwg_Controller';
    }
    
}