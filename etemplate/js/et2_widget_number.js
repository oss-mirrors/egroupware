/**
 * EGroupware eTemplate2 - JS Number object
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
	et2_widget_textbox;
*/

/**
 * Class which implements the "int" and textbox type=float XET-Tags
 *
 * @augments et2_textbox
 */
var et2_number = et2_textbox.extend(
{
	attributes: {
		"value": {
			"type": "float"
		},
		// Override default width, numbers are usually shorter
		"size": {
			"default": 5
		},
		"min": {
			"name": "Minimum",
			"type": "integer",
			"default": et2_no_init,
			"description": "Minimum allowed value"
		},
		"max": {
			"name": "Maximum",
			"type": "integer",
			"default": et2_no_init,
			"description": "Maximum allowed value"
		},
		"precision": {
			// TODO: Implement this in some nice way other than HTML5's step attribute
			"name": "Precision",
			"type": "integer",
			"default": et2_no_init,
			"description": "Allowed precision - # of decimal places",
			"ignore": true
		}
	},

	/**
	 * Constructor
	 *
	 * @memberOf et2_number
	 */
	init: function() {
		this._super.apply(this, arguments);
	},

	createInputWidget: function() {
		this.input = $j(document.createElement("input"));
		this.input.attr("type", "number");
		this.input.addClass("et2_textbox");

		this.setDOMNode(this.input[0]);
	},

	/**
	 * Set input widget size
	 *
	 * Overwritten from et2_textbox as input type=number seems to ignore size,
	 * therefore we set width in em instead, if not et2_fullWidth given.
	 *
	 * @param _size Rather arbitrary size units, approximately characters
	 */
	set_size: function(_size) {
		if (typeof _size != 'undefined' && _size != this.input.attr("size"))
		{
			this.size = _size;
			this.input.attr("size", this.size);

			if (typeof this.options.class == 'undefined' || this.options.class.search('et2_fullWidth') == -1)
			{
				this.input.css('width', _size+'em');
			}
		}
	},

	set_min: function(_value) {
		this.min = _value;
		if(this.min == null) {
			this.input.removeAttr("min");
		} else {
			this.input.attr("min",this.min);
		}
	},
	set_max: function(_value) {
		this.max = _value;
		if(this.max == null) {
			this.input.removeAttr("max");
		} else {
			this.input.attr("max",this.max);
		}
	}
});
et2_register_widget(et2_number, ["int", "integer", "float"]);

/**
 * Extend read-only to tell it to ignore special attributes, which
 * would cause warnings otherwise
 * @augments et2_textbox_ro
 * @class
 */
var et2_number_ro = et2_textbox_ro.extend(
{
	attributes: {
		min: { ignore: true},
		max: { ignore: true},
		precision: { ignore: true},
		value: { type: "float" }
	}
});
et2_register_widget(et2_number_ro, ["int_ro", "integer_ro", "float_ro"]);
