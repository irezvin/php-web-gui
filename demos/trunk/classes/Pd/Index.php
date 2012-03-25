<?php

/**
 * PaxDemos examples index
 */
class Pd_Index extends Ae_Legacy_Controller {

    var $_templateClass = 'Pd_Index_Template';
    
    /**
     * @var Pd_PaxDemos
     */
    var $application = false;
    
    var $descriptions = array();
    
    /**
     * Returns example controller classes
     */
    function listExamples() {
        $res = array(
            'Example_HelloWorld',
            'Example_Label'
        );
        return $res;
    }
    
    function getExamplesIndex() {
        $res = array();
        $curr = $this->getExample();
        foreach ($this->listExamples() as $className) {
            if ($d = $this->getDescription($className)) {
                $res[$className]  = array(
                    'title' => $d->getTitle(), 
                    'brief' => $d->getBrief(), 
                    'url' => $this->getUrl(array('example' => $className)),
                    'current' => $curr == $className,
                );
            }
        }
        return $res;
    }
    
    /**
     * @param type $class
     * @return Pd_Description
     */
    function getDescription($class = false) {
        $res = null;
        if (!strlen($class)) $class = $this->getExample();
        if (strlen($class)) {
            if (!isset($this->descriptions[$class])) {
                $descClass = str_replace('Example', 'Description', $class);
                if (class_exists($descClass, true)) {
                    $res = $this->descriptions[$class] = Ae_Autoparams::factory(array(
                        'class' => $descClass,
                        'index' => $this,
                        'exampleClass' => $class,
                    ), 'Pd_Description');
                }
            } else {
                $res = $this->descriptions[$class];
            }
        }
        return $res;
    }
    
    function getExample() {
        $res =  $this->_context->getData('example');
        if (!strlen($res) || !in_array($res, $this->listExamples()) || !class_exists($res)) {
            $res = false;
        }
        return $res;
    }
    
    protected function setupExample() {
        $res = false;
        if (strlen($class = $this->getExample())) {
            $wf = $this->application->getWebFront();
            $u = new Ae_Url($wf->baseUrl);
            $u->query['example'] = $class;
            $wf->baseUrl = ''.$u;
            $u->query['reset'] = 1;
            $wf->setResetUrl(''.$u);
            $wf->setSessionSuffix($class);
            $wf->setTopController($class);
            $res = $class;
        }
        return $res;
    }
    
    function execute() {
        if (($class = $this->setupExample())) {
            ob_start();
            $resp = $this->application->getWebFront()->getResponse();
            $buf = ob_get_clean();
            if (strlen($buf) && !strlen($resp->content) && $resp->noHtml) {
                $this->_response->content = $buf;
            } else {
                $this->_tplData['exampleBody'] = $buf;
                if (($d = $this->getDescription($class))) {
                    $d->exampleBody = $resp->content;
                    $d->resetUrl = $this->application->getWebFront()->getResetUrl();
                    $d->sourceUrl = ''.$this->getUrl(array('action' => 'source', 'class' => $class), false);
                    $this->_tplData['exampleDescription'] = $d;
                }
                $this->_tplData['exampleClass'] = $class;
                $this->_tplData['examplesIndex'] = $this->getExamplesIndex();
                $this->_templatePart = 'exampleWithDescription';
            }
            $this->_response->mergeWithResponse($resp);
        } else {
            $this->_tplData['examplesIndex'] = $this->getExamplesIndex();
            $this->_templatePart = 'start';
        }
    }
    
    function executeSource() {
        if (($class = $this->getExample())) {
            $rc = new ReflectionClass($class);
            $d = new Pd_ExampleFile($rc->getFileName());
            $this->_tplData['exampleClass'] = $class;
            $this->_tplData['exampleFile'] = $d;
            $this->_templatePart = 'source';
        }
    }
    
}
