<?php

    
/**
 * Format of parts in the example file:
 * // {partId [tags]} [partComment]
 * ,,,
 * part content
 * ...
 * // {/partId}
 */
class Pd_ExampleFile {
    
    protected $filename = false;
    
    protected $parts = false;
    
    protected $parsed = false;
    
    protected $lines = false;

    protected $highlightedLines = false;
    
    protected $problems = array();
    
    protected $loaded = false;
    
    /**
     * @var Pd_Highlighter
     */
    protected $highlighter = false;
    
    var $partBeginRx = '#^\s*//\s*\{\s*(?P<partId>\w+)(\s*(?P<tags>[^}]+))?\}(?P<partComment>.*)$#';
    
    var $partEndRx = '#^\s*//\s*\{\s*/(?P<partId>\w+)\s*\}#';
    
    var $partClass = 'Pd_ExamplePart';
    
    function __construct($filename = false) {
        $this->filename = $filename;
    }
    
    function setFilename($filename) {
        if ($this->filename !== false) throw new Exception("Can setFilename() only once!");
        $this->filename = $filename;
    }

    function getFilename() {
        return $this->filename;
    }
    
    protected function load() {
        if (!$this->loaded) {
            $this->lines = file($this->filename);
            $this->highlightedLines = $this->getHighlighter()->highlight($this->lines);
            $this->loaded = true;
        }
        return $this->loaded;
    }

    protected function parse() {
        if (!$this->parsed) {
            $this->load();
            
            $lines = $this->getLines();
            $offsets = array();

            foreach ($lines as $offset => $line) {
                if (preg_match($this->partBeginRx, $line, $matches)) {
                    if (isset($offsets[$matches['partId']]['begin'])) {
                        $this->addProblem("Part '{$matches['partId']}' beginning was already found at line #{$offsets[$matches['partId']]['begin']['line']}, ignoring start tag at line {$offset}");
                    }
                    $offsets[$matches['partId']]['begin'] = array(
                        'line' => $offset, 
                        'partId' => $matches['partId'],
                        'tags' => $matches['tags'],
                        'partComment' => $matches['partComment']
                    );
                } elseif (preg_match($this->partEndRx, $line, $matches)) {
                    if (isset($offsets[$matches['partId']]['end'])) {
                        $this->addProblem("Part '{$matches['partId']}' ending was already found at line #{$offsets[$matches['partId']]['end']['line']}, ignoring end tag at line {$offset}");
                    }
                    $offsets[$matches['partId']]['end'] = array(
                        'line' => $offset,
                        'partId' => $matches['partId']
                    );
                }
            }

            $this->parts = array();

            foreach ($offsets as $partId => $info) {
                if (isset($info['begin']) && !isset($info['end'])) {
                    $this->addProblem("Part '{$partId}' begins at line #{$info['begin']['line']} but does not have end tag");
                } elseif (isset($info['end']) && !isset($info['begin'])) {
                    $this->addProblem("Part '{$partId}' ends at line #{$info['end']['line']} but does not have begin tag");
                } else {
                    $part = new $this->partClass;
                    $part->setStartLine($info['begin']['line']);
                    $part->setEndLine($info['end']['line']);
                    $part->setExampleFile($this);
                    $part->setTags($info['begin']['tags']);
                    $part->setComment($info['begin']['partComment']);
                    $this->parts[$partId] = $part;
                }
            }
            
            $this->parsed = true;
        }
        return $this->parsed;
    }

    function getLines($from = false, $to = false, $highlighted = false) {
        if ($this->lines === false) {
            $this->load();
        }
        if ($highlighted) return $this->getHighlightedLines($from, $to);
        elseif ($from || $to) {
                if ($from === false) $from = 0;
                if ($to === false) $to = count($this->lines);
                return array_slice($this->lines, $from, $to - $from + 1);
        }
        return $this->lines;
    }

    function getHighlightedLines($from = false, $to = false) {
        if ($this->highlightedLines === false) {
            $this->load();
        }
        if ($from || $to) {
            if ($from === false) $from = 0;
            if ($to === false) $to = count($this->highlightedLines);
            return array_slice($this->highlightedLines, $from, $to - $from + 1);
        }
        return $this->highlightedLines;
    }    

    function setHighlighter(Pd_Highlighter $highlighter) {
        if ($this->highlighter !== false) throw new Exception("Can setHighlighter() only once!");
        $this->highlighter = $highlighter;
    }

    /**
     * @return Pd_Highlighter
     */
    function getHighlighter() {
        return $this->highlighter? $this->highlighter : Pd_Highlighter::getInstance();
    }    

    function getParts() {
        $this->parse();
        return $this->parts;
    }
    
    protected function addProblem($problem) {
        $this->problems[] = $problem;
        if ($this->raiseNotices) $this->raiseNotice($problem);
    }
    
    function getProblems() {
        return $this->problems;
    }
    
    protected $raiseNotices = false;
    
    protected function raiseNotice($problem) {
        trigger_error($problem, E_USER_NOTICE);
    }

    function setRaiseNotices($raiseNotices) {
        $this->raiseNotices = $raiseNotices;
        if ($this->raiseNotices && $this->problems) foreach ($this->problems as $problem) $this->raiseNotice($problem);
    }

    function getRaiseNotices() {
        return $this->raiseNotices;
    }    
    
}