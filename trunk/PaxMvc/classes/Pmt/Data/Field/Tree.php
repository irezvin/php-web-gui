<?php

class Pmt_Data_Field_Tree extends Pmt_Data_Field {

    // +---------------------- visual components ----------------------+ 
    
    /**
     * @var Pmt_Yui_Panel
     */
    protected $popup = false;
    
    /**
     * @var Pmt_Data_Tree
     */
    public $tvNodes = false;
    
    /**
     * Label that displays currently selected node(s) when popup isn't visible
     * @var Pmt_Label
     */
    protected $lblHeader = false;
    
    /**
     * Label that displays currently selected node(s) when popup is visible if $this->withSelectionLabels is TRUE
     * @var Pmt_Label
     */
    protected $lblSelectionLabels = false;
    
    protected $btnOk = false;
    
    protected $btnCancel = false;
    
    protected $btnReset = false;
    
    protected $btnReload = false;

    /**
     * Autocomplete control that is displayed inside a popup for a quick node lookups (when $this->withSearch is TRUE)  
     * @var Pmt_Yui_Autocomplete
     */
    protected $acSearch = false;

    // +--------------------------- features --------------------------+ 
    
    protected $lazyPopup = false;

    protected $multiple = false;
    
    protected $withSearch = true;
    
    protected $withSelectionLabels = true;
    
    protected $withBtnOk = true;
    
    protected $withBtnCancel = true;
    
    protected $withBtnReset = true;

    protected $instantApply = false;

    protected $popupPrototypesOverride = array();
    
    protected $applyOnClose = false;
    
    protected $modelFieldWithNodeId = 'id';
    
    protected $withBtnReload = true;
    
    protected $withClearLink = true;
    
    // +-------------------------- locale data ----------------------------+
    
    protected $lngHeader = '';

    protected $lngSelectionLabel = 'Текущий выбор:';
    
    protected $lngNothingSelected = 'Ничего не выбрано';
    
    protected $lngSearch = 'Поиск:';
    
    protected $lngClear = '[Очистить]';
    
    // +----------------------- runtime properties ------------------------+
    
    protected $popupVisible = false;
    
    // +--------------------------- dependencies --------------------------+

    /**
     * Class of concrete Pmt_I_Tree_Mapper_NestedSets mapper (can be skipped if $this->treeProvider is set) 
     * @var string | FALSE
     */
    protected $mapperClass = false;

    /**
     * Provider of data nodes. Should be set if $this->mapperClass is not.
     * @var Pmt_I_Tree_Provider
     */
    protected $treeProvider = false;
    
    // +--------------------------- internal use --------------------------+
    
    protected $originalValue = false;
    
    protected $value = false;
    
    // +--------------------- features set-up methods ---------------------+    
    
    protected $readOnly = false;    
    
    /**
     * Whether user will be able to select multiple tree items with a checkboxes (instead of one)
     * @param bool $multiple
     */
    protected function setMultiple($multiple) {
        $this->multiple = (bool) $multiple;
        if ($this->multiple && ($this->value === false)) $this->value = array();
    }

    function getMultiple() {
        return $this->multiple;
    }

    /**
     * Whether search autocomplete will be shown to do a quick jump to required node 
     * @param bool $withSearch
     */
    protected function setWithSearch($withSearch) {
        $this->withSearch = (bool) $withSearch;
    }

    function getWithSearch() {
        return $this->withSearch;
    }

    /**
     * Whether user will be able to see clickable label (or several labels if $multiple is true) of selected item(s)
     * @param bool $multiple
     */
    protected function setWithSelectionLabels($withSelectionLabels) {
        $this->withSelectionLabels = (bool) $withSelectionLabels;
    }

    function getWithSelectionLabels() {
        return $this->withSelectionLabels;
    }

    /**
     * Whether to show OK button (that applies current choice and hides the popup) 
     * @param bool $multiple
     */
    protected function setWithBtnOk($withBtnOk) {
        $this->withBtnOk = (bool) $withBtnOk;
    }

