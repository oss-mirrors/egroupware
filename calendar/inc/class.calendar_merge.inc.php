<?php
/**
 * Calendar - document merge
 *
 * @link http://www.egroupware.org
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @author Nathan Gray
 * @package calendar
 * @copyright (c) 2007-9 by Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @copyright 2011 Nathan Gray
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

/**
 * Calendar - document merge object
 */
class calendar_merge extends bo_merge
{
	/**
	 * Functions that can be called via menuaction
	 *
	 * @var array
	 */
	var $public_functions = array(
		'show_replacements'	=> true,
	);

	// Object for getting calendar info
	protected $bo;

	// Object used for getting resource info
	protected static $resources;

	/**
	 * Recognised relative days - used as a day table, like day_<n>
	 */
	protected static $relative = array(
		'today',
		'tomorrow',
		'yesterday',
	);

	/**
	 * If you use a range, these extra tags are available
	 */
	protected static $range_tags = array(
		'start'	=> 'Y-m-d',
		'end'	=> 'Y-m-d',
		'month'	=> 'F',
		'year'	=> 'Y'
	);

	/**
	 * Base query for all event searches
	 */
	protected $query = array();

	/**
	 * Stored IDs, if user passed in ID / events instead of date range
	 */
	protected $ids = array();

	/**
	 * Constructor
	 */
	function __construct()
	{
		parent::__construct();

		// overwrite global export-limit, if one is set for calendar/appointments
		$this->export_limit = bo_merge::getExportLimit('calendar');

		$this->bo = new calendar_boupdate();

		self::$range_tags['start'] = $GLOBALS['egw_info']['user']['preferences']['common']['dateformat'];
		self::$range_tags['end'] = $GLOBALS['egw_info']['user']['preferences']['common']['dateformat'];

		// Register table plugins
		$this->table_plugins['participant'] = 'participant';
		for($i = 0; $i < 7; $i++)
		{
			$this->table_plugins[date('l', strtotime("+$i days"))] = 'day_plugin';
		}
		for($i = 1; $i <= 31; $i++) {
			$this->table_plugins['day_'.$i] = 'day'; // Numerically by day number (1-31)
		}
		foreach(self::$relative as $day) {
			$this->table_plugins[$day] = 'day'; // Current day
		}
		$this->query = $GLOBALS['egw']->session->appsession('session_data','calendar');
		$this->query['users'] = explode(',', $this->query['owner']);
		$this->query['num_rows'] = -1;
	}

	/**
	 * Get replacements
	 *
	 * @param int|array $id event-id array with id,recur_date, or array with search parameters
	 * @param string &$content=null content to create some replacements only if they are used
	 * @return array|boolean
	 */
	protected function get_replacements($id,&$content=null)
	{
		$prefix = '';

		// List events ?
		if(is_array($id) && !$id['id'] && !$id[0]['id'])
		{
			$events = $this->bo->search($this->query + $id + array(
				'offset' => 0,
				'order' => 'cal_start',
			));
			if(strpos($content,'$$calendar/') !== false || strpos($content, '$$table/day') !== false)
			{
				array_unshift($events,false); unset($events[0]);	// renumber the array to start with key 1, instead of 0
				$prefix = 'calendar/%d';
			}
		}
		elseif ($id[0]['id'])
		{
			// Passed an array of events, to be handled like a date range
			$events = $id;
			$id = array('start' => PHP_INT_MAX, 'end' => 0);
			$this->ids = array();
			foreach($events as $event) {
				if($event['start'] && $event['start'] < $id['start']) $id['start'] = $event['start'];
				if($event['end'] && $event['end'] > $id['end']) $id['end'] = $event['end'];
				// Keep ids for future use
				$this->ids[]  = $event['id'];
			}
		}
		else
		{
			$events = array($id);
			$this->ids = $events;
		}
		// as this function allows to pass query- parameters, we need to check the result of the query against export_limit restrictions
		if (bo_merge::hasExportLimit($this->export_limit) && !bo_merge::is_export_limit_excepted() && count($events) > (int)$this->export_limit)
		{
			$err = lang('No rights to export more than %1 entries!',(int)$this->export_limit);
			throw new egw_exception_wrong_userinput($err);
		}
		$replacements = array();
		$n = 0;
		foreach($events as $event)
		{
			$values = $this->calendar_replacements($event,sprintf($prefix,++$n));
			if(is_array($id) && $id['start'])
			{
				foreach(self::$range_tags as $key => $format)
				{
					$value = date($format, $key == 'end' ? $id['end'] : $id['start']);
					if($key == 'month') $value = lang($value);
					$values["$\$range/$key$$"] = $value;
				}
			}
			$replacements += $values;
		}
		return $replacements;
	}

