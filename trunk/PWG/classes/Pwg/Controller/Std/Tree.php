<?php

class Pwg_Controller_Std_Tree extends Pwg_Controller_MDI_Window implements Pwg_I_RecordTree {
    
    protected $mapperClass = false;
    
    /**
     * Whether dnNavigator will contain create/delete/edit/cancel buttons
     * @var bool
     */
    protected $editInForm = false;
    
    protected $parentIdPropName = false;
    
    protected $searchTool = false;
    
    /**
     * @var Pwg_Data_Tree
     */
    protected $tvTree = false;
    
    /**
     * @var Pwg_Yui_AutoComplete
     */
    protected $acSearch = false;
    
    /**
     * @var Pwg_Yui_Tree_Node
     */
    protected $currNode = false;
    
    /**
     * @var Pwg_Data_Source
     */
    protected $dsData = false;
    
    /**
     * @var Pwg_Button
     */
    protected $btnNewSibling = false;
    
    /**
     * @var Pwg_Button
     */
    protected $btnNewChild = false;
    
    protected $lockCurrentNodeSwitch = false;
    
    protected $mapper = false;

    /**
     * @return Ac_Model_Mapper
     */
    function getMapper() {
        if ($this->mapper === false) {
            if (!strlen($this->mapperClass)) throw new Exception("\$mapperClass not provided");
            $this->mapper = $this->getApplication()->getMapper($this->mapperClass);
            if ($this->mapper instanceof Pwg_I_Tree_Mapper) {
                if (!($this->mapper instanceof Pwg_I_Tree_Mapper_AdjacencyList || $this->mapper instanceof Pwg_I_Tree_Mapper_NestedSets))
                    throw new Exception("Currently Pwg_Controller_Std_Tree supports only mappers that implement "
                        ."Pwg_I_Tree_Mapper_AdjacencyList or Pwg_I_Tree_Mapper_NestedSets interfaces");
            } else {
                 throw new Exception("{$this->mapperClass} doesn't implement Pwg_I_Tree_Mapper");
            }
        }
        return $this->mapper;
    }

    /**
     * @return Pwg_I_Tree_SearchTool
     */
    function getSearchTool() {
        if (is_object($this->searchTool) && !($this->searchTool instanceof Pwg_I_Tree_SearchTool))
            throw new Exception("\$searchTool must implement Pwg_I_Tree_SearchTool, but ".get_class($this->searchTool)." doesn't");
        else {
            if (is_array($this->searchTool))
                $this->searchTool = Pwg_Base::factory($this->searchTool, 'Pwg_I_Tree_SearchTool');
            elseif ($this->getMapper() instanceof Pwg_I_Tree_SearchTool) {
                $this->searchTool = $this->getMapper();
            }
        }
        return $this->searchTool;           
    }
    
    protected function setSearchTool($searchTool) {
        if ($searchTool) $this->searchTool = $searchTool;
    }
    
