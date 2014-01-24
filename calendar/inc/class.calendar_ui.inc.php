<?php
/**
 * eGroupWare - Calendar's shared base-class of all UI classes
 *
 * @link http://www.egroupware.org
 * @package calendar
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @copyright (c) 2004-9 by RalfBecker-At-outdoor-training.de
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

/**
 * Shared base-class of all calendar UserInterface classes
 *
 * It manages eg. the state of the controls in the UI and generated the calendar navigation (sidebox-menu)
 *
 * The new UI, BO and SO classes have a strikt definition, in which time-zone they operate:
 *  UI only operates in user-time, so there have to be no conversation at all !!!
 *  BO's functions take and return user-time only (!), they convert internaly everything to servertime, because
 *  SO operates only on server-time
 *
 * All permanent debug messages of the calendar-code should done via the debug-message method of the bocal class !!!
 */
class calendar_ui
{
	/**
	 * @var $debug mixed integer level or string function-name
	 */
	var $debug=false;
	/**
	 * instance of the bocal or bocalupdate class
	 *
	 * @var calendar_boupdate
	 */
	var $bo;
	/**
	 * instance of jscalendar
	 *
	 * @var jscalendar
	 */
	var $jscal;
	/**
	 * Reference to global datetime class
	 *
	 * @var egw_datetime
	 */
	var $datetime;
	/**
	 * Instance of categories class
	 *
	 * @var categories
	 */
	var $categories;
	/**
	 * Reference to global uiaccountsel class
	 *
	 * @var uiaccountsel
	 */
	var $accountsel;
	/**
	 * @var array $common_prefs reference to $GLOBALS['egw_info']['user']['preferences']['common']
	 */
	var $common_prefs;
	/**
	 * @var array $cal_prefs reference to $GLOBALS['egw_info']['user']['preferences']['calendar']
	 */
	var $cal_prefs;
	/**
	 * @var int $wd_start user pref. workday start
	 */
	var $wd_start;
	/**
	 * @var int $wd_start user pref. workday end
	 */
	var $wd_end;
	/**
	 * @var int $interval_m user pref. interval
	 */
	var $interval_m;
	/**
	 * @var int $user account_id of loged in user
	 */
	var $user;
	/**
	 * @var string $date session-state: date (Ymd) of shown view
	 */
	var $date;
	/**
	 * @var int $cat_it session-state: selected category
	 */
	var $cat_id;
	/**
	 * @var int $filter session-state: selected filter, at the moment all or hideprivate
	 */
	var $filter;
	/**
	 * @var int/array $owner session-state: selected owner(s) of shown calendar(s)
	 */
	var $owner;
	/**
	 * @var string $sortby session-state: filter of planner: 'category' or 'user'
	 */
	var $sortby;
	/**
	 * @var string $view session-state: selected view
	 */
	var $view;
	/**
	 * @var string $view menuaction of the selected view
	 */
	var $view_menuaction;

	/**
	 * @var int $first first day of the shown view
	 */
	var $first;
	/**
	 * @var int $last last day of the shown view
	 */
	var $last;

	/**
	 * @var array $states_to_save all states that will be saved to the user prefs
	 */
	var $states_to_save = array('owner','filter','cat_id','view','sortby','planner_days');

	/**
	 * Constructor
	 *
	 * @param boolean $use_boupdate use bocalupdate as parenent instead of bocal
	 * @param array $set_states=null to manualy set / change one of the states, default NULL = use $_REQUEST
	 */
	function __construct($use_boupdate=false,$set_states=NULL)
	{
		if ($use_boupdate)
		{
			$this->bo = new calendar_boupdate();
		}
		else
		{
			$this->bo = new calendar_bo();
		}
		$this->jscal = $GLOBALS['egw']->jscalendar;
		$this->datetime = $GLOBALS['egw']->datetime;
		$this->accountsel = $GLOBALS['egw']->uiaccountsel;

		$this->categories = new categories($this->user,'calendar');

		$this->common_prefs	= &$GLOBALS['egw_info']['user']['preferences']['common'];
		$this->cal_prefs	= &$GLOBALS['egw_info']['user']['preferences']['calendar'];
		$this->bo->check_set_default_prefs();

		$this->wd_start		= 60*$this->cal_prefs['workdaystarts'];
		$this->wd_end		= 60*$this->cal_prefs['workdayends'];
		$this->interval_m	= $this->cal_prefs['interval'];

		$this->user = $GLOBALS['egw_info']['user']['account_id'];

		$this->manage_states($set_states);

		$GLOBALS['uical'] = &$this;	// make us available for ExecMethod, else it creates a new instance

		// calendar does not work with hidden sidebox atm.
		unset($GLOBALS['egw_info']['user']['preferences']['common']['auto_hide_sidebox']);

		// make sure the hook for export_limit is registered
		if (!$GLOBALS['egw']->hooks->hook_exists('export_limit','calendar')) $GLOBALS['egw']->hooks->register_single_app_hook('calendar','export_limit');
	}

