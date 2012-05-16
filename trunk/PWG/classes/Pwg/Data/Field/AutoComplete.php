<?php

class Pwg_Data_Field_AutoComplete extends Pwg_Data_Field {
    
    protected $labelFieldName = false;
    
    protected $valueFieldName = false;
    
    protected $value = false;
    
    protected $maxResults = 15;
    
    protected $readOnly = false;
    
    protected $disabled = false;
    
    protected $labelExpression = false;
    
    protected $labelDisplayExpression = false;
    
    /**
     * @var mixed Value that is returned when item isn't found in the list
     */
    protected $defaultValue = null;
    
    protected $emptyValue = null;
    
    protected $emptyValueIsSet = true;
    
    //protected $allowNullValues = false;
    
    /**
     * @var Pwg_Data_Source
     */
    protected $listSource = false;

    protected $ownListSourcePrototype = false;
    
    /**
     * @var Pwg_Data_Source
     */
    public $ownListSource = false;
    
    /**
     * @var Pwg_Yui_AutoComplete
     */
    public $editor = false;
    
    /**
     * @var string|bool Text of edit control
     */
    protected $text = false;

    protected $multipleValuesSeparator = false;
    
    protected $multipleValuesGlue = false;

    protected $clearTextWhenNotInList = false;
    
    protected function doOnGetControlPrototypes(& $prototypes) {
        parent::doOnGetControlPrototypes($prototypes);
        $p = array(
            'editor' => array(
                'class' => 'Pwg_Yui_AutoComplete', 
                'displayParentPath' => '../panel', 
                'containerIsBlock' => false,
                'autoCompleteProperties' => array(
                    //'resultTypeList' => false,
                ),
                'dataSourceProperties' => array(
                    'responseSchema' => array('fields' => array('label', 'value')),
                ),
            ),
            'binder' => array(
                'dataControlPath' => '..',
                'dataPropertyName' => 'value',
                'controlChangeEvents' => array('change'),
            ),
        );
        if (strlen($this->multipleValuesSeparator)) {
            $p['editor']['autoCompleteProperties']['delimChar'] = array($this->multipleValuesSeparator);
            $p['editor']['autoCompleteProperties']['autoHighlight'] = false;
        }
        if (is_array($this->ownListSourcePrototype)) {
            $p['ownListSource'] = $this->ownListSourcePrototype;
            $this->ownListSourcePrototype = false;
        }
        Ac_Util::ms($prototypes, $p);
    }
    
    protected function replaceQuery(& $expression, $query) {
        $res = false;
        $res = (strpos($expression, '{{query}}') !== false) || (strpos($expression, '{{value}}') !== false);
        if ($res) {
            $expression = str_replace('{{query}}', $this->listSource->getLegacyDb()->Quote($query), $expression);
            $expression = str_replace('{{value}}', $this->value, $expression);
        }
        return $res;
    }
    
    function handleEditorDataRequest(Pwg_Yui_AutoComplete $editor, $eventName, array $params) {
        if (isset($params['request'])) {
            $lf = $this->getLabelFieldName();
            $alde = $this->getActualLabelDisplayExpression();
            if (!$this->listSource) Pwg_Conversation::log("~~~ no list source ~~~");
            if ($this->listSource && (strlen($lf) || strlen($alde)) && strlen($this->valueFieldName)) {
                $c = $this->listSource->createCollection();
                
                if (strlen($this->labelExpression)) $qlf = $this->labelExpression; 
                    else $qlf = $this->listSource->getLegacyDb()->NameQuote($c->getAlias()).'.'.$this->listSource->getLegacyDb()->NameQuote($lf);
                
                if ($this->replaceQuery($qlf, $params['request']))  
                    $c->addWhere($qlf);
                else
                    $c->addWhere($qlf.' LIKE '.$this->listSource->getLegacyDb()->Quote('%'.$params['request'].'%'));
                
                if (strlen($alde)) $c->addExtraColumn($alde.' AS _alde');
                if ($this->maxResults !== false) $c->setLimits(0, $this->maxResults);
                $list = array();
                while ($rec = $c->getNext()) {
                    if (strlen($alde)) $label = $rec->_otherValues['_alde'];
                        else $label = $rec->getField($lf);
                    $list[] = array('label' => $label, 'value' => $rec->getField($this->valueFieldName));
                }
                $this->editor->setResponse($list);
            } else {
                throw new Exception("Pwg_Yui_AutoComplete isn't properly configured; check if listSource, valueFieldName and labelField (or labelExpression) properties are set.");
            }
        }
    }
    