    protected function doOnGetControlPrototypes(& $prototypes) {
        parent::doOnGetControlPrototypes($prototypes);
        Ac_Util::ms($prototypes, array(
            'pnlLayout' => array(
                'template' => "
                    <table cols='2'>
                        <tr>
                            <td colspan='2' style='padding: 0.5em; vertical-align: top'>
                                {dnNavigator}
                            </td>
                        </tr>
                        <tr>
                            <td style='padding: 0.5em; vertical-align: top'>
                                {pnlSearch}
                            </td>
                        </tr>
                        <tr>
                            <td style='padding: 0.5em; vertical-align: top'>
                                {tvTree}
                            </td>
                            <td style='padding: 0.5em; vertical-align: top'>
                                {pnlDetails}
                                <br />
                                {btnNewSibling} {btnNewChild}
                            </td>
                        </tr>
                    </table>
                "
            ),
            'pnlSearch' => array(
                'template' => '{lng:search}: {acSearch}',
                'visible' => false,
                'displayParentPath' => '../pnlLayout',
            ),
            'pnlDetails' => array(
                'displayParentPath' => '../pnlLayout',
            ),
            'acSearch' => array(
                'containerIsBlock' => false,
                'class' => 'Pwg_Yui_AutoComplete',
                'displayParentPath' => '../pnlSearch',
                'dataSourceProperties' => array(
                    'responseSchema' => array('fields' => array('value', 'label')),
                ),
                'labelKey' => 'label',
                'textKey' => 'value',
                'size' => 40,
            ),
            'tvTree' => array(
                'class' => 'Pwg_Data_Tree',
                'displayParentPath' => '../pnlLayout', 
                'containerAttribs' => array(
                    'style' => 'height: 400px; width: 400px; background-color: white; border: 1px solid silver; overflow: scroll;'
                ),
                'lazyLoad' => true,
                'setCurrentNodeOnClick' => true,
                'alwaysLoadChildrenOnExpand' => true,
            ),
            'dsData' => array(
                'mapperClass' => $this->mapperClass,
                '.onCurrentRecord' => 'updateEditButtons',
                '.onUpdateRecord' => 'updateEditButtons',
                '.onStoreRecord' => array('buildTree', 'updateEditButtons'), 
                '.onDeleteRecord' => array('buildTree', 'updateEditButtons'),
            ),
            'dnNavigator' => array(
                'displayParentPath' => '../pnlLayout',
                'dataSourcePath' => '../dsData',
                'hasBtnFirst' => false,
                'hasBtnNext' => false,
                'hasBtnPrev' => false,
                'hasBtnLast' => false,
                'hasBtnNew' => $this->editInForm,
                'hasBtnSave' => $this->editInForm,
                'hasBtnCancel' => $this->editInForm,
                'hasBtnReload' => true,
                'deleteConfirmation' => new Pwg_Lang_String('deleteRecordConfirmation'),
            ),
            'btnNewSibling' => array(
                'label' => 'Новая запись (сосед)',
                'displayParentPath' => '../pnlLayout',
                'containerIsBlock' => false,
            ),
            'btnNewChild' => array(
                'label' => 'Новая запись (потомок)',
                'displayParentPath' => '../pnlLayout',
                'containerIsBlock' => false,
            ),
        ));
        $mapper = $this->getMapper();
        if ($mapper instanceof Pwg_I_Tree_Mapper_NestedSets) {
            $ns = $mapper->getNestedSets();
            Ac_Util::ms($prototypes, array(
                'dsData' => array(
                    'extraJoins' => array($ns->getJoinClause(array('t', 'id'))),
                    'ordering' => array($ns->getOrderByPart()),
                )
            ));
        } elseif ($mapper instanceof Pwg_I_Tree_Mapper_AdjacencyList) {
            Ac_Util::ms($prototypes, array(
                'dsData' => array(
                    'ordering' => array($mapper->database->NameQuote($mapper->getNodeOrderField())),
                )
            ));
        }
        if ($this->getSearchTool()) $prototypes['pnlSearch']['visible'] = true;
    }
    
    function buildTree() {
        $this->tvTree->clear();
        $this->tvTree->getTreeProvider()->destroyAllNodes();
        $this->tvTree->showNodes($this->getMapper()->listTopNodes());
        if (($rec = $this->dsData->getCurrentRecord()) && $rec->hasFullPrimaryKey()) {
            $this->tvTree->setCurrentNode($rec->getPrimaryKey(), true);
        }
    }

    protected function doAfterControlsCreated() {
        parent::doAfterControlsCreated();
        $this->tvTree->setTreeProvider($this->getMapper()->createTreeProvider());
        $this->buildTree();
        $this->dsData->open();
    }
    
    function handleDnNavigator__BtnReloadClick() {
        $this->buildTree();
    }
    
    function updateEditButtons() {
        $sibEnabled = false;
        $childEnabled = false;
        if ($this->dsData && $this->dsData->canCreate() && ($rec = $this->dsData->getCurrentRecord()) && ($rec->hasFullPrimaryKey())) {
            if (!$this->lockCurrentNodeSwitch) {
                $this->lockCurrentNodeSwitch = true;
                $this->tvTree->setCurrentNode($rec->getPrimaryKey(), true);
                $this->lockCurrentNodeSwitch = false;
            }
            $sibEnabled = true;
            $childEnabled = true;
        }
        $this->btnNewChild->setDisabled(!$childEnabled);
        $this->btnNewSibling->setDisabled(!$sibEnabled);
    }
    
