window.Pwg_Transport = function(serverUrl, threadId) {

    if (window.YAHOO && window.YAHOO.util && window.YAHOO.util.Element)
    window.YAHOO.util.Element.prototype.DEFAULT_HTML_SETTER = function(value, key) {
        var el = this.get('element');

        if (el) {
            try {
                el[key] = value;
            } catch(e) {
                //throw(e);
            }
        }
    };
	
	if (threadId === undefined && serverUrl && ((typeof serverUrl) == 'object')) {
		console.log(serverUrl);
		Pwg_Util.override(this, serverUrl);
		console.log(this.serverUrl);
	} else {
    
		this.serverUrl = serverUrl;
		this.threadId = threadId? threadId : null;
		
	}
    this.initialize();

}

window.Pwg_Transport_Impl = function(options) {
	if (options && ((typeof options) == 'object')) Pwg_Util.override(this, options);
}

window.Pwg_Transport_Impl.prototype = {
		
	transport: null,
	
	pollServer: function(forceEmpty) {
		throw "Call to abstract function";
	},
	
	reset: function() {
		throw "Call to abstract function";
	},
	
	isErrorState: function() {
		throw "Call to abstract function";
	},
	
	setErrorState: function() {
		throw "Call to abstract function";
	},

    dataToQueryString: function(data, name) {
        if (data == undefined || data == null) return '';
        var res = '';
        if (name == undefined) name = '';
        if (data instanceof Array) {
            for (var i = 0; i < data.length; i++) {
                var r = this.dataToQueryString(data[i], name.length? name + '[' + i + ']' : i);
                if (r.length) res = res.length? res + '&' + r : r;
            }
        } else {
            if (typeof(data) == 'object') {
                var keys = $H(data).keys();
                for (var i = 0; i < keys.length; i++) {
                    var r = this.dataToQueryString(data[keys[i]], name.length? name + '[' + keys[i] + ']' : keys[i]);
                    if (r.length) res = res.length? res + '&' + r : r;
                }
            } else {
                res = name.length? name + '=' + encodeURIComponent(data): '' + encodeURIComponent(data);
            }
        }
        return res;
    }
		
}

window.Pwg_Transport_Impl_Ajax = function(options) {
	window.Pwg_Transport_Impl.call(this, options);
}

window.Pwg_Transport_Impl_Ajax.prototype = {
	    
	axRequest: null,
    messageVarName: 'messages',
    checkComplete: false,
    
    reset: function() {
    	this.axRequest = null;
	},
	
    axComplete: function(ajax) {
        var msgs = null, exception = null;
        this._lastResponseText = ajax.responseText;
        try {
            eval("var msgs = " + ajax.responseText + ";");
        } catch (e) {
            msgs = null;
            exception = e;
        }
        if (msgs == null) {
            this.axErrorCallback(null, exception, ajax.responseText);
        }
        else {
            if (msgs.errorPushData) {
            	this.transport.registerErrorPush(msgs.errorPushData);
            	this.reset();
            	this.transport.unsent = [];
            	return;
            }
        }
        if (!this.checkComplete && ((msgs === null) || !msgs.messages)) {
        	if (msgs === 1) {
        		this.checkComplete = true;
        	} else {
        		var s = '' + ajax.responseText;
        		this.transport.registerErrorPush(
        				'Cannot establish connection with the server.'
        				+ '\nServer URL: "' + this.transport.serverUrl + '"' 
        				+ '\nResponse text (length = ' + s.length + '):\n\n' + ajax.responseText
        		);
        	}
        } else {
        	if (msgs && msgs.messages) {
        		this.checkComplete = true;
        		this.transport.processServerData(msgs);
        	}
        }
    },
	
    axErrorCallback: function(context, exception, responseText) {
        this.transport.clearCursor();
        this.transport.errorCount++;
        if (this.transport.errorCount > this.transport.maxErrors) {
            this.transport.criticalError(context, exception, responseText);
        } else {
            this.axRequest = null;
            this.pollServer();
        }
    },
    
    isErrorState: function() {
		return this.axRequest === 'error';
	},
	
	setErrorState: function() {
		this.axRequest = 'error';
	},

    pollServer: function(forceEmpty) {
	    if (!this.axRequest) {
	        // fetch next part of the query
	        if (this.transport.outbox.length && (!this.transport.maxMessagesToSend || this.transport.unsent.length < this.transport.maxMessagesToSend)) {
	            var nMessages = this.transport.maxMessagesToSend? this.transport.maxMessagesToSend - this.transport.unsent.length : this.transport.outbox.length;
	            this.transport.moveMessagesToUnsent(nMessages);
	        }
	        if (this.transport.unsent.length || forceEmpty) {
	            this.transport.showBusyCursor();
	            this.axRequest = new Ajax.Request(this.transport.serverUrl, {
	                postBody: this.getPollPostBody(),
	                onComplete: this.axComplete.bind(this),
	                onException: this.axErrorCallback.bind(this),
	                onFailure: this.axErrorCallback.bind(this)
	            });
	        }
	    }
    },
    
    getPollPostBody: function() {
    	var val;
    		if (!this.transport.unsent.length) val = '';
    		else val = this.transport.unsent;
        return this.dataToQueryString(val, this.messageVarName);
    },
    
}

	Pwg_Util.extend (window.Pwg_Transport_Impl_Ajax, window.Pwg_Transport_Impl);

