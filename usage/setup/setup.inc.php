<?php
/**
 * EGroupware - Usage statistic
 *
 * @link http://www.egroupware.org
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package usage
 * @subpackage setup
 * @version $Id$
 */

$setup_info['usage']['name']      = 'usage';
$setup_info['usage']['version']   = '1.7.001';
$setup_info['usage']['app_order'] = 1;
$setup_info['usage']['tables']    = array('egw_usage','egw_usage_apps');
$setup_info['usage']['enable']    = 1;

$setup_info['usage']['author'][] = $setup_info['usage']['maintainer'][] = array(
	'name'  => 'Ralf Becker',
	'email' => 'ralfbecker@outdoor-training.de',
	'url'   => 'www.stylite.de'
);

$setup_info['usage']['license']  = 'GPL';
$setup_info['usage']['description'] = 'EGroupware usage statistic';

// The hooks this app includes, needed for hooks registration
$setup_info['usage']['hooks'] = array();

/* Dependencies for this app to work */
$setup_info['usage']['depends'][] = array(
	'appname' => 'phpgwapi',
	'versions' => Array('1.7','1.8','1.9')
);
