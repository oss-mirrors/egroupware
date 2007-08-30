<?php
/**
 * eGroupWare - GroupDAV access
 *
 * Using the PEAR HTTP/WebDAV/Server class (which need to be installed!)
 * 
 * @link http://www.egroupware.org
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package icalsrv
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @copyright (c) 2007 by Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @version $Id$
 */

/**
 * check if the given user has access
 * 
 * Create a session or if the user has no account return authenticate header and 401 Unauthorized
 *
 * @param array &$account
 * @return int session-id
 */
function check_access(&$account)
{
	$account = array(
		'login'  => $_SERVER['PHP_AUTH_USER'],
		'passwd' => $_SERVER['PHP_AUTH_PW'],
		'passwd_type' => 'text',
	);
	if (!($sessionid = $GLOBALS['egw']->session->create($account)))
	{
		header('WWW-Authenticate: Basic realm="eGroupWare GroupDAV"');
        header("HTTP/1.1 401 Unauthorized");
        header("X-WebDAV-Status: 401 Unauthorized", true);
        exit;
	}
	return $sessionid;
}

$GLOBALS['egw_info']['flags'] = array(
	'disable_Template_class' => True,
	'noheader'  => True,
	'currentapp' => 'icalsrv',
	'autocreate_session_callback' => 'check_access',
);
// if you move this file somewhere else, you need to adapt the path to the header!
include('../header.inc.php');

ExecMethod('icalsrv.icalsrv_groupdav.ServeRequest');
