<?php

class Pwg_Data_Field_Label extends Pwg_Data_Field {

    /**
     * @var Pwg_Label
     */
    public $label = false;
    
    protected function doOnGetControlPrototypes(& $prototypes) {
        parent::doOnGetControlPrototypes($prototypes);
        $p = array(
            'editor' => array(
                'class' => 'Pwg_Label',
            ),
            'binder' => array(
                'dataPropertyName' => 'html',
            ),
        );
        Ac_Util::ms($prototypes, $p);
    }
    
}