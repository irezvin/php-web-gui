window.Pwg_CommandReceiver = function(options) {

    window.Pwg_Core.apply(this, arguments);
	
};


Pwg_Util.augment(window.Pwg_CommandReceiver, {
	ORIGIN_API: 'api',
	ORIGIN_HISTORY: 'history',
	ORIGIN_USER: 'user',
	ORIGIN_INIT: 'init',
	ORIGIN_ANCHOR: 'anchor',

	yuiHistoryFrameId: 'yuiHistoryFrame',
	yuiHistoryInputId: 'yuiHistoryInput',
	registry: [],
	initialState: null,

	handleStateChange: function(state, origin) {
		if (!state) {
			var s = Pwg_Util.toString(document.location.hash);
			if (s.length > 1) state = s.slice(1);
		}
		var pcr = window.Pwg_CommandReceiver;
	    if (!origin) origin = pcr.ORIGIN_API;
	    for (var i = 0; i < pcr.registry.length; i++) {
	        pcr.registry[i].receiveCommand(state, pcr.ORIGIN_HISTORY);
	    }
	},

	receiveCommand: function(command) {
	    var pcr = window.Pwg_CommandReceiver;
	    for (var i = 0; i < pcr.registry.length; i++) {
	        pcr.registry[i].receiveCommand(command);
	    }
	}
});
 

if (YAHOO.util.Event && YAHOO.util.History) {
    YAHOO.util.Event.onDOMReady(function() {
        var pcr = window.Pwg_CommandReceiver;
        var initialState = YAHOO.util.History.getBookmarkedState("pcrState");
        YAHOO.util.History.register("pcrState", "", pcr.handleStateChange);
        // timeout allows other modules to register before history initialization
        window.setTimeout(function() {
            YAHOO.util.History.initialize(
                pcr.yuiHistoryInputId,
                pcr.yuiHistoryFrameId
            );
            if ((typeof initialState === 'string') && initialState.length) pcr.handleStateChange(initialState, pcr.ORIGIN_INIT);
            else {
            	var s = Pwg_Util.toString(document.location.hash);
            	if (s.length > 1) pcr.handleStateChange(s.slice(1), pcr.ORIGIN_INIT);
            }
        }, 300);
    });
}

window.Pwg_CommandReceiver.prototype = {
		
	/**
	 * @var bool
	 * Whether #anchors not found in the document that user has clicked on should be treated as commands 
	 */	
	treatAnchorsAsCommands: true,
	
	/**
	 * @var string
	 * Prefix of commands that receiver accepts
	 */
	commandPrefix: '',
	
	/**
	 * @var string
	 * Prefix of #anchors that receiver treats as commands 
	 */
	anchorPrefix: '',
	
	/**
	 * @var string
	 * id of current window (window.name = this.windowGroupId + '_' + this.windowId)
	 */
	windowId: '',
	
	windowGroupId: '',
	
	checkClicksInsideAnchors: false,
	
	setWindowId: function(value) {
		if (value === undefined) value = this.windowId;
			else this.windowId = value;
		window.name = this.calcWindowId(value); 
	},
	
	calcWindowId: function(windowId) {
		if (windowId === undefined) windowId = this.windowId;
		var sWindowId = Pwg_Util.toString(windowId);
		var sWindowGroupId = Pwg_Util.toString(this.windowGroupId);
		if (sWindowId.length && sWindowGroupId.length) sWindowId = sWindowGroupId.length + '_' + sWindowId;
		return sWindowId;
	},
	
	sendCommand: function(windowId, command, url) {
        if (!url) url = '';
        url += '#pcrState=' + this.addHash(command);
        var windowName = this.calcWindowId(windowId);
//      console.log('windowName is ' + windowName);
        var sWnd = window.open(url, windowName);
//        YAHOO.util.Event.on(sWnd, 'load', function() {
//            console.log("Loaded");
//            if (this.Pwg_CommandReceiver) this.Pwg_CommandReceiver.receiveCommand(command);
//        });
	},
    
    addHash: function(command) {
        return Math.round(Math.random() * 1000) + '__' + this.stripHash(command);
    },
    
    stripHash: function(command) {
        var sCommand = Pwg_Util.toString(command);
        var sepPos = sCommand.indexOf('__', 0);
        var res = sCommand;
        if (sepPos >= 0) res = sCommand.slice(sepPos + 2);
        return res;
    },

    receiveCommand: function(commandWithOptionalHash, origin) {
        if (!origin) origin = window.Pwg_CommandReceiver.ORIGIN_API;
        var commandWithoutHash = this.stripHash(commandWithOptionalHash);
        if ((commandWithoutHash === commandWithOptionalHash) && (origin === window.Pwg_CommandReceiver.ORIGIN_HISTORY))
        	origin = window.Pwg_CommandReceiver.ORIGIN_USER;
        
        var sCommandPrefix = Pwg_Util.toString(this.commandPrefix);
        var res = false
        if (!sCommandPrefix.length || (commandWithoutHash.indexOf(sCommandPrefix) === 0)) {
            res = true;
        	this.handleCommand(commandWithoutHash, origin);
        }
        return res;
    },

    handleCommand: function(command, origin) {
        this.sendMessage('commandReceived', command, origin);
        this.doHandleCommand(command, origin);
    },

    doHandleCommand: function(command, origin) {
    },

    initialize: function(options) {
        window.Pwg_Core.prototype.initialize.apply(this, arguments);
        if (options.doHandleCommand && (typeof options.doHandleCommand === 'function')) 
        		this.doHandleCommand = options.doHandleCommand;
        if (Pwg_Util.toString(this.windowId).length) this.setWindowId();
        this.setTreatAnchorsAsCommands();
        window.Pwg_CommandReceiver.registry.push(this);
    },
    
    setTreatAnchorsAsCommands: function(value) {
    	if (value === undefined) value = this.treatAnchorsAsCommands;
    	if (value) {
    		YAHOO.util.Event.addListener(window.document, 'click', this.handleDocumentClick, null, this);
    	} else {
    		YAHOO.util.Event.removeListener(window.document, this.handleDocumentClick);
    	}
    },
    
    handleDocumentClick: function(event) {
    	var href = '';
    	for (var src = event.srcElement? event.srcElement : event.target; src; src = src.parentNode) {
                try {
                    href = YAHOO.util.Dom.getAttribute(src, 'href');
                } catch(e) {
                    
                }
                if ((typeof href === 'string') && (href.slice(0, 1) === '#')) {
                        if (!this.anchorPrefix.length || (href.indexOf(this.anchorPrefix) === 1)) {
                                //console.log("Found command", href);
                                if (this.receiveCommand(href.slice(1 + this.anchorPrefix.length), window.Pwg_CommandReceiver.ORIGIN_ANCHOR)) {
                                        YAHOO.util.Event.stopEvent(event);
                                }
                        }
                }
    		if (!this.checkClicksInsideAnchors) break;
    	}
    }
	
};

Pwg_Util.extend (Pwg_CommandReceiver, Pwg_Core);