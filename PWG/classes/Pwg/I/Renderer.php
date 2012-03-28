<?php

interface Pwg_I_Renderer {
    
    function renderAssets(array $assets);
    
    function renderContainer($containerHtml);
    
    function renderInitializer(Pwg_Js_Initializer $initializer);
    
}

?>