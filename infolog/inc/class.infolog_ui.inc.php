<?php
/**
 * InfoLog - User interface
 *
 * @link http://www.egroupware.org
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @package infolog
 * @copyright (c) 2003-13 by Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

/**
 * This class is the UI-layer (user interface) of InfoLog
 */
class infolog_ui
{
	var $public_functions = array(
		'index'       => True,
		'edit'        => True,
		'delete'      => True,
		'close'       => True,
		'admin'       => True,
		'hook_view'   => True,
		'writeLangFile' => True,
		'import_mail' => True,
	);
	/**
	 * reference to the infolog preferences of the user
	 *
	 * @var array
	 */
	var $prefs;
	/**
	 * instance of the bo-class
	 *
	 * @var infolog_bo
	 */
	var $bo;
	/**
	 * instance of the etemplate class
	 *
	 * @var etemplate
	 */
	var $tmpl;
	/**
	 * allowed units and hours per day, can be overwritten by the projectmanager configuration, default all units, 8h
	 *
	 * @var string
	 */
	var $duration_format = ',';	// comma is necessary!

	var $icons = array(
		'type' => array(
			'task'      => 'task.gif',      'task_alt'      => 'Task',
			'phone'     => 'phone.gif',     'phone_alt'     => 'Phonecall',
			'note'      => 'note.gif',      'note_alt'      => 'Note',
			'confirm'   => 'confirm.gif',   'confirm_alt'   => 'Confirmation',
			'reject'    => 'reject.gif',    'reject_alt'    => 'Reject',
			'email'     => 'email.gif',     'email_alt'     => 'Email' ),
		'action' => array(
			'new'       => 'new.gif',       'new_alt'       => 'Add Sub',
			'view'      => 'view.gif',      'view_alt'      => 'View Subs',
			'parent'    => 'parent.gif',    'parent_alt'    => 'View other Subs',
			'edit'      => 'edit.gif',      'edit_alt'      => 'Edit',
			'addfile'   => 'addfile.gif',   'addfile_alt'   => 'Add a file',
			'delete'    => 'delete.gif',    'delete_alt'    => 'Delete',
			'close'     => 'done.gif',      'close_alt'     => 'Close' ,
			'close_all' => 'done_all.gif',  'close_all_alt' => 'Close' ),
		'status' => array(
			'billed'    => 'billed.gif',    'billed_alt'    => 'billed',
			'done'      => 'done.gif',      'done_alt'      => 'done',
			'will-call' => 'will-call.gif', 'will-call_alt' => 'will-call',
			'call'      => 'call.gif',      'call_alt'      => 'call',
			'ongoing'   => 'ongoing.gif',   'ongoing_alt'   => 'ongoing',
			'offer'     => 'offer.gif',     'offer_alt'     => 'offer' )
	);
	var $filters;
	var $messages = array(
		'edit'    => 'InfoLog - Edit',
		'add'     => 'InfoLog - New',
		'add_sub' => 'InfoLog - New Subproject',
		'sp'      => '- Subprojects from',
	);

	/**
	 * Constructor
	 *
	 * @return infolog_ui
	 */
	function __construct()
	{
		if ($GLOBALS['egw_info']['flags']['currentapp'] != 'infolog') translation::add_app('infolog');

		// Make sure Global category is infolog - on first load, it may not be
		if($GLOBALS['egw_info']['flags']['currentapp'] == 'infolog' && !$GLOBALS['egw']->categories->app_name)
		{
			$GLOBALS['egw']->categories = new categories();
		}

		$this->bo = new infolog_bo();

		$this->tmpl = new etemplate_new();

		$this->user = $GLOBALS['egw_info']['user']['account_id'];

		$this->prefs =& $GLOBALS['egw_info']['user']['preferences']['infolog'];

		// read the duration format from project-manager
		if ($GLOBALS['egw_info']['apps']['projectmanager'])
		{
			$pm_config = config::read('projectmanager');
			$this->duration_format = str_replace(',','',implode('', (array)$pm_config['duration_units']));
			//error_log(__METHOD__."() ".__LINE__." duration_format=$this->duration_format, duration_unit=".array2string($pm_config['duration_units']));
			$this->hours_per_workday = $pm_config['hours_per_workday'];
			unset($pm_config);
		}
		$this->filters =& $this->bo->filters;
		/* these are just for testing of the notifications
		for($i = -1; $i <= 3; ++$i)
		{
			$this->filters['delegated-open-enddate'.date('Y-m-d',time()+$i*24*60*60)] = "delegated due in $i day(s)";
		}
		for($i = -1; $i <= 3; ++$i)
		{
			$this->filters['responsible-open-enddate'.date('Y-m-d',time()+$i*24*60*60)] = "responsible due in $i day(s)";
		}
		for($i = -1; $i <= 3; ++$i)
		{
			$this->filters['delegated-open-date'.date('Y-m-d',time()+$i*24*60*60)] = "delegated starting in $i day(s)";
		}
		for($i = -1; $i <= 3; ++$i)
		{
			$this->filters['responsible-open-date'.date('Y-m-d',time()+$i*24*60*60)] = "responsible starting in $i day(s)";
		}
		*/
		$GLOBALS['infolog_ui'] =& $this;	// make ourself availible for ExecMethod of get_rows function

		// can be removed for next release / infolog update
		if (!$GLOBALS['egw']->hooks->hook_exists('calendar_set','infolog'))
		{
			$GLOBALS['egw']->hooks->register_single_app_hook('infolog','calendar_set');
		}
	}

	/**
	 * Sets additional fields for one infolog entry, which are not persistent in the DB
	 *
	 * @param array $info infolog entry read from the db
	 * @param array &$readonlys ACL specific settings for the buttons
	 * @param string $action
	 * @param string/int $action_id
	 * @param boolean $show_links
	 * @param int $details
	 * @return array
	 */
	function get_info($info,&$readonlys,$action='',$action_id='',$show_links=false,$details = 1)
	{
		if (!is_array($info))
		{
			$info = $this->bo->read($info);
		}
		$id = $info['info_id'];
		$done = $info['info_status'] == 'done' || $info['info_status'] == 'billed' || $info['info_status'] == 'cancelled'; //cancelled is regarded as a completed status as well in bo
		// regard an infolog as done/billed/cancelled if its percentage is 100% when there is to status like the above for that type
		if (!$done && !isset($this->bo->status[$info['info_type']]['done']) && !isset($this->bo->status[$info['info_type']]['billed']) &&
			!isset($this->bo->status[$info['info_type']]['cancelled']) && (int)$info['info_percent']==100) $done = true ;
		$info['sub_class'] = $this->bo->enums['priority'][$info['info_priority']] . ($done ? '_done' : '');
		if (!$done && $info['info_enddate'] < $this->bo->user_time_now)
		{
			$info['end_class'] = 'infolog_overdue';
		}
		if (!isset($info['info_anz_subs'])) $info['info_anz_subs'] = $this->bo->anzSubs($id);
		$this->bo->link_id2from($info,$action,$action_id);	// unset from for $action:$action_id
		$info['info_percent'] = (int) $info['info_percent'].'%';
		$editrights = $this->bo->check_access($info,EGW_ACL_EDIT);
		$isresposible = $this->bo->is_responsible($info);
		if ((!($editrights || // edit rights or more then standard responsible rights
			$isresposible && array_diff($this->bo->responsible_edit,array('info_status','info_percent','info_datecompleted')))))
		{
			$info['class'] .= 'rowNoEdit ';
		}
		if ($info['status'] == 'deleted' && !$this->bo->check_access($info, EGW_ACL_UNDELETE))
		{
			$info['class'] .= 'rowNoUndelete ';
		}
		if (($done || (!($editrights || $isresposible))))
		{
			$info['class'] .= 'rowNoClose ';
		}
		// this one is supressed, when you are not allowed to edit, or not responsible, or the entry is closed
		// and has no children. If you want that this one is shown if there are children regardless of the status of the current or its childs,
		// then modify ($done) to ($done && !$info['info_anz_subs'])
		if ($done || !$info['info_anz_subs'] || (!($editrights || $isresposible)))
		{
			$info['class'] .= 'rowNoCloseAll ';
		}
		if (!$this->bo->check_access($info,EGW_ACL_DELETE))
		{
			$info['class'] .= 'rowNoDelete ';
		}
		if (!$this->bo->check_access($info,EGW_ACL_ADD))
		{
			$info['class'] .= 'rowNoSubs ';
		}
		if ($info['info_id_parent']) $info['class'] .= 'infolog_rowHasParent ';
		if ($info['info_anz_subs'] > 0) $info['class'] .= 'infolog_rowHasSubs ';

		if (!$show_links) $show_links = $this->prefs['show_links'];
		if (($show_links != 'none' && $show_links != 'no_describtion' ||
			 $this->prefs['show_times'] || isset($GLOBALS['egw_info']['user']['apps']['timesheet'])) &&
			(isset($info['links']) || ($info['links'] = egw_link::get_links('infolog',$info['info_id'],'','link_lastmod DESC',true))))
		{
			$timesheets = array();
			foreach ($info['links'] as $link)
			{
				if ($show_links != 'none' && $show_links != 'no_describtion' &&
					$link['link_id'] != $info['info_link_id'] &&
				    ($link['app'] != $action || $link['id'] != $action_id) &&
					($show_links == 'all' || ($show_links == 'links') === ($link['app'] != egw_link::VFS_APPNAME)))
				{
					$info['filelinks'][] = $link;
				}
				if (!$info['pm_id'] && $link['app'] == 'projectmanager')
				{
					$info['pm_id'] = $link['id'];
				}
				if ($link['app'] == 'timesheet') $timesheets[] = $link['id'];
			}
			if ($this->prefs['show_times'] && isset($GLOBALS['egw_info']['user']['apps']['timesheet']) && $timesheets)
			{
				$sum = ExecMethod('timesheet.timesheet_bo.sum',$timesheets);
				$info['info_sum_timesheets'] = $sum['duration'];
			}
		}
		$info['info_type_label'] = $this->bo->enums['type'][$info['info_type']];
		$info['info_status_label'] = isset($this->bo->status[$info['info_type']][$info['info_status']]) ?
			$this->bo->status[$info['info_type']][$info['info_status']] : $info['info_status'];

		if (!$this->prefs['show_percent'] || $this->prefs['show_percent'] == 2 && !$details)
		{
			if ($info['info_status'] == 'ongoing' && $info['info_type'] != 'phone')
			{
				$info['info_status'] = $info['info_status_label'] = $info['info_percent'];
			}
			$readonlys["edit_percent[$id]"] = true;
		}
		elseif($readonlys["edit_percent[$id]"])	// show percent, but button is switched off
		{
			$info['info_percent2'] = $info['info_percent'];
		}
		if ($this->prefs['show_id'] == 1 || $this->prefs['show_id'] == 2 && $details)
		{
			$info['info_number'] = $info['info_id'];
		}
		//error_log(__METHOD__."() returning ".array2string($info));
		return $info;
	}

