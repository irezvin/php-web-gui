<?php

class Pwg_List extends Pwg_Element {

    protected $autoSize = false;
    
    protected $size = 1;
    
    protected $disabled = false;
    
    protected $options = array();
    
    protected $multiple = false;
    
    protected $selectedOptions = array();
    
    protected $optionClass = 'Pwg_List_Option';
    
    protected $className = 'list';
    
    /**
     * Whether to use custom-made 'checkbox list' javascript control instead of select list 
     */ 
    protected $useCheckboxes = false;

    protected $readOnly = false;
    
    function setOptions(array $options = array()) {
        //foreach(array_keys($this->options) as $k) $this->removeOption($k);
        $this->clearOptions();
        $this->lockMessages();
        foreach($options as $o) {
            $opt = $this->addOption(array_key_exists('label', $o)? $o['label'] : false, array_key_exists('value', $o)? $o['value'] : false);
            if (array_key_exists('data', $o)) $opt->setData($o['data']);
        }
        $this->unlockMessages();
        if ($this->autoSize) $this->applyAutoSize();
        $this->sendMessage('setListOptions', array($this->options));
    }
    
    /**
     * @param string $label
     * @param string $value
     * @param int $index
     * @return Pwg_List_Option
     */
    function addOption($label = false, $value = false, $index = false) {
        $cl = $this->optionClass;
        $res = new $cl($this);
        if ($label !== false) $res->setLabel($label);
        if ($value !== false) $res->setValue($value);
        if ($index !== false) $index = min($index, count($this->options));
        if ($index !== false && ($index < count($this->options))) {
            $headOptions = array_slice($this->options, 0, $index);
            $tailOptions = array_slice($this->options, $index);
            $headOptions[] = $res;
            $this->options = array_merge($headOptions, $tailOptions);
        } else {
            $this->options[] = $res;
        }
        if ($this->autoSize) $this->applyAutoSize();
        $this->sendMessage(__FUNCTION__, array($res, $index));
        return $res;
    }
    
    function getOptionIndex(Pwg_List_Option $option) {
        $k = $this->getOptionKey($option);
        if ($k !== false) {
            $res = array_search($k, array_keys($this->options));
        } else {
            $res = false;
        }
        return $res;
    }
    
    /**
     * @return Pwg_List_Option
     */
    function getOptionByIndex($index) {
        $res = false;
        $keys = array_keys($this->options);
        if (isset($keys[$index])) $res = $this->options[$keys[$index]];
        return $res;
    }
    
    function notifyOptionUpdated(Pwg_List_Option $option) {
        if (($index = $this->getOptionIndex($option)) !== false) {
            $this->sendMessage('optionUpdated', array($option, $index));
        }
    }
    
//  function notifyOptionDeleted(Pwg_List_Option $option) {
//      if (($index = $this->getOptionIndex($option)) !== false) {
//          $this->sendMessage('optionDeleted', array($index));
//      }
//  }
    
    function getOptionKey(Pwg_List_Option $option) {
        $res = false;
        foreach ($this->options as $k => $opt) {
            if ($opt === $option) {
                $res = $k; 
                break; 
            }
        }
        return $res;
    }
    
    function removeOption($k) {
        if (isset($this->options[$k])) {
            $idx = $this->getOptionIndex($this->options[$k]);
            unset($this->options[$k]);
            $this->sendMessage(__FUNCTION__, array($idx));
            if ($this->autoSize) $this->applyAutoSize();
        } else {
            throw new Exception ("No such option: '{$k}'"); 
        }
    }
    
    function listOptions() {
        return array_keys($this->options);
    }

    protected function notifySelected(Pwg_List_Option $option) {
        if (($index = $this->getOptionIndex($option)) !== false) {
            $this->sendMessage('optionSelected', array($index));
        }
    }
    
    protected function notifyDeselected($option) {
        if (($index = $this->getOptionIndex($option)) !== false) {
            $this->sendMessage('optionDeselected', array($index));
        }
    }
    
