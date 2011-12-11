<?php

interface Pm_I_Renderer {
    
    function renderAssets(array $assets);
    
    function renderContainer($containerHtml);
    
    function renderInitializer(Pm_Js_Initializer $initializer);
    
}

?>