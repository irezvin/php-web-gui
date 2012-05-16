<?php 

abstract class Pd_Description {

    var $exampleBody = false;
    var $exampleClass = false;
    var $resetUrl = false;
    var $sourceUrl = false;
    
    /**
     * @var Pd_Index
     */
    protected $index = false;
    
    protected $exampleBodyShown = false;

    function setIndex(Pd_Index $index) {
        if ($this->index !== false) throw new Exception("Can setIndex() only once!");
        $this->index = $index;
    }

    /**
     * @return Pd_Index
     */
    function getIndex() {
        return $this->index;
    }
    
    function getTitle() {
        return $this->exampleClass;
    }
    
    function showBrief() {
    }
    
    abstract function showDescription();
    
    function showSource($partName = false) {
        if ($partName === false) $res = implode("\n", $this->getExampleFile()->getHighlightedLines());
            else {
                $parts = $this->getExampleFile()->getParts();
                if (isset($parts[$partName])) $res = $parts[$partName]->getHighlighted();
                else $res = '<div class="error">No such example part: '.$partName." in ".$this->getExampleFile()->getFilename().'</div>';
            }
            
        if ($partName !== false) {
            $res = "<pre class='source-code php'>$res</pre>";
        }
        echo $res;
          
    }
    
    function showExampleLink($className, $text = false) {
        if ($text === false) $text = $className;
        $res = Ac_Util::mkElement('a', $className, array('href' => $this->index->getUrl(array('example' => $className), false)));
        return $res;
    }
    
    /**
     * @var Pd_ExampleFile
     */
    protected $exampleFile = false;

    /**
     * @return Pd_ExampleFile
     */
    function getExampleFile() {
        if ($this->exampleFile === false) {
            if (!strlen($this->exampleClass)) throw new Exception("\setExampleClass() first");
            $c = new ReflectionClass($this->exampleClass);
            $f = $c->getFileName();
            $this->exampleFile = new Pd_ExampleFile($f);
        }
        return $this->exampleFile;
    }

    final function getDescription() {
        ob_start(); $this->showDescription(); return ob_get_clean();
    }
    
    final function getBrief() {
        ob_start(); $this->showBrief(); return ob_get_clean();
    }

    function getSourceShown() {
        return false;
    }

    function showResetLink($text = 'Reset to intial state') {
?><a href='<?php echo htmlspecialchars($this->resetUrl, ENT_QUOTES); ?>'><?php echo $text; ?></a><?php        
    }
    
    function showSourceLink($text = 'View source') {
?><a href='<?php echo htmlspecialchars($this->sourceUrl, ENT_QUOTES); ?>'><?php echo $text; ?></a><?php        
    }
    
    function showCoolExampleBody() {
?>
        <div class='exampleBody'>
<?php   if (strlen($this->resetUrl) || strlen($this->sourceUrl)) { ?>
            <div class='links'>
<?php   if (strlen($this->sourceUrl)) { ?>
            <span class='source'><?php $this->showSourceLink(); ?></span>
<?php   } ?>            
<?php   if (strlen($this->resetUrl)) { ?>
            <span class='resetLink'><?php $this->showResetLink(); ?></span>
<?php   } ?>            
            </div>
<?php   } ?>            
            <div class='body'>
<?php       ob_start(); $this->showExampleBody(); $foo = ob_get_clean(); ?>
                
                <?php Ac_Indent::s($foo); ?>
                
            </div>
        </div>
<?php
    }
    
    final function showExampleBody() {
        $this->exampleBodyShown = (bool) strlen($this->exampleBody);
        echo $this->exampleBody;
    }
    
    final function getExampleBodyShown() {
        return $this->exampleBodyShown;
    }
    
}