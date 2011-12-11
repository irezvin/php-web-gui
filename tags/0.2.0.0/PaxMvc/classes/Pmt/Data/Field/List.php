<?php

class Pmt_Data_Field_List extends Pmt_Data_Field {
    
    /**
     * @var Pmt_List
     */
    public $editor = false;

    /**
     * @var Pmt_Data_Binder_LookupList
     */
    public $binder = false;
    
    protected $dummyCaption = null;
    
    protected function doOnGetControlPrototypes(& $prototypes) {
        parent::doOnGetControlPrototypes($prototypes);
        $p = array(
            'editor' => array(
                'class' => 'Pmt_List', 
            ),
            'binder' => array(
                'class' => 'Pmt_Data_Binder_LookupList',
                'controlChangeEvents' => array('selectionChange'),
                'dataPropertyName' => 'selectedValue',
                'listPropertyName' => 'optionsAsArray',
                'readOnlyPropertyName' => 'readOnly',
            ),
        );
        Ae_Util::ms($prototypes, $p);
    }
    
    function setDummyCaption($dummyCaption) {
        if (!$this->binder) $this->controlPrototypes['binder']['dummyCaption'] = $dummyCaption;
            else $this->binder->setDummyCaption($dummyCaption);
    }

    function getDummyCaption() {
        if ($this->binder) return $this->binder->getDummyCaption();
            else return Ae_Util::getArrayByPath($this->controlPrototypes, array('binder', 'dummyCaption'), false);
    }   
    
}

?>