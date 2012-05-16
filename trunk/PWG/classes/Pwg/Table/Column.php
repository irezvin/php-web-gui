<?php

class Pwg_Table_Column extends Pwg_Composite {

    protected $caption = false;
    
    protected $hasEditor = false;
    
    protected $editorPrototype = false;
    
    /**
     * @var Pwg_Table_Editor
     */
    protected $editor = false;
    
    // YUI column configuration options
    
    protected $className = false;

    protected $currencyOptions = false;

    protected $dateOptions = false;

    protected $fieldName = false;

    protected $formatter = false;

    protected $hidden = false;

    protected $alwaysExport = false;
    
    protected $canToggle = true;    

    protected $label = false;

    protected $maxAutoWidth = false;

    protected $minWidth = false;

    protected $resizeable = false;

    protected $selected = false;

    protected $sortable = false;

    protected $sortOptions = false;

    protected $width = false;
    
    protected $searchable = true;
    
    /**
     * @var Ac_I_Decorator
     */
    protected $decorator = false;
    
    protected function setEditorPrototype($prototype) {
        $this->editorPrototype = $prototype;
    }

    protected function setHasEditor($hasEditor) {
        $this->hasEditor = $hasEditor;
    }

    function getHasEditor() {
        return $this->hasEditor;
    }   
    
    /**
     * @return Pwg_Table_Editor
     */
    function getEditor() {
        //Pwg_Conversation::log("Editor", $this->editor, "hasEditor", $this->hasEditor, "editorPrototype", $this->editorPrototype);      
        if (($this->editor === false) && $this->hasEditor && is_array($this->editorPrototype)) {
            $ep = $this->editorPrototype;
            $ep['column'] = $this;
            $this->editor = Pwg_Base::factory($this->editorPrototype, 'Pwg_Table_Editor');
        }
        //Pwg_Conversation::log("Editor of {$this} is {$this->editor}");
        return $this->editor;
    }
    
    /**
     * @return Pwg_Table_Colset
     */
    function getColset() {
        return $this->parent;
    }
    
    /**
     * @return Pwg_Table
     */
    function getTable() {
        $res = false;
        $colset = $this->getColset();
        if ($colset !== false) $res = $colset->getTable();
        return $res;
    }
    
    function getEffectiveFieldName() {
        $res = $this->id;
        if (strlen($this->fieldName)) $res = $this->fieldName;
        return $res;
    }
    
    function hasJsObject() {
        return false;
    }

    function toJs() {
        $res = $this->getPassthroughParams(array(
            'className',
            'currencyOptions',
            'dateOptions',
            //'fieldName' => 'field',
            'editor',
            'formatter',
            'hidden',
            'id' => 'key',
            'label',
            'maxAutoWidth',
            'minWidth',
            'resizeable',
            'selected',
            'sortable',
            'sortOptions',
            'width',
        ), false, false);
        if ($e = $this->getEditor()) $res['editor'] = $e->toJs();
        return $res;
    }
    
    // YUI column configuration options

    protected function setClassName($className) {
        $this->className = $className;
    }

    function getClassName() {
        return $this->className;
    }

    protected function setCurrencyOptions($currencyOptions) {
        $this->currencyOptions = $currencyOptions;
    }

    function getCurrencyOptions() {
        return $this->currencyOptions;
    }

    protected function setDateOptions($dateOptions) {
        $this->dateOptions = $dateOptions;
    }

    function getDateOptions() {
        return $this->dateOptions;
    }

    protected function setFieldName($fieldName) {
        $this->fieldName = $fieldName;
    }

    function getFieldName() {
        return strlen($this->fieldName)? $this->fieldName : $this->getId(); 
    }

    protected function setFormatter($formatter) {
        $this->formatter = $formatter;
    }
    
    const onEvaluate = 'onEvaluate';

    function getFormatter() {
        return $this->formatter;
    }
    
    const onEvaluate = 'onEvaluate';

