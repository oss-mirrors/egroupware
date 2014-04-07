<?php
/**
 * EGroupware EMailAdmin - Setup
 *
 * @link http://www.egroupware.org
 * @author Klaus Leithoff <kl@stylite.de>
 * @author Ralf Becker <rb@stylite.de>
 * @package emailadmin
 * @subpackage setup
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

$setup_info['emailadmin']['name']      = 'emailadmin';
$setup_info['emailadmin']['title']     = 'EMailAdmin';
$setup_info['emailadmin']['version']   = '1.9.019';
$setup_info['emailadmin']['app_order'] = 10;
$setup_info['emailadmin']['enable']    = 2;
$setup_info['emailadmin']['index']     = 'emailadmin.emailadmin_ui.listProfiles';

$setup_info['emailadmin']['author'] = 'Klaus Leithoff';
$setup_info['emailadmin']['license']  = 'GPL';
$setup_info['emailadmin']['description'] =
	'A central Mailserver management application for EGroupWare. Completely rewritten by K.Leithoff in 10-2009';
$setup_info['emailadmin']['note'] =
	'';
$setup_info['emailadmin']['maintainer'] = array(
	'name'  => 'Leithoff, Klaus',
	'email' => 'kl@stylite.de'
);

$setup_info['emailadmin']['tables'][]	= 'egw_emailadmin';
$setup_info['emailadmin']['tables'][]	= 'egw_mailaccounts';
$setup_info['emailadmin']['tables'][]	= 'egw_ea_accounts';
$setup_info['emailadmin']['tables'][]	= 'egw_ea_credentials';
$setup_info['emailadmin']['tables'][]	= 'egw_ea_identities';
$setup_info['emailadmin']['tables'][]	= 'egw_ea_valid';
$setup_info['emailadmin']['tables'][]	= 'egw_ea_notifications';

/* The hooks this app includes, needed for hooks registration */
#$setup_info['emailadmin']['hooks'][] = 'preferences';
$setup_info['emailadmin']['hooks']['admin'] = 'emailadmin_hooks::admin';
$setup_info['emailadmin']['hooks']['edit_user'] = 'emailadmin_hooks::edit_user';
$setup_info['emailadmin']['hooks']['view_user'] = 'emailadmin_hooks::edit_user';
$setup_info['emailadmin']['hooks']['edit_group'] = 'emailadmin_hooks::edit_group';
$setup_info['emailadmin']['hooks']['group_manager'] = 'emailadmin_hooks::edit_group';
$setup_info['emailadmin']['hooks']['deleteaccount'] = 'emailadmin_hooks::deleteaccount';
$setup_info['emailadmin']['hooks']['deletegroup'] = 'emailadmin_hooks::deletegroup';
$setup_info['emailadmin']['hooks']['changepassword'] = 'emailadmin_bo::changepassword';
$setup_info['emailadmin']['hooks']['addaccount']	= 'emailadmin_hooks::accountHooks';
$setup_info['emailadmin']['hooks']['editaccount']	= 'emailadmin_hooks::accountHooks';

// SMTP and IMAP support
$setup_info['emailadmin']['hooks']['smtp_server_types'] = 'emailadmin_hooks::server_types';
$setup_info['emailadmin']['hooks']['imap_server_types'] = 'emailadmin_hooks::server_types';

/* Dependencies for this app to work */
$setup_info['emailadmin']['depends'][] = array(
	'appname'  => 'phpgwapi',
	'versions' => Array('1.7','1.8','1.9')
);
$setup_info['emailadmin']['depends'][] = array(
	'appname'  => 'egw-pear',
	'versions' => Array('1.8','1.9')
);
// installation checks for felamimail
$setup_info['emailadmin']['check_install'] = array(
	'' => array(
		'func' => 'pear_check',
		'from' => 'EMailAdmin',
	),
	'Auth_SASL' => array(
		'func' => 'pear_check',
		'from' => 'EMailAdmin',
	),
	'Net_Socket' => array(
		'func' => 'pear_check',
		'from' => 'EMailAdmin',
	),
	'Net_Sieve' => array(
		'func' => 'pear_check',
		'from' => 'EMailAdmin',
	),
	'Net_IMAP' => array(
		'func' => 'pear_check',
		'from' => 'EMailAdmin',
	),
	'imap' => array(
		'func' => 'extension_check',
		'from' => 'EMailAdmin',
	),
	'pear.horde.org/Horde_Imap_Client' => array(
		'func' => 'pear_check',
		'from' => 'EMailAdmin',
		'version' => '2.16.0',
	),
	'pear.horde.org/Horde_Nls' => array(
		'func' => 'pear_check',
		'from' => 'EMailAdmin',
		'version' => '2.0.3',
	),
	'pear.horde.org/Horde_Mail' => array(
		'func' => 'pear_check',
		'from' => 'EMailAdmin',
		'version' => '2.1.2',
	),
	'pear.horde.org/Horde_Smtp' => array(
		'func' => 'pear_check',
		'from' => 'EMailAdmin',
		'version' => '1.3.0',
	),
);

