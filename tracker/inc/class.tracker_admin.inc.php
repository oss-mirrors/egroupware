<?php
/**
 * Tracker - Universal tracker (bugs, feature requests, ...) - Admin Interface
 *
 * @link http://www.egroupware.org
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @package tracker
 * @copyright (c) 2006-10 by Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

/**
 * Admin User Interface of the tracker
 */
class tracker_admin extends tracker_bo
{
	/**
	 * Functions callable via menuaction
	 *
	 * @var array
	 */
	var $public_functions = array(
		'admin' => true,
		'escalations' => true,
	);
	/**
	 * reference to the preferences of the user
	 *
	 * @var array
	 */
	var $prefs;

	/**
	 * Constructor
	 *
	 * @return tracker_admin
	 */
	function __construct()
	{
		// check if user has admin rights and bail out if not
		if (!$GLOBALS['egw_info']['user']['apps']['admin'])
		{
			$GLOBALS['egw']->framework->render('<h1 style="color: red;">'.lang('Permission denied !!!')."</h1>\n",null,true);
			return;
		}
		parent::__construct();

		$this->prefs =& $GLOBALS['egw_info']['user']['preferences']['tracker'];
	}

	/**
	 * Site configuration
	 *
	 * @param array $content=null
	 * @return string
	 */
	function admin($content=null,$msg='')
	{
		//_debug_array($content);
		$tracker = (int) $content['tracker'];

		// apply preferences for assigning of defaultprojects, and provide the project list
		if ($this->prefs['allow_defaultproject'] && $tracker)
		{
			$allow_defaultproject = $this->prefs['allow_defaultproject'];
		}

		if (is_array($content))
		{
			list($button) = @each($content['button']);
			$defaultresolution = false;
			if (isset($content['resolutions']['isdefaultresolution']))
			{
				$name = 'resolutions';
				$defaultresolution = $content[$name]['isdefaultresolution'];
				unset($content[$name]['isdefaultresolution']);
			}
			switch($button)
			{
				case 'add':
					if (!$content['add_name'])
					{
						$msg = lang('You need to enter a name');
					}
					elseif (($id = $this->add_tracker($content['add_name'])))
					{
						$tracker = $id;
						$msg = lang('Tracker added');
					}
					else
					{
						$msg = lang('Error adding the new tracker!');
					}
					break;

				case 'rename':
					if (!$content['add_name'])
					{
						$msg = lang('You need to enter a name');
					}
					elseif($tracker && $this->rename_tracker($tracker,$content['add_name']))
					{
						$msg = lang('Tracker queue renamed');
					}
					else
					{
						$msg = lang('Error renaming tracker queue!');
					}
					break;

				case 'delete':
					if ($tracker && isset($this->trackers[$tracker]))
					{
						$this->delete_tracker($tracker);
						$tracker = 0;
						$msg = lang('Tracker deleted');
					}
					break;

				case 'apply':
				case 'save':
					$need_update = false;
					if (!$tracker)	// tracker unspecific config
					{
						foreach(array_diff($this->config_names,array('field_acl','technicians','admins','users','restrictions','notification','mailhandling','priorities')) as $name)
						{
							if (in_array($name,array('overdue_days','pending_close_days')) &&
								$content[$name] === '')
							{
								$content[$name] = '0';	// otherwise it does NOT get stored
							}
							if ((string) $this->$name !== $content[$name])
							{
								$this->$name = $content[$name];
								$need_update = true;
							}
						}
						// field_acl
						foreach($content['field_acl'] as $row)
						{
							$rights = 0;
							foreach(array(
								'TRACKER_ADMIN'         => TRACKER_ADMIN,
								'TRACKER_TECHNICIAN'    => TRACKER_TECHNICIAN,
								'TRACKER_USER'          => TRACKER_USER,
								'TRACKER_EVERYBODY'     => TRACKER_EVERYBODY,
								'TRACKER_ITEM_CREATOR'  => TRACKER_ITEM_CREATOR,
								'TRACKER_ITEM_ASSIGNEE' => TRACKER_ITEM_ASSIGNEE,
								'TRACKER_ITEM_NEW'      => TRACKER_ITEM_NEW,
								'TRACKER_ITEM_GROUP'    => TRACKER_ITEM_GROUP,
							) as $name => $right)
							{
								if ($row[$name]) $rights |= $right;
							}
							if ($this->field_acl[$row['name']] != $rights)
							{
								//echo "<p>$row[name] / $row[label]: rights: ".$this->field_acl[$row['name']]." => $rights</p>\n";
								$this->field_acl[$row['name']] = $rights;
								$need_update = true;
							}
						}
					}
					// tracker specific config and mail handling
					foreach(array('technicians','admins','users','notification','restrictions','mailhandling') as $name)
					{
						$staff =& $this->$name;
						if (!isset($staff[$tracker])) $staff[$tracker] = array();
						if (!isset($content[$name])) $content[$name] = array();

						if ($staff[$tracker] != $content[$name])
						{
							$staff[$tracker] = $content[$name];
							$need_update = true;
						}
					}

					// build the (normalized!) priority array
					$prios = array();
					foreach($content['priorities'] as $value => $data)
					{
						if ($value == 'cat_id')
						{
							$cat_id = $data;
							continue;
						}
						$value = (int) $data['value'];
						$prios[(int)$value] = (string)$data['label'];
					}
					if(!array_diff($prios,array('')))	// user deleted all label --> use the one from the next level above
					{
						$prios = null;
					}
					// priorities are only stored if they differ from the stock-priorities or the default chain of get_tracker_priorities()
					if ($prios !== $this->get_tracker_priorities($tracker,$cat_id,false))
					{
						$key = (int)$tracker;
						if ($cat_id) $key .= '-'.$cat_id;
						if (is_null($prios))
						{
							unset($this->priorities[$key]);
						}
						else
						{
							$this->priorities[$key] = $prios;
						}
						$need_update = true;
					}
					if ($need_update)
					{
						$this->save_config();
						$msg = lang('Configuration updated.').' ';
					}
					$need_update = false;
					foreach(array(
						'cats'      => lang('Category'),
						'versions'  => lang('Version'),
						'projects'  => lang('Projects'),
						'statis'    => lang('Stati'),
						'resolutions'=> lang('Resolution'),
						'responses' => lang('Canned response'),
					) as $name => $what)
					{
						foreach($content[$name] as $cat)
						{
							//_debug_array(array($name=>$cat));
							if (!is_array($cat) || !$cat['name']) continue;	// ignore empty (new) cats

							$new_cat_descr = 'tracker-';
							switch($name)
							{
								case 'cats':
									$new_cat_descr .= 'cat';
									break;
								case 'versions':
									$new_cat_descr .= 'version';
									break;
								case 'statis':
									$new_cat_descr .= 'stati';
									break;
								case 'resolutions':
									$new_cat_descr .= 'resolution';
									break;
								case 'projects':
									$new_cat_descr .= 'project';
									break;
							}
							$old_cat = array(	// some defaults for new cats
								'main'   => $tracker,
								'parent' => $tracker,
								'access' => 'public',
								'data'   => array('type' => substr($name,0,-1)),
								'description'  => $new_cat_descr,
							);
							// search cat in existing ones
							foreach($this->all_cats as $c)
							{
								if ($cat['id'] == $c['id'])
								{
									$old_cat = $c;
									$old_cat['data'] = unserialize($old_cat['data']);
									break;
								}
							}
							// check if new cat or changed, in case of projects the id and a free name is stored
							if (!$old_cat || $cat['name'] != $old_cat['name'] ||
								($name == 'cats' && (int)$cat['autoassign'] != (int)$old_cat['data']['autoassign']) ||
								($name == 'statis' && (int)$cat['closed'] != (int)$old_cat['data']['closed']) ||
								($name == 'projects' && (int)$cat['projectlist'] != (int)$old_cat['data']['projectlist']) ||
								($name == 'responses' && $cat['description'] != $old_cat['data']['response']) ||
								($name == 'resolutions' && (($defaultresolution && ($cat['id']==$defaultresolution || $cat['isdefault'] && $cat['id']!=$defaultresolution))||!$defaultresolution && $cat['isdefault']) ))
							{
								$old_cat['name'] = $cat['name'];
								switch($name)
								{
									case 'cats':
										$old_cat['data']['autoassign'] = $cat['autoassign'];
										break;
									case 'statis':
										$old_cat['data']['closed'] = $cat['closed'];
										break;
									case 'projects':
										$old_cat['data']['projectlist'] = $cat['projectlist'];
										break;
									case 'responses':
										$old_cat['data']['response'] = $cat['description'];
										break;
									case 'resolutions':
										if ($cat['id']==$defaultresolution)
										{
											$no_change = $cat['isdefault'];
											$old_cat['data']['isdefault'] = $cat['isdefault'] = true;
											if($no_change)
											{
												// No real change - use 2 because switch is a loop in PHP
												continue 2;
											}
										}
										else
										{
											if (isset($old_cat['data']['isdefault'])) unset($old_cat['data']['isdefault']);
											if (isset($cat['isdefault'])) unset($cat['isdefault']);
										}
										break;
								}
								//echo "update to"; _debug_array($old_cat);
								if (!isset($cats))
								{
									$cats = new categories(categories::GLOBAL_ACCOUNT,'tracker');
								}
								if (($id = $cats->add($old_cat)))
								{
									$msg .= $old_cat['id'] ? lang("Tracker-%1 '%2' updated.",$what,$cat['name']) : lang("Tracker-%1 '%2' added.",$what,$cat['name']);
									$need_update = true;
								}
							}
						}
					}
					if ($need_update)
					{
						$this->reload_labels();
					}
					if ($button == 'apply') break;
					// fall-through for save
				case 'cancel':
					$GLOBALS['egw']->redirect_link('/index.php',array(
						'menuaction' => 'tracker.tracker_ui.index',
						'msg' => $msg,
					));
					break;

				default:

					foreach(array(
						'cats'      => lang('Category'),
						'versions'  => lang('Version'),
						'projects'  => lang('Projects'),
						'statis'    => lang('State'),
						'resolutions'=> lang('Resolution'),
						'responses' => lang('Canned response'),
					) as $name => $what)
					{
						if (isset($content[$name]['delete']))
						{
							list($id) = each($content[$name]['delete']);
							if ((int)$id)
							{
								$GLOBALS['egw']->categories->delete($id);
								$msg = lang('Tracker-%1 deleted.',$what);
								$this->reload_labels();
							}
						}
					}
					break;
			}

		}
		$content = array(
			'msg' => $msg,
			'tracker' => $tracker,
			'admins' => $this->admins[$tracker],
			'technicians' => $this->technicians[$tracker],
			'users' => $this->users[$tracker],
			'notification' => $this->notification[$tracker],
			'restrictions' => $this->restrictions[$tracker],
			'mailhandling' => $this->mailhandling[$tracker],
			'tabs' => $content['tabs'],
			// keep priority cat only if tracker is unchanged, otherwise reset it
			'priorities' => $tracker == $content['tracker'] ? array('cat_id' => $content['priorities']['cat_id']) : array(),
		);
		if (!$this->enabled_queue_acl_access)
		{
			$GLOBALS['egw']->js->set_onload("document.getElementById('eT_accountsel_exec_users_').disabled = true;");
		}

		foreach(array_diff($this->config_names,array('admins','technicians','users','notification','restrictions','mailhandling','priorities')) as $name)
		{
			$content[$name] = $this->$name;
		}
		// cats & versions & responses & projects
		$v = $c = $r = $s = $p = $i = 1;
		usort($this->all_cats,create_function('$a,$b','return strcasecmp($a["name"],$b["name"]);'));
		foreach($this->all_cats as $cat)
		{
			if (!is_array($data = unserialize($cat['data']))) $data = array('type' => $data);
			//echo "<p>$cat[name] ($cat[id]/$cat[parent]/$cat[main]): ".print_r($data,true)."</p>\n";

			if ($cat['parent'] == $tracker && $data['type'] != 'tracker')
			{
				switch ($data['type'])
				{
					case 'version':
						$content['versions'][$v++] = $cat + $data;
						break;
					case 'response':
						if ($data['response']) $cat['description'] = $data['response'];
						$content['responses'][$r++] = $cat;
						break;
					case 'project':
						$content['projects'][$p++] = $cat + $data;
						break;
					case 'stati':
						$content['statis'][$s++] = $cat + $data;
						break;
					case 'resolution':
						$content['resolutions'][$i++] = $cat + $data;
						if ($data['isdefault']) $content['resolutions']['isdefaultresolution'] = $cat['id'];
						break;
					default:	// cat
						$data['type'] = 'cat';
						$content['cats'][$c++] = $cat + $data;
						break;
				}
			}
		}
		$content['versions'][$v++] = $content['cats'][$c++] = $content['responses'][$r++] = $content['projects'][$p++] = $content['statis'][$s++] = $content['resolutions'][$i++] =
			array('id' => 0,'name' => '');	// one empty line for adding
		// field_acl
		$f = 1;
		foreach($this->field2label as $name => $label)
		{
			if (in_array($name,array('num_replies'))) continue;

			$rights = $this->field_acl[$name];
			$content['field_acl'][$f++] = array(
				'label'                 => $label,
				'name'                  => $name,
				'TRACKER_ADMIN'         => !!($rights & TRACKER_ADMIN),
				'TRACKER_TECHNICIAN'    => !!($rights & TRACKER_TECHNICIAN),
				'TRACKER_USER'          => !!($rights & TRACKER_USER),
				'TRACKER_EVERYBODY'     => !!($rights & TRACKER_EVERYBODY),
				'TRACKER_ITEM_CREATOR'  => !!($rights & TRACKER_ITEM_CREATOR),
				'TRACKER_ITEM_ASSIGNEE' => !!($rights & TRACKER_ITEM_ASSIGNEE),
				'TRACKER_ITEM_NEW'      => !!($rights & TRACKER_ITEM_NEW),
				'TRACKER_ITEM_GROUP'    => !!($rights & TRACKER_ITEM_GROUP),
			);
		}

		$n = 2;	// cat selection + table header
		foreach($this->get_tracker_priorities($tracker,$content['priorities']['cat_id'],false) as $value => $label)
		{
			$content['priorities'][$n++] = array(
				'value' => self::$stock_priorities[$value],
				'label' => $label,
			);
		}
		//_debug_array($content);
		if (is_array($content['exclude_app_on_timesheetcreation']) && !in_array('timesheet',$content['exclude_app_on_timesheetcreation'])) $content['exclude_app_on_timesheetcreation'][]='timesheet';
		if (isset($content['exclude_app_on_timesheetcreation']) && !is_array($content['exclude_app_on_timesheetcreation']) && stripos($content['exclude_app_on_timesheetcreation'],'timesheet')===false) $content['exclude_app_on_timesheetcreation']=(strlen(trim($content['exclude_app_on_timesheetcreation']))>0?$content['exclude_app_on_timesheetcreation'].',':'').'timesheet';
		if (!isset($content['exclude_app_on_timesheetcreation'])) $content['exclude_app_on_timesheetcreation']='timesheet';
		if ($allow_defaultproject)	$content['allow_defaultproject'] = $this->prefs['allow_defaultproject'];
		$sel_options = array(
			'tracker' => &$this->trackers,
			'allow_assign_groups' => array(
				0 => lang('No'),
				1 => lang('Yes, display groups first'),
				2 => lang('Yes, display users first'),
			),
			'allow_voting' => array('No','Yes'),
			'allow_bounties' => array('No','Yes'),
			'autoassign' => $this->get_staff($tracker),
			'lang' => $GLOBALS['egw']->translation->get_installed_langs(),
			'cat_id' => $this->get_tracker_labels('cat',$tracker),
			// Mail handling
			'interval' => array(
				0 => 'Disabled',
				5 => 5,
				10 => 10,
				15 => 15,
				20 => 20,
				30 => 30,
				60 => 60
			),
			'servertype' => array(),
			'default_tracker' => ($tracker ? array($tracker => $this->trackers[$tracker]) : $this->trackers),
			// TODO; enable the default_trackers onChange() to reload categories
			'default_cat' => $this->get_tracker_labels('cat',$content['mailhandling']['default_tracker']),
			'unrec_reply' => array(
				0 => 'Creator',
				1 => 'Nobody',
			),
			'auto_reply' => array(
				0 => lang('Never'),
				1 => lang('Yes, new tickets only'),
				2 => lang('Yes, always'),
			),
			'reply_unknown' => array(
				0 => 'Creator',
				1 => 'Nobody',
			),
			'exclude_app_on_timesheetcreation' => egw_link::app_list('add'),
		);
		foreach($this->mailservertypes as $ind => $typ)
		{
			$sel_options['servertype'][] = $typ[1];
		}
		foreach($this->mailheaderhandling as $ind => $typ)
		{
			$sel_options['mailheaderhandling'][] = $typ[1];
		}
		$readonlys = array(
			'button[delete]' => !$tracker,
			'delete[0]' => true,
			'button[rename]' => !$tracker,
			'tabs' => array('tracker.admin.acl'=>$tracker),
		);
		$GLOBALS['egw_info']['flags']['app_header'] = lang('Tracker configuration').($tracker ? ': '.$this->trackers[$tracker] : '');
		$tpl = new etemplate('tracker.admin');
		return $tpl->exec('tracker.tracker_admin.admin',$content,$sel_options,$readonlys,$content);
	}