    function getWithBtnOk() {
        return $this->withBtnOk;
    }

    /**
     * Whether to show Cancel button (that reverts current choice and hides the popup) 
     * @param bool $multiple
     */
    protected function setWithBtnCancel($withBtnCancel) {
        $this->withBtnCancel = (bool) $withBtnCancel;
    }

    function getWithBtnCancel() {
        return $this->withBtnCancel;
    }

    /**
     * Whether to show Reset button (that reverts current choice but leaves the popup remaining) 
     * @param bool $multiple
     */
    protected function setWithBtnReset($withBtnReset) {
        $this->withBtnReset = (bool) $withBtnReset;
    }

    function getWithBtnReset() {
        return $this->withBtnReset;
    }

    /**
     * Whether a change of selection will instantly trigger onChange event
     * @param bool $instantApply
     */
    function setInstantApply($instantApply) {
        $this->instantApply = (bool) $instantApply;
    }

    function getInstantApply() {
        return $this->instantApply;
    }
    
    /**
     * Whether a pop-up will be created immediately before first display
     * @param bool $lazyPopup
     */
    protected function setLazyPopup($lazyPopup) {
        $this->lazyPopup = (bool) $lazyPopup;
    }

    function getLazyPopup() {
        return $this->lazyPopup;
    }
    
    /**
     * Overrides for controls that are created with the popup. If $this->lazyPopup set to FALSE, controls are created at Pmt_Data_Field_Tree 
     * initialization time and using $popupPrototypesOverride doesn't make much sense; but when $this->lazyPopup is TRUE, it's the only legal way to
     * provide prototypes for controls that should be created with the popup.
     * 
     * @param array $popupPrototypesOverride
     */
    protected function setPopupPrototypesOverride(array $popupPrototypesOverride) {
        $this->popupPrototypesOverride = $popupPrototypesOverride;
    }

    function getPopupPrototypesOverride() {
        return $this->popupPrototypesOverride;
    }    
    
    function setApplyOnClose($applyOnClose) {
        $this->applyOnClose = $applyOnClose;
    }

    function getApplyOnClose() {
        return $this->applyOnClose;
    }
    
    /**
     * Name of the field in the search result objects that contains ID of tree node to perform the lookup 
     * @param string $modelFieldWithNodeId
     */
    protected function setModelFieldWithNodeId($modelFieldWithNodeId) {
        $this->modelFieldWithNodeId = $modelFieldWithNodeId;
    }

    function getModelFieldWithNodeId() {
        return $this->modelFieldWithNodeId;
    }

    protected function setWithBtnReload($withBtnReload) {
        $this->withBtnReload = (bool) $withBtnReload;
    }

    function getWithBtnReload() {
        return $this->withBtnReload;
    }   
    
    protected function setWithClearLink($withClearLink) {
        $this->withClearLink = $withClearLink;
    }

    function getWithClearLink() {
        return $this->withClearLink;
    }    
    
    // +----------------- locale set-up methods --------------------------+
    
    protected function setLngHeader($lngHeader) {
        $this->lngHeader = $lngHeader;
    }

    function getLngHeader() {
        return $this->lngHeader;
    }

    protected function setLngSelectionLabel($lngSelectionLabel) {
        $this->lngSelectionLabel = $lngSelectionLabel;
    }

    function getLngSelectionLabel() {
        return $this->lngSelectionLabel;
    }

    protected function setLngSearch($lngSearch) {
        $this->lngSearch = $lngSearch;
    }

    function getLngSearch() {
        return $this->lngSearch;
    }
    
    protected function setLngNothingSelected($lngNothingSelected) {
        $this->lngNothingSelected = $lngNothingSelected;
    }

    function getLngNothingSelected() {
        return $this->lngNothingSelected;
    }    

    protected function setLngClear($lngClear) {
        $this->lngClear = $lngClear;
    }

    function getLngClear() {
        return $this->lngClear;
    }   
        
