<?php
/**
 * EGgroupware - Usage statistic
 *
 * @link http://www.egroupware.org
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @package usage
 * @copyright (c) 2009/10 by Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

/**
 * Usage statistic: business object
 *
 */
class usage_bo extends so_sql
{
	/**
	 * Application name
	 */
	const APP_NAME = 'usage';
	/**
	 * Name of main table
	 */
	const MAIN_TABLE = 'egw_usage';
	/**
	 * Name of application table
	 */
	const APP_TABLE = 'egw_usage_apps';

	/**
	 * Minimal time in seconds between two submission for the same IP
	 */
	const MAX_SUBMISSION_RATE_IP = 3600;	// = 1 hour

	/**
	 * Minimal time in seconds between two submission for the same submit ID
	 */
	const MAX_SUBMISSION_RATE_ID = 2419200;	// = 28 days

	/**
	 * Constructor
	 *
	 */
	public function __construct()
	{
		parent::__construct(self::APP_NAME,self::MAIN_TABLE);
	}

	/**
	 * Receive submission
	 *
	 * @throws egw_exception with error mesage
	 */
	public function receive()
	{
		$submitted = $_POST['exec'];
		unset($submitted['postpone']);
		unset($submitted['submit']);

		$this->check_submit($submitted);

		$this->data = array(
			'usage_country' => $submitted['country'] ? $submitted['country'] : null,
			'usage_type' => $submitted['usage_type'] == 'comercial' ? 'commercial' : $submitted['usage_type'],
			'usage_users' => $submitted['users'],
			'usage_sessions' => $submitted['sessions'],
			'usage_version' => $submitted['version'],
			'usage_os' => $submitted['os'],
			'usage_php' => $submitted['php'],
			'usage_install_type' => $submitted['install_type'],
			'usage_ip_hash' => sha1(egw_session::getuser_ip()),
			'usage_submit_id' => $submitted['submit_id'] ? $submitted['submit_id'] : null,
		);
		if (($err = $this->save()))
		{
			throw new egw_exception_db(__METHOD__."() error storing submission!");
		}
		foreach(preg_split('/[\r\n]+/',$submitted['apps']) as $line)
		{
			$app = $percent = $records = null;
			list($app,$percent,$records) = explode(':',$line);
			if (empty($app)) continue;

			$users = (int) round((float)$percent * $this->data['usage_users'] / 100.0);
			//echo "<p>$app: $percent = $users, records=$records</p>\n";

			$this->db->insert(self::APP_TABLE,array(
				'usage_app_users' => $users ? $users : null,
				'usage_app_records' => $records ? $records : null,
			),array(
				'usage_id' => $this->data['usage_id'],
				'usage_app_name' => $app,
			),__LINE__,__FILE__,self::APP_NAME);
		}
		// update statistics data in cache
		$stats = egw_cache::setInstance('usage','stats',$this->calc_statistic());

		return $this->acknowledge();
	}

	/**
	 * Acknowledge submission
	 *
	 * @return string
	 */
	public function acknowledge()
	{
		return '<p>'.lang('Thank you for contributing to EGroupware usage statistic.')."</p>\n".
			'<p>'.lang('Your submission is number %1.',$this->data['usage_id'])."</p>\n";
	}

	/**
	 * Check if submission is ok:
	 * - IP is not submitting to many
	 * - submit_id has not submitted in last month
	 *
	 * @param array $submitted
	 * @throws egw_exception with error mesage
	 */
	public function check_submit($submitted)
	{
		foreach(array('usage_type','users','sessions','version','os','php','install_type') as $name)
		{
			if (empty($submitted[$name]))
			{
				throw new egw_exception_wrong_userinput(lang("Required information missing ($name)! --> report ignored"));
			}
		}
		//_debug_array($submitted);
		if (($last_submission = $this->db->select(self::MAIN_TABLE,'MAX(usage_submitted)',array(
			'usage_ip_hash' => sha1($ip=egw_session::getuser_ip()),
		),__LINE__,__FILE__,false,'',self::APP_NAME)->fetchColumn()) && ($last_submission = strtotime($last_submission)) &&
			time() - $last_submission < self::MAX_SUBMISSION_RATE_IP)
		{
			throw new egw_exception_wrong_userinput(lang("This IP address '$ip' already submitted a report! --> report ignored"));
		}
		if (!empty($submitted['submit_id']) && ($last_submission = $this->db->select(self::MAIN_TABLE,'MAX(usage_submitted)',array(
			'usage_submit_id' => $submitted['submit_id'],
		),__LINE__,__FILE__,false,'',self::APP_NAME)->fetchColumn()) && ($last_submission = strtotime($last_submission)) &&
			time() - $last_submission < self::MAX_SUBMISSION_RATE_ID)
		{
			throw new egw_exception_wrong_userinput(lang("This submit ID already submitted it's monthly report! --> report ignored"));
		}
	}

