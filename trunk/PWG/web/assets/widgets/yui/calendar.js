Pwg_Yui_Calendar = function(options) {

    this.configProps = [
        'minDate', 'maxDate', 'navigator', 'pages', 'dateClasses', 'pageDate'
    ];

    this.autoEvents = [];

    this.dateClasses = new Array;
    Pwg_Element.call(this, options);
    this.initialize(options);
}

Pwg_Yui_Calendar.prototype = {
    id: false,
    configProps: null,
    autoEvents: null,
    container: false,

    multiple: null,
    selectedValue: null,
    minDate: null,
    maxDate: null,
    navigator: null,
    pages: null,
    dateClasses: null,
    defaultDateClass: null,
    pageDate: null,
    closeButton: null,
    visible: null,
    showHeader: true,

    hasNextPrevButtons: true,

    yuiCalendar: null,

    _isMultiPage: false,
    _selCall: false,
    _pageCall: false,
    
    _renderCall: false,

    localizationData: false,

    _myRender: function() {
        if (this._origRender) this._origRender.apply(this, [].concat(arguments));
        if (this.hasNextPrevButtons === false) {
            var e = YAHOO.util.Dom.getElementsByClassName('calnavleft', 'a', this.oDomContainer).concat(
                YAHOO.util.Dom.getElementsByClassName('calnavright', 'a', this.oDomContainer)
            );
            for (var i = 0; i < e.length; i++) e[i].parentNode.removeChild(e[i]);
        }
    },

    setPageDate: function(pageDate, dontRender) {

        if (pageDate === undefined) pageDate = this.pageDate;
            else this.pageDate = pageDate;
        if (pageDate !== null && pageDate !== false) {
            this.yuiCalendar.cfg.setProperty('pagedate', pageDate);
            if (!dontRender) this.callRender();
        }
    },
    
    callRender: function() {
    	this._renderCall.call();
    },

    handleCalendarPage:  function() {
        var c = this.yuiCalendar.pages? this.yuiCalendar.pages[0] : this.yuiCalendar;
        var pageDate = c.cfg.getProperty('pagedate');
        if (pageDate instanceof Date) {
            pageDate = (pageDate.getMonth() + 1) + '/' + pageDate.getFullYear();
        }
        if (pageDate != this.pageDate) {
            this.pageDate = pageDate;
            this.sendMessage('pageDate', this.pageDate);
        }
    },

    handleCalendarSelection: function() {
        var sd = this.yuiCalendar.getSelectedDates();
        this.selectedValue = [];
        for (var i = 0; i < sd.length; i++) {
            var d = sd[i];
            var v = '' + (d.getMonth() + 1) + '/' + d.getDate() + '/' + d.getFullYear();
            this.selectedValue.push(v);
        }
        this.sendMessage('selectedValue', this.selectedValue);
    },

    setSelectedValue: function(selectedValue, dontRender) {
        if (selectedValue === undefined) selectedValue = this.selectedValue;
            else this.selectedValue = selectedValue;
        this.selectedValue = Pwg_Util.toArray(this.selectedValue);

        if (this.yuiCalendar) {
            this.lockMessages();
            this.yuiCalendar.deselectAll();
            if (!this.multiple) this.selectedValue = this.selectedValue.slice(0, 1);
            var s = this.selectedValue.join(",");
            if (s.length)
                this.yuiCalendar.select(s);
            if (!dontRender) this.callRender();
            this.unlockMessages();
        }
    },

    dateClassesRenderer: function(date, cell) {
		YAHOO.widget.Calendar.prototype.renderCellDefault.call(this, date, cell);
        var d = (date.getMonth() + 1) + '/' + date.getDate() + '/' + date.getFullYear();
        if (this.dateClasses && this.dateClasses[d]) {
            var a = Pwg_Util.toArray(this.dateClasses[d]);
            for (var i = a.length - 1; i >= 0; i--) YAHOO.util.Dom.addClass(cell, a[i]);
        }
    },

    setDefaultDateClass: function(defaultDateClass, dontRender) {
        if (defaultDateClass === undefined) defaultDateClass = this.defaultDateClass;
            else this.defaultDateClass = defaultDateClass;
    },

    setDateClasses: function(dateClasses, dontRender) {
        if (dateClasses === undefined) dateClasses = this.dateClasses;
            else this.dateClasses = dateClasses;

        if (this.yuiCalendar && dateClasses) {
            this.yuiCalendar.dateClasses = dateClasses;
            if (this.pages  > 1) {
                for (var i = 0; i < this.yuiCalendar.pages.length; i++) {
                    this.yuiCalendar.pages[i].dateClasses = dateClasses;
                }
            }

            if (!dontRender && !this._renderCall.isActive()) this.callRender();
        }
    },
    
    setVisible: function(visible) {
        if (visible === undefined) visible = this.visible;
            else this.visible = visible;
        if (this.yuiCalendar) {
            this.lockMessages();
            if (visible) this.yuiCalendar.show();
                else this.yuiCalendar.hide();
            this.unlockMessages();
        }
    },

    setHideOnClose: function(hideOnClose) {
        this.hideOnClose = hideOnClose;
    },

    setMultiple: function(multiple, dontRender, force) {
        if (multiple === undefined) multiple = this.multiple;
        if ((multiple !== this.multiple) || force) {
            this.multiple = multiple;
            if (this.yuiCalendar) this.yuiCalendar.cfg.setProperty('multi_select', this.multiple);
            if (!this.multiple) this.setSelectedValue(undefined, dontRender);
            else{ 
                if (!dontRender) this.yuiCalendar.callRender();
            }
        }
        
    },

    callSelCall: function() {
        if (!this.msgLock && this._selCall) this._selCall.call.apply(this._selCall, arguments);
    },

    callPageCall: function() {
        if (!this.msgLock && this._pageCall) this._pageCall.call.apply(this._pageCall, arguments);
    },

    renderElement: function() {

        this.getContainer();
        var ic = this.getInnerContainer();

        var calendarOptions = {
        };

        if (this.localizationData !== null && (typeof this.localizationData == 'object')) {
            Pwg_Util.override(calendarOptions, this.localizationData);
        }

        var calClass = YAHOO.widget.Calendar;

        if (this.pages > 1) {
            this._isMultiPage = true;
            calClass = YAHOO.widget.CalendarGroup;
            calendarOptions['PAGES'] = this.pages;
        }

        if (!this.navigator) calendarOptions['navigator'] = false;
        if (this.closeButton) calendarOptions['close'] = true;

        for (var i = 0; i < this.configProps.length; i++) {
            var p = this.configProps[i];
            if (this[p] !== null && this[p] !== false) calendarOptions[p] = this[p];
        }

        this._selCall = new Pwg_Util.DelayedCall(this.handleCalendarSelection, null, this, [], this.getDefault('clickDelay', 300), false);
        this._pageCall = new Pwg_Util.DelayedCall(this.handleCalendarPage, null, this, [], this.getDefault('clickDelay', 300), false);
        
        this.yuiCalendar = new calClass(this.id, ic, calendarOptions);
		
		this.yuiCalendar.renderCellDefault = this.dateClassesRenderer;
		if (this.pages > 1) {
			for (var i = 0; i < this.pages.length; i++) {
				this.yuiCalendar.pages[i].renderCellDefault = this.dateClassesRenderer;
			}
		}
		
        this.lockMessages();
        this.setVisible(this.visible, true);
        this.setMultiple(this.multiple, true, true);
        this.setSelectedValue(this.selectedValue, true);
        this.setPageDate(this.pageDate, true);
        this.yuiCalendar._origRender = this.yuiCalendar.render;
        this.yuiCalendar.hasNextPrevButtons = this.hasNextPrevButtons;
        this.yuiCalendar.render = this._myRender;
        if (!this.showHeader) this.yuiCalendar.renderHeader = function(html) {return html;};
		this._renderCall = new Pwg_Util.DelayedCall(this.yuiCalendar.render, null, this.yuiCalendar, [], this.getDefault('renderDelay', 50), false);
        this.setDateClasses(this.dateClasses, false);
        this.callRender();
        this.yuiCalendar.changePageEvent.subscribe(this.callPageCall, null, this);
        this.yuiCalendar.selectEvent.subscribe(this.callSelCall, null, this);
        this.yuiCalendar.deselectEvent.subscribe(this.callSelCall, null, this);
        this.unlockMessages();
        
        this.yuiCalendar.showEvent.subscribe(function() {
            this.visible = true;
            this.sendMessage('visible', this.visible);
        }, null, this);

        this.yuiCalendar.hideEvent.subscribe(function() {
           this.sendMessage('visible', !this.visible? 1 : 0);
           if (this.hideOnClose) {
               this.visible = false;
           } else {
               return false;
           }
        }, null, this);
    },

    refresh: function() {
        
    },

    doOnDelete: function() {
        if (this.yuiCalendar) {
            this.yuiCalendar.destroy();
            delete this.yuiCalendar;
        }
        if (this._selCall) {
            this._selCall.destroy();
            delete this._selCall;
        }
        if (this._pageCall) {
            this._pageCall.destroy();
            delete this._pageCall;
        }
    }

}

Pwg_Util.extend(Pwg_Yui_Calendar, Pwg_Element);