	/**
	 * Callback for nextmatch widget
	 *
	 * @param array &$query
	 * @param array &$rows
	 * @param array &$readonlys
	 * @return int
	 */
	function get_rows(&$query,&$rows,&$readonlys)
	{
		//error_log(__METHOD__."() query[csv_export]=".array2string($query['csv_export']).", query[filter]=".array2string($query['filter']).", query[col_filter]=".array2string(array_diff($query['col_filter'],array('',0))).' '.function_backtrace());
		if (!$query['csv_export'])
		{
			unset($query['no_actions']);
			$parent_id = $query['col_filter']['parent_id'];
			unset($query['col_filter']['parent_id']);
			egw_cache::setSession('infolog', $query['session_for'].'session_data', $query);
			$query['actions'] = $this->get_actions($query);
			$query['row_id'] = 'info_id';
			$query['row_modified'] = 'info_datemodified';
			$query['parent_id'] = 'info_id_parent';
			$query['is_parent'] = 'info_anz_subs';
			$query['action_var'] = 'multi_action';	// as 'action' is already used in infolog
		}
		$orginal_colfilter = $query['col_filter'];
		if (isset($parent_id)) $query['col_filter']['info_id_parent'] = (int)$parent_id;

		//echo "<p>infolog_ui.get_rows(start=$query[start],search='$query[search]',filter='$query[filter]',cat_id=$query[cat_id],action='$query[action]/$query[action_id]',col_filter=".print_r($query['col_filter'],True).",sort=$query[sort],order=$query[order])</p>\n";
		if (!isset($query['start'])) $query['start'] = 0;

		// handle linked filter (show only entries linked to a certain other entry)
		if ($query['col_filter']['linked'])
		{
			list($app,$id) = explode(':',$query['col_filter']['linked']);
			if (!($links = egw_link::get_links($app,$id,'infolog')))
			{
				$rows = array();	// no infologs linked to project --> no rows to return
				return 0;
			}
			$query['col_filter']['info_id'] = array_values(array_unique($links));
			$linked = $query['col_filter']['linked'];
		}
		unset($query['col_filter']['linked']);

		// check if we have a custom, type-specific template
		unset($query['template']);
		unset($query['custom_fields']);
		if ($query['col_filter']['info_type'])
		{
			$tpl = new etemplate_new;
			if ($tpl->read('infolog.index.rows.'.$query['col_filter']['info_type']))
			{
				$query['template'] =& $tpl;
				$query['custom_fields'] = true;	// read the custom fields too
			}
			//echo "<p align=right>template ='".'infolog.index.rows.'.$query['col_filter']['info_type']."'".(!$query['template'] ? ' not' : '')." found</p>\n";
			// If status is not valid for selected type, clear status filter
			if($query['col_filter']['info_status'] && $query['col_filter']['info_status'] != 'deleted' &&
				!in_array($query['col_filter']['info_status'], $this->bo->status[$query['col_filter']['info_type']]))
			{
				$query['col_filter']['info_status'] = '';
			}
		}
		// do we need to read the custom fields, depends on the column is enabled and customfields exist, prefs are filter specific
		// so we have to check that as well
		$details = $query['filter2'] == 'all';
		$columselection = $this->prefs['nextmatch-infolog.index.rows'.($details?'-details':'')];
		//_debug_array($columselection);
		if ($columselection)
		{
			$query['selectcols'] = $columselection;
			$columselection = explode(',',$columselection);
		}
		else
		{
			$columselection = $query['selectcols'] ? explode(',',$query['selectcols']) : array();
		}
		// do we need to query the cf's
		$query['custom_fields'] = $this->bo->customfields && (!$columselection || in_array('customfields',$columselection));

		$infos = $this->bo->search($query);
		$query['col_filter'] = $orginal_colfilter;
		if (!is_array($infos))
		{
			$infos = array( );
		}
		// add a '-details' to the name of the columnselection pref
		if ($details)
		{
			$query['columnselection_pref'] = (is_object($query['template'])?$query['template']->name:'infolog.index.rows').'-details';
			$query['default_cols'] = '!cat_id,info_used_time_info_planned_time,info_used_time_info_planned_time_info_replanned_time,info_id,actions';
		}
		else
		{
			$query['columnselection_pref'] = 'infolog.index.rows';
			$query['default_cols'] = '!cat_id,info_datemodified,info_used_time_info_planned_time,info_used_time_info_planned_time_info_replanned_time,info_id,actions';
		}
		// set old show_times pref, that get_info calculates the cumulated time of the timesheets (we only check used&planned to work for both time cols)
		$this->prefs['show_times'] = strpos($this->prefs['nextmatch-'.$query['columnselection_pref']],'info_used_time_info_planned_time') !== false;

		// query all links and sub counts in one go
		if ($infos && (!$query['csv_export'] || !is_array($query['csv_export'])))
		{
			$links = egw_link::get_links_multiple('infolog',array_keys($infos),true);
			$anzSubs = $this->bo->anzSubs(array_keys($infos));
		}
		$rows = array();

		// Don't add parent in if info_id_parent (expanding to show subs)
		if ($query['action_id'] && !$query['col_filter']['info_id_parent'])
		{
			$parents = $query['action'] == 'sp' && $query['action_id'] ? (array)$query['action_id'] : array();
			if (count($parents) == 1 && is_array($query['action_id']))
			{
				$query['action_id'] = array_shift($query['action_id']);	// display single parent as app_header
			}
		}

		$parent_first = count($parents) == 1;
		$parent_index = 0;
		// et2 nextmatch listens to total, and only displays that many rows, so add parent in or we'll lose the last row
		if($parent_first || $query['action'] = 'sp' && is_array($query['action_id'])) $query['total']++;

		// Check to see if we need to remove description
		foreach($infos as $id => $info)
		{
			if (!(strpos($info['info_addr'],',')===false) && strpos($info['info_addr'],', ')===false) $info['info_addr'] = str_replace(',',', ',$info['info_addr']);
			if (!$query['csv_export'] || !is_array($query['csv_export']))
			{
				$info['links'] =& $links[$id];
				$info['info_anz_subs'] = (int)$anzSubs[$id];
				$info = $this->get_info($info,$readonlys,$query['action'],$query['action_id'],$query['filter2'],$details);
			}
			// for subs view ('sp') add parent(s) in front of subs once(!)
			if ( $parent_first && ($main = $this->bo->read($query['action_id'])) ||
				$parents && ($parent_index = array_search($info['info_id_parent'], $parents)) !== false &&
				($main = $this->bo->read($info['info_id_parent'])))
			{
				$main = $this->get_info($main, $readonlys);
				$main['class'] .= 'th ';
				// if only certain custom-fields are to be displayed, we need to unset the not displayed ones manually
				// as read() always read them all, while search() only reads the selected ones
				if ($query['custom_fields'])
				{
					foreach($columselection as $col)
					{
						if ($col[0] == '#')
						{
							foreach(array_keys($main) as $n)
							{
								if ($n[0] == '#' && !in_array($n, $columselection)) unset($main[$n]);
							}
							break;
						}
					}
				}
				$parent_first = false;
				if($query['start'] == 0)
				{
					array_splice($rows, $id, 0, array($main));
					unset($parents[$parent_index]);
				}
			}
			$rows[] = $info;
		}
		unset($links);

		if ($query['cat_id']) $rows['no_cat_id'] = true;
		if ($query['no_actions']) $rows['no_actions'] = true;
		$rows['no_timesheet'] = !isset($GLOBALS['egw_info']['user']['apps']['timesheet']);
		$rows['duration_format'] = ','.$this->duration_format.',,1';

		// switch cf column off, if we have no cf's
		if (!$query['custom_fields']) $rows['no_customfields'] = true;

		if ($GLOBALS['egw_info']['user']['preferences']['common']['account_selection'] == 'none' &&
			!isset($GLOBALS['egw_info']['user']['apps']['admin']))
		{
			$rows['no_info_owner_info_responsible'] = true;
			// dont show owner, responsible in the columnselection
			$query['options-selectcols']['info_owner'] = $query['options-selectcols']['info_responsible'] = false;
		}

		// if filtered by type, show only the stati of the filtered type
		$rows['sel_options']['info_status'] = $this->bo->get_status($query['col_filter']['info_type']);

		if ($this->bo->history)
		{
			$rows['sel_options']['info_status']['deleted'] = 'deleted';
		}

		if ($GLOBALS['egw_info']['flags']['currentapp'] == 'infolog')
		{
			$GLOBALS['egw_info']['flags']['app_header'] = lang('Infolog');
			if ($query['filter'] != '' && !empty($this->filters[$query['filter']]))
			{
				$GLOBALS['egw_info']['flags']['app_header'] .= ' - '.lang($this->filters[$query['filter']]);
			}
			if ($query['action'] && ($title = $query['action_title'] || is_array($query['action_id']) ?
				$query['action_title'] : egw_link::title($query['action']=='sp'?'infolog':$query['action'],$query['action_id'])))
			{
				$GLOBALS['egw_info']['flags']['app_header'] .= ': '.$title;
			}
		}

		if (isset($linked)) $query['col_filter']['linked'] = $linked;  // add linked back to the colfilter

		return $query['total'];
	}

	/**
	 * Hook for timesheet to set some extra data and links
	 *
	 * @param array $data
	 * @param int $data[id] info_id
	 * @return array with key => value pairs to set in new timesheet and link_app/link_id arrays
	 */
	function timesheet_set($data)
	{
		$set = array();
		if ((int)$data['id'] && ($info = $this->bo->read($data['id'])))
		{
			if ($info['info_cat']) $set['cat_id'] = $info['info_cat'];

			foreach(egw_link::get_links('infolog',$info['info_id'],'','link_lastmod DESC',true) as $link)
			{
				if ($link['app'] != 'timesheet' && $link['app'] != egw_link::VFS_APPNAME)
				{
					$set['link_app'][] = $link['app'];
					$set['link_id'][]  = $link['id'];
				}
			}
		}
		return $set;
	}

	/**
	 * Hook for calendar to set some extra data and links
	 *
	 * @param array $data event-array preset by calendar plus
	 * @param int $data[entry_id] info_id
	 * @return array with key => value pairs to set in new event and link_app/link_id arrays
	 */
	function calendar_set($data)
	{
		if (!($infolog = $this->bo->read($data['entry_id'])))
		{
			return $data;
		}
		$event = array_merge($data,array(
			'category'	=> $GLOBALS['egw']->categories->check_list(EGW_ACL_READ, $infolog['info_cat']),
			'priority'	=> $infolog['info_priority'] + 1,
			'public'	=> $infolog['info_access'] != 'private',
			'title'		=> $infolog['info_subject'],
			'description'	=> $infolog['info_des'],
			'location'	=> $infolog['info_location'],
			'start'		=> $infolog['info_startdate'],
			'end'		=> $infolog['info_enddate'] ? $infolog['info_enddate'] : $infolog['info_datecompleted']
		));
		unset($event['entry_id']);
		if (!$event['end']) $event['end'] = $event['start'] + (int) $GLOBALS['egw_info']['user']['preferences']['calendar']['defaultlength']*60;

		// Match categories by name
		$event['category'] = $GLOBALS['egw']->categories->name2id(categories::id2name($infolog['info_cat']));

		// make current user the owner of the new event, not the selected calendar, if current user has rights for it
		$event['owner'] = $user = $GLOBALS['egw_info']['user']['account_id'];

		// add/modify participants according to prefs
		$prefs = explode(',',$this->prefs['calendar_set'] ? $this->prefs['calendar_set'] : 'responsible,contact,user');

		// if no default participants (selected calendars) --> remove all
		if (!in_array('selected',$prefs))
		{
			$event['participants'] = $event['participant_types'] = array();
		}
		// Add responsible as participant
		if (in_array('responsible',$prefs))
		{
			foreach($infolog['info_responsible'] as $responsible)
			{
				$event['participants'][$responsible] = $event['participant_types']['u'][$responsible] =
					calendar_so::combine_status($user==$responsible?'A':'U');
			}
		}
		// Add linked contact as participant
		if (in_array('contact',$prefs) && $infolog['info_link']['app'] == 'addressbook')
		{
			$event['participants'][calendar_so::combine_user('c',$infolog['info_link']['id'])] =
				$event['participant_types']['c'][$infolog['info_link']['id']] = calendar_so::combine_status('U');
		}
		if (in_array('owner',$prefs))
		{
			$event['participants'][$infolog['info_owner']] = $event['participant_types']['u'][$infolog['info_owner']] =
				calendar_so::combine_status('A',1,'CHAIR');
		}
		// Add current user, if set or no other participants, which is not allowed
		if (in_array('user',$prefs))
		{
			$event['participants'][$user] = $event['participant_types']['u'][$user] =
				calendar_so::combine_status('A',1,'CHAIR');
		}

		// Add infolog link to calendar entry
		$event['link_app'][] = $infolog['info_link']['app'];
		$event['link_id'][]  = $infolog['info_link']['id'];

		// Copy infolog's links
		foreach(egw_link::get_links('infolog',$infolog['info_id'],'','link_lastmod DESC',true) as $link)
		{
			if ($link['app'] != egw_link::VFS_APPNAME)
			{
				$event['link_app'][] = $link['app'];
				$event['link_id'][]  = $link['id'];
			}
		}
		// Copy same custom fields
		foreach(array_keys(config::get_customfields('calendar')) as $name)
		{
			if ($this->bo->customfields[$name]) $event['#'.$name] = $infolog['#'.$name];
		}
		//error_log(__METHOD__.'('.array2string($data).') infolog='.array2string($infolog).' returning '.array2string($event));
		return $event;
	}