    /**
     * @return array(aeUid => value) Values of this column for different records 
     */
    function getColData() {
        $res = array();
        $d = $this->getDecorator();
        if ($t = $this->getTable()) {
            $t->triggerDataCollect($this->id);
            foreach ($t->listRows() as $r) {
                $row = $t->getRow($r);
                if ($rec = $row->getRecord()) {
                    $val = $rec->getField($this->getFieldName());
                    if (isset($this->observers[self::onEvaluate])) {
                        $this->triggerEvent(self::onEvaluate, array('row' => & $row, 'value' => & $val));
                    }
                    if ($d) $val = $d->apply($val);
                    $res[$rec->getUid()] = $val;
                }
            }
            $this->decorator = $d;
            $t->triggerAfterDataCollect($this->id, $res);
        } else {
        }
        return $res;
    }

    function setHidden($hidden) {
        if ($hidden !== ($oldHidden = $this->hidden)) {
            $this->hidden = $hidden;
            if ($t = $this->getTable()) {
                $t->resetShownFieldsList();
                if ($hidden) {
                    $colData = false;
                } else {
                    $colData = $this->getColData();
                }
                $t->sendColumnMessage($this->id, 'toggle', array(!$hidden, $colData));
            }
        }
    }

    function getHidden() {
        return $this->hidden;
    }
    
    function getIsExported() {
        return !$this->hidden || $this->alwaysExport;
    }

    protected function setAlwaysExport($alwaysExport) {
        $this->alwaysExport = $alwaysExport;
    }

    function getAlwaysExport() {
        return $this->alwaysExport;
    }    
    
    function setLabel($label) {
        if (($oldLabel = ($this->label)) !== $label) {
            $this->label = $label;
            if ($t = $this->getTable()) {
                $t->sendColumnMessage($this->id, 'setLabel', array($label));
            }
        }
    }

    function getLabel() {
        if ($this->label !== false) {
        	$res = $this->label;
        } else {
        	$res = $this->id;
        	$mdp = $this->getTable()->getMetadataProvider();
        	if ($mdp && ($fi = $mdp->getFieldInfo($this->getFieldName()))) {
        		if ($fi->getCaption() !== false) $res = $fi->getCaption();
        	}
        }
    	return $res;
    }

    protected function setMaxAutoWidth($maxAutoWidth) {
        $this->maxAutoWidth = $maxAutoWidth;
    }

    function getMaxAutoWidth() {
        return $this->maxAutoWidth;
    }

    protected function setMinWidth($minWidth) {
        $this->minWidth = $minWidth;
    }

    function getMinWidth() {
        return $this->minWidth;
    }

    protected function setResizeable($resizeable) {
        $this->resizeable = $resizeable;
    }

    function getResizeable() {
        return $this->resizeable;
    }

    protected function setSelected($selected) {
        $this->selected = $selected;
    }

    function getSelected() {
        return $this->selected;
    }

    protected function setSortable($sortable) {
        $this->sortable = $sortable;
    }

    function getSortable() {
        return $this->sortable;
    }

    protected function setSortOptions($sortOptions) {
        $this->sortOptions = $sortOptions;
    }

    function getSortOptions() {
        return $this->sortOptions;
    }

    function setWidth($width) {
        $width = intval($width);
        if ($width <= 0) throw new Exception ("\$width should be above or equal to zero");
        if ($width !== ($oldWidth = $this->width)) {
            $this->width = $width;
            if ($this->getTable()) $this->getTable()->sendColumnMessage($this->id, 'resize', array($width));
        }
    }

    function getWidth() {
        return $this->width;
    }   

    function hasContainer() {
        return false;
    }
    
    function setDisplayOrder($displayOrder) {
        $old = $this->displayOrder;
        if ($old !== $displayOrder) {
            if ($this->displayParent) $this->displayParent->updateDisplayChildPosition($this, $displayOrder);
            if ($this->getTable()) $this->getTable()->sendColumnMessage($this->id, 'reorder', array($displayOrder));
        }
    }
    
    function setCanToggle($canToggle) {
        if ($canToggle !== ($oldCanToggle = $this->canToggle)) {
            $this->canToggle = $canToggle;
        }
    }

    function getCanToggle() {
        return $this->canToggle;
    }

    function setSearchable($searchable) {
        $this->searchable = $searchable;
    }

    function getSearchable() {
        return $this->searchable;
    }

    function setDecorator($decorator) {
        $this->decorator = $decorator;
    }

    /**
     * @return Ac_I_Decorator
     */
    function getDecorator() {
        $this->decorator = Ac_Decorator::instantiate($this->decorator);
        return $this->decorator;
    }    
    
}

?>