/**
 * EGroupware - Infolog - Javascript UI
 *
 * @link http://www.egroupware.org
 * @package infolog
 * @author Hadi Nategh	<hn-AT-stylite.de>
 * @copyright (c) 2008-13 by Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

/**
 * UI for Infolog
 *
 * @augments AppJS
 */
app.classes.infolog = AppJS.extend(
{
	appname: 'infolog',
	/**
	 * et2 widget container
	 */
	et2: null,
	/**
	 * path widget
	 */

	/**
	 * Constructor
	 *
	 * @memberOf app.infolog
	 */
	init: function()
	{
		// call parent
		this._super.apply(this, arguments);
	},

	/**
	 * Destructor
	 */
	destroy: function()
	{
		delete this.et2;
		// call parent
		this._super.apply(this, arguments);
	},

	/**
	 * This function is called when the etemplate2 object is loaded
	 * and ready.  If you must store a reference to the et2 object,
	 * make sure to clean it up in destroy().
	 *
	 * @param et2 etemplate2 Newly ready object
	 */
	et2_ready: function(et2)
	{
		// call parent
		this._super.apply(this, arguments);

		if(typeof et2.templates['infolog.index'] != 'undefined')
		{
			this.filter_change();
		}
	},

	/**
	 *
	 */
	filter_change: function()
	{

		var filter = this.et2.getWidgetById('filter');
		var temp_header_left = this.et2.getWidgetById('infolog.index.dates');

		if (filter && temp_header_left)
		{
			temp_header_left.set_disabled(filter.value !== "bydate");

		}

	},

	/**
	 * show or hide the details of rows by selecting the filter2 option
	 * either 'all' for details or 'no_description' for no details
	 *
	 */
	filter2_change: function()
	{
		var nm = this.et2.getWidgetById('nm');
		var filter2 = this.et2.getWidgetById('filter2');

		if (nm && filter2)
		{
			// Show / hide descriptions
			this.show_details(filter2.value == 'all');

			// Change preference location - widget is nextmatch
			nm.options.settings.columnselection_pref = 'infolog.index.rows'+(filter2.value == 'all' ? '-details' :'');

			// Load new preferences
			var colData = []
			for(var i = 0; i < nm.columns.length; i++) colData[i] = {disabled: true, width: '0'};
			nm._applyUserPreferences(nm.columns, colData);
			for(var i = 0; i < colData.length; i++)
			{
				// Wants a string
				nm.dataview.getColumnMgr().columns[i].set_width(colData[i].width + 'px');
				nm.dataview.getColumnMgr().columns[i].set_visibility(!colData[i].disabled);
			}
			nm.dataview.getColumnMgr().updated = true;
			// Update page
			nm.dataview.updateColumns();
		}
	},

	/**
	 * Show or hide details by changing the CSS class
	 *
	 * @param show
	 */
	show_details: function(show)
	{
		// Show / hide descriptions
        egw.css(".et2_box.infoDes","display:" + (show ? "block;" : "none;"));
	},

	confirm_delete_2: function (_action, _senders)
	{
		var children = false;
		var child_button = jQuery('#delete_sub').get(0) || jQuery('[id*="delete_sub"]').get(0);
		if(child_button)
		{
			for(var i = 0; i < _senders.length; i++)
			{
				if ($j(_senders[i].iface.node).hasClass('infolog_rowHasSubs'))
				{
					children = true;
					break;
				}
			}
			child_button.style.display = children ? 'block' : 'none';
		}
		var callbackDeleteDialog = function (button_id)
					{
						if (button_id == et2_dialog.YES_BUTTON )
						{

						}
					}
		var confirmDeleteDialog = et2_dialog.show_dialog(callbackDeleteDialog, this.egw.lang("Do you really want to DELETE this Rule"),this.egw.lang("Delete"), {},et2_dialog.BUTTONS_YES_NO_CANCEL, et2_dialog.WARNING_MESSAGE);

	},

	/**
	 * Confirm delete
	 * If entry has children, asks if you want to delete children too
	 *
	 *@param _action
	 *@param _senders
	 */
	confirm_delete: function(_action, _senders)
	{
		var children = false;
		var child_button = jQuery('#delete_sub').get(0) || jQuery('[id*="delete_sub"]').get(0);
		if(child_button)
		{
			for(var i = 0; i < _senders.length; i++)
			{
				if ($j(_senders[i].iface.node).hasClass('infolog_rowHasSubs'))
				{
					children = true;
					break;
				}
			}
			child_button.style.display = children ? 'block' : 'none';
		}
		nm_open_popup(_action, _senders);
	},

	/**
	 * Add email from addressbook
	 *
	 * @param ab_id
	 * @param info_cc
	 */
	add_email_from_ab: function(ab_id,info_cc)
	{
		var ab = document.getElementById(ab_id);

		if (!ab || !ab.value)
		{
			jQuery("tr.hiddenRow").css("display", "table-row");
		}
		else
		{
			var cc = document.getElementById(info_cc);

			for(var i=0; i < ab.options.length && ab.options[i].value != ab.value; ++i) ;

			if (i < ab.options.length)
			{
				cc.value += (cc.value?', ':'')+ab.options[i].text.replace(/^.* <(.*)>$/,'$1');
				ab.value = '';
				ab.onchange();
				jQuery("tr.hiddenRow").css("display", "none");
			}
		}
		return false;
	},

	/**
	* If one of info_status, info_percent or info_datecompleted changed --> set others to reasonable values
	*
	* @param string changed_id id of changed element
	* @param string status_id
	* @param string percent_id
	* @param string datecompleted_id
	*/
	status_changed: function(changed_id, status_id, percent_id, datecompleted_id)
	{
		var status = document.getElementById(status_id);
		var percent = document.getElementById(percent_id);
		var datecompleted = document.getElementById(datecompleted_id+'[str]');
		if(!datecompleted)
		{
			datecompleted = jQuery('#'+datecompleted_id +' input').get(0);
		}
		var completed;

		switch(changed_id)
		{
			case status_id:
				completed = status.value == 'done' || status.value == 'billed';
				if (completed || status.value == 'not-started' ||
					(status.value == 'ongoing') != (percent.value > 0 && percent.value < 100))
				{
					percent.value = completed ? 100 : (status.value == 'not-started' ? 0 : 10);
				}
				break;

			case percent_id:
				completed = percent.value == 100;
				if (completed != (status.value == 'done' || status.value == 'billed') ||
					(status.value == 'not-started') != (percent.value == 0))
				{
					status.value = percent.value == 0 ? 'not-started' : (percent.value == 100 ? 'done' : 'ongoing');
				}
				break;

			case datecompleted_id+'[str]':
			case datecompleted_id:
				completed = datecompleted.value != '';
				if (completed != (status.value == 'done' || status.value == 'billed'))
				{
					status.value = completed ? 'done' : 'not-started';
				}
				if (completed != (percent.value == 100))
				{
					percent.value = completed ? 100 : 0;
				}
				break;
		}
		if (!completed && datecompleted && datecompleted.value != '')
		{
			datecompleted.value = '';
		}
		else if (completed && datecompleted && datecompleted.value == '')
		{
			// todo: set current date in correct format
		}
	},

	/**
	 * handle "print" action from "Actions" selectbox in edit infolog window.
	 * check if the template is dirty then submit the template otherwise just open new window as print.
	 *
	 */
	edit_actions: function()
	{
		var widget = this.et2.getWidgetById('action');
		var template = this.et2._inst;
		if (template)
		{
			var id = template.widgetContainer.getArrayMgr('content').data['info_id'];
		}
		if (widget)
		{
			switch (widget.get_value())
			{
				case 'print':
					if (template.isDirty())
					{
						template.submit();
					}
					egw_open(id,'infolog','edit',{print:1});
					break;
				default:
					template.submit();
			}
		}
	},

	/**
	 *
	 */
	add_link_sidemenu: function()
	{
		egw.open('','infolog','add');
	},

	/**
	 * Opens a new edit dialog with some extra url parameters pulled from
	 * standard locations.  Done with a function instead of hardcoding so
	 * the values can be updated if user changes them in UI.
	 *
	 * @param _type string Type of infolog entry
	 * @param _action string Special action for new infolog entry
	 * @param _action_id string ID for special action
	 */
	add_with_extras: function(_type, _action, _action_id)
	{
		var nm = this.et2.getWidgetById('nm');
		var nm_value = nm.getValue() || {};

		var extras = {
			type: _type || nm_value.filter || null,
			cat_id: nm_value.cat_id || null,
			action: _action || null,
			action_id: _action_id != '0' ? _action_id : null || null
		};

		// Remove any missing values
		for(var key in extras)
		{
			if(extras[key] == null)
			{
				delete extras[key];
			}
		}
		egw.open('','infolog','add',extras);
	}
});
