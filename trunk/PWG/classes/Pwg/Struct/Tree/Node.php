<?php

class Pwg_Struct_Tree_Node extends Pwg_Autoparams implements Pwg_I_Refcontrol {
    
    const idPathSeparator = '_';
    
    /**
     * @var Pwg_Struct_Tree_Node 
     */
    protected $parentNode = null;
    
    protected $id = false;
    
    protected $idPath = false;
    
    protected $nodes = false;
    
    protected $nodePrototypes = array();
    
    protected $nodeBaseClass = false;
    
    protected $displayOrder = false;

    
    // ------------- Pwg_I_Refcontrol implementation -------- 
    

    protected $refReg = array();

    protected function refGetSelfVars() {
        $res = array();
        foreach (array_keys(get_object_vars($this)) as $v) $res[$v] = & $this->$v;
        return $res;
    }
    
    function refHas($otherObject) { return Pwg_Impl_Refcontrol::refHas($otherObject, $this->refReg); }
    
    function refAdd($otherObject) { return Pwg_Impl_Refcontrol::refAdd($this, $otherObject, $this->refReg); }
    
    function refRemove($otherObject, $nonSymmetrical = false) { $v = $this->refGetSelfVars(); return Pwg_Impl_Refcontrol::refRemove($this, $otherObject, $v, false, $nonSymmetrical); }

    function refNotifyDestroying() { return Pwg_Impl_Refcontrol::refNotifyDestroying($this, $this->refReg); }
    
    // ------------- /Pwg_I_Refcontrol implementation -------- 
    
    
    protected function checkClass(Pwg_Struct_Tree_Node $node = null) {
    	if ($node && strlen($this->nodeBaseClass) && !($node instanceof $this->nodeBaseClass))
    		trigger_error("Node {$node->id} isn't an instance of class '{$this->nodeBaseClass}' (but is instance of class '".get_class($node)."')", E_USER_ERROR);
    }
    
    protected function setId($id) {
         $this->id = $id;
    }

    function getId() {
        return $this->id;
    }

    function getIdPath() {
        if ($this->idPath === false) {
            $res = $this->id;
            if ($this->parentNode) $res = $this->parentNode->getIdPath().self::idPathSeparator.$res;
        }
        return $res;
    }
    
    function getIndexPath() {
    	$res = array();
    	if ($this->parentNode) $res = array_merge($this->parentNode->getIndexPath(), array($this->parentNode->getNodeIndex($this)));
    	return $res;
    }
    
    final function setParentNode(Pwg_Struct_Tree_Node $parentNode = null, Pwg_Struct_Tree_Node $placeBeforeNode = null, $suppressWarnings = false) {
        $this->checkClass($parentNode);
    	if ($parentNode !== ($oldParentNode = $this->parentNode)) {
            $this->parentNode = $parentNode;
            $this->idPath = false;
            if ($this->parentNode) $this->parentNode->insertNode($this, $placeBeforeNode, $suppressWarnings);
            	elseif ($oldParentNode) $oldParentNode->removeNode($this);
        }
    }
    
    /** Template method */
    protected function doOnSetParentNode(Pwg_Struct_Tree_Node $oldParentNode = null) {
    }

    /**
     * @return Pwg_Struct_Tree_Node
     */
    function getParentNode() {
        return $this->parentNode;
    }

    function listNodes() {
        if ($this->nodes === false) {
            $this->nodes = array_values(Pwg_Autoparams::factoryCollection(
                $this->nodePrototypes, 
                strlen($this->nodeBaseClass)? $this->nodeBaseClass : 'Pwg_Struct_Tree_Node',
                array(),
                'id',
                false
            ));
            foreach ($this->nodes as $n) $n->parentNode = $this;
            $this->nodePrototypes = array();
        }
        return array_keys($this->nodes);
    }   
    
    function getNode($index, $suppressWarning = false) {
    	if (in_array($index, $this->listNodes())) {
    		$res = $this->nodes[$index];
    	} else {
    		if (!$suppressWarning) trigger_error("There is no node with index #{$index} in node #{$this->id}", E_USER_NOTICE);
			$res = null;    		
    	}
    	return $res;
    }

    function getNodeIndex(Pwg_Struct_Tree_Node $node = null) {
        if (is_null($node)) {
            $res = 0;
            if ($this->parentNode) $res = $this->parentNode->getNodeIndex($this);
            return $res;
        } else {
    	    $res = false;
    	    $this->listNodes();
    	    return array_search($node, $this->nodes, true);
        }
    }
    
    protected function findIndexByDisplayOrder($displayOrder, $returnNextNode = false, $defaultIndex = false) {
        $res = false;
        $this->listNodes();
        $nextNode = null;
        $index = $defaultIndex;
        foreach($this->nodes as $i => $node) {
            if ($node->displayOrder !== false) {
                if (is_numeric($displayOrder) && is_numeric($node->displayOrder)) {
                    $crit = $displayOrder < $node->displayOrder;
                } else {
                    $crit = strcmp($displayOrder, $node->displayOrder) < 0;
                }
                if ($crit) {
                    $nextNode = $node;
                    $index = $i;
                    break;
                } 
            }
        }
        if ($returnNextNode) return $nextNode;
        else return $index;
    }
    