    function selectOption(Pwg_List_Option $option) {
        if (!$this->isOptionSelected($option)) {
            if (!$this->multiple) {
                foreach ($this->selectedOptions as $opt) $opt->setSelected(false);
                $this->selectedOptions = array();
            }
            $this->selectedOptions[] = $option;
            $this->doOnOptionSelected($option);
            $this->notifySelected($option);
        } else {
        }
    }
    
    function isOptionSelected(Pwg_List_Option $option) {
        $res = false;
        foreach (array_keys($this->selectedOptions) as $k) 
            if ($this->selectedOptions[$k] === $option) {
                $res = true;
            }
        return $res;
    }
    
    function deselectOption(Pwg_List_Option $option) {
        foreach ($this->selectedOptions as $k => $opt) 
            if ($this->selectedOptions[$k] === $option) {
                unset($this->selectedOptions[$k]); 
                $this->notifyDeselected($opt);
                break; 
            }
    }
    
    function listSelectedOptions() {
        $res = array();
        foreach ($this->selectedOptions as $opt) {
            $ok = $this->getOptionKey($opt);
            if ($ok !== false) $res[] = $ok;
        }
        return $res; 
    }
    
    function getSelectedOptionIndices() {
        $res = array();
        foreach ($this->selectedOptions as $opt) $res[] = $this->getOptionIndex($opt);
        return $res; 
    }
    
    function getFirstSelectedIndex() {
        $res = false;
        $i = $this->getSelectedOptionIndices();
        if (count($i) > 0) $res = $i[0];
        return $res;
    }
    
    /**
     * @return Pwg_List_Option
     */
    function getFirstSelectedOption() {
        if (strlen($i = $this->getFirstSelectedIndex())) {
            $res = $this->getOption($i);
        } else {
            $res = false;
        }
        return $res;
    }
    
    function setAutoSize($v) {
        if (!is_array($v)) $v = ($v === false? array() : array($v));
        
        $n = substr(__FUNCTION__, 3); $n{0} = strtolower($n{0}); 
        $ov = $this->$n;
        $this->$n = $v;
        if ($ov !== $v) {
            if ($v) {
                $this->applyAutoSize();
            }
        }
    }
    
    function getAutoSize($v) {$n = substr(__FUNCTION__, 3); $n{0} = strtolower($n{0}); return $this->$n;}
    
    protected function setSelectedOptionIndices(array $indices = array()) {
        foreach ($this->selectedOptions as $opt) $opt->setSelected(false);
        $this->selectedOptions = array();
        foreach ($indices as $idx) {
            if ($opt = $this->getOptionByIndex($idx)) $opt->setSelected(true);
        }
    }
    
    function setMultiSelect($value = null) {
        $prop = substr(__FUNCTION__, 3); $prop{0} = strtolower($prop{0}); 
        if (!is_null($value)) $this->$prop = $value;
        $this->sendMessage(__FUNCTION__, array($this->$prop));
    }
    
    function getMultiSelect() {
        $prop = substr(__FUNCTION__, 3); $prop{0} = strtolower($prop{0});
        return $this->$prop;
    }
    
    /**
     * @return Pwg_List_Option
     */
    function getOption($k) {
        if (isset($this->options[$k])) {
            $res = $this->options[$k];
        } else {
            throw new Exception ("No such option: '{$k}'"); 
        }
        return $res;
    }

    /**
     * @param mixed $val
     * @return array
     */
    function findOptionsByValue($val) {
        $res = array();
        foreach ($this->listOptions() as $i) {
            $opt = $this->getOption($i);
            $ov = $opt->getValue();
            
            // Options that have strict matches go first
            if ($ov === $val) $res = array_merge(array($opt), $res);
                elseif ($ov == $val) $res[] = $opt;
        }
        return $res;
    }