window.Pwg_Transport_Impl_Hybrid = function(options) {
	window.Pwg_Transport_Impl.call(this, options);
	this.sendQueue = [];
}

window.Pwg_Transport_Impl_Hybrid.prototype = {
	
	comet: null,
	lastMsgId: 0,
	cometParamSuffix: 'comet=1',
	sessionId: null,
    messageVarName: 'messages',
	_lastResponseText: false,
	errorState: false,
		
	handleCometData: function(string) {
		console.log('comet data', string);
	    var msgs = null, exception = null;
	    this._lastResponseText = string;
	    try {
	        eval("var msgs = " + string + ";");
	    } catch (e) {
	        msgs = null;
	        exception = e;
	    }
	    if (msgs == null) {
	        this.errorCallback(null, exception, string);
	    }
	    else {
	        if (msgs.errorPushData) this.transport.registerErrorPush(msgs.errorPushData);
	    	else {
	    		if (msgs.messages) this.checkComplete = true;
	    		this.transport.processServerData(msgs);
	    	}
	    }
	},
	
	handleCometDisconnect: function() {
		console.log('comet disconnect', this);
		this.comet = null;
	},
	
	cometConnect: function() {
		if (!this.comet) {
			var cmtUrl = '' + this.transport.serverUrl;
			if (cmtUrl.indexOf('?') < 0) cmtUrl += '?';
				else cmtUrl += '&';
			cmtUrl += this.cometParamSuffix;
			this.comet = new Pwg_Comet(cmtUrl, this.handleCometData, this.handleCometDisconnect, undefined, this);
			this.comet.connect();
		}
	},
	
	axComplete: function() {
	},
	
    pollServer: function(forceEmpty) {
	    if (!this.axRequest) {
	        // fetch next part of the query
	        if (this.transport.outbox.length && (!this.transport.maxMessagesToSend || this.transport.unsent.length < this.transport.maxMessagesToSend)) {
	            var nMessages = this.transport.maxMessagesToSend? this.transport.maxMessagesToSend - this.transport.unsent.length : this.transport.outbox.length;
	            this.transport.moveMessagesToUnsent(nMessages);
	        }
	        if (this.transport.unsent.length || forceEmpty) {
	        	for (var i = 0; i < this.transport.unsent.length; i++) {
	        		if (this.transport.unsent[i].msgId === undefined) this.transport.unsent[i].msgId = ++this.lastMsgId;
	        	}
	            new Ajax.Request(this.transport.serverUrl, {
	                postBody: this.getPostBody(),
	                onComplete: this.axComplete.bind(this),
	                onException: this.errorCallback.bind(this),
	                onFailure: this.errorCallback.bind(this)
	            });
	            this.transport.unsent = [];
	            this.cometConnect();
	        }
	    }
    },
    
    getPostBody: function() {
    	var val;
    		if (!this.transport.unsent.length) val = '';
    		else val = this.transport.unsent;
    	var v = {};
    	v[this.messageVarName] = val;
    	v['sid']= this.sessionId;
    	console.log(v);
        return this.dataToQueryString(v);
    },
    
    errorCallback: function(context, exception, responseText) {
        this.transport.errorCount++;
        if (this.transport.errorCount > this.transport.maxErrors) {
            this.transport.criticalError(context, exception, responseText);
        } else {
            this.pollServer();
        }
    },
    
    isErrorState: function() {
		return this.errorState == null;
	},
	
	setErrorState: function() {
		this.errorState = true;
	},
	
	reset: function() {
		this.errorState = false;
	}
    
}

