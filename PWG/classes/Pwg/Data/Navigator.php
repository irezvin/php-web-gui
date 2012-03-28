<?php

class Pwg_Data_Navigator extends Pwg_Controller {

    const evtNavButtonClick = 'navButtonClick';
    
    protected $allowPassthroughEvents = true;
    
    /**
     * @var Pwg_Data_Source
     */
    protected $dataSource = false;
    
    /**
     * @var Pwg_Button
     */
    protected $btnNext = false;
    
    /**
     * @var Pwg_Button
     */
    protected $btnPrev = false;
    
    /**
     * @var Pwg_Button
     */
    protected $btnFirst = false;
    
    /**
     * @var Pwg_Button
     */
    protected $btnLast = false;
    
    /**
     * @var Pwg_Button
     */
    protected $btnNew = false;
    
    /**
     * @var Pwg_Button
     */
    protected $btnSave = false;
    
    /**
     * @var Pwg_Button
     */
    protected $btnCancel = false;
    
    /**
     * @var Pwg_Button
     */
    protected $btnDelete = false;
    
    /**
     * @var Pwg_Text
     */
    protected $txtRecordNo = false;

    /**
     * @var Pwg_Text
     */
    protected $txtRecordsCount = false;

    protected $lockRecordNo = false;

    protected $hasBtnFirst = true;

    protected $hasBtnLast = true;

    protected $hasBtnPrev = true;
    
    protected $hasBtnNext = true;

    protected $hasBtnReload = false;
    
    protected $hasBtnEdit = true;

    protected $hasBtnCancel = true;

    protected $hasBtnSave = true;

    protected $hasBtnDelete = true;

    protected $hasIndexDisplay = true;
    
    protected $hasBtnNew = true;    
    
    protected $deleteConfirmation = false;
    
    /**
     * @return Pwg_Button
     */
    function getBtnNext() { return $this->getControl('btnNext'); }

    /**
     * @return Pwg_Button
     */
    function getBtnPrev() { return $this->getControl('btnPrev'); }
    
    /**
     * @return Pwg_Button
     */
    function getBtnFirst() { return $this->getControl('btnFirst'); }
    
    /**
     * @return Pwg_Button
     */
    function getBtnLast() { return $this->getControl('btnLast'); }
    
    /**
     * @return Pwg_Button
     */
    function getBtnReload() { return $this->getControl('btnReload'); }
    
    /**
     * @return Pwg_Button
     */
    function getBtnSave() { return $this->getControl('btnSave'); }
    
    /**
     * @return Pwg_Button
     */
    function getBtnCancel() { return $this->getControl('btnCancel'); }
    
    /**
     * @return Pwg_Button
     */
    function getBtnDelete() { return $this->getControl('btnDelete'); }
    
    /**
     * @return Pwg_Text
     */
    function getTxtRecordNo() { return $this->getControl('txtRecordNo'); }
    
    /**
     * @return Pwg_Text
     */
    function getTxtRecordsCount() { return $this->getControl('txtRecordsCount'); }
    
    /**
     * @return Pwg_Data_Source
     */
    function getDataSource() {
        return $this->dataSource;
    }
    
    function setDataSource(Pwg_Data_Source $source) {
        $this->dataSource = $source;
        $this->dataSource->observe('onCurrentRecord', $this, 'handleCurrentRecord');
        $this->dataSource->observe('onUpdateRecord', $this, 'handleCurrentRecord');
        $this->dataSource->observe('onReadOnlyStatusChange', $this, 'handleCurrentRecord');
    }

    function setDataSourcePath($dataSourcePath) {
        $this->associations['dataSource'] = $dataSourcePath;
    }
    
    function hasContainer() {
        return true;
    }
    
    protected function doGetContainerBody() {
        ob_start();
?>
<?php   if ($this->hasIndexDisplay) { ?> 
                <span class='pos'><?php echo new Pwg_Lang_String('navigator_record_no', 'Record # '); ?><?php $this->getControl('txtRecordNo')->showContainer(); ?></span>  
                <span class='count'><?php echo new Pwg_Lang_String('navigator_record_of', 'of '); ?><?php $this->getControl('txtRecordsCount')->showContainer(); ?></span>
<?php   } ?>                 
                <span class='buttons'>
<?php       foreach ($this->getNavButtonNames() as $n) if ($c = $this->getControl($n)) { ?>
                <?php $c->showContainer(); ?>  
<?php       } ?>
                </span>
<?php
        return ob_get_clean();
    }
    