    // +--------------- dependency set-up methods ------------------------+
    
    protected function setMapperClass($mapperClass) {
        $this->mapperClass = $mapperClass;
        if (strlen($mapperClass)) {
            $mapper = $this->getApplication()->getMapper($mapperClass);
            if ($mapper instanceof Pmt_I_Tree_Mapper)
                $this->setTreeProvider($mapper->createTreeProvider());
        }
    }

    function getMapperClass() {
        return $this->mapperClass;
    }

    protected function setTreeProvider(Pmt_I_Tree_Provider $treeProvider) {
        $this->treeProvider = $treeProvider;
    }

    function getTreeProvider() {
        return $this->treeProvider;
    }    
    
    // +-------------------------- data methods --------------------------+
    
    function getValue() {
        if ($this->popupVisible) {
            if ($this->multiple) {
                $res = $this->tvNodes->getCheckedIds();
            } else {
                $res = false;
                if ($currentNode = $this->tvNodes->getCurrentNode()) $res = $currentNode->getNodeId();
            }
        } else {
            $res = $this->value;
        }
        return $res;
    }
    
    function setValue($value) {
        $this->value = $value;
        if ($this->popupVisible) {
            if ($this->multiple) {
                if (!is_array($value)) {
                    if (is_null($value) || ($value === false)) $value = array();
                        else $value = array($value);
                }
                $this->tvNodes->setCheckedNodes($value);
            } else {
                if (is_null($value) || ($value === false)) $this->tvNodes->setCurrentNode(null);
                    else $this->tvNodes->setCurrentNode($value, true);
            }
            if ($this->lblSelectionLabels) $this->lblSelectionLabels->setHtml($this->getNodeLabels($value));
            if ($this->popup instanceof Pmt_Yui_Panel) $this->popup->applyAutoSize();
        } else {
        }
        if ($this->lblHeader) {
            $this->lblHeader->setHtml($this->getNodeLabels($value, true));
        }
    }
    
    // -+----------------------- runtime methods -------------------------+
    
    function setPopupVisible($popupVisible) {
        if ($popupVisible !== ($oldPopupVisible = $this->popupVisible)) {
            $this->popupVisible = $popupVisible;
            if (!$this->popup && is_array($this->controls)) {
                $this->createPopupControls();
            }
            if ($this->popup) {
                $this->popup->setVisible($this->popupVisible);
                if ($this->popupVisible) {
                    $this->popup->focus();
                    $this->popup->setContext($this->popup->getContext());
                    $this->reload();
                    $this->originalValue = $this->getValue();
                    if ($this->lblSelectionLabels) {
                        $this->lblSelectionLabels->setHtml($this->getNodeLabels($this->originalValue));
                        if ($this->popup instanceof Pmt_Yui_Panel) $this->popup->applyAutoSize();
                    }
                }
            } 
        }
    }

    function getPopupVisible() {
        return $this->popupVisible;
    }
    
    function reload() {
        $val = $this->value;
        if ($this->tvNodes) {
            $this->tvNodes->clear();
            $this->tvNodes->showNodes($this->treeProvider->listTopNodes());
            $this->setValue($val);
        }
    }    
    
    // +--------------------- implementation methods ---------------------+
    
    protected function doOnInitialize(array $options) {
        parent::doOnInitialize($options);
        if (!$this->treeProvider)
            throw new Exception("treeProvider must be set (either with treeProvider or with an appropriate mapperClass parameter)");
    }

    protected function doOnGetControlPrototypes(& $prototypes) {
        parent::doOnGetControlPrototypes($prototypes);
        unset($prototypes['editor']); // we don't need it
        Ae_Util::ms($prototypes, array(
            'lblHeader' => array(
                '.click' => 'nodesLabelClick',
                'displayParentPath' => '../panel',
                'html' => $this->getNodeLabels($this->value, true),
            ),
            'binder' => array(
                'dataControlPath' => '..',
                'dataPropertyName' => 'value',
                'readOnlyPropertyName' => 'readOnly',
                'controlChangeEvents' => array('apply'),
            ),
        ));
        if (!$this->lazyPopup) Ae_Util::ms($prototypes, $this->getPopupControlPrototypes());
    }
    
