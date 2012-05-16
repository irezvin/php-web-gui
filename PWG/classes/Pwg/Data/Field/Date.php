<?php

class Pwg_Data_Field_Date extends Pwg_Data_Field {

    protected $labelText = false;
    
    /**
     * @var Pwg_Yui_Calendar_Popup
     */
    public $editor = false;

    protected function doOnGetControlPrototypes(& $prototypes) {
        parent::doOnGetControlPrototypes($prototypes);
        $p = array(
            'label' => array(
                'html' => $this->labelText,
            ),
            'editor' => array(
                'class' => 'Pwg_Yui_Calendar_Popup',
                'closeOnSelect' => true,
                'label' => $this->labelText,
            ),
            'binder' => array(
                'controlChangeEvents' => array('selectedValueChange'),
                'dataPropertyName' => 'selectedValue',
                'readOnlyPropertyName' => 'readOnly',
            ),
        );
        Ac_Util::ms($prototypes, $p);
    }
    
    function setLabelText($labelText) {
        $this->labelText = $labelText;
        if ($this->editor instanceof Pwg_Yui_Calendar_Popup)
            $this->editor->setLabel($labelText);
        if ($this->label instanceof Pwg_Label) $this->label->setHtml($labelText);
    }
    
    function getLabelText() {
        return $this->labelText;
    }
    
}

?>