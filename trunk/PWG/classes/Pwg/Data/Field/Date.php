<?php

class Pmt_Data_Field_Date extends Pmt_Data_Field {

    protected $labelText = false;
    
    /**
     * @var Pmt_Yui_Calendar_Popup
     */
    public $editor = false;

    protected function doOnGetControlPrototypes(& $prototypes) {
        parent::doOnGetControlPrototypes($prototypes);
        $p = array(
            'label' => array(
                'html' => $this->labelText,
            ),
            'editor' => array(
                'class' => 'Pmt_Yui_Calendar_Popup',
                'closeOnSelect' => true,
                'label' => $this->labelText,
            ),
            'binder' => array(
                'controlChangeEvents' => array('selectedValueChange'),
                'dataPropertyName' => 'selectedValue',
                'readOnlyPropertyName' => 'readOnly',
            ),
        );
        Ae_Util::ms($prototypes, $p);
    }
    
    function setLabelText($labelText) {
        $this->labelText = $labelText;
        if ($this->editor instanceof Pmt_Yui_Calendar_Popup)
            $this->editor->setLabel($labelText);
        if ($this->label instanceof Pmt_Label) $this->label->setHtml($labelText);
    }
    
    function getLabelText() {
        return $this->labelText;
    }
    
}

?>