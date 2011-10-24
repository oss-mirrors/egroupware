/**
 * EGroupware eTemplate2 - JS Progrss object
 *
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package etemplate
 * @subpackage api
 * @link http://www.egroupware.org
 * @author Ralf Becker
 * @version $Id$
 */

"use strict";

/*egw:uses
	jquery.jquery;
	et2_core_interfaces;
	et2_core_valueWidget;
*/

/**
 * Class which implements the "image" XET-Tag
 */ 
var et2_progress = et2_valueWidget.extend(et2_IDetachedDOM, 
{
	attributes: {
		"href": {
			"name": "Link Target",
			"type": "string",
			"description": "Link URL, empty if you don't wan't to display a link."
		},
		"extra_link_target": {
			"name": "Link target",
			"type": "string",
			"default": "_self",
			"description": "Link target descriptor"
		},
		"extra_link_popup": {
			"name": "Popup",
			"type": "string",
			"description": "widthxheight, if popup should be used, eg. 640x480"
		},
		"label": {
			"name": "Label",
			"default": "",
			"type": "string",
			"description": "The label is displayed as the title.  The label can contain variables, as descript for name. If the label starts with a '@' it is replaced by the value of the content-array at this index (with the '@'-removed and after expanding the variables).",
			"translate": true
		},
		"onchange": {
			"name": "onchange",
			"type": "js",
			"description": "JS code which is executed when the value changes."
		}
	},
	legacyOptions: ["href", "extra_link_target", "imagemap", "extra_link_popup", "id"],

	init: function() 
	{
		this._super.apply(this, arguments);

		var outer = document.createElement("div");
		outer.className = "et2_progress";
		this.progress = document.createElement("div");
		this.progress.style.width = "0";
		outer.appendChild(this.progress);

		if (this.options.href) 
		{
			outer.className += ' et2_clickable';
		}
		if(this.options["class"])
		{
			outer.className += ' '+this.options["class"];
		}
		this.setDOMNode(outer);	// set's this.node = outer
	},
	
	click: function()
	{
		this._super.apply(this, arguments);

		if(this.options.href)
		{
			egw.call_link(this.options.href, this.options.extra_link_target, this.options.extra_link_popup);
		}
	},

	// setting the value as width of the progress-bar
	set_value: function(_value) 
	{
		_value = parseInt(_value)+"%";	// make sure we have percent attached
		this.progress.style.width = _value;
		if (!this.options.label) this.set_label(_value);
	},
	
	// set's label as title of this.node
	set_label: function(_value) 
	{
		this.node.title = _value;
	},

	/**
	 * Implementation of "et2_IDetachedDOM" for fast viewing in gridview
	 */

	getDetachedAttributes: function(_attrs) {
		_attrs.push("value", "label");
	},

	getDetachedNodes: function() {
		return [this.node];
	},

	setDetachedAttributes: function(_nodes, _values) {
		// Set the given DOM-Nodes
		this.node = _nodes[0];

		// Set the attributes
		if (_values["label"])
		{
			this.set_label(_values["label"]);
		}
		if (_values["value"])
		{
			this.set_value(_values["value"]);
		}
	}
});

et2_register_widget(et2_progress, ["progress"]);
