<?php

interface Pwg_I_Web_Front {
    
    /**
     * Should map incomplete JS or CSS path to URL where resource is actually located according to object's presets.
     * Example of such strings are:
     * - '{YUI}/yahoo-dom-event/yahoo-dom-event.js',
     * - 'http://foo/bar.js',
     * - 'widgets.css'
     * 
     * Most time these are the strings returned by Pwg_I_Control::getAssetLibs()
     * 
     * @param string $jsOrCssLinkWithPlaceholders
     */
    function getJsOrCssUrl ($jsOrCssLinkWithPlaceholders);
    
    /**
     * Should return URL that allows user to 'reset' dialogs to their initial state
     * @return string
     */
    function getResetUrl();
    
    /**
     * @return array List of assets that were shown upon initial page rendering (needed to prevent unnecessary loading of inline assets)
     */ 
    function getInitiallyLoadedAssets();

    function applyHacksToAssetLibs($assetLibs);

    function saveSessionData($dontClose = false);
    
}

?>