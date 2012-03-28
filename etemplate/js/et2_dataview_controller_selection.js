/**
 * eGroupWare eTemplate2
 *
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package etemplate
 * @subpackage dataview
 * @link http://www.egroupware.org
 * @author Andreas Stöckel
 * @copyright Stylite 2011-2012
 * @version $Id$
 */

/*egw:uses
	et2_dataview_view_aoi;
*/

/**
 * The selectioManager is internally used by the et2_dataview_controller class
 * to manage the row selection.
 */
var et2_dataview_selectionManager = Class.extend({

	init: function (_indexMap, _actionObjectManager, _queryRangeCallback,
			_context) {
		// Copy the arguments
		this._indexMap = _indexMap;
		this._actionObjectManager = _actionObjectManager;
		this._queryRangeCallback = _queryRangeCallback;
		this._context = _context;

		// Internal map which contains all curently selected uids and their
		// state
		this._registeredRows = {};
		this._focusedEntry = null;
		this._invertSelection = false;
		this._inUpdate = false;
	},

	setIndexMap: function (_indexMap) {
		this._indexMap = _indexMap;
	},

	registerRow: function (_uid, _idx, _tr, _links) {

		// Get the corresponding entry from the registered rows array
		var entry = this._getRegisteredRowsEntry(_uid);

		// Create the AOI for the tr
		if (!entry.tr)
		{
			// Create the AOI which is used internally in the selection manager
			// this AOI is not connected to the AO, as the selection manager
			// cares about selection etc.
			entry.aoi = new et2_dataview_rowAOI(_tr);
			entry.aoi.setStateChangeCallback(
				function (_newState, _changedBit, _shiftState) {
					if (_changedBit === EGW_AO_STATE_SELECTED)
					{
						// Call the select handler
						this._handleSelect(
								_uid,
								entry,
								egwBitIsSet(_shiftState, EGW_AO_SHIFT_STATE_BLOCK),
								egwBitIsSet(_shiftState, EGW_AO_SHIFT_STATE_MULTI)
							);
					}
				}, this);

			// Create AOI
			if (_links)
			{
				var dummyAOI = new egwActionObjectInterface();
				var self = this;

				// Handling for Action Implementations updating the state
				dummyAOI.doSetState = function (_state) {
					if (egwBitIsSet(_state, EGW_AO_STATE_FOCUSED) && !self._inUpdate)
					{
						self.resetSelection();
						self._updateState(_uid, _state);
						self.setFocused(_uid, true);
					}
				};

				// Implementation of the getDOMNode function, so that the event
				// handlers can be properly bound
				dummyAOI.getDOMNode = function () {return _tr};

				// Create an action object for the tr and connect it to a dummy AOI
				entry.ao = this._actionObjectManager.addObject(_uid, dummyAOI);
				entry.ao.updateActionLinks(_links);
			}
		}

		// Update the entry
		entry.idx = _idx;
		entry.tr = _tr;

		// Update the visible state of the _tr
		this._updateEntryState(entry, entry.state);
	},

	unregisterRow: function (_uid, _tr) {
		if (typeof this._registeredRows[_uid] !== "undefined"
		    && this._registeredRows[_uid].tr === _tr)
		{
			this._registeredRows[_uid].tr = null;
			this._registeredRows[_uid].aoi = null;

			// Remove the action object from its container
			if (this._registeredRows[_uid].ao)
			{
				this._registeredRows[_uid].ao.remove();
				this._registeredRows[_uid].ao = null;
			}

			if (this._registeredRows[_uid].state === EGW_AO_STATE_NORMAL)
			{
				delete this._registeredRows[_uid];
			}
		}
	},

	resetSelection: function () {
		this._invertSelection = false;

		for (var key in this._registeredRows)
		{
			this.setSelected(key, false);
		}
	},

	setSelected: function (_uid, _selected) {
		var entry = this._getRegisteredRowsEntry(_uid);
		this._updateEntryState(entry,
				egwSetBit(entry.state, EGW_AO_STATE_SELECTED, _selected));
	},

	setFocused: function (_uid, _focused) {
		// Reset the state of the currently focused entry
		if (this._focusedEntry)
		{
			this._updateEntryState(this._focusedEntry,
					egwSetBit(this._focusedEntry.state, EGW_AO_STATE_FOCUSED,
							false));
			this._focusedEntry = null;
		}

		// Mark the new given uid as focused
		if (_focused)
		{
			var entry = this._focusedEntry = this._getRegisteredRowsEntry(_uid);
			this._updateEntryState(entry,
					egwSetBit(entry.state, EGW_AO_STATE_FOCUSED, true));
		}
	},

	selectAll: function () {
		// Reset the selection
		this.resetSelection();

		// Set the "invert selection" flag
		this._invertSelection = true;

		// Update the selection
		for (var key in this._registeredRows)
		{
			var entry = this._registeredRows[key];
			this._updateEntryState(entry, entry.state);
		}
	},


	/** -- PRIVATE FUNCTIONS -- **/


	_updateState: function (_uid, _state) {
		var entry = this._getRegisteredRowsEntry(_uid);

		this._updateEntryState(entry, _state);

		return entry;
	},

	_updateEntryState: function (_entry, _state) {

		// Update the state of the entry
		_entry.state = _state;

		if (this._invertSelection)
		{
			_state ^= EGW_AO_STATE_SELECTED;
		}

		// Update the state if it has changed
		if ((_entry.aoi && _entry.aoi.getState() !== _state) || _entry.state != _state)
		{
			this._inUpdate = true; // Recursion prevention

			// Update the visual state
			if (_entry.aoi)
			{
				_entry.aoi.setState(_state);
			}

			// Update the state of the action object
			if (_entry.ao)
			{
				_entry.ao.setSelected(egwBitIsSet(_state, EGW_AO_STATE_SELECTED));
				_entry.ao.setFocused(egwBitIsSet(_state, EGW_AO_STATE_FOCUSED));
			}

			this._inUpdate = false;

			// Delete the element if state was set to "NORMAL" and there is
			// no tr
			if (_state === EGW_AO_STATE_NORMAL && !_entry.tr)
			{
				delete this._registeredRows[_entry.uid];
			}
		}
	},

	_getRegisteredRowsEntry: function (_uid) {
		if (typeof this._registeredRows[_uid] === "undefined")
		{
			this._registeredRows[_uid] = {
				"uid": _uid,
				"idx": null,
				"state": EGW_AO_STATE_NORMAL,
				"tr": null,
				"aoi": null,
				"ao": null
			};
		}

		return this._registeredRows[_uid];
	},

	_handleSelect: function (_uid, _entry, _shift, _ctrl) {
		// If not "_ctrl" is set, reset the selection
		if (!_ctrl)
		{
			this.resetSelection();
		}

		// Mark the element that was clicked as selected
		var entry = this._getRegisteredRowsEntry(_uid);
		this.setSelected(_uid,
			!_ctrl || !egwBitIsSet(entry.state, EGW_AO_STATE_SELECTED));

		// Focus the element if shift is not pressed
		if (!_shift)
		{
			this.setFocused(_uid, true);
		}
		else if (this._focusedEntry)
		{
			this._selectRange(this._focusedEntry.idx, _entry.idx);
		}
	},

	_selectRange: function (_start, _stop) {
		// Contains ranges that are not currently in the index map and that have
		// to be queried
		var queryRanges = [];

		// Iterate over the given range and select the elements in the range
		// from _start to _stop
		var naStart = false;
		var s = Math.min(_start, _stop);
		var e = Math.max(_stop, _start);
		for (var i = s; i <= e; i++)
		{
			if (typeof this._indexMap[i] !== "undefined" &&
			    this._indexMap[i].uid)
			{
				// Add the range to the "queryRanges"
				if (naStart !== false) {
					queryRanges.push(et2_bounds(naStart, i - 1));
					naStart = false;
				}

				// Select the element
				this.setSelected(this._indexMap[i].uid, true);
			} else if (naStart === false) {
				naStart = i;
			}
		}

		// Add the last range to the "queryRanges"
		if (naStart !== false) {
			queryRanges.push(et2_bounds(naStart, i - 1));
			naStart = false;
		}

		// Query all unknown ranges from the server
		for (var i = 0; i < queryRanges.length; i++)
		{
			this._queryRangeCallback.call(this._context, queryRanges[i], 
				function (_order) {
					for (var j = 0; j < _order.length; j++)
					{
						this.setSelected(_order[j], true);
					}
				}, this);
		}
	}

});

