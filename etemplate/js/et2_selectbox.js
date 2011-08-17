/**
 * eGroupWare eTemplate2 - JS Selectbox object
 *
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package etemplate
 * @subpackage api
 * @link http://www.egroupware.org
 * @author Nathan Gray
 * @copyright Nathan Gray 2011
 * @version $Id$
 */

"use strict";

/*egw:uses
	jquery.jquery;
	et2_inputWidget;
*/

/**
 * Class which implements the "menulist" XET-Tag
 */ 
var et2_selectbox = et2_inputWidget.extend({

	attributes: {
		"multiselect": {
			"name": "multiselect",
			"type": "boolean",
			"default": false,
			"description": "Allow selecting multiple options"
		},
		"rows": {
			"name": "Rows",
			"type": "any",	// Old options put either rows or empty_label in first space
			"default": 1,
			"description": "Number of rows to display"
		},
		"empty_label": {
			"name": "Empty label",
			"type": "string",
			"default": "",
			"description": "Textual label for first row, eg: 'All' or 'None'.  ID will be ''"
		}
	},

	legacyOptions: ["rows"],

	init: function(_parent) {
		this._super.apply(this, arguments);

		this.input = null;
		this.id = "";

		this.createInputWidget();
	},

	/**
	 * Override load to be able to handle menupopup tag inside of menulist
	 */
	loadFromXML: function(_node) {
		var menupopupElems = et2_directChildrenByTagName(_node, "menupopup");
		if(menupopupElems.length == 1) {
			this.loadAttributes(menupopupElems[0].attributes);
		} else {
			this._super.apply(this,arguments);
		}

		// Legacy options could have row count or empty label in first slot
		if(typeof this.rows == "string" && isNaN(this.rows)) {
			this.set_empty_label(this.rows);
			this.set_rows(1);
		}
		if(this.rows > 1) this.set_multiselect(true);
	},

	createInputWidget: function() {
		if(this.type == "menupopup") {
			return;
		} else {
			this.input = $j(document.createElement("select"));

			this.input.addClass("et2_selectbox");
		}

		this.setDOMNode(this.input[0]);
	},

	/**
	 * Override parent to get the select options.
	 * Can't get them before this, because the ID is not set when createInputWidget() is called.
	 */
	set_id: function() {
		this._super.apply(this,arguments);

		// Get select options from the manager(s)
		var options = null;

		// Check the sel_options (from managers)
		this.set_select_options(null);
	},

	set_select_options: function(_options) {
		if(_options == null) {
			var mgr = this.getArrayMgr('sel_options');
			if(mgr) {
				options = mgr.getValueForID(this.id);
			}
			if(options == null) {
				// Check in the content
				var mgr = this.getArrayMgr('content');
				if(mgr) {
					options = mgr.getValueForID('options-'+this.id);
				}
			}
		}
		this.input.children().remove();
		if(this.empty_label) {
			this.input.append("<option value=''" + ("" == this.getValue() ? "selected":"") +">"+this.empty_label+"</option>");
		}
		if(_options == null) return;
		for(var key in _options) {
			this.input.append("<option value='"+key+"'" + (key == this.getValue() ? "selected":"") +">"+_options[key]+"</option>");
		}
	},

	set_multiselect: function(_value) {
		if (_value != this.multiselect)
		{
			this.multiselect = _value;
			if(this.multiselect) {
				this.input.attr("multiple","multiple");
			} else {
				this.input.removeAttr("multiple");
			}
		}
	},
	set_rows: function(_rows) {
		if (_rows != this.rows)
		{
			this.rows = _rows;
			this.input.attr("size",this.rows);
		}
	},
	set_empty_label: function(_label) {
		if(_label != this.empty_label) {
			this.empty_label = _label;
			this.set_select_options(null);
		}
	}
});

et2_register_widget(et2_selectbox, ["menulist","listbox"]);