    /**
     * @param mixed $val
     * @return array
     */
    function findOptionIndicesByValue($val) {
        $res = array();
        foreach ($this->listOptions() as $i) {
            $opt = $this->getOption($i);
            $ov = $opt->getValue();
            
            if ($ov === $val) {
                $res = array_merge(array($i), $res);
            } elseif ($ov == $val) {
                $res[] = $i;
            }
        }
        return $res;
    }
    
    function triggerFrontendFocus() {
        $evt = substr(__FUNCTION__, 15); $evt{0} = strtolower($evt{0}); 
        $this->triggerEvent($evt);
    }
    
    function triggerFrontendBlur() {
        $evt = substr(__FUNCTION__, 15); $evt{0} = strtolower($evt{0}); 
        $this->triggerEvent($evt);
    }
    
    function triggerFrontendSelectionChange($optionIndices = array()) {
        if (!is_array($optionIndices)) $optionIndices = array();
        $evt = substr(__FUNCTION__, 15); $evt{0} = strtolower($evt{0});
        $currentSelection = $this->getSelectedOptionIndices();
        $selected = array_diff($optionIndices, $currentSelection);
        $deselected = array_diff($currentSelection, $optionIndices);
        $actualOptionIndices = array();
        if ($selected || $deselected) {
            $this->lockMessages();
            $this->setSelectedOptionIndices($optionIndices);
            $this->unlockMessages();
            $actualOptionIndices = $this->getSelectedOptionIndices();
            $selected = array_diff($actualOptionIndices, $currentSelection);
            $deselected = array_diff($currentSelection, $optionIndices);
        }
        if (array_diff($actualOptionIndices, $optionIndices)) {
            $this->sendMessage('setSelectedIndices', array($actualOptionIndices));
        }
        $this->triggerEvent($evt, array('currentSelectionIndices' => $actualOptionIndices, 'selectedIndices' => $selected, 'deselectedIndices' => $deselected));
//      if ($optionIndices)
//          $this->selectOption($option);
//          $this->unlockMessages();
        
//      if ($option = $this->getOptionByIndex($optionIndex)) {
//          $this->lockMessages();
//          $this->selectOption($option);
//          $this->unlockMessages();
//      }
//      $this->triggerEvent($evt, array($optionIndex));
    
    }
    
//  function triggerFrontendDeselected($optionIndex) {
//      $evt = substr(__FUNCTION__, 15); $evt{0} = strtolower($evt{0}); 
//      if ($option = $this->getOptionByIndex($optionIndex)) {
//          $this->lockMessages();
//          $this->deselectOption($option);
//          $this->unlockMessages();
//      }
//      $this->triggerEvent($evt, array($optionIndex));
//  }
    
    function setSize($value = null) {
        $prop = substr(__FUNCTION__, 3); $prop{0} = strtolower($prop{0}); 
        if (!is_null($value)) $this->$prop = $value;
        $this->sendMessage(__FUNCTION__, array($this->$prop));
    }
    
    function getSize() {
        $prop = substr(__FUNCTION__, 3); $prop{0} = strtolower($prop{0});
        return $this->$prop;
    }
    
    function setDisabled($value = null) {
        $prop = substr(__FUNCTION__, 3); $prop{0} = strtolower($prop{0}); 
        if (!is_null($value)) $this->$prop = $value;
        $this->sendMessage(__FUNCTION__, array($this->$prop));
    }
    
    function getDisabled() {
        $prop = substr(__FUNCTION__, 3); $prop{0} = strtolower($prop{0});
        return $this->$prop;
    }
    
    function setReadOnly($readOnly) {
        if ($readOnly !== ($oldReadOnly = $this->readOnly)) {
            $this->readOnly = $readOnly;
            $this->sendMessage(__FUNCTION__, array($this->readOnly));
        }
    }

