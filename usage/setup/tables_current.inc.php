<?php
/**
 * eGroupWare - Setup
 * http://www.egroupware.org
 * Created by eTemplates DB-Tools written by ralfbecker@outdoor-training.de
 *
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package usage
 * @subpackage setup
 * @version $Id$
 */


$phpgw_baseline = array(
	'egw_usage' => array(
		'fd' => array(
			'usage_id' => array('type' => 'auto','nullable' => False,'comment' => 'Primary key'),
			'usage_country' => array('type' => 'varchar','precision' => '2','comment' => 'Country or NULL for multinational'),
			'usage_type' => array('type' => 'varchar','precision' => '16','comment' => 'comercial, govermental, educational, non-profit, personal or other'),
			'usage_users' => array('type' => 'int','precision' => '4','comment' => 'number of accounts'),
			'usage_sessions' => array('type' => 'int','precision' => '4','comment' => 'number of sessions last month'),
			'usage_version' => array('type' => 'varchar','precision' => '16','comment' => 'API version'),
			'usage_os' => array('type' => 'varchar','precision' => '128','comment' => 'server os'),
			'usage_php' => array('type' => 'varchar','precision' => '32','comment' => '5.2.11: apache2handler'),
			'usage_install_type' => array('type' => 'varchar','precision' => '32','comment' => 'archive, package (rpm, deb), svn, other'),
			'usage_ip_hash' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'sha1 hash of ip-addr'),
			'usage_submitted' => array('type' => 'timestamp','nullable' => False,'default' => 'current_timestamp','comment' => 'Submission time'),
			'usage_submit_id' => array('type' => 'varchar','precision' => '40','comment' => 'sha1 hash of install_id or null')
		),
		'pk' => array('usage_id'),
		'fk' => array(),
		'ix' => array('usage_submit_id'),
		'uc' => array()
	),
	'egw_usage_apps' => array(
		'fd' => array(
			'usage_id' => array('type' => 'int','precision' => '4','nullable' => False,'comment' => 'foreing key to egw_usage'),
			'usage_app_name' => array('type' => 'varchar','precision' => '32','nullable' => False,'comment' => 'app name'),
			'usage_app_users' => array('type' => 'int','precision' => '4','comment' => 'users with rights to use it'),
			'usage_app_records' => array('type' => 'int','precision' => '4','comment' => 'number of records in db')
		),
		'pk' => array('usage_id','usage_app_name'),
		'fk' => array(),
		'ix' => array('usage_app_name'),
		'uc' => array()
	)
);
