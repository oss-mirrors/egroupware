/*
 * Egroupware Calendar event widget
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package etemplate
 * @subpackage api
 * @link http://www.egroupware.org
 * @author Nathan Gray
 * @version $Id$
 */


"use strict";

/*egw:uses
	/etemplate/js/et2_core_valueWidget;
*/

/**
 * Class for a single event, displayed in a timegrid
 *
 *
 * @augments et2_valueWidget
 */
var et2_calendar_event = et2_valueWidget.extend([et2_IDetachedDOM],
{

	attributes: {
		"value": {
			type: "any",
			default: et2_no_init
		},
		"onclick": {
			"description": "JS code which is executed when the element is clicked. " +
				"If no handler is provided, or the handler returns true and the event is not read-only, the " +
				"event will be opened according to calendar settings."
		}
	},

	/**
	 * Constructor
	 *
	 * @memberOf et2_calendar_daycol
	 */
	init: function() {
		this._super.apply(this, arguments);

		// Main container
		this.div = $j(document.createElement("div"))
			.addClass("calendar_calEvent")
			.addClass(this.options.class)
			.css('width',this.options.width)
			.on('mouseenter', function() {
				// Hacky to remove egw's tooltip border
				window.setTimeout(function() {
					$j('body .egw_tooltip').css('border','none');
				},105);
			});
		this.title = $j(document.createElement('div'))
			.addClass("calendar_calEventHeader")
			.appendTo(this.div);
		this.body = $j(document.createElement('div'))
			.addClass("calendar_calEventBody")
			.appendTo(this.div);
		this.icons = $j(document.createElement('div'))
			.addClass("calendar_calEventIcons")
			.appendTo(this.title);

		this.setDOMNode(this.div[0]);
	},

	doLoadingFinished: function() {
		this._super.apply(this, arguments);

		// Parent will have everything we need, just load it from there
		if(this.title.text() == '' && this.options.date &&
			this._parent && this._parent.instanceOf(et2_calendar_timegrid))
		{
			// Forces an update
			var date = this.options.date;
			this.options.date = '';
			this.set_date(date);
		}
	},

	destroy: function() {
		this._super.apply(this, arguments);

		if(this._actionObject)
		{
			this._actionObject.remove();
			this._actionObject = null;
		}
		
		this.div.off();
		this.title.remove();
		this.title = null;
		this.body.remove();
		this.body = null;
		this.icons = null;
		this.div.remove();
		this.div = null;

		
		// Unregister, or we'll continue to be notified...
		if(this.options.value)
		{
			var old_app_id = this.options.value.app_id ? this.options.value.app_id : this.options.value.id + (this.options.value.recur_type ? ':'+this.options.value.recur_date : '');
			egw.dataUnregisterUID('calendar::'+old_app_id,false,this);
		}
	},

	set_value: function(_value) {
		// Un-register for updates
		if(this.options.value)
		{
			var old_app_id = this.options.value.app_id ? this.options.value.app_id : this.options.value.id + (this.options.value.recur_type ? ':'+this.options.value.recur_date : '');
			egw.dataUnregisterUID('calendar::'+old_app_id,false,this);
		}
		this.options.value = _value;

		// Register for updates
		var app_id = this.options.value.app_id ? this.options.value.app_id : this.options.value.id + (this.options.value.recur_type ? ':'+this.options.value.recur_date : '');
		egw.dataRegisterUID('calendar::'+app_id, function(event) {
			// Check for changing days in the grid view
			if(this._parent && this._parent.instanceOf(et2_calendar_daycol) &&
				this.options.value.date && event.date != this.options.value.date)
			{
				// Date changed, reparent
				var new_parent = this._parent._parent.getWidgetById(event.date);
				if(new_parent)
				{
					new_parent.addChild(this);
				}
				else
				{
					// Could not find the right date
					this._parent.removeChild(this);
					this.destroy();
					return;
				}
			}
			// Copy to avoid changes, which may cause nm problems
			this.options.value = jQuery.extend({},event);

			// Let parent position
			this._parent.position_event(this);

			// Parent may remove this if the date isn't the same
			if(this._parent)
			{
				this._update(this.options.value);
			}

		},this,this.getInstanceManager().execId,this.id);

		if(_value && !egw.dataHasUID('calendar::'+app_id))
		{
			egw.dataStoreUID('calendar::'+app_id, _value);
		}
	},

	_update: function(event) {

		// Copy new information
		this.options.value = event;

		var eventId = event.id.match(/-?\d+\.?\d*/g)[0];
		var appName = event.id.replace(/-?\d+\.?\d*/g,'');
		var app_id = event.app_id ? event.app_id : event.id + (event.recur_type ? ':'+event.recur_date : '');
		this._parent.date_helper.set_value(event.start.valueOf ? new Date(event.start) : event.start);
		var formatted_start = this._parent.date_helper.getValue();

		this.set_id('event_' + (eventId || event.id));
		
		this.div
			// Empty & re-append to make sure dnd helpers are gone
			.empty()
			.append(this.title)
			.append(this.body)
		
			// ?
			.attr('data-draggable-id',event['id']+'_O'+event.owner+'_C'+(event.owner<0?'group'+Math.abs(event.owner):event.owner))
		
			// Put everything we need for basic interaction here, so it's available immediately
			.attr('data-id', eventId || event.id)
			.attr('data-app', appName || 'calendar')
			.attr('data-app_id', app_id)
			.attr('data-start', formatted_start)
			.attr('data-owner', event.owner)
			.attr('data-recur_type', event.recur_type)
			.attr('data-resize', event.whole_day ? 'WD' : '' + (event.recur_type ? 'S':''))
			// Remove any category classes
			.removeClass(function(index, css) {
				return (css.match (/(^|\s)cat_\S+/g) || []).join(' ');
			})
			// Remove any status classes
			.removeClass(function(index, css) {
				return (css.match(/calendar_calEvent\S+/g) || []).join(' ');
			})
			// Remove any resize classes, the handles are gone due to empty()
			.removeClass('ui-resizable')
			.addClass(event.class)
			.toggleClass('calendar_calEventPrivate', event.private);
		this.options.class = event.class;
		if(event.category)
		{
			this.div.addClass('cat_' + event.category);
		}
		this.div.css('border-color', this.div.css('background-color'));

		this.div.toggleClass('calendar_calEventUnknown', event.participants[egw.user('account_id')] ? event.participants[egw.user('account_id')][0] === 'U' : false);
		this.div.addClass(this._status_class());

		this.title.toggle(!event.whole_day_on_top);
		this.body.toggleClass('calendar_calEventBodySmall', event.whole_day_on_top || false);
		
		// Header
		var title = !event.is_private ? event['title'] : egw.lang('private');
		var small_height = true;
		if(this._parent.display_settings)
		{
			small_height = event['end_m']-event['start_m'] < 2*this._parent.display_settings.granularity ||
				event['end_m'] <= this._parent.display_settings.wd_start || event['start_m'] >= this._parent.display_settings.wd_end;
		}

		this.div.attr('data-title', title);
		this.title.text(small_height ? title : this._get_timespan(event))
			// Set title color based on background brightness
			.css('background-color', this.div.css('background-color'))
			.css('color', jQuery.Color(this.div.css('background-color')).lightness() > 0.5 ? 'black':'white');

		this.icons.appendTo(this.title)
			.html(this._icons());
		
		// Body
		if(event.whole_day_on_top)
		{
			this.body.html(title);
		}
		else
		{
			this.body.html('<span class="calendar_calEventTitle">'+title+'</span>')
		}
		this.body
			// Set background color to a lighter version of the header color
			.css('background-color',jQuery.Color(this.title.css('background-color')).lightness("+=0.3"));

		this.set_statustext(this._tooltip());
	},

	/**
	 * Examines the participants & returns CSS classname for status
	 * 
	 * @returns {String}
	 */
	_status_class: function() {
		var status_class = 'calendar_calEventAllAccepted';
		for(var id in this.options.value.participants)
		{
			var status = this.options.value.participants[id];

			if (parseInt(id) < 0) continue;	// as we cant accept/reject groups, we dont care about them here

			status = et2_calendar_event.split_status(status);

			switch (status)
			{
				case 'A':
				case '':	// app without status
					break;
				case 'U':
					status_class = 'calendar_calEventSomeUnknown';
					return status_class;	// break for
				default:
					status_class = 'calendar_calEventAllAnswered';
					break;
			}
		}
		return status_class;
	},

	_tooltip: function() {
		
		var border = this.div.css('border-color');
		var bg_color = this.div.css('background-color');
		var header_color = this.title.css('color');

		this._parent.date_helper.set_value(this.options.value.start.valueOf ? new Date(this.options.value.start) : this.options.value.start);
		var start = this._parent.date_helper.input_date.val();
		this._parent.date_helper.set_value(this.options.value.end.valueOf ? new Date(this.options.value.end) : this.options.value.end);
		var end = this._parent.date_helper.input_date.val();

		var times = !this.options.value.multiday ?
			'<span class="calendar_calEventLabel">'+this.egw().lang('Time')+'</span>:' + this._get_timespan(this.options.value) :
			'<span class="calendar_calEventLabel">'+this.egw().lang('Start') + '</span>:' +start+
			'<span class="calendar_calEventLabel">'+this.egw().lang('End') + '</span>:' + end
		var cat = et2_createWidget('select-cat',{'readonly':true},this);
		cat.set_value(this.options.value.category);
		var cat_label = cat.span.text();
		cat.destroy();
		
		return '<div class="calendar_calEventTooltip ' + this._status_class() + '" style="border-color: '+border+'; background: '+bg_color+';">'+
			'<div class="calendar_calEventHeaderSmall" style="background-color: '+border+';">'+
				'<font style="color:'+header_color+'">'+this._get_timespan(this.options.value)+'</font>'+
				this.icons[0].outerHTML+
			'</div>'+
			'<div class="calendar_calEventBodySmall" style="background-color: '+
				jQuery.Color(this.title.css('background-color')).lightness("+=0.3") + '">'+
				'<p style="margin: 0px;">'+
				'<span class="calendar_calEventTitle">'+this.div.attr('data-title')+'</span><br>'+
				this.options.value.description+'</p>'+
				'<p style="margin: 2px 0px;">'+times+'</p>'+
				(this.options.value.location ? '<p><span class="calendar_calEventLabel">'+this.egw().lang('Location') + '</span>:' + this.options.value.location+'</p>' : '')+
				(cat_label ? '<p><span class="calendar_calEventLabel">'+this.egw().lang('Category') + '</span>:' + cat_label+'</p>' : '')+
				'<p><span class="calendar_calEventLabel">'+this.egw().lang('Participants')+'</span>:<br />'+
					(this.options.value.parts ? this.options.value.parts.replace("\n","<br />"):'')+'</p>'+
			'</div>'+
		'</div>';
	},

	/**
	 * Get actual icons from list
	 * @returns {undefined}
	 */
	_icons: function() {
		var icons = [];
		
		if(this.options.value.is_private)
		{
			icons.push('<img src="'+this.egw().image('private','calendar')+'"/>');
		}
		else
		{
			if(this.options.value.priority == 3)
			{
				icons.push('<img src="'+this.egw().image('high','calendar')+'" title="'+this.egw().lang('high priority')+'"/>');
			}
			if(this.options.value['recur_type'])
			{
				icons.push('<img src="'+this.egw().image('recur','calendar')+'" title="'+this.egw().lang('recurring event')+'"/>');
			}
			// icons for single user, multiple users or group(s) and resources
			var single = '<img src="'+this.egw().image('single','calendar')+'" title="'+'"/>';
			var multiple = '<img src="'+this.egw().image('users','calendar')+'" title="'+'"/>';
			for(var uid in this.options.value['participants'])
			{
				if(Object.keys(this.options.value.participants).length == 1 && !isNaN(uid))
				{
					icons.push(single);
					break;
				}
				if(!isNaN(uid) && icons.indexOf(multiple) === -1)
				{
					icons.push(multiple);
				}
				/*
				 * TODO: resource icons
				elseif(!isset($icons[$uid[0]]) && isset($this->bo->resources[$uid[0]]) && isset($this->bo->resources[$uid[0]]['icon']))
				{
				 	$icons[$uid[0]] = html::image($this->bo->resources[$uid[0]]['app'],
				 		($this->bo->resources[$uid[0]]['icon'] ? $this->bo->resources[$uid[0]]['icon'] : 'navbar'),
				 		lang($this->bo->resources[$uid[0]]['app']),
				 		'width="16px" height="16px"');
				}
				*/
			}

			if(this.options.value.non_blocking)
			{
				icons.push('<img src="'+this.egw().image('nonblocking','calendar')+'" title="'+this.egw().lang('non blocking')+'"/>');
			}
			if(this.options.value.alarm && !jQuery.isEmptyObject(this.options.value.alarm) && !this.options.value.is_private)
			{
				icons.push('<img src="'+this.egw().image('alarm','calendar')+'" title="'+this.egw().lang('alarm')+'"/>');
			}
			if(this.options.value.participants[egw.user('account_id')] && this.options.value.participants[egw.user('account_id')][0] == 'U')
			{
				icons.push('<img src="'+this.egw().image('cnr-pending','calendar')+'" title="'+this.egw().lang('Needs action')+'"/>');
			}
		}
		return icons;
	},

	/**
	 * Get a text representation of the timespan of the event.  Either start
	 * - end, or 'all day'
	 *
	 * @param {Object} event Event to get the timespan for
	 * @param {number} event.start_m Event start, in minutes from midnight
	 * @param {number} event.end_m Event end, in minutes from midnight
	 *
	 * @return {string} Timespan
	 */
	_get_timespan: function(event) {
		var timespan = '';
		if (event['start_m'] === 0 && event['end_m'] >= 24*60-1)
		{
			if (event['end_m'] > 24*60)
			{
				timespan = jQuery.datepicker.formatTime(
					egw.preference("timeformat") === 12 ? "h:mmtt" : "HH:mm",
					{
						hour: event.start_m / 60,
						minute: event.start_m % 60,
						seconds: 0,
						timezone: 0
					},
					{"ampm": (egw.preference("timeformat") === "12")}
				).trim()+' - '+jQuery.datepicker.formatTime(
					egw.preference("timeformat") === 12 ? "h:mmtt" : "HH:mm",
					{
						hour: event.end_m / 60,
						minute: event.end_m % 60,
						seconds: 0,
						timezone: 0
					},
					{"ampm": (egw.preference("timeformat") === "12")}
				).trim();
			}
			else
			{
				timespan = egw.lang('all day');
			}
		}
		else
		{
			var duration = event.end_m - event.start_m;
			if (event.end_m === 24*60-1) ++duration;
			duration = Math.floor(duration/60) + this.egw().lang('h')+(duration%60 ? duration%60 : '');

			timespan = jQuery.datepicker.formatTime(
				egw.preference("timeformat") === 12 ? "h:mmtt" : "HH:mm",
				{
					hour: event.start_m / 60,
					minute: event.start_m % 60,
					seconds: 0,
					timezone: 0
				},
				{"ampm": (egw.preference("timeformat") === "12")}
			).trim();

			timespan += ' ' + duration;
		}
		return timespan;
	},

	attachToDOM: function()
	{
		this._super.apply(this, arguments);

		// Remove the binding for the click handler, unless there's something
		// custom here.
		if (!this.onclick)
		{
			$j(this.node).off("click");
		}
	},

	/**
	 * Click handler calling custom handler set via onclick attribute to this.onclick.
	 * All other handling is done by the timegrid widget.
	 *
	 * @param {Event} _ev
	 * @returns {boolean}
	 */
	click: function(_ev) {
		var result = true;
		if(typeof this.onclick == 'function')
		{
			// Make sure function gets a reference to the widget, splice it in as 2. argument if not
			var args = Array.prototype.slice.call(arguments);
			if(args.indexOf(this) == -1) args.splice(1, 0, this);

			result = this.onclick.apply(this, args);
		}
		return result;
	},

	/**
	 * Show the recur prompt for this event
	 *
	 * @param {function} callback
	 */
	recur_prompt: function(callback)
	{
		et2_calendar_event.recur_prompt(this.options.value,callback);
	},

	/**
	 * Link the actions to the DOM nodes / widget bits.
	 *
	 * @param {object} actions {ID: {attributes..}+} map of egw action information
	 */
	_link_actions: function(actions)
	{
		if(!this._actionObject)
		{
			// Get the top level element - timegrid or so
			var objectManager = this.getParent().getParent()._actionObject ||
			   egw_getAppObjectManager(true).getObjectById(this._parent._parent._parent.id) || egw_getAppObjectManager(true);
			this._actionObject = objectManager.getObjectById('calendar::'+this.id);
		}

		if (this._actionObject == null) {
			// Add a new container to the object manager which will hold the widget
			// objects
			this._actionObject = objectManager.insertObject(false, new egwActionObject(
				'calendar::'+this.id, objectManager, new et2_event_action_object_impl(this,this.getDOMNode()),
				this._actionManager || objectManager.manager.getActionById(this.id) || objectManager.manager
			));
		}
		else
		{
			this._actionObject.setAOI(new et2_event_action_object_impl(this, this.getDOMNode()));
		}

		// Delete all old objects
		this._actionObject.clear();
		this._actionObject.unregisterActions();

		// Go over the widget & add links - this is where we decide which actions are
		// 'allowed' for this widget at this time
		var action_links = this._get_action_links(actions);
		action_links.push('egw_link_drag');
		action_links.push('egw_link_drop');
		this._actionObject.updateActionLinks(action_links);
	},
	
	/**
	 * Code for implementing et2_IDetachedDOM
	 *
	 * @param {array} _attrs array to add further attributes to
	 */
	getDetachedAttributes: function(_attrs) {

	},

	getDetachedNodes: function() {
		return [this.getDOMNode()];
	},

	setDetachedAttributes: function(_nodes, _values) {

	},
});
et2_register_widget(et2_calendar_event, ["calendar-event"]);

