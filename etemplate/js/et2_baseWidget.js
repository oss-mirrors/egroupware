/**
 * eGroupWare eTemplate2 - JS Widget base class
 *
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package etemplate
 * @subpackage api
 * @link http://www.egroupware.org
 * @author Andreas Stöckel
 * @copyright Stylite 2011
 * @version $Id: et2_widget.js 36021 2011-08-07 13:43:46Z igel457 $
 */

"use strict";

/*egw:uses
	jquery.jquery;
	lib/tooltip.js;
	et2_widget;
*/

/**
 * Class which manages the DOM node itself. The simpleWidget class is derrived
 * from et2_DOMWidget and implements the getDOMNode function. A setDOMNode
 * function is provided, which attatches the given node to the DOM if possible.
 */
var et2_baseWidget = et2_DOMWidget.extend({

	attributes: {
		"statustext": {
			"name": "Tooltip",
			"type": "string",
			"description": "Tooltip which is shown for this element"
		}
	},

	init: function() {
		this._super.apply(this, arguments);

		this.node = null;
		this.statustext = "";

		this._tooltipElem = null;
	},

	detatchFromDOM: function() {
		// Detach this node from the tooltip node
		if (this._tooltipElem)
		{
			egw_global_tooltip.unbindFromElement(this._tooltipElem);
			this._tooltipElem = null;
		}

		this._super.apply(this, arguments);
	},

	attachToDOM: function() {
		this._super.apply(this,arguments);

		// Update the statustext
		this.set_statustext(this.statustext);
	},

	setDOMNode: function(_node) {
		if (_node != this.node)
		{
			// Deatch the old node from the DOM
			this.detatchFromDOM();

			// Set the new DOM-Node
			this.node = _node;

			// Attatch the DOM-Node to the tree
			return this.attachToDOM();
		}

		return false;
	},

	getDOMNode: function() {
		return this.node;
	},

	getTooltipElement: function() {
		return this.getDOMNode();
	},

	set_statustext: function(_value) {
		// Don't execute the code below, if no tooltip will be attached/detached
		if (_value == "" && !this._tooltipElem)
		{
			return;
		}

		this.statustext = _value;

		//Get the domnode the tooltip should be attached to
		var elem = $j(this.getTooltipElement());

		if (elem)
		{
			//If a tooltip is already attached to the element, remove it first
			if (this._tooltipElem)
			{
				egw_global_tooltip.unbindFromElement(this._tooltipElem);
				this._tooltipElem = null;
			}

			if (_value && _value != '')
			{
				egw_global_tooltip.bindToElement(elem, _value);
				this._tooltipElem = elem;
			}
		}
	}

});