    protected function getActualLabelDisplayExpression() {
        $res = false;
        if (!strlen($this->labelFieldName)) {
            if (strlen($this->labelDisplayExpression)) $res = $this->labelDisplayExpression;
            else if (strlen($this->labelExpression)) $res = $this->labelExpression;
        }
        return $res;
    }
    
    function handleEditorItemSelected() {
        $this->handleEditorChange();
    }
    
    function handleEditorChange() {
        $this->text = $this->editor->getText();
        if (strlen($this->multipleValuesSeparator)) $this->text = $this->explode ($this->text);
        $v = $this->findValue(true);
        $cancel = false;
        $hasMissingValues = false;
        $isNotInList = false;
        if (is_array($v)) {
            foreach ($v as $k => $subV) {
                if (is_null($subV)) {
                    if (is_array($this->text) && isset($this->text[$k])) {
                        $subText = $this->text[$k];
                    } else {
                        $subText = false;
                    }
                    $this->triggerEvent('notInList', array('value' => & $subV, 'text' => $this->text, 'subText' => $subText, 'cancel' => & $cancel));
                    $isNotInList = true;
                    $v[$k] = $subV;
                }
            }
        } else {
            if (is_null($v)) {
                $v = $this->defaultValue;
                $this->value = $v;
                $this->triggerEvent('notInList', array('value' => & $v, 'text' => $this->text, 'cancel' => & $cancel));
                $isNotInList = true;
            }
        }
        if (!$cancel) {
            $this->setValue($v, true, $isNotInList && !$this->clearTextWhenNotInList, true);
            
        }
    }

    function setValue($value, $triggerChange = false, $dontUpdateEditor = false, $force = false) {
        $args = func_get_args();
        if ($value !== ($oldValue = $this->value) || $force) {
            $this->value = $value;
            $this->triggerEvent('valueUpdate', array('value' => $value, 'oldValue' => $oldValue));
            if ($triggerChange) $this->triggerEvent('change', array('value' => $value, 'oldValue' => $oldValue));
            if ($this->editor && !$dontUpdateEditor) {
                $this->text = $this->findText();
                $this->editor->setText(is_null($this->text)? '' : $this->text);
            }
            else {
                $this->controlPrototypes['editor']['text'] = $this->text;
            }
        }
    }

    function getValue() {
        return $this->value;
    }
    
    function setLabelFieldName($labelFieldName) {
        if ($labelFieldName !== ($oldLabelFieldName = $this->labelFieldName)) {
            $this->labelFieldName = $labelFieldName;
            $this->intReset();
        }
    }
    
    function setLabelDisplayExpression($labelDisplayExpression) {
        if ($labelDisplayExpression !== ($oldLabelDisplayExpression = $this->labelDisplayExpression)) {
            $this->labelDisplayExpression = $labelDisplayExpression;
            $this->intReset();
        }
    }
    
    function getLabelDisplayExpression() {
        return $this->labelDisplayExpression;
    }    

    function getLabelFieldName() {
        return strlen($this->labelFieldName)? $this->labelFieldName : $this->valueFieldName;
    }

    function setValueFieldName($valueFieldName) {
        if ($valueFieldName !== ($oldValueFieldName = $this->valueFieldName)) {
            $this->valueFieldName = $valueFieldName;
            $this->intReset();
        }
    }

    function getValueFieldName() {
        return $this->valueFieldName;
    }

