<?php

class Pmt_Checkbox extends Pmt_Element {
    
    protected $checked = false;
    
    protected $className = 'checkbox';
    
    protected $readOnly = false;
    
    protected $disabled = false;
    
    /**
     * @var Pmt_Label 
     */ 
    protected $labelControl = false;

    function setLabelControl(Pmt_Label $labelControl = null) {
        if ($labelControl !== ($oldLabelControl = $this->labelControl)) {
            if ($oldLabelControl) $oldLabelControl->unobserve('click', $this, 'handleLabelControlClick');
            $this->labelControl = $labelControl;
            if ($this->labelControl) $this->labelControl->observe('click', $this, 'handleLabelControlClick');
        }
    }
    
    function handleLabelControlClick() {
        $this->setChecked(!$this->getChecked(), true);
        //$this->triggerFrontendChange(!$this->getChecked(), true);
    }

    /**
     * @return Pmt_Label
     */
    function getLabelControl() {
        return $this->labelControl;
    }
    
    protected function setLabelControlPath($path) {
        $this->associations['labelControl'] = $path;
    }
        
    function doOnInitialize($options) {
        parent::doOnInitialize($options);
        $this->internalObservers['change'] = 1;
        $this->internalObservers['click'] = 1;
    }
    
    function triggerFrontendChange($checked) {
        $checked = $checked? 1: 0;
        $oldChecked = $this->checked? 1 : 0;
        $this->checked = $checked;
        $evt = substr(__FUNCTION__, 15); $evt{0} = strtolower($evt{0});
        //Pm_Conversation::log("oldChecked is ", $oldChecked, " new checked is ", $this->checked); 
        if ($oldChecked !== $checked) $this->triggerEvent($evt, array('oldChecked' => $oldChecked));
    }
    
    function triggerFrontendFocus() {
        $evt = substr(__FUNCTION__, 15); $evt{0} = strtolower($evt{0}); $args = func_get_args(); $args = array_merge(array($evt), $args); 
        call_user_func_array(array($this, 'triggerEvent'), $args);
    }
    
    function triggerFrontendBlur() {
        $evt = substr(__FUNCTION__, 15); $evt{0} = strtolower($evt{0}); $args = func_get_args(); $args = array_merge(array($evt), $args); 
        call_user_func_array(array($this, 'triggerEvent'), $args);
    }
    
    function triggerFrontendClick() {
        $evt = substr(__FUNCTION__, 15); $evt{0} = strtolower($evt{0}); $args = func_get_args(); $args = array_merge(array($evt), $args); 
        call_user_func_array(array($this, 'triggerEvent'), $args);
    }
    
    function setChecked($value = null, $trigger = false) {
    
        $prop = substr(__FUNCTION__, 3); $prop{0} = strtolower($prop{0}); 
        /*
        if (!is_null($value)) $this->$prop = $value;
        $this->sendMessage(__FUNCTION__, array($this->$prop));
        */
        if (is_null($value)) $value = $this->$prop; $oldValue = $this->{$prop}; if ((bool) $value !== $this->$prop) {
            $oldChecked = $this->$prop;
            $this->$prop = (bool) $value; $this->sendMessage(__FUNCTION__, array($this->$prop));
            if ($trigger) $this->triggerEvent('change', array('oldChecked' => $oldChecked));
        }
    }

    function getChecked() {
        $prop = substr(__FUNCTION__, 3); $prop{0} = strtolower($prop{0});
        return $this->$prop? 1: 0;
    }
    
    function setDisabled($value = null) {
        $prop = substr(__FUNCTION__, 3); $prop{0} = strtolower($prop{0}); 
        if (!is_null($value)) $this->$prop = $value;
        $this->sendMessage(__FUNCTION__, array($this->$prop));
    }
    
    function getDisabled() {
        $prop = substr(__FUNCTION__, 3); $prop{0} = strtolower($prop{0});
        return $this->$prop;
    }
    
    function setReadOnly($value = null) {
    
        $prop = substr(__FUNCTION__, 3); $prop{0} = strtolower($prop{0}); 
        /*
        if (!is_null($value)) $this->$prop = $value;
        $this->sendMessage(__FUNCTION__, array($this->$prop));
        */
        if (is_null($value)) $value = $this->$prop; $oldValue = $this->{$prop}; if ($value !== $this->$prop) {
            $this->$prop = $value; $this->sendMessage(__FUNCTION__, array($this->$prop));
        }
    }
            
    function getReadOnly() {
        $prop = substr(__FUNCTION__, 3); $prop{0} = strtolower($prop{0});
        return $this->$prop;
    }
    
//  Template methods of Pmt_Base

    protected function doListPassthroughParams() {
        return array_merge(parent::doListPassthroughParams(), array('checked', 'readOnly', 'disabled')); 
    }
    
}

?>