	/**
	 * Checks and terminates (or returns for home) with a message if $this->owner include a user/resource we have no read-access to
	 *
	 * If currentapp == 'home' we return the error instead of terminating with it !!!
	 *
	 * @return boolean/string false if there's no error or string with error-message
	 */
	function check_owners_access()
	{
		$no_access = $no_access_group = array();
		foreach(explode(',',$this->owner) as $owner)
		{
			$owner = trim($owner);
			if (is_numeric($owner) && $GLOBALS['egw']->accounts->get_type($owner) == 'g')
			{
				foreach($GLOBALS['egw']->accounts->member($owner) as $member)
				{
					$member = $member['account_id'];
					if (!$this->bo->check_perms(EGW_ACL_READ|EGW_ACL_READ_FOR_PARTICIPANTS|EGW_ACL_FREEBUSY,0,$member))
					{
						$no_access_group[$member] = $this->bo->participant_name($member);
					}
				}
			}
			elseif (!$this->bo->check_perms(EGW_ACL_READ|EGW_ACL_READ_FOR_PARTICIPANTS|EGW_ACL_FREEBUSY,0,$owner))
			{
				$no_access[$owner] = $this->bo->participant_name($owner);
			}
		}
		if (count($no_access))
		{
			$msg = '<p class="message" align="center">'.lang('Access denied to the calendar of %1 !!!',implode(', ',$no_access))."</p>\n";

			if ($GLOBALS['egw_info']['flags']['currentapp'] == 'home')
			{
				return $msg;
			}
			common::egw_header();
			if ($GLOBALS['egw_info']['flags']['nonavbar']) parse_navbar();

			echo $msg;

			common::egw_footer();
			common::egw_exit();
		}
		if (count($no_access_group))
		{
			$this->bo->warnings['groupmembers'] = lang('Groupmember(s) %1 not included, because you have no access.',implode(', ',$no_access_group));
		}
		return false;
	}

	/**
	 * show the egw-framework plus evtl. $this->group_warning from check_owner_access
	 */
	function do_header()
	{
		// Include the jQuery-UI CSS - many more complex widgets use it
		$theme = 'redmond';
		egw_framework::includeCSS("/phpgwapi/js/jquery/jquery-ui/$theme/jquery-ui-1.10.3.custom.css");
		// Load our CSS after jQuery-UI, so we can override it
		egw_framework::includeCSS('/etemplate/templates/default/etemplate2.css');

		// load etemplate2
		egw_framework::validate_file('/etemplate/js/etemplate2.js');

		// load our app.js file
		egw_framework::validate_file('/calendar/js/app.js');

		common::egw_header();

		if ($this->bo->warnings) echo '<p class="message" align="center">'.implode('<br />',$this->bo->warnings)."</p>\n";
	}

