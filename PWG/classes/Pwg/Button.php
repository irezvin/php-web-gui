<?php

class Pwg_Button extends Pwg_Element {

    const btImage = 'image';
    const btButton = 'button';
    const btSubmit = 'submit';
    
    protected $label = 'button';
    
    protected $className = 'button';
    
    protected $disabled = false;
    
    protected $confirmationMessage = false;
    
    protected $buttonType = self::btButton;
    
    function triggerFrontendClick() {
        if ($this->disabled) return; else {
            return parent::triggerFrontendClick();
        }
    }
    
    function triggerFrontendFocus() {
        $evt = substr(__FUNCTION__, 15); $evt{0} = strtolower($evt{0}); 
        $this->triggerEvent($evt);
    }
    
    function triggerFrontendBlur() {
        $evt = substr(__FUNCTION__, 15); $evt{0} = strtolower($evt{0}); 
        $this->triggerEvent($evt);
    }
    
    function setLabel($value = null) {
    
        $prop = substr(__FUNCTION__, 3); $prop{0} = strtolower($prop{0}); 

        if (is_null($value)) $value = $this->$prop; $oldValue = $this->{$prop}; if ($value !== $this->$prop) {
            $this->$prop = $value; $this->sendMessage(__FUNCTION__, array($this->$prop));
        }
    }
    
    function getLabel() {
        $prop = substr(__FUNCTION__, 3); $prop{0} = strtolower($prop{0});
        return $this->$prop;
    }
    
    function setDisabled($value = null) {
        $prop = substr(__FUNCTION__, 3); $prop{0} = strtolower($prop{0}); 
        /*
        if (!is_null($value)) $this->$prop = $value;
        $this->sendMessage(__FUNCTION__, array($this->$prop));
        */
        if (is_null($value)) $value = $this->$prop;
        $oldValue = $this->{$prop};
        if ($value !== $this->$prop) {
            $this->$prop = $value;
            $this->sendMessage(__FUNCTION__, array($this->$prop));
        }
    }
    
    function getDisabled() {
        $prop = substr(__FUNCTION__, 3); $prop{0} = strtolower($prop{0});
        return $this->$prop;
    }

    static function setClassMap(array $cm) {
        Pwg_Base::$classMap = $cm;
    }
    
    static function getClassMap() {
        return Pwg_Base::classMap;
    }
    
//  Template methods of Pwg_Base

    protected function doListPassthroughParams() {
        return array_merge(parent::doListPassthroughParams(), array(
            'label', 
            'disabled', 
            'confirmationMessage',
            'buttonType'
        )); 
    }

    function setConfirmationMessage($confirmationMessage) {
        if ($confirmationMessage !== ($oldConfirmationMessage = $this->confirmationMessage)) {
            $this->confirmationMessage = $confirmationMessage;
            $this->sendMessage(__FUNCTION__, array($confirmationMessage));
        }
    }

    function getConfirmationMessage() {
        return $this->confirmationMessage;
    }


    protected function setButtonType($buttonType) {
        if (!in_array($buttonType, $a = array(self::btButton, self::btImage, self::btSubmit)))
            throw new Exception("Invalid \$buttonType '{$buttonType}'; value should be one of"
                ." '".implode("', '", $a)."'");
        $this->buttonType = $buttonType;
    }

    function getButtonType() {
        return $this->buttonType;
    }    
    
    
}

?>