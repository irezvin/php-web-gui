<?php

class Pwg_List_Option implements Pwg_I_RefControl {

    /**
     * @var Pwg_List
     */
    protected $list = false;
    
    protected $label = false;
    
    protected $value = false;
    
    protected $data = false;
    
    protected $index = false;
    
    function toJs() {
        return array('label' => $this->getLabel(), 'value' => $this->getValue());
    }
    
    function __construct(Pwg_List $list) {
        $this->list = $list;
        $this->refAdd($list);
    }
    
    function setLabel($label) {
        $this->label = $label;
        if ($this->list) $this->list->notifyOptionUpdated($this);
        $this->list->redrawOptions(array($this));
    }
    
    function getLabel() {
        return $this->label;
    }
    
    function setValue($value) {
        $this->value = $value;
        if ($this->list) $this->list->notifyOptionUpdated($this);
    }
    
    function getValue() {
        return $this->value;
    }
    
    function getIndex() {
        if ($this->index === false) {
            $res = false;
            if ($this->list) $res = $this->list->getOptionIndex($this); 
        } else {
            $res = $this->index;
        }
        return $res;
    }
    
    function setIndex($index) {
        $oi = $this->list->getOptionIndex($this);
        if ($oi != $index) {
            $this->list->setMoveOption($this, $index);
        }
    }
    
    function setSelected($selected) {
        //Pwg_Conversation::log(($selected? "Select " : "Deselect ")." option: ".$this->getLabel()." / ".$this->getValue());
        if ($this->list) {
            if ($selected) {
                $this->list->selectOption($this);
            } else {
                $this->list->deselectOption($this);
            }
        }
    }
    
    function getSelected() {
        $res = false;
        if ($this->list) {
            $res = $this->list->isOptionSelected($this);
        }
        return $res;
    }
    
    function redraw() {
        return $this->list->redrawOptions(array($this));
    }
    
    function getData() {
        return $this->data;
    }
    
    function setData($data) {
        $this->data = $data;
    }
    
//  +-------------- Pwg_I_Refcontrol implementation ---------------+

    protected $refReg = array();

    protected function refGetSelfVars() {
        $res = array();
        foreach (array_keys(get_object_vars($this)) as $v) $res[$v] = & $this->$v;
        return $res;
    }
    
    function refHas($otherObject) { return Pwg_Impl_Refcontrol::refHas($otherObject, $this->refReg); }
    
    function refAdd($otherObject) { return Pwg_Impl_Refcontrol::refAdd($this, $otherObject, $this->refReg); }
    
    function refRemove($otherObject, $nonSymmetrical = false) { $v = $this->refGetSelfVars(); return Pwg_Impl_Refcontrol::refRemove($this, $otherObject, $v, false, $nonSymmetrical); }

    function refNotifyDestroying() { return Pwg_Impl_Refcontrol::refNotifyDestroying($this, $this->refReg); }

//  +-------------------------------------------------------------+ 
    
}

?>