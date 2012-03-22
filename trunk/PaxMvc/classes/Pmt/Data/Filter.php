<?php

class Pmt_Data_Filter extends Pmt_Base {

    protected $holdMode = Pmt_Data_Source::HOLD_NUMBER;
    
    /**
     * @var Pmt_Data_Source
     */
    protected $dataSource = false;

    protected $finderClass = false;

    protected $sqlSelectPrototype = false;

    protected $sqlPartPrototypes = false;
    
    protected $sqlParts = false;
    
    protected $finderDefaults = array();
    
    /**
     * @var Pmt_Finder
     */
    protected $finder = false;

    /**
     * @var bool Whether to automatically apply updates to the datasource when any change is made with setPartValue() or setFinderCriterion() methods  
     */
    protected $autoApply = true;
    
    protected $defaultJoins = false;
    
    protected $defaultOrdering = array();
    
    protected $defaultWhere = array();
    
    protected $defaultHaving = array();
    
    protected $initDefaultsFromDataSource = true;

    /**
     * Aliases to exclude from Ae_Sql_Select joins (in case when they already are in the dataSource extraJoins) 
     * @var array
     */
    protected $excludeAliases = array();
    
    function setAutoApply($autoApply) {
        if ($autoApply !== ($oldAutoApply = $this->autoApply)) {
            $this->autoApply = $autoApply;
            if ($this->autoApply) $this->apply();
        }
    }

    function getAutoApply() {
        return $this->autoApply;
    }    
    
    function apply() {
        $this->refreshDs();
    }
    
    function setExcludeAliases(array $excludeAliases) {
        if ($excludeAliases !== ($oldExcludeAliases = $this->excludeAliases)) {
            $this->excludeAliases = $excludeAliases;
            if ($this->autoApply) $this->refreshDs();
        }
    }

    function getExcludeAliases() {
        return $this->excludeAliases;
    }    
    
    function setFinderCriterion($paramName, $paramValue = null) {
        if (!$f = $this->getFinder()) throw new Exception("Can\t setFinderCriterion() without Finder");
        $f->getCriterion($paramName)->setValue($paramValue); 
        if ($this->autoApply) $this->apply();
    }
    
    function setFinderCriteria(array $critValues, $leaveExistingValues = false) {
        if (!$f = $this->getFinder()) throw new Exception("Can\t setFinderCriteria() without Finder");
        $f->setValues($critValues, $leaveExistingValues); 
        if ($this->autoApply) $this->apply();
    }
    
    function setPartValue($id, $value) {
        $this->getSqlPart($id)->bind($value);
        if ($this->autoApply) $this->apply();
    }
    
    function setPartValues(array $idsAndValues) {
        foreach ($idsAndValues as $id => $value) {
            $this->getSqlPart($id)->bind($value);
        }
        if ($this->autoApply) $this->apply();
    }
    
    protected function refreshDs() {
        if ($this->dataSource) {
            $o =  $this->dataSource->isOpen();
            if ($o) {
                if ($this->holdMode === Pmt_Data_Source::HOLD_NUMBER) {
                    $holdNo = $this->dataSource->getRecordNo();
                } elseif ($this->holdMode === Pmt_Data_Source::HOLD_KEY) {
                    $holdNo = $this->dataSource->getRecordNo();
                    $holdKey = $this->dataSource->getCurrentRecord()? $this->dataSource->getCurrentRecord()->getPrimaryKey() : false;
                }
            }
            $where = $this->defaultWhere;
            $having = $this->defaultHaving;
            $ordering = $this->defaultOrdering;
            $joins = $this->defaultJoins;
            if ($sqs = $this->createSqlSelect()) {
                //$this->dataSource->setAlias($sqs->getEffectivePrimaryAlias());
                if ($joins) {
                    if (!is_array($joins)) $joins = array($joins);
                    $sqs->otherJoins = array_merge($joins, $sqs->otherJoins); 
                }
                if ($where) $sqs->where = array_merge($where, $sqs->where);
                if ($ordering) $sqs->orderBy = array_merge($ordering, $sqs->orderBy);
                $where = $sqs->getWhereClause(false, true);
                $ordering = $sqs->getOrderByClause(false, true);
                $joins = $sqs->getFromClause(false, $this->excludeAliases, false);
                $having = $sqs->getHavingClause(false, true);
            }
            
            $this->dataSource->setWhere($where);
            $this->dataSource->setOrdering($ordering);
            $this->dataSource->setExtraJoins($joins);
            $this->dataSource->setHaving($having);
            if ($o) {
                if ($this->holdMode === Pmt_Data_Source::HOLD_NUMBER) {
                    $this->dataSource->setRecordNo($holdNo);
                } elseif ($this->holdMode === Pmt_Data_Source::HOLD_KEY) {
                    if ($holdKey !== false) {
                        $recNo = $this->dataSource->locateRecordByPrimaryKey($holdKey);
                        if ($recNo === false) $recNo = $holdNo;
                    } else {
                        $recNo = $holdNo;
                    }
                    $this->dataSource->setRecordNo($recNo);                 
                }
                $this->dataSource->open();
            }
        }
    }
    
    function setDataSourcePath($dataSourcePath) {
        $this->associations['dataSource'] = $dataSourcePath;
    }
    
    function setDataSource(Pmt_Data_Source $dataSource = null) {
        if ($dataSource !== ($oldDataSource = $this->dataSource)) {
            $this->dataSource = $dataSource;
            if ($this->initDefaultsFromDataSource) {
                $this->defaultWhere = $this->dataSource->getWhere();
                $this->defaultWhere = $this->dataSource->getHaving();
                $this->defaultJoins = $this->dataSource->getExtraJoins();
                $this->defaultOrdering = $this->dataSource->getOrdering();
            }
            $this->refreshDs();
        }
    }