	/**
	 * Manages the states of certain controls in the UI: date shown, category selected, ...
	 *
	 * The state of all these controls is updated if they are set in $_REQUEST or $set_states and saved in the session.
	 * The following states are used:
	 *	- date or year, month, day: the actual date of the period displayed
	 *	- cat_id: the selected category
	 *	- owner: the owner of the displayed calendar
	 *	- save_owner: the overriden owner of the planner
	 *	- filter: the used filter: all or hideprivate
	 *	- sortby: category or user of planner
	 *	- view: the actual view, where dialogs should return to or which they refresh
	 * @param array $set_states array to manualy set / change one of the states, default NULL = use $_REQUEST
	 */
	function manage_states($set_states=NULL)
	{
		$states = $states_session = $GLOBALS['egw']->session->appsession('session_data','calendar');

		// retrieve saved states from prefs
		if(!$states)
		{
			$states = unserialize($this->bo->cal_prefs['saved_states']);
		}
		// only look at _REQUEST, if we are in the calendar (prefs and admin show our sidebox menu too!)
		if (is_null($set_states))
		{
			// ajax-exec call has get-parameter in some json
			if (isset($_REQUEST['json_data']) && ($json_data = json_decode($_REQUEST['json_data'], true)) &&
				!empty($json_data['request']['parameters'][0]))
			{
				parse_str(substr($json_data['request']['parameters'][0], 10), $set_states);	// cut off "/index.php?"
			}
			else
			{
				$set_states = substr($_GET['menuaction'],0,9) == 'calendar.' ? $_REQUEST : array();
			}
		}
		if (!$states['date'] && $states['year'] && $states['month'] && $states['day'])
		{
			$states['date'] = $this->bo->date2string($states);
		}

		foreach(array(
			'date'       => $this->bo->date2string($this->bo->now_su),
			'cat_id'     => 0,
			'filter'     => 'default',
			'owner'      => $this->user,
			'save_owner' => 0,
			'sortby'     => 'category',
			'planner_days'=> 0,	// full month
			'view'       => ($this->bo->cal_prefs['defaultcalendar']?$this->bo->cal_prefs['defaultcalendar']:'day'), // use pref, if exists else use the dayview
			'listview_days'=> '',	// no range
		) as $state => $default)
		{
			if (isset($set_states[$state]))
			{
				if ($state == 'owner')
				{
					// only change the owners of the same resource-type as given in set_state[owner]
					$set_owners = explode(',',$set_states['owner']);
					if ((string)$set_owners[0] === '0')	// set exactly the specified owners (without the 0)
					{
						if ($set_states['owner'] === '0,r0')	// small fix for resources
						{
							$set_states['owner'] = $default;	// --> set default, instead of none
						}
						else
						{
							$set_states['owner'] = substr($set_states['owner'],2);
						}
					}
					else	// change only the owners of the given type
					{
						$res_type = is_numeric($set_owners[0]) ? false : $set_owners[0][0];
						$owners = explode(',',$states['owner'] ? $states['owner'] : $default);
						foreach($owners as $key => $owner)
						{
							if (!$res_type && is_numeric($owner) || $res_type && $owner[0] == $res_type)
							{
								unset($owners[$key]);
							}
						}
						if (!$res_type || !in_array($res_type.'0',$set_owners))
						{
							$owners = array_merge($owners,$set_owners);
						}
						$set_states['owner'] = implode(',',$owners);
					}
				}
				// for the uiforms class (eg. edit), dont store the (new) owner, as it might change the view
				if (substr($_GET['menuaction'],0,25) == 'calendar.calendar_uiforms')
				{
					$this->owner = $set_states[$state];
					continue;
				}
				$states[$state] = $set_states[$state];
			}
			elseif (!is_array($states) || !isset($states[$state]))
			{
				$states[$state] = $default;
			}
			if ($state == 'date')
			{
				$date_arr = $this->bo->date2array($states['date']);
				foreach(array('year','month','day') as $name)
				{
					$this->$name = $states[$name] = $date_arr[$name];
				}
			}
			$this->$state = $states[$state];
		}
		// remove a given calendar from the view
		if (isset($_GET['close']) && ($k = array_search($_GET['close'], $owners=explode(',',$this->owner))) !== false)
		{
			unset($owners[$k]);
			$this->owner = $states['owner'] = implode(',',$owners);
		}

		if (substr($this->view,0,8) == 'planner_')
		{
			$states['sortby'] = $this->sortby = $this->view == 'planner_cat' ? 'category' : 'user';
			$states['view'] = $this->view = 'planner';
		}
		// set the actual view as return_to
		if (isset($_GET['menuaction']))
		{
			list($app,$class,$func) = explode('.',$_GET['menuaction']);
			if ($func == 'index')
			{
				$func = $this->view; $this->view = 'index';	// switch to the default view
			}
		}
		else	// eg. calendar/index.php
		{
			$func = $this->view;
			$class = $this->view == 'listview' ? 'calendar_uilist' : 'calendar_uiviews';
		}
		if ($class == 'calendar_uiviews' || $class == 'calendar_uilist')
		{
			// if planner_start_with_group is set in the users prefs: switch owner for planner to planner_start_with_group and back
			if ($this->cal_prefs['planner_start_with_group'])
			{
				if ($this->cal_prefs['planner_start_with_group'] > 0) $this->cal_prefs['planner_start_with_group'] *= -1;	// fix old 1.0 pref

				if (!$states_session && !$_GET['menuaction']) $this->view = '';		// first call to calendar

				if ($func == 'planner' && $this->view != 'planner' && $this->owner == $this->user)
				{
					//echo "<p>switched for planner to {$this->cal_prefs['planner_start_with_group']}, view was $this->view, func=$func, owner was $this->owner</p>\n";
					$states['save_owner'] = $this->save_owner = $this->owner;
					$states['owner'] = $this->owner = $this->cal_prefs['planner_start_with_group'];
				}
				elseif ($func != 'planner' && $this->view == 'planner' && $this->owner == $this->cal_prefs['planner_start_with_group'] && $this->save_owner)
				{
					//echo "<p>switched back to $this->save_owner, view was $this->view, func=$func, owner was $this->owner</p>\n";
					$states['owner'] = $this->owner = $this->save_owner;
					$states['save_owner'] = $this->save_owner = 0;
				}
			}
			$this->view = $states['view'] = $func;
		}
		$this->view_menuaction = $this->view == 'listview' ? 'calendar.calendar_uilist.listview' : 'calendar.calendar_uiviews.'.$this->view;

		if ($this->debug > 0 || $this->debug == 'manage_states') $this->bo->debug_message('uical::manage_states(%1) session was %2, states now %3',True,$set_states,$states_session,$states);
		// save the states in the session only when we are in calendar
		if ($GLOBALS['egw_info']['flags']['currentapp']=='calendar')
		{
			$GLOBALS['egw']->session->appsession('session_data','calendar',$states);
			// save defined states into the user-prefs
			if(!empty($states) && is_array($states))
			{
				$saved_states = serialize(array_intersect_key($states,array_flip($this->states_to_save)));
				if ($saved_states != $this->cal_prefs['saved_states'])
				{
					$GLOBALS['egw']->preferences->add('calendar','saved_states',$saved_states);
					$GLOBALS['egw']->preferences->save_repository(false,'user',true);
				}
				// store state in request for clientside favorites to use
				// remove date and other states never stored in a favorite
				$states = array_diff_key($states,array('date'=>false,'year'=>false,'month'=>false,'day'=>false,'save_owner'=>false));
				if (strpos($_GET['menuaction'], 'ajax_sidebox') !== false)
				{
					// sidebox request is from top frame, which has app.calendar NOT loaded by time response arrives
				}
				elseif (egw_json_request::isJSONRequest())
				{
					$response = egw_json_response::get();
					$response->apply('app.calendar.set_state', array($states, $_GET['menuaction']));
				}
				else
				{
					egw_framework::set_extra('calendar', 'state', $states);
				}
			}
		}
	}