    protected function getNavButtonNames() {
        return array('btnFirst', 'btnPrev', 'btnNext', 'btnLast', 'btnReload', 'btnNew', 'btnSave', 'btnCancel', 'btnDelete');
    } 
    
    protected function doOnInitialize(array $options) {
//      $this->createDisplayParentImpl();
        parent::doOnInitialize($options);
        $proto = $this->getDefaultPrototype();
        $this->controlPrototypes = Ae_Util::m($proto, $this->controlPrototypes);

        
        foreach ($this->getNavButtonNames() as $n) {
            $hasProp = 'has'.ucfirst($n);
            if (isset($this->{$hasProp}) && !$this->{$hasProp}) unset($this->controlPrototypes[$n]);
            if (!$this->hasIndexDisplay) {
                unset($this->controlPrototypes['txtRecordNo']);
                unset($this->controlPrototypes['txtRecordsCount']);
            }
        }
        
    }
    
    /*function getControlPrototypes() {
        return Ae_Util::m($this->controlPrototypes, $this->getDefaultPrototype());
    }*/
        
    
    protected function getDefaultPrototype() {
        $res = array();
        
        foreach ($this->getNavButtonNames() as $n) {
            $res[$n] = array(
                'attribs' => array('class' => 'button'),
                '.click' => 'handleNavButtonsClick',
                'containerIsBlock' => false,
            );
        }
        
        $res = Ae_Util::ms($res, array(
            'btnNext' => array(
                'label' => html_entity_decode(new Pwg_Lang_String('next', array('suffix' => ' &gt;')), null, 'utf-8'),
            ),
            'btnPrev' => array(
                'label' => html_entity_decode(new Pwg_Lang_String('prev', array('prefix' => '&lt; ')), null, 'utf-8'),
            ),
            'btnFirst' => array(
                'label' => html_entity_decode(new Pwg_Lang_String('first', array('prefix' => ' &laquo;')), null, 'utf-8'),
            ),
            'btnLast' => array(
                'label' => html_entity_decode(new Pwg_Lang_String('last', array('suffix' => ' &raquo;')), null, 'utf-8'),
            ),
            'btnReload' => array(
                'label' => html_entity_decode(new Pwg_Lang_String('refresh'), null, 'utf-8'),
            ),
            'btnNew' => array(
                'label' => new Pwg_Lang_String('create'),
            ),
            'btnSave' => array(
                'label' => new Pwg_Lang_String('save'),
            ),
            'btnCancel' => array(
                'label' =>  new Pwg_Lang_String('cancel'),
            ),
            'btnDelete' => array(
                'label' =>  new Pwg_Lang_String('delete'),
            	'confirmationMessage' => $this->deleteConfirmation,
            ),
            'txtRecordNo' => array(
                'text' => '1',
                'size' => 3,
                '.change' => 'handleRecordNoChange',
                '.keyup' => 'handleFRecordNoChange', 
                'containerIsBlock' => false,
            ),
            'txtRecordsCount' => array(
                'text' => '100',
                'disabled' => true,
                'size' => 3,
                'containerIsBlock' => false,
            ),
        ));
        
        return $res;
    }
    
    function handleNavButtonsClick(Pwg_Button $button, $eventType, $params = array()) {
        
        $process = true;
        $params['process'] = & $process;
        $params['button'] = & $button;
        $this->triggerEvent(self::evtNavButtonClick, $params);
        if ($process) {
            
            if ($this->dataSource) {
                switch($button->getId()) {
                    case 'btnNext': $this->dataSource->gotoNext(); break;
                    case 'btnPrev': $this->dataSource->gotoPrev(); break;
                    case 'btnFirst': $this->dataSource->gotoFirst(); break;
                    case 'btnLast': $this->dataSource->gotoLast(); break;
                    case 'btnNew': $this->dataSource->createRecord(); break;
                    case 'btnCancel': $this->dataSource->cancel(); break;
                    case 'btnDelete': $this->dataSource->deleteRecord(); break;
                    case 'btnReload': $this->dataSource->reload(Pwg_Data_Source::HOLD_NUMBER); break;
                    case 'btnSave': 
                        $this->dataSource->saveRecord(); 
                        break;
                }
            }
            
        }
    }
    
