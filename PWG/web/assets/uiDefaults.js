Pwg_UiDefaults = {
		
	override: function(options) {
		if (typeof(options) === 'object') Pwg_Util.override(this, options);
	},
	
	pollDelay: null, // protocol default
	
	clickDelay: 100,
	
	dblClickDelay: 150,
	
	typeDelay: 100,
	
	renderDelay: 300,
	
	fastRenderDelay: 50,
	
	reportDelay: 25
		
};