    final function removeNode(Pwg_Struct_Tree_Node $node, $suppressWarning = false) {
    	if (($i = $this->getNodeIndex($node)) !== false) {
    		array_splice($this->nodes, $i, 1);
    		$this->doOnNodeRemoved($node, $i);
    		$node->doOnSetParentNode($this, $i);
    	} else {
    		if (!$suppressWarning) trigger_error("Node #{$node->id} isn't child of node #{$this->id}", E_USER_NOTICE);
    	}
    	return $node;
    }
    
    protected function doOnNodeRemoved(Pwg_Struct_Tree_Node $node, $index) {
    }
    
    /**
     * @return Pwg_Struct_Tree_Node
     */
    function getNextChild(Pwg_Struct_Tree_Node $node, $suppressWarning = false) {
    	$idx = $this->getNodeIndex($node);
    	if ($idx === false) {
    		if (!$suppressWarning) trigger_error("Node #{$node->id} isn't child of node #{$this->id}", E_USER_NOTICE);
    		$res = false;
    	} else {
    		if ($idx < (count($this->nodes) - 1)) $res = $this->nodes[$idx + 1];
    			else $res = null; 
    	}
    	return $res;
    }
    
    final function insertNode(Pwg_Struct_Tree_Node $node, Pwg_Struct_Tree_Node  $beforeNode = null, $suppressWarnings = false) {
        $this->checkClass($node);
    	$ni = $this->getNodeIndex($node);
    	if (is_null($beforeNode)) {
    	    if ($node->displayOrder !== false) {
    	        $beforeNode = $this->findIndexByDisplayOrder($node->displayOrder, true, false);
    	    }
    	    if (method_exists($node, 'getLabel')) $t = " '".$node->getLabel()."'";
    	        else $t = '';
    	}
        if ($beforeNode !== null) {
        	$idx = $this->getNodeIndex($beforeNode);
        	if ($idx === false) {
        		if (!$suppressWarnings)   trigger_error("Node #{$beforeNode->id} isn't child of node #{$this->id}", E_USER_NOTICE);
        		$idx = count($this->nodes);
        	} else {
        	}
        } else {
        	$idx = count($this->nodes);
        }
        if (($node instanceof Pwg_Yui_Tree_Node_Text) && ($node->getLabel() == 'Дубининская')) {
        }
        if ($ni !== ($idx - 1)) {
        	if ($ni !== false) {
        	    array_splice($this->nodes, $ni, 1);
        	    $targetIdx = ($idx < $ni)? $idx - 1 : $idx;
        	} else {
        	    $targetIdx = $idx;
        	}
        	array_splice($this->nodes, $targetIdx, 0, array($node));
        	if ($ni !== false) $this->doOnMoveNode($node, $ni, $targetIdx);
        		else {
        			$oldParentNode = $node->parentNode;
        			$node->setParentNode(null);
        			$node->parentNode = $this;
            		$node->doOnSetParentNode($oldParentNode);
        			$this->doOnInsertNode($node, $targetIdx);
        		}
        }

    }

    
    final function moveNode(Pwg_Struct_Tree_Node $node, $newIndex, $suppressWarnings = false) {
    	$index = $this->getNodeIndex($node);
        if ($index === false) {
        		if (!$suppressWarnings)
        		    trigger_error("Node #{$node->id} isn't child of node #{$this->id}", E_USER_NOTICE);
        		$res = false;
        } else {
        	if ($index !== $newIndex) {
        	    array_splice($this->nodes, $index, 1);
        	    $targetIndex = $index < $newIndex? $newIndex - 1 : $newIndex; 
        	    array_splice($this->nodes, $newIndex, 0, array($node));
        	    $this->doOnMoveNode($node, $index, $targetIndex);
        	}
        	$res = true;
        }
        return $res;
    }
    
    protected function doOnInsertNode(Pwg_Struct_Tree_Node $node, $index) {
    }
    
    protected function doOnMoveNode(Pwg_Struct_Tree_Node $node, $oldIndex, $newIndex) {
    }
    
    protected function setNodePrototypes(array $nodePrototypes) {
        $this->nodePrototypes = $nodePrototypes;
    }
    
    final function destroy() {
        if ($this->nodes) foreach($this->nodes as $n) $n->destroy();
        $this->doOnDestroy();
        $index = $this->parentNode->getNodeIndex($this);
        if ($index >= 0) array_splice($this->parentNode->nodes, $index, 1); 
        $this->refNotifyDestroying();
        unset($this->parent);
    }
    
    protected function doOnDestroy() {
    }

    function findNodesByPattern(array $pattern = array(), $baseClass = 'Pwg_Struct_Tree_Node', $recursive = true, $strict = false) {
        $res = array();
        foreach ($this->listNodes() as $i) {
            $n = $this->getNode($i);
            if ($n instanceof $baseClass && (Pwg_Autoparams::itemMatchesPattern($n, $pattern, $strict))) $res[] = $n;
            if ($recursive) $res = array_merge($res, $n->findNodesByPattern($pattern, $baseClass, $recursive, $strict));
        }
        return $res;
    }

    function setDisplayOrder($displayOrder, $suppressWarnings = false) {
        if ($displayOrder !== ($oldDisplayOrder = $this->displayOrder)) {
            $this->displayOrder = $displayOrder;
            if ($this->parentNode && $this->displayOrder !== false) {
                $currentIndex = $this->getNodeIndex();
                $newIndex = $this->parentNode->findIndexByDisplayOrder($this->displayOrder, false, $currentIndex);
                if ($newIndex !== false) $this->parentNode->moveNode($this, $newIndex, $suppressWarnings); 
            }
        }
    }

    function getDisplayOrder() {
        return $this->displayOrder;
    }    
    
    
}