	/**
	 * Get escalation rows
	 *
	 * @param array $query
	 * @param array &$rows
	 * @param array &$readonlys
	 * @return int|boolean
	 */
	function get_rows($query,&$rows,&$readonlys)
	{
		$escalations = new tracker_escalations();
		$Ok = $escalations->get_rows($query,$rows,$readonlys);

		if ($rows)
		{
			foreach($rows as &$row)
			{
				// Show before / after
				$row['esc_before_after'] = ($row['esc_time'] < 0 ? tracker_escalations::BEFORE : tracker_escalations::AFTER);
				$row['esc_time'] = abs($row['esc_time']);
				
				// show the right tracker and/or cat specific priority label
				if ($row['tr_priority'])
				{
					if (is_null($prio_labels) || $row['tr_tracker'] != $prio_tracker || $row['cat_id'] != $prio_cat)
					{
						$prio_labels = $this->get_tracker_priorities($prio_tracker=$row['tr_tracker'],$prio_cat = $row['cat_id']);
					}
					$row['prio_label'] = $prio_labels[$row['tr_priority']];
				}

				// Show repeat limit, if set
				if($row['esc_limit']) $row['esc_limit_label'] = lang('maximum %1 times', $row['esc_limit']);
			}
		}
		return $Ok;
	}