	/**
	 * Shows the infolog list
	 *
	 * @param array/string $values=null etemplate content or 'reset_action_view' if called by index.php to reset an action-view
	 * @param string $action='' if set only entries liked to that $action:$action_id are shown
	 * @param string $action_id='' if set only entries liked to that $action:$action_id are shown
	 * @param mixed $called_as=0 this is how we got called, for a hook eg. the call-params of that page containing the hook
	 * @param boolean $extra_app_header=false
	 * @param boolean $return_html=false
	 * @param string $own_referer='' this is our own referer
	 * @param string $action_title='' app_header for the action, if '' we try the link-title
	 */
	function index($values = null,$action='',$action_id='',$called_as=0,$extra_app_header=False,$return_html=False,$own_referer='',$action_title='')
	{
		unset($extra_app_header);	// not used, but dont want to change signature
		if (is_array($values))
		{
			$called_as = $values['called_as'];
			$own_referer = $values['own_referer'];
		}
		elseif ($own_referer === '')
		{
			$own_referer = common::get_referer();
			if (strpos($own_referer,'menuaction=infolog.infolog_ui.edit') !== false)
			{
				$own_referer = $GLOBALS['egw']->session->appsession('own_session','infolog');
			}
			else
			{
				$GLOBALS['egw']->session->appsession('own_session','infolog',$own_referer);
			}
		}

		// Handle legacy buttons like actions
		if(is_array($values))
		{
			foreach(array('document', 'view', 'delete') as $button)
			{
				if(isset($values['nm']['rows'][$button]))
				{
					list($id) = @each($values['nm']['rows'][$button]);
					$values['nm']['multi_action'] = $button;
					$values['nm']['selected'] = array($id);
					break; // Only one can come per submit
				}
			}
		}
		if (is_array($values) && !empty($values['nm']['multi_action']))
		{
			if (!count($values['nm']['selected']) && !$values['nm']['select_all'])
			{
				$msg = lang('You need to select some entries first');
			}
			else
			{
				// Some processing to add values in for links and cats
				$multi_action = $values['nm']['multi_action'];
				// Action has an additional action - add / delete, etc.  Buttons named <multi-action>_action[action_name]
				if(in_array($multi_action, array('link', 'responsible')))
				{
					// eTemplate ignores the _popup namespace, but et2 doesn't
					if($values[$multi_action.'_popup'])
					{
						$popup =& $values[$multi_action.'_popup'];
					}
					else
					{
						$popup =& $values;
					}
					$values['nm']['multi_action'] .= '_' . key($popup[$multi_action . '_action']);
					if($multi_action == 'link')
					{
						$popup[$multi_action] = $popup['link']['app'] . ':'.$popup['link']['id'];
					}
					else if(is_array($popup[$multi_action]))
					{
						$popup[$multi_action] = implode(',',$popup[$multi_action]);
					}
					$values['nm']['multi_action'] .= '_' . $popup[$multi_action];
					unset($values[$multi_action.'_popup']);
					unset($values[$multi_action]);
				}
				$success = $failed = $action_msg = null;
				if ($this->action($values['nm']['multi_action'], $values['nm']['selected'], $values['nm']['select_all'],
					$success, $failed, $action_msg, $values['nm'], $msg, $values['nm']['checkboxes']['no_notifications']))
				{
					$msg .= lang('%1 entries %2',$success,$action_msg);
					egw_framework::message($msg);
				}
				elseif(is_null($msg))
				{
					$msg .= lang('%1 entries %2, %3 failed because of insufficent rights !!!',$success,$action_msg,$failed);
					egw_framework::message($msg,'error');
				}
				elseif($msg)
				{
					$msg .= "\n".lang('%1 entries %2, %3 failed.',$success,$action_msg,$failed);
					egw_framework::message($msg,'error');
				}
				unset($values['nm']['multi_action']);
				unset($values['nm']['select_all']);
			}
		}
		if (!$action)
		{
			$action = is_array($values) && $values['action'] ? $values['action'] : get_var('action',array('POST','GET'));
			$action_id = is_array($values) && $values['action_id'] ? $values['action_id'] : get_var('action_id',array('POST','GET'));
			$action_title = is_array($values) && $values['action_title'] ? $values['action_title'] : get_var('action_title',array('POST','GET'));
		}
		//echo "<p>".__METHOD__."(action='$action/$action_id',called_as='$called_as/$values[referer]',own_referer='$own_referer') values=\n"; _debug_array($values);
		if (!is_array($values))
		{
			$nm = egw_cache::getSession('infolog', $this->called_by.'session_data');
			unset($nm['rows']);
			if ($values === 'reset_action_view')
			{
				$action = '';
				$action_id = 0;
				$action_title = '';
			}
			if($_GET['ajax'] === 'true')
			{
				$nm['action'] = '';
				$nm['action_id'] = 0;
				$nm['action_title'] = '';
				// check if action-view reset filter and restore it
				if (($filter = egw_cache::getSession('infolog', 'filter_reset_from')))
				{
					$nm['filter'] = $filter;
					egw_cache::unsetSession('infolog', 'filter_reset_from');
				}
			}
			$values = array('nm' => $nm);

			if (isset($_GET['filter']) && $_GET['filter'] != 'default' || !isset($values['nm']['filter']) && !$this->called_by)
			{
				$values['nm']['filter'] = $_GET['filter'] && $_GET['filter'] != 'default' ? $_GET['filter'] :
					$this->prefs['defaultFilter'];
			}
			if (!isset($values['nm']['order']) || !$values['nm']['order'])
			{
				$values['nm']['order'] = 'info_datemodified';
				$values['nm']['sort'] = 'DESC';
			}

			if (!$values['nm']['session_for'] && $this->called_by) $values['nm']['session_for'] = $this->called_by;

			$action_id = $values['action_id'] = $action ? $action_id : $nm['action_id'];
			$action_title = $values['action_title'] = $action ? $action_title : $nm['action_title'];
			$action = $values['action'] = $action ? $action : $nm['action'];
		}
		if($_GET['search']) $values['nm']['search'] = $_GET['search'];

		if ($values['nm']['add'])
		{
			$values['add'] = $values['nm']['add'];
			unset($values['nm']['add']);
		}
		unset($values['nm']['rows']['checked']);	// not longer used, but hides button actions

		if ($values['add'] || $values['cancel'] || isset($values['nm']['rows']) || isset($values['main']))
		{
			if ($values['add'])
			{
				list($type) = each($values['add']);
				return $this->edit(0,$action,$action_id,$type,$called_as);
			}
			elseif ($values['cancel'] && $own_referer)
			{
				unset($values['nm']['multi_action']);
				unset($values['nm']['action_id']);
				egw_cache::setSession('infolog', $values['nm']['session_for'].'session_data', $values['nm']);
				$this->tmpl->location($own_referer);
			}
			else
			{
				list($do,$do2) = isset($values['main']) ? each($values['main']) : @each($values['nm']['rows']);
				list($do_id) = @each($do2);
				switch((string)$do)
				{
					case 'close':
						$closesingle=true;
					case 'close_all':
						$this->close($do_id,$called_as,$closesingle);
						break;
					case 'view':
						$value = array();
						$action = 'sp';
						$action_id = $do_id;
						break;
					default:
						$value = array();
						$action = '';
						$action_id = 0;
						break;
				}
			}
		}
		switch ($action)
		{
			case 'sp':
				if (!is_array($action_id) && strpos($action_id, 'infolog:') === 0) $action_id = (int)substr($action_id, 8);
				if ((is_array($action_id) && !$this->bo->read(current($action_id))) || !$this->bo->read($action_id))
				{
					$action = '';
					$action_id = 0;
					break;
				}
				else
				{
					$values['nm']['col_filter']['info_id_parent'] = $action_id;
				}
				break;
			default:
				if(in_array($action, array_keys(egw_link::app_list())))
				{
					$values['nm']['col_filter']['linked'] = "$action:$action_id";
				}
		}
		$readonlys['cancel'] = $action != 'sp';

		$this->tmpl->read('infolog.index');
		$values['nm']['options-filter'] = $this->filters;
		$values['nm']['get_rows'] = 'infolog.infolog_ui.get_rows';
		$values['nm']['options-filter2'] = (in_array($this->prefs['show_links'],array('all','no_describtion')) ? array() : array(
			''               => 'default',
		)) + array(
			'no_describtion' => 'no details',
			'all'            => 'details',
		);
		if(!isset($values['nm']['filter2'])) $values['nm']['filter2'] = $this->prefs['show_links'];

		//apply infolog_filter_change javascript method (hide/show of date filter form) over onchange filter
		$values['nm']['filter_onchange'] = "app.infolog.filter_change();";

		//apply infolog_filter2_change javascript method (show/hide details each rows) over onchange filter2
		$values['nm']['filter2_onchange'] = "app.infolog.filter2_change();";

		// disable favories dropdown button, if not running as infolog
		if ($called_as && $called_as != 'infolog')
		{
			$values['nm']['favorites'] = false;
		}
		else
		{
			// Allow saving parent ID into favorites
			$values['nm']['favorites'] = array('action','action_id');
		}

		// Allow add actions even when there's no rows
		$values['nm']['placeholder_actions'] = array('new');

		// disable columns for main entry as set in the pref for details or no details
		if ($action == 'sp')
		{
			$pref = 'nextmatch-infolog.index.rows'.($values['nm']['filter2']=='all'?'-details':'');
			foreach(array('info_used_time_info_planned_time_info_replanned_time','info_datemodified','info_owner_info_responsible','customfields') as $name)
			{
				$values['main']['no_'.$name] = strpos($this->prefs[$pref],$name) === false;
			}
			if (!$values['main']['no_customfields'])
			{
				// set the column-header of the main table for the customfields.
				foreach(array_keys($this->bo->customfields) as $lname)
				{
					$values['main']['customfields'].=$lname."\n";
				}
			}
		}
		$values['nm']['header_right'] = 'infolog.index.header_right';
		if ($values['nm']['filter']=='bydate')
		{
			foreach (array_keys($values['nm']['col_filter']) as $colfk)
			{
				if (is_int($colfk)) unset($values['nm']['col_filter']);
			}
		}
		$values['action'] = $persist['action'] = $values['nm']['action'] = $action;
		$values['action_id'] = $persist['action_id'] = $values['nm']['action_id'] = $action_id;
		$values['action_title'] = $persist['action_title'] = $values['nm']['action_title'] = $action_title;
		$persist['called_as'] = $called_as;
		$persist['own_referer'] = $own_referer;

		// store whole $values[nm] in etemplate request
		unset($values['nm']['rows']);
		$persist['nm'] = $values['nm'];

		if (!$called_as)
		{
			$GLOBALS['egw_info']['flags']['params']['manual'] = array('page' => 'ManualInfologIndex');
		}
		else
		{
			$values['css'] = '<style type="text/css">@import url('.$GLOBALS['egw_info']['server']['webserver_url'].'/infolog/templates/default/app.css);'."</style>";
			// Avoid DOM conflicts
			$this->tmpl->set_dom_id("{$this->tmpl->name}-$action-$action_id");
		}
		// add scrollbar to long description, if user choose so in his prefs
		if ($this->prefs['limit_des_lines'] > 0 || (string)$this->prefs['limit_des_lines'] == '')
		{
			$values['css'] .= '<style type="text/css">@media screen { .infoDes {  '.
				' max-height: '.
				(($this->prefs['limit_des_lines'] ? $this->prefs['limit_des_lines'] : 5) * 1.35).	// dono why em is not real lines
				'em; overflow: auto; }}</style>';
		}

		$sel_options = array(
			'info_type'     => $this->bo->enums['type'],
			'pm_id'      => array(lang('No project')),
			'info_priority' => $this->bo->enums['priority'],
		);

		// remove group-types user has not any rights to as filter
		// does not take implicit rights as delegated into account, so they will not be available as filters
		foreach($this->bo->group_owners as $type => $group)
		{
			if (!isset($this->bo->grants[$group])) unset($sel_options['info_type'][$type]);
		}


		return $this->tmpl->exec('infolog.infolog_ui.index',$values,$sel_options,$readonlys,$persist,$return_html ? -1 : 0);
	}

	/**
	 * Get valid types
	 *
	 * @return array - array of valid types
	 */
	private function get_validtypes()
	{
		// Types
		$types = $this->bo->enums['type'];
		if ($this->bo->group_owners)
		{
			// remove types owned by groups the user has no edit grant
			foreach($this->bo->group_owners as $type => $group)
			{
				if (!($this->bo->grants[$group] & EGW_ACL_EDIT))
				{
					unset($types[$type]);
				}
			}
		}
		return $types;
	}