	/**
	 * Return replacements for the calendar
	 *
	 * @param int|array $id event-id or array with id/recur_date, or array with event info
	 * @param boolean $last_event_too=false also include information about the last event
	 * @return array
	 */
	public function calendar_replacements($id,$prefix = '')
	{
		$replacements = array();
		if(!is_array($id) || !$id['start']) {
			$event = $this->bo->read(is_array($id) ? $id['id'] : $id, is_array($id) ? $id['recur_date'] : null);
		} else {
			$event = $id;
		}
		$replacements['$$' . ($prefix ? $prefix . '/' : '') . 'calendar_id'. '$$'] = $event['id'];
		foreach($this->bo->event2array($event) as $name => $data)
		{
			if (substr($name,-4) == 'date') $name = substr($name,0,-4);
			$replacements['$$' . ($prefix ? $prefix . '/' : '') . 'calendar_'.$name . '$$'] = is_array($data['data']) ? implode(', ',$data['data']) : $data['data'];
		}
		if(!$replacements['$$'.($prefix ? $prefix . '/' : '') . 'calendar_recur_type$$'])
		{
			// Need to set it to '' if not set or previous record may be used
			$replacements['$$'.($prefix ? $prefix . '/' : '') . 'calendar_recur_type$$'] = '';
		}
		foreach(array('start','end') as $what)
		{
			foreach(array(
				'date' => $GLOBALS['egw_info']['user']['preferences']['common']['dateformat'],
				'day'  => 'l',
				'time' => (date('Ymd',$event['start']) != date('Ymd',$event['end']) ? $GLOBALS['egw_info']['user']['preferences']['common']['dateformat'].' ' : '') . ($GLOBALS['egw_info']['user']['preferences']['common']['timeformat'] == 12 ? 'h:i a' : 'H:i'),
			) as $name => $format)
			{
				$value = date($format,$event[$what]);
				if ($format == 'l') $value = lang($value);
				$replacements['$$' .($prefix ? $prefix.'/':'').'calendar_'.$what.$name.'$$'] = $value;
			}
		}
		$duration = ($event['end'] - $event['start'])/60;
		$replacements['$$'.($prefix?$prefix.'/':'').'calendar_duration$$'] = floor($duration/60).lang('h').($duration%60 ? $duration%60 : '');

		$custom = config::get_customfields('calendar');
		foreach($custom as $name => $field)
		{
			$replacements['$$'.($prefix?$prefix.'/':'').'#'.$name.'$$'] = $event['#'.$name];
		}
		return $replacements;
	}

