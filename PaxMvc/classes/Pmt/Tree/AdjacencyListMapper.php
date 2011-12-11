<?php

/**
 * @deprecated
 * Use Ae_Tree_AdjacencyListMapper
 */
class Pmt_Tree_AdjacencyListMapper extends Ae_Tree_AdjacencyListMapper {
	
    var $_rootNodeId = false;
    
    var $_childrenCountRelation = false;
    
    var $_childIdsRelation = false;
    
    var $_containersRelation = false;
    
    var $_treeProvider = false;
    
    /**
     * @var Ae_Sql_Db_Ae
     */
    var $sqlDb = false;
    
    /**
     * @var Ae_Sql_Statement_Cache
     */
    var $_stmtCache = false;
    
    // Variables to be overridden in concrete class

    protected $nodeParentField = 'parentId';
    
    protected $nodeOrderField = 'ordering';

    function Pmt_Tree_AdjacencyListMapper($tableName, $recordClass, $pk = 'id') {
    	parent::Ae_Model_Mapper($tableName, $recordClass, $pk);
    	Ae_Dispatcher::loadClass('Ae_Sql_Db_Ae');
    	$this->sqlDb = new Ae_Sql_Db_Ae($this->database);
    	$this->_stmtCache = new Ae_Sql_Statement_Cache(array(
    		'defaults' => array(
    			'table' => $this->tableName,
    			'pk' => $this->pk,
    			'nodeParent' => $this->nodeParentField,
    			'nodeOrder' => $this->nodeOrderField,
    		),
    	));
    }    
    
    function listTopNodes() {
        $stmt = $this->_stmtCache->getStatement('SELECT [[pk]] FROM [[table]] WHERE '.$this->database->getIfnullFunction().'([[nodeParent]], 0) = 0 ORDER BY [[nodeOrder]]');
        $res = $this->sqlDb->fetchColumn($stmt);
        Pm_Conversation::log("topNodes are", $res);
        return $res; 
    }
    
    function getNodeClass() {
        return 'Pmt_Tree_AdjacencyListImpl';
    }
    
    function loadOriginalDataForNode(Pmt_Tree_AdjacencyListImpl $node) {
        $nid = $node->getNodeId();
        Pm_Conversation::log("Mapper: loading original data for node $nid", gettype($nid));
        $res = false;
        if ($nid !== false) {
            $od = array_values($this->loadOriginalData(array($nid)));
            if (count($od)) {
                $node->setOriginalData($od[0]);
                $res = true;
            }
        }
        return $res;
    }
    
