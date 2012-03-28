<?php

class Pmt_Yui_Tree_Root extends Pmt_Yui_Tree_Node {

	/**
	 * @var Pmt_Yui_Tree 
	 */
	protected $tree = false;
	
    protected function setTree(Pmt_Yui_Tree $tree) {
        $this->tree = $tree;
    }

    /**
     * @return Pmt_Yui_Tree 
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