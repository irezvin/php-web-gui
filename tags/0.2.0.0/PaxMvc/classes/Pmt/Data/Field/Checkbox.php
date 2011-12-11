<?php

class Pmt_Data_Field_Checkbox extends Pmt_Data_Field {
    
    /**
     * @var Pmt_Checkbox
     */
    public $editor = false;

    protected function doOnGetControlPrototypes(& $prototypes) {
        parent::doOnGetControlPrototypes($prototypes);
        $p = array(
            'editor' => array(
                'class' => 'Pmt_Checkbox',
        		'labelControlPath' => '../label', 
            ),
            'binder' => array(
                'controlChangeEvents' => array('change'),
                'dataPropertyName' => 'checked',
                'readOnlyPropertyName' => 'readOnly',
            ),
        );
        Ae_Util::ms($prototypes, $p);
    }
    
}

?>