	/**
	 * Get actions / context menu items
	 *
	 * @param array $query
	 * @return array see nextmatch_widget::get_actions()
	 */
	private function get_actions(array $query)
	{
		for($i = 0; $i <= 100; $i += 10)
		{
			$percent[$i] = $i.'%';
		}
		// Types
		$types = $this->get_validtypes();
		$types_add = array();
		foreach($types as $type => &$data)
		{
			if ($type=='email') continue;//requirement by sales that it should not be shown in right - click - action dialog
			$data = array(
				'caption' => $data,
				'icon' => $type,
			);
			$types_add[$type] = $data + array(
				'url' => 'menuaction=infolog.infolog_ui.edit&type='.$type,
				'popup' => egw_link::get_registry('infolog', 'add_popup'),
			);
		}

		$icons = null;
		$statis = $this->bo->get_status($query['col_filter']['info_type'], $icons);
		foreach($statis as $type => &$data)
		{
			$data = array(
				'caption' => $data,
				'icon' => $icons[$type],
			);
		}

		$actions = array(
			'open' => array(
				'caption' => 'Open',
				'default' => true,
				'allowOnMultiple' => false,
				'url' => 'menuaction=infolog.infolog_ui.edit&info_id=$id',
				'popup' => egw_link::get_registry('infolog', 'add_popup'),
				'group' => $group=1,
			),
			'view' => array(
				'caption' => 'View subs',
				'icon' => 'egw_action/arrow_left',
				'group' => $group,
				'hint' => 'View all subs of this entry',
				'enableClass' => 'infolog_rowHasSubs',
				'enabled' => true,
			),
			'parent' => array(
				'caption' => 'View parent',
				'icon' => 'egw_action/arrow_up',
				'group' => $group,
				'hideOnDisabled' => true,
				'hint' => 'View the parent of this entry and all his subs',
				'enabled' => true,
				'enableClass' => 'infolog_rowHasParent'
			),
			'add' => array(
				'caption' => 'Add',
				'group' => $group,
				'children' => array(
					'new' => array(
						'caption' => 'New',
						'children' => $types_add,
						'icon' => 'task',
					),
					'sub' => array(
						'caption' => 'Sub-entry',
						'url' => 'menuaction=infolog.infolog_ui.edit&action=sp&action_id=$id',
						'popup' => egw_link::get_registry('infolog', 'add_popup'),
						'allowOnMultiple' => false,
						'hint' => 'Add a new sub-task, -note, -call to this entry',
						'icon' => 'new',
					),
					'copy' => array(
						'caption' => 'Copy',
						'url' => 'menuaction=infolog.infolog_ui.edit&action=copy&info_id=$id',
						'popup' => egw_link::get_registry('infolog', 'add_popup'),
						'allowOnMultiple' => false,
						'icon' => 'copy',
					),
				),
			),
			'no_notifications' => array(
				'caption' => 'Do not notify',
				'checkbox' => true,
				'hint' => 'Do not notify of these changes',
				'group' => $group,
			),
			// modifying content of one or multiple infolog(s)
			'change' => array(
				'caption' => 'Change',
				'group' => ++$group,
				'icon' => 'edit',
				'disableClass' => 'rowNoEdit',
				'children' => array(
					'type' => array(
						'caption' => 'Type',
						'prefix' => 'type_',
						'children' => $types,
						'group' => $group,
						'icon' => 'task',
					),
					'status' => array(
						'caption' => 'Status',
						'prefix' => 'status_',
						'children' => $statis,
						'group' => $group,
						'icon' => 'ongoing',
					),
					'completion' => array(
						'caption' => 'Completed',
						'prefix' => 'completion_',
						'children' => $percent,
						'group' => $group,
						'icon' => 'completed',
					),
					'cat' =>  nextmatch_widget::category_action(
						'infolog',$group,'Change category','cat_'
					),
					'responsible' => array(
						'caption' => 'Delegation',
						'group' => $group,
						'icon' => 'users',
						'nm_action' => 'open_popup',
					),
					'link' => array(
						'caption' => 'Links',
						'group' => $group,
						'nm_action' => 'open_popup',
					),
				),
			),
			'close' => array(
				'caption' => 'Close',
				'icon' => 'done',
				'group' => $group,
				'disableClass' => 'rowNoClose',
			),
			'close_all' => array(
				'caption' => 'Close all',
				'icon' => 'done_all',
				'group' => $group,
				'hint' => 'Sets the status of this entry and its subs to done',
				'allowOnMultiple' => false,
				'disableClass' => 'rowNoCloseAll',
			),
		);
		++$group;	// integration with other apps
		if ($GLOBALS['egw_info']['user']['apps']['filemanager'])
		{
			$actions['filemanager'] = array(
				'icon' => 'filemanager/navbar',
				'caption' => 'Filemanager',
				'url' => 'menuaction=filemanager.filemanager_ui.index&path=/apps/infolog/$id&ajax=true',
				'allowOnMultiple' => false,
				'group' => $group,
			);
		}
		if ($GLOBALS['egw_info']['user']['apps']['calendar'])
		{
			$actions['calendar'] = array(	// interactive add for a single event
				'icon' => 'calendar/navbar',
				'caption' => 'Schedule appointment',
				'group' => $group,
				'url' => 'menuaction=calendar.calendar_uiforms.edit&'.
					egw_link::get_registry('calendar', 'add_app') . '[]=infolog&'.egw_link::get_registry('calendar','add_id').'[]=$id',
				'allowOnMultiple' => false,
				'popup' => egw_link::get_registry('calendar', 'add_popup'),
			);
		}
		if ($GLOBALS['egw_info']['user']['apps']['timesheet'])
		{
			$actions['timesheet'] = array(	// interactive add for a single event
				'icon' => 'timesheet/navbar',
				'caption' => 'Timesheet',
				'url' => 'menuaction=timesheet.timesheet_ui.edit&link_app[]=infolog&link_id[]=$id',
				'group' => $group,
				'allowOnMultiple' => false,
				'popup' => egw_link::get_registry('timesheet', 'add_popup'),
			);
		}
		if ($GLOBALS['egw_info']['user']['apps']['tracker'])
		{
			$actions['tracker'] = array(
				'icon' => 'tracker/navbar',
				'caption' => 'Tracker',
				'hint' => 'Convert to a ticket',
				'group' => $group,
				'url' => 'menuaction=tracker.tracker_ui.edit&'.
					egw_link::get_registry('tracker', 'add_app') . '[]=infolog&'.egw_link::get_registry('tracker','add_id').'[]=$id',
				'allowOnMultiple' => false,
				'popup' => egw_link::get_registry('tracker', 'add_popup'),
			);
		}

		$actions['documents'] = infolog_merge::document_action(
			$this->prefs['document_dir'], ++$group, 'Insert in document', 'document_',
			$this->prefs['default_document']
		);
		$actions['ical'] = array(
			'icon' => 'ical',
			'caption' => 'Export iCal',
			'postSubmit' => true,	// download needs post submit to work
			'group' => $group,
			'allowOnMultiple' => true,
		);

		$actions['delete'] = array(
			'caption' => 'Delete',
			'group' => ++$group,
			'disableClass' => 'rowNoDelete',
			'onExecute' => 'javaScript:app.infolog.confirm_delete',
		);
		if ($query['col_filter']['info_status'] == 'deleted')
		{
			$actions['undelete'] = array(
				'caption' => 'Un-Delete',
				'group' => $group,
				'icon' => 'revert',
				'disableClass' => 'rowNoUndelete',
			);
		}

		//echo "<p>".__METHOD__."($do_email, $tid_filter, $org_view)</p>\n"; _debug_array($actions);
		return $actions;
	}

	/**
	 * Handles actions on multiple infologs
	 *
	 * @param action
	 * @param array $checked contact id's to use if !$use_all
	 * @param boolean $use_all if true use all entries of the current selection (in the session)
	 * @param int &$success number of succeded actions
	 * @param int &$failed number of failed actions (not enought permissions)
	 * @param string &$action_msg translated verb for the actions, to be used in a message like '%1 entries deleted'
	 * @param array $query get_rows parameter
	 * @param string &$msg on return user feedback
	 * @param boolean $skip_notifications=false true to NOT notify users about changes
	 * @return boolean true if all actions succeded, false otherwise
	 */
	function action($action, $checked, $use_all, &$success, &$failed, &$action_msg,
		array $query, &$msg, $skip_notifications = false)
	{
		//echo '<p>'.__METHOD__."('$action',".array2string($checked).','.(int)$use_all.",...)</p>\n";
		$success = $failed = 0;
		if ($use_all)
		{
			@set_time_limit(0);                     // switch off the execution time limit, as it's for big selections to small
			$query['num_rows'] = -1;        // all
			$result = $readonlys = null;
			$this->get_rows($query,$result,$readonlys);
			$checked = array();
			foreach($result as $key => $info)
			{
				if(is_numeric($key))
				{
					$checked[] = $info['info_id'];
				}
			}
		}

		// Actions with options in the selectbox
		list($action, $settings) = explode('_', $action, 2);

		// Actions that can handle a list of IDs
		switch($action)
		{
			case 'link':
				list($add_remove, $link) = explode('_', $settings, 2);
				list($app, $link_id) = explode(strpos($link,':') !== false ? ':' : ',', $link);
				if(!$link_id)
				{
					$action_msg = 'linked';
					$msg = lang('You need to select an entry for linking.');
					break;
				}
				$title = egw_link::title($app, $link_id);
				foreach($checked as $id)
				{
					if(!$this->bo->check_access($id, EGW_ACL_EDIT))
					{
						$failed++;
						continue;
					}
					if($add_remove == 'add')
					{
						$action_msg = lang('linked to %1', $title);
						if(egw_link::link('infolog', $id, $app, $link_id))
						{
							$success++;
						}
						else
						{
							$failed++;
						}
					}
					else
					{
						$action_msg = lang('unlinked from %1', $title);
						$count = egw_link::unlink(0, 'infolog', $id, '', $app, $link_id);
						$success += $count;
					}
				}
				return $failed == 0;

			case 'document':
				if (!$settings) $settings = $this->prefs['default_document'];
				$document_merge = new infolog_merge();
				$msg = $document_merge->download($settings, $checked, '', $this->prefs['document_dir']);
				$failed = count($checked);
				return false;

			case 'parent':
				$parent_query = array('col_filter' => array('info_id' => $checked));
				$result = $this->bo->search($parent_query);
				$parents = array();
				foreach($result as $key => $info)
				{
					if(is_numeric($key))
					{
						$parents[] = $info['info_id_parent'];
					}
				}
				$checked = array_unique($parents);
				// Fall through

			case 'view':
				// remember filter to restore it, if infolog icon get's clicked next time
				if ($query['filter']) egw_cache::setSession('infolog', 'filter_reset_from', $query['filter']);
				$this->index(array(),'sp',$checked,0);
				common::egw_exit();
			case 'ical':
				// infolog_ical lets horde be auto-loaded, so it must go first
				$boical = new infolog_ical();
				html::content_header('todo.ics','text/calendar');
				echo $boical->exportvCalendar($checked);
				common::egw_exit();

		}

		// Actions that need to loop
		foreach($checked as $id)
		{
			if(!$entry = $this->bo->read($id))
			{
				continue;
			}
			switch($action)
			{
				case 'close':
					$action_msg = lang('closed');
					$this->close($id, '', false, $skip_notifications);
					$success++;
					break;

				case 'delete':
					$action_msg = $settings == 'sub' ? lang(' (and children) deleted') : lang('deleted');
					$result = $this->bo->delete($id, $settings=='sub', false, $skip_notifications);
					if($result == true)
					{
						$success++;
					}
					else
					{
						$failed++;
					}
					break;

				case 'type':
					$action_msg = lang('changed type');
					// Dont allow to change the type, if user has no delete rights from the group-owner
					if ($id && !($this->bo->grants[$entry['info_owner']] & EGW_ACL_DELETE))
					{
						$failed++;
						break;
					}
					$entry['info_type'] = $settings;
					try {
						$this->bo->write($entry, true,true,true,$skip_notifications,true); // Throw exceptions
					}
					catch (egw_exception_wrong_userinput $e)
					{
						$msg .= "\n".$e->getMessage();
						$failed++;
						break;
					}
					$success++;
					break;

				case 'completion':
					$action_msg = lang('changed completion to %1%', $settings);
					$entry['info_percent'] = $settings;
					// Done entries will get changed right back if we don't change the status too
					if($entry['info_status'] == 'done')
					{
						$entry['info_status'] = 'ongoing';
					}
					if($this->bo->write($entry, true,true,true,$skip_notifications))
					{
						$success++;
					}
					else
					{
						$failed++;
					}
					break;

				case 'undelete':	// set it to valid status != 'deleted' for that type
					$settings = isset($this->bo->status[$entry['info_type']]['done']) ?
						$this->bo->status[$entry['info_type']]['done'] :
						$this->bo->status['defaults'][$entry['info_type']];
					// fall-through
				case 'status':
					if(isset($this->bo->status[$entry['info_type']][$settings]))
					{
						$action_msg = lang('changed status to %1', lang($this->bo->status[$entry['info_type']][$settings]));
						if($settings != 'done' && $entry['info_status'] == 'done' && $entry['info_percent'] == 100)
						{
							// Done entries will get changed right back if we don't change the completion too
							$entry['info_percent'] = 99;
						}
						$entry['info_status'] = $settings;
						if($this->bo->write($entry, true,true,true,$skip_notifications))
						{
							$success++;
						}
					}
					else
					{
						$msg .= lang('Invalid status for entry type %1.', lang($this->bo->enums['type'][$entry['info_type']]));
						$failed++;
					}
					break;

				case 'cat':
					if($settings)
					{
						$cat_name = categories::id2name($settings);
						$action_msg = lang('changed category to %1', $cat_name);
					}
					else
					{
						$action_msg = lang('removed category');
					}
					$entry['info_cat'] = $settings;
					if($this->bo->write($entry, true,true,true,$skip_notifications))
					{
						$success++;
					}
					else
					{
						$failed++;
					}
					break;

				case 'responsible':
					list($add_remove, $user_str) = explode('_', $settings, 2);
					$action_msg = ($add_remove == 'add' ? lang('added') : lang('removed')) . ' ';
					$names = array();
					$users = explode(',', $user_str);
					foreach($users as $account_id)
					{
						$names[] = common::grab_owner_name($account_id);
					}
					$action_msg .= implode(', ', $names);
					$function = $add_remove == 'add' ? 'array_merge' : 'array_diff';
					$entry['info_responsible'] = array_unique($function($entry['info_responsible'], (array)$users));
					if($this->bo->write($entry, true,true,true,$skip_notifications))
					{
						$success++;
					}
					else
					{
						$failed++;
					}
					break;
			}
		}
		return $failed == 0;
	}

