<?php
/**
 * eGroupWare - Calendar setup
 *
 * @link http://www.egroupware.org
 * @package calendar
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

$phpgw_baseline = array(
	'egw_cal' => array(
		'fd' => array(
			'cal_id' => array('type' => 'auto','nullable' => False),
			'cal_uid' => array('type' => 'varchar','precision' => '255','nullable' => False,'comment' => 'unique id of event(-series)'),
			'cal_owner' => array('type' => 'int','precision' => '4','nullable' => False,'comment' => 'event owner / calendar'),
			'cal_category' => array('type' => 'varchar','precision' => '30','comment' => 'category id'),
			'cal_modified' => array('type' => 'int','precision' => '8','comment' => 'ts of last modification'),
			'cal_priority' => array('type' => 'int','precision' => '2','nullable' => False,'default' => '2'),
			'cal_public' => array('type' => 'int','precision' => '2','nullable' => False,'default' => '1','comment' => '1=public, 0=private event'),
			'cal_title' => array('type' => 'varchar','precision' => '255','nullable' => False,'default' => '1'),
			'cal_description' => array('type' => 'text'),
			'cal_location' => array('type' => 'varchar','precision' => '255'),
			'cal_reference' => array('type' => 'int','precision' => '4','nullable' => False,'default' => '0','comment' => 'cal_id of series for exception'),
			'cal_modifier' => array('type' => 'int','precision' => '4','comment' => 'user who last modified event'),
			'cal_non_blocking' => array('type' => 'int','precision' => '2','default' => '0','comment' => '1 for non-blocking events'),
			'cal_special' => array('type' => 'int','precision' => '2','default' => '0'),
			'cal_etag' => array('type' => 'int','precision' => '4','default' => '0','comment' => 'etag for optimistic locking'),
			'cal_creator' => array('type' => 'int','precision' => '4','nullable' => False,'comment' => 'creating user'),
			'cal_created' => array('type' => 'int','precision' => '8','nullable' => False,'comment' => 'creation time of event'),
			'cal_recurrence' => array('type' => 'int','precision' => '8','nullable' => False,'default' => '0','comment' => 'cal_start of original recurrence for exception')
		),
		'pk' => array('cal_id'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),
	'egw_cal_holidays' => array(
		'fd' => array(
			'hol_id' => array('type' => 'auto','nullable' => False),
			'hol_locale' => array('type' => 'char','precision' => '2','nullable' => False),
			'hol_name' => array('type' => 'varchar','precision' => '50','nullable' => False),
			'hol_mday' => array('type' => 'int','precision' => '8','nullable' => False,'default' => '0'),
			'hol_month_num' => array('type' => 'int','precision' => '8','nullable' => False,'default' => '0'),
			'hol_occurence' => array('type' => 'int','precision' => '8','nullable' => False,'default' => '0'),
			'hol_dow' => array('type' => 'int','precision' => '8','nullable' => False,'default' => '0'),
			'hol_observance_rule' => array('type' => 'int','precision' => '8','nullable' => False,'default' => '0')
		),
		'pk' => array('hol_id'),
		'fk' => array(),
		'ix' => array('hol_locale'),
		'uc' => array()
	),
	'egw_cal_repeats' => array(
		'fd' => array(
			'cal_id' => array('type' => 'int','precision' => '4','nullable' => False),
			'recur_type' => array('type' => 'int','precision' => '2','nullable' => False),
			'recur_enddate' => array('type' => 'int','precision' => '8'),
			'recur_interval' => array('type' => 'int','precision' => '2','default' => '1'),
			'recur_data' => array('type' => 'int','precision' => '2','default' => '1'),
			'recur_exception' => array('type' => 'text','comment' => 'comma-separated start timestamps of exceptions')
		),
		'pk' => array('cal_id'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),
	'egw_cal_user' => array(
		'fd' => array(
			'cal_id' => array('type' => 'int','precision' => '4','nullable' => False),
			'cal_recur_date' => array('type' => 'int','precision' => '8','default' => '0'),
			'cal_user_type' => array('type' => 'varchar','precision' => '1','nullable' => False,'default' => 'u'),
			'cal_user_id' => array('type' => 'varchar','precision' => '128','nullable' => False),
			'cal_status' => array('type' => 'char','precision' => '1','default' => 'A'),
			'cal_quantity' => array('type' => 'int','precision' => '4','default' => '1')
		),
		'pk' => array('cal_id','cal_recur_date','cal_user_type','cal_user_id'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),
	'egw_cal_extra' => array(
		'fd' => array(
			'cal_id' => array('type' => 'int','precision' => '4','nullable' => False),
			'cal_extra_name' => array('type' => 'varchar','precision' => '40','nullable' => False),
			'cal_extra_value' => array('type' => 'varchar','precision' => '255','nullable' => False,'default' => '')
		),
		'pk' => array('cal_id','cal_extra_name'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),
	'egw_cal_dates' => array(
		'fd' => array(
			'cal_id' => array('type' => 'int','precision' => '4','nullable' => False),
			'cal_start' => array('type' => 'int','precision' => '8','nullable' => False),
			'cal_end' => array('type' => 'int','precision' => '8','nullable' => False)
		),
		'pk' => array('cal_id','cal_start'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	)
);
