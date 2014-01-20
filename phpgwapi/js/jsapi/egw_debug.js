/**
 * EGroupware clientside API object
 *
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package etemplate
 * @subpackage api
 * @link http://www.egroupware.org
 * @author Andreas Stöckel (as AT stylite.de)
 * @author Ralf Becker <RalfBecker@outdoor-training.de>
 * @version $Id$
 */

"use strict";

/*egw:uses
	egw_core;
*/

egw.extend('debug', egw.MODULE_GLOBAL, function(_app, _wnd) {

	/**
	 * DEBUGLEVEL specifies which messages are printed to the console.
	 * Decrease the value of EGW_DEBUGLEVEL to get less messages.
	 *
	 * @type Number
	 * 0 = off, no logging
	 * 1 = only "error"
	 * 2 = -- " -- plus "warning"
	 * 3 = -- " -- plus "info"
	 * 4 = -- " -- plus "log"
	 * 5 = -- " -- plus a stacktrace
	 */
	var DEBUGLEVEL = 2;

	/**
	 * Log-level for local storage
	 *
	 * @type Number
	 */
	var LOCAL_LOG_LEVEL = 1;
	/**
	 * Number of log-entries stored on client, new errors overwrite old ones
	 *
	 * @type Number
	 */
	var MAX_LOGS = 1000;
	/**
	 * Number of last old log entry = next one to overwrite
	 *
	 * @type String
	 */
	var LASTLOG = 'lastLog';
	/**
	 * Prefix for key of log-message, message number gets appended to it
	 *
	 * @type String
	 */
	var LOG_PREFIX = 'log_';

	/**
	 * Log to clientside html5 localStorage
	 *
	 * @param {String} _level "navigation", "log", "info", "warn", "error"
	 * @param {Array} _args arguments to egw.debug
	 * @returns {Boolean} false if localStorage is NOT supported, null if level requires no logging, true if logged
	 */
	function log_on_client(_level, _args)
	{
		if (!window.localStorage) return false;

		switch(_level)
		{
			case 'warn':
				if (LOCAL_LOG_LEVEL < 2) return null;
			case 'info':
				if (LOCAL_LOG_LEVEL < 3) return null;
			case 'log':
				if (LOCAL_LOG_LEVEL < 4) return null;
			default:
				if (!LOCAL_LOG_LEVEL) return null;
		}
		var data = {
			time: (new Date()).getTime(),
			level: _level,
			args: _args,
		};
		// Add in a trace, if no navigation _level
		if (_level != 'navigation' && typeof (new Error).stack != 'undefined')
		{
			data.stack = (new Error).stack;
		}
		if (typeof window.localStorage[LASTLOG] == 'undefined')
		{
			window.localStorage[LASTLOG] = 0;
		}
		window.localStorage[LOG_PREFIX+window.localStorage[LASTLOG]] = JSON.stringify(data);

		window.localStorage[LASTLOG] = (1 + parseInt(window.localStorage[LASTLOG])) % MAX_LOGS;
	}

	/**
	 * Get log from localStorage with oldest message first
	 *
	 * @returns {Array} of Object with values for attributes level, message, trace
	 */
	function get_client_log()
	{
		var logs = [];

		if (window.localStorage && typeof window.localStorage[LASTLOG] != 'undefined')
		{
			var lastlog = parseInt(window.localStorage[LASTLOG]);
			for(var i=lastlog; i < MAX_LOGS && typeof window.localStorage[LOG_PREFIX+i] != 'undefined'; ++i)
			{
				logs.push(JSON.parse(window.localStorage[LOG_PREFIX+i]));
			}
			for (var i=0; i < lastlog; ++i)
			{
				logs.push(JSON.parse(window.localStorage[LOG_PREFIX+i]));
			}
		}
		return logs;
	}

	/**
	 * Clears whole client log
	 */
	function clear_client_log()
	{
		if (!window.localStorage) return false;

		for(var i=0; i < MAX_LOGS; ++i)
		{
			if (typeof window.localStorage[LOG_PREFIX+i] != 'undefined')
			{
				delete window.localStorage[LOG_PREFIX+i];
			}
		}
		delete window.localStorage[LASTLOG];

		return true;
	}

	/**
	 * Show user an error happend by displaying a clickable icon with tooltip of current error
	 */
	function raise_error()
	{
		var icon = jQuery('#topmenu_info_error');
		if (!icon.length)
		{
			var icon = jQuery(egw(_wnd).image_element(egw.image('dialog_error')));
			icon.addClass('topmenu_info_item').attr('id', 'topmenu_info_error');
			// ToDo: tooltip
			icon.on('click', egw(_wnd).show_log);
			jQuery('#egw_fw_topmenu_info_items').append(icon);
		}
	}

	// bind to global error handler
	jQuery(_wnd).on('error', function(e)
	{
		log_on_client('error', [e.originalEvent.message]);
		raise_error();
		// rethrow error to let browser log and show it in usual way too
		throw e;
	});

	/* show error icon / show_log trigger */
	window.setTimeout(function(){
		raise_error();
	}, 10000);


	/**
	 * The debug function can be used to send a debug message to the
	 * java script console. The first parameter specifies the debug
	 * level, all other parameters are passed to the corresponding
	 * console function.
	 */
	return {
		debug: function(_level) {
			if (typeof _wnd.console != "undefined")
			{
				// Get the passed parameters and remove the first entry
				var args = [];
				for (var i = 1; i < arguments.length; i++)
				{
					args.push(arguments[i]);
				}

				// Add in a trace
				if (DEBUGLEVEL >= 5 && typeof (new Error).stack != "undefined")
				{
					var stack = (new Error).stack;
					args.push(stack);
				}

				if (_level == "log" && DEBUGLEVEL >= 4 &&
					typeof _wnd.console.log == "function")
				{
					_wnd.console.log.apply(_wnd.console, args);
				}

				if (_level == "info" && DEBUGLEVEL >= 3 &&
					typeof _wnd.console.info == "function")
				{
					_wnd.console.info.apply(_wnd.console, args);
				}

				if (_level == "warn" && DEBUGLEVEL >= 2 &&
					typeof _wnd.console.warn == "function")
				{
					_wnd.console.warn.apply(_wnd.console, args);
				}

				if (_level == "error" && DEBUGLEVEL >= 1 &&
					typeof _wnd.console.error == "function")
				{
					_wnd.console.error.apply(_wnd.console, args);
				}
			}
			// raise errors to user
			if (_level == "error") raise_error(args);

			// log to html5 localStorage
			if (typeof stack != 'undefined') args.pop();	// remove stacktrace again
			log_on_client(_level, args);

		},

		/**
		 * Display log to user because he clicked on icon showed by raise_error
		 *
		 * @returns {undefined}
		 */
		show_log: function()
		{
			alert('show_log clicked :-)');
			if (_wnd.console) _wnd.console.log(get_client_log());
		}
	};
});