	/**
	 * Closes an infolog
	 *
	 * @param int|array $values=0 info_id (default _GET[info_id])
	 * @param string $referer=''
	 * @param boolean $closesingle=false
	 */
	function close($values=0,$referer='',$closesingle=false,$skip_notification = false)
	{
		//echo "<p>".__METHOD__."($values,$referer,$closeall)</p>\n";
		$info_id = (int) (is_array($values) ? $values['info_id'] : ($values ? $values : $_GET['info_id']));
		$referer = is_array($values) ? $values['referer'] : $referer;

		if ($info_id)
		{
			$info = $this->bo->read($info_id);
			#_debug_array($info);
			$status = $info['info_status'];
			// closed stati assumed array('done','billed','cancelled')
			if (isset($this->bo->status[$info['info_type']]['done'])) {
				$status ='done';
			} elseif (isset($this->bo->status[$info['info_type']]['billed'])) {
				$status ='billed';
			} elseif (isset($this->bo->status[$info['info_type']]['cancelled'])) {
				$status ='cancelled';
			}
			#_debug_array($status);
			$values = array(
				'info_id'     => $info_id,
				'info_type'   => $info['info_type'],
				'info_status' => $status,
				'info_percent'=> 100,
				'info_datecompleted' => $this->bo->now_su,
			);
			$this->bo->write($values, true,true,true,$skip_notification);

			$query = array('action'=>'sp','action_id'=>$info_id);
			if (!$closesingle) {
				foreach((array)$this->bo->search($query) as $info)
				{
					if ($info['info_id_parent'] == $info_id)	// search also returns linked entries!
					{
						$this->close($info['info_id'],$referer,$closesingle,$skip_notification);	// we call ourselfs recursive to process subs from subs too
					}
				}
			}
		}
		if ($referer) $this->tmpl->location($referer);
	}

	/**
	 * Deletes an InfoLog entry
	 *
	 * @param array|int $values=0 info_id (default _GET[info_id])
	 * @param string $referer=''
	 * @param string $called_by=''
	 * @param boolean $skip_notification Do not send notification of deletion
	 */
	function delete($values=0,$referer='',$called_by='',$skip_notification=False)
	{
		$info_id = (int) (is_array($values) ? $values['info_id'] : ($values ? $values : $_GET['info_id']));
		$referer = is_array($values) ? $values['referer'] : $referer;

		if (!is_array($values) && $info_id > 0 && !$this->bo->anzSubs($info_id))	// entries without subs get confirmed by javascript
		{
			$values = array('delete' => true);
		}
		//echo "<p>infolog_ui::delete(".print_r($values,true).",'$referer','$called_by') info_id=$info_id</p>\n";

		if (is_array($values) || $info_id <= 0)
		{
			if (($values['delete'] || $values['delete_subs']) && $info_id > 0 && $this->bo->check_access($info_id,EGW_ACL_DELETE))
			{
				$deleted = $this->bo->delete($info_id,$values['delete_subs'],$values['info_id_parent'], $skip_notification);
			}
			if ($called_by)		// direct call from the same request
			{
				return $deleted ? lang('InfoLog entry deleted') : '';
			}
			if ($values['called_by'] == 'edit')	// we run in the edit popup => give control back to edit
			{
				$this->edit(array(
					'info_id' => $info_id,
					'button'  => array('deleted' => true),	// not delete!
					'referer' => $referer,
					'msg'     => $deleted ? lang('Infolog entry deleted') : '',
				));
			}
			return $referer ? $this->tmpl->location($referer) : $this->index();
		}
		$readonlys = $values = array();
		$values['main'][1] = $this->get_info($info_id,$readonlys['main']);

		$this->tmpl->read('infolog.delete');

		$values['nm'] = array(
			'action'         => 'sp',
			'action_id'      => $info_id,
			'options-filter' => $this->filters,
			'get_rows'       => 'infolog.infolog_ui.get_rows',
			'no_filter2'     => True
		);
		$values['main']['no_actions'] = $values['nm']['no_actions'] = True;

		$persist['info_id'] = $info_id;
		$persist['referer'] = $referer;
		$persist['info_id_parent'] = $values['main'][1]['info_id_parent'];
		$persist['called_by'] = $called_by;

		$GLOBALS['egw_info']['flags']['app_header'] = lang('InfoLog').' - '.lang('Delete');
		$GLOBALS['egw_info']['flags']['params']['manual'] = array('page' => 'ManualInfologDelete');

		$this->tmpl->exec('infolog.infolog_ui.delete',$values,array(),$readonlys,$persist,$called_by == 'edit' ? 2 : 0);
	}

