<?php
/**
 * TimeSheet - user interface
 *
 * @link http://www.egroupware.org
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @package timesheet
 * @copyright (c) 2005-8 by Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

/**
 * User interface object of the TimeSheet
 */
class timesheet_ui extends timesheet_bo
{
	var $public_functions = array(
		'view' => true,
		'edit' => true,
		'index' => true,
	);
	/**
	 * ProjectManager integration: 'none', 'full' or default null
	 *
	 * @var string
	 */
	var $pm_integration;

	/**
	 * TimeSheet view type: 'short' or 'normal'
	 *
	 * @var string
	 */
	var $ts_viewtype;

	/**
	 * Constructor
	 *
	 */
	function __construct()
	{
		parent::__construct();

		$this->pm_integration = $this->config_data['pm_integration'];
		$this->ts_viewtype = $this->config_data['ts_viewtype'];

		// our javascript
		// to be moved in a seperate file if rewrite is over
		$GLOBALS['egw_info']['flags']['java_script'] .= $this->js();
	}

	function view()
	{
		$this->edit(null,true);
	}

	function edit($content = null,$view = false)
	{
		$tabs = 'general|notes|links|customfields';
		$etpl =& new etemplate('timesheet.edit');

		if (!is_array($content))
		{
			if ($view || (int)$_GET['ts_id'])
			{
				if (!$this->read((int)$_GET['ts_id']))
				{
					$GLOBALS['egw']->common->egw_header();
					echo "<script>alert('".lang('Permission denied!!!')."'); window.close();</script>\n";
					$GLOBALS['egw']->common->egw_exit();
				}
				if (!$view && !$this->check_acl(EGW_ACL_EDIT))
				{
					$view = true;
				}
			}
			else	// new entry
			{
				$this->data = array(
					'ts_start' => $this->today,
					'end_time' => $this->now - $this->today,
					'ts_owner' => $GLOBALS['egw_info']['user']['account_id'],
					'cat_id'   => (int) $_REQUEST['cat_id'],
				);
			}
			$referer = preg_match('/menuaction=([^&]+)/',$_SERVER['HTTP_REFERER'],$matches) ? $matches[1] :
				(strpos($_SERVER['HTTP_REFERER'],'/infolog/index.php') !== false ? 'infolog.uiinfolog.index' : TIMESHEET_APP.'.timesheet_ui.index');
		}
		else
		{
			//echo "<p>ts_start=$content[ts_start], start_time=$content[start_time], end_time=$content[end_time], ts_duration=$content[ts_duration], ts_quantity=$content[ts_quantity]</p>\n";
			// we only need 2 out of 3 values from start-, end-time or duration (the date in ts_start is always required!)
			if ($content['start_time'])		// start-time specified
			{
				$content['ts_start'] += $content['start_time'];
			}
			if ($content['end_time'] && $content['start_time'])	// start- & end-time --> calculate the duration
			{
				$content['ts_duration'] = ($content['end_time'] - $content['start_time']) / 60;
			}
			elseif ($content['ts_duration'] && $content['end_time'])	// no start, calculate from end and duration
			{
				$content['ts_start'] += $content['end_time'] - 60*$content['ts_duration'];
			}
			if ($content['ts_duration'] > 0) unset($content['end_time']);
			// now we only deal with start (date+time) and duration
			list($button) = @each($content['button']);
			$view = $content['view'];
			$referer = $content['referer'];
			$this->data = $content;
			foreach(array('button','view','referer',$tabs,'start_time') as $key)
			{
				unset($this->data[$key]);
			}
			switch($button)
			{
				case 'edit':
					if ($this->check_acl(EGW_ACL_EDIT)) $view = false;
					break;

				case 'save':
				case 'save_new':
				case 'apply':
					if (!$this->data['ts_quantity'] && $this->data['ts_duration'])	// set the quantity (in h) from the duration (in min)
					{
						$this->data['ts_quantity'] = $this->data['ts_duration'] / 60.0;
					}
					if (!$this->data['ts_quantity'])
					{
						$etpl->set_validation_error('ts_quantity',lang('Field must not be empty !!!'));
					}
					if ($this->data['ts_duration'] < 0)	// for layout purpose we show the error behind the quantity field
					{
						$etpl->set_validation_error('ts_quantity',lang('Starttime has to be before endtime !!!'));
					}
					//echo "<p>ts_start=$content[ts_start], start_time=$content[start_time], end_time=$content[end_time], ts_duration=$content[ts_duration], ts_quantity=$content[ts_quantity]</p>\n";
					if (!$this->data['ts_project']) $this->data['ts_project'] = $this->data['ts_project_blur'];
					// set ts_title to ts_project if short viewtype (title is not editable)
					if($this->ts_viewtype == 'short')
					{
						$this->data['ts_title'] = $this->data['ts_project'];
					}
					if (!$this->data['ts_title'])
					{
						$this->data['ts_title'] = $this->data['ts_title_blur'] ?
							$this->data['ts_title_blur'] : $this->data['ts_project_blur'];

						if (!$this->data['ts_title'])
						{
							$etpl->set_validation_error('ts_title',lang('Field must not be empty !!!'));
						}
					}
					if ($etpl->validation_errors()) break;	// the user need to fix the error, before we can save the entry

					if ($this->save() != 0)
					{
						$msg = lang('Error saving the entry!!!');
						$button = '';
					}
					else
					{
						$msg = lang('Entry saved');
						if ((int) $this->data['pm_id'] != (int) $this->data['old_pm_id'])
						{
							// update links accordingly
							if ($this->data['pm_id'])
							{
								egw_link::link(TIMESHEET_APP,$content['link_to']['to_id'],'projectmanager',$this->data['pm_id']);
							}
							if ($this->data['old_pm_id'])
							{
								egw_link::unlink2(0,TIMESHEET_APP,$content['link_to']['to_id'],0,'projectmanager',$this->data['old_pm_id']);
								unset($this->data['old_pm_id']);
							}
						}
						if (is_array($content['link_to']['to_id']) && count($content['link_to']['to_id']))
						{
							egw_link::link(TIMESHEET_APP,$this->data['ts_id'],$content['link_to']['to_id']);
						}
					}
					$js = "opener.location.href='".$GLOBALS['egw']->link('/index.php',array(
						'menuaction' => $referer,
						'msg'        => $msg,
					))."';";
					if ($button == 'apply') break;
					if ($button == 'save_new')
					{
						$msg .= ', '.lang('creating new entry');		// giving some feedback to the user

						if (!is_array($content['link_to']['to_id']))	// set links again, so new entry gets the same links as the existing one
						{
							$content['link_to']['to_id'] = 0;
							foreach(egw_link::get_links(TIMESHEET_APP,$this->data['ts_id'],'!'.egw_link::VFS_APPNAME) as $link)
							{
								egw_link::link(TIMESHEET_APP,$content['link_to']['to_id'],$link['app'],$link['id'],$link['remark']);
							}
						}
						// create a new entry
						$this->data['ts_start'] += 60 * $this->data['ts_duration'];
						foreach(array('ts_id','ts_title','ts_description','ts_duration','ts_quantity','ts_modified','ts_modifier') as $name)
						{
							unset($this->data[$name]);
						}
						// save the selected project, to delete the project-link, if the user changes the project
						$this->data['old_pm_id'] = $this->data['pm_id'];
						break;
					}
					// fall-through for save
				case 'delete':
					if ($button == 'delete')
					{
						if ($this->delete())
						{
							$msg = lang('Entry deleted');
							$js = "opener.location.href=opener.location.href+'&msg=$msg';";
						}
						else
						{
							$msg = lang('Error deleting the entry!!!');
							break;	// dont close window
						}
					}
					// fall-through for save
				case 'cancel':
					$js .= 'window.close();';
					echo "<html>\n<body>\n<script>\n$js\n</script>\n</body>\n</html>\n";
					$GLOBALS['egw']->common->egw_exit();
					break;
			}
		}
		$preserv = $this->data + array(
			'view'    => $view,
			'referer' => $referer,
			'ts_title_blur' => $content['ts_title_blur'],
		);
		$content = array_merge($this->data,array(
			'msg'  => $msg,
			'view' => $view,
			$tabs  => $content[$tabs],
			'link_to' => array(
				'to_id' => $this->data['ts_id'] ? $this->data['ts_id'] : $content['link_to']['to_id'],
				'to_app' => TIMESHEET_APP,
			),
			'js' => "<script>\n$js\n</script>\n",
			'ts_quantity_blur' => $this->data['ts_duration'] ? round($this->data['ts_duration'] / 60.0,3) : '',
			'start_time' => $this->datetime2time($this->data['ts_start']),
			'pm_integration' => $this->pm_integration,
		));
		$links = array();
		// create links specified in the REQUEST (URL)
		if (!$this->data['ts_id'] && isset($_REQUEST['link_app']) && isset($_REQUEST['link_id']) && !is_array($content['link_to']['to_id']))
		{
			$link_ids = is_array($_REQUEST['link_id']) ? $_REQUEST['link_id'] : array($_REQUEST['link_id']);
			foreach(is_array($_REQUEST['link_app']) ? $_REQUEST['link_app'] : array($_REQUEST['link_app']) as $n => $link_app)
			{
				$link_id = $link_ids[$n];
				if (preg_match('/^[a-z_0-9-]+:[:a-z_0-9-]+$/i',$link_app.':'.$link_id))	// gard against XSS
				{
					egw_link::link(TIMESHEET_APP,$content['link_to']['to_id'],$link_app,$link_id);
					switch ($link_app)
					{
						case 'projectmanager':
							$links[] = $link_id;
							break;
						case 'infolog':
							// a preserved title blur is only set for other (non-project) links, it stays with Save&New!
							$preserv['ts_title_blur'] = egw_link::title('infolog',$link_id);
							break;
					}
				}
			}
		}
		elseif ($this->data['ts_id'])
		{
			$links = egw_link::get_links(TIMESHEET_APP,$this->data['ts_id'],'projectmanager');
		}
		// make all linked projects availible for the pm-pricelist widget, to be able to choose prices from all
		$content['all_pm_ids'] = array_values($links);

		// set old id, pm selector (for later removal)
		if (count($links) > 0)
		{
			$preserv['old_pm_id'] = array_shift($links);
		}
		if (!isset($this->data['pm_id']) && $preserv['old_pm_id'])
		{
			$content['pm_id'] = $preserv['old_pm_id'];
		}
		if ($content['pm_id'])
		{
			$preserv['ts_project_blur'] = $content['ts_project_blur'] = egw_link::title('projectmanager',$content['pm_id']);
		}
		if ($this->pm_integration == 'full')
		{
			$preserv['ts_project'] = $preserv['ts_project_blur'];
		}
		// the actual title-blur is either the preserved title blur (if we are called from infolog entry),
		// or the preserved project-blur comming from the current selected project
		$content['ts_title_blur'] = $preserv['ts_title_blur'] ? $preserv['ts_title_blur'] : $preserv['ts_project_blur'];

		$readonlys = array(
			'button[delete]'   => !$this->data['ts_id'] || !$this->check_acl(EGW_ACL_DELETE),
			'button[edit]'     => !$view || !$this->check_acl(EGW_ACL_EDIT),
			'button[save]'     => $view,
			'button[save_new]' => $view,
			'button[apply]'    => $view,
		);
		if ($view)
		{
			foreach(array_merge(array_keys($this->data),array('pm_id','pl_id','link_to')) as $key)
			{
				$readonlys[$key] = true;
			}
			$readonlys['start_time'] = $readonlys['end_time'] = true;
		}
		$edit_grants = $this->grant_list(EGW_ACL_EDIT);
		if (count($edit_grants) == 1)
		{
			$readonlys['ts_owner'] = true;
		}
		$GLOBALS['egw_info']['flags']['app_header'] = lang('timesheet').' - '.
			($view ? lang('View') : ($this->data['ts_id'] ? lang('Edit') : lang('Add')));

		// supress unknow widget 'projectmanager-*', if projectmanager is not installed or old
		if (!@file_exists(EGW_INCLUDE_ROOT.'/projectmanager/inc/class.projectmanager_widget.inc.php'))
		{
			$etpl->set_cell_attribute('pm_id','disabled',true);
			$etpl->set_cell_attribute('pl_id','disabled',true);
		}

		if($this->ts_viewtype == 'short')
		{
			$content['ts_viewtype'] = $readonlys[$tabs]['notes'] = true;
		}
		if (!$this->customfields) $readonlys[$tabs]['customfields'] = true;	// suppress tab if there are not customfields

		return $etpl->exec(TIMESHEET_APP.'.timesheet_ui.edit',$content,array(
			'ts_owner' => $edit_grants,
		),$readonlys,$preserv,2);
	}

