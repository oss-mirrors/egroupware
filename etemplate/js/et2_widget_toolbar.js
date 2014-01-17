/**
 * EGroupware eTemplate2 - JS toolbar object
 *
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package etemplate
 * @subpackage api
 * @link http://www.egroupware.org
 * @author Nathan Gray
 * @copyright Nathan Gray 2013
 * @version $Id$
 */

"use strict";

/*egw:uses
	jquery.jquery;
	jquery.jquery-ui;
	et2_DOMWidget;
*/

/**
 * This toolbar gets its contents from its actions
 *
 * @augments et2_valueWidget
 */
var et2_toolbar = et2_DOMWidget.extend(
{
	attributes: {
	},

	/**
	 * Default buttons, so there is something for the widget browser / editor to show
	 */
	default_toolbar: {
		view: {caption:'View', icons: {primary: 'ui-icon-check'}, group:1},
		edit: {caption:'Edit', group:1},
		save: {caption:'Save', group:2}
	},

	/**
	 * Constructor
	 *
	 * @memberOf et2_dropdown_button
	 */
	init: function() {
		this._super.apply(this, arguments);
		this.div = $j(document.createElement('div'))
			.addClass('et2_toolbar ui-widget-header ui-corner-all');

		//actionbox is the div for stored actions
		this.actionbox = $j(document.createElement('div'))
				.addClass("et2_toolbar_activeList")
				.attr('id',this.id +'-'+ 'actionbox');
		//actionlist is div for active actions
		this.actionlist = $j(document.createElement('div'))

			.attr('id',this.id +'-'+ 'actionlist');

		this.dropdowns = {};
		this.preference = {};

		if (typeof this.preference != "undefined")
		{
			console.log();
		}
		this._build_menu(this.default_toolbar);
	},

	destroy: function() {
		// Destroy widget
		if(this.div && this.div.data('ui-menu')) this.menu.menu("destroy");

		// Null children

		// Remove
		this.div.empty().remove();
		this.actionbox.empty().remove();
		this.actionlist.empty().remove();
	},

	/**
	 * Go through actions and build buttons for the toolbar
	 */
	_build_menu: function(actions)
	{
		// Clear existing
		this.div.empty();
		this.actionbox.empty();
		this.actionlist.empty();
		this.actionbox.append('<h></h>');
		this.actionbox.append('<div id="' + this.id + '-menulist' +'" class="ui-toolbar-menulist" ></div>');

		this.preference = egw.preference(this.id,this.egw().getAppName())?egw.preference(this.id,this.egw().getAppName()):this.preference;

		var last_group = false;
		var last_group_id = false;
		for(var name in actions)
		{
			var action = actions[name];

			// Add in divider
			if(last_group_id != action.group)
			{
				last_group = $j('[data-group="' + action.group + '"]',this.actionlist);
				if(last_group.length == 0)
				{
					$j('<span data-group="'+action.group+'">').appendTo(this.actionlist);
				}
				last_group_id = action.group;
			}

			// Make sure there's something to display
			if(!action.caption && !action.icon && !action.iconUrl) continue;

			if(action.children)
			{
				this.dropdowns[action.id] = $j(document.createElement('span'))
					.addClass("ui-state-default")
					.appendTo(last_group);
				var children = {};
				var add_children = function(root, children) {
					for(var id in root.children)
					{
						var info = {
							id: id || root.children[id].id,
							label: root.children[id].caption
						};
						if(root.children[id].iconUrl)
						{
							info.icon = root.children[id].iconUrl;
						}
						if(root.children[id].children)
						{
							add_children(root.children[id], info);
						}
						children[id] = info;
					}
				};
				add_children(action, children);
				var dropdown = et2_createWidget("dropdown_button", {
					id: action.id,
					label: action.caption
				},this);
				dropdown.set_select_options(children);
				var toolbar = this;
				dropdown.onchange = jQuery.proxy(function(selected, dropdown)
				{
					var action = toolbar._actionManager.getActionById(selected.attr('data-id'));
					if(action)
					{
						if(action)
						{
							action.onExecute.exec(action);
						}
					}
					console.debug(selected, this, action);
				},action);
				dropdown.onclick = jQuery.proxy(function(selected, dropdown)
				{
					var action = toolbar._actionManager.getActionById(this.getValue());
					if(action)
					{
						if(action)
						{
							action.onExecute.exec(action);
						}
					}
					console.debug(selected, this, action);
				},dropdown);
			}
			else
			{
				this._make_button(action);
			}
		}

		// ************** Drag and Drop feature for toolbar *****
		this.actionlist.appendTo(this.div);
		this.actionbox.appendTo(this.div);

		var toolbar =jQuery('#'+this.id+'-'+'actionlist').find('button'),
			toolbox = jQuery('#'+this.id+'-'+'actionbox'),
			menulist = jQuery('#'+this.id+'-'+'menulist');

		toolbar.draggable({
			cancel:true,
			//revert:"invalid",
			containment: "document",
			cursor: "move",
			helper: "clone",
		});
		menulist.children().draggable({
			cancel:true,
			containment:"document",
			helper:"clone",
			cursor:"move",
		});
		var that = this;
		toolbox.droppable({
			accept:toolbar,
			drop:function (event, ui) {
					that.set_prefered(ui.draggable.attr('id').replace(that.id+'-',''),"add");
					ui.draggable.appendTo(menulist);
			},
			tolerance:"pointer",

			});

		jQuery('#'+this.id+'-'+'actionlist').droppable({
			tolerance:"pointer",
			drop:function (event,ui) {
				that.set_prefered(ui.draggable.attr('id').replace(that.id+'-',''),"remove");
				ui.draggable.appendTo(jQuery('#'+that.id+'-'+'actionlist'));
			}
		});
		toolbox.accordion({
			heightStyle:"fill",
			collapsible: true,
			active:'none'
		});
	},

	/**
	 * Add/Or remove an action from prefence
	 *
	 * @param {string} _action name of the action which needs to be stored in pereference
	 * @param {string} _do if set to "add" add the action to preference and "remove" remove one from preference
	 *
	 */
	set_prefered: function(_action,_do)
	{
		switch(_do)
		{
			case "add":
				this.preference[_action] = _action;
				egw.set_preference(this.egw().getAppName(),this.id,this.preference);
				break;
			case "remove":
				delete this.preference[_action];
				egw.set_preference(this.egw().getAppName(),this.id,this.preference);
		}
	},

	/**
	 * Make a button based on the given action
	 */
	_make_button: function(action)
	{
		var button_options = {
		};
		var button = $j(document.createElement('button'))
			.addClass("et2_button")
			.attr('id', this.id+'-'+action.id)
			.appendTo(this.preference[action.id]?this.actionbox.children()[1]:$j('[data-group='+action.group+']',this.actionlist));
		if(action.iconUrl)
		{
			button.prepend("<img src='"+action.iconUrl+"' title='"+action.caption+"' class='et2_button_icon'/>");
		}
		if(action.icon)
		{
			button_options.icon = action.icon
		}
		button.button(button_options);

		// Set up the click action
		var click = function(e) {
			var action = this._actionManager.getActionById(e.data);
			if(action)
			{
				action.data.event = e;
				action.onExecute.exec(action);
			}
		};

		button.click(action.id, jQuery.proxy(click, this));
	},

	/**
	 * Link the actions to the DOM nodes / widget bits.
	 *
	 */
	_link_actions: function(actions)
	{
		this._build_menu(actions);
	},

	getDOMNode: function(asker)
	{
		if(asker != this && asker.id && this.dropdowns[asker.id])
		{
			return this.dropdowns[asker.id][0];
		}
		return this.div[0];
	}
});
et2_register_widget(et2_toolbar, ["toolbar"]);

