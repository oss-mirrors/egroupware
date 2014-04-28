/**
 * EGroupware eTemplate2 - JS widget for GANTT chart
 *
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package etemplate
 * @subpackage api
 * @link http://www.egroupware.org
 * @author Nathan Gray
 * @copyright Nathan Gray 2014
 * @version $Id$
 */

"use strict";

/*egw:uses
	jsapi.jsapi;
	jquery.jquery;
	/phpgwapi/js/dhtmlxtree/js/dhtmlXCommon.js; // otherwise gantt breaks
	/phpgwapi/js/dhtmlxGantt/codebase/dhtmlxgantt.js;
	et2_core_inputWidget;
*/

/**
 * Gantt chart
 *
 * The gantt widget allows children, which are displayed as a header.  Any child input
 * widgets are bound as live filters on existing data.  The filter is done based on
 * widget ID, such that the value of the widget must match that attribute in the task
 * or the task will not be displayed.  There is special handling for
 * date widgets with IDs 'start_date' and 'end_date' to filter as an inclusive range
 * instead of simple equality.
 *
 * @see http://docs.dhtmlx.com/gantt/index.html
 * @augments et2_valueWidget
 */
var et2_gantt = et2_valueWidget.extend([et2_IResizeable],
{
	// Filters are inside gantt namespace
	createNamespace: true,

	attributes: {
		"autoload": {
			"name": "Autoload",
			"type": "string",
			"default": "",
			"description": "JSON URL or menuaction to be called for projects with no, GET parameter selected contains id"
		},
		"ajax_update": {
			"name": "AJAX update method",
			"type": "string",
			"default": "",
			"description": "AJAX menuaction to be called when the user changes a task.  The function should take two parameters: the updated element, and all template values."
		},
		"on_edit": {
			"name": "Edit handler",
			"type": "js",
			"default": et2_no_init,
			"description": "Function to handle when a user double-clicks a task.  The function should take two parameters, the task information and this widget"
		},
		value: {type: 'any'}
	},
	
	// Common configuration for Egroupware/eTemplate
	gantt_config: {
		// Gantt takes a different format of date format, all the placeholders are prefixed with '%'
		api_date: '%Y-%n-%d %H:%i:%s',
		xml_date: '%Y-%n-%d %H:%i:%s',
		
		// Duration is a unitless field.  This is the unit.
		duration_unit: 'minute',

		show_progress: true,
		min_column_width: 30,
		fit_tasks: true,
		autosize: 'y',
		// Date rounding happens either way, but this way it rounds to the displayed grid resolution
		// Also avoids a potential infinite loop thanks to how the dates are rounded with false
		round_dnd_dates: true,
		scale_unit: 'day',
		date_scale: '%d',
		subscales: [
			{unit:"month", step:1, date:"%F, %Y"},
			//{unit:"hour", step:1, date:"%G"}
		],
		columns: [
			{name: "text", label: egw.lang('Title'), tree: true, width: '*'}
		]
	},

	init: function(_parent, _attrs) {
		// _super.apply is responsible for the actual setting of the params (some magic)
		this._super.apply(this, arguments);

		// Gantt instance
		this.gantt = null;

		// DOM Nodes
		this.filters = $j(document.createElement("div"))
			.addClass('et2_gantt_header');
		this.gantt_node = $j('<div style="width:100%;height:100%"></div>');
		this.htmlNode = $j(document.createElement("div"))
			.css('height', this.options.height)
			.addClass('et2_gantt');

		this.htmlNode.prepend(this.filters);
		this.htmlNode.append(this.gantt_node);
		
		// Create the dynheight component which dynamically scales the inner
		// container.
		this.dynheight = new et2_dynheight(
			this.getParent().getDOMNode(this.getParent()) || this.getInstanceManager().DOMContainer,
			this.gantt_node, 300
		);
		
		this.setDOMNode(this.htmlNode[0]);
	},

	destroy: function() {
		if(this.gantt !== null)
		{
			this.gantt.detachAllEvents();
			this.gantt.clearAll();
			this.gantt = null;
			
		// Destroy dynamic full-height
		if(this.dynheight) this.dynheight.free();

		this._super.apply(this, arguments);}
	
		this.htmlNode.remove();
		this.htmlNode = null;
	},

	doLoadingFinished: function() {
		this._super.apply(this, arguments);
		if(this.gantt != null) return false;

		var config = jQuery.extend({}, this.gantt_config);

		// Set initial values for start and end, if those filters exist
		var start_date = this.getWidgetById('start_date');
		var end_date = this.getWidgetById('end_date');
		if(start_date)
		{
			config.start_date = start_date.getValue() ? new Date(start_date.getValue() * 1000) : null;
		}
		if(end_date)
		{
			config.end_date = end_date.getValue() ? new Date(end_date.getValue() * 1000): null;
		}

		// Initialize chart
		this.gantt = this.gantt_node.dhx_gantt(config);

		if(this.options.value)
		{
			this.set_value(this.options.value);
		}

		// Update start & end dates with chart values for consistency
		if(start_date)
		{
			start_date.set_value(this.gantt.getState().min_date);
		}
		if(end_date)
		{
			end_date.set_value(this.gantt.getState().max_date);
		}

		// Bind some events to make things nice and et2
		this._bindGanttEvents();

		// Bind filters
		this._bindChildren();

		return true;
	},

	getDOMNode: function(_sender) {
		// Return filter container for children
		if (_sender != this && this._children.indexOf(_sender) != -1)
		{
			return this.filters[0];
		}

		// Normally simply return the main div
		return this._super.apply(this, arguments);
	},

	/**
	 * Implement the et2_IResizable interface to resize
	 */
	resize: function()
	{
		if(this.dynheight)
		{
			this.dynheight.update(function(w,h) {
				this.gantt.setSizes();
			}, this);
		}
		else
		{
			this.gantt.setSizes();
		}
	},

	/**
	 * Sets the data to be displayed in the gantt chart.
	 *
	 * Data is a JSON object with 'data' and 'links', both of which are arrays.
	 * {
	 *		data:[
	 *			{id:1, text:"Project #1", start_date:"01-04-2013", duration:18},
	 *			{id:2, text:"Task #1", start_date:"02-04-2013", duration:8, parent:1},
	 *			{id:3, text:"Task #2", start_date:"11-04-2013", duration:8, parent:1}
	 *		],
	 *		links:[
	 *			{id:1, source:1, target:2, type:"1"},
	 *			{id:2, source:2, target:3, type:"0"}
	 *		]
	 * };
	 * Any additional data can be included and used, but the above is the minimum
	 * required data.
	 *
	 * @see http://docs.dhtmlx.com/gantt/desktop__loading.html
	 */
	set_value: function(value) {
		if(this.gantt == null) return false;

		// Ensure proper format, no extras
		var safe_value = {
			data: value.data || [],
			links: value.links || []
		};
		this.gantt.parse(safe_value);

		// Set some things from the value

		// Set zoom
		if(!this.options.zoom) this.set_zoom();

		// If this is not the first gantt chart the browser renders, sometimes it needs a nudge
		try
		{
			this.gantt.render();
		}
		catch (e)
		{
			this.egw().debug('warning', 'Problem rendering gantt', e);
		}
	},

	/**
	 * Set a URL to fetch the data from the server.
	 * Data must be in the specified format.
	 * @see http://docs.dhtmlx.com/gantt/desktop__loading.html
	 */
	set_autoload: function(url) {
		if(this.gantt == null) return false;
		this.options.autoloading = url;

		throw new Exception('Not implemented yet - apparently loading segments is not supported automatically');
	},

	/**
	 * Sets the level of detail for the chart, which adjusts the scale(s) across the
	 * top and the granularity of the drag grid.
	 *
	 * Gantt chart needs a render() after changing.
	 *
	 * @param {int} level Higher levels show more grid, at larger granularity.
	 * @return {int} Current level
	 */
	set_zoom: function(level) {

		var subscales = [];
		var scale_unit = 'day';
		var date_scale = '%d';
		var step = 1;

		// No level?  Auto calculate.
		if(level > 4) level = 4;
		if(!level || level < 1) {
			// Make sure we have the most up to date info for the calculations
			// There may be a more efficient way to trigger this though
			try {
				this.gantt.render();
			}
			catch (e)
			{}

			var difference = (this.gantt.getState().max_date - this.gantt.getState().min_date)/1000; // seconds
			// Spans more than a year
			if(difference > 31536000 || this.gantt.getState().max_date.getFullYear() != this.gantt.getState().min_date.getFullYear())
			{
				level = 4;
			}
			// More than 2 months
			else if(difference > 5256000 || this.gantt.getState().max_date.getMonth() != this.gantt.getState().min_date.getMonth())
			{
				level = 3;
			}
			// More than 3 days
			else if (difference > 259200)
			{
				level = 2;
			}
			else
			{
				level = 1;
			}
		}

		// Adjust Gantt settings for specified level
		switch(level)
		{
			case 4:
				// A year or more, scale in weeks
				subscales.push({unit: "month", step: 1, date: '%F %Y'});
				scale_unit = 'week';
				date_scale= '#%W';
				break;
			case 3:
				// Less than a year, several months
				subscales.push({unit: "month", step: 1, date: '%F %Y'});
				break;
			case 2:
			default:
				// About a month
				subscales.push({unit: "day", step: 1, date: '%F %d'});
				scale_unit = 'hour';
				date_scale = this.egw().preference('timeformat') == '24' ? "%G" : "%g";
				break;
			case 1:
				// A day or two, scale in Minutes
				subscales.push({unit: "day", step: 1, date: '%F %d'});
				date_scale = this.egw().preference('timeformat') == '24' ? "%G:%i" : "%g:%i";

				step = parseInt(this.egw().preference('interval','calendar') || 15);
				scale_unit = 'minute';
		}

		// Apply settings
		this.gantt.config.subscales = subscales;
		this.gantt.config.scale_unit = scale_unit;
		this.gantt.config.date_scale = date_scale;
		this.gantt.config.step = step;

		this.options.zoom = level;
		return level;
	},

	/**
	 * Bind all the internal gantt events for nice widget actions
	 */
	_bindGanttEvents: function() {
		var gantt_widget = this;

		// Click on scale to zoom - top zooms out, bottom zooms in
		this.gantt_node.on('click','.gantt_scale_line', function(e) {
			if(this.parentNode.firstChild == this)
			{
				// Zoom out
				gantt_widget.set_zoom(gantt_widget.options.zoom + 1);
				gantt_widget.gantt.render();
			}
			else if (gantt_widget.options.zoom > 1)
			{
				// Zoom in
				gantt_widget.set_zoom(gantt_widget.options.zoom - 1);
				gantt_widget.gantt.render();
			}
		});

		// Double click
		this.gantt.attachEvent("onBeforeLightbox", function(id) {
			var task = this.getTask(id);
			if(gantt_widget.options.on_edit)
			{
				gantt_widget.on_edit.apply(gantt_widget, [task,gantt_widget]);
			}
			/*
			if(task.pe_app)
			{
				gantt_widget.egw().open(task.pe_app_id, task.pe_app);
			}
			else
			{
				gantt_widget.egw().open(id, 'projectmanager');
			}
*/
			// Don't do gantt default actions
			return false;
		});

		// Update server after dragging a task
		this.gantt.attachEvent("onAfterTaskDrag", function(id, mode, e) {
			var task = jQuery.extend({},this.getTask(id));

			// Gantt chart deals with dates as Date objects, format as server likes
			var date_parser = this.date.date_to_str(this.config.api_date);
			if(task.start_date) task.start_date = date_parser(task.start_date);
			if(task.end_date) task.end_date = date_parser(task.end_date);
			
			var value = gantt_widget.getInstanceManager().getValues(gantt_widget.getInstanceManager().widgetContainer);

			if(gantt_widget.options.ajax_update)
			{
				var request = gantt_widget.egw().json(gantt_widget.options.ajax_update,
					[task,value]
				).sendRequest(true);
			}
		});

		// Bind AJAX for dynamic expansion
		// TODO: This could be improved
		this.gantt.attachEvent("onTaskOpened", function(id, item) {
			// Node children are already there & displayed
			var value = gantt_widget.getInstanceManager().getValues(gantt_widget.getInstanceManager().widgetContainer);

			var request = gantt_widget.egw().json(gantt_widget.options.autoload,
				[id,value],
				function(data) {
					this.parse(data);
				},
				this,true,this
			).sendRequest();
		});

		// Filters
		this.gantt.attachEvent("onBeforeTaskDisplay", function(id, task) {
			var display = true;
			gantt_widget.iterateOver(function(_widget){
				switch(_widget.id)
				{
					// Start and end date are an interval.  Also update the chart to
					// display those dates.  Special handling because date widgets give
					// value in timestamp (seconds), gantt wants Date object (ms)
					case 'start_date':
						if(_widget.getValue())
						{
							display = display && ((task[_widget.id].valueOf() / 1000) >= _widget.getValue());
						}
						return;
					case 'end_date':
						// End date is not actually a required field, so accept undefined too
						if(_widget.getValue())
						{
							display = display && (typeof task[_widget.id] == 'undefined' || !task[_widget.id] || ((task[_widget.id].valueOf() / 1000) <= _widget.getValue()));
						}
						return;
				}

				// Regular equality comparison
				if(_widget.getValue() && typeof task[_widget.id] != 'undefined')
				{
					if (task[_widget.id] != _widget.getValue())
					{
						display = false;
					}
					// Special comparison for objects, any intersection is a match
					if(!display && typeof task[_widget.id] == 'object' || typeof _widget.getValue() == 'object')
					{
						var a = typeof task[_widget.id] == 'object' ? task[_widget.id] : _widget.getValue();
						var b = a == task[_widget.id] ? _widget.getValue() : task[_widget.id];
						if(typeof b == 'object')
						{
							display = jQuery.map(a,function(x) {
								return jQuery.inArray(x,b) >= 0;
							});
						}
						else
						{
							display = jQuery.inArray(b,a) >= 0;
						}
					}
				}
			},gantt_widget, et2_inputWidget);
			return display;
		});
	},

	/**
	 * Bind onchange for any child input widgets
	 */
	_bindChildren: function() {
		var gantt_widget = this;
		this.iterateOver(function(_widget){
			// Existing change function
			var widget_change = _widget.change;

			var change = function(_node) {
				// Call previously set change function
				var result = widget_change.call(_widget,_node);

				// Update filters
				if(result && _widget.isDirty()) {
					// Update dirty
					_widget._oldValue = _widget.getValue();

					// Start date & end date change the display
					if(_widget.id == 'start_date' || _widget.id == 'end_date')
					{
						var start = this.getWidgetById('start_date');
						var end = this.getWidgetById('end_date');
						gantt_widget.gantt.config.start_date = start && start.getValue() ? new Date(start.getValue() * 1000) : gantt_widget.gantt.getState().min_date;
						gantt_widget.gantt.config.end_date = end && end.getValue() ? new Date(end.getValue() * 1000) : gantt_widget.gantt.getState().max_date;
						if(gantt_widget.gantt.config.end_date <= gantt_widget.gantt.config.start_date)
						{
							gantt_widget.gantt.config.end_date = null;
							if(end) end.set_value(null);
						}
						gantt_widget.set_zoom();
						gantt_widget.gantt.render();
					}

					gantt_widget.gantt.refreshData();
				}
				// In case this gets bound twice, it's important to return
				return true;
			};

			if(_widget.change != change) _widget.change = change;
		}, this, et2_inputWidget);
	}
});
et2_register_widget(et2_gantt, ["gantt"]);

/**
 * Common look, feel & settings for all Gantt charts
 */
// Localize to user's language - breaks if file is not there
//egw.includeJS("/phpgwapi/js/dhtmlxGantt/codebase/locale/locale_" + egw.preference('lang') + ".js");

// Set icon to match application
gantt.templates.grid_file = function(item) {
	if(!item.pe_app || !egw.image(item.pe_icon)) return "<div class='gantt_tree_icon gantt_file'></div>";
	return "<div class='gantt_tree_icon' style='background-image: url(\"" + egw.image(item.pe_icon) + "\");'/></div>";
}

// Show nicer intervals in minute duration
gantt.templates.date_scale = function(date) {
	if(gantt.config.scale_unit == 'minute')
	{
		date.setMinutes((date.getMinutes() % this.gantt.config.step) * this.gantt.config.step);
	}
	return gantt.date.date_to_str(gantt.config.date_scale)(date);
}