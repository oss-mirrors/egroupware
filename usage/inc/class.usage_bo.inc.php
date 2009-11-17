<?php
/**
 * EGgroupware - Usage statistic
 *
 * @link http://www.egroupware.org
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @package usage
 * @copyright (c) 2009 by Ralf Becker <RalfBecker-AT-outdoor-training.de>
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
			'usage_type' => $submitted['usage_type'],
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
	 * Display statistic
	 *
	 * @return string
	 */
	public function statistic()
	{
		return "<h2>Usage statistics get currently only collected, there's not yet any statistic to display.</h2>\n<h2>Come back soon ...</h2>\n".
			"<h2>".$this->db->select(self::MAIN_TABLE,'COUNT(*)','',__LINE__,__FILE__,false,'',self::APP_NAME)->fetchColumn()." submissions to the statistic so far.</h2>\n";
	}
}