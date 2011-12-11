<?php

class Pmt_Data_Field_DateTime extends Pmt_Data_Field_Date {

    protected $labelText = false;
    
    /**
     * @var Pmt_Yui_Calendar_PopupWithTime
     */
    public $editor = false;

    protected function doOnGetControlPrototypes(& $prototypes) {
        parent::doOnGetControlPrototypes($prototypes);
        $p = array(
            'editor' => array(
                'class' => 'Pmt_Yui_Calendar_PopupWithTime',
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