    function setListSource(Pwg_Data_Source $listSource = null) {
        if ($listSource !== ($oldListSource = $this->listSource)) {
            $this->listSource = $listSource;
            $this->intReset();
        }
    }

    function getListSource() {
        return $this->listSource;
    }

    function setText($text) {
        if ($text !== ($oldText = $this->text)) {
            $this->text = $text;
            $this->value = false;
            $this->findValue();
        }
    }

    function getText() {
        if ($this->text === false) {
            $this->findText();
        }
        return $this->text;
    }
    
    protected function findText($value = null) {
        if (!func_num_args()) {
            $value = $this->value;
        } 
        if (is_array($value) && strlen($this->multipleValuesSeparator)) {
            $labels = array();
            foreach ($value as $val) $labels[] = $this->findText($val);
            $res = $this->implode($labels); 
        } else {
            $res = null;
            $found = false;
            $lf = $this->labelFieldName;
            $alde = $this->getActualLabelDisplayExpression();
            if (strlen($lf) || strlen($alde)) {
                if ($this->listSource && strlen($this->valueFieldName)) {
                    $c = $this->listSource->createCollection();
                    if (strlen($alde)) $c->addExtraColumn($alde.' AS _alde');
                    $qvf = $this->listSource->getLegacyDb()->NameQuote($this->valueFieldName);
                    $c->addWhere($qvf.' = '.$this->listSource->getLegacyDb()->Quote($value));
                    $c->setLimits(0, 1);
                    if ($rec = $c->getNext()) {
                        if (strlen($lf)) $res = $rec->getField($lf);
                        elseif (strlen($alde)) $res = $rec->_otherValues['_alde'];
                        $found = true;
                    }
                    if (!$found) $this->triggerEvent("textNotFound", array('text' => & $res, 'value' => $value));
                }
            }
        }
        return $res;
    }
    
    protected function findValue($exact = false, $text = null) {
        if (is_null($text)) {
            $text = $this->text;
            if (strlen($this->multipleValuesSeparator) && !is_array($text)) $text = $this->explode($text);
        }
        if (is_array($text)) {
            $res = array();
            foreach ($text as $textPart) {
                $res[] = $this->findValue($extact, $textPart);
                $val = $this->findValue($exact, $textPart);
                if (!is_null($val)) $res[] = $val;
            }
        } else {
            if (!strlen($text) && $this->emptyValueIsSet) {
                $res = $this->emptyValue;
            } else {
                $res = null;
                $lf = $this->getLabelFieldName();
                if ($this->listSource && strlen($lf) && strlen($this->valueFieldName)) {
                    $c = $this->listSource->createCollection();
                    if (strlen($this->labelExpression)) $qlf = $this->labelExpression; 
                        else $qlf = $this->listSource->getAeDb()->NameQuote($c->getAlias()).'.'.$this->listSource->getAeDb()->NameQuote($lf);

                    if ($this->replaceQuery($qlf, $text)) {
                        $c->addWhere($qlf);
                        $c->setDistinctCount(true); 
                        if (($c->countRecords() == 1) && ($rec = $c->getNext())) {
                            $res = $rec->getField($this->valueFieldName);
                        }

                    } else {

                        if ($exact) $c->addWhere($qlf.' = '.$this->listSource->getAeDb()->Quote($text));
                            else $c->addWhere($qlf.' LIKE '.$this->listSource->getAeDb()->Quote($text.'%'));
                        $c->setLimits(0, 1);
                        if ($rec = $c->getNext()) {
                            $res = $rec->getField($this->valueFieldName);
                        }

                    }
                    
                }
            }
        }
        return $res;
    }
    
    protected function intReset() {
        $this->text = false;
    }
    
    /**
     * @param int|bool $maxResults Number of results to display in autoComplete list (FALSE == unlimited)
     */
    function setMaxResults($maxResults) {
        if ($maxResults !== ($oldMaxResults = $this->maxResults)) {
            $this->maxResults = $maxResults;
        }
    }

    function getMaxResults() {
        return $this->maxResults;
    }

