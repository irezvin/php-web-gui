<?php

class Pwg_Data_Field_Rte extends Pwg_Data_Field {

    protected $labelText = false;
    
    /**
     * @var Pwg_Yui_Rte
     */
    public $editor = false;
    
    protected $withLabel = false;

    protected function doOnGetControlPrototypes(& $prototypes) {
        parent::doOnGetControlPrototypes($prototypes);
        $p = array(
            'editor' => array(
                'class' => 'Pwg_Yui_Rte',
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
        if ($this->editor instanceof Pwg_Yui_Rte)
            $this->editor->setToolbarTitle($labelText);
        if ($this->label instanceof Pwg_Label) $this->label->setHtml($labelText);
    }
    
    function getLabelText() {
        return $this->labelText;
    }
    
}