    function loadOriginalData(array $nodeIds) {
    	$stmt = $this->_stmtCache->getStatement('
    		SELECT [[pk]] AS nodeId, [[nodeParent]] AS parentId, [[nodeOrder]] AS ordering 
    		FROM [[table]] WHERE [[pk]] IN ({{ids}})
    	', array('ids' => $nodeIds));
        
    	$res = $this->sqlDb->fetchArray($stmt, 'nodeId');
    	return $res;
    }
    
    function loadNodes(array $ids) {
    	
        $res = array();
        foreach ($this->loadOriginalData($ids) as $id => $node) {
        	$prot = array(
        		'mapper' => $this,
        	    'originalData' => $node
            );
            $objNode = new Pmt_Tree_AdjacencyListImpl($prot);
            $res[$id] = $objNode;
        }
        return $res;
    }
    
    function loadNodeChildrenCounts(array $nodes) {
        $rel = $this->getNodeChildrenCountRelation();
        return $rel->loadDest($nodes, false, true);        
    }
    
    function loadNodeAllChildrenCounts(array $nodes) {
    	$ids = array();
    	$nodesByIds = array();
    	foreach ($nodes as $node) {
    		$nodeId = $node->getNodeId();
    		$nodesByIds[$nodeId] = $node;
    		$ids[] = $nodeId; 
    	}
    	foreach (($childIdsRecursive = $this->loadChildIdsRecursive($ids)) as $nodeId => $childIds) {
    		$f = Ae_Util::flattenArray($childIds);
    		$nodesByIds[$nodeId]->setAllChildNodesCount(count($f));
    	}
    	return $childIdsRecursive;
    }
    
    function loadChildIdsRecursive(array $ids) {
    	$res = array();
    	$nodes2load = array();
    	foreach ($ids as $id) 
    		$nodes2load[] = array('nodeId' => $id);
    	$this->getNodeChildIdsRelation()->loadDest($nodes2load, true, false);
    	foreach ($nodes2load as $node) {
    		$childIds = array();
    		if (isset($node['childNodeIds']) && is_array($node['childNodeIds'])) 
    			foreach($node['childNodeIds'] as $nId) $childIds[] = $n['childId'];
    		if ($childIds) $childrenRecursive = $this->loadChildIdsRecursive($childIds);
    			else $childrenRecursive = array();
    		$res[$node['nodeId']] = $childrenRecursive;
    	}
    	return $res;
    }
    
    function loadNodeChildIds(array $nodes) {
        $rel = $this->getNodeChildIdsRelation();
        $n = Ae_Util::flattenArray($rel->loadDest($nodes, false, true), 1);
        $ids = array();
        foreach ($n as $i) $ids[] = $i['id'];
    	return $ids;
    }
    
    function loadNodeContainers(array $nodes) {
        $rel = $this->getNodeContainersRelation();
        return $rel->loadDest($nodes, false, true);
    }
    
    function getNodePath($id) {
        $sql = $this->_stmtCache->getStatement('SELECT [[nodeParent]] FROM [[table]] WHERE [[pk]] = {{id}}', array('id' => $id));
        $parentId = $this->sqlDb->fetchValue($sql, 0, null);
        if (!is_null($parentId)) $res = array_merge($this->getNodePath($parentId), array($parentId));
            else $res = array();
        return $res;
    }
    
    /**
     * @return Ae_Model_Relation
     */
    function getNodeChildrenCountRelation() {
        if ($this->_childrenCountRelation === false) {
            $this->_childrenCountRelation = new Ae_Model_Relation(array(
                'srcTableName' => $this->tableName,
                'destTableName' => new Ae_Sql_Expression("
                (
                    SELECT {$this->nodeParentField} AS parentId, COUNT(ns.{$this->pk}) AS ".$this->database->NameQuote('count')." 
                    FROM {$this->tableName} AS ns 
                    WHERE ".$this->database->getIfnullFunction()."({$this->nodeParentField}, 0) <> 0 
                    GROUP BY {$this->nodeParentField}
                )  AS nsc"),
                'fieldLinks' => array(
                    'nodeId' => 'parentId',
                ),
                'srcVarName' => 'childNodesCount',
                'srcIsUnique' => true,
                'destIsUnique' => true,
                'database' => $this->database,
            ));
        }
        return $this->_childrenCountRelation;
    }
    
    /**
     * @return Ae_Model_Relation
     */
    function getNodeChildIdsRelation() {
        if ($this->_childIdsRelation === false) {
        	$this->_childIdsRelation = new Ae_Model_Relation(array(
                'srcTableName' => $this->tableName,
                'destTableName' => new Ae_Sql_Expression("
                	( SELECT "
        				.$this->database->NameQuote($this->nodeParentField)." AS id, "
        				.$this->database->NameQuote($this->nodeOrderField)." AS ordering, "
        				.$this->database->NameQuote($this->pk)." AS childId 
        			  FROM ".$this->database->NameQuote($this->tableName)." 
        			  WHERE ".$this->database->getIfnullFunction()."(".$this->database->NameQuote($this->nodeParentField).", 0) <> 0) AS childIds
                "),
                'fieldLinks' => array(
                    'nodeId' => 'id',
                ),
                'srcVarName' => 'childNodeIds',
                'srcIsUnique' => true,
                'destIsUnique' => false,
                'database' => $this->database,
                //'destOrdering' => $this->database->NameQuote($this->nodeOrderField),
                'destOrdering' => 'ordering',
            ));
        }
        return $this->_childIdsRelation;
    }
    
    /**
     * @return Ae_Model_Relation
     */
    function getNodeContainersRelation() {
        if ($this->_containersRelation === false) {
            $this->_containersRelation = new Ae_Model_Relation(array(
                'srcTableName' => $this->tableName,
                'destMapperClass' => get_class($this),
                'fieldLinks' => array(
                    'nodeId' => $this->pk,
                ),
                'srcVarName' => 'container',
                'srcIsUnique' => true,
                'destIsUnique' => true,
                'database' => $this->database,
            ));
        }
        return $this->_containersRelation;
    }
    
    /**
     * @return Pmt_I_Tree_Provider
     */
    function getDefaultTreeProvider() {
        if ($this->_treeProvider === false) {
            $this->_treeProvider = $this->createTreeProvider();
        }
        return $this->_treeProvider;
    }

    /**
     * @return Pmt_I_Tree_Provider
     */
    function createTreeProvider() {
    	return new Pmt_Tree_Provider($this);
    }
    
    function getNodeParentField() {
        return $this->nodeParentField;
    }

    function getNodeOrderField() {
        return $this->nodeOrderField;
    }
    

    protected function getOrderingValuesColumns() {
        return 't.'.$this->getTitleFieldName();
    }
    
    protected function getOrderingValuesLabel(array $entry) {
        return implode(' - ', $entry);
    }
    
    /** 
     * @return array 
     */
    function getOrderingValues(Ae_Model_Object $modelObject) {
        $res = array();
        $ords = array();
        $pId = $modelObject->getTreeImpl()->getParentNodeId();
        
        if (!strlen($pId) || ((string) $pId == '0')) {
            $crit = $this->database->getIfnullFunction()."(t.{$this->nodeParentField},0) = 0";
        } else {
            $crit = "t.{$this->nodeParentField} = ".$this->database->Quote($pId);
        }
            
        $foundMyself = false;
        $this->database->setQuery($sql = "
            SELECT t.".$this->database->NameQuote($this->nodeOrderField)." AS ordering, ".$this->getOrderingValuesColumns().", t.{$this->pk} 
            FROM {$this->tableName} AS t 
            WHERE {$crit} 
            ORDER BY t.".$this->database->NameQuote($this->nodeOrderField)." ASC
        ");
        $ords = $this->database->loadAssocList();
        foreach ($ords as $ord) {
            $lbl = $this->getOrderingValuesLabel($ord);
            if ($ord[$this->pk] == $modelObject->{$this->pk}) {
                $lbl .= ' '.(new Pmt_Lang_String('model_ordering_current'));
                $foundMyself = true;
            }
            $res[$ord['ordering']] = $lbl;   
        }
        if (!count($ords)) $res[' 0'] = new Pmt_Lang_String('model_ordering_only');
        elseif (!$foundMyself) {
            $res[' '.($ord['ordering'] + 1)] = new Pmt_Lang_String('model_ordering_last');
        }
        return $res;
    }
    
    /**
     * @return Pmt_I_Tree_Impl
     */
    function createTreeImpl(Ae_Model_Object $modelObject) {
        $nc = $this->getNodeClass();
        return new $nc(array(
            'container' => $modelObject,
            'mapper' => $this,
        ));        
    }
    
    function reorderNode($oldParentId, $oldOrdering, $newParentId, $newOrdering) {
                    
        $db = $this->sqlDb;
        
                    if ($oldParentId != $newParentId) {
                        
                        // move with parent change...
                        
                        $db->query($this->_stmtCache->getStatement('
                                UPDATE [[table]] 
                                SET [[nodeOrder]] = [[nodeOrder]] + 1
                                WHERE [[nodeParent]] = {{newParentId}} AND [[nodeOrder]] >= {{newOrdering}}
                            ', array('newParentId' => $newParentId, 'newOrdering' => $newOrdering)
                        ));
                        
                        $db->query($this->_stmtCache->getStatement('
                                UPDATE [[table]] 
                                SET [[nodeOrder]] = [[nodeOrder]] - 1
                                WHERE [[nodeParent]] = {{oldParentId}} AND [[nodeOrder]] >= {{oldOrdering}}
                            ', array('oldParentId' => $oldParentId, 'oldOrdering' => $oldOrdering)
                        ));
                    
                    } else {
                        
                        if ($newOrdering > $oldOrdering) {
                            $rightOrder = $newOrdering;
                            $leftOrder = $oldOrdering;
                            $delta = '-1';
                        } elseif ($newOrdering < $oldOrdering) {
                            $rightOrder = $oldOrdering;
                            $leftOrder = $newOrdering;
                            $delta = '+ 1';
                        } else {
                            $delta = false;
                        }
                        
                        if ($delta !== false) { 
                            
                            $db->query($this->_stmtCache->getStatement('
                                    UPDATE [[table]] 
                                    SET [[nodeOrder]] = IF ([[pk]] = {{id}}, {{newOrdering}}, [[nodeOrder]] {{delta}})
                                    WHERE ([[nodeOrder]] BETWEEN {{leftOrder}} AND {{rightOrder}}) AND [[nodeParent]] = {{parentId}}
                                ', array('parentId' => $newParentId, 'leftOrder' => $leftOrder, 'rightOrder' => $rightOrder, 'newOrdering' => $newOrdering, 'id' => $id, 'delta' => new Ae_Sql_Expression($delta))
                            ));
                        
                        }
                        
                    }        
    }
    

}