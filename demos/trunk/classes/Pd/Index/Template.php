<?php

class Pd_Index_Template extends Ac_Template_Html {
 
    var $exampleBody = false;
    var $exampleClass = false;
    var $examplesIndex = false;
    
    /**
     * @var Pd_ExampleFile
     */
    var $exampleFile = false;
    
    /**
     * @var Pd_Description
     */
    var $exampleDescription = false;
    
    function addAssets() {
        $this->htmlResponse->addAssetLibs(array(
            '{PD}/hyperlight/colors/zenburn.css'   ,
            '{PD}/pd.css',
//            '{YUI}/reset/reset.css',
//            '{YUI}/fonts/fonts.css',
//            '{YUI}/base/base.css',
        ));
    }
    
    function showIndex() {
        $links = array();
        foreach ($this->examplesIndex as $item) {
            $attribs = array('href' => $item['url']);
            $liAttribs = array();
            if ($item['current']) $liAttribs['class'] = 'current';
            $links[] = Ac_Util::mkElement('li', Ac_Util::mkElement('a', $item['title'], $attribs), $liAttribs);
        }
        echo Ac_Util::mkElement('ul', "\n    ".implode("\n    ", $links));
    }
    
    function showWrap($body) {
        ob_start();
        
        $this->addAssets();
        
        $this->showIndex();
        $idx = ob_get_clean();
?>
        <div class='top'>
        </div>
        <div class='left'>
            <div class='logo'>
                <img src='assets/images/PWG.png' alt='PWG: PHP Web GUI' />
                <br /><small>Examples and tutorials</small>

            </div>
            <?php Ac_Indent::s($idx); ?>
            <div class='clr'></div>
        </div>
        <div class='main'>
            <?php Ac_Indent::s($body); ?>
            <div class='clr'></div>
        </div>
        <div class='footer'>
            &copy; 2012 Ilya Rezvin &middot; Google Code: <a href='http://code.google.com/p/php-web-gui/'>PWG</a>, <a href='http://code.google.com/p/avancore/'>Avancore</a>
        </div>
        
<?php
    }

    function showExampleSrc() {
        $c = new ReflectionClass($this->exampleClass);
        $f = $c->getFileName();
        $xf = new Pd_ExampleFile($f);
        
        // $this->addAssetLibs('{PD}/vendor/hyperlight/colors/zenburn.css'); // FIXME
        
        foreach ($xf->getParts() as $name => $part) {
            echo "<h2>$name</h2>";
            echo '<pre class="source-code php">'.$part->getHighlighted().'</pre>';
        }
        
    }
    
    function showExampleWithDescription() {
        
        ob_start();
        
        $this->htmlResponse->pageTitle[] = $this->exampleDescription->getTitle();
        
        if (!$this->exampleDescription) $this->showSimpleExample();
        else {
?>   
        <h1><?php echo $this->exampleDescription->getTitle(); ?></h1>
<?php   if (strlen($d = $this->exampleDescription->getBrief())) { ?>
        <div class='brief'>
            <?php echo $d; ?>
        </div>
<?php   } ?>        
        <div class='full'>
            <?php $this->exampleDescription->showDescription(); ?>
        </div>
<?php   if (!$this->exampleDescription->getExampleBodyShown()) $this->exampleDescription->showCoolExampleBody(); ?>
<?php

        $this->showWrap(ob_get_clean());

        }
    }
    
    function showSimpleExample() {
        echo "<link rel='stylesheet' type='text/css' href='assets/hyperlight/colors/zenburn.css' />";
?>
        <h1><?php echo $this->exampleClass; ?></h1>
        <p style="text-align: right"><a href='<?php echo $this->controller->application->getWebFront()->getResetUrl(); ?>'>Reset</a></p>
        <div style="border: 1px solid silver; padding: 1em">
            <?php echo $this->exampleBody; ?>
        </div>
        <?php $this->showExampleSrc(); ?>
<?php
    }
    
    function showSource() {
        $this->addAssets();
        echo implode("\n", $this->exampleFile->getHighlightedLines());
    }
    
    function showStart() {
        ob_start();
?>
        <h1>PWG examples</h1>
        <p>Select example from the list</p>
<?php        
        $this->showWrap(ob_get_clean());
    }
    
}