	/**
	 * Render statistic using cached version of data
	 *
	 * @return string
	 */
	public function statistic()
	{
		translation::add_app('admin');	// contains translations of our labels

		$sel_options['usage_type'] = array(
			'commercial'   => lang('Commercial: all sorts of companies'),
			'governmental' => lang('Governmental: incl. state or municipal authorities or services'),
			'educational' => lang('Educational: Universities, Schools, ...'),
			'non-profit'  => lang('Non profit: Clubs, associations, ...'),
			'personal'    => lang('Personal: eg. within a family'),
			'other'       => lang('Other'),
		);
		$sel_options['usage_install_type'] = array(
			'archive' => lang('Archive: zip or tar'),
			'package' => lang('RPM or Debian package'),
			'svn'     => lang('Subversion checkout'),
			'other'   => lang('Other'),
		);
		// get statistics data from cache (if set)
		$stats = egw_cache::getInstance('usage','stats',array($this,'calc_statistic'));
		//$stats = $this->calc_statistic();

		$all_time_total = $stats['all_time_total']; unset($stats['all_time_total']);
		$total_30d = $stats['total_30d']; unset($stats['total_30d']);

		$content .= "<h1>Evaluation of $total_30d submissions of last 30 days:</h1>\n";

		$content .= '<style type="text/css">
	.usageList {
		margin: 0;
		padding: 0;
		max-height: 4em;
		overflow-y: auto;
		overflow-x: hidden;
	}
	.usageList li {
		line-height: 133%;
		white-space: nowrap;
	}
</style>
';
		$content .= "<table width='100%'>\n";
		/*$content .= "<thead>\n\t<tr>\n";
		$content .= "\t\t<th>".lang('Category')."</th>\n";
		$content .= "\t\t<th>".lang('Answers')."</th>\n";
		$content .= "\t</tr>\n</thead>\n";*/
		$content .= "<tbody\n";
		foreach(array(
			'usage_country' => 'Country',
			'usage_type' => 'Usage',
			'usage_users' => 'Number of users',
			'usage_sessions' => 'Sessions last 30 days',
			'usage_version' => 'EGroupware Version',
			'usage_os' => 'Operating System',
			'usage_php' => 'PHP Version',
			'usage_install_type' => 'Installation Type',
		) as $name => $label)
		{
			$colspan = !isset($stats[$name.'2']) ? 'colspan="2"' : ' width="200"';
			$content .= "\t<tr valign='top'>\n\t\t<td><b>".lang($label)."</b></td>\n\t\t<td $colspan>\n\t\t\t<ol class='usageList'>\n";
			foreach($stats[$name] as $row)
			{
				switch($name)
				{
					case 'usage_country':
						if (!isset($country)) $country = new country();
						$row['value'] = empty($row['value']) ? lang('International use') : $country->get_full_name($row['value']);
						break;
					case 'usage_type':
					case 'usage_install_type':
						$row['value'] = $sel_options[$name][$row['value']];
						break;
				}
				$content .= "\t\t\t\t<li>"./*$row['count'].' = '.*/number_format(100.0*$row['count']/$total_30d,1).'% &nbsp; '.$row['value']."</li>\n";
			}
			$content .= "\t\t\t</ol>\n\t\t</td>\n";

			if (isset($stats[$name.'2']))
			{
				$content .= "\t\t<td>\n";
				if (is_array($stats[$name.'2']))
				{
					$content .= "\t\t\t<ol class='usageList'>\n";
					foreach($stats[$name.'2'] as $row)
					{
						$content .= "\t\t\t\t<li>"./*$row['count'].' = '.*/number_format(100.0*$row['count']/$total_30d,1).'%&nbsp; '.$row['value']."</li>\n";
					}
					$content .= "\t\t\t</ol>\n";
				}
				else
				{
					$content .= lang('Total sum').': '.number_format($stats[$name.'2'],0,'',' ');
				}
				$content .= "\t\t</td>\n";
			}
			$content .= "\t</tr>\n";
		}
		$content .= "</tbody>\n</table>\n";

		$content .= "<h2>$all_time_total submissions to the statistic since it's start.</h2>\n";

		return $content;
	}

