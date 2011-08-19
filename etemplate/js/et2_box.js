/**
 * eGroupWare eTemplate2 - JS Box object
 *
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package etemplate
 * @subpackage api
 * @link http://www.egroupware.org
 * @author Andreas Stöckel
 * @copyright Stylite 2011
 * @version $Id$
 */

"use strict";

/*egw:uses
	jquery.jquery;
	et2_baseWidget;
*/

/**
 * Class which implements the hbox and vbox tag
 */ 
var et2_box = et2_baseWidget.extend({

	createNamespace: true,

	init: function() {
		this._super.apply(this, arguments);

		this.div = $j(document.createElement("div"))
			.addClass("et2_" + this._type)
			.addClass("et2_box_widget");

		this.setDOMNode(this.div[0]);
	}

});

et2_register_widget(et2_box, ["vbox", "box"]);