	/**
	 * Edit/Create an InfoLog Entry
	 *
	 * @param array $content=null Content from the eTemplate Exec call or info_id on inital call
	 * @param string $action='' Name of an app of 'sp' for a infolog-sub
	 * @param int $action_id=0 Id of app-entry to which a link is created
	 * @param string $type='' Type of log-entry: note,todo,task
	 * @param string $referer='' array with param/get-vars of the refering page
	 */
	function edit($content = null,$action = '',$action_id=0,$type='',$referer='')
	{
		if (($submit = is_array($content)))
		{
			//echo "infolog_ui::edit: content="; _debug_array($content);
			$info_id   = $content['info_id'];
			$action    = $content['action'];    unset($content['action']);
			$action_id = $content['action_id']; unset($content['action_id']);
			$referer   = $content['referer'];   unset($content['referer']);
			$no_popup  = $content['no_popup'];  unset($content['no_popup']);

			list($button) = @each($content['button']);
			if (!$button && $action) $button = $action;	// action selectbox
			unset($content['button']);
			if ($button)
			{
				// Copy or schedule Infolog
				if (in_array($button,array('copy','schedule','ical','tracker')))
				{
					$action = $button;
					if (!$info_id || $this->bo->check_access($info_id,EGW_ACL_EDIT))
					{
						$button = 'apply';	// need to store infolog first
					}
				}
				//Validate the enddate must be grather than startdate
				if (isset($content['info_enddate']) && isset($content['info_startdate']))
				{
					$duration_date = $content['info_enddate']-$content['info_startdate'];
					if (isset($duration_date) && $duration_date < 0 )
					{
						$this->tmpl->set_validation_error('info_startdate', lang('Startdate must be before Enddate!!!'));
						$button = $action = '';	// stop save or apply
					}
				}
				//echo "<p>infolog_ui::edit(info_id=$info_id) '$button' button pressed, content="; _debug_array($content);
				if (($button == 'save' || $button == 'apply') && isset($content['info_subject']) && empty($content['info_subject']))
				{
					$this->tmpl->set_validation_error('info_subject',lang('Field must not be empty !!!'));
					$button = $action = '';	// stop save or apply
				}
				if (($button == 'save' || $button == 'apply') && $info_id)
				{
					if (!($edit_acl = $this->bo->check_access($info_id,EGW_ACL_EDIT)))
					{
						$old = $this->bo->read($info_id);
						$status_only = $this->bo->is_responsible($old);
						$undelete = $this->bo->check_access($old,EGW_ACL_UNDELETE);
					}
				}
				if (($button == 'save' || $button == 'apply') && (!$info_id || $edit_acl || $status_only || $undelete))
				{
					$operation = $info_id ? 'update' : 'add';
					if ($content['info_contact'])
					{
						$old_link_id = (int)$content['info_link_id'];
						if(is_array($content['info_contact']))
						{
							// eTemplate2 returns the array all ready
							$app = $content['info_contact']['app'];
							$id = $content['info_contact']['id'];
						}
						if($app && $id)
						{
							if(!is_array($content['link_to']))
							{
								$content['link_to'] = array();
							}
							$content['info_link_id'] = (int)($info_link_id = egw_link::link('infolog',$content['link_to']['to_id'],$app,$id));
						}
						if ($old_link_id && $old_link_id != $content['info_link_id']) egw_link::unlink($old_link_id);
					}
					if (is_array($content['link_to']['to_id']) && count($content['link_to']['to_id']))
					{
						$content['info_link_id'] = 0;	// as field has to be int
					}
					$active_tab = $content['tabs'];
					if (!($info_id = $this->bo->write($content, true, true, true, $content['no_notifications'])))
					{
						$content['msg'] = $info_id !== 0 || !$content['info_id'] ? lang('Error: saving the entry') :
							lang('Error: the entry has been updated since you opened it for editing!').'<br />'.
							lang('Copy your changes to the clipboard, %1reload the entry%2 and merge them.','<a href="'.
								htmlspecialchars(egw::link('/index.php',array(
									'menuaction' => 'infolog.infolog_ui.edit',
									'info_id'    => $content['info_id'],
									'no_popup'   => $no_popup,
									'referer'    => $referer,
								))).'">','</a>');
						$button = $action = '';	// not exiting edit
						$info_id = $content['info_id'];
					}
					else
					{
						$content['msg'] = lang('InfoLog entry saved');
						egw_framework::refresh_opener($content['msg'],'infolog',$info_id,$operation);
					}
					$content['tabs'] = $active_tab;
					if ((int) $content['pm_id'] != (int) $content['old_pm_id'])
					{
						//echo "<p>pm_id changed: $content[old_pm_id] -> $content[pm_id]</p>\n";
						// update links accordingly, if selected project changed
						if ($content['pm_id'])
						{
							//echo "<p>this->link->link('infolog',{$content['link_to']['to_id']},'projectmanager',{$content['pm_id']});</p>";
							egw_link::link('infolog',$content['link_to']['to_id'],'projectmanager',$content['pm_id']);
							// making the project the selected link, if no other link selected
							if (!$info_link_id || $info_link_id == 'projectmanager:'.$content['old_pm_id'])
							{
								$info_link_id = 'projectmanager:'.$content['pm_id'];
							}
						}
						if ($content['old_pm_id'])
						{
							//echo "<p>this->link->unlink2(0,infolog,{$content['link_to']['to_id']},0,'projectmanager',{$content['old_pm_id']});</p>\n";
							egw_link::unlink2(0,infolog,$content['link_to']['to_id'],0,'projectmanager',$content['old_pm_id']);
							$content['old_pm_id'] = $content['pm_id'];
						}
					}
					// writing links for a new entry
					if ($info_id && is_array($content['link_to']['to_id']) && count($content['link_to']['to_id']))
					{
						//echo "<p>writing links for new entry $info_id</p>\n"; _debug_array($content['link_to']['to_id']);
						egw_link::link('infolog',$info_id,$content['link_to']['to_id']);
						$content['link_to']['to_id'] = $info_id;
					}
					if ($info_link_id && strpos($info_link_id,':') !== false)	// updating info_link_id if necessary
					{
						list($app,$id) = explode(':',$info_link_id);
						$link = egw_link::get_link('infolog',$info_id,$app,$id);
						if ((int) $content['info_link_id'] != (int) $link['link_id'])
						{
							$content['info_link_id'] = $link['link_id'];

							$to_write = array(
								'info_id'      => $content['info_id'],
								'info_link_id' => $content['info_link_id'],
								'info_from'    => $content['info_from'],
								'info_type'    => $content['info_type'],
								'info_owner'   => $content['info_owner'],
							);
							//echo "<p>updating info_link_id: ".print_r($to_write,true)."</p>\n";
							$this->bo->write($to_write,False,true,true,true);	// last true = no notifications, as no real change
							// we need eg. the new modification date, for further updates
							$content = array_merge($content,$to_write);
						}
					}
				}
				elseif ($button == 'delete' && $info_id > 0)
				{
					if (!$referer && $action) $referer = array(
						'menuaction' => 'infolog.infolog_ui.index',
						'action' => $action,
						'action_id' => $action_id
					);
					if (!($content['msg'] = $this->delete($info_id,$referer,'edit'))) return;	// checks ACL first

					egw_framework::refresh_opener($content['msg'],'infolog',$info_id,'delete');
				}
				// called again after delete confirmation dialog
				elseif ($button == 'deleted'  && $content['msg'])
				{
					egw_framework::refresh_opener($content['msg'],'infolog',$info_id,'delete');
				}
				if ($button == 'save' || $button == 'cancel' || $button == 'delete' || $button == 'deleted')
				{
					if ($no_popup)
					{
						egw::redirect_link($referer,array('msg' => $content['msg']));
					}
					egw_framework::window_close();
				}
			}
			// on a type-change, set the status to the default status of that type, if the actual status is not supported by the new type
			if (!array_key_exists($content['info_status'],$this->bo->status[$content['info_type']]))
			{
				$content['info_status'] = $this->bo->status['defaults'][$content['info_type']];
				if ($content['info_status'] != 'done') $content['info_datecompleted'] = '';
			}
		}
		else	// new call via GET
		{
			//echo "<p>infolog_ui::edit: info_id=$info_id,  action='$action', action_id='$action_id', type='$type', referer='$referer'</p>\n";
			$action    = $action    ? $action    : get_var('action',   array('POST','GET'));
			$action_id = $action_id ? $action_id : get_var('action_id',array('POST','GET'));
			$info_id   = $content   ? $content   : get_var('info_id',  array('POST','GET'));
			$type      = $type      ? $type      : get_var('type',     array('POST','GET'));
			$referer   = $referer !== '' ? $referer : ($_GET['referer'] ? $_GET['referer'] :
				common::get_referer('/index.php?menuaction=infolog.infolog_ui.index'));
			if (strpos($referer, 'msg=') !== false) $referer = preg_replace('/([&?]{1})msg=[^&]+&?/','\\1',$referer);	// remove previou/old msg from referer
			$no_popup  = $_GET['no_popup'];
			$print = (int) $_REQUEST['print'];
			//echo "<p>infolog_ui::edit: info_id=$info_id,  action='$action', action_id='$action_id', type='$type', referer='$referer'</p>\n";

			$content = $this->bo->read( $info_id || $action != 'sp' ? $info_id : $action_id );
			if (!(strpos($content['info_addr'],',')===false) && strpos($content['info_addr'],', ')===false) $content['info_addr'] = str_replace(',',', ',$content['info_addr']);
			foreach(array('info_subject', 'info_des') as $key)
			{
				if(!isset($content[$key]) || strlen($content[$key]) < 75)
				{
					continue;
				}
				$contlines = explode("\n", $content[$key]);
				$clarray = array();
				foreach ($contlines as &$line)
				{
					if(strlen($line) < 75)
					{
						$clarray[] = $line;
						continue;
					}
					$cont = explode(' ', $line);
					$ckarray = array();
					foreach($cont as &$word)
					{
						// set blank behind all , and . if words are too long, apply wordwrap afterwards to make sure we get
						if (strlen($word)>75)
						{
							$buff = html::activate_links($word);
							if (strlen($buff) == strlen($word)) // no links -> try to break overlong words
							{
								if (!(strpos($word,',')===false) && strpos($word,', ')===false) $word = str_replace(',',', ',$word);
								if (!(strpos($word,'.')===false) && strpos($word,'. ')===false) $word = str_replace('.','. ',$word);
								$word = wordwrap($word, 75, ' ', true);
							}
						}
						$ckarray[] =$word;
					}
					$line = join(' ',$ckarray);
					unset($ckarray);
					$clarray[] = $line;
				}
				$content[$key] = join("\n",$clarray);
				unset($clarray);
			}
			if (is_numeric($_REQUEST['cat_id']))
			{
				$content['info_cat'] = (int) $_REQUEST['cat_id'];
			}
			if (!$content)
			{
				$content['info_cat'] = $this->prefs['cat_add_default'];
			}
			if ($_GET['msg']) $content['msg'] = strip_tags($_GET['msg']);	// dont allow HTML!

			switch($this->prefs['set_start'])
			{
				case 'date': default: $set_startdate = mktime(0,0,0,date('m',$this->bo->user_time_now),date('d',$this->bo->user_time_now),date('Y',$this->bo->user_time_now)); break;
				case 'datetime':      $set_startdate = $this->bo->user_time_now; break;
				case 'empty':         $set_startdate = 0; break;
			}
			if ((int)$content['info_link_id'] > 0 && !egw_link::get_link($content['info_link_id']))
			{
				$content['info_link_id'] = 0;	// link has been deleted
				if (!$content['info_custom_link']) $content['info_from'] = '';
			}
			if (!$info_id && $action_id && $action == 'sp')    // new SubProject
			{
				if (!$this->bo->check_access($action_id,EGW_ACL_ADD))
				{
					return $referer ? $this->tmpl->location($referer) : $this->index(0,$action,$action_id);
				}
			}
			else
			{
				$undelete = $this->bo->check_access($info_id,EGW_ACL_UNDELETE);
			}
			$content['links'] = $content['link_to'] = array(
				'to_id' => $info_id,
				'to_app' => 'infolog',
			);
		}
		// new call via GET or some actions handled here, as they can happen both ways ($_GET[action] or button/action in GUI)
		if (!$submit || in_array($action,array('sp','copy','schedule','ical','tracker')))
		{
			switch ($action)
			{
				case 'schedule':
					egw::redirect_link('/index.php',array(
						'menuaction' => 'calendar.calendar_uiforms.edit',
						'link_app' => 'infolog',
						'link_id' => $info_id,
					));
					break;
				case 'ical':
					$boical = new infolog_ical();
					$result = $boical->exportVTODO($content,'2.0','PUBLISH',false);
					html::content_header('todo.ics', 'text/calendar');
					echo $result;
					common::egw_exit();
				case 'sp':
				case 'copy':
					$info_id = 0;
					$this->create_copy($content, $action == 'sp');
					if ($action == 'sp')	// for sub-entries use type or category, like for new entries
					{
						if ($type) $content['info_type'] = $type;
						if (is_numeric($_REQUEST['cat_id'])) $content['info_cat'] = (int) $_REQUEST['cat_id'];
					}
					unset($action);	// it get stored in $content and will cause an other copy after [apply]
					break;
				case 'tracker':
					egw::redirect_link('/index.php',array(
						'menuaction' => 'tracker.tracker_ui.edit',
						egw_link::get_registry('tracker', 'add_app').'[]' => 'infolog',
						egw_link::get_registry('tracker','add_id').'[]' => $info_id,
					));
					break;
				case 'projectmanager':
					$pm_links = array($action_id);
				default:	// to allow other apps to participate
					$content['info_subject'] = egw_link::title($action, $id);
					$content['info_contact'] = $action.':'.$action_id;
					foreach (explode(',', $action_id) as $n => $id)
					{
						egw_link::link('infolog', $content['link_to']['to_id'], $action, $id);

						// calling "infolog_set" hook for first, in case app wants to set some more values
						if (!$n && ($set = $GLOBALS['egw']->hooks->single(array('location'=>'infolog_set','id'=>$action_id),$action)))
						{
							foreach((array)$set['link_app'] as $i => $l_app)
							{
								if (($l_id=$set['link_id'][$i])) egw_link::link('infolog',$content['link_to']['to_id'],$l_app,$l_id);
							}
							unset($set['link_app']);
							unset($set['link_id']);

							$content = array_merge($content, $set);
						}
					}
					// fall through
				case '':
					if ($info_id)
					{
						if (!isset($pm_links))
						{
							$pm_links = egw_link::get_links('infolog',$info_id,'projectmanager');
						}
						break;	// normal edit
					}
				case 'new':		// new entry, set some defaults, if not set by infolog_set hook
					if (empty($content['info_startdate'])) $content['info_startdate'] = (int) $_GET['startdate'] ? (int) $_GET['startdate'] : $set_startdate;
					if (empty($content['info_priority'])) $content['info_priority'] = 1; // normal
					$content['info_owner'] = $this->user;
					if ($type != '' && empty($content['info_type']))
					{
						$content['info_type'] = $type;
					}
					if (empty($content['info_status'])) $content['info_status'] = $this->bo->status['defaults'][$content['info_type']];
					if (empty($content['info_percent'])) $content['info_percent'] = $content['info_status'] == 'done' ? '100%' : '0%';
					break;
			}
			if (!isset($this->bo->enums['type'][$content['info_type']]))
			{
				$content['info_type'] = 'note';
			}
		}
		// group owners
		$types = $this->bo->enums['type'];
		if ($this->bo->group_owners)
		{
			// remove types owned by groups the user has no edit grant (current type is made readonly)
			foreach($this->bo->group_owners as $type => $group)
			{
				if (!($this->bo->grants[$group] & EGW_ACL_EDIT))
				{
					if ($type == $content['info_type'])
					{
						//echo "<p>setting type to r/o as user has no edit rights from group #$group</p>\n";
						$readonlys['info_type'] = true;
					}
					else
					{
						unset($types[$type]);
					}
				}
			}
			// set group as owner if type has a group-owner set
			if (isset($this->bo->group_owners[$content['info_type']]))
			{
				$content['info_owner'] = $this->bo->group_owners[$content['info_type']];
				// Dont allow to change the type, if user has no delete rights from the group-owner
				if ($info_id && !($this->bo->grants[$content['info_owner']] & EGW_ACL_DELETE))
				{
					//echo "<p>setting type to r/o as user has no delete rights from group #$group</p>\n";
					$readonlys['info_type'] = true;
				}
				// disable info_access for group-owners
				$readonlys['info_access'] = true;
			}
			elseif($GLOBALS['egw']->accounts->get_type($content['info_owner']) == 'g')
			{
				$content['info_owner'] = $this->user;
			}
		}
		$preserv = $content;

		// Don't preserve message or links
		unset($preserv['msg']);
		unset($preserv['links']); unset($preserv['link_to']);

		// for no edit rights or implizit edit of responsible user make all fields readonly, but status and percent
		if ($info_id && !$this->bo->check_access($info_id,EGW_ACL_EDIT) && !$undelete)
		{
			$readonlys['__ALL__'] = true;	// make all fields not explicitly set readonly
			if ($this->bo->is_responsible($content))
			{
				foreach($this->bo->responsible_edit as $name)
				{
					$readonlys[$name] = false;
				}
				$readonlys['button[edit]'] = $readonlys['button[save]'] = $readonlys['button[apply]'] = $readonlys['no_notifications'] = false;
			}
			$readonlys['action'] = $readonlys['button[cancel]'] = false;	// always allowed
		}
		elseif (!$info_id)
		{
			$readonlys['action'] = true;
		}
		// ToDo: use the old status before the delete
		if ($undelete) $content['info_status'] = $this->bo->status['defaults'][$content['info_type']];


		if (!($readonlys['button[delete]'] = !$info_id || !$this->bo->check_access($info_id,EGW_ACL_DELETE)))
		{
			$content['info_anz_subs'] = $this->bo->anzSubs($info_id);	// to determine js confirmation of delete or not
		}
		$GLOBALS['egw_info']['flags']['app_header'] = lang($this->messages[$info_id ? 'edit' : ($action == 'sp' ? 'add_sub' : 'add')]);

		// use a typ-specific template (infolog.edit.xyz), if one exists, otherwise fall back to the generic one
		if (!$this->tmpl->read('infolog.edit.'.$content['info_type']))
		{
			$this->tmpl->read($print ? 'infolog.edit.print':'infolog.edit');
		}
		if ($this->bo->has_customfields($content['info_type']))
		{
			$content['customfields'] = $content['info_type'];
		}
		else
		{
			$readonlys['tabs']['customfields'] = true;
		}
		if (!isset($GLOBALS['egw_info']['user']['apps']['projectmanager']))
		{
			$readonlys['tabs']['project'] = true;	// disable the project tab
		}
		$readonlys['tabs']['delegation'] = $GLOBALS['egw_info']['user']['preferences']['common']['account_selection'] == 'none' &&
			!isset($GLOBALS['egw_info']['user']['apps']['admin']);

		$content['duration_format'] = $this->duration_format;
		$content['hours_per_workday'] = $this->hours_per_workday;
		if ($this->prefs['show_id']) $content['info_number'] = $info_id;

		$content['info_anz_subs'] = (int)$content['info_anz_subs'];	// gives javascript error if empty!

		$old_pm_id = is_array($pm_links) ? array_shift($pm_links) : $content['old_pm_id'];
		if (!isset($content['pm_id']) && $old_pm_id) $content['pm_id'] = $old_pm_id;

		if ($info_id && $this->bo->history)
		{
			$content['history'] = array(
				'id'  => $info_id,
				'app' => 'infolog',
				'status-widgets' => array(
					'Ty' => $types,
					//'Li',	// info_link_id
					'parent' => 'link-entry:infolog',
					'Ca' => 'select-cat',
					'Pr' => $this->bo->enums['priority'],
					'Ow' => 'select-account',
					//'Ac',	//	info_access: private||public
					'St' => $this->bo->status[$content['info_type']]+array('deleted' => 'deleted'),
					'Pe' => 'select-percent',
					'Co' => 'date-time',
					'st' => 'date-time',
					'Mo' => 'date-time',
					'En' => 'date',
					'Re' => 'select-account',
					// PM fields, ToDo: access control!!!
					'pT' => 'date-duration',
					'uT' => 'date-duration',
					'replanned' => 'date-duration',
//					'pL' => 'projectmanager-pricelist',
					'pr' => 'float',
				),
			);
			$history_stati = array();
			$tracking = new infolog_tracking($this);
			foreach($tracking->field2history as $field => $history)
			{
				$history_stati[$history] = $tracking->field2label[$field];
			}
			// Modified date removed from field2history, we don't need that in the history
			$history_stati['Mo'] = $tracking->field2label['info_datemodified'];
			unset($tracking);
		}
		else
		{
			$readonlys['tabs']['history'] = true;
		}
		$sel_options = array(
			'info_type'     => $types,
			'info_priority' => $this->bo->enums['priority'],
			'info_confirm'  => $this->bo->enums['confirm'],
			'info_status'   => $this->bo->status[$content['info_type']],
			'status'        => $history_stati,
			'action'        => array(
				'copy'  => array('label' => 'Copy', 'title' => 'Copy this Infolog'),
				'sp'    => 'Sub-entry',
				'print' => array('label' => 'Print', 'title' => 'Print this Infolog'),
				'ical' => array('label' => 'Export iCal', 'title' => 'Export iCal'),
				'tracker' => array('label' => 'Tracker', 'title' => 'Convert to a ticket'),
			),
		);
		if ($GLOBALS['egw_info']['user']['apps']['calendar'])
		{
			$sel_options['action']['schedule'] = array('label' => 'Schedule', 'title' => 'Schedule appointment');
		}
		egw_framework::validate_file('.','edit','infolog');
		$GLOBALS['egw_info']['flags']['app_header'] = lang('InfoLog').' - '.
			($content['status_only'] ? lang('Edit Status') : lang('Edit'));
		$GLOBALS['egw_info']['flags']['params']['manual'] = array('page' => ($info_id ? 'ManualInfologEdit' : 'ManualInfologAdd'));
		//error_log(substr($content['info_des'],1793,10));
		//$content['info_des'] = substr($content['info_des'],0,1793);
		//echo "<p>infolog_ui.edit(info_id='$info_id',action='$action',action_id='$action_id') readonlys="; print_r($readonlys); echo ", content = "; _debug_array($content);
		$this->tmpl->exec('infolog.infolog_ui.edit',$content,$sel_options,$readonlys,$preserv+array(	// preserved values
			'info_id'       => $info_id,
			'action'        => $action,
			'action_id'     => $action_id,
			'referer'       => $referer,
			'no_popup'      => $no_popup,
			'old_pm_id'     => $old_pm_id,
		),$no_popup ? 0 : 2);
	}

