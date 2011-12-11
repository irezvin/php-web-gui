<?php

class Pmt_Controller_Std_Backups extends Pmt_Controller_MDI_Window {
	
    /**
     * @var Pmt_Backup_List
     */
    protected $backupsList = false;
    
    /**
     * @var Pmt_Backup
     */
    protected $backup = false;
    
    /**
     * @var Pmt_Table
     */
    protected $tblBackupsList = false;
    
    /**
     * @var Pmt_Button
     */
    protected $btnCreateBackup = false;
    
    /**
     * @var Pmt_Button
     */
    protected $btnDeleteBackup = false;
    
    /**
     * @var Pmt_Button
     */
    protected $btnRestoreBackupMySql = false;
    
    /**
     * @var Pmt_Text
     */
    protected $txtComment = false;
    
    /**
     * @var Pmt_Button
     */
    protected $btnSetComment = false;
    
    /**
     * @var Pmt_Button
     */
    protected $btnRefresh = false;
    
    /**
     * @var Pmt_Label
     */
    protected $lblEtc = false;
    
    /**
     * @var Pmt_Label
     */
    protected $lblResult = false;
    
    protected function doGetDefaultWindowHeader() {
    	return new Pmt_Lang_String('backups');
    }
        
    protected function doOnGetControlPrototypes (& $prototypes) {
        parent::doOnGetControlPrototypes($prototypes);
        Ae_Util::ms($prototypes, array(
            'pnlLayout' => array(
                'template' => '
                    <table cols="2">
                        <tr>
                            <td style="padding: 0.5em; vertical-align: top">
                                {lng:backups_list}: {btnRefresh}<br /><br />
                                {tblBackupsList}
                                
                            </td>
                            <td style="padding: 0.5em; vertical-align: top">
                                {btnCreateBackup} <br />
                                <hr />
                                {lblEtc}
                                <br />
                                {btnRestoreBackupMySql}<br />
                                {lblResult}
                                <hr />
                                {lng:notice}: <br />{txtComment}<br /> {btnSetComment}
                                
                                <br />
                                <hr />
                                {btnDeleteBackup}
                            </td>
                        </tr>
                    </table>
                ',
            ),
            'tblBackupsList' => array(
                'displayParentPath' => '../pnlLayout',
                'class' => 'Pmt_Table',
                'columnPrototypes' => array(
                    'readableDateTime' => array('label' => new Pmt_Lang_String('dateTime')),
                    'size' => array('label' => new Pmt_Lang_String('size')),
                    'hasMySql' => array('label' => new Pmt_Lang_String('backups_hasMySql')),
                    'comment' => array('label' => new Pmt_Lang_String('notice'))
                ),
            ),
            
            'btnCreateBackup' => array(
                'displayParentPath' => '../pnlLayout', 'containerIsBlock' => false,
                'label' => new Pmt_Lang_String('create'),
            ),
            'btnDeleteBackup' => array(
                'displayParentPath' => '../pnlLayout', 'containerIsBlock' => false,
                'label' => new Pmt_Lang_String('delete'),
                'confirmationMessage' => new Pmt_Lang_String('backups_delete_confirmation'),
            ),
            'btnRestoreBackupMySql' => array(
                'displayParentPath' => '../pnlLayout', 'containerIsBlock' => false,
                'label' => new Pmt_Lang_String('backups_restore_data'),
                'confirmationMessage' => new Pmt_Lang_String('backups_restore_confirmation'),
            ),
            'txtComment' => array(
                'displayParentPath' => '../pnlLayout', 'containerIsBlock' => false,
            ),
            'btnSetComment' => array(
                'displayParentPath' => '../pnlLayout', 'containerIsBlock' => false, 'label' => new Pmt_Lang_String('save'),
            ),
            'btnRefresh' => array(
                'displayParentPath' => '../pnlLayout', 'containerIsBlock' => false, 'label' => new Pmt_Lang_String('refresh'),
            ),
            'lblEtc' => array('displayParentPath' => '../pnlLayout', 'containerIsBlock' => false,),
            'lblResult' => array('displayParentPath' => '../pnlLayout', 'containerIsBlock' => false,),
        ));
    }

    protected function doAfterControlsCreated() {
        parent::doAfterControlsCreated();
        $this->backupsList = new Pmt_Backup_List(array('path' => _DEPLOY_BACKUPS_PATH));
        $this->refresh(); 
    }
    