    function getReadOnly() {
        return $this->readOnly;
    }    
    
    
    function setSelectedValue($v, $silent = true) {
        if ($this->multiple) {
            if (!is_array($v)) $v = is_null($v)? array() : array($v);
            $selectedIndices = array();
            foreach ($v as $selVal) $selectedIndices = array_merge($selectedIndices, $this->findOptionIndicesByValue($selVal));
            $this->setSelectedOptionIndices($selectedIndices);
        } else {
            if ($this->responderId === 'Nc_Admin_Controller40Controller_Nc_Admin_Details_Building_1Fld_c_hasCentralWaterHeatingEditor') {
                $foo = 0;
            }
            $oi = $this->findOptionIndicesByValue($v);
            if (count($oi) > 1) $oi = array($oi[0]);
            $this->setSelectedOptionIndices($oi);
        }
        if (!$silent) $this->triggerEvent('selectionChange');
    }
    
    function getSelectedValue() {
        if ($this->multiple) {
            $res = array();
            foreach ($this->listSelectedOptions() as $i) {
                $opt = & $this->getOption($i);
                $res[] = $opt->getValue();
            }
        } else {
            $res = null;
            $so = $this->listSelectedOptions();
            if (count($so)) {
                $opt = $this->getOption($so[0]);
                $res = $opt->getValue();
            }
        }
        return $res;
    }
    
    
    function clearOptions() {
        foreach ($this->options as $opt) $opt->refNotifyDestroying();
        $this->options = array();
        $this->selectedOptions = array();
        $this->sendMessage('clearListOptions');
    }
        
    function setOptionsAsArray($options = array()) {
        $this->lockMessages();
        $this->clearOptions();
        foreach ($options as $value => $label) {
            $this->addOption($label, $value);
        }
        $this->unlockMessages();
        $this->sendMessage('setListOptions', array($this->options));
    }
    
    function getOptionsAsArray() {
        $res = array();
        foreach ($this->listOptions() as $i) {
            $opt = $this->getOption($i);
            $res[$opt->getValue()] = $opt->getLabel(); 
        }
        return $res;
    }
    
//  Template methods of Pwg_Base
    
    protected function doOnInitialize($options) {
        parent::doOnInitialize($options);
        $this->internalObservers['selectionChange'] = 1;
    }
    
    protected function doOnGetInitializer(Pwg_Js_Initializer $initializer) {
        parent::doOnGetInitializer($initializer);
        $initializer->constructorParams[0]['selectedIndices'] = $this->getSelectedOptionIndices(); 
    }
        
    protected function doListPassthroughParams() {
        return array_merge(parent::doListPassthroughParams(), array('options' => 'listOptions', 'size', 'multiple', 'disabled', 'readOnly')); 
    }
    
    protected function doOnOptionSelected($option) {
    }
    
    protected function applyAutoSize() {
        $this->setSize(count($this->options));
    }
    
    function redrawOptions($options = false) {
        if ($options === false) $options = $this->options;
        $o = array();
        foreach ($options as $opt) {
            $json = $opt->toJs();
            $json['index'] = $opt->getIndex();
            if ($json['index'] !== false) {
                $o[] = $json;
            }
        }
        if ($o) $this->sendMessage('redrawOptions', array($o));
    }

    protected function setUseCheckboxes($useCheckboxes) {
        $this->useCheckboxes = $useCheckboxes;
    }

    function getUseCheckboxes() {
        return $this->useCheckboxes;
    }   
    
    protected function doGetAssetLibs() {
        if ($this->useCheckboxes) {
            $res = array('widgets.js', 'widgets/checklist.js');
        } else {
            $res = array('widgets.js');
        }
        return $res;
    }
    
    protected function doGetConstructorName() {
        if ($this->useCheckboxes) {
            $res = 'Pwg_Checklist';
        } else {
            $res = 'Pwg_List';
        }
        return $res;
    }

    function setMultiple($multiple) {
        if ($multiple !== ($oldMultiple = $this->multiple)) {
            $this->multiple = $multiple;
            $this->sendMessage(__FUNCTION__, array($multiple));
        }
    }

    function getMultiple() {
        return $this->multiple;
    }
    
}

?>