// Static class stuff
/**
 * Recur prompt
 * If the event is recurring, asks the user if they want to edit the event as 
 * an exception, or change the whole series.  Then the callback is called.
 *
 * @param {Object} event_data - Event information
 * @param {string} event_data.id - Unique ID for the event, possibly with a timestamp
 * @param {string|Date} event_data.start - Start date/time for the event
 * @param {number} event_data.recur_type - Recur type, or 0 for a non-recurring event
 * @param {Function} [callback] - Callback is called with the button (exception, series, single or cancel) and the event data.
 * 
 * @augments {et2_calendar_event}
 */
et2_calendar_event.recur_prompt = function(event_data, callback)
{
	var edit_id = event_data.id;
	var edit_date = event_data.start;
	var egw = this.egw ? (typeof this.egw == 'function' ? this.egw() : this.egw) : (window.opener || window).egw;
	var that = this;

	if(typeof callback != 'function')
	{
		callback = function(_button_id)
		{
			switch(_button_id)
			{
				case 'exception':
					egw.open(edit_id, event_data.app||'calendar', 'edit', {date:edit_date,exception: '1'});
					break;
				case 'series':
				case 'single':
					egw.open(edit_id, event_data.app||'calendar', 'edit', {date:edit_date});
					break;
				case 'cancel':
				default:
					break;
			}
		};
	}
	if(parseInt(event_data.recur_type))
	{
		var buttons = [
			{text: egw.lang("Edit exception"), id: "exception", class: "ui-priority-primary", "default": true},
			{text: egw.lang("Edit series"), id:"series"},
			{text: egw.lang("Cancel"), id:"cancel"}
		];
		et2_dialog.show_dialog(
			function(button_id) {callback.call(that, button_id, event_data);},
			(!event_data.is_private ? event_data['title'] : egw.lang('private')) + "\n" +
			egw.lang("Do you want to edit this event as an exception or the whole series?"),
			egw.lang("This event is part of a series"), {}, buttons, et2_dialog.QUESTION_MESSAGE
		);
	}
	else
	{
		callback.call(this,'single',event_data);
	}
};

