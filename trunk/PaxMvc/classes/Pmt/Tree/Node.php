<?php

class Pmt_Tree_Node extends Pmt_Tree_Parent {
    
    protected $content = false;
    
    protected $href = false;
    
    protected $expanded = false;
    
    protected $selected = false;
    
    protected $type = 'text';
    
    protected $data = false;
    
    protected $triggerEventOnSoftExpand = false;
    
    function setContent($content) {
        if ($content !== ($oldContent = $this->content)) {
            $this->content = $content;
            $this->sendMessage(__FUNCTION__, array($this->content));
        }
    }

    function getContent() {
        return $this->content;
    }

    function setHref($href) {
        if ($href !== ($oldHref = $this->href)) {
            $this->href = $href;
            $this->sendMessage(__FUNCTION__, array($this->href));
        }
    }

    function getHref() {
        return $this->href;
    }

    function setExpanded($expanded, $noTrigger = false) {
        if ($expanded !== ($oldExpanded = $this->expanded)) {
            $this->expanded = $expanded;
            $this->sendMessage(__FUNCTION__, array($this->expanded));
            if (!$noTrigger) {
                $this->triggerEvent('expand', array('byUser' => false));
                $p = $this->parent;
                while ($p instanceof Pmt_Tree_Parent) {
                    if ($p->observeChildExpand) {
                        $p->notifyChildExpand($this, false);
                    }
                    $p = $p->parent;
                }
            }
        }
    }

    function getExpanded() {
        return $this->expanded;
    }

    function setSelected($selected) {
        if ($selected !== ($oldSelected = $this->selected)) {
            $this->selected = $selected;
            $this->sendMessage(__FUNCTION__, array($this->selected));
        }
    }

    function getSelected() {
        return $this->selected;
    }

    function hasContainer() {
        return false;
    }
    
    protected function setType($type) {
        $this->type = $type;
        if (!in_array($type, $allowedTypes = array('text', 'menu', 'html', 'date')))
            throw new Exception ("Unknown treeNode type: '$type', allowed types are '".implode("', '", $allowedTypes)."'");
    }

    function getType() {
        return $this->type;
    }    
    
    function doListPassthroughParams() {
        return array_merge(parent::doListPassthroughParams(), array(
            'parentId',
            'content',
            'href',
            'expanded',
            'selected',
            'type',
            'displayOrder',
        ));
    }
    
    protected function jsGetParentId() {
        if ($this->displayParent) $res = 'v_'.$this->displayParent->getResponderId();
            else $res = null;
        return $res;
    }
    
    function triggerFrontendEnter() {
        $this->selected = true;
        $this->triggerEvent('enter');
    }
    
    function triggerFrontendExit() {
        $this->selected = false;
        $this->triggerEvent('exit');
    }
    
    function triggerFrontendClick() {
        $this->triggerEvent('click');
        $p = $this->parent;
        while ($p instanceof Pmt_Tree_Parent) {
            if ($p->observeChildClicks) {
                $p->notifyChildClick($this);
            }
            $p = $p->parent;
        }
    }
    
    function triggerFrontendDblClick() {
        $this->triggerEvent('dblClick');
        $p = $this->parent;
        while ($p instanceof Pmt_Tree_Parent) {
            if ($p->observeChildClicks) {
                $p->notifyChildDblClick($this);
            }
            $p = $p->parent;
        }
    }
    
    function triggerFrontendExpand() {
        $this->expanded = true;
        $this->triggerEvent('expand', array('byUser' => true));
        $p = $this->parent;
        while ($p instanceof Pmt_Tree_Parent) {
            if ($p->observeChildExpand) {
                $p->notifyChildExpand($this, true);
            }
            $p = $p->parent;
        }
    }
    
    function triggerFrontendCollapse() {
        $this->expanded = false;
        $this->triggerEvent('collapse');
        $p = $this->parent;
        while ($p instanceof Pmt_Tree_Parent) {
            if ($p->observeChildCollapse) {
                $p->notifyChildCollapse($this);
            }
            $p = $p->parent;
        }
    }
    
    function setData($data) {
        $this->data = $data;
    }

    function getData() {
        return $this->data;
    }
    
    function expandAncestors() {
        $p = & $this;
        while (($p = & $p->getParent()) instanceof Pmt_Tree_Node) {
            $p->setExpanded(true);
        }
    }
    
    function changeParent(Pmt_Tree_Parent $otherParent) {
        $this->setParent($otherParent);
        $this->sendMessage('changeParent', array($otherParent->getResponderId()), 1);
    }
    
    function scrollIntoView() {
        
        $this->sendMessage('scrollIntoView', array(1), 1);
    }
    
    function setTriggerEventOnSoftExpand($triggerEventOnSoftExpand) {
        $this->triggerEventOnSoftExpand = $triggerEventOnSoftExpand;
    }

    function getTriggerEventOnSoftExpand() {
        return $this->triggerEventOnSoftExpand;
    }

    protected function doListDelayableMessages() {
        return array_merge(parent::doListDelayableMessages(), array('scrollIntoView'));
    }
    
    protected function doOnDestroy() {
        parent::doOnDestroy();
        $parentTree = false;
        $p = & $this->parent;
        while ($p && !$p instanceof Pmt_Tree_View) {
            $p = & $p->parent;
        }
        if ($p instanceof Pmt_Tree_View) $p->notifyNodeDestroyed($this);
    } 

}

?>