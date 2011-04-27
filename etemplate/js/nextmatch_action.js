/**
 * EGroupware eTemplate nextmatch row action object interface
 *
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package etemplate
 * @subpackage api
 * @link http://www.egroupware.org
 * @author Andreas Stöckel (as AT stylite.de)
 * @author Ralf Becker <RalfBecker@outdoor-training.de>
 * @version $Id$
 */

/**
 * Contains the action object interface implementation for the nextmatch widget
 * row.
 */

/**
 * An action object interface for each nextmatch widget row - "inherits" from 
 * egwActionObjectInterface
 */
function nextmatchRowAOI(_node)
{
	var aoi = new egwActionObjectInterface();

	aoi.node = _node;

	aoi.checkBox = ($(":checkbox", aoi.node))[0];

	// Rows without a checkbox OR an id set are unselectable
	if (typeof aoi.checkBox != "undefined" || _node.id)
	{
		aoi.doGetDOMNode = function() {
			return aoi.node;
		}

		// Prevent the browser from selecting the content of the element, when
		// a special key is pressed.
		$(_node).mousedown(egwPreventSelect);

		// Now append some action code to the node
		$(_node).click(function(e) {

			// Reset the prevent selection code (in order to allow wanted
			// selection of text)
			_node.onselectstart = null;

			if (e.target != aoi.checkBox)
			{
				var selected = egwBitIsSet(aoi.getState(), EGW_AO_STATE_SELECTED);
				var state = egwGetShiftState(e);

				aoi.updateState(EGW_AO_STATE_SELECTED,
					!egwBitIsSet(state, EGW_AO_SHIFT_STATE_MULTI) || !selected,
					state);
			}
		});

		$(aoi.checkBox).change(function() {
			aoi.updateState(EGW_AO_STATE_SELECTED, this.checked, EGW_AO_SHIFT_STATE_MULTI);
		});

		// Don't execute the default action when double clicking on an entry
		$(aoi.checkBox).dblclick(function() {
			return false;
		});

		aoi.doSetState = function(_state) {
			var selected = egwBitIsSet(_state, EGW_AO_STATE_SELECTED);

			if (this.checkBox)
			{
				this.checkBox.checked = selected;
			}

			$(this.node).toggleClass('focused',
				egwBitIsSet(_state, EGW_AO_STATE_FOCUSED));
			$(this.node).toggleClass('selected',
				selected);
		}
	}

	return aoi;
}

/**
 * Default action for nextmatch rows, runs action specified _action.data.nm_action: see nextmatch_widget::egw_actions()
 * 
 * @param _action action object with attributes caption, id, nm_action, ...
 * @param _senders array of rows selected
 */
function nm_action(_action, _senders)
{
	// ignore checkboxes, unless they have an explicit defined nm_action
	if (_action.checkbox && (!_action.data || typeof _action.data.nm_action == 'undefined')) return;

	if (typeof _action.data == 'undefined' || !_action.data) _action.data = {};
	if (typeof _action.data.nm_action == 'undefined') _action.data.nm_action = 'submit';
	
	var ids = "";
	for (var i = 0; i < _senders.length; i++)
	{
		ids += (_senders[i].id.indexOf(',') >= 0 ? '"'+_senders[i].id.replace(/"/g,'""')+'"' : _senders[i].id) + 
			((i < _senders.length - 1) ? "," : "");
	}
	//console.log(_action); console.log(_senders);

	var select_all = egw_actionManager.getActionById("select_all");
	var confirm_msg = (_senders.length > 1 || select_all && select_all.checked) && 
		typeof _action.data.confirm_multiple != 'undefined' ?
			_action.data.confirm_multiple : _action.data.confirm;

	// let user confirm the action first (if not select_all set and nm_action == 'submit'  --> confirmed later)
	if (!(select_all && select_all.checked && _action.data.nm_action == 'submit') &&
		typeof _action.data.confirm != 'undefined')
	{
		if (!confirm(confirm_msg)) return;
	}
	
	var url = '#';
	if (typeof _action.data.url != 'undefined')
	{
		url = _action.data.url.replace(/(\$|%24)id/,ids);
	}

	var target = null;
	if (typeof _action.data.target != 'undefined')
	{
		target = _action.data.target;
	}
	
	switch(_action.data.nm_action)
	{
		case 'alert':
			alert(_action.caption + " (\'" + _action.id + "\') executed on rows: " + ids);
			break;
			
		case 'location':
			window.location.href = url;
			break;
			
		case 'popup':
			egw_openWindowCentered2(url,target,_action.data.width,_action.data.height);
			break;
			
		case 'submit':
			// let user confirm select-all
			if (select_all && select_all.checked)
			{
				if (!confirm(confirm_msg+"\n\n"+select_all.hint)) return;
			}
			var checkboxes = egw_actionManager.getActionsByAttr("checkbox", true);
			var checkboxes_elem = document.getElementById('exec[nm][checkboxes]');
			if (checkboxes && checkboxes_elem)
				for (var i in checkboxes)
					checkboxes_elem.value += checkboxes[i].id + ":" + (checkboxes[i].checked ? "1" : "0") + ";";

			var form = document.getElementsByName("eTemplate")[0];
			document.getElementById('exec[nm][action]').value = _action.id;
			document.getElementById('exec[nm][selected]').value = ids;
			if (typeof _action.data.button != 'undefined')
			{
				submitit(form.context, 'exec[nm][rows]['+_action.data.button+']['+ids+']');
			}
			else
			{
				form.submit();
			}
			break;
	}
}

/**
 * Callback to check if none of _senders rows has disableClass set
 * 
 * @param _action egwAction object, we use _action.data.disableClass to check
 * @param _senders array of egwActionObject objects
 * @param _target egwActionObject object, get's called for every object in _senders
 * @returns boolean true if none has disableClass, false otherwise
 */
function nm_not_disableClass(_action, _senders, _target)
{
	return !$(_target.iface.getDOMNode()).hasClass(_action.data.disableClass);
}

/**
 * Callback to check if all of _senders rows have enableClass set
 * 
 * @param _action egwAction object, we use _action.data.enableClass to check
 * @param _senders array of egwActionObject objects
 * @param _target egwActionObject object, get's called for every object in _senders
 * @returns boolean true if none has disableClass, false otherwise
 */
function nm_enableClass(_action, _senders, _target)
{
	return $(_target.iface.getDOMNode()).hasClass(_action.data.enableClass);
}

/**
 * Callback to check if a certain field (_action.data.fieldId) is (not) equal to given value (_action.data.fieldValue)
 * 
 * If field is not found, we return false too!
 * 
 * @param _action egwAction object, we use _action.data.fieldId to check agains _action.data.fieldValue
 * @param _senders array of egwActionObject objects
 * @param _target egwActionObject object, get's called for every object in _senders
 * @returns boolean true if field found and has specified value, false otherwise
 */
function nm_compare_field(_action, _senders, _target)
{
	var field = document.getElementById(_action.data.fieldId);

	if (!field) return false;

	var value = $(field).val();
	
	if (_action.data.fieldValue.substr(0,1) == '!')
		return value != _action.data.fieldValue.substr(1);
	
	return value == _action.data.fieldValue;
}