    function refresh() {
        $this->backupsList->refresh();
        if ($this->backup && !$this->backup->hasDir()) $this->backup = false;
        $recs = array();
        $colNames = $this->tblBackupsList->getColset()->listControls();
        if ($this->backup) $currPx = $this->backup->getPrefix();
            else $currPx = false;
        foreach ($this->backupsList->listBackups() as $i) {
            $b = $this->backupsList->getBackup($i);
            $a = array();
            $a['prefix'] = $b->getPrefix();
            foreach ($colNames as $c) $a[$c] = Pmt_Base::getProperty($b, $c, '?');
            if (is_array($a['comment']) && isset($a['comment']['text'])) $a['comment'] = $a['comment']['text'];
            $recs[$b->getPrefix()] = new Pmt_Record_Array($a);
        }
        $this->tblBackupsList->setRecordRows($recs);
        if ($this->backup && strlen($px = $this->backup->getPrefix())) {
            $rows = $this->tblBackupsList->findRowByData(array('prefix' => $px), true);
            $this->tblBackupsList->setSelectedRows($rows);
        }
        $this->refreshCurrent();
    }
    
    function handleBtnRefreshClick() {
        $this->refresh();
    }
    
    function handleBtnCreateBackupClick() {
        $this->backup = $this->backupsList->createBackup();
        $this->backup->createMySqlBackup();
        if (strlen($c = trim($this->txtComment->getText()))) {
        	$this->backup->setComment(array('text' => $c));
        }
        $this->refresh();
    }
    
    function refreshCurrent() {
        $this->lblResult->setVisible(false);
        $this->btnDeleteBackup->setDisabled(!($this->backup && $this->backup->hasDir()));
        $this->btnRestoreBackupMySql->setDisabled(!($this->backup && $this->backup->hasMySql()));
        $commentText = '';
        if ($this->backup) {
            $c = $this->backup->getComment();
            if (isset($c['text'])) $commentText = $c['text'];
            $this->lblEtc->setHtml(sprintf(new Pmt_Lang_String('backups_chosen_copy'), $this->backup->getDirName()));
        } else {
            $this->lblEtc->setHtml(new Pmt_Lang_String('backups_chosen_none'));
        }
        $this->btnSetComment->setDisabled(!($this->backup && ($this->txtComment->getText() !== $commentText) ));
        
    }
    
    function handleTblBackupsListOnRecordSelected() {
        $prefix = false;
        if (count($r = $this->tblBackupsList->getSelectedRowIndices())) {
            $row = $this->tblBackupsList->getRow($r[0]);
            $prefix = $row->getRecord()->getField('prefix');
            $this->backup = $this->backupsList->getBackup($prefix);
            $c = $this->backup->getComment();
            if (isset($c['text'])) $commentText = $c['text'];
                else $commentText = '';
            $this->txtComment->setText($commentText);
        } else {
            $this->backup = null;
        }
        $this->refreshCurrent();
    }
    
    function handleBtnRestoreBackupMySqlClick() {
        if ($this->backup && $this->backup->hasMySql()) {
            $res = $this->backup->restoreMySqlBackup();
            $this->lblResult->setHtml('<br />'.($res? new Pmt_Lang_String('ok') : sprintf(new Pmt_Lang_String('backups_problems'), $this->backup->getLastResult()).'<br /><pre>'.nl2br(htmlspecialchars(implode("\n", $this->backup->getOutput()))).'</pre>').'<br />');
            $this->lblResult->setVisible(true);
        }
    }
    
    function handleBtnDeleteBackupClick() {
        if ($this->backup) {
            $this->backup->delete();
            $this->refresh();
        }
    }
    
    function handleTxtCommentChange() {
        $newComment = trim($this->txtComment->getText());
        if ($this->backup) {
            $c = $this->backup->getComment();
            if ($c && isset($c['text'])) $cText = $c['text']; else $cText = '';
            $this->btnSetComment->setDisabled($cText == $newComment);
        } else {
            $this->btnSetComment->setDisabled(true);
        }
    }
    
    function handleBtnSetCommentClick() {
        $newComment = trim($this->txtComment->getText());
        if ($this->backup) {
            $this->backup->setComment(array('text' => $newComment));
            $this->refresh();
        }
    }
    
}