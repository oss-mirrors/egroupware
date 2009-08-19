<?php
/**
 * eGroupWare - Setup
 * http://www.egroupware.org
 * Created by eTemplates DB-Tools written by ralfbecker@outdoor-training.de
 *
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package phpfreechat
 * @subpackage setup
 * @version $Id$
 */


$phpgw_baseline = array(
	'egw_phpfreechat' => array(
		'fd' => array(
			'server' => array('type' => 'varchar','precision' => '32','nullable' => False),
			'group' => array('type' => 'varchar','precision' => '64','nullable' => False),
			'subgroup' => array('type' => 'varchar','precision' => '128','nullable' => False),
			'leaf' => array('type' => 'varchar','precision' => '128','nullable' => False),
			'leafvalue' => array('type' => 'text'),
			'timestamp' => array('type' => 'int','precision' => '4','nullable' => False,'default' => '0')
		),
		'pk' => array('group','subgroup','leaf'),
		'fk' => array(),
		'ix' => array(array('server','group','subgroup','timestamp')),
		'uc' => array()
	)
);
