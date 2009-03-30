<?php
/**
 * eGroupWare - PEAR
 *
 * @link http://www.egroupware.org
 * @package egw-pear
 * @subpackage setup
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

$setup_info['egw-pear']['name']		= 'egw-pear';
$setup_info['egw-pear']['title']	= 'egw-pear';
$setup_info['egw-pear']['version']	= '1.4.000';
$setup_info['egw-pear']['app_order']	= 99;
$setup_info['egw-pear']['enable']	= 2;

$setup_info['egw-pear']['author'] = array(
	'name' => 'PEAR - PHP Extension and Application Repository',
	'url'  => 'http://pear.php.net',
);
$setup_info['egw-pear']['license']	= 'PHP';
$setup_info['egw-pear']['description']	=
	'A place for PEAR modules modified for eGroupWare.';

$setup_info['egw-pear']['note'] 	=
	'This application is a place for PEAR modules used by eGroupWare, which are NOT YET available from pear,
	because we patched them somehow and the PEAR modules are not released upstream.
	This application is under the LGPL license because the GPL is not compatible with the PHP license.
	If the modules are available from PEAR they do NOT belong here anymore.';

$setup_info['egw-pear']['maintainer']	= array(
	'name'  => 'eGroupWare coreteam',
	'email' => 'egroupware-developers@lists.sourceforge.net',
);

// installation checks for egw-pear
$setup_info['egw-pear']['check_install'] = array(
	// we need pear itself to be installed
	'' => array(
		'func' => 'pear_check',
		'from' => 'FeLaMiMail',
	),
	// Net_Socket is required from Net_IMAP & Net_Sieve
	'Net_Socket' => array(
		'func' => 'pear_check',
		'from' => 'FeLaMiMail',
	),
);