	/**
	 * Create copy or sub-entry from an entry currently read into $content
	 *
	 * Taking into account prefs and config about what to copy
	 *
	 * @param array &$content
	 * @param boolean $create_sub=false true: create a sub-entry instead of a copy, default false to create a copy
	 */
	private function create_copy(array &$content, $create_sub=false)
	{
		$info_id = $content['info_id'];	// it will be unset by exclude-fields

		// empty fields configured to be excluded (also contains id, uid, ...)
		$exclude_fields = $create_sub ? $this->bo->sub_excludefields : $this->bo->copy_excludefields;
		foreach ($exclude_fields as $field)
		{
			unset($content[$field]);
			if ($field == 'info_from') unset($content['info_link_id']);	// both together is called contact in UI
		}
		if ($create_sub)
		{
			$content['info_id_parent'] = $info_id;
		}
		// no startdate or startdate in the past --> set startdate from pref
		if (!isset($content['info_startdate']) || $content['info_startdate'] < $this->bo->user_time_now)
		{
			switch($this->prefs['set_start'])
			{
				case 'date': default: $set_startdate = mktime(0,0,0,date('m',$this->bo->user_time_now),date('d',$this->bo->user_time_now),date('Y',$this->bo->user_time_now)); break;
				case 'datetime':      $set_startdate = $this->bo->user_time_now; break;
				case 'empty':         $set_startdate = 0; break;
			}
			$content['info_startdate'] = $set_startdate;
		}
		// enddate in the past --> uset it
		if (isset($content['info_enddate']) || $content['info_enddate'] < $this->bo->user_time_now)
		{
			unset($content['info_enddate']);
		}
		if (!isset($content['info_type']))
		{
			$types = array_keys($this->get_validtypes());
			$content['info_type'] = $types[0];
		}
		// get a consistent status, percent and date-completed
		if (!isset($content['info_status'])) $content['info_status'] = $this->bo->status['defaults'][$content['info_type']];
		if (!isset($content['info_percent'])) $content['info_percent'] = $content['info_status'] == 'done' ? '100%' : '0%';
		$content['info_datecompleted'] =$content['info_status'] == 'done' ? $this->bo->user_time_now : 0;

		if (!isset($content['info_cat'])) $content['info_cat'] = $this->prefs['cat_add_default'];

		if(!is_array($content['link_to'])) $content['link_to'] = array();
		$content['link_to']['to_app'] = 'infolog';
		$content['link_to']['to_id'] = 0;
		// Get links to be copied, if not excluded
		if (!in_array('link_to',$exclude_fields) || !in_array('attachments',$exclude_fields))
		{
			foreach(egw_link::get_links($content['link_to']['to_app'], $info_id) as $link)
			{
				if ($link['app'] != egw_link::VFS_APPNAME && !in_array('link_to', $exclude_fields))
				{
					egw_link::link('infolog', $content['link_to']['to_id'], $link['app'], $link['id'], $link['remark']);
				}
				elseif ($link['app'] == egw_link::VFS_APPNAME && !in_array('attachments', $exclude_fields))
				{
					egw_link::link('infolog', $content['link_to']['to_id'], egw_link::VFS_APPNAME, array(
						'tmp_name' => egw_link::vfs_path($link['app2'], $link['id2']).'/'.$link['id'],
						'name' => $link['id'],
					), $link['remark']);
				}
			}
		}
		$content['links'] = $content['link_to'];

		if ($content['info_link_id'])
		{
			$info_link_id = $content['info_link_id'];
			// we need this if copy is triggered via context menu action
			if (!isset($content['info_contact']) || empty($content['info_contact']) || $content['info_contact'] === 'copy:')
			{
				$linkinfos = egw_link::get_link($info_link_id);
				$content['info_contact'] = $linkinfos['link_app1']=='infolog'? $linkinfos['link_app2'].':'.$linkinfos['link_id2']:$linkinfos['link_app1'].':'.$linkinfos['link_id1'];
				if (stripos($content['info_contact'],'projectmanager')!==false) $content['pm_id'] = $linkinfos['link_app1']=='projectmanager'? $linkinfos['link_id1']:$linkinfos['link_id2'];
			}
			unset($content['info_link_id']);
		}
		$content['info_owner'] = !(int)$this->owner || !$this->bo->check_perms(EGW_ACL_ADD,0,$this->owner) ? $this->user : $this->owner;

		if (!empty($content['info_subject']))
		{
			if ($create_sub)
			{
				$config = config::read('infolog');
				$prefix = lang(empty($config['sub_prefix']) ? 'Re:': $config['sub_prefix']);
			}
			else
			{
				$prefix = lang('Copy of:');
			}
			$content['info_subject'] = $prefix.' '.$content['info_subject'];
		}
		if (!$create_sub)
		{
			$content['msg'] .= ($content['msg']?"\n":'').lang('Infolog copied - the copy can now be edited');
		}
	}

	function icon($cat,$id,$status='')
	{
		if (!$status || !($icon = $this->icons[$cat][$id.'_'.$status]))
		{
			$icon = $this->icons[$cat][$id];
		}
		if ($icon && !is_readable(common::get_image_dir() . '/' . $icon))
		{
			$icon = False;
		}
		if (!$status || !($alt = $this->icons[$cat][$id.'_'.$status.'_alt']))
		{
			if (!($alt = $this->icons[$cat][$id.'_alt']))
			{
				$alt = $id;
			}
		}
		return $icon ? html::image('infolog',$icon,lang($alt),'border=0') : lang($alt);
	}

	/**
	 * stripping slashes from an array
	 *
	 * @static
	 * @param array $arr
	 * @return array
	 */
	function array_stripslashes($arr)
	{
		foreach($arr as $key => $val)
		{
			if (is_array($val))
			{
				$arr[$key] = self::array_stripslashes($val);
			}
			else
			{
				$arr[$key] = stripslashes($val);
			}
		}
		return $arr;
	}

	/**
	 * Infolog's site configuration
	 *
	 */
	public function admin($content = array())
	{
		$fields = array(
			'info_cat'      => 'Category',
			'info_from'     => 'Contact',
			'info_addr'     => 'Phone/Email',
			'info_subject'  => 'Subject',
			'info_des'      => 'Description',
			'link_to'       => 'Links',
			'info_priority' => 'Priority',
			'info_location' => 'Location',
			'info_planned_time' => 'Planned time',
			'info_used_time'    => 'Used time',
		);
		$excludefields = array(
			'info_cat'      => 'Category',
			'info_from'     => 'Contact',
			'info_addr'     => 'Phone/Email',
			'info_subject'  => 'Subject',
			'info_des'      => 'Description',
			'link_to'       => 'Links',
			'attachments'   => 'Attachments',
			'info_priority' => 'Priority',
			'info_location' => 'Location',
			'info_planned_time' => 'Planned time',
			'info_used_time'    => 'Used time',
			'info_type' => 'Type',
			'info_owner' => 'Owner',
			'info_responsible' => 'Responsible',
			'info_access' => 'Access',
			'info_startdate' => 'Startdate',
			'info_enddate' => 'Enddate',
			'info_id_parent' => 'Parent',
			'info_status' => 'Status',
			'info_confirm' => 'Confirm',
			'pl_id' => 'pricelist',
			'info_price' => 'price',
			'info_percent' => 'completed',
			'info_datecompleted' => 'date completed',
			'info_replanned_time' => 're-planned time',
			'info_cc' => 'CC',
		);
		// add customfields to field list
		foreach(config::get_customfields('infolog') as $name => $data)
		{
			$excludefields['#'.$name] = $data['label'];
		}
		$sub_excludefields = $excludefields;
		unset($sub_excludefields['info_id_parent']);	// always set to parent!

		$config = config::read('infolog');

		if($content)
		{
			// Save
			$button = key($content['button']);
			if($button == 'save' || $button == 'apply')
			{
				$this->bo->responsible_edit = array('info_status','info_percent','info_datecompleted');

				if ($content['responsible_edit'])
				{
					$extra = array_intersect($content['responsible_edit'],array_keys($fields));
					$this->bo->responsible_edit = array_unique(array_merge($this->bo->responsible_edit,$extra));
				}
				config::save_value('copy_excludefields', $content['copy_excludefields'] ? $content['copy_excludefields'] : null, 'infolog');
				config::save_value('sub_excludefields', $content['sub_excludefields'] ? $content['sub_excludefields'] : array('*NONE*'), 'infolog');
				config::save_value('responsible_edit', $this->bo->responsible_edit, 'infolog');
				config::save_value('implicit_rights', $this->bo->implicit_rights = $content['implicit_rights'] == 'edit' ? 'edit' : 'read', 'infolog');
				config::save_value('history', $this->bo->history = $content['history'], 'infolog');
				config::save_value('index_load_cfs', implode(',', (array)$content['index_load_cfs']), 'infolog');
				config::save_value('sub_prefix', $content['sub_prefix'], 'infolog');

				// Notifications
				$notifications =& $config[infolog_tracking::CUSTOM_NOTIFICATION];
				$notifications[$content['notification_type']] = $content['notification'];
				config::save_value(infolog_tracking::CUSTOM_NOTIFICATION, $notifications,'infolog');
			}

			if($button == 'save' || $button == 'cancel')
			{
				egw::redirect_link('/infolog/index.php');
			}
		}
		else
		{
			// Load
			$content = $config;

			$content['implicit_rights'] = $this->bo->implicit_rights;
			$content['responsible_edit'] = $this->bo->responsible_edit;
			$content['copy_excludefields'] = $this->bo->copy_excludefields;
			$content['sub_excludefields'] = $this->bo->sub_excludefields;
			$content['history'] = $this->bo->history;
		}

		$GLOBALS['egw_info']['flags']['app_header'] = lang('InfoLog').' - '.lang('Site configuration');

		// Load selected custom notification
		if(!$content['notification_type'])
		{
			$content['notification_type'] = '~global~';
		}
		$content['notification'] = $config[infolog_tracking::CUSTOM_NOTIFICATION][$content['notification_type']];
		$sel_options = array(
			'implicit_rights' => array(
				'read' => 'read rights (default)',
				'edit' => 'edit rights (full edit rights incl. making someone else responsible!)',
			),
			'responsible_edit' => $fields,
			'copy_excludefields' => $excludefields,
			'sub_excludefields' => $sub_excludefields,
			'history'     => array(
				'' => lang('No'),
				'history' => lang('Yes, with purging of deleted items possible'),
				'history_admin_delete' => lang('Yes, only admins can purge deleted items'),
				'history_no_delete' => lang('Yes, noone can purge deleted items'),
			),
			'index_load_cfs' => $this->bo->enums['type'],
			'notification_type' => array('~global~' => 'all') + $this->bo->enums['type']
		);
		$preserve['notification_old_type'] = $content['notification_type'];
		$this->tmpl->read('infolog.config');
		$this->tmpl->exec('infolog.infolog_ui.admin',$content,$sel_options,array(),$preserve);
	}

