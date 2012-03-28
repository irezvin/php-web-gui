<?php

class Pwg_Data_Field_DateTime extends Pwg_Data_Field_Date {

    protected $labelText = false;
    
    /**
     * @var Pwg_Yui_Calendar_PopupWithTime
     */
    public $editor = false;

    protected function doOnGetControlPrototypes(& $prototypes) {
        parent::doOnGetControlPrototypes($prototypes);
        $p = array(
            'editor' => array(
                'class' => 'Pwg_Yui_Calendar_PopupWithTime',
            ),
            'binder' => array(
                'debug' => true,
                'dataPropertyName' => 'valueWithTime',
            ),
        );
        Ae_Util::ms($prototypes, $p);
        $prototypes['binder']['controlChangeEvents'] = array('valueWithTimeChange');
    }
    
}

?>