	/**
	 * Calculate statistic
	 *
	 * @todo App statistic like:
	 * SELECT usage_app_name, count( *  ) , sum( usage_app_users ) , sum( usage_app_records )
	 * FROM egw_usage_apps
	 * JOIN egw_usage
	 * USING ( usage_id )
	 * WHERE usage_submitted >= '2010-03-03'
	 * GROUP BY usage_app_name
	 * ORDER BY sum( usage_app_users ) DESC
	 *
	 * @return array
	 */
	public function calc_statistic()
	{
		$stats = array();
		$stats['all_time_total'] = $this->db->select(self::MAIN_TABLE,'COUNT(*)','',__LINE__,__FILE__,false,'',self::APP_NAME)->fetchColumn();

		$stat_limit = time() - 30*24*60*60;	// 30 days back
		$stat_limit_sql = 'usage_submitted >= '.$this->db->quote($stat_limit,'timestamp');
		$stats['total_30d'] = $this->db->select(self::MAIN_TABLE ,'COUNT(*)',$stat_limit_sql,__LINE__,__FILE__,false,'',self::APP_NAME)->fetchColumn();

		foreach(array(
			'usage_country',
			'usage_type',
			'usage_users',
			'usage_sessions',
			'usage_version',
			'usage_os',
			'usage_php',
			'usage_install_type',
		) as $name)
		{
			$group_by = $col = $name;
			$group_by2 = $col2 = '';
			if ($col == 'usage_users' || $col == 'usage_sessions')
			{
				$col2 = 'SUM('.$col.')';
				$max = $this->db->select(self::MAIN_TABLE ,"MAX($col)",$stat_limit_sql,__LINE__,__FILE__,false,'',self::APP_NAME)->fetchColumn();
				$max_fraction = ceil($max / 20);
				$group_by = "TRUNCATE($col/$max_fraction,0)";
				$col = "CONCAT($group_by*$max_fraction,' - ',($group_by+1)*$max_fraction)";
			}
			elseif($col == 'usage_php')
			{
				$group_by2 = $col2 = "SUBSTR($col,LOCATE(':',$col)+1,99)";
				$group_by = $col = "LEFT($col,CASE WHEN LOCATE(':',$col) < LOCATE('-',$col) OR LOCATE('-',$col)=0 THEN LOCATE(':',$col) ELSE LOCATE('-',$col) END-1)";
			}
			elseif($col == 'usage_os')
			{
				$group_by2 = $col2 = $col;
				$group_by = $col = "CASE WHEN LOCATE('Linux',$col) > 0 THEN 'Linux' ELSE $col END";
			}
			elseif($col == 'usage_version')
			{
				$group_by = $col = "CASE WHEN RIGHT($col,3)='EPL' THEN CONCAT('EPL ',SUBSTR($col,LOCATE(' ',$col),4)) ELSE $col END";
			}
			foreach($this->db->select(self::MAIN_TABLE,$col.' AS value,COUNT(*) AS count',$stat_limit_sql,__LINE__,__FILE__,false,'GROUP BY '.$group_by.' ORDER BY COUNT(*) DESC',self::APP_NAME) as $row)
			{
				$stats[$name][] = $row;
			}

			if (substr($col2,0,4) == 'SUM(')
			{
				$stats[$name.'2'] = $this->db->select(self::MAIN_TABLE,$col2,$stat_limit_sql,__LINE__,__FILE__)->fetchColumn();
			}
			elseif (!empty($col2))
			{
				foreach($this->db->select(self::MAIN_TABLE,$col2.' AS value,COUNT(*) AS count',$stat_limit_sql,__LINE__,__FILE__,false,'GROUP BY '.$group_by2.' ORDER BY COUNT(*) DESC',self::APP_NAME) as $row)
				{
					$stats[$name.'2'][] = $row;
				}
			}
		}
		return $stats;
	}
}