<?php

class Pwg_Data_Field_List extends Pwg_Data_Field {
    
    /**
     * @var Pwg_List
     */
    public $editor = false;

    /**
     * @var Pwg_Data_Binder_LookupList
     */
    public $binder = false;
    
    protected $dummyCaption = null;
    
    protected function doOnGetControlPrototypes(& $prototypes) {
        parent::doOnGetControlPrototypes($prototypes);
        $p = array(
            'editor' => array(
                'class' => 'Pwg_List', 
            ),
            'binder' => array(
                'class' => 'Pwg_Data_Binder_LookupList',
                'controlChangeEvents' => array('selectionChange'),
                'dataPropertyName' => 'selectedValue',
                'listPropertyName' => 'optionsAsArray',
                'readOnlyPropertyName' => 'readOnly',
            ),
        );
        Ac_Util::ms($prototypes, $p);
    }
    
    function setDummyCaption($dummyCaption) {
        if (!$this->binder) $this->controlPrototypes['binder']['dummyCaption'] = $dummyCaption;
            else $this->binder->setDummyCaption($dummyCaption);
    }

    function getDummyCaption() {
        if ($this->binder) return $this->binder->getDummyCaption();
            else return Ac_Util::getArrayByPath($this->controlPrototypes, array('binder', 'dummyCaption'), false);
    }   
    
}

?>