<?php

class Pd_ExamplePart {
    
    protected $filename = false;

    protected $id = false;

    protected $startLine = false;

    protected $endLine = false;

    protected $content = false;

    protected $tags = array();
    
    protected $comment = false;
    
    /**
     * @var Pd_ExampleFile
     */
    protected $exampleFile = false;

    function setFilename($filename) {
        if ($this->filename !== false) throw new Exception("Can setFilename() only once!");
        $this->filename = $filename;
    }

    function getFilename() {
        if ($this->filename === false && $this->exampleFile) return $this->exampleFile->getFilename();
        return $this->filename;
    }

    function setExampleFile(Pd_ExampleFile $exampleFile) {
        $this->exampleFile = $exampleFile;
    }

    /**
     * @return Pd_ExampleFile
     */
    function getExampleFile() {
        if ($this->exampleFile === false) {
            if (!$this->filename) throw new Exception("filename not provided");
            $this->exampleFile = new Pd_ExampleFile();
            $this->exampleFile->setFilename($this->filename);
        }
        return $this->exampleFile;
    }
    
    function setId($id) {
        if ($this->id !== false) throw new Exception("Can setId() only once!");
        $this->id = $id;
    }

    function getId() {
        return $this->id;
    }

    function setStartLine($startLine) {
        if ($this->startLine !== false) throw new Exception("Can setStartLine() only once!");
        $this->startLine = $startLine;
    }

    function getStartLine() {
        return $this->startLine;
    }

    function setEndLine($endLine) {
        if ($this->endLine !== false) throw new Exception("Can setEndLine() only once!");
        $this->endLine = $endLine;
    }

    function getEndLine() {
        return $this->endLine;
    }

    function getContent() {
        return implode("", $this->getExampleFile()->getLines($this->startLine, $this->endLine));
    }

    function getHighlighted() {
        return implode("\n", array_slice($this->getExampleFile()->getHighlightedLines($this->startLine, $this->endLine), 1, -1));
    }

    function setTags($tags) {
        if (!is_array($tags)) $tags = preg_split("/\W+/", trim($tags));
        $this->tags = $tags;
    }

    function getTags() {
        return $this->tags;
    }

    function setComment($comment) {
        $this->comment = $comment;
    }

    function getComment() {
        return $this->comment;
    }
    
}