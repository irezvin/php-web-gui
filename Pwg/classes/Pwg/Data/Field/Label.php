<?php

class Pmt_Data_Field_Label extends Pmt_Data_Field {

    /**
     * @var Pmt_Label
     */
    public $label = false;
    
    protected function doOnGetControlPrototypes(& $prototypes) {
        parent::doOnGetControlPrototypes($prototypes);
        $p = array(
            'editor' => array(
                'class' => 'Pmt_Label',
            ),
            'binder' => array(
                'dataPropertyName' => 'html',
            ),
        );
        Ae_Util::ms($prototypes, $p);
    }
    
}