    protected function getControlPrototypes() {
    	$res = parent::getControlPrototypes();
    	$res['panel']['template'] = str_replace('{editor}', '{lblHeader}', $res['panel']['template']);
    	return $res;
    }
    
    protected function getPopupControlPrototypes() {
        
        $templateParts = array();
        
        if ($this->withSelectionLabels) { 
            $templateParts [] = $this->lngSelectionLabel . '{lblSelectionLabels}';
        }
        
        if ($this->withSearch) {
            $templateParts [] = $this->lngSearch . '{acSearch}';
        }
        
        $templateParts[] = '{tvNodes}';
        
        $btns = array();
        if ($this->withBtnOk) $btns[] = '{btnOk}';
        if ($this->withBtnCancel) $btns[] = '{btnCancel}';
        if ($this->withBtnReset) $btns[] = '{btnReset}';
        if ($this->withBtnReload) $btns[] = '{btnReload}';
        
        if (count($btns)) $templateParts[] = implode (' ', $btns);
        $template = '<div class="treeFieldPart">'.implode('</div>'."\n".'<div class="treeFieldPart">', $templateParts).'</div>';
            
        $res = array(
            'popup' => array(
                'class' => 'Pmt_Yui_Panel',
                'closeOnOutsideClick' => true,
                'context' => array(new Pmt_Control_Path('../lblHeader'), 'tl', 'tl'),
                'visible' => $this->popupVisible,
                'width' => 400,
                'header' => $this->lngHeader,
                //'height' => 325,              
            ),
            'pnlPopup' => array(
                'displayParentPath' => '../popup',
                'template' => $template,
            ),
            'lblSelectionLabels' => array(
                'displayParentPath' => '../pnlPopup',
                '.click' => 'nodesLabelClick',
            ),
            'acSearch' => array(
                'class' => 'Pmt_Yui_AutoComplete',
                'displayParentPath' => '../pnlPopup',
                'containerIsBlock' => false,
                'dataSourceProperties' => array(
                    'responseSchema' => array('fields' => array('value', 'label')),
                ),
                'labelKey' => 'label',
                'textKey' => 'value',
                'size' => 40,
            ),
            'tvNodes' => array(
                'class' => 'Pmt_Data_Tree',
                'displayParentPath' => '../pnlPopup',
                'treeProvider' => $this->treeProvider,
                'withCheckboxes' => $this->multiple,
                'containerAttribs' => array(
                    'style' => array(
                        'height' => '200px',
                        //'width' => '250px',
                        'background-color' => 'white',
                        'border' => '1px solid silver',
                        'overflow' => 'scroll',
                    ),
                ),
                'lazyLoad' => true,
                'setCurrentNodeOnClick' => true,
                'alwaysLoadChildrenOnExpand' => true,
                
                '.currentNodeChange' => 'treeSelectionChange',
                '.childCheckedChange' => 'treeSelectionChange',
                '.childBranchToggle' => 'treeSelectionChange',
            ),
            'btnOk' => array(
                'label' => 'Ок',
                'containerIsBlock' => false,
                'displayParentPath' => '../pnlPopup',
            ),
            'btnCancel' => array(
                'label' => 'Отмена',
                'containerIsBlock' => false,
                'displayParentPath' => '../pnlPopup',
            ),
            'btnReset' => array(
                'label' => 'Сброс',
                'containerIsBlock' => false,
                'displayParentPath' => '../pnlPopup',
            ),
            'btnReload' => array(
                'label' => 'Обновить дерево',
                'containerIsBlock' => false,
                'displayParentPath' => '../pnlPopup',
            ),
        );
        
        if (!$this->withSelectionLabels) unset($res['lblSelectionLabels']);
        if (!$this->withSearch) unset($res['acSearch']);
        if (!$this->withBtnOk) unset($res['btnOk']);
        if (!$this->withBtnCancel) unset($res['btnCancel']);
        if (!$this->withBtnReset) unset($res['btnReset']);
        if (!$this->withBtnReload) unset($res['btnReload']);
        
        Ae_Util::ms($res, $this->popupPrototypesOverride);
        return $res;
    }
    
