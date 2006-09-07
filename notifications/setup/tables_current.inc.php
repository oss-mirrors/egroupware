<?php
/**
 * eGroupWare - Notifications
 *
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package notifications
 * @link http://www.egroupware.org
 * @author Cornelius Weiss <nelius@cwtech.de>
 * @version $Id:  $
 */

	$phpgw_baseline = array(
		'egw_notificationpopup' => array(
			'fd' => array(
				'account_id' => array('type' => 'varchar','precision' => '255','nullable' => False),
				'session_id' => array('type' => 'varchar','precision' => '255','nullable' => False),
				'message' => array('type' => 'longtext')
			),
			'pk' => array(),
			'fk' => array(),
			'ix' => array('account_id','session_id'),
			'uc' => array()
		)
	);