	/**
	 * Calculate the time from a timestamp containing date & time
	 *
	 * @param int $datetime
	 * @return int
	 */
	function datetime2time($datetime)
	{
		if (!$datetime) return 0;

		return $datetime - mktime(0,0,0,date('m',$datetime),date('d',$datetime),date('Y',$datetime));
	}

	/**
	 * query projects for nextmatch in the projects-list
	 *
	 * reimplemented from so_sql to disable action-buttons based on the acl and make some modification on the data
	 *
	 * @param array &$query
	 * @param array &$rows returned rows/cups
	 * @param array &$readonlys eg. to disable buttons based on acl
	 * @param boolean $id_only=false if true only return (via $rows) an array of contact-ids, dont save state to session
	 * @return int total number of contacts matching the selection
	 */
	function get_rows(&$query_in,&$rows,&$readonlys,$id_only=false)
	{
		$this->show_sums = false;
		if ($query_in['filter'])
		{
			$date_filter = $this->date_filter($query_in['filter'],$query_in['startdate'],$query_in['enddate']);

			$start = explode('-',date('Y-m-d',$query_in['startdate']+12*60*60));
			$end   = explode('-',date('Y-m-d',$query_in['enddate'] ? $query_in['enddate'] : $query_in['startdate']+7.5*24*60*60));

			// show year-sums, if we are year-aligned (show full years)?
			if ((int)$start[2] == 1 && (int)$start[1] == 1 && (int)$end[2] == 31 && (int)$end[1] == 12)
			{
				$this->show_sums[] = 'year';
			}
			// show month-sums, if we are month-aligned (show full monthes)?
			if ((int)$start[2] == 1 && (int)$end[2] == (int)date('d',mktime(12,0,0,$end[1]+1,0,$end[0])))
			{
				$this->show_sums[] = 'month';
			}
			// show week-sums, if we are week-aligned (show full weeks)?
			$week_start_day = $GLOBALS['egw_info']['user']['preferences']['calendar']['weekdaystarts'];
			if (!$week_start_day) $week_start_day = 'Sunday';
			switch($week_start_day)
			{
				case 'Sunday': $week_end_day = 'Saturday'; break;
				case 'Monday': $week_end_day = 'Sunday'; break;
				case 'Saturday': $week_end_day = 'Friday'; break;
			}
			$filter_start_day = date('l',$query_in['startdate']+12*60*60);
			$filter_end_day   = $query_in['enddate'] ? date('l',$query_in['enddate']+12*60*60) : false;
			//echo "<p align=right>prefs: $week_start_day - $week_end_day, filter: $filter_start_day - $filter_end_day</p>\n";
			if ($filter_start_day == $week_start_day && (!$filter_end_day || $filter_end_day == $week_end_day))
			{
				$this->show_sums[] = 'week';
			}
			// show day-sums, if range <= 5 weeks
			if (!$query_in['enddate'] || $query_in['enddate'] - $query_in['startdate'] < 36*24*60*60)
			{
				$this->show_sums[] = 'day';
			}
		}
		//echo "<p align=right>show_sums=".print_r($this->show_sums,true)."</p>\n";
		$GLOBALS['egw']->session->appsession('index',TIMESHEET_APP,$query_in);
		$query = $query_in;	// keep the original query

		// PM project filter for the PM integration
		if ((string)$query['col_filter']['pm_id'] != '')
		{
			//$query['col_filter']['ts_id'] = egw_link::get_links('projectmanager',$query['col_filter']['pm_id'],'timesheet');
			$query['col_filter']['ts_id'] = $this->get_ts_links($query['col_filter']['pm_id']);
			if (!$query['col_filter']['ts_id']) $query['col_filter']['ts_id'] = 0;
		}
		unset($query['col_filter']['pm_id']);

		// filter for no project
		if ((string)$query['col_filter']['ts_project'] == '0') $query['col_filter']['ts_project'] = null;

		if ((int)$query['filter2'] != (int)$GLOBALS['egw_info']['user']['preferences'][TIMESHEET_APP]['show_details'])
		{
			$GLOBALS['egw']->preferences->add(TIMESHEET_APP,'show_details',(int)$query['filter2']);
			$GLOBALS['egw']->preferences->save_repository(true);
		}
		// category filter: cat_id or ''=All cats or 0=No cat
		if ($query['cat_id'])
		{
			$cats = $GLOBALS['egw']->categories->return_all_children((int)$query['cat_id']);
			$query['col_filter']['cat_id'] = count($cats) > 1 ? $cats : $query['cat_id'];
		}
		elseif ((string)$query['cat_id'] == '0')	// no category
		{
			$query['col_filter']['cat_id'] = null;
		}
		else	// all cats --> no filter
		{
			unset($query['col_filter']['cat_id']);
		}
		$GLOBALS['egw_info']['flags']['app_header'] = lang('timesheet');
		if ($query['col_filter']['ts_owner'])
		{
			$GLOBALS['egw_info']['flags']['app_header'] .= ': '.$GLOBALS['egw']->common->grab_owner_name($query['col_filter']['ts_owner']);
		}
		else
		{
			unset($query['col_filter']['ts_owner']);
		}
		if ($query['filter'])
		{
			$query['col_filter'][0] = $date_filter;

			// generate a meaningful app-header / report title
			if ($this->show_sums['month'])
			{
				if ((int)$start[1] == 1 && (int) $end[1] == 12)		// whole year(s)
				{
					$GLOBALS['egw_info']['flags']['app_header'] .= ': ' . $start[0] . ($start[0] != $end[0] ? ' - '.$end[0] : '');
				}
				else
				{
					$GLOBALS['egw_info']['flags']['app_header'] .= ': ' . lang(date('F',$query['startdate']+12*60*60)) . ' ' . $start[0];
					if ($start[0] != $end[0] || $start[1] != $end[1])
					{
						$GLOBALS['egw_info']['flags']['app_header'] .= ' - ' . lang(date('F',$query['enddate']+12*60*60)) . ' ' . $end[0];
					}
				}
			}
			elseif ($this->show_sums['week'])
			{
				$GLOBALS['egw_info']['flags']['app_header'] .= ': ' . lang('week') . ' ' . date('W',$query['startdate']+36*60*60) . '/' . $start[0];
				if ($query['enddate'] && $query['enddate'] - $query['startdate'] > 10*24*60*60)
				{
					$GLOBALS['egw_info']['flags']['app_header'] .= ' - ' . date('W',$query['enddate']-36*60*60) . '/' . $end[0];
				}
			}
			else
			{
				$df = $GLOBALS['egw_info']['user']['preferences']['common']['dateformat'];
				$GLOBALS['egw_info']['flags']['app_header'] .= ': ' . $GLOBALS['egw']->common->show_date($query['startdate']+12*60*60,$df,false);
				if ($start != $end)
				{
					$GLOBALS['egw_info']['flags']['app_header'] .= ' - '.$GLOBALS['egw']->common->show_date($query['enddate']+12*60*60,$df,false);
				}
			}
			if ($query['filter'] == 'custom')	// show the custome dates
			{
				$GLOBALS['egw']->js->set_onload("set_style_by_class('table','custom_hide','visibility','visible');");
			}
		}
		$total = parent::get_rows($query,$rows,$readonlys);

		$ids = array();
		foreach($rows as $row)
		{
			$ids[] = $row['ts_id'];
		}
		if ($id_only)
		{
			$rows = $ids;
			return $this->total;	// no need to set other fields or $readonlys
		}
		$links = egw_link::get_links_multiple(TIMESHEET_APP,$ids);

		unset($query['col_filter'][0]);

		$readonlys = array();
		$have_cats = false;
		foreach($rows as &$row)
		{
			if ($row['cat_id']) $have_cats = true;

			$row['class'] = 'row';
			if ($row['ts_id'] <= 0)	// sums
			{
				$readonlys["view[$row[ts_id]]"] = $readonlys["edit[$row[ts_id]]"] = $readonlys["delete[$row[ts_id]]"] = true;
				if ($query['sort'] == 'ASC') $row['ts_start'] -= 7200;	// fix for DSL change
				switch($row['ts_id'])
				{
					case 0:	// day-sum
						$row['ts_title'] = lang('Sum %1:',lang(date('l',$row['ts_start'])).' '.$GLOBALS['egw']->common->show_date($row['ts_start'],
							$GLOBALS['egw_info']['user']['preferences']['common']['dateformat'],false));
						break;
					case -1:	// week-sum
						$row['ts_title'] = lang('Sum %1:',lang('week').' '.substr($row['ts_week'],4).'/'.substr($row['ts_week'],0,4));
						break;
					case -2:	// month-sum
						$row['ts_title'] = lang('Sum %1:',lang(date('F',$row['ts_start'])).' '.substr($row['ts_month'],0,4));
						break;
					case -3:	// year-sum
						$row['ts_title'] = lang('Sum %1:',$row['ts_year']);
						break;
				}
				$row['ts_start'] = $row['ts_unitprice'] = '';
				if (!$this->quantity_sum) $row['ts_quantity'] = '';
				$row['class'] = 'th';
				$row['titleClass'] = 'titleSum';
				continue;
			}
			if (!$this->check_acl(EGW_ACL_EDIT,$row))
			{
				$readonlys["edit[$row[ts_id]]"] = true;
			}
			if (!$this->check_acl(EGW_ACL_DELETE,$row))
			{
				$readonlys["delete[$row[ts_id]]"] = true;
			}
			if ($query['col_filter']['ts_project'] || !$query['filter2'])
			{
				unset($row['ts_project']);	// dont need or want to show it
			}
			elseif ($links[$row['ts_id']])
			{
				foreach($links[$row['ts_id']] as $link)
				{
					if ($link['app'] == 'projectmanager')
					{
						$row['ts_link'] = $link;
						$row['ts_link']['title'] = $row['ts_project'];
						break;
					}
				}
			}
			if (!$query['filter2'])
			{
				unset($row['ts_description']);
			}
			else
			{
				$row['titleClass'] = 'titleDetails';
			}
		}
		if (!$have_cats || $query['cat_id']) $rows['no_cat_id'] = true;
		if ($query['col_filter']['ts_owner']) $rows['ownerClass'] = 'noPrint';
		$rows['no_owner_col'] = $query['no_owner_col'];
		if ($query['filter'])
		{
			$rows += $this->summary;
		}
		$rows['pm_integration'] = $this->pm_integration;

		if($this->ts_viewtype == 'short') {
			$rows['ts_viewtype'] = true;
		}

		return $total;
	}

