<?php

class Pmt_Data_Source_Lister extends Pmt_Autoparams {

    /**
     * @var Pmt_Data_Source
     */
    protected $dataSource = false;

    function setDataSource(Pmt_Data_Source $dataSource = null) {
        $this->dataSource = $dataSource;
    }
    
    /**
     * @return Pmt_Data_Source
     */
    function getDataSource() {
        return $this->dataSource;
    }

    /**
     * Is called when query parameters are changed
     */
    function refresh() {
    }
    
    
    /**
     * Calculates values of given expressions for the record identified by primary key and returns numeric array with results if such record exists.
     * 
     * @param string|array $pk Primary key values for table identified by $this->createCollection()
     * @param array $sqlExpressions List of sql expressions. Table is referenced by alias $this->alias.  
     * @return array expression values 
     */
    protected function getSqlExpressionsByKey($pk, $sqlExpressions) {
        if (!is_array($pk)) $pk = array($pk); 
        $coll = $this->dataSource->createCollection();
        $i = 0;
        
        $columns = array();
        foreach ($sqlExpressions as $o) {
            $columns[] = $o.' AS _expr_'.$i;
            $i++;
        }
        
        $aeDb = $this->dataSource->getAeDb();
        $coll->addWhere($aeDb->sqlKeysCriteria(array($pk), $this->dataSource->getMapper()->listPkFields(), $this->dataSource->getAlias()));
        $sql = "SELECT ".implode(", ", $columns)." ".$coll->getStatementTail();
        $aeDb->setQuery($sql);
        $rows = $aeDb->loadAssocList();
        $res = false;
        if (count($rows)) {
            $res = array_values($rows[0]);
        }
        return $res;
    }
    
    function getRecordNumberByKey($pk) {
        $pkf = $this->dataSource->getMapper()->listPkFields();
        $coll = $this->dataSource->createCollection();
        $owd = $this->dataSource->getOrderingWithDirections($this->dataSource->getEffectiveOrdering(), true);
        $ordValues = $this->getSqlExpressionsByKey($pk, $owd[0]);
        if ($ordValues !== false) {
            $crits = array();
            $i = 0;
            $aeDb = $this->dataSource->getAeDb();
            foreach ($owd[0] as $orderExpr) {
                //$o = $orderExpr;
                //$crits[] = "IF(".$o.'<>'.$aeDb->Quote($ordValues[$i]).','.$o.($owd[1][$i]? ' < ' : ' > ').$aeDb->Quote($ordValues[$i]);
                $val = $aeDb->Quote($ordValues[$i]);
                
                $falseOrTrue = $owd[1][$i]? "0" : "1";
                $trueOrFalse = $owd[1][$i]? "1" : "0";
                $lessOrGreater = $owd[1][$i]? "<" : ">";
                
                if (!is_null($ordValues[$i])) {
                    $eq = $aeDb->ifStatement("$orderExpr IS NULL", "0", $aeDb->ifStatement("$orderExpr <> $val", "1", "0"));
                    $compare = $aeDb->ifStatement("$orderExpr IS NULL", $trueOrFalse, $aeDb->ifStatement("$orderExpr $lessOrGreater $val", "1", "0"));
                    $c = $aeDb->ifStatement(
                        "$eq = 1", $compare, "", false 
                    );
                    //$crits[] = "IF(IFNULL( $orderExpr <> $val, $falseOrTrue ), IFNULL($orderExpr $lessOrGreater $val, $trueOrFalse)";
                    $crits[] = $c;
                } else {
                    //$crits[] = "IF(NOT ISNULL($orderExpr), $falseOrTrue";
                    $crits[] = $aeDb->ifStatement("$orderExpr IS NULL", $falseOrTrue, "", false);
                }
                
                $i++; 
            }
            $coll->addWhere(implode(" ", $crits)."0".str_repeat($aeDb->ifClose(), $i).' = 1');
            if ($this->dataSource->getDontGroupOnCount() && $this->dataSource->getGroupBy()) $coll->setGroupBy(false);
            if ($this->dataSource->getDebug()) Pm_Conversation::log($coll->_getSql());
            $res = $coll->countRecords();
        } else {
            // no such record
            $res = false;
        }
        return $res;
    }
    
    
//  DataSource: record retrieval and editing
    
    function getRecords($startIndex = false, $length = false) {
        $coll = $this->dataSource->createCollection();
        if ($startIndex !== false)
            $coll->setLimits(max(0, $startIndex), $length);
            
        $res = array();
        while($rec = $coll->getNext()) {
            Pm_Conversation::log("LOADED PK is", $rec->getPrimaryKey());
            $res[] = $rec;
            unset($rec);
        }
        return $res;
    }
    
    function getKeyByRecordNumber($number, $returnWholeRecord = false) {
        if ($number < 0) return false;
        $coll = & $this->dataSource->createCollection();
        $coll->setOrder($this->dataSource->getEffectiveOrdering());
        $coll->setLimits($number, 1);
        if (!$returnWholeRecord) {
            $cols = implode(", ", $this->dataSource->getMapper()->listPkFields());
            $sql = $coll->getStatementTail(true, true, true, $cols);
            $aeDb = $this->dataSource->getAeDb();
            $aeDb->setQuery($sql);
            $rows = $aeDb->loadResult();
            if (count($rows)) {
                $res = array_values($rows[0]);
                if (count($pk) == 1) $res = $res[0]; 
            } else $res = false;
            return $res;
        } else {
            $res = $coll->getNext();
        }
        
        if ($this->dataSource->getDebug()) {
            $cols = implode(", ", $this->getMapper()->listPkFields());
            $sql = $coll->getStatementTail(true, true, true, $cols);
            Pm_Conversation::log($this->dataSource->getId(), "Number is", $number, "sql is ", $sql, "pk is ", $res? $res->getPrimaryKey() : "");
        }
        return $res;
    }
    
}