<?php

class Description_Label extends Pd_Description {
    
    function getTitle() {
        return 'Labels example';
    }
    
    function showBrief() {
?>
        Shows different examples of labels usage.
<?php
    }
    
    function showDescription() {
        $this->showCoolExampleBody();
?>
        <p>
            Pmt_Label represents a DIV or a SPAN with HTML code in it. Label can respond to events (mostly clicks), its content and style
            can be changed.
        </p>

        <p>
            One of notable properties of a label is its ability to intercept clicks inside a label if an HREF of A element starts with '##'.
            To do that, set <em>allowHrefClicks</em> property to true:
            <?php $this->showSource('clickableDecl'); ?>
            and then intercept HREF in a handler function:
            <?php $this->showSource('clickableHandler'); ?>

            Since Pmt_Label is a Pmt_Element descendant, it can handle most of DOM events, even hover and out can be attached (of course
            it will respond with some delay):
            <?php $this->showSource('hoverHandler'); ?>
        </p>

<?php
    }
    
}