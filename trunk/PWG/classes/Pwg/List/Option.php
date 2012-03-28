<?php

class Pmt_List_Option implements Pm_I_RefControl {

    /**
     * @var Pmt_List
     */
    protected $list = false;
    
    protected $label = false;
    
    protected $value = false;
    
    protected $data = false;
    
    protected $index = false;
    
    function toJs() {
        return array('label' => $this->getLabel(), 'value' => $this->getValue());
    }
    
    function __construct(Pmt_List $list) {
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
        //Pm_Conversation::log(($selected? "Select " : "Deselect ")." option: ".$this->getLabel()." / ".$this->getValue());
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
    
//  +-------------- Pm_I_Refcontrol implementation ---------------+

    protected $refReg = array();

    protected function refGetSelfVars() {
        $res = array();
        foreach (array_keys(get_object_vars($this)) as $v) $res[$v] = & $this->$v;
        return $res;
    }
    
    function refHas($otherObject) { return Pm_Impl_Refcontrol::refHas($otherObject, $this->refReg); }
    
    function refAdd($otherObject) { return Pm_Impl_Refcontrol::refAdd($this, $otherObject, $this->refReg); }
    
    function refRemove($otherObject, $nonSymmetrical = false) { $v = $this->refGetSelfVars(); return Pm_Impl_Refcontrol::refRemove($this, $otherObject, $v, false, $nonSymmetrical); }

    function refNotifyDestroying() { return Pm_Impl_Refcontrol::refNotifyDestroying($this, $this->refReg); }

//  +-------------------------------------------------------------+ 
    
}

?>