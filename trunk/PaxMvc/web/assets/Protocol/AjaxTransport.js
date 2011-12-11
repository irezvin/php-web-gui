Pm_Protocol_AjaxTransport = function(options) {

	this._unsent = [];
	Pm_Protocol_Transport.call(this, options);
	
	if (window.Pmt_UiDefaults && window.Pmt_UiDefaults.pollDelay !== null)
		this.pollDelay = window.Pmt_UiDefaults.pollDelay;
	
};

Pm_Protocol_AjaxTransport.prototype = {
	
	msgVar: 'messages',
	checkComplete: false,
	maxErrors: 3,
	pollDelay: 25,
	
	_lastResponseText: null,
	_nErrors: 0,
	_axRequest: null,
	
	/**
	 * Array of messages that are processed on the server
	 */
	_unsent: null,
	
	_dc: null,
	
	reset: function() {
		this._axRequest = null;
		this._nErrors = 0;
		this.protocol.notifyTransportStatusChanged();
	},
	
	isRequestPending: function() {
		return !!this._axRequest;
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
		var msgs = null,
			exception = null;

		this._lastResponseText = ajax.responseText;
		
		try {
			eval("var msgs = " + ajax.responseText + ";");
		} catch (e) {
			msgs = null;
			exception = e;
		}
		
		if (msgs === null) {
			this._axErrorCallback(null, exception, ajax.responseText);
		} else {
			
			this.reset();
			
			/**
			 * In AjaxTransport each parsable response means that all sent messages are answered
			 */
			this._clearUnsent();
			
			if (msgs.errorPushData) {
				this.protocol.reportError(
					msgs.errorPushData,
					Pm_Protocol.ERROR_SERVER_EXCEPTION,
					null,
					false
				);
			}
		}
		
		if (!this.checkComplete && ((msgs === null) || !msgs.messages)) {
			if (msgs === 1) {
				this.checkComplete = true;
			} else {
				this.protocol.reportError(
					'Cannot establish connection with the server' 
						+ '\nServer URL: ' + this.protocol.serverUrl
						+ '\nResponse text: \n\n' + ajax.responseText,
					Pm_Protocol.ERROR_SERVER_UNAVAILABLE,
					exception,
					true
				);
			}
		} else {
			if (msgs && msgs.messages) {
				this.checkComplete = true;
				this.protocol.processInbox(msgs.messages);
			}
		}
		
		this._checkAndPoll();
		
	},
	
	_axErrorCallback: function(context, exception, responseText) {
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
	
	_clearUnsent: function() {
		var ids = [];
		for (var i = this._unsent.length - 1; i >= 0; i--) {
			ids.push(this._unsent[i].msgId);
		}
		this.protocol.reportOutboundProcessed(ids);
		this._unsent = [];
	},

	_immediateCheckAndPoll: function(forceEmpty) {
		var ob = this.protocol.getOutboundMessages();
		if (!this._unsent.length && ob.length) {
			this._unsent = this._unsent.concat(ob);
		}
		if (!this.isRequestPending() && (forceEmpty || this._unsent.length)) {
			this.protocol.notifyTransportStatusChanged();
            this._axRequest = YAHOO.util.Connect.asyncRequest('POST', this.protocol.serverUrl,
                {
                    success: this._axComplete,
                    failure: this._axErrorCallback,
                    scope: this
                },
                Pmt_Util.makeQuery(this._unsent, this.msgVar, true)
            );
//            this._axRequest = new Ajax.Request(this.protocol.serverUrl, {
//                postBody: Pmt_Util.makeQuery(this._unsent, this.msgVar, true),
//                onComplete: this._axComplete.bind(this),
//                onException: this._axErrorCallback.bind(this),
//                onFailure: this._axErrorCallback.bind(this)
//            });
		}
	},
	
	_checkAndPoll: function(forceEmpty) {
		if (this._dc) this._dc.callWithArgs(forceEmpty);
			else this._immediateCheckAndPoll(forceEmpty);
	}
		
};

Pmt_Util.extend (Pm_Protocol_AjaxTransport, Pm_Protocol_Transport);