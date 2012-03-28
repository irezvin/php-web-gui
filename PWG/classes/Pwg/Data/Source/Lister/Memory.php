<?php

class Pwg_Data_Source_Lister_Memory extends Pwg_Data_Source_Lister {
    
    protected $keyIndex = false;
    
    protected $indexKey = false;
    
    /**
     * Is called when query parameters are changed
     */
    function refresh() {
        $this->keyIndex = false;
    }
    
    protected function updateKeyIndex() {
        if ($this->keyIndex === false) {
            $this->keyIndex = array();
            $this->indexKey = array();
            $coll = & $this->dataSource->createCollection();
            $coll->setOrder($this->dataSource->getEffectiveOrdering());
            $pkf = $this->dataSource->getMapper()->listPkFields();
            $alias = $coll->getAlias();
            $pkkf = array();
            foreach ($pkf as $f) $pkkf[] = $alias.'.'.$f;
            $cols = implode(", ", $pkkf);
            $sql = $coll->getStatementTail(true, true, true, $cols);
            $sqlDb = $this->dataSource->getSqlDb();
            $idx = 0;
            $cpkf = count($pkf);
            foreach ($sqlDb->fetchArray($sql) as $row) {
                $pk = '';
                foreach ($pkf as $f) {
                    if (is_a($row[$f], 'DateTime')) $row[$f] = Ae_Util::date($row[$f], 'm/d/Y H:i:s');
                    $pk .= '_'.$row[$f];
                }
                if ($cpkf == 1) $pka = $row[$pkf[0]];
                    else {
                        $pka = array();
                        foreach ($pkf as $f) $pka[] = $row[$f];
                    }
                $this->indexKey[$idx] = $pka;
                $this->keyIndex[$pk] = $idx;
                $idx++;
            }
        }
    }
    
    function getRecordNumberByKey($pk) {
        $this->updateKeyIndex();
        //if (is_object($pk)) $pk = $this->dataSource->getLegacyDb()->quote($pk);
        if (is_array($pk)) {
            $pk = '_'.implode('_', $pk);
        } else {
            $pk = '_'.$pk;
        }
        if (isset($this->keyIndex[$pk])) $res = $this->keyIndex[$pk];
            else $res = false;
        return $res;
    }
    
    
//  DataSource: record retrieval and editing
    
    function getRecords($startIndex = false, $length = false) {
        $this->updateKeyIndex();
        if ($startIndex === false || $length === false) $res = parent::getRecords($startIndex, $length);
        else {
            $keys = array_slice($this->indexKey, $startIndex, $length);
            $records = $this->dataSource->getMapper()->loadRecordsArray($keys);
            usort($records, array($this, 'compareRecords'));
            $res = $records;
        }
        return $res;
    }
    
    function compareRecords($record1, $record2) {
        $pk1 = $record1->getPrimaryKey();
        if (is_array($pk1)) $pk1 = implode('_', $pk1);
        $pk1 = '_'.$pk1;
        $pk2 = $record2->getPrimaryKey();
        if (is_array($pk2)) $pk2 = implode('_', $pk2);
        $pk2 = '_'.$pk2;
        if (isset($this->keyIndex[$pk1]) && isset($this->keyIndex[$pk2])) {
            $i1 = $this->keyIndex[$pk1];
            $i2 = $this->keyIndex[$pk2];
            $res = $i1 - $i2;
        } else {
            $res = 0;
        }
        return $res;
    }
    
    function getKeyByRecordNumber($number, $returnWholeRecord = false) {
        $this->updateKeyIndex();
        $res = false;
        if (isset($this->indexKey[$number])) {
            if ($returnWholeRecord) $res = $this->dataSource->getMapper()->loadRecord($this->indexKey[$number]);
            else $res = $this->indexKey[$number];
        }
        return $res;
    }
    
    
}