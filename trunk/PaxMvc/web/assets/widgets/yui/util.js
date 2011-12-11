Pmt_Yui_Util = {
		
		getMaxZIndexByParent: function(el, zIndex) {
			if (!zIndex) zIndex = 0;
			while (el) {
				if (el.style) {
					var zi = parseInt(el.style.zIndex);
					if (zi != NaN && zi > zIndex) zIndex = zi;
				}
				el = el.parentNode;
			}
			return zIndex;
		},

		getMaxZIndexOfOverlayManager: function(overlayManager) {
			if (!overlayManager && window.Pmt_Yui_Panel) overlayManager = window.Pmt_Yui_Panel.getOverlayManager();
			var zIndex = 0, elZIndex;
			if (overlayManager) {
				for (var i = 0; i < overlayManager.overlays.length; i++) {
					if (overlayManager.overlays[i].element && overlayManager.overlays[i].element.style && overlayManager.overlays[i].element.style.zIndex) {
						elZIndex = parseInt(overlayManager.overlays[i].element.style.zIndex);
						if (elZIndex !== NaN && elZIndex > zIndex) zIndex = elZIndex;
					}
				}
			}
			return zIndex;
		},

		fixMenuDisplay: function(menu) {
			menu.showEvent.subscribe(function() { 
					if (this.cfg.getProperty('position') === 'dynamic') {
						var zIndex = Pmt_Yui_Util.getMaxZIndexOfOverlayManager(), mn = this;
						while (mn && mn.element) {
							zIndex = Pmt_Yui_Util.getMaxZIndexByParent(mn.element, zIndex);
							if (mn.parent) mn = mn.parent; else mn = null;
						}
						this.element.style.zIndex = zIndex + 1;
					}
			}, null, menu);
		}
		
}