	/**
	 * imports a mail as infolog
	 * two possible calls:
	 * 1. with function args set. (we come from send mail)
	 * 2. with $_GET['uid] = someuid (we come from display mail)
	 *
	 * @author Cornelius Weiss <nelius@cwtech.de>
	 * @param string $_to_emailAddress
	 * @param string $_subject
	 * @param string $_body
	 * @param array $_attachments
	 * @param string $_date
	 * @param string $_rawMailHeader
	 * @param string $_rawMailBody
	 */
	function import_mail($_to_emailAddress=false,$_subject=false,$_body=false,$_attachments=false,$_date=false,$_rawMailHeader=null,$_rawMailBody=null)
	{
		$uid = $_GET['uid'];
		$partid = $_GET['part'];
		$mailbox = base64_decode($_GET['mailbox']);
		$mailClass = 'felamimail_bo';
		$sessionLocation = 'felamimail';
		// if rowid is set, we are called from new mail module.
		if (method_exists('mail_ui','splitRowID') && isset($_GET['rowid']) && !empty($_GET['rowid']))
		{
			// rowid holds all needed information: server, folder, uid, etc.
			$rowID = $_GET['rowid'];
			$hA = mail_ui::splitRowID($rowID);
			$sessionLocation = $hA['app']; // THIS is part of the row ID, we may use this for validation
			if ($sessionLocation != 'mail') throw new egw_exception_assertion_failed(lang('Application mail expected but got: %1',$sessionLocation));
			$uid = $hA['msgUID'];
			$mailbox = $hA['folder'];
			$icServerID = $hA['profileID'];
			$mailClass = 'mail_bo';
		}
		if ($_date == false || empty($_date)) $_date = $this->bo->user_time_now;
		if (!empty($_to_emailAddress))
		{
			$GLOBALS['egw_info']['flags']['currentapp'] = 'infolog';

			if (!($GLOBALS['egw_info']['user']['preferences'][$sessionLocation]['saveAsOptions']==='text_only')&&is_array($_attachments))
			{
				//echo __METHOD__.'<br>';
				//_debug_array($_attachments);
				if (!isset($icServerID)) $icServerID =& egw_cache::getSession($sessionLocation,'activeProfileID');
				$mailobject = $mailClass::getInstance(true,$icServerID);
				$mailobject->openConnection();
				foreach ($_attachments as $attachment)
				{
					//error_log(__METHOD__.__LINE__.array2string($attachment));
					if (trim(strtoupper($attachment['type'])) == 'MESSAGE/RFC822' && !empty($attachment['uid']) && !empty($attachment['folder']))
					{
						$mailobject->reopen(($attachment['folder']?$attachment['folder']:$mailbox));

						// get the message itself, and attach it, as we are able to display it in egw
						// instead of fetching only the attachments attached files (as we did previously)
						$message = $mailobject->getMessageRawBody($attachment['uid'],$attachment['partID'],($attachment['folder']?$attachment['folder']:$mailbox));
						$headers = $mailobject->getMessageHeader($attachment['uid'],$attachment['partID'],true,false,($attachment['folder']?$attachment['folder']:$mailbox));
						$subject = str_replace('$$','__',($headers['SUBJECT']?$headers['SUBJECT']:lang('(no subject)')));
						$attachment_file =tempnam($GLOBALS['egw_info']['server']['temp_dir'],$GLOBALS['egw_info']['flags']['currentapp']."_");
						$tmpfile = fopen($attachment_file,'w');
						fwrite($tmpfile,$message);
						fclose($tmpfile);
						$size = filesize($attachment_file);
						$attachments[] = array(
								'name' => trim($subject).'.eml',
								'mimeType' => 'message/rfc822',
								'tmp_name' => $attachment_file,
								'size' => $size,
							);
					}
					else
					{
						if (!empty($attachment['folder']))
						{
							$is_winmail = $_GET['is_winmail'] ? $_GET['is_winmail'] : 0;
							$mailobject->reopen($attachment['folder']);
							$attachmentData = $mailobject->getAttachment($attachment['uid'],$attachment['partID'],$is_winmail);
							$attachment['file'] =tempnam($GLOBALS['egw_info']['server']['temp_dir'],$GLOBALS['egw_info']['flags']['currentapp']."_");
							$tmpfile = fopen($attachment['file'],'w');
							fwrite($tmpfile,$attachmentData['attachment']);
							fclose($tmpfile);
						}

						$attachments[] = array(
							'name' => $attachment['name'],
							'mimeType' => $attachment['type'],
							'tmp_name' => $attachment['file'],
							'size' => $attachment['size'],
						);
					}
				}
				$mailobject->closeConnection();
			}
			// this one adds the mail itself (as message/rfc822 (.eml) file) to the infolog as additional attachment
			// this is done to have a simple archive functionality (ToDo: opening .eml in email module)
			if ($_rawMailHeader && $_rawMailBody && $GLOBALS['egw_info']['user']['preferences'][$sessionLocation]['saveAsOptions']==='add_raw')
			{
				$message = ltrim(str_replace("\n","\r\n",$_rawMailHeader)).str_replace("\n","\r\n",$_rawMailBody);
				$subject = str_replace('$$','__',($_subject?$_subject:lang('(no subject)')));
				$attachment_file =tempnam($GLOBALS['egw_info']['server']['temp_dir'],$GLOBALS['egw_info']['flags']['currentapp']."_");
				$tmpfile = fopen($attachment_file,'w');
				fwrite($tmpfile,$message);
				fclose($tmpfile);
				$size = filesize($attachment_file);
				$attachments[] = array(
						'name' => trim($subject).'.eml',
						'mimeType' => 'message/rfc822',
						'tmp_name' => $attachment_file,
						'size' => $size,
					);
			}

			//_debug_array($_to_emailAddress);
			$toaddr = array();
			foreach(array('to','cc','bcc') as $x)
			{
				if (is_array($_to_emailAddress[$x]) && !empty($_to_emailAddress[$x]))
				{
					$toaddr = array_merge($toaddr,$_to_emailAddress[$x]);
				}
			}
			//_debug_array($attachments);
			$body_striped = strip_tags($mailClass::htmlspecialchars($_body)); //we need to fix broken tags (or just stuff like "<800 USD/p" )
			$body_decoded = htmlspecialchars_decode($body_striped,ENT_QUOTES);
			$body = $mailClass::createHeaderInfoSection(array('FROM'=>$_to_emailAddress['from'],
				'TO'=>(!empty($_to_emailAddress['to'])?implode(',',$_to_emailAddress['to']):null),
				'CC'=>(!empty($_to_emailAddress['cc'])?implode(',',$_to_emailAddress['cc']):null),
				'BCC'=>(!empty($_to_emailAddress['bcc'])?implode(',',$_to_emailAddress['bcc']):null),
				'SUBJECT'=>$_subject,
				'DATE'=>$mailClass::_strtotime($_date))).$body_decoded;
			$this->edit($this->bo->import_mail(
				implode(',',$toaddr),$_subject,$body,$attachments,$_date
			));
			exit;
		}
		elseif ($uid && $mailbox)
		{
			if (!isset($icServerID)) $icServerID =& egw_cache::getSession($sessionLocation,'activeProfileID');
			$mailobject	= $mailClass::getInstance(true,$icServerID);
			$mailobject->openConnection();
			$mailobject->reopen($mailbox);

			$mailcontent = $mailClass::get_mailcontent($mailobject,$uid,$partid,$mailbox,false,true,(!($GLOBALS['egw_info']['user']['preferences'][$sessionLocation]['saveAsOptions']==='text_only')));

			// this one adds the mail itself (as message/rfc822 (.eml) file) to the infolog as additional attachment
			// this is done to have a simple archive functionality (ToDo: opening .eml in email module)
			if ($GLOBALS['egw_info']['user']['preferences'][$sessionLocation]['saveAsOptions']==='add_raw')
			{
				$message = $mailobject->getMessageRawBody($uid, $partid,$mailbox);
				$headers = $mailobject->getMessageHeader($uid, $partid,true,false,$mailbox);
				$subject = str_replace('$$','__',($headers['SUBJECT']?$headers['SUBJECT']:lang('(no subject)')));
				$attachment_file =tempnam($GLOBALS['egw_info']['server']['temp_dir'],$GLOBALS['egw_info']['flags']['currentapp']."_");
				$tmpfile = fopen($attachment_file,'w');
				fwrite($tmpfile,$message);
				fclose($tmpfile);
				$size = filesize($attachment_file);
				$mailcontent['attachments'][] = array(
						'name' => trim($subject).'.eml',
						'mimeType' => 'message/rfc822',
						'tmp_name' => $attachment_file,
						'size' => $size,
					);
			}
			return $this->edit($this->bo->import_mail(
				$mailcontent['mailaddress'],
				$mailcontent['subject'],
				$mailcontent['message'],
				$mailcontent['attachments'],
				strtotime($mailcontent['headers']['DATE'])
			));
		}
		egw_framework::window_close(lang('Error: no mail (Mailbox / UID) given!'));
	}

	/**
	 * shows infolog in other applications
	 *
	 * @param $args['location'] location des hooks: {addressbook|projects|calendar}_view|infolog
	 * @param $args['view']     menuaction to view, if location == 'infolog'
	 * @param $args['app']      app-name, if location == 'infolog'
	 * @param $args['view_id']  name of the id-var for location == 'infolog'
	 * @param $args[$args['view_id']] id of the entry
	 * this function can be called for any app, which should include infolog: \
	 * 	$GLOBALS['egw']->hooks->process(array( \
	 * 		 * 'location' => 'infolog', \
	 * 		 * 'app'      => <your app>, \
	 * 		 * 'view_id'  => <id name>, \
	 * 		 * <id name>  => <id value>, \
	 * 		 * 'view'     => <menuaction to view an entry in your app> \
	 * 	));
	 */
	function hook_view($args)
	{
		// Load JS for infolog actions
		egw_framework::validate_file('.','app','infolog');

		switch ($args['location'])
		{
			case 'addressbook_view':
				$app     = 'addressbook';
				$view_id = 'ab_id';
				$view_id2 = 'contact_id';
				$view    = 'addressbook.addressbook_ui.view';
				break;
			case 'projects_view':
				$app     = 'projects';
				$view_id = 'project_id';
				$view    = 'projects.uiprojects.view';
				break;
			default:
				$app     = $args['app'];
				$view_id = $args['view_id'];
				$view    = $args['view'];
		}
		if (!is_array($args) || $args['debug'])
		{
			echo "<p>infolog_ui::hook_view("; print_r($args); echo "): app='$app', $view_id='$args[$view_id]', view='$view'</p>\n";
		}
		if (!isset($app) || !isset($args[$view_id]))
		{
			return False;
		}
		$this->called_by = $app;	// for read/save_sessiondata, to have different sessions for the hooks
		$GLOBALS['egw_info']['flags']['currentapp'] = 'infolog';
		translation::add_app('infolog');

		$this->index(null,$app,$args[$view_id],array(
			'menuaction' => $view,
			isset($view_id2) ? $view_id2 : $view_id => $args[$view_id]
		),True);
	}

	/**
	 * Defines the fields for the csv export
	 *
	 * @param string $type=null infolog type to include only the matching custom fields if set
	 * @return array
	 */
	function csv_export_fields($type=null)
	{
		$fields = array(
			'info_type'          => lang('Type'),
			'info_from'          => lang('Contact'),
			'info_addr'          => lang('Phone/Email'),
//			'info_link_id'       => lang('primary link'),
			'info_cat'           => array('label' => lang('Category'),'type' => 'select-cat'),
			'info_priority'      => lang('Priority'),
			'info_owner'         => array('label' => lang('Owner'),'type' => 'select-account'),
			'info_access'        => lang('Access'),
			'info_status'        => lang('Status'),
			'info_percent'       => lang('Completed'),
			'info_datecompleted' => lang('Date completed'),
			'info_datemodified'  => lang('Last modified'),
			'info_modifier'      => array('label' => lang('Modifier'),'type' => 'select-account'),
			'info_location'      => lang('Location'),
			'info_startdate'     => lang('Startdate'),
			'info_enddate'       => lang('Enddate'),
			'info_responsible'   => array('label' => lang('Responsible'),'type' => 'select-account'),
			'info_subject'       => lang('Subject'),
			'info_des'           => lang('Description'),
			'info_id'            => lang('Id'),
			// PM fields
			'info_planned_time'  => lang('planned time'),
			'info_used_time'     => lang('used time'),
			'pl_id'              => lang('pricelist'),
			'info_price'         => lang('price'),
		);
		foreach($this->bo->timestamps as $name)
		{
			$fields[$name] = array('label' => $fields[$name],'type' => 'date-time');
		}
		foreach($this->bo->customfields as $name => $data)
		{
			if ($data['type2'] && $type && !in_array($type,explode(',',$data['type2']))) continue;

			$fields['#'.$name] = array(
				'label' => $data['label'],
				'type'  => $data['type'],
			);
		}
		return $fields;
	}
}