	/**
	* Table plugin for event
	* Lists events for a certain day of the week.  Only works for one week at a time, so for multiple weeks,
	* use multiple date ranges.
	*
	* Use:
	* $$table/Monday$$ $$starttime$$ $$title$$ $$endtable$$
	* The day of the week may be language specific (date('l')).
	*
	* @param string $plugin (Monday-Sunday)
	* @param int/array date or date range
	* @param int $n Row number
	* @param string $repeat Text being repeated for each entry
	* @return array
	*/
	public function day_plugin($plugin,$date,$n,$repeat)
	{
		static $days;
		if(is_array($date) && !$date['start']) {
			// List of IDs
			if($date[0]['start']) {
				$id = array('start' => PHP_INT_MAX, 'end' => 0);
				foreach($date as $event) {
					if($event['start'] && $event['start'] < $id['start']) $id['start'] = $event['start'];
					if($event['end'] && $event['end'] > $id['end']) $id['end'] = $event['end'];
				}
				$date = $id;
			} else {
				$event = $this->bo->read(is_array($date) ? $date['id'] : $date, is_array($date) ? $date['recur_date'] : null);
				if(date('l',$event['start']) != $plugin) return array();
				$date = $event['start'];
			}
		}

		$_date = $date['start'] ? $date['start'] : $date;
		if($days[date('Ymd',$_date)][$plugin]) return $days[date('Ymd',$_date)][$plugin][$n];

		$events = $this->bo->search($this->query + array(
			'start' => $date['end'] ? $date['start'] : mktime(0,0,0,date('m',$_date),date('d',$_date),date('Y',$_date)),
			'end' => $date['end'] ? $date['end'] : mktime(23,59,59,date('m',$_date),date('d',$_date),date('Y',$_date)),
			'offset' => 0,
			'num_rows' => 20,
			'order' => 'cal_start',
			'daywise' => true
		));

		$days = array();
		$replacements = array();
		$time_format = $GLOBALS['egw_info']['user']['preferences']['common']['timeformat'] == 12 ? 'h:i a' : 'H:i';
		foreach($events as $day => $list) 
		{
			foreach($list as $key => $event)
			{
				if($this->ids && !in_array($event['id'], $this->ids)) continue;
				$start = egw_time::to($event['start'], 'array');
				$end = egw_time::to($event['end'], 'array');
				$replacements = $this->calendar_replacements($event);
				if($start['year'] == $end['year'] && $start['month'] == $end['month'] && $start['day'] == $end['day']) {
					$dow = date('l',$event['start']);
				} else {
					$dow = date('l', strtotime($day));
					// Fancy date+time formatting for multi-day events
					$replacements['$$calendar_starttime$$'] = date($time_format, $day == date('Ymd', $event['start']) ? $event['start'] : mktime(0,0,0,0,0,1));
					$replacements['$$calendar_endtime$$'] = date($time_format, $day == date('Ymd', $event['end']) ? $event['end'] : mktime(23,59,59,0,0,0));
				}
				
				$days[date('Ymd',$_date)][$dow][] = $replacements;
			}
			if(strpos($repeat, 'day/date') !== false || strpos($repeat, 'day/name') !== false) {
				$date_marker = array(
					'$$day/date$$' => date($GLOBALS['egw_info']['user']['preferences']['common']['dateformat'], strtotime($day)),
					'$$day/name$$' => lang(date('l', strtotime($day)))
				);
				if(!is_array($days[date('Ymd',$_date)][date('l',strtotime($day))])) {
					$blank = $this->calendar_replacements(array());
					foreach($blank as &$value) $value = '';
					$days[date('Ymd',$_date)][date('l',strtotime($day))][] = $blank;
				}
				$days[date('Ymd',$_date)][date('l',strtotime($day))][0] += $date_marker;
			}
		}
		return $days[date('Ymd',$_date)][$plugin][0];
	}

