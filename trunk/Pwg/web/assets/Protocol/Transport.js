Pm_Protocol_Transport = function(options) {
	
	if (options && ((typeof options) === 'object')) Pmt_Util.override(this, options);
	
};

Pm_Protocol_Transport.prototype = {
		
	protocol: null,
	
	notifyMessagePushed: function() {
		throw new Exception("Call to abstract function");
	},
	
	isRequestPending: function() {
		throw new Exception("Call to abstract function");
	},
	
	notifyProtocolInitialized: function() {
	}
	
};