et2_calendar_event.drag_helper = function(event,ui) {
	ui.helper.width(ui.width());
};
/**
* splits the combined status, quantity and role
*
* @param {string} status - combined value, O: status letter: U, T, A, R
* @param {int} [quantity] - quantity
* @param {string} [role] 
* @return string status U, T, A or R, same as $status parameter on return
*/
et2_calendar_event.split_status = function(status,quantity,role)
{
	quantity = 1;
	role = 'REQ-PARTICIPANT';
	//error_log(__METHOD__.__LINE__.array2string($status));
	var matches = null;
	if (typeof status === 'string' && status.length > 1)
	{
		matches = status.match(/^.([0-9]*)(.*)$/gi);
	}
	if(matches)
	{
		if (parseInt(matches[1]) > 0) quantity = parseInt(matches[1]);
		if (matches[2]) role = matches[2];
		status = status[0];
	}
	else if (status === true)
	{
		status = 'U';
	}
	return status;
}

/**
 * The egw_action system requires an egwActionObjectInterface Interface implementation
 * to tie actions to DOM nodes.  This one can be used by any widget.
 *
 * The class extension is different than the widgets
 *
 * @param {et2_DOMWidget} widget
 * @param {Object} node
 *
 */
function et2_event_action_object_impl(widget, node)
{
	var aoi = new et2_action_object_impl(widget, node);

// _outerCall may be used to determine, whether the state change has been
// evoked from the outside and the stateChangeCallback has to be called
// or not.
	aoi.doSetState = function(_state, _outerCall) {
	};

	return aoi;
};
