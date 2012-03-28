<?php

class Pwg_Menu_Item extends Pwg_Menu_Parent {

    protected $submenuIsHorizontal = false;
    
    protected $caption = false;

    protected $imageUrl = false;
    
    protected $helpText = false;

    protected $url = null;
    
    function setCaption($caption) {
        if ($caption !== ($oldCaption = $this->caption)) {
            $this->caption = $caption;
            $a = func_get_args();
            $this->sendMessage('setText', $a);
        }
    }

    function getCaption() {
        return $this->caption;
    }

    function setImageUrl($imageUrl) {
        if ($imageUrl !== ($oldImageUrl = $this->imageUrl)) {
            $this->imageUrl = $imageUrl;
            $a = func_get_args();
            $this->sendMessage(__FUNCTION__, $a);
        }
    }

    function getImageUrl() {
        return $this->imageUrl;
    }
    
    function setUrl($url) {
        if ($url !== ($oldUrl = $this->url)) {
            $this->url = $url;
            $a = func_get_args();
            $this->sendMessage(__FUNCTION__, $a);
        }
    }

    function getUrl() {
        return $this->url;
    }    

    function setHelpText($helpText) {
        if ($helpText !== ($oldHelpText = $this->helpText)) {
            $this->helpText = $helpText;
            $a = func_get_args();
            $this->sendMessage('setHelptext', $a);
        }
    }

    function getHelpText() {
        return $this->helpText;
    }   

    protected function setSubmenuIsHorizontal($submenuIsHorizontal) {
        trigger_error("This property is deprecated", E_USER_WARNING);
        $this->submenuIsHorizontal = $submenuIsHorizontal;
    }

    function getSubmenuIsHorizontal() {
        return $this->submenuIsHorizontal;
    }    
        
    function doListPassthroughParams() {
        return array_merge(parent::doListPassthroughParams(), array(
            'caption' => 'text', 
            'imageUrl', 
            'helpText' => 'helptext', 
            'submenuIsHorizontal',
            'parentId',
            'url'
        ));
    }
    
    function hasContainer() {return false;}
    
    protected function jsGetParentId() {
        if ($this->displayParent) $res = 'v_'.$this->displayParent->getResponderId();
            else $res = null;
        return $res;
    }
    
    function triggerFrontendClick() {
        if ($this->disabled) return;
        $this->triggerEvent('click');
        $p = $this->parent;
        while ($p instanceof Pwg_Menu_Parent) {
            if ($p->observeChildClicks) {
                $p->notifyChildClick($this);
            }
            $p = $p->parent;
        }
    }
    
}

?>