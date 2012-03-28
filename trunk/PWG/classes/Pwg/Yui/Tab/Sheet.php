<?php

class Pwg_Yui_Tab_Sheet extends Pwg_Group {
    
    protected $title = false;

    /**
     * @var Yui_Tab_Control
     */
    protected $parent = false;
    
    /**
     * @var Yui_Tab_Control
     */
    protected $displayParent = false;
    
    function toJs() {
        ob_start();
        $this->showContainer();
        $cnt = ob_get_clean();
        $res = array(
            'active' => $this->isActive(),
            'content' => $cnt,
            'label' => $this->getTitle(),
            'id' => $this->id,
            'visible' => $this->getVisible(),
        );
        return $res;
    }
    
    function setTitle($title) {
        $ov = $this->title;
        $this->title = $title;
        if (($title != $ov) && $this->parent) {
            $this->parent->updateTabTitle($this);
        }
    }
    
    function getTitle() {
        $prop = substr(__FUNCTION__, 3); $prop{0} = strtolower($prop{0});
        return $this->$prop;
    }
    
    function isActive() {
        $res = ($this->parent->getCurrentTab() === $this);
        return $res; 
    }
    
    function activate() {
        $this->parent->setCurrentTab($this);
    }
    
    function setVisible($v) {
        $v = (bool) $v;
        $ov = $this->visible;
        $this->visible = $v;
        if (($ov != $v) && $this->parent) {
            $this->parent->updateTabVisibility($this);
        }
    }
    
    function doGetConstructorName() {
        return 'Pwg_Group';
    }
    
//    function hasJsObject() {
//        return false;
//    }
    
}

?>