	/**
	* Table plugin for a certain date
	*
	* Can be either a particular date (2011-02-15) or a day of the month (15)
	*
	* @param string $plugin
	* @param int $id ID for this record
	* @param int $n Repeated row number
	* @param string $repeat Text being repeated for each entry
	* @return array
	*/
	public function day($plugin,$id,$n,$repeat)
	{
		static $days;

		// Figure out which day
		list($type, $which) = explode('_',$plugin);
		if($type == 'day' && $which)
		{
			if($id[0]['start'])
			{
				$dates = array('start' => PHP_INT_MAX, 'end' => 0);
				foreach($id as $event) {
					if($event['start'] && $event['start'] < $dates['start']) $dates['start'] = $event['start'];
					if($event['end'] && $event['end'] > $dates['end']) $dates['end'] = $event['end'];
				}
				$id = $dates;
			}
			$date = $this->bo->date2array($id['start']);
			$date['day'] = $which;
			$date = $this->bo->date2ts($date);
			if(is_array($id) && $id['start'] && ($date < $id['start'] || $date > $id['end'])) return array();
		}
		else
		{
			$date = strtotime($plugin);
		}
		if($type == 'day' && is_array($id) && !$id['start']) {
			$event = $this->bo->read(is_array($id) ? $id['id'] : $id, is_array($id) ? $id['recur_date'] : null);
			if($which && date('d',$event['start']) != $which) return array();
			if(date('Ymd',$date) != date('Ymd', $event['start'])) return array();
			return $n == 0 ? $this->calendar_replacements($event) : array();
		}
		
		// Use start for cache, in case of multiple months
		$_date = $id['start'] ? $id['start'] : $date;
		if($days[date('Ymd',$_date)][$plugin]) return $days[date('Ymd',$_date)][$plugin][$n];

		$events = $this->bo->search($this->query + array(
			'start' => $date,
			'end' => mktime(23,59,59,date('m',$date),date('d',$date),date('Y',$date)),
			'offset' => 0,
			'num_rows' => 20,
			'order' => 'cal_start',
			'daywise' => true
		));

		$replacements = array();
		$days = array();
		$time_format = $GLOBALS['egw_info']['user']['preferences']['common']['timeformat'] == 12 ? 'h:i a' : 'H:i';
		foreach($events as $day => $list) 
		{
			foreach($list as $key => $event)
			{
				if($this->ids && !in_array($event['id'], $this->ids)) continue;
				$start = egw_time::to($event['start'], 'array');
				$end = egw_time::to($event['end'], 'array');
				$replacements = $this->calendar_replacements($event);
				if($start['year'] == $end['year'] && $start['month'] == $end['month'] && $start['day'] == $end['day']) {
					$dow = date('l',$event['start']);
				} else {
					// Fancy date+time formatting for multi-day events
					$replacements['$$calendar_starttime$$'] = date($time_format, $day == date('Ymd', $event['start']) ? $event['start'] : mktime(0,0,0,0,0,1));
					$replacements['$$calendar_endtime$$'] = date($time_format, $day == date('Ymd', $event['end']) ? $event['end'] : mktime(23,59,59,0,0,0));
				}
				$days[date('Ymd',$_date)][$plugin][] = $replacements;
			}
			if(strpos($repeat, 'day/date') !== false || strpos($repeat, 'day/name') !== false) {
				$date_marker = array(
					'$$day/date$$' => date($GLOBALS['egw_info']['user']['preferences']['common']['dateformat'], strtotime($day)),
					'$$day/name$$' => lang(date('l', strtotime($day)))
				);
				if(!is_array($days[date('Ymd',$_date)][$plugin])) {
					$blank = $this->calendar_replacements(array());
					foreach($blank as &$value) $value = '';
					$days[date('Ymd',$_date)][$plugin][] = $blank;
				}
				$days[date('Ymd',$_date)][$plugin][0] += $date_marker;
			}
		}
		return $days[date('Ymd',$_date)][$plugin][0];
	}

