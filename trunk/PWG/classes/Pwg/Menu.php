<?php

class Pmt_Menu extends Pmt_Menu_Parent {
        
    protected $isHorizontal = false;
    
    protected $autoSubmenuDisplay = true;

    protected $clickToHide = true;

    protected $hideDelay = 0;

    protected $keepOpen = false;

    protected $maxHeight = 0;

    protected $minScrollHeight = 90;
    
    protected $position = 'dynamic';

    protected $scrollIncrement = 1;

    protected $shadow = true;

    protected $showDelay = 250;

    protected $submenuAlignment = false;

    protected $submenuHideDelay = 250;

    
    protected function setIsHorizontal($isHorizontal) {
        $this->isHorizontal = $isHorizontal;
    }

    function getIsHorizontal() {
        return $this->isHorizontal;
    }

    function setAutoSubmenuDisplay($autoSubmenuDisplay) {
        if ($autoSubmenuDisplay !== ($oldAutoSubmenuDisplay = $this->autoSubmenuDisplay)) {
            $this->autoSubmenuDisplay = $autoSubmenuDisplay;
            $a = func_get_args();
            $this->sendMessage('setAutosubmenudisplay', $a);
        }
    }

    function getAutoSubmenuDisplay() {
        return $this->autoSubmenuDisplay;
    }

    function setClickToHide($clickToHide) {
        if ($clickToHide !== ($oldClickToHide = $this->clickToHide)) {
            $this->clickToHide = $clickToHide;
            $a = func_get_args();
            $this->sendMessage('setClicktohide', $a);
        }
    }

    function getClickToHide() {
        return $this->clickToHide;
    }

    function setHideDelay($hideDelay) {
        if ($hideDelay !== ($oldHideDelay = $this->hideDelay)) {
            $this->hideDelay = $hideDelay;
            $a = func_get_args();
            $this->sendMessage('setHidedelay', $a);
        }
    }

    function getHideDelay() {
        return $this->hideDelay;
    }

    function setKeepOpen($keepOpen) {
        if ($keepOpen !== ($oldKeepOpen = $this->keepOpen)) {
            $this->keepOpen = $keepOpen;
            $a = func_get_args();
            $this->sendMessage('setKeepopen', $a);
        }
    }

    function getKeepOpen() {
        return $this->keepOpen;
    }

    function setMaxHeight($maxHeight) {
        if ($maxHeight !== ($oldMaxHeight = $this->maxHeight)) {
            $this->maxHeight = $maxHeight;
            $a = func_get_args();
            $this->sendMessage('setMaxheight', $a);
        }
    }

    function getMaxHeight() {
        return $this->maxHeight;
    }

    function setMinScrollHeight($minScrollHeight) {
        if ($minScrollHeight !== ($oldMinScrollHeight = $this->minScrollHeight)) {
            $this->minScrollHeight = $minScrollHeight;
            $a = func_get_args();
            $this->sendMessage('setMinscrollheight', $a);
        }
    }

    function getMinScrollHeight() {
        return $this->minScrollHeight;
    }

    function setScrollIncrement($scrollIncrement) {
        if ($scrollIncrement !== ($oldScrollIncrement = $this->scrollIncrement)) {
            $this->scrollIncrement = $scrollIncrement;
            $a = func_get_args();
            $this->sendMessage('setScrollincrement', $a);
        }
    }

    function getScrollIncrement() {
        return $this->scrollIncrement;
    }

    function setShadow($shadow) {
        if ($shadow !== ($oldShadow = $this->shadow)) {
            $this->shadow = $shadow;
            $a = func_get_args();
            $this->sendMessage(__FUNCTION__, $a);
        }
    }

    function getShadow() {
        return $this->shadow;
    }

    function setShowDelay($showDelay) {
        if ($showDelay !== ($oldShowDelay = $this->showDelay)) {
            $this->showDelay = $showDelay;
            $a = func_get_args();
            $this->sendMessage('setShowdelay', $a);
        }
    }

    function getShowDelay() {
        return $this->showDelay;
    }

    function setSubmenuAlignment($submenuAlignment) {
        if ($submenuAlignment !== ($oldSubmenuAlignment = $this->submenuAlignment)) {
            $this->submenuAlignment = $submenuAlignment;
            $a = func_get_args();
            $this->sendMessage('setSubmenualignment', $a);
        }
    }

    function getSubmenuAlignment() {
        if ($this->submenuAlignment === false) {
            if ($this->isHorizontal)
                $res = array("tl", "bl");
            else 
                $res = array("tl", "tr");
        } else {
            $res = $this->submenuAlignment;
        }
        return $res;
    }

    function setSubmenuHideDelay($submenuHideDelay) {
        if ($submenuHideDelay !== ($oldSubmenuHideDelay = $this->submenuHideDelay)) {
            $this->submenuHideDelay = $submenuHideDelay;
            $a = func_get_args();
            $this->sendMessage('setSubmenuhidedelay', $a);
        }
    }

    function getSubmenuHideDelay() {
        return $this->submenuHideDelay;
    }

    protected function setPosition($position) {
        if (!in_array($position, $a = array('static', 'dynamic')))
            throw new Exception("Invalid \$position value '{$position}'; allowed values are '".implode("'|'", $a)."'");
        $this->position = $position;
    }

    function getPosition() {
        return $this->position;
    }    
    
    function doListPassthroughParams() {
        return array_merge(parent::doListPassthroughParams(), array(
            'isHorizontal',
            'autoSubmenuDisplay' => 'autosubmenudisplay',
            'clickToHide' => 'clicktohide',
            'hideDelay' => 'hidedelay',
            'keepOpen' => 'keepopen',
            'maxHeight' => 'maxheight',
            'minScrollHeight' => 'minscrollheight',
            'scrollIncrement' => 'scrollincrement',
            'shadow',
            'showDelay' => 'showdelay',
            'submenuAlignment' => 'submenualignment',
            'submenuHideDelay' => 'submenuhidedelay',
            'position',
        ));
    }

    function hasContainer() {return true;}
    
    function doGetContainerBody() {
        return "<div class='bd'></div>";
        //return "<div class='hd'></div><div class='bd'></div><div class='ft'></div>";
    }
        
}

?>