/**
 * eGroupWare egw_action framework - egw action framework
 *
 * @link http://www.egroupware.org
 * @author Andreas Stöckel <as@stylite.de>
 * @copyright 2011 by Andreas Stöckel
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package egw_action
 * @version $Id$
 */

/*egw:uses
	jquery.jquery;
	egw_menu;
	/phpgwapi/js/jquery/jquery-tap-and-hold/jquery.tapandhold.js;
*/

if (typeof window._egwActionClasses == "undefined")
	window._egwActionClasses = {}
_egwActionClasses["popup"] = {
	"actionConstructor": egwPopupAction,
	"implementation": getPopupImplementation
}

function egwPopupAction(_id, _handler, _caption, _icon, _onExecute, _allowOnMultiple)
{
	var action = new egwAction(_id, _handler, _caption, _icon, _onExecute, _allowOnMultiple);
	action.type = "popup";
	action.canHaveChildren = ["popup"];
	action["default"] = false;
	action.order = 0;
	action.group = 0;
	action.hint = false;
	action.checkbox = false;
	action.radioGroup = 0;
	action.checked = false;
	action.shortcut = null;

	action.set_default = function(_value) {
		action["default"] = _value;
	}

	action.set_order = function(_value) {
		action.order = _value;
	}

	action.set_group = function(_value) {
		action.group = _value;
	}

	action.set_hint = function(_value) {
		action.hint = _value;
	}

	// If true, the action will be rendered as checkbox
	action.set_checkbox = function(_value) {
		action.checkbox = _value;
	}

	action.set_checked = function(_value) {
		action.checked = _value;
	}

	// If radioGroup is >0 and the element is a checkbox, radioGroup specifies
	// the group of radio buttons this one belongs to
	action.set_radioGroup = function(_value) {
		action.radioGroup = _value;
	}

	action.set_shortcut = function(_value) {
		if (_value)
		{
			var sc = {
				"keyCode": -1,
				"shift": false,
				"ctrl": false,
				"alt": false
			}

			if (typeof _value == "object" && typeof _value.keyCode != "undefined" &&
			    typeof _value.caption != "undefined")
			{
				sc.keyCode = _value.keyCode;
				sc.caption = _value.caption;
				sc.shift = (typeof _value.shift == "undefined") ? false : _value.shift;
				sc.ctrl = (typeof _value.ctrl == "undefined") ? false : _value.ctrl;
				sc.alt = (typeof _value.alt == "undefined") ? false : _value.alt;
			}

			this.shortcut = sc;
		}
		else
		{
			this.shortcut = false;
		}
	}

	return action;
}

var
	_popupActionImpl = null;

function getPopupImplementation()
{
	if (!_popupActionImpl)
	{
		_popupActionImpl = new egwPopupActionImplementation();
	}
	return _popupActionImpl
}