	/**
	* gets the icons displayed for a given event
	*
	* @param array $event
	* @return array of 'img' / 'title' pairs
	*/
	function event_icons($event)
	{
		$is_private = !$event['public'] && !$this->bo->check_perms(EGW_ACL_READ,$event);
		$viewable = !$this->bo->printer_friendly && $this->bo->check_perms(EGW_ACL_READ,$event);

		$icons = array();
		if (!$is_private)
		{
			if($event['priority'] == 3)
			{
				$icons[] = html::image('calendar','high',lang('high priority'));
			}
			if($event['recur_type'] != MCAL_RECUR_NONE)
			{
				$icons[] = html::image('calendar','recur',lang('recurring event'));
			}
			// icons for single user, multiple users or group(s) and resources
			foreach($event['participants'] as  $uid => $status)
			{
				if(is_numeric($uid) || !isset($this->bo->resources[$uid[0]]['icon']))
				{
					if (isset($icons['single']) || $GLOBALS['egw']->accounts->get_type($uid) == 'g')
					{
						unset($icons['single']);
						$icons['multiple'] = html::image('calendar','users');
					}
					elseif (!isset($icons['multiple']))
					{
						$icons['single'] = html::image('calendar','single');
					}
				}
				elseif(!isset($icons[$uid[0]]) && isset($this->bo->resources[$uid[0]]) && isset($this->bo->resources[$uid[0]]['icon']))
				{
				 	$icons[$uid[0]] = html::image($this->bo->resources[$uid[0]]['app'],
				 		($this->bo->resources[$uid[0]]['icon'] ? $this->bo->resources[$uid[0]]['icon'] : 'navbar'),
				 		lang($this->bo->resources[$uid[0]]['app']),
				 		'width="16px" height="16px"');
				}
			}
		}
		if($event['non_blocking'])
		{
			$icons[] = html::image('calendar','nonblocking',lang('non blocking'));
		}
		if($event['public'] == 0)
		{
			$icons[] = html::image('calendar','private',lang('private'));
		}
		if(isset($event['alarm']) && count($event['alarm']) >= 1 && !$is_private)
		{
			$icons[] = html::image('calendar','alarm',lang('alarm'));
		}
		if($event['participants'][$this->user][0] == 'U')
		{
			$icons[] = html::image('calendar','cnr-pending',lang('Needs action'));
		}
		return $icons;
	}

