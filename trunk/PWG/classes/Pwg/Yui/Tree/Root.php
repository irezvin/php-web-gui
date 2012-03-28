<?php

class Pwg_Yui_Tree_Root extends Pwg_Yui_Tree_Node {

	/**
	 * @var Pwg_Yui_Tree 
	 */
	protected $tree = false;
	
    protected function setTree(Pwg_Yui_Tree $tree) {
        $this->tree = $tree;
    }

    /**
     * @return Pwg_Yui_Tree 
     */
    function getTree() {
        return $this->tree;
    }	
    
    function getJsNodeType() {
    	return '';
    }
    
    function toJs() {
    	$res = array();
    	foreach ($this->listNodes() as $i) {
			$res[] = $this->getNode($i);
		}
    	return $res;
    }
	
}