/**
 * Calendar - static javaScript functions
 *
 * @link http://www.egroupware.org
 * @author Ralf Becker <RalfBecker@stylite.de>
 * @package calendar
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

/**
 * Fix calendar specific id: "cal_id:recurrence" or "appId:", replacing $app and $id in url
 * 
 * Cut away the recurrence date from id, and use app from calendar integration
 * 
 * @param _action
 * @param _senders
 */
function cal_fix_app_id(_action, _senders)
{
	var app = 'calendar';
	var id = _senders[0].id;
	var matches = id.match(/^([0-9]+):([0-9]+)$/);
	if (matches)
	{
		id = matches[1];
	}
	else if (matches = id.match(/^([a-z_-]+)([0-9]+)/i))
	{
		app = matches[1];
		id = matches[2];
	}
	var backup_url = _action.data.url;
	
	_action.data.url = _action.data.url.replace(/(\$|%24)id/,id);
	_action.data.url = _action.data.url.replace(/(\$|%24)app/,app);

	nm_action(_action, _senders);
	
	_action.data.url = backup_url;	// restore url
}

/**
 * Open calendar entry, taking into accout the calendar integration of other apps
 * 
 * calendar_uilist::get_rows sets var js_calendar_integration object
 * 
 * @param _action
 * @param _senders
 */
function cal_open(_action, _senders)
{
	var id = _senders[0].id;
	var matches = id.match(/^(?:calendar::)?([0-9]+):([0-9]+)$/);
	var backup = _action.data;
	if (matches)
	{
		edit_series(matches[1],matches[2]);
		return;
	}
	else if (matches = id.match(/^([a-z_-]+)([0-9]+)/i))
	{
		var app = matches[1];
		_action.data.url = window.egw_webserverUrl+'/index.php?';
		var get_params = js_integration_data[app].edit;
		get_params[js_integration_data[app].edit_id] = matches[2];
		for(var name in get_params)
			_action.data.url += name+"="+encodeURIComponent(get_params[name])+"&";

		if (js_integration_data[app].edit_popup &&
			(matches = js_integration_data[app].edit_popup.match(/^(.*)x(.*)$/)))
		{
			_action.data.width = matches[1];
			_action.data.height = matches[2];
		}
		else
		{
			_action.data.nm_action = 'location';
		}
	}
	console.log(_action);
	nm_action(_action, _senders, null, {ids: []});
	
	_action.data = backup;	// restore url, width, height, nm_action
}

/**
 * Delete calendar entry, asking if you want to delete series or exception
 * 
 * 
 * @param _action
 * @param _senders
 */
function cal_delete(_action, _senders)
{
	var backup = _action.data;
	var matches = false;

	// Loop so we ask if any of the selected entries is part of a series
	for(var i = 0; i < _senders.length; i++)
	{
		var id = _senders[i].id;
		if(!matches)
		{
			matches = id.match(/^(?:calendar::)?([0-9]+):([0-9]+)$/);
		}
	}
	if (matches)
	{
		var id = matches[1];
		var date = matches[2];
		var popup = jQuery(document.getElementById(_action.getManager().etemplate_var_prefix + '[' + _action.id + '_popup]'));
		var row = null;

		// Cancel normal confirm
		delete _action.data.confirm;
		delete _action.data.confirm_multiple;

		// nm action - show popup
		nm_open_popup(_action,_senders);

		if(!popup)
		{
			return;
		}
		if (row = jQuery("#"+id+"\\:"+date)) {
			// Open at row
			popup.css({
				position: "absolute",
				top: row.position().top + row.height() -popup.height()/2,
				left: $j(window).width()/2-popup.width()/2
			});
		} else {
			// Open popup in the middle
			popup.css({
				position: "absolute",
				top: $j(window).height()/2-popup.height()/2,
				left: $j(window).width()/2-popup.width()/2
			});
		}
		return;
	}
	console.log(_action);
	nm_action(_action, _senders, null, {ids: []});
	
	_action.data = backup;	// restore url, width, height, nm_action
}