    protected function getNodeLabels($nodeIds, $putEmptyPlaceholderIntoTheLink = false) {
        if (!is_array($nodeIds)) {
            if ($nodeIds === false || $nodeIds === null) $nodeIds = array();
            else $nodeIds = array($nodeIds);
        }
        $labels = array();
        foreach ($nodeIds as $id) {
            if ($node = $this->treeProvider->getNode($id, true)) {
                if ($this->readOnly) $labels[] = $node->getTitle();
                    else $labels[] = '<a href="##node-'.$node->getNodeId().'">'.$node->getTitle().'</a>';
            }
        }
        if (count($labels) && !$this->readOnly) {
            $res = implode('; ', $labels);
            if ($this->withClearLink) {
                $res .= " <a href='##-clear-'>{$this->lngClear}</a>";
            }
        } else {
                $res = $this->lngNothingSelected;
                if ($putEmptyPlaceholderIntoTheLink && !$this->readOnly) $res = "<a href='##'>{$res}</a>";
            }
        
        return $res;
    }
    
    protected function createPopupControls() {
        if (!$this->popup && is_array($this->controls)) {
            $cc = array();
            foreach ($this->getPopupControlPrototypes() as $id => $proto) {
                $cc[] = $this->createControl($proto, is_numeric($id)? false : $id);
            }
            $this->resolveAssociations();
            foreach ($cc as $c) $c->initializeFront();
        }
    }
    
    protected function apply($value = false) {
        if (!func_num_args()) $value = $this->getValue();
        if ($this->lblHeader) $this->lblHeader->setHtml($this->getNodeLabels($value, true));
        $this->triggerEvent('apply', array('value' => $value));
    }
    
    protected function reset($instantApply = null) {
        if (is_null($instantApply)) $instantApply = $this->instantApply; 
        $this->setValue($this->originalValue);
        if ($instantApply) $this->apply($this->originalValue);
    }

    protected function findTreeItems($substring, $limit = null) {
        if (is_null($limit)) {
            if ($this->acSearch) {
                $acc = $this->acSearch->getAutoCompleteConfig();
                if (isset($acc['maxResultsDisplayed'])) $limit = $acc['maxResultsDisplayed'];
                    else $limit = 10; 
            } else $limit = false;
        }
        $results = false;
        $this->triggerEvent('findTreeItems', array('substring' => $substring, 'limit' => $limit, 'results' => & $results));
        
        if (!is_array($results)) { // handler didn't done anything useful
            $results = $this->defaultFindTreeItems($substring, $limit);
        }
        
        return $results;
    }
    
    protected function defaultFindTreeItems($substring, $limit) {
        $results = array();
        $mapper = false;
        if ($this->mapperClass) $mapper = $this->getApplication()->getMapper($this->mapperClass);
        if ($mapper && strlen($titleFieldName = $mapper->getTitleFieldName())) {
            $db =  & $mapper->database;
            if (strlen($substring)) {
                $p = $substring.'%';
                $crit = $titleFieldName.' LIKE '.$db->Quote($p);
            } else {
                $crit = '1';
            }
            $results = $mapper->loadRecordsByCriteria($crit, false, $titleFieldName.' ASC', '', 0, $limit);
        } 
        return $results;
    }
    
    protected function formatSearchResults (array $results, $substring) {
        $items = false;
        $this->triggerEvent('formatSearchResults', array('results' => $results, 'listItems' => & $items, 'substring' => $substring));
        if (!is_array($items)) $items = $this->defaultFormatSearchResults($results);
        return $items;
    }
    
