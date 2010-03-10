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
 * Check if we allow anon access and with which creditials
 *
 * @param array &$anon_account anon account_info with keys 'login', 'passwd' and optional 'passwd_type'
 * @return boolean true if we allow anon access, false otherwise
 */
function check_anon_access(&$anon_account)
{
	$anon_account = array(
		'login'  => 'anonymous',
		'passwd' => 'anonymous',
		'passwd_type' => 'text',
	);
	return true;
}

// uncomment the next line to use an EGw domain different from the first one defined in your header.inc.php
// and of cause change the name accordingly ;-)
// $GLOBALS['egw_info']['user']['domain'] = $GLOBALS['egw_info']['server']['default_domain'] = 'developers';

$GLOBALS['egw_info']['flags'] = array(
	'disable_Template_class' => True,
	'currentapp' => 'usage',
	'autocreate_session_callback' => isset($_POST['exec']) ? 'check_anon_access' : null,
);
include('../header.inc.php');

try
{
	$usage = new usage_bo();
	if (isset($_POST['exec']))
	{
		echo $usage->receive();
	}
	echo $usage->statistic();
}
catch(Exception $e)
{
	echo "<groupbox><legend style='font-size:150%; font-weight: bold;'> ".lang('Error').": </ledgend><h1>".$e->getMessage()."</h1></groupbox>\n";
}
common::egw_footer();