    function updateButtons(Pwg_Data_Source $source) {
        if (!is_array($this->controls)) $this->createControls();
        
        if ($this->btnFirst) $this->btnFirst->setDisabled($source->isFirst() || $source->isStart() || !$source->canMove());
        if ($this->btnPrev) $this->btnPrev->setDisabled($source->isFirst() || $source->isStart() || !$source->canMove());
        if ($this->btnNext) $this->btnNext->setDisabled($source->isLast() || $source->isEnd() || !$source->canMove());
        if ($this->btnLast) $this->btnLast->setDisabled($source->isLast() || $source->isEnd() || !$source->canMove());
        
        if ($this->btnNew) {
            $this->btnNew->setDisabled($dis = !$source->canCreate());
        }
        if ($this->btnDelete) $this->btnDelete->setDisabled(!$source->canDelete());
        if ($this->btnCancel) $this->btnCancel->setDisabled(!$source->canCancel());
        if ($this->btnSave) $this->btnSave->setDisabled(!$source->canSave());
    }   
    
    function handleCurrentRecord(Pwg_Data_Source $source, $eventType, $params = array()) {
        
        $this->updateButtons($source);
        
        if (!$this->lockRecordNo) {
            $rn = $source->getRecordNo();
            if ($this->txtRecordNo) {
                $this->txtRecordNo->setDisabled(!$source->canMove());
                if ($rn !== false && ($rn >= 0)) {
                    $this->txtRecordNo->setText($rn + 1);
                } else {
                    $this->txtRecordNo->setText('');
                }
            }
        }
        if ($this->txtRecordsCount) $this->txtRecordsCount->setText($source->getRecordsCount());
    }
    
    function handleRecordNoChange(Pwg_Text $recordNo, $eventType, $params = array()) {
        $rn = $recordNo->getText();
        if (is_numeric($rn) && (intval($rn - 1) != $this->dataSource->getRecordNo())) {
            $this->lockRecordNo = true;
            $this->dataSource->setRecordNo(intval($rn - 1));
            $actRecordNo = $this->dataSource->getRecordNo();
            if (intval($actRecordNo) != intval($rn - 1)) $this->txtRecordNo->setText($actRecordNo + 1);
            $this->lockRecordNo = false;
        }
        //$this->txtName->setText('Record no: '.$recordNo->getText());
    }

    function doGetConstructorName() {
        return 'Pwg_Controller';
    }
    

    protected function setHasBtnFirst($hasBtnFirst) {
        $this->hasBtnFirst = $hasBtnFirst;
    }

    function getHasBtnFirst() {
        return $this->hasBtnFirst;
    }

    protected function setHasBtnLast($hasBtnLast) {
        $this->hasBtnLast = $hasBtnLast;
    }

    function getHasBtnLast() {
        return $this->hasBtnLast;
    }

    protected function setHasBtnPrev($hasBtnPrev) {
        $this->hasBtnPrev = $hasBtnPrev;
    }

    function getHasBtnPrev() {
        return $this->hasBtnPrev;
    }

    protected function setHasBtnNext($hasBtnNext) {
        $this->hasBtnNext = $hasBtnNext;
    }

    function getHasBtnNext() {
        return $this->hasBtnNext;
    }

    protected function setHasBtnEdit($hasBtnEdit) {
        $this->hasBtnEdit = $hasBtnEdit;
    }

    function getHasBtnEdit() {
        return $this->hasBtnEdit;
    }

    protected function setHasBtnCancel($hasBtnCancel) {
        $this->hasBtnCancel = $hasBtnCancel;
    }

    function getHasBtnCancel() {
        return $this->hasBtnCancel;
    }

    protected function setHasBtnSave($hasBtnSave) {
        $this->hasBtnSave = $hasBtnSave;
    }

    function getHasBtnSave() {
        return $this->hasBtnSave;
    }

    protected function setHasBtnDelete($hasBtnDelete) {
        $this->hasBtnDelete = $hasBtnDelete;
    }

    function getHasBtnDelete() {
        return $this->hasBtnDelete;
    }

    protected function setHasIndexDisplay($hasIndexDisplay) {
        $this->hasIndexDisplay = $hasIndexDisplay;
    }

    function getHasIndexDisplay() {
        return $this->hasIndexDisplay;
    }

    protected function setHasBtnNew($hasBtnNew) {
        $this->hasBtnNew = $hasBtnNew;
    }

    function getHasBtnNew() {
        return $this->hasBtnNew;
    }

    protected function setHasBtnReload($hasBtnReload) {
        $this->hasBtnReload = $hasBtnReload;
    }

    function getHasBtnReload() {
        return $this->hasBtnReload;
    }    

    function setDeleteConfirmation($deleteConfirmation) {
        $this->deleteConfirmation = $deleteConfirmation;
        if ($this->btnDelete) $this->btnDelete->setConfirmationMessage($deleteConfirmation);
    }

    function getDeleteConfirmation() {
        return $this->deleteConfirmation;
    }
    
}
?>