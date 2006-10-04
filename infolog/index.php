<?php
/**
 * InfoLog
 *
 * @link http://www.egroupware.org
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @package infolog
 * @copyright (c) 2003-6 by Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

$GLOBALS['egw_info'] = array(
	'flags' => array(
		'currentapp'	=> 'infolog', 
		'noheader'		=> True,
		'nonavbar'		=> True,
	)
);
include('../header.inc.php');

include_once(EGW_INCLUDE_ROOT.'/infolog/setup/setup.inc.php');
if ($setup_info['infolog']['version'] != $GLOBALS['egw_info']['apps']['infolog']['version'])
{
	$GLOBALS['egw']->common->egw_header();
	parse_navbar();
	echo '<p style="text-align: center; color:red; font-weight: bold;">'.lang('Your database is NOT up to date (%1 vs. %2), please run %3setup%4 to update your database.',
		$setup_info['infolog']['version'],$GLOBALS['egw_info']['apps']['infolog']['version'],
		'<a href="../setup/">','</a>')."</p>\n";
	$GLOBALS['egw']->common->egw_exit();
}
unset($setup_info);

ExecMethod('infolog.uiinfolog.index','reset_action_view');

$GLOBALS['egw']->common->egw_exit();
