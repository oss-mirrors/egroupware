/**
 * EGroupware - Projectmanager - Javascript UI
 *
 * @link http://www.egroupware.org
 * @package projectmanager
 * @author Nahtan Gray
 * @copyright (c) 2013 by Nathan Gray
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

/**
 * UI for projectmanager
 *
 * @augments AppJS
 */
app.classes.projectmanager = AppJS.extend(
{
	appname: 'projectmanager',

	/**
	 * Constructor
	 *
	 * @memberOf app.projectmanager
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
	},

	/**
	 * Change the selected project
	 *
	 * This is a callback for the tree, either on click (node_id is a string) or
	 * context menu
	 *
	 * TODO: This could be a little more efficient and just change the project ID of
	 * whatever we're viewing
	 *
	 * Crazy parameters thanks to action system.
	 * @param {string|egwAction} node_id Either the selected leaf, or a context-menu action
	 * @param {et2_tree|egwActionObject[]} tree_widget Either the tree widget, or the selected leaf.
	 */
	set_project: function(node_id, tree_widget, old_node_id)
	{
		if(node_id == old_node_id)
		{
			return false;
		}
		if(typeof node_id == 'object' && tree_widget[0])
		{
			node_id = tree_widget[0].id;
		}

		if(node_id)
		{
			var split = node_id.split('::');
			if(split.length > 1 && split[1]) node_id = split[1];
			this.egw.open(node_id, 'projectmanager', 'view',{},'projectmanager','projectmanager');
		}
		else
		{
			this.egw.open('','projectmanager','list',{},'projectmanager','projectmanager');
		}
	},

	/**
	 * Handles delete button in edit popup
	 *
	 */
	p_element_delete: function()
	{
		var template = this.et2._inst;
		if (template)
		{
			var content = template.widgetContainer.getArrayMgr('content');
			var id = content.data['pe_id'];
		}
		console.log('I am element delete');
		opener.location.href= egw.link('/index.php', {
				menuaction: (content.data['caller'])? content.data['caller'] :'projectmanager.projectmanager_elements_ui.index',
				delete: id,
			});
		window.close();
	},

	/**
	 *
	 *
	 */
	calc_budget: function(form)
	{
		form['exec[pe_used_budget]'].value = form['exec[pe_used_quantity]'].value.replace(/,/,'.') * form['exec[pe_unitprice]'].value.replace(/,/,'.');
		if (form['exec[pe_used_budget]'].value == '0')
		{
			form['exec[pe_used_budget]'].value = '';
		}
		form['exec[pe_planned_budget]'].value = form['exec[pe_planned_quantity]'].value.replace(/,/,'.') * form['exec[pe_unitprice]'].value.replace(/,/,'.');
		if (form['exec[pe_planned_budget]'].value == '0')
		{
			form['exec[pe_planned_budget]'].value = '';
		}
	},
	/**
	 *
	 *
	 */


	/**
	 * Open window for a new project using link system, and pass on the
	 * template if one is selected.
	 *
	 * @param {etemplate_widget} widget The button, gives us access to the widget
	 *	context without needing to store a reference.
	 */
	new_project: function(widget)
	{
		// Find the template
		var template = '';
		if(typeof widget != 'undefined')
		{
			var templ_widget = widget.getRoot().getWidgetById('template_id');
			if(templ_widget)
			{
				template = templ_widget.getValue();
			}
		}
		else if (document.getElementById(et2_form_name('nm','template_id')))
		{
			template = document.getElementById(et2_form_name('nm','template_id')).value;
		}

		// Open the popup
		egw.open('','projectmanager','add',{'template': template},'_blank');
		return false;
	},

	/**
	 * Refresh the multi select box of eroles list
	 */
	erole_refresh: function(action)
	{
		switch (action)
		{
			case 'delete':
				return confirm("Delete this role?");
				break;
			case 'edit'	:
				break;
			default:
				this.et2._inst.submit();
				
		}
		
		// Refresh element edit so it knows about the new role
		var elemEditWind = window.opener;
		if(elemEditWind)
		{
			elemEditWind.location.reload();
			
			// Refresh list so it knows about the new role
			if (elemEditWind.opener)
			{
				elemEditWind.opener.egw_appWindow('projectmanager').location.reload();
			}
		}
	},

	/**
	 * Toggles display of a div
	 *
	 *  Used in erole list in element list, maybe others?
	 */
	toggleDiv: function(event, widget, target)
	{
		var element = $j(target).closest('div').parent('div').find('table.egwLinkMoreOptions');
		if($j(element).css('display') == 'none')
		{
			$j(element).fadeIn('medium');
		}
		else
		{
			$j(element).fadeOut('medium');
		}
	},

	/**
	 * Show a jpgraph gantt chart.
	 *
	 * The gantt chart is a single image of static size.  The size must be known
	 * in advance, so we include it in the GET request.
	 */
	show_gantt: function(action,selected)
	{
		var id = [];
		for(var i = 0; i < selected.length; i++)
		{
			// IDs look like projectmanager::#, or projectmanager_elements::projectmanager:#:#
			// gantt wants just #
			var split = selected[i].id.split('::');
			if(split.length > 1)
			{
				var matches = split[1].match(':([0-9]+):?');
				id.push(matches ? matches[1] : split[1]);
			}
		}
		egw.open_link(egw.link('/index.php', {
			menuaction: 'projectmanager.projectmanager_gantt.chart',
			pm_id:id.join(','), // Server expects CSV, not array
			width: $j(app.projectmanager.et2.getDOMNode() || window).width(),
			ajax: 'true'
		}), 'projectmanager',false,'projectmanager');
	},

	/**
	 * Handler for open action (double click) on the gantt chart
	 *
	 * @param {egwAction} action
	 * @param {egwActionObject[]} selected
	 */
	gantt_open_action: function(action,selected)
	{
		var task = {};
		if(selected[0].data)
		{
			task = selected[0].data;
		}
		// Project element
		if(task.pe_app)
		{
			this.egw.open(task.pe_app_id, task.pe_app);
		}
		else if (task.type && task.type == 'milestone')
		{
			egw.open_link(egw.link('/index.php',{
				menuaction: 'projectmanager.projectmanager_milestones_ui.edit',
				pm_id: task.pm_id,
				ms_id: task.ms_id
			}), false, '680x450', 'projectmanager');
		}
		// Project
		else
		{
			this.egw.open(task.pm_id, 'projectmanager');
		}
	},

	gantt_edit_element: function(action,selected)
	{
		var task = {};
		if(selected[0].data)
		{
			task = selected[0].data;
		}
		// Project element
		if(task.pe_id)
		{
			this.egw.open(task.pe_id, 'projectelement');
		}
	},

	/**
	 * Show the pricelist for a selected project
	 *
	 * @param {egwAction} action
	 * @param {egwActionObject[]} selected
	 */
	show_pricelist: function(action,selected)
	{
		var id = [];
		for(var i = 0; i < selected.length; i++)
		{
			// IDs look like projectmanager::#, or projectmanager_elements::projectmanager:#:#
			// pricelist wants just #
			var split = selected[i].id.split('::');
			if(split.length > 1)
			{
				var matches = split[1].match(':([0-9]+):?');
				id.push(matches ? matches[1] : split[1]);
			}
		}
		egw.open_link(egw.link('/index.php', {
			menuaction: 'projectmanager.projectmanager_pricelist_ui.index',
			pm_id:id.join(','), // Server expects CSV, not array
			ajax: 'true'
		}), 'projectmanager',false,'projectmanager');
	},

	/**
	 * Show the filemanager for a selected project
	 *
	 * @param {egwAction} action
	 * @param {egwActionObject[]} selected
	 */
	show_filemanager: function(action,selected)
	{
		var app = '';
		var id = '';
		for(var i = 0; i < selected.length && id == ''; i++)
		{
			// Data was provided, just read from there
			if(selected[i].data && selected[i].data.pe_app)
			{
				app = selected[i].data.pe_app;
				id = selected[i].data.pe_app_id;
			}
			else
			{
				// IDs look like projectmanager::#, or projectmanager_elements::app:app_id:element_id
				var split = selected[i].id.split('::');
				if(split.length > 1)
				{
					var matches = split[1].match('([_a-z]+):([0-9]+):?');
					if(matches != null)
					{
						app = matches[1];
						id = matches[2];
					}
					else
					{
						app = split[0];
						id = split[1];
					}
				}
			}
		}
		egw.open_link(egw.link('/index.php', {
			menuaction: 'filemanager.filemanager_ui.index',
			path: '/apps/'+app+'/'+id,
			ajax: 'true'
		}), 'filemanager',false,'filemanager');
	},

	/**
	 * Enabled check for erole action
	 *
	 * @param {egwAction} action
	 * @param {egwActionObject[]} selected
	 */
	is_erole_allowed: function(action,selected)
	{
		var allowed = true;

		// Some eroles can only be assigned to a single element.  If already assigned,
		// they won't be an action, but we'll prevent setting multiple elements
		if(action.data && !action.data.role_multi && selected.length > 1)
		{
			allowed = false;
		}
		
		// Erole is limited to only these apps, from projectmanager_elements_bo
		var erole_apps = ['addressbook','calendar','infolog'];

		for(var i = 0; i < selected.length && allowed; i++)
		{
			var data = selected[i].data || egw.dataGetUIDdata(selected[i].id);
			if(data && data.data) data = data.data;
			if(!data)
			{
				allowed = false;
				continue;
			}
			if(erole_apps.indexOf(data.pe_app) < 0)
			{
				allowed = false
			}
		}
		
		return allowed;
	},
	
	/**
	 * Add new record's apps to a project
	 * 
	 * @param {action object} action
	 *
	 */
	add_new: function (action)
	{
		var content = this.et2.getArrayMgr('content');
		if (typeof content != 'undefined')
		{
			var pm_id = content.getEntry('project_tree');

			// Gantt chart can have multiple selected
			if(jQuery.isArray(pm_id)) pm_id = pm_id[0];
			
			pm_id = pm_id.replace('::',':');
			if (typeof action != 'undefined')
			{
				this.egw.open(pm_id, action.id.replace('act-',''), 'add');
			}
		}
	}
});
