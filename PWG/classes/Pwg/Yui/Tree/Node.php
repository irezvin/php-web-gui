<?php

abstract class Pwg_Yui_Tree_Node extends Pwg_Struct_Tree_Node {

    private static $LAST_UID = 0;

    protected $uid = 0;
    
    protected $data = false;

    protected $expanded = false;
    	
	protected $nodeBaseClass = 'Pwg_Yui_Tree_Node';
    
    
	abstract protected function getJsNodeType();
    

	function __construct(array $options = array()) {
	    $this->uid = ++self::$LAST_UID;
	    parent::__construct($options);
	}
	
	function __wakeup() {
	    self::$LAST_UID = max($this->uid, self::$LAST_UID);
	}
	
	function getUid() {
	    return $this->uid;
	}
	
	/**
	 * @param $uid
	 * @return Pwg_Yui_Tree_Node
	 */
	function findNodeByUid($uid) {
	    if ($this->uid == $uid) return $this;
	    $this->listNodes();
	    foreach ($this->nodes as $node) {
	        if ($res = $node->findNodeByUid($uid)) return $res;
	    }
	    return null;
	}

	function setData($data) {
        $this->data = $data;
    }

    function getData() {
        return $this->data;
    }

    function setExpanded($expanded, $dontSendMessage = false) {
        if ($expanded !== ($oldExpanded = $this->expanded)) {
            $this->expanded = $expanded;
            if (!$dontSendMessage) {
                $this->callMethod($expanded? 'expand' : 'collapse');
                $t = $this->getTree();
                if ($t) $t->notifyChildExpandChange($this, $expanded);
            }
        }
    }

    function getExpanded() {
        return $this->expanded;
    }

    
    function expandAncestors() {
        for ($p = $this->getParentNode(); $p; $p = $p->getParentNode()) $p->setExpanded(true); 
    }
    
    function scrollIntoView() {
        if ($t = $this->getTree()) {
            $this->expandAncestors();
            $t->msgScrollNodeIntoView($this);
        }
    }
    
    /**
     * @return Pwg_Yui_Tree
     */
    function getTree() {
    	$n = $this;
    	while ($n && !($n instanceof Pwg_Yui_Tree_Root)) {
    		$n = $n->getParentNode();
    	}
    	if ($n) $res = $n->getTree();
    		else $res = null;
    	return $res;
    }
    
    protected function getJsConstructorArgs() {
        $res = array();
        $res['uid'] = $this->uid;
		foreach (array('expanded') as $p)
    		if (strlen($this->$p)) $res[$p] = $this->$p;
    		
		foreach ($this->listNodes() as $i) {
			$res['children'][] = $this->getNode($i)->toJs();
		}
        return $res;
    }

	function toJs() {
		$res = new Ae_Js_Call($this->getJsNodeType(), array($this->getJsConstructorArgs()), true);
		return $res;
	}
	
	protected function sendProperty($propName, $propValue) {
		if ($t = $this->getTree()) $t->msgSetNodeProperty($this, $propName, $propValue);
	}
	
	protected function callMethod($methodName) {
		$args = func_get_args();
		if ($t = $this->getTree()) $t->msgExecuteNodeMethod($this, $methodName, array_slice($args, 1));
	}

	protected function doOnNodeRemoved(Pwg_Struct_Tree_Node $node, $oldIndex) {
	    if ($t = $this->getTree()) {
            $t->msgRemoveChild($this, $oldIndex);
	    }
	}
	
	protected function doOnDestroy() {
	    if ($t = $this->getTree()) $t->msgDeleteNode($this);
	}
	
    protected function doOnSetParentNode(Pwg_Struct_Tree_Node $oldParentNode = null) {
        $t = $this->getTree();
        if ($t) {
            if ($this->parentNode) $t->msgAddNode($this, $this->parentNode, $this->parentNode->getNodeIndex($this));
        } 
    }
    
    protected function doOnMoveNode(Pwg_Struct_Tree_Node $node, $oldIndex, $newIndex) {
        $t = $this->getTree();
        $t->msgMoveNode($node, $newIndex);
    }
    
}