	/**
	 * Define escalations
	 *
	 * @param array $content
	 * @param string $msg
	 */
	function escalations(array $content=null,$msg='')
	{
		$escalations = new tracker_escalations();

		if (!is_array($content))
		{
			$content['nm'] = array(
				'get_rows'       =>	'tracker.tracker_admin.get_rows',
				'no_cat'         => true,
				'no_filter2'=> true,
				'no_filter' => true,
				'order'          =>	'esc_time',
				'sort'           =>	'ASC',// IO direction of the sort: 'ASC' or 'DESC'
			);
		}
		else
		{
			//_debug_array($content);
			list($button) = @each($content['button']);
			unset($content['button']);
			$escalations->init($content);

			switch($button)
			{
				case 'save':
				case 'apply':
					// 'Before' only valid for start & due dates
					if($content['esc_before_after'] == tracker_escalations::BEFORE &&
						!in_array($content['esc_type'],array(tracker_escalations::START,tracker_escalations::DUE)))
					{	
						$msg = lang('"%2" only valid for start date and due date.  Use "%1".',lang('after'),lang('before'));
						$escalations->data['esc_before_after'] = tracker_escalations::AFTER;
						break;
					}
					// Handle before time
					$escalations->data['esc_time'] *= ($content['esc_before_after'] == tracker_escalations::BEFORE ? -1 : 1);

					if (($err = $escalations->not_unique()))
					{
						$msg = lang('There already an escalation for that filter!');
						$button = '';
					}
					elseif (($err = $escalations->save()) == 0)
					{
						$msg = $content['esc_id'] ? lang('Escalation saved.') : lang('Escalation added.');
					}
					if ($button == 'apply' || $err) break;
					// fall-through
				case 'cancel':
					$escalations->init();
					break;
			}
			if ($content['nm']['rows']['edit'])
			{
				list($id) = each($content['nm']['rows']['edit']);
				unset($content['nm']['rows']);
				if (!$escalations->read($id))
				{
					$msg = lang('Escalation not found!');
					$escalations->init();
				}
			}
			elseif($content['nm']['rows']['delete'])
			{
				list($id) = each($content['nm']['rows']['delete']);
				unset($content['nm']['rows']);
				if (!$escalations->delete(array('esc_id' => $id)))
				{
					$msg = lang('Error deleting escalation!');
				}
				else
				{
					$msg = lang('Escalation deleted.');
				}
			}
		}
		$content = $escalations->data + array(
			'nm' => $content['nm'],
			'msg' => $msg,
		);

		// Handle before time
		$content['esc_before_after'] = ($content['esc_time'] < 0 ? tracker_escalations::BEFORE : tracker_escalations::AFTER);
		$content['esc_time'] = abs($content['esc_time']);

		$preserv['esc_id'] = $content['esc_id'];
		$preserv['nm'] = $content['nm'];

		$tracker = $content['tr_tracker'];
		$sel_options = array(
			'tr_tracker'  => &$this->trackers,
			'cat_id'      => $this->get_tracker_labels('cat',$tracker),
			'tr_version'  => $this->get_tracker_labels('version',$tracker),
			'tr_priority' => $this->get_tracker_priorities($tracker,$content['cat_id']),
			'tr_status'   => $this->get_tracker_stati($tracker),
			'tr_assigned' => $this->get_staff($tracker,$this->allow_assign_groups),
			'esc_before_after' => array(
				tracker_escalations::AFTER => lang('after'),
				tracker_escalations::BEFORE => lang('before'),
			),
			'esc_type'    => array(
				tracker_escalations::CREATION => lang('creation date'),
				tracker_escalations::MODIFICATION => lang('last modified'),
				tracker_escalations::START => lang('start date'),
				tracker_escalations::DUE => lang('due date'),
				tracker_escalations::REPLIED => lang('last reply'),
				tracker_escalations::REPLIED_CREATOR => lang('last reply by creator'),
				tracker_escalations::REPLIED_ASSIGNED => lang('last reply by assigned'),
				tracker_escalations::REPLIED_NOT_CREATOR => lang('last reply by anyone but creator'),
			),
			'notify' => tracker_escalations::$notification
		);
		$tpl = new etemplate('tracker.escalations');
		if ($content['set']['tr_assigned'] && !is_array($content['set']['tr_assigned']))
		{
			$content['set']['tr_assigned'] = explode(',',$content['set']['tr_assigned']);
		}
		if (count($content['set']['tr_assigned']) > 1)
		{
			$widget =& $tpl->get_widget_by_name('tr_assigned');	//$tpl->set_cell_attribute() sets all widgets with this name, so the action too!
			$widget['size'] = '3+';
		}
		if ($content['tr_status'] && !is_array($content['tr_status']))
		{
			$content['tr_status'] = explode(',',$content['tr_status']);
		}
		if (count($content['tr_status']) > 1)
		{
			$widget =& $tpl->get_widget_by_name('tr_status');
			$widget['size'] = '3+';
		}
		if ($this->tracker_has_cat_specific_priorities($tracker))
		{
			$widget =& $tpl->get_widget_by_name('cat_id');
			$widget['onchange'] = true;
		}
		$GLOBALS['egw_info']['flags']['app_header'] = lang('Tracker').' - '.lang('Define escalations');
		//_debug_array($content);
		return $tpl->exec('tracker.tracker_admin.escalations',$content,$sel_options,$readonlys,$preserv);
	}
}
