<?php

class Pmt_Menu_Toggle extends Pmt_Menu_Item {

    protected $checked = false;

    function setChecked($checked) {
        if ($checked !== ($oldChecked = $this->checked)) {
            $this->checked = $checked;
            $a = func_get_args();
            $this->sendMessage(__FUNCTION__, $a);
        }
    }

    function getChecked() {
        return $this->checked;
    }
        
    function doListPassthroughParams() {
        return array_merge(parent::doListPassthroughParams(), array(
            'checked', 
        ));
    }
    
    function triggerFrontendClick() {
        if (!$this->disabled) {
            $oldChecked = $this->checked;
            $newChecked = $this->checked = !$this->checked;
            parent::triggerFrontendClick();
            if ($this->checked === $newChecked) $this->sendMessage('setChecked', array($this->checked));
        }
    }
    
    protected function doGetConstructorName() {
        return 'Pmt_Menu_Item';
    }
    
}

?>