	/**
	* Create a select-box item in the sidebox-menu
	* @privat used only by sidebox_menu !
	*/
	function _select_box($title,$name,$options,$baseurl='')
	{
		$select = ' <select style="width: 99%;" name="'.$name.'" id="calendar_'.$name.'" title="'.
			lang('Select a %1',lang($title)).'">'.
			$options."</select>\n";

		return array(
			'text' => $select,
			'no_lang' => True,
			'link' => False,
			'icon' => false,
		);
	}

	/**
	 * Generate a link to add an event, incl. the necessary popup
	 *
	 * @param string $content content of the link
	 * @param string $date=null which date should be used as start- and end-date, default null=$this->date
	 * @param int $hour=null which hour should be used for the start, default null=$this->hour
	 * @param int $minute=0 start-minute
	 * @param array $extra_vars=null
	 * @return string the link incl. content
	 */
	function add_link($content,$date=null,$hour=null,$minute=0,array $vars=null)
	{
		$vars['menuaction'] = 'calendar.calendar_uiforms.edit';
		$vars['date'] =  $date ? $date : $this->date;

		if (!is_null($hour))
		{
			$vars['hour'] = $hour;
			$vars['minute'] = $minute;
		}
		return html::a_href($content,'/index.php',$vars,' target="_blank" title="'.html::htmlspecialchars(lang('Add')).
			'" onclick="'.$this->popup('this.href','this.target').'; return false;"');
	}

	/**
	 * returns javascript to open a popup window: window.open(...)
	 *
	 * @param string $link link or this.href
	 * @param string $target='_blank' name of target or this.target
	 * @param int $width=750 width of the window
	 * @param int $height=400 height of the window
	 * @return string javascript (using single quotes)
	 */
	function popup($link,$target='_blank',$width=750,$height=410)
 	{
		return 'egw_openWindowCentered2('.($link == 'this.href' ? $link : "'".$link."'").','.
			($target == 'this.target' ? $target : "'".$target."'").",$width,$height,'yes')";
 	}

