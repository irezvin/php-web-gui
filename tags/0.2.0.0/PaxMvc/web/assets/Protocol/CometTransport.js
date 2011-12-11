Pm_Protocol_CometTransport = function(options) {

	this._unsent = [];
	this._sent = [];
	
	if (window.Pmt_UiDefaults && window.Pmt_UiDefaults.pollDelay !== null)
		this.pollDelay = window.Pmt_UiDefaults.pollDelay;
	
	Pm_Protocol_Transport.call(this, options);
};

Pm_Protocol_CometTransport.prototype = {
	
	msgVar: 'messages',
	checkComplete: false,
	cometParamSuffix: 'comet=1',
	sid: null,
	maxErrors: 3,
	pollDelay: 50,
	
	_lastResponseText: null,
	_nErrors: 0,
	_comet: null,
	
	/**
	 * Array of messages that are processed on the server
	 */
	_unsent: null,
	
	_sent: null,
	
	_dc: null,
	
	reset: function() {
		this._axRequest = null;
		this._nErrors = 0;
		this.protocol.notifyTransportStatusChanged();
	},
	
	isRequestPending: function() {
		return !!this.protocol.getOutboundMessages().length;
	},
	
	notifyMessagePushed: function() {
		this._checkAndPoll();
	},
	
	notifyProtocolInitialized: function() {
		if (this.pollDelay) this._dc = new Pmt_Util.DelayedCall(this._immediateCheckAndPoll, null, this, [], this.pollDelay, false);
		Pm_Protocol_Transport.prototype.notifyProtocolInitialized.call(this);
		if (!this.checkComplete) this._checkAndPoll();
	},
	
	_axComplete: function(ajax) {
		var response = null;
		try {
			eval("response = " + ajax.responseText + ";");
		} catch(e) {
		}
		if (!this.checkComplete && response == 1) this.checkComplete = true;
		if (response != 2) // ok message
			this._axError(ajax);
		this._axRequest = null;
	},
	
	_axError: function(ajax) {
		this._errorCallback(ajax, null, ajax.responseText);
		if (ajax.outboundMessages && ajax.outboundMessages instanceof Array) this._unsent = this._unsent.concat(ajax.outboundMessages);
	},
	
	_sendRequest: function() {
		var q = {
				sid: this.sid
		};
		q[this.msgVar] = [].concat(this._unsent);

        this._axRequest = YAHOO.util.Connect.asyncRequest('POST', this.protocol.serverUrl, {
            success: this._axComplete,
            failure: this._axError,
            scope: this
        }, Pmt_Util.makeQuery(q, '', true));

        this._sent = this._sent.concat(this._unsent);
        this._unsent = [];
        this.protocol._updateStatus();
        this._checkRunComet();
	},
	
	_checkRunComet: function() {
		if (!this._comet && this.protocol.getOutboundMessages().length) {
			this._startComet();
		}
	},
	
	_startComet: function() {
		if (!this._comet) {
			var cmtUrl = '' + this.protocol.serverUrl;
			if (cmtUrl.indexOf('?') < 0) cmtUrl += '?';
				else cmtUrl += '&';
			cmtUrl += this.cometParamSuffix;
			this._comet = new Pmt_Comet(cmtUrl, this._cometData, this._cometFinished, undefined, this, this._cometError);
			this._comet.connect();
		}
	},
	
	_cometData: function(string) {
	    var msgs = null, exception = null;
	    this._lastResponseText = string;
	    try {
	        eval("var msgs = " + string + ";");
	    } catch (e) {
	        msgs = null;
	        exception = e;
	    }
	    if (msgs == null) {
	        this._errorCallback(null, exception, string);
	    }
	    else {
	        if (msgs.errorPushData) {
	        	
	        	if (msgs.messages) this.protocol.processInbox(msgs.messages);
	        	
				/** Clear protocol outbox, our outbox */
	        	
	        	this._resetQueue();
	        	
	        	this.protocol.reportError(
						msgs.errorPushData,
						Pm_Protocol.ERROR_SERVER_EXCEPTION,
						null,
						false
				);
	        } else {
	    		if (msgs.processedIds) this._registerProcessedMessages(msgs.processedIds);
	    		if (msgs.messages) {
	    			this.checkComplete = true;
	    			this.protocol.processInbox(msgs.messages);
	    		}
	    	}
	    }
	},
	
	_registerProcessedMessages: function(messageIds) {
		var mi = [].concat(messageIds), 
		idx, 
		j, 
		found = false;
	
		for (var i = this._sent.length - 1; (i >= 0) && mi.length; i--) {
			if ((idx = Pmt_Util.indexOf(this._sent[i].msgId, mi)) >= 0) {
				this._sent.splice(i, 1);
				mi.splice(idx, 1);
				found = true;
				continue;
			}
		}
		this.protocol.reportOutboundProcessed(messageIds);
	},
	
	_cometError: function(comet, exception, response) {
		console.log("Comet error", comet, exception, response);
		this._comet = null;
		this._checkRunComet();
	},
	
	_cometFinished: function() {
		this._comet = null;
		this._checkRunComet();
	},
	
	
	_errorCallback: function(context, exception, responseText) {
		if (!responseText.length) return;
		this.protocol.notifyTransportStatusChanged();
		this._nErrors++;
		if (this._nErrors > this.maxErrors) {
			this.protocol.reportError(
				"Cannot parse server message:\n" + responseText,
				Pm_Protocol.ERROR_UNPARSABLE_RESPONSE,
				exception,
				true
			);
		} else {
			this._axRequest = null;
			this._checkAndPoll();
		}
	},
	
	_resetQueue: function() {
		this._unsent = [];
		this._sent = [];
		this.protocol.clearOutboundMessages();
	},
	
	_clearUnsent: function() {
		var ids = [];
		for (var i = this._unsent.length - 1; i >= 0; i--) {
			ids.push(this._unsent[i].id);
		}
		this.protocol.reportOutboundProcessed(ids);
		console.log(ids);
		this._unsent = [];
	},

	_immediateCheckAndPoll: function(forceEmpty) {
		var ob = this.protocol.getOutboundMessages(), obl = ob.length;
		var a = this._unsent.concat(this._sent), al = a.length;
		var toSend = [];
		for (var i = 0; i < obl; i++) {
			var notFound = true;
			for (var j = al - 1; j >= 0; j--) {
				if (a[j].msgId == ob[i].msgId) {
					notFound = false;
					//al -= 1;
					break;
				}
			}
			if (notFound) toSend.push(ob[i]);
		}
		this._unsent = this._unsent.concat(toSend);
		if (this._unsent.length || !this.checkComplete || forceEmpty) this._sendRequest();
	},
	
	_checkAndPoll: function(forceEmpty) {
		if (this._dc) this._dc.callWithArgs(forceEmpty);
			else this._immediateCheckAndPoll(forceEmpty);
	}
		
};

Pmt_Util.extend (Pm_Protocol_CometTransport, Pm_Protocol_Transport);