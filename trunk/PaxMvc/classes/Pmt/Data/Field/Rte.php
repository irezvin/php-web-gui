<?php

class Pmt_Data_Field_Rte extends Pmt_Data_Field {

    protected $labelText = false;
    
    /**
     * @var Pmt_Yui_Rte
     */
    public $editor = false;
    
    protected $withLabel = false;

    protected function doOnGetControlPrototypes(& $prototypes) {
        parent::doOnGetControlPrototypes($prototypes);
        $p = array(
            'editor' => array(
                'class' => 'Pmt_Yui_Rte',
                'toolbarCollapsed' => true,
                'toolbarTitle' => $this->labelText,
            ),
            'binder' => array(
            ),
        );
        if ($this->withLabel) $p['label']['html'] = $this->labelText;
        Ae_Util::ms($prototypes, $p);
    }
    
    function setLabelText($labelText) {
        $this->labelText = $labelText;
        if ($this->editor instanceof Pmt_Yui_Rte)
            $this->editor->setToolbarTitle($labelText);
        if ($this->label instanceof Pmt_Label) $this->label->setHtml($labelText);
    }
    
    function getLabelText() {
        return $this->labelText;
    }
    
}