    /**
     * @return Pmt_Data_Source
     */
    function getDataSource() {
        return $this->dataSource;
    }

    protected function setFinderClass($finderClass) {
        $this->finderClass = $finderClass;
        $this->sqlSelectPrototype = false;
    }

    protected function setSqlSelectPrototype(array $sqlSelectPrototype) {
        $this->sqlSelectPrototype = $sqlSelectPrototype;
        $this->finderClass = false;
    }

    protected function setSqlPartPrototypes(array $sqlPartPrototypes) {
        $this->sqlPartPrototypes = $sqlPartPrototypes;
    }

    function setSqlSelect(Ae_Sql_Select $sqlSelect = null) {
        if ($sqlSelect !== ($oldSqlSelect = $this->sqlSelect)) {
            $this->sqlSelect = $sqlSelect;
        }
    }

    /**
     * @return Ae_Sql_Select
     */
    function createSqlSelect() {
        if ($f = $this->getFinder()) {
            $res = $f->createSqlSelect();
        } elseif ($this->sqlSelectPrototype) {
            $res = new Ae_Sql_Select($this->getApplication()->getDb(), $this->sqlSelectPrototype);
        } else {
            $res = new Ae_Sql_Select($this->getApplication()->getDb(), array(
                'primaryAlias' => $this->dataSource->getAlias(),
                'tables' => array(
                    $this->dataSource->getAlias() => array('tableName' => $this->dataSource->getMapper()->tableName),
                ),
            ));
        }
        if ($res) {
            foreach ($this->listSqlParts() as $i) {
                $this->getSqlPart($i)->applyToSelect($res);
            }
        }
        return $res;
    }
    
    function listSqlParts() {
        if ($this->sqlParts === false) {
            $this->sqlParts = array();
            if (is_array($this->sqlPartPrototypes))
                foreach ($this->sqlPartPrototypes as $id => $proto) {
                    $proto['id'] = $id;
                    $this->sqlParts[$id] = Ae_Sql_Part::factory($proto);
                }
        }
        return array_keys($this->sqlParts);
    }
    
    function addSqlPart(Ae_Sql_Part $part, $key = false) {
        if ($key === false) $key = $part->id; else $part->id = $key;
        if ($key === false) $key = $part->id = 'part'.count($this->listSqlParts);
        if (in_array($key, $this->listSqlParts())) throw new Exception("Part '{$key}' is already in the parts collection");
        $this->sqlParts[$key] = $part;
    }
    
    function removeSqlPart($key) {
        if (!in_array($key, $this->listSqlParts())) throw new Exception("No such sql part: '{$key}'");
        unset($this->sqlParts[$key]);
    }
    
    /**
     * @param string $key
     * @return Ae_Sql_Part
     */
    function getSqlPart($key) {
        if (!in_array($key, $this->listSqlParts())) throw new Exception("No such sql part: '{$key}'");
        return $this->sqlParts[$key];
    }

    function setFinder($finder) {
        if ($finder !== ($oldFinder = $this->finder)) {
            $this->finder = $finder;
        }
    }

    /**
     * @return Pmt_Finder
     */
    function getFinder() {
        if ($this->finder === false) {
            if (strlen($this->finderClass)) $this->finder = Pmt_Autoparams::factory($this->finderDefaults, $this->finderClass);
        }
        return $this->finder;
    }   

    function setFinderDefaults(array $finderDefaults) {
        $this->finderDefaults = $finderDefaults;
    }

    function getFinderDefaults() {
        return $this->finderDefaults;
    }
    
    function setDefaultJoins($defaultJoins = false) {
        if ($defaultJoins !== ($oldDefaultJoins = $this->defaultJoins)) {
            $this->defaultJoins = $defaultJoins;
            $this->refreshDs();
        }
    }

    function getDefaultJoins() {
        return $this->defaultJoins;
    }

    function setDefaultOrdering(array $defaultOrdering = array()) {
        if ($defaultOrdering !== ($oldDefaultOrdering = $this->defaultOrdering)) {
            $this->defaultOrdering = $defaultOrdering;
            $this->refreshDs();
        }
    }

    function getDefaultOrdering() {
        return $this->defaultOrdering;
    }

    function setDefaultWhere(array $defaultWhere = array()) {
        if ($defaultWhere !== ($oldDefaultWhere = $this->defaultWhere)) {
            $this->defaultWhere = $defaultWhere;
            $this->refreshDs();
        }
    }

    function getDefaultWhere() {
        return $this->defaultWhere;
    }    

    function setDefaultHaving(array $defaultHaving = array()) {
        if ($defaultHaving !== ($oldDefaultHaving = $this->defaultHaving)) {
            $this->defaultHaving = $defaultHaving;
            $this->refreshDs();
        }
    }

    function getDefaultHaving() {
        return $this->defaultHaving;
    }    
    
    function setInitDefaultsFromDataSource($initDefaultsFromDataSource) {
        $this->initDefaultsFromDataSource = $initDefaultsFromDataSource;
    }

    function getInitDefaultsFromDataSource() {
        return $this->initDefaultsFromDataSource;
    }
    
    function hasJsObject() {
        return false;
    }
    
    /**
     * @param int $holdMode Pmt_Data_Source::HOLD_NUMBER | Pmt_Data_Source::HOLD_KEY 
     */
    function setHoldMode($holdMode) {
        $this->holdMode = $holdMode;
    }

    function getHoldMode() {
        return $this->holdMode;
    }
        
}

?>