    protected function doOnNewChild($primaryKey, Ac_Model_Object $currentRecord = null) {
        
    }
    
    protected function doOnNewSibling($primaryKey, Ac_Model_Object $currentRecord = null) {
        
    }
    
    function handleBtnNewChildClick() {
        if ($this->dsData && $this->dsData->canCreate() && ($rec = $this->dsData->getCurrentRecord()) && ($rec->hasFullPrimaryKey())) {
            if ($this->doOnNewChild($rec->getPrimaryKey(), $rec)) {
                $this->triggerEvent(self::evtCreateRecordChild, array('primaryKey' => $rec->getPrimaryKey(), 'record' => $rec, 'mapperClass' => $this->mapperClass));
            }
        }
    }   
    
    function handleBtnNewSiblingClick() {
        if ($this->dsData && $this->dsData->canCreate() && ($rec = $this->dsData->getCurrentRecord()) && ($rec->hasFullPrimaryKey())) {
            if ($this->doOnNewSibling($rec->getPrimaryKey(), $rec)) {
                $this->triggerEvent(self::evtCreateRecordSibling, array('primaryKey' => $rec->getPrimaryKey(), 'record' => $rec, 'mapperClass' => $this->mapperClass));
            }
        }
    }

    function handleDsDataOnCurrentRecord() {
        if (($b = $this->getControl('btnOpenDetails')))
            $b->setDisabled(! $this->dsData->getCurrentRecord());
    }
    
    function handleDsDataOnDeleteRecord($dataSource, $eventType, $params) {
        $this->triggerEvent(Pwg_I_RecordList::evtDeleteRecord, $params);
    }
    
    function handleBtnOpenDetailsClick() {
        if ($r = $this->dsData->getCurrentRecord()) {
            $this->triggerEvent(Pwg_I_RecordList::evtOpenDetails, array('primaryKey' => $r->getPrimaryKey(), 'record' => $r, 'mapperClass' => $this->getMapperClass()));
        }
    }
    
    function handleBtnCreateClick() {
        $this->triggerEvent(Pwg_I_RecordList::evtCreateRecord, array('mapperClass' => $this->getMapperClass()));
    }   
    
    function handleTvTreeCurrentNodeChange(Pwg_Data_Tree $tree, $eventType, array $params) {
        if ($this->lockCurrentNodeSwitch) return;
        $this->lockCurrentNodeSwitch = true;
        if (isset($params['currentNode']) && $params['currentNode']) {
            $id = $params['currentNode']->getNodeId();
            Pwg_Conversation::log("Lets switch to node #", $id, "current id is ", $this->dsData->getCurrentRecord()->getPrimaryKey());
            if (!($rec = $this->dsData->getCurrentRecord()) || ($rec->getPrimaryKey() !== $id)) {
                $this->dsData->locateRecordByPrimaryKey($id, true);
            }
        }
        $this->lockCurrentNodeSwitch = false;
    }
    
    function handleAcSearchDataRequest(Pwg_Yui_Autocomplete $acSearch, $eventType, array $params) {
        if ($t = $this->getSearchTool()) return $t->handleAutocompleteDataRequest($acSearch, $params);
    }
    
    function handleAcSearchItemSelected(Pwg_Yui_Autocomplete $acSearch, $eventType, array $params) {
        if ($t = $this->getSearchTool()) {
            $id = $t->handleAutocompleteItemSelected($acSearch, $params);
            if ($id !== false) $this->tvTree->setCurrentNode($id, true);
        }
    }
    
    function handleTvTreeOnCreateTreeNode($tree, $eventType, $params) {
        if ($t = $this->getSearchTool()) $t->handleOnCreateTreeNode($tree, $params);
    }
    
    protected function setEditInForm($editInForm) {
        $this->editInForm = $editInForm;
    }

    function getEditInForm() {
        return $this->editInForm;
    }    
     
}