    protected function defaultFormatSearchResults (array $results) {
        $items = array();
        $m = false;
        if ($this->mapperClass) $m = $this->getApplication()->getMapper($this->mapperClass);
        if ($m && strlen($tf = $m->getTitleFieldName())) {
            foreach ($results as $result)
            $items[] = array('value' => $result->$tf, 'label' => $result->$tf);
        } else {
            $items[] = array('value' => '', 'label' => 'Cannot format search result');
        }
        return $items;
    }
    
    protected function getNodeIdFromLabelClick(array $params) {
        if (isset($params['href']) && strlen($params['href']) && (substr($params['href'], 0, 7) === '##node-')) {
            $res = intval(substr($params['href'], 7));
        } else {
            $res = false;
        }
        return $res;
    }
    
    // +------------------------- event handlers -------------------------+
    
    function handleBtnOkClick() {
        $this->apply();
        if ($this->popup) $this->setPopupVisible(false);
    }
    
    function handleBtnCancelClick() {
        $this->reset(true);
        if ($this->popup) $this->setPopupVisible(false);
    }
    
    function handleBtnResetClick() {
        $this->reset();
    }
    
    function handlePopupClose() {
        if ($this->applyOnClose) $this->apply();
        $this->setPopupVisible(false);
    }
        
    function handleAcSearchDataRequest(Pmt_Yui_Autocomplete $acSearch, $eventType, array $params) {
        $records = $this->findTreeItems($params['request']);
        $resp = $this->formatSearchResults($this->findTreeItems(trim($params['request'])), trim($params['request']));
        $acSearch->setResponse($resp);
    }
    
    function nodesLabelClick(Pmt_Label $label, $eventType, array $params) {
        
        if (!$this->readOnly)  {
            if (isset($params['href']) && $params['href'] == '##-clear-') {
                $this->setValue($this->multiple? array() : $this->emptyValue);
                if ($label === $this->lblHeader) $this->apply();
            } else {
                if (!$this->readOnly) {
                    if ($label === $this->lblHeader) 
                        if (!$this->popupVisible) $this->setPopupVisible(true);
                        elseif ($this->popup) $this->popup->focus();

                    if ($this->tvNodes && ($id = $this->getNodeIdFromLabelClick($params))) {
                        $this->tvNodes->showNodes($id);
                        if ($tn = $this->tvNodes->findTreeNodeByDataNode($id)) $tn->scrollIntoView();
                    }
                }
            }            
        }
    }
    
    function handleAcSearchItemSelected(Pmt_Yui_Autocomplete $acSearch, $eventType, array $params) {
        if (strlen($txt = trim($params['text'])) && strlen($mf = $this->modelFieldWithNodeId)) {
            $records = array_values($this->findTreeItems($txt, 1));
            if (count($records)) {
                $id = $records[0]->$mf;
                $this->tvNodes->showNodes($id);
                if ($treeNode = $this->tvNodes->findTreeNodeByDataNode($id)) $treeNode->scrollIntoView();
                $this->tvNodes->setCurrentNode($id);
            }
        }
    }
    
    function treeSelectionChange(Pmt_Data_Tree $treeView, $eventType, $params) {
        if (!$this->readOnly) {
            $this->value = $this->getValue();
            if ($this->lblSelectionLabels) {
                $this->lblSelectionLabels->setHtml($this->getNodeLabels($this->value));
                if ($this->popup instanceof Pmt_Yui_Panel) $this->popup->applyAutoSize();
            }
            if ($this->instantApply) $this->apply();
        }
    }
    
    function handleBtnReloadClick() {
        $this->reload();
    }
    
    function setReadOnly($readOnly) {
        if ($readOnly !== ($oldReadOnly = $this->readOnly)) {
            $this->readOnly = $readOnly;
              if ($this->lblSelectionLabels) {
                $this->lblSelectionLabels->setHtml($this->getNodeLabels($this->originalValue));
            }
        }
    }

    function getReadOnly() {
        return $this->readOnly;
  }    
    
}

?>