	/**
	* Table plugin for participants
	*
	* Copied from eventmgr resources
	*
	* @param string $plugin
	* @param int $id
	* @param int $n
	* @return array
	*/
	public function participant($plugin,$id,$n)
	{
		if(!is_array($id) || !$id['start']) {
			$event = $this->bo->read(is_array($id) ? $id['id'] : $id, is_array($id) ? $id['recur_date'] : null);
		} else {
			$event = $id;
		}

		if(!is_array($event['participants']) || $n >= count($event['participants'])) return array();

		$participant = null;
		$status = null;
		$i = -1;
		foreach($event['participants'] as $participant => $status) {
			if(++$i == $n) break;
		}

		if(!$participant) return array();

		// Add some common information
		calendar_so::split_status($status,$quantity,$role);
		if ($role != 'REQ-PARTICIPANT')
		{
			if (isset($this->bo->roles[$role]))
			{
				$role = lang($this->bo->roles[$role]);
			}
			// allow to use cats as roles (beside regular iCal ones)
			elseif (substr($role,0,6) == 'X-CAT-' && ($cat_id = (int)substr($role,6)) > 0)
			{
				$role = $GLOBALS['egw']->categories->id2name($cat_id);
			}
			else
			{
				$role = lang(str_replace('X-','',$role));
			}
		}
		$info = array(
			'name'		=> $this->bo->participant_name($participant),
			'status'	=> $this->bo->verbose_status[$status],
			'quantity'	=> $quantity,
			'role'		=> $role
		);
		
		switch ($participant[0])
		{
			case 'c':
				$replacements = $this->contact_replacements(substr($participant,1),'');
				break;
			case 'r':
				if (is_null(self::$resources)) self::$resources = CreateObject('resources.resources_bo');
				if (($resource = self::$resources->read(substr($participant,1))))
				{
					foreach($resource as $name => $value)
					{
					    $replacements['$$'.$name.'$$'] = $value;
					}
				}
				break;
			default:
				if (is_numeric($participant) && ($contact = $GLOBALS['egw']->accounts->id2name($participant,'person_id')))
				{
					$replacements = $this->contact_replacements($contact,'');
				}
				break;
		}
		foreach($info as $name => $value)
		{
			$replacements['$$'.$name.'$$'] = $value;
		}
		return $replacements;
	}