Pwg_Util.extend (window.Pwg_Transport_Impl_Hybrid, window.Pwg_Transport_Impl);

window.Pwg_Transport.prototype = {

	impl: null,
		
    serverUrl: null,
    threadId : null,
    primaryThreadId: null,
    outbox: [],
    unsent: [],
    inbox: [],
    observers: [],
    observersByRecipient: [],
    currobserverIndex: 0,
    firstPollDelay: 100,
    minInterval: 100,
    maxInterval: 5000,
    maxMessagesToSend: 0,
    timerInterval: 500,
    lastPollTime: null,
    intervalHandle: null,
    pollMessage: null,
    errorCount: 0,
    maxErrors: 3,
    _errorDiv: false,
    _affectedObservers: [],
    _firstPollTimeout: false,
    _lastResponseText: null,
    busyCursor: 'progress',
    pauseCursor: 'wait',
    queueSeparator: {queueSeparator: true},

    initialize: function() {
    	if (!this.impl) this.impl = new Pwg_Transport_Impl_Ajax({transport: this});
    	this.impl.transport = this;
        if (this.timerInterval) {
            this.intervalHandle = window.setInterval(this.checkAndPoll.bind(this), this.timerInterval);
        }
        if (!this.impl.transport.checkComplete) this.pollServer(true);
    },
    
    showBusyCursor: function() {
    	//if (this.busyCursor) document.body.style.cursor = this.busyCursor;
    	$(document.documentElement).removeClassName('pauseCursor');
    	$(document.documentElement).addClassName('busyCursor');
    	this._blinkCursor();
    },
    
    showPauseCursor: function() {
    	$(document.documentElement).removeClassName('busyCursor');
    	$(document.documentElement).addClassName('pauseCursor');
    	this._blinkCursor();
    },
    
    clearCursor: function() {
    	//document.body.style.cursor = '';
    	$(document.documentElement).removeClassName('busyCursor');
    	$(document.documentElement).removeClassName('pauseCursor');
    	this._blinkCursor();
    },
    
    _blinkCursor: function() {
    	document.body.style.cursor = 'pause';
    	document.body.style.cursor = '';
    },
    
    getTime: function() {
        var d = new Date();
        return d.getTime();
    },
    
    setPollMessage: function(pollMessage) {
        this.pollMessage = pollMessage;
    },

    processServerData: function(serverData) {
        // if serverData is provided, add it to the inbox
        if (serverData && serverData.messages && (serverData.messages instanceof Array)) {
            this.inbox = this.inbox.concat(serverData.messages);
            this.inbox.push(this.queueSeparator);
        }
        
        var paused = false;
        
        while (this.inbox.length && !paused) {
            this.clearCursor();
            var msg = this.inbox[0];
            if (msg === this.queueSeparator) {
                this.inbox.splice(0, 1);
                for (var i = 0; i < this._affectedObservers.length; i++) {
                    var c = this._affectedObservers[i];
                    if (typeof c.notifyMessageQueueEnd == 'function')
                        c.notifyMessageQueueEnd();
                }
                this._affectedObservers = [];
            } else {
                try {
                    this.processInboundMessage(msg);
                    this.inbox.splice(0, 1);
                } catch (e) {
                    if (e === window.Pwg_Transport.pauseException) {
                        // do nothing
                        //console.log("Inbound queue paused");
                        paused = true;
                    } else {
                        this.clearCursor();
                        this.inbox.splice(0, 1);
                        throw e;
                    }
                }
            }
        }
        
        if (!this.inbox.length) {
            this.clearCursor();
            this.errorCount = 0;
            this.impl.reset();
            this.unsent = new Array();
        }
        
    },

    registerErrorPush: function(errorPushData) {
    	this.clearCursor();
        var closeFn = function() {
            this._errorDiv.style.display = 'none';
            var c, cc;
            cc = this._errorDiv.getElementsByTagName('pre');
            for (var i = cc.length - 1; i >= 0 ; i--) this._errorDiv.removeChild(cc[i]);
        }
        if (!this._errorDiv) {
            this._errorDiv = document.createElement('div');
            this._errorDiv.setAttribute('class', 'paxError');
            var lnk = document.createElement('a');
            lnk.appendChild(document.createTextNode('Clear & Close'));
            lnk.setAttribute('href', '#');
            this._errorDiv.appendChild(lnk);
            //$(this._errorDiv).observe('dblclick', closeFn.bind(this));
            $(lnk).observe('click', closeFn.bind(this));
            if (!document.body.firstChild) document.body.appendChild(this._errorDiv);
                else document.body.insertBefore(this._errorDiv, document.body.firstChild);
        }
        var n, p;
        n = document.createTextNode(errorPushData);
        p = document.createElement('pre');
        p.appendChild(n);
        this._errorDiv.appendChild(p);
        if (this._errorDiv.style.display == 'none') this._errorDiv.style.display = '';
    },
    
    processInboundMessage: function(msg) {
        var rcptId = msg.recipientId;
        
        // Capability of transport to accept other threads' messages
        if (msg.threadId) {
            var threadTransport = this.getThreadTransport(msg.threadId);
            if (! threadTransport instanceof Pwg_Transport) throw new Exception("Can't find transport of thread #" + msg.threadId);
            if (threadTransport !== this) return threadTransport.processInboundMessage(msg);
        }
        
        if (this.observersByRecipient['rcpt-' + rcptId]) {
            for (var i = 0; this.observersByRecipient['rcpt-' + rcptId] && (i < this.observersByRecipient['rcpt-' + rcptId].length); i++) {
                var odata = this.observersByRecipient['rcpt-' + rcptId][i];
                var hasAo = false;
                for (var j = 0; j < this._affectedObservers.length; j++) {
                    if (this._affectedObservers[j] === odata.listenerObject) { hasAo = true; break; }
                }
                if (!hasAo) this._affectedObservers.push(odata.listenerObject);
                odata.listenerFunction(msg.methodName, msg.params);
            }
        }
    },
    
    broadcast: function(methodName, params) {
        for (var i = 0; i < this.observers.length; i++) {
            if (typeof(this.observers[i][methodName]) == 'function') this.observers[i][methodName](params);
        }
    },
    
    criticalError: function(context, exception, responseText) {
        if (this.impl.isErrorState()) return;
        
    	var resetUrl = this.serverUrl;
        if (resetUrl.indexOf('?') <= 0) resetUrl += '?';
        	else resetUrl += '&';
        resetUrl += 'reset=1';
        var notice = 
        	'\n\nError! Critical error occured (I\'m not able to parse server response).'
			+ '\nPossible solutions:'
			+ '\n- press [F5] or [Ctrl-R] to reload application in it\'s last consistent state;'
			+ '\n- open URL at "' + resetUrl + '" to reset application.';
        if (exception !== undefined) notice += '\n\nLast javascript exception: ' + exception;
        if (responseText === undefined) responseText = this._lastResponseText;
        responseText = responseText + ''; 
        notice += '\n\nLast server response (length = ' + responseText.length + '):\n\n' + responseText;
    	this.registerErrorPush(notice);
    	
        this.impl.setErrorState();
        throw "Critical error count reached";
    },
    
    hasMessagesToSend: function() {
        return this.unsent.length || this.outbox.length;
    },
    
    pollServer: function(forceEmpty) {
        this.lastPollTime = this.getTime();
        this.impl.pollServer(forceEmpty);
    },
    
    checkAndPoll: function() {
        if (this._firstPollTimeout) {
            window.clearTimeout(this._firstPollTimeout);
            this._firstPollTimeout = false;
        }
        var t = this.getTime();
        if (!this.hasMessagesToSend() && this.pollMessage && this.maxInterval && (t - this.lastPollTime >= this.maxInterval)) {
            this.pushMessage(this.pollMessage.recipientId, this.pollMessage.methodName, this.pollMessage.params, true);
        }
        if (this.hasMessagesToSend() && (t - this.lastPollTime >= this.minInterval)) {
            this.pollServer();
        }
    },
    
    pushMessage: function(recipientId, methodName, params, dontPoll, threadId) {
        if (!dontPoll) dontPoll = false;
        if (!threadId && this.threadId) threadId = this.threadId;
        if (threadId && this.primaryThreadId) {
            var tt = this.getThreadTransport(this.primaryThreadId);
            if (tt) return tt.pushMessage(recipientId, methodName, params, dontPoll, threadId);
        }
        var msg = {'recipientId': recipientId, 'methodName': methodName, 'params' : params};
        if (threadId) msg.threadId = threadId;
        this.outbox.push(msg);
        if (!dontPoll) {
            if (this.firstPollDelay) {
                if (this._firstPollTimeout) window.clearTimeout(this._firstPollTimeout);
                this._firstPollTimeout = window.setTimeout(
                    function(foo) {return function() {foo.checkAndPoll();}} (this),
                    this.firstPollDelay
                );
            } else {
                this.checkAndPoll();
            }
        }
    },
    
    moveMessagesToUnsent: function(length) {
        this.unsent = this.outbox.slice(this.outbox.length - length, this.outbox.length);
        this.outbox = this.deleteFromArray(this.outbox, this.outbox.length - length, length);
    },
    
    deleteFromArray: function(arr, index, length) {
        if (length == undefined) length = 1;
        return arr.slice(0, index).concat(arr.slice(index+length));
    },
    
    observe: function(listenerFunction, recipientId, listenerObject) {
        if (typeof(listenerFunction) == 'object' && !listenerObject) {
            var o = listenerFunction;
            listenerObject = o;
            if (recipientId == undefined) recipientId = o.id;
            listenerFunction = function(methodName, params) {  
                o.handleServerMessage(methodName, params);
            }
            if (typeof(o.setTransport) == 'function') o.setTransport(this); else o.transport = this;
        }
        var observerIndex = this.currobserverIndex;
        var l = 0;
        this.observers[observerIndex] = {'recipientId': recipientId, 'listenerFunction': listenerFunction};
        if (!this.observersByRecipient['rcpt-'+recipientId])  {
            this.observersByRecipient['rcpt-'+recipientId] = new Array();
            l = 0;
        } else {
            l = this.observersByRecipient['rcpt-'+recipientId];
        }
        this.observersByRecipient['rcpt-'+recipientId][l] = {'observerIndex': observerIndex, 'listenerFunction': listenerFunction, 'listenerObject': listenerObject};
        //console.log("Observing", recipientId, listenerObject);
        Pwg_Debug.d('lifecycle', 'Observing', recipientId, listenerObject.jsClassName? ' [' + listenerObject.jsClassName + ']' : '');
        return observerIndex;
    },
    
    unobserve: function(observerIndex) {
        if (this.observers[observerIndex]) {
            var rcptId = this.observers[observerIndex];
            if (this.observersByRecipient['rcpt-'+rcptId]) {
                for (var i = 0; i < this.observersByRecipient['rcpt-'+rcptId].length; i++) {
                    if (this.observers[this.observersByRecipient['rcpt-'+rcptId][i].observerIndex] == observerIndex) 
                        delete (this.observers[this.observersByRecipient['rcpt-'+rcptId][i]]);
                }
            }
            Pwg_Debug.d('lifecycle', 'Unobserving', observerIndex);
            delete(this.observers[observerIndex]);
        } else {
            Pwg_Debug.d('lifecycle', 'WARN: this.observers[' + observerIndex + '] not found!');
        }
    },
    
    unobserveRecipient: function (recipientId) {
        if (this.observersByRecipient['rcpt-'+recipientId]) {
            Pwg_Debug.d('lifecycle', 'Unobserving recipient', recipientId);
            for (var i = 0; i < this.observersByRecipient['rcpt-'+recipientId].length; i++) {
                this.observers.splice(this.observersByRecipient['rcpt-'+recipientId][i].observerIndex, 1);
                //delete(this.observers[this.observersByRecipient['rcpt-'+recipientId][i].observerIndex]);
            }
            delete this.observersByRecipient['rcpt-'+recipientId];
        }
    },
    
    // 'Threads' support: other threads' transport should reside in the window memebers with the same name as the corresponding thread id.
    getThreadTransport: function(threadId) {
        var res = null;
        if (window[threadId] instanceof Pwg_Transport) res = window[threadId];
        return res;
    },
    
    pause: function() {
    	this.showPauseCursor();
        throw window.Pwg_Transport.pauseException; 
    },
    
    resume: function() {
        if (this.inbox.length) this.processServerData();
        this.clearCursor();
    },
    
	reportAffectedObserver: function(o) {
		this._affectedObservers.push(o);
	}
    
}

window.Pwg_Transport.pauseException = {pauseException: true}