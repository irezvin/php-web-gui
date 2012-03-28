<?php

class Pwg_Tree_Node_Toggle extends Pwg_Tree_Node {
    
    protected $checked = false;
    
    protected $disabled = false;

    protected $groupId = false;

    protected $doubleClickTogglesChildren = true;
    
    protected $clickTogglesChildren = false;

    function doGetAssetLibs() {
        return array_merge(parent::doGetAssetLibs(), array(
            'widgets/yui/tree/toggle.js',
            'widgets/yui/tree/toggle.css',
        )); 
    }
    
    function setChecked($checked) {
        if ($checked !== ($oldChecked = $this->checked)) {
            $this->checked = $checked;
            $this->sendMessage(__FUNCTION__, array($checked), 1);
        }
    }

    function getChecked() {
        return $this->checked;
    }

    function setDisabled($disabled) {
        if ($disabled !== ($oldDisabled = $this->disabled)) {
            $this->disabled = $disabled;
            $this->sendMessage(__FUNCTION__, array($disabled), 1);
        }
    }

    function getDisabled() {
        return $this->disabled;
    }

    function setGroupId($groupId) {
        if ($groupId !== ($oldGroupId = $this->groupId)) {
            $this->groupId = $groupId;
            $this->sendMessage(__FUNCTION__, array($groupId), 1);
        }
    }

    function getGroupId() {
        return $this->groupId;
    }

    function setDoubleClickTogglesChildren($doubleClickTogglesChildren) {
        if ($doubleClickTogglesChildren !== ($oldDoubleClickTogglesChildren = $this->doubleClickTogglesChildren)) {
            $this->doubleClickTogglesChildren = $doubleClickTogglesChildren;
            $this->sendMessage(__FUNCTION__, array($doubleClickTogglesChildren), 1);
        }
    }

    function getDoubleClickTogglesChildren() {
        return $this->doubleClickTogglesChildren;
    }    

    function setClickTogglesChildren($clickTogglesChildren) {
        if ($clickTogglesChildren !== ($oldClickTogglesChildren = $this->clickTogglesChildren)) {
            $this->clickTogglesChildren = $clickTogglesChildren;
            $this->sendMessage(__FUNCTION__, array($clickTogglesChildren), 1);
        }
    }

    function getClickTogglesChildren() {
        return $this->clickTogglesChildren;
    }
        
    function triggerFrontendCheckedChange($value) {
        $value = (bool) $value;
        if ($this->checked !== $value) {
            $this->checked = $value;
            $this->triggerEvent('checkedChange', array('value' => $value));
            $p = $this->parent;
            while ($p instanceof Pwg_Tree_Parent) {
                if ($p->observeChildCheckedChange) {
                    $p->notifyChildCheckedChange($this);
                }
                $p = $p->parent;
            }
        }
    }
    
    function triggerFrontendBranchToggle($value) {
        $value = (bool) $value;
        if ($this->checked !== $value) {
            $this->checked = $value;
            $this->triggerEvent('checkedChange', array('value' => $value));
            $this->triggerEvent('branchToggle', array('value' => $value));
            $p = $this->parent;
            while ($p instanceof Pwg_Tree_Parent) {
                if ($p->observeChildCheckedChange) {
                    $p->notifyChildCheckedChange($this);
                }
                if ($p->observeChildBranchToggle) {
                    $p->notifyChildBranchToggle($this);
                }
                $p = $p->parent;
            }
        }
    }
    
    function doListPassthroughParams() {
        return array_merge(parent::doListPassthroughParams(), array(
            'checked',
            'disabled',
            'groupId',
            'doubleClickTogglesChildren',
            'clickTogglesChildren',
        ));
    }
    
}

?>