function egwPopupActionImplementation()
{
	var ai = new egwActionImplementation();

	ai.type = "popup";

	/**
	 * Registers the handler for the default action
	 */
	ai._registerDefault = function(_node, _callback, _context) {
		var defaultHandler = function(e) {
			if (typeof document.selection != "undefined" && typeof document.selection.empty != "undefined")
			{
				document.selection.empty();
			}
			else if( typeof window.getSelection != "undefined")
			{
				var sel = window.getSelection();
				sel.removeAllRanges();
			}

			_callback.call(_context, "default", ai);

			return false;
		}

		if (egwIsMobile()) {
			$j(_node).bind('click', defaultHandler);
		} else {
			_node.ondblclick = defaultHandler;
		}
	}

	ai._getDefaultLink = function(_links) {
		var defaultAction = null;
		for (var k in _links)
		{
			if (_links[k].actionObj["default"] && _links[k].enabled)
			{
				defaultAction = _links[k].actionObj;
				break;
			}
		}

		return defaultAction;
	}

	ai._searchShortcut = function (_key, _objs, _links) {
		for (var i = 0; i < _objs.length; i++)
		{
			var sc = _objs[i].shortcut;
			if (sc && sc.keyCode == _key.keyCode && sc.shift == _key.shift &&
			    sc.ctrl == _key.ctrl && sc.alt == _key.alt &&
			    _objs[i].type == "popup" && (typeof _links[_objs[i].id] == "undefined" ||
			    _links[_objs[i].id].enabled))
			{
				return _objs[i];
			}

			var obj = this._searchShortcut(_key, _objs[i].children, _links);
			if (obj) {
				return obj;
			}
		}
	}

	ai._searchShortcutInLinks = function(_key, _links) {
		var objs = [];
		for (var k in _links)
		{
			if (_links[k].enabled)
			{
				objs.push(_links[k].actionObj);
			}
		}

		return ai._searchShortcut(_key, objs, _links);
	}

	/**
	 * Handles a key press
	 */
	ai._handleKeyPress = function(_key, _selected, _links, _target) {
		// Handle the default
		if (_key.keyCode == EGW_KEY_ENTER && !_key.ctrl && !_key.shift && !_key.alt) {
			var defaultAction = this._getDefaultLink(_links);
			if (defaultAction)
			{
				defaultAction.execute(_selected);
				return true;
			}
		}

		// Menu button
		if (_key.keyCode == EGW_KEY_MENU && !_key.ctrl)
		{
			return this.doExecuteImplementation({posx:0,posy:0}, _selected, _links, _target);
		}
			

		// Check whether the given shortcut exists
		var obj = this._searchShortcutInLinks(_key, _links);
		if (obj)
		{
			obj.execute(_selected);
			return true;
		}

		return false;
	}

	/**
	 * Registers the handler for the context menu
	 */
	ai._registerContext = function(_node, _callback, _context) {
		var contextHandler = function(e) {
			//Obtain the event object
			if (!e)
			{
				e = window.event;
			}

			if (_egw_active_menu)
			{
				_egw_active_menu.hide()
			}
			else if (!e.ctrlKey)
			{
				_xy = ai._getPageXY(e);
				_callback.call(_context, _xy, ai);
			}

			e.cancelBubble = !e.ctrlKey;
			if (e.stopPropagation && !e.ctrlKey)
			{
				e.stopPropagation();
			}
			return e.ctrlKey;
		}

		if (egwIsMobile()) {
			$j(_node).bind('taphold', contextHandler);
		} else {
			$j(_node).on('contextmenu', contextHandler);
		}
	}

	ai.doRegisterAction = function(_aoi, _callback, _context)
	{
		var node = _aoi.getDOMNode();

		if (node)
		{
			this._registerDefault(node, _callback, _context);
			this._registerContext(node, _callback, _context);
			return true;
		}
		return false;
	}

	ai.doUnregisterAction = function(_aoi)
	{
		//
	}

	/**
	 * Builds the context menu and shows it at the given position/DOM-Node.
	 */
	ai.doExecuteImplementation = function(_context, _selected, _links, _target)
	{
		if (typeof _target == "undefined")
		{
			_target = null;
		}

		if (typeof _context == "object" && typeof _context.keyEvent == "object")
		{
			return ai._handleKeyPress(_context.keyEvent, _selected, _links, _target);
		}
		else if (_context != "default")
		{
			//Check whether the context has the posx and posy parameters
			if ((typeof _context.posx != "number" || typeof _context.posy != "number") &&
			    typeof _context.id != "undefined")
			{
				// Calculate context menu position from the given DOM-Node
				var node = _context;

				x = $j(node).offset().left;
				y = $j(node).offset().top;

				_context = {"posx": x, "posy": y}
			}

			var menu = ai._buildMenu(_links, _selected, _target);
			menu.showAt(_context.posx, _context.posy);

			return true;
		}
		else
		{
			var defaultAction = ai._getDefaultLink(_links);
			if (defaultAction)
			{
				defaultAction.execute(_selected);
			}
		}

		return false;
	}

	/**
	 * Groups and sorts the given action tree layer
	 */
	ai._groupLayers = function(_layer, _links, _parentGroup)
	{
		// Seperate the multiple groups out of the layer
		var link_groups = {};

		for (var i = 0; i < _layer.children.length; i++)
		{
			var actionObj = _layer.children[i].action;

			// Check whether the link group of the current element already exists,
			// if not, create the group
			var grp = actionObj.group;
			if (typeof link_groups[grp] == "undefined")
			{
				link_groups[grp] = [];
			}

			// Search the link data for this action object if none is found,
			// visible and enabled = true is assumed
			var visible = true;
			var enabled = true;

			if (typeof _links[actionObj.id] != "undefined")
			{
				visible = _links[actionObj.id].visible;
				enabled = _links[actionObj.id].enabled;
			}

			// Insert the element in order
			var inserted = false;
			var groupObj = {
				"actionObj": actionObj,
				"visible": visible,
				"enabled": enabled,
				"groups": []
			};

			for (var j = 0; j < link_groups[grp].length; j++)
			{
				var elem = link_groups[grp][j].actionObj;
				if (elem.order > actionObj.order)
				{
					inserted = true;
					link_groups[grp].splice(j, 0, groupObj);
					break;
				}
			}

			// If the object hasn't been inserted, add it to the end of the list
			if (!inserted)
			{
				link_groups[grp].push(groupObj);
			}

			// If this child itself has children, group those elements too
			if (_layer.children[i].children.length > 0)
			{
				this._groupLayers(_layer.children[i], _links, groupObj);
			}
		}

		// Transform the link_groups object into an sorted array
		var groups = [];

		for (var k in link_groups)
		{
			groups.push({"grp": k, "links": link_groups[k]});
		}

		groups.sort(function(a, b) {
			var ia = parseInt(a.grp);
			var ib = parseInt(b.grp);
			return (ia > ib) ? 1 : ((ia < ib) ? -1 : 0);
		});

		// Append the groups to the groups2 array
		var groups2 = [];
		for (var i = 0; i < groups.length; i++)
		{
			groups2.push(groups[i].links);
		}

		_parentGroup.groups = groups2;
	}

	/**
	 * Build the menu layers
	 */
	ai._buildMenuLayer = function(_menu, _groups, _selected, _enabled, _target)
	{
		var firstGroup = true;

		for (var i = 0; i < _groups.length; i++)
		{
			var firstElem = true;

			// Go through the elements of each group
			for (var j = 0; j < _groups[i].length; j++)
			{
				var link = _groups[i][j];

				if (link.visible)
				{
					// Add an seperator after each group
					if (!firstGroup && firstElem)
					{
						_menu.addItem("", "-");
					}
					firstElem = false;

					var item = _menu.addItem(link.actionObj.id, link.actionObj.caption,
						link.actionObj.iconUrl);
					item["default"] = link.actionObj["default"];

					// As this code is also used when a drag-drop popup menu is built,
					// we have to perform this check
					if (link.actionObj.type == "popup")
					{
						item.set_hint(link.actionObj.hint);
						item.set_checkbox(link.actionObj.checkbox);
						item.set_checked(link.actionObj.checked);
						item.set_groupIndex(link.actionObj.radioGroup);

						if (link.actionObj.shortcut)
						{
							var sc = link.actionObj.shortcut;
							item.set_shortcutCaption(sc.caption);
						}
					}

					item.set_data(link.actionObj);
					if (link.enabled && _enabled)
					{
						item.set_onClick(function(elem) {
							// Copy the "checked" state
							if (typeof elem.data.checked != "undefined")
							{
								elem.data.checked = elem.checked;
							}

							elem.data.execute(_selected, _target);

							if (typeof elem.data.checkbox != "undefined" && elem.data.checkbox)
							{
								return elem.data.checked;
							}
						});
					}
					else
					{
						item.set_enabled(false);
					}

					// Append the parent groups
					if (link.groups)
					{
						this._buildMenuLayer(item, link.groups, _selected, link.enabled, _target);
					}
				}
			}

			firstGroup = firstGroup && firstElem;
		}
	}

	/**
	 * Builds the context menu from the given action links
	 */
	ai._buildMenu = function(_links, _selected, _target)
	{
		// Build a tree containing all actions
		var tree = {"root": []};

		for (var k in _links)
		{
			_links[k].actionObj.appendToTree(tree);
		}

		// We need the dummy object container in order to pass the array by 
		// reference
		var groups = {
			"groups": []
		};

		if (tree.root.length > 0)
		{
			// Sort every action object layer by the given sort position and grouping
			this._groupLayers(tree.root[0], _links, groups);
		}

		var menu = new egwMenu();

		// Build the menu layers
		this._buildMenuLayer(menu, groups.groups, _selected, true, _target);

		return menu;
	}

	ai._getPageXY = function getPageXY(event)
	{
		// document.body.scrollTop does not work in IE
		var scrollTop = document.body.scrollTop ? document.body.scrollTop :
			document.documentElement.scrollTop;
		var scrollLeft = document.body.scrollLeft ? document.body.scrollLeft :
			document.documentElement.scrollLeft;

		return {'posx': (event.clientX + scrollLeft), 'posy': (event.clientY + scrollTop)};
	}

	return ai;
}



