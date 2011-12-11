<?php
class Pmt_Data_Field_List_MultiWithFilter extends Pmt_Data_Field_List {

    /**
     * @var Pmt_Text
     */
    public $filter = false;
    
    /**
     * @var Pmt_Checkbox
     */
    public $cbShowAll = false;
    
    /**
     * @var Pmt_Label
     */
    public $lblShowAll = false;

    protected $tmpFilterText = false;
    
    protected $filterText = null;
    
    protected $showAll = null;
    
    protected $listValues = null;
    
    protected $recordPropertyValue = null;
    
    protected $lockListUpdate = null;
    
    protected $readOnly = null;
    
    protected $idExpr = 'id';

    protected $filterExpr = 'name';
    
    protected function doOnGetControlPrototypes(& $prototypes) {
        parent::doOnGetControlPrototypes($prototypes);
        $p = array(
            'panel' => array(
                'template' => "
                    {label} 
                	<div style='padding-bottom: 0.5em' class='filterContainer'>
                        {filter}
                        {cbShowAll}
                        {lblShowAll}
                    </div>
                    {editor} 
                    {error}
                ",
            ),
            'editor' => array(
                'useCheckboxes' => true,
                'multiple' => true,
                'style' => 'height: 200px; width: 200px; border: 1px solid silver; overflow-y: scroll',
            ),
            
            'filter' => array(
                'displayParentPath' => '../panel',
                'containerIsBlock' => false,
                'size' => 12,
                'class' => 'Pmt_Text',
            ),
            
            'lblShowAll' => array(
                'displayParentPath' => '../panel',
                'containerIsBlock' => false,
                'html' => 'All',
            ),
            
            'cbShowAll' => array(
                'displayParentPath' => '../panel',
                'containerIsBlock' => false,
                'labelControlPath' => '../lblShowAll',
            ),
        );
        Ae_Util::ms($prototypes, $p);
    }

    function setIdExpr($idExpr) {
        if ($idExpr !== ($oldIdExpr = $this->idExpr)) {
            $this->idExpr = $idExpr;
        }
    }

    function getIdExpr() {
        return $this->idExpr;
    }

    function setFilterExpr($filterExpr) {
        if ($filterExpr !== ($oldFilterExpr = $this->filterExpr)) {
            $this->filterExpr = $filterExpr;
        }
    }

    function getFilterExpr() {
        return $this->filterExpr;
    }   
    
    protected function observeDataSource($ds = false) {
        if ($ds === false) $ds = $this->dataSource;
        $ds->observe('onCurrentRecord', $this, 'checkUpdateList');
        $ds->observe('onUpdateRecord', $this, 'checkUpdateList');
        $ds->observe('onReadOnlyStatusChange', $this, 'checkUpdateList');
    }
    
    protected function unobserveDataSource($ds = false) {
        if ($ds === false) $ds = $this->dataSource;
        $ds->unobserve('onCurrentRecord', $this, 'checkUpdateList');
        $ds->unobserve('onUpdateRecord', $this, 'checkUpdateList');
        $ds->unobserve('onReadOnlyStatusChange', $this, 'checkUpdateList');
    }
    
    function setDataSource($dataSource) {
        $changed = ($dataSource !== ($oldDataSource = $this->dataSource)); 
        if ($this->dataSource && $changed) $this->unobserveDataSource();
        parent::setDataSource($dataSource);
        if ($this->dataSource && $changed) $this->observeDataSource();
    }
    
    protected function doAfterControlsCreated() {
        parent::doAfterControlsCreated();
        $this->cbShowAll->observe('change', $this, 'checkUpdateList');
        $this->filter->observe('change', $this, 'checkUpdateList');
        if ($ds = $this->getDataSource()) $this->observeDataSource($ds);
    }
    
    function handleFilterChange() {
        if (strlen($this->filter->getText())) $this->cbShowAll->setChecked(false);
    }
    
    function handleCbShowAllChange() {
        if ($this->cbShowAll->getChecked()) {
            $this->tmpFilterText = $this->filter->getText(); 
            $this->filter->setText('');
        } else {
            if (!strlen($this->filter->getText())) 
                $this->filter->setText($this->tmpFilterText);
        }
    }
    
    function checkUpdateList() {
        $dataSource = $this->getDataSource();
        if ($dataSource && $this->filter && $this->cbShowAll && $this->editor && $this->binder) {
            $modelChanged = false;
            $filterText = $this->filter->getText();
            $readOnly = $dataSource->getReadOnly();
            $showAll = $this->cbShowAll->getChecked() && !$readOnly;
            $listValues = $this->editor->getSelectedValue();
            $recordPropertyValue = $this->binder->getRecordPropertyValue();
            if (!is_array($recordPropertyValue)) $recordPropertyValue = array();
            foreach (array('filterText', 'listValues', 'showAll', 'recordPropertyValue', 'readOnly') as $varName) {
                if ($$varName !== $this->{$varName}) $modelChanged = true;
                $this->{$varName} = $$varName;
            }
            if ($modelChanged) $this->updateList();
        }
    }
    
    function updateList() {
        if (!strlen($this->idExpr)) throw new Exception('idExpr property must be set to non-empty string');
        if (!strlen($this->filterExpr)) throw new Exception('filterExpr property must be set to non-empty string');
        $this->cbShowAll->setDisabled($this->readOnly);
        $this->filter->setDisabled($this->readOnly);
        if (!$this->lockListUpdate) {
            $this->lockListUpdate = true;
            $db = Ae_Dispatcher::getInstance()->database;
            if ($vp = $this->binder->getValuesProvider()) {
                $vp->resetCache();
                $w = array();
                $o = array();
                if ($this->recordPropertyValue) {
                    if (!$this->showAll) $w[] = $this->idExpr.' '.$db->sqlEqCriteria($this->recordPropertyValue);
                    $o[] = 'IF ('.$this->idExpr.' '.$db->sqlEqCriteria($this->recordPropertyValue).', 0, 1) ASC';
                }
                $o[] = $this->filterExpr.' ASC';
                if (!$this->showAll && strlen($this->filterText)) {
                    $w[] = $this->filterExpr.' LIKE '.$db->Quote($this->filterText.'%');
                }
                $vp->where = implode(" OR ", $w); 
                $vp->ordering = implode(", ", $o);
                $this->binder->refreshListFromProvider();
                $this->binder->setControlValue($this->recordPropertyValue);
            }
            $this->lockListUpdate = false;
        }
    }
}
?>