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
	et2_core_baseWidget;
*/

/**
 * Class which implements the "image" XET-Tag
 */ 
var et2_progress = et2_baseWidget.extend(/*et2_IDetachedDOM,*/ {

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
		}
	},
	legacyOptions: ["href", "extra_link_target", "imagemap", "extra_link_popup", "id"],

	init: function() 
	{
		this._super.apply(this, arguments);

		var outer = $j(document.createElement("div")).addClass("et2_progress");
		this.node = $j(document.createElement("div")).width(0).appendTo(outer);

		if (this.options.href) 
		{
			outer.addClass('et2_clickable');
		}
		if(this.options["class"])
		{
			outer.addClass(this.options["class"]);
		}
		this.setDOMNode(outer[0]);
// gives error "this.node has no method width"
// this.set_value(50);
	},
	
	click: function()
	{
		if(this.options.href)
		{
			egw.call_link(this.options.href, this.options.extra_link_target, this.options.extra_link_popup);
		}		
	},

	// tried set_value and setValue, both get never called :-(
	set_value: function(_value) 
	{
		if (_value != "") _value = parseInt(_value)+"%";	// make sure we have percent attached
		this.node.width(_value);
		if (!this.options.label) this.set_label(_value);
	},
	
	// set's label as title of this.node
	set_label: function(_value) 
	{
		this.node.attr("title", _value);
	}

	/**
	 * Implementation of "et2_IDetachedDOM" for fast viewing in gridview
	 */
/*
	getDetachedAttributes: function(_attrs) {
		_attrs.push("src", "label");
	},

	getDetachedNodes: function() {
		return [this.node[0]];
	},

	setDetachedAttributes: function(_nodes, _values) {
		// Set the given DOM-Nodes
		this.node = $j(_nodes[0]);

		this.transformAttributes(_values);

		// Set the attributes
		if (_values["src"])
		{
			this.set_value(_values["value"]);
		}

		if (_values["label"])
		{
			this.set_label(_values["label"]);
		}
	}*/
});

et2_register_widget(et2_progress, ["progress"]);