	/**
	 * List timesheet entries
	 *
	 * @param array $content=null
	 * @param string $msg=''
	 */
	function index($content = null,$msg='')
	{
		$etpl =& new etemplate('timesheet.index');

		if ($_GET['msg']) $msg = $_GET['msg'];

		if ($content['nm']['rows']['delete'])
		{
			list($ts_id) = each($content['nm']['rows']['delete']);
			if ($this->delete($ts_id))
			{
				$msg = lang('Entry deleted');
			}
			else
			{
				$msg = lang('Error deleting the entry!!!');
			}
		}
		$content = array(
			'nm' => $GLOBALS['egw']->session->appsession('index',TIMESHEET_APP),
			'msg' => $msg,
		);
		if (!is_array($content['nm']))
		{
			$date_filters = array('All');
			foreach($this->date_filters as $name => $date)
			{
				$date_filters[$name] = $name;
			}
			$date_filters['custom'] = 'custom';

			$content['nm'] = array(
				'get_rows'       =>	TIMESHEET_APP.'.timesheet_ui.get_rows',
				'options-filter' => $date_filters,
				'options-filter2' => array('No details','Details'),
				'order'          =>	'ts_start',// IO name of the column to sort after (optional for the sortheaders)
				'sort'           =>	'DESC',// IO direction of the sort: 'ASC' or 'DESC'
				'header_left'    => 'timesheet.index.dates',
				'header_right'   => 'timesheet.index.add',
				'filter_onchange' => "set_style_by_class('table','custom_hide','visibility',this.value == 'custom' ? 'visible' : 'hidden'); if (this.value != 'custom') this.form.submit();",
				'filter2'        => (int)$GLOBALS['egw_info']['user']['preferences'][TIMESHEET_APP]['show_details'],
			);
		}
		$read_grants = $this->grant_list(EGW_ACL_READ);
		$content['nm']['no_owner_col'] = count($read_grants) == 1;

		$sel_options = array(
			'ts_owner'   => $read_grants,
			'pm_id'      => array(lang('No project')),
			'cat_id'     => array(lang('None')),
		);
		if ($this->pm_integration != 'full')
		{
			$projects =& $this->query_list('ts_project');
			if (!is_array($projects)) $projects = array();
			$sel_options['ts_project'] = $projects + array(lang('No project'));
		}
		// dont show [Export] button if app is not availible to the user or we are on php4
		$readonlys['export'] = !$GLOBALS['egw_info']['user']['apps']['importexport'] || (int) phpversion() < 5;

		return $etpl->exec(TIMESHEET_APP.'.timesheet_ui.index',$content,$sel_options,$readonlys,$preserv);
	}

	function js()
	{
		return '<script LANGUAGE="JavaScript">

		function timesheet_export()
		{
			egw_openWindowCentered(
				"'. $GLOBALS['egw']->link('/index.php','menuaction=importexport.uiexport.export_dialog&appname=timesheet&selection=use_all') . '",
				"Export",400,400);
			return false;
		}
		</script>';
	}
}