	/**
	 * Generate table with replacements for the preferences
	 *
	 */
	public function show_replacements()
	{
		$GLOBALS['egw']->translation->add_app('calendar');
		$GLOBALS['egw_info']['flags']['app_header'] = lang('calendar').' - '.lang('Replacements for inserting events into documents');
		$GLOBALS['egw_info']['flags']['nonavbar'] = false;
		common::egw_header();

		echo "<table width='90%' align='center'>\n";
		echo '<tr><td colspan="4"><h3>'.lang('Calendar fields:')."</h3></td></tr>";

		foreach(array(
			'calendar_id' => lang('Calendar ID'),
			'calendar_title' => lang('Title'),
			'calendar_description' => lang('Description'),
			'calendar_participants' => lang('Participants'),
			'calendar_location' => lang('Location'),
			'calendar_start'    => lang('Start').': '.lang('Date').'+'.lang('Time'),
			'calendar_startday' => lang('Start').': '.lang('Weekday'),
			'calendar_startdate'=> lang('Start').': '.lang('Date'),
			'calendar_starttime'=> lang('Start').': '.lang('Time'),
			'calendar_end'      => lang('End').': '.lang('Date').'+'.lang('Time'),
			'calendar_endday'   => lang('End').': '.lang('Weekday'),
			'calendar_enddate'  => lang('End').': '.lang('Date'),
			'calendar_endtime'  => lang('End').': '.lang('Time'),
			'calendar_duration' => lang('Duration'),
			'calendar_category' => lang('Category'),
			'calendar_priority' => lang('Priority'),
			'calendar_updated'  => lang('Updated'),
			'calendar_recur_type' => lang('Repetition'),
			'calendar_access'   => lang('Access').': '.lang('public').', '.lang('private'),
			'calendar_owner'    => lang('Owner'),
		) as $name => $label)
		{
			if (in_array($name,array('start','end')) && $n&1)		// main values, which should be in the first column
			{
				echo "</tr>\n";
				$n++;
			}
			if (!($n&1)) echo '<tr>';
			echo '<td>{{'.$name.'}}</td><td>'.$label.'</td>';
			if ($n&1) echo "</tr>\n";
			$n++;
		}

		echo '<tr><td colspan="4"><h3>'.lang('Range fields').":</h3></td></tr>";
		echo '<tr><td colspan="4">'.lang('If you select a range (month, week, etc) instead of a list of entries, these extra fields are available').'</td></tr>';
		foreach(self::$range_tags as $name => $format)
		{
			echo '<tr><td>{{range/'.$name.'}}</td><td>'.lang($name)."</td></tr>\n";
		}
		echo '<tr><td colspan="4"><h3>'.lang('Custom fields').":</h3></td></tr>";
		$custom = config::get_customfields('calendar');
		foreach($custom as $name => $field)
		{
			echo '<tr><td>{{#'.$name.'}}</td><td colspan="3">'.$field['label']."</td></tr>\n";
		}

		echo '<tr><td colspan="4"><h3>'.lang('Participant table').":</h3></td></tr>";
		echo '<tr><td colspan="4">{{table/participant}} ... </td></tr>';
		echo '<tr><td>{{name}}</td><td>'.lang('name').'</td></tr>';
		echo '<tr><td>{{role}}</td><td>'.lang('role').'</td></tr>';
		echo '<tr><td>{{quantity}}</td><td>'.lang('quantity').'</td></tr>';
		echo '<tr><td>{{status}}</td><td>'.lang('status').'</td></tr>';
		echo '<tr><td colspan="4">{{endtable}}</td></tr>';
		
		echo '<tr style="vertical-align:top"><td colspan="2"><table >';
		echo '<tr><td><h3>'.lang('Day of week tables').":</h3></td></tr>";
		$days = array();
		for($i = 0; $i < 7; $i++)
		{
			$days[date('N',strtotime("+$i days"))] = date('l',strtotime("+$i days"));
		}
		ksort($days);
		foreach($days as $day)
		{
			echo '<tr><td>{{table/'.$day. '}} ... {{endtable}}</td></tr>';
		}
		echo '</table></td><td colspan="2"><table >';
		echo '<tr><td><h3>'.lang('Daily tables').":</h3></td></tr>";
		foreach(self::$relative as $key => $value) {
			echo '<tr><td>{{table/'.$value. '}} ... {{endtable}}</td></tr>';
		}
		echo '<tr><td>{{table/day_n}} ... {{endtable}}</td><td>1 <= n <= 31</td></tr>';
		echo '</table></td></tr>';
		echo '<tr><td>{{day/date}}</td><td colspan="3">'.lang('Date for the day of the week, available for the first entry inside each day of week or daily table inside the selected range.').'</td></tr>';
		echo '<tr><td>{{day/name}}</td><td colspan="3">'.lang('Name of the week (ex: Monday), available for the first entry inside each day of week or daily table inside the selected range.').'</td></tr>';

		echo '<tr><td colspan="4"><h3>'.lang('General fields:')."</h3></td></tr>";
		foreach(array(
			'date' => lang('Date'),
			'user/n_fn' => lang('Name of current user, all other contact fields are valid too'),
			'user/account_lid' => lang('Username'),
			'pagerepeat' => lang('For serial letter use this tag. Put the content, you want to repeat between two Tags.'),
			'label' => lang('Use this tag for addresslabels. Put the content, you want to repeat, between two tags.'),
			'labelplacement' => lang('Tag to mark positions for address labels'),
			'IF fieldname' => lang('Example {{IF n_prefix~Mr~Hello Mr.~Hello Ms.}} - search the field "n_prefix", for "Mr", if found, write Hello Mr., else write Hello Ms.'),
			'NELF' => lang('Example {{NELF role}} - if field role is not empty, you will get a new line with the value of field role'),
			'NENVLF' => lang('Example {{NELFNV role}} - if field role is not empty, set a LF without any value of the field'),
			'LETTERPREFIX' => lang('Example {{LETTERPREFIX}} - Gives a letter prefix without double spaces, if the title is emty for  example'),
			'LETTERPREFIXCUSTOM' => lang('Example {{LETTERPREFIXCUSTOM n_prefix title n_family}} - Example: Mr Dr. James Miller'),
			) as $name => $label)
		{
			echo '<tr><td>{{'.$name.'}}</td><td colspan="3">'.$label."</td></tr>\n";
		}

		echo "</table>\n";

		common::egw_footer();
	}
}