    function setDefaultValue($defaultValue) {
        $this->defaultValue = $defaultValue;
    }

    function getDefaultValue() {
        return $this->defaultValue;
    }

    function setListSourcePath($listSourcePath = false) {
        $this->associations['listSource'] = $listSourcePath;
    }
    
    /**
     * @param array|bool $ownListSourcePrototype 
     * - Array to enable Pwg_Data_Field_AutoComplete to create ownListSource and use it;
     * - FALSE to remove prototype and to DO NOT create ownListSource
     */
    protected function setOwnListSourcePrototype($ownListSourcePrototype) {
        if (is_array($ownListSourcePrototype)) {
            if (!isset($ownListSourcePrototype['class'])) $ownListSourcePrototype['class'] = 'Pwg_Data_Source';
            if (!isset($this->associations['listSource'])) $this->associations['listSource'] = 'ownListSource'; 
        }
        $this->ownListSourcePrototype = $ownListSourcePrototype;
    }        
    
    function setReadOnly($readOnly) {
        $readOnly = (bool) $readOnly;
        if (($oldReadOnly = $this->readOnly) !== $readOnly) {
            $this->readOnly = $readOnly;    
            if ($this->editor) {
                if (method_exists($this->editor, 'setReadOnly'))  
                    $this->editor->setReadOnly($readOnly);
            } 
            else $this->controlPrototypes['editor']['readOnly'] = $readOnly;
        }
    }

    function getReadOnly() {
        return $this->readOnly;
    }    
    
    function setDisabled($disabled) {
        $disabled = (bool) $disabled;
        if (($oldDisabled = $this->disabled) !== $disabled) {
            $this->disabled = $disabled;    
            if ($this->editor) {
                if (method_exists($this->editor, 'setDisabled'))  
                    $this->editor->setDisabled($disabled);
            } 
            else $this->controlPrototypes['editor']['disabled'] = $disabled;
        }
    }

    function getDisabled() {
        return $this->disabled;
    }

    function setLabelExpression($labelExpression) {
        if ($labelExpression !== ($oldLabelExpression = $this->labelExpression)) {
            $this->labelExpression = $labelExpression;
            $this->intReset();
        }
    }

    function getLabelExpression() {
        return $this->labelExpression;
    }   
    
    protected function setMultipleValuesSeparator($multipleValuesSeparator) {
        $this->multipleValuesSeparator = $multipleValuesSeparator;
    }

    function getMultipleValuesSeparator() {
        return $this->multipleValuesSeparator;
    }

    protected function explode($foo) {
        $foos = explode($this->multipleValuesSeparator, $foo);
        $res = array();
        foreach ($foos as $fooItem) $res[] = trim($fooItem);
        return $res;
    }

    protected function implode($foo) {
        if (is_array($foo)) {
            $implo = strlen($this->multipleValuesGlue)? $this->multipleValuesGlue : $this->multipleValuesSeparator; 
        }
        $res = implode($implo, $foo);
        return $res;
    }
    

    protected function setMultipleValuesGlue($multipleValuesGlue) {
        $this->multipleValuesGlue = $multipleValuesGlue;
    }

    function getMultipleValuesGlue() {
        return $this->multipleValuesGlue;
    }

    function setClearTextWhenNotInList($clearTextWhenNotInList) {
        $this->clearTextWhenNotInList = $clearTextWhenNotInList;
    }

    function getClearTextWhenNotInList() {
        return $this->clearTextWhenNotInList;
    }
    
    function setEmptyValue($emptyValue) {
        if ($emptyValue !== ($oldEmptyValue = $this->emptyValue)) {
            $this->emptyValue = $emptyValue;
            $this->emptyValueIsSet = true;
        }
    }
    
    function clearEmptyValue() {
        $this->emptyValue = null;
        $this->emptyValueIsSet = false;
    }

    function getEmptyValue() {
        return $this->emptyValue;
    }    
    
    function setClearEmptyValue($val) {
        if ((bool) $val) $this->clearEmptyValue();
    }
    
}

?>