	/**
	 * creates the content for the sidebox-menu, called as hook
	 */
	function sidebox_menu()
	{
		$link_vars = array();
		// Magic etemplate2 favorites menu (from nextmatch widget)
		display_sidebox('calendar',lang('Favorites'),array(
			array(
				'no_lang' => true,
				'text'=> egw_framework::favorite_list('calendar',false),
				'link'=>false,
				'icon' => false
			)
		));

		$n = 0;	// index for file-array

		$planner_days_for_view = false;
		switch($this->view)
		{
			case 'month': $planner_days_for_view = 0; break;
			case 'week':  $planner_days_for_view = $this->cal_prefs['days_in_weekview'] == 5 ? 5 : 7; break;
			case 'day':   $planner_days_for_view = 1; break;
		}
		// Toolbar with the views
		$views = '<table style="width: 100%;"><tr>'."\n";
		foreach(array(
			'add' => array('icon'=>'new','text'=>'add'),
			'day' => array('icon'=>'today','text'=>'Today','menuaction' => 'calendar.calendar_uiviews.day','date' => $this->bo->date2string($this->bo->now_su)),
			'week' => array('icon'=>'week','text'=>'Weekview','menuaction' => 'calendar.calendar_uiviews.week'),
			'weekN' => array('icon'=>'multiweek','text'=>'Multiple week view','menuaction' => 'calendar.calendar_uiviews.weekN'),
			'month' => array('icon'=>'month','text'=>'Monthview','menuaction' => 'calendar.calendar_uiviews.month'),
			//'year' => array('icon'=>'year','text'=>'yearview','menuaction' => 'calendar.calendar_uiviews.year'),
			'planner' => array('icon'=>'planner','text'=>'Group planner','menuaction' => 'calendar.calendar_uiviews.planner','sortby' => $this->sortby),
			'list' => array('icon'=>'list','text'=>'Listview','menuaction'=>'calendar.calendar_uilist.listview','ajax'=>'true'),
		) as $view => $data)
		{
			$icon = array_shift($data);
			$title = array_shift($data);
			$vars = array_merge($link_vars,$data);

			$icon = html::image('calendar',$icon,lang($title),"class=sideboxstar");  //to avoid jscadender from not displaying with pngfix
			if ($view == 'add')
			{
				$link = html::a_href($icon,'javascript:'.$this->popup(egw::link('/index.php',array(
					'menuaction' => 'calendar.calendar_uiforms.edit',
				),false)));
			}
			else
			{
				$link = html::a_href($icon,'/index.php',$vars);
			}
			$views .= '<td align="center">'.$link."</td>\n";
		}
		$views .= "</tr></table>\n";

		// hack to disable invite ACL column, if not enabled in config
		if ($_GET['menuaction'] == 'preferences.uiaclprefs.index' &&
			(!$this->bo->require_acl_invite || $this->bo->require_acl_invite == 'groups' && !($_REQUEST['owner'] < 0)))
		{
			$views .= "<style type='text/css'>\n\t.aclInviteColumn { display: none; }\n</style>\n";
		}

		$file[++$n] = array('text' => $views,'no_lang' => True,'link' => False,'icon' => False);

		// special views and view-options menu
		$options = '';
		foreach(array(
			array(
				'text' => lang('dayview'),
				'value' => 'menuaction=calendar.calendar_uiviews.day',
				'selected' => $this->view == 'day',
			),
			array(
				'text' => lang('four days view'),
				'value' => 'menuaction=calendar.calendar_uiviews.day4',
				'selected' => $this->view == 'day4',
			),
			array(
				'text' => lang('weekview with weekend'),
				'value' => 'menuaction=calendar.calendar_uiviews.week&days=7',
				'selected' => $this->view == 'week' && $this->cal_prefs['days_in_weekview'] != 5,
			),
			array(
				'text' => lang('weekview without weekend'),
				'value' => 'menuaction=calendar.calendar_uiviews.week&days=5',
				'selected' => $this->view == 'week' && $this->cal_prefs['days_in_weekview'] == 5,
			),
			array(
				'text' => lang('Multiple week view'),
				'value' => 'menuaction=calendar.calendar_uiviews.weekN',
				'selected' => $this->view == 'weekN',
			),
			array(
				'text' => lang('monthview'),
				'value' => 'menuaction=calendar.calendar_uiviews.month',
				'selected' => $this->view == 'month',
			),
			array(
				'text' => lang('yearview'),
				'value' => 'menuaction=calendar.calendar_uiviews.year',
				'selected' => $this->view == 'year',
			),
			array(
				'text' => lang('planner by category'),
				'value' => 'menuaction=calendar.calendar_uiviews.planner&sortby=category'.
					($planner_days_for_view !== false ? '&planner_days='.$planner_days_for_view : ''),
				'selected' => $this->view == 'planner' && $this->sortby != 'user',
			),
			array(
				'text' => lang('planner by user'),
				'value' => 'menuaction=calendar.calendar_uiviews.planner&sortby=user'.
					($planner_days_for_view !== false ? '&planner_days='.$planner_days_for_view : ''),
				'selected' => $this->view == 'planner' && $this->sortby == 'user',
			),
			array(
				'text' => lang('yearly planner'),
				'value' => 'menuaction=calendar.calendar_uiviews.planner&sortby=month',
				'selected' => $this->view == 'planner' && $this->sortby == 'month',
			),
			array(
				'text' => lang('listview'),
				'value' => 'menuaction=calendar.calendar_uilist.listview&ajax=true',
				'selected' => $this->view == 'listview',
			),
		) as $data)
		{
			$options .= '<option value="'.$data['value'].'"'.($data['selected'] ? ' selected="1"' : '').'>'.html::htmlspecialchars($data['text'])."</option>\n";
		}
		$file[++$n] = $this->_select_box('displayed view','view',$options,egw::link('/index.php','',false));

		// Search
		$file[++$n] = array(
			'text' => html::input('keywords', '', 'text',
					'id="calendar_keywords" style="width: 97.5%;" placeholder="'.html::htmlspecialchars(lang('Search').'...').'"'),
			'no_lang' => True,
			'link' => False,
			'icon' => false,
		);
		// Minicalendar
		$link = array();
		foreach(array(
			'day'   => 'calendar.calendar_uiviews.day',
			'week'  => 'calendar.calendar_uiviews.week',
			'month' => 'calendar.calendar_uiviews.month') as $view => $menuaction)
		{
			if ($this->view == 'planner' || $this->view == 'listview')
			{
				$link_vars['menuaction'] = $this->view_menuaction;	// must be first one
				switch($view)
				{
					case 'day':   $link_vars[$this->view.'_days'] = $this->view == 'planner' ? 1 : ''; break;
					case 'week':  $link_vars[$this->view.'_days'] = $this->cal_prefs['days_in_weekview'] == 5 ? 5 : 7; break;
					case 'month': $link_vars[$this->view.'_days'] = 0; break;
				}
				if ($this->view == 'listview') $link_vars['ajax'] = 'true';
			}
			elseif(substr($this->view,0,4) == 'week' && $view == 'week')
			{
				$link_vars['menuaction'] = $this->view_menuaction;	// stay in the N-week-view
			}
			elseif ($view == 'day' && $this->view == 'day4')
			{
				$link_vars['menuaction'] = $this->view_menuaction;	// stay in the day-view
			}
			else
			{
				$link_vars['menuaction'] = $menuaction;
			}
			unset($link_vars['date']);	// gets set in jscal
			$link[$view] = $l = egw::link('/index.php',$link_vars,false);
		}

		if (($flatdate = $this->date))	// string if format YYYYmmdd or timestamp
		{
			$flatdate = is_int($flatdate) ? adodb_date('m/d/Y',$flatdate) :
			substr($flatdate,4,2).'/'.substr($flatdate,6,2).'/'.substr($flatdate,0,4);
		}
		$jscalendar = '<div id="calendar-container"></div>';

		$file[++$n] = array('text' => $jscalendar,'no_lang' => True,'link' => False,'icon' => False);

		// set a baseurl for selectboxes, if we are not running inside calendar (eg. prefs or admin)
		if (substr($_GET['menuaction'],0,9) != 'calendar.')
		{
			$baseurl = egw::link('/index.php',array('menuaction'=>'calendar.calendar_uiviews.index'),false);
		}

		// Category Selection
		$cat_id = explode(',',$this->cat_id);
		$options = '<option value="0">'.lang('All categories').'</option>'.
			$this->categories->formatted_list('select','all',$cat_id,'True');

		$select = ' <select style="width: 87%;" id="calendar_cat_id" name="cat_id" title="'.
			lang('Select a %1',lang('Category')). '"'.($cat_id && count($cat_id) > 1 ? ' multiple=true size=4':''). '>'.
			$options."</select>\n" . html::image('phpgwapi','attach','','id="calendar_cat_id_multiple"');

		$file[++$n] =  array(
			'text' => $select,
			'no_lang' => True,
			'link' => False,
			'icon' => false,
		);


		// Filter all or hideprivate
		$options = '';
		foreach(array(
			'default'     => array(lang('Not rejected'), lang('Show all status, but rejected')),
			'accepted'    => array(lang('Accepted'), lang('Show only accepted events')),
			'unknown'     => array(lang('Invitations'), lang('Show only invitations, not yet accepted or rejected')),
			'tentative'   => array(lang('Tentative'), lang('Show only tentative accepted events')),
			'delegated'   => array(lang('Delegated'), lang('Show only delegated events')),
			'rejected'    => array(lang('Rejected'),lang('Show only rejected events')),
			'owner'       => array(lang('Owner too'),lang('Show also events just owned by selected user')),
			'all'         => array(lang('All incl. rejected'),lang('Show all status incl. rejected events')),
			'hideprivate' => array(lang('Hide private infos'),lang('Show all events, as if they were private')),
			'showonlypublic' =>  array(lang('Hide private events'),lang('Show only events flagged as public, (not checked as private)')),
			'no-enum-groups' => array(lang('only group-events'),lang('Do not include events of group members')),
			'not-unknown' => array(lang('No meeting requests'),lang('Show all status, but unknown')),
		) as $value => $label)
		{
			list($label,$title) = $label;
			$options .= '<option value="'.$value.'"'.($this->filter == $value ? ' selected="selected"' : '').' title="'.$title.'">'.$label.'</options>'."\n";
		}
		// add deleted filter, if history logging is activated
		if($GLOBALS['egw_info']['server']['calendar_delete_history'])
		{
			$options .= '<option value="deleted"'.($this->filter == 'deleted' ? ' selected="selected"' : '').' title="'.lang('Show events that have been deleted').'">'.lang('Deleted').'</options>'."\n";
		}
		$file[] = $this->_select_box('Filter','filter',$options,$baseurl ? $baseurl.'&filter=' : '');

		// Calendarselection: User or Group
		if(count($this->bo->grants) > 0 && $this->accountsel->account_selection != 'none')
		{
			$grants = array();
			foreach($this->bo->list_cals() as $grant)
			{
				$grants[] = $grant['grantor'];
			}
			// we no longer exclude non-accounts from the account-selection: it shows all types of participants
			$accounts = explode(',',$this->owner);
			$current_view_url = egw::link('/index.php',array(
				'menuaction' => $this->view_menuaction,
				'date' => $this->date,
			)+($this->view == 'listview' ? array('ajax' => 'true') : array()),false);
			$file[] = array(
				'text' => "
					<script type=\"text/javascript\" src=\"{$GLOBALS['egw_info']['server']['webserver_url']}/calendar/js/navigation.js\" id=\"calendar-navigation-script\"".
					" data-link-day-url =\"".htmlspecialchars($link['day']).
					"\" data-link-week-url=\"".htmlspecialchars($link['week']).
					"\" data-link-month-url=\"".htmlspecialchars($link['month']).
					"\" data-date=\"".htmlspecialchars($flatdate).
					"\" data-current-date=\"".htmlspecialchars(egw_time::to('now', 'Ymd')).
					"\" data-current-view-url=\"".htmlspecialchars($current_view_url)."\"/></script>\n".

				$this->accountsel->selection('owner','uical_select_owner',$accounts,'calendar+',count($accounts) > 1 ? 4 : 1,False,
					' style="width: '.(count($accounts) > 1 && in_array($this->common_prefs['account_selection'],array('selectbox','groupmembers')) ? '99%' : '86%').';"'.
					' title="'.lang('select a %1',lang('user')).'"','',$grants,false,array($this->bo,'participant_name')),
				'no_lang' => True,
				'link' => False,
				'icon' => false,
			);
		}

		// Merge print
		if ($GLOBALS['egw_info']['user']['preferences']['calendar']['document_dir'])
		{
			$options = '';
			if ($GLOBALS['egw_info']['user']['apps']['importexport'])
			{
				$mime_filter = array('!',	// negativ filter, everything but ...
					'application/vnd.oasis.opendocument.spreadsheet',
					'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
				);
			}
			$documents = calendar_merge::get_documents($GLOBALS['egw_info']['user']['preferences']['calendar']['document_dir'], '', $mime_filter,'calendar');
			foreach($documents as $key => $value)
			{
				$options .= '<option value="'.html::htmlspecialchars($key).'">'.html::htmlspecialchars($value)."</option>\n";
			}
			if($options != '') {
				$options = '<option value="">'.lang('Insert in document')."</option>\n" . $options;
				$select = ' <select style="width: 99%;" name="merge" id="calendar_merge" title="'.
					html::htmlspecialchars(lang('Select a %1',lang('merge document...'))).'">'.
					$options."</select>\n";

				$file[] = array(
					'text' => $select,
					'no_lang' => True,
					'link' => False,
					'icon' => false,
				);
			}
		}

		$appname = 'calendar';
		$menu_title = lang('Calendar Menu');
		display_sidebox($appname,$menu_title,$file);

		// resources menu hooks
 		foreach ($this->bo->resources as $resource)
		{
			if(!is_array($resource['cal_sidebox'])) continue;
			$menu_title = $resource['cal_sidebox']['menu_title'] ? $resource['cal_sidebox']['menu_title'] : lang($resource['app']);
			$file = ExecMethod($resource['cal_sidebox']['file'],array(
				'menuaction' => $this->view_menuaction,
				'owner' => $this->owner,
			));
			display_sidebox($appname,$menu_title,$file);
		}

		if ($GLOBALS['egw_info']['user']['apps']['admin'])
		{
			$file = Array(
				'Configuration'=>egw::link('/index.php','menuaction=admin.uiconfig.index&appname=calendar'),
				'Custom Fields'=>egw::link('/index.php','menuaction=admin.customfields.edit&appname=calendar'),
				'Holiday Management'=>egw::link('/index.php','menuaction=calendar.uiholiday.admin'),
				'Global Categories' =>egw::link('/index.php','menuaction=admin.admin_categories.index&appname=calendar'),
			);
			$GLOBALS['egw']->framework->sidebox($appname,lang('Admin'),$file,'admin');
		}
	}

	public function merge($timespan = array())
	{
		// Merge print
		if($_GET['merge'])
		{
			if(!$timespan)
			{
				$timespan = array(array(
					'start' => is_array($this->first) ? $this->bo->date2ts($this->first) : $this->first,
					'end' => is_array($this->last) ? $this->bo->date2ts($this->last) : $this->last
				));
			}
			$merge = new calendar_merge();
			return $merge->download($_GET['merge'], $timespan, '', $GLOBALS['egw_info']['user']['preferences']['calendar']['document_dir']);
		}
		return false;
	}
}
