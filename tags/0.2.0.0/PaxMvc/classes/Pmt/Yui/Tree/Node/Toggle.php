<?php 

class Pmt_Yui_Tree_Node_Toggle extends Pmt_Yui_Tree_Node_Text {
    
    protected $checked = false;

    protected $disabled = false;

    protected $groupId = false;
        
    function setChecked($checked) {
        if ($checked !== ($oldChecked = $this->checked)) {
            $this->checked = $checked;
            $this->sendProperty('checked', $checked);
        }
    }

    function getChecked() {
        return $this->checked;
    }

    function setDisabled($disabled) {
        if ($disabled !== ($oldDisabled = $this->disabled)) {
            $this->disabled = $disabled;
            $this->sendProperty('disabled', $disabled);
        }
    }

    function getDisabled() {
        return $this->disabled;
    }

    function setGroupId($groupId) {
        if ($groupId !== ($oldGroupId = $this->groupId)) {
            $this->groupId = $groupId;
            $this->sendProperty('groupId', $groupId);
        }
    }

    function getGroupId() {
        return $this->groupId;
    }
    
    protected function getJsConstructorArgs() {
    	$res = parent::getJsConstructorArgs();
    	foreach (array('checked', 'disabled', 'groupId', 'title', 'label', 'labelStyle') as $p)
    		if (strlen($this->$p)) $res[$p] = $this->$p;
    	return $res;
    }
	
    protected function getJsNodeType() {
    	return new Ae_Js_Var('YAHOO.widget.ToggleNode');
    }    
    
}