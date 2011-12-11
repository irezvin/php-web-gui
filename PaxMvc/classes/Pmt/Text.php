<?php

class Pmt_Text extends Pmt_Element {
    
    protected $text = '';
    
    protected $className = 'text';
    
    protected $readOnly = false;
    
    protected $disabled = false;
    
    protected $size = false;

    protected $multiline = false;
    
    protected $rows = false;
    
    protected $isPassword = false;
    
    protected $dummyText = '';
    
    protected $keypressFilter = false;
    
    protected function doOnInitialize($options) {
        parent::doOnInitialize($options);
        $this->internalObservers['change'] = 1;
        //$this->internalObservers['keyup'] = 1;
    }
    
    function triggerFrontendSelect() {
        $evt = substr(__FUNCTION__, 15); $evt{0} = strtolower($evt{0}); 
        $this->triggerEvent($evt);
    }
    
    function triggerFrontendKeyup($newText) {
        //Pm_Conversation::log("keyup: ".$newText);
        $evt = substr(__FUNCTION__, 15); $evt{0} = strtolower($evt{0});
        $this->triggerEvent($evt);
    }
    
    function triggerFrontendKeypress($keyParams) {
        $evt = substr(__FUNCTION__, 15); $evt{0} = strtolower($evt{0});
        $this->triggerEvent($evt, $keyParams);
    }
    
    function triggerFrontendKeydown() {
        $evt = substr(__FUNCTION__, 15); $evt{0} = strtolower($evt{0}); 
        $this->triggerEvent($evt);
    }
    
    function triggerFrontendChange($newText) {
        $oldText = $this->text;
        $this->text = $newText;
        $evt = substr(__FUNCTION__, 15); $evt{0} = strtolower($evt{0}); 
        if ($oldText <> $newText) $this->triggerEvent($evt, array('oldText' => $oldText));
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
    
    function setText($value) {
    
        $prop = substr(__FUNCTION__, 3); $prop{0} = strtolower($prop{0}); 
        $oldValue = $this->{$prop}; if ($value !== $this->$prop) {
            $this->$prop = $value; $this->sendMessage(__FUNCTION__, array(is_null($this->$prop)? '' : $this->$prop));
        }
    }
    
    function getText() {
        $prop = substr(__FUNCTION__, 3); $prop{0} = strtolower($prop{0});
        return $this->$prop;
    }
    
    function setMultiline($value) {
        $prop = substr(__FUNCTION__, 3); $prop{0} = strtolower($prop{0}); 
        $oldValue = $this->{$prop}; if ($value !== $this->$prop) {
            $this->$prop = $value; $this->sendMessage(__FUNCTION__, array($this->$prop));
        }
    }
    
    function getMultiline() {
        $prop = substr(__FUNCTION__, 3); $prop{0} = strtolower($prop{0});
        return $this->$prop;
    }
    
    function setRows($value) {
        $prop = substr(__FUNCTION__, 3); $prop{0} = strtolower($prop{0}); 
        $oldValue = $this->{$prop}; if ($value !== $this->$prop) {
            $this->$prop = $value; $this->sendMessage(__FUNCTION__, array($this->$prop));
        }
    }
    
    function getRows() {
        $prop = substr(__FUNCTION__, 3); $prop{0} = strtolower($prop{0});
        return $this->$prop;
    }
    
    function setDisabled($value) {
        $prop = substr(__FUNCTION__, 3); $prop{0} = strtolower($prop{0}); 
        if ($value !== $this->$prop) {
            $this->$prop = $value;
            $this->sendMessage(__FUNCTION__, array($this->$prop));
        }
    }
        
    function getDisabled() {
        $prop = substr(__FUNCTION__, 3); $prop{0} = strtolower($prop{0});
        return $this->$prop;
    }
    
    function setReadOnly($value) {
        $prop = substr(__FUNCTION__, 3); $prop{0} = strtolower($prop{0}); 
        $oldValue = $this->{$prop}; if ($value !== $this->$prop) {
            $this->$prop = $value; $this->sendMessage(__FUNCTION__, array($this->$prop));
        }
    }
    
    function getReadOnly() {
        $prop = substr(__FUNCTION__, 3); $prop{0} = strtolower($prop{0});
        return $this->$prop;
    }
    
    function setSize($value) {
        $prop = substr(__FUNCTION__, 3); $prop{0} = strtolower($prop{0}); 
        $oldValue = $this->{$prop}; if ($value !== $this->$prop) {
            $this->$prop = $value; $this->sendMessage(__FUNCTION__, array($this->$prop));
        }
    }
    
    function getSize() {
        $prop = substr(__FUNCTION__, 3); $prop{0} = strtolower($prop{0});
        return $this->$prop;
    }
    
    protected function setIsPassword($isPassword) {
        $this->isPassword = $isPassword;
    }

    function getIsPassword() {
        return $this->isPassword;
    }    

    function setDummyText($dummyText) {
        $dummyText = (string) $dummyText;
        if ($dummyText !== ($oldDummyText = $this->dummyText)) {
            $this->dummyText = $dummyText;
            $this->sendMessage(__FUNCTION__, $dummyText);
        }
    }

    function getDummyText() {
        return $this->dummyText;
    }    
    
//  Template methods of Pmt_Base

    protected function doListPassthroughParams() {
        return array_merge(parent::doListPassthroughParams(), array(
            'text', 
            'readOnly', 
            'disabled', 
            'size', 
            'multiline', 
            'rows', 
            'isPassword', 
            'dummyText',
            'id' => 'inputName',
            'keypressFilter',
        )); 
    }

    function setKeypressFilter($keypressFilter) {
        if ($keypressFilter !== ($oldKeypressFilter = $this->keypressFilter)) {
            $this->keypressFilter = $keypressFilter;
            $this->sendMessage(__FUNCTION__, $keypressFilter);
        }
    }

    function getKeypressFilter() {
        return $this->keypressFilter;
    }    
    
}

?>