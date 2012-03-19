/**
 * eGroupWare eTemplate2 - JS Groupbox object
 *
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package etemplate
 * @subpackage api
 * @link http://www.egroupware.org
 * @author Nathan Gray
 * @copyright Nathan Gray 2012
 * @version $Id$
 */

"use strict";

/*egw:uses
	et2_core_baseWidget;
*/

/**
 * Class which implements the hrule tag
 */ 
var et2_groupbox = et2_baseWidget.extend({

	init: function() {
		this._super.apply(this, arguments);

		this.setDOMNode(document.createElement("fieldset"));
	}
});

et2_register_widget(et2_groupbox, ["groupbox"]);

var et2_groupbox_legend = et2_baseWidget.extend({
	attributes: {
		"label": {
                        "name": "Label",
                        "type": "string",
                        "default": "",
                        "description": "Label for group box"
                }
	},

	init: function() {
		this._super.apply(this, arguments);

		var legend = jQuery("<legend>"+this.options.label+"</legend>");
		this.setDOMNode(legend[0]);
	}
});
et2_register_widget(et2_groupbox_legend, ["caption"]);
