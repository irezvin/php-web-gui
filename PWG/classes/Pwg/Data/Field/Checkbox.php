<?php

class Pwg_Data_Field_Checkbox extends Pwg_Data_Field {
    
    /**
     * @var Pwg_Checkbox
     */
    public $editor = false;

    protected function doOnGetControlPrototypes(& $prototypes) {
        parent::doOnGetControlPrototypes($prototypes);
        $p = array(
            'editor' => array(
                'class' => 'Pwg_Checkbox',
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