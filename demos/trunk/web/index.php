<?php

require_once(dirname(__FILE__).'/bootstrap.php');

$app = Pd_PaxDemos::getInstance();
$app->processRequest();


/*
 $f = new Pd_ExampleFile(dirname(__FILE__).'/../classes/Example/HelloWorld.php');
 
echo "<link rel='stylesheet' type='text/css' href='assets/hyperlight/colors/zenburn.css' />";

foreach ($f->getParts() as $name => $part) {
    echo "<h2>$name</h2>";
    echo '<pre class="source-code php">'.$part->getHighlighted().'</pre>';
}
 */