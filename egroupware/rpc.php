<?php
/**
 * $Horde: horde/rpc.php,v 1.31 2005/01/03 14:34:43 jan Exp $
 *
 * Copyright 2002-2005 Jan Schneider <jan@horde.org>
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 */

error_reporting(E_ALL & ~E_NOTICE);
@define('AUTH_HANDLER', true);
@define('HORDE_BASE', dirname(__FILE__).'/phpgwapi/inc/horde/');
require_once HORDE_BASE . '/lib/core.php';
require_once 'Horde/RPC.php';

$GLOBALS['egw_info'] = array();
$GLOBALS['egw_info']['flags'] = array(
	'currentapp'			=> 'login',
	'noheader'			=> True,
	'nonavbar'			=> True,
	'disable_Template_class'	=> True
);
include('./header.inc.php');

// SyncML works currently only with PHP sessions
if($GLOBALS['egw_info']['server']['sessions_type'] == 'db')
{
	error_log('SyncML support is currently not available with DB sessions. Please switch to PHP sessions in header.inc.php.');
	exit;
}

if(ini_get('mbstring.func_overload') != 0) {
	error_log('You need to set mbstring.func_overload to 0 for rpc.php.');
	exit;
}

if(version_compare(PHP_VERSION, '5.0.0') < 0) {
	error_log('eGroupWare\'s SyncML server requires PHP5. Please update to PHP5 if you want to make use of SyncML.');
	exit;
}

$config =& CreateObject('phpgwapi.config','syncml');
$config->read_repository();
$GLOBALS['config_syncml'] =& $config->config_data;
unset($config);

/* Look at the Content-type of the request, if it is available, to try
 * and determine what kind of request this is. */
$input = null;
$params = null;

if (!empty($_SERVER['CONTENT_TYPE'])) {
    if (strpos($_SERVER['CONTENT_TYPE'], 'application/vnd.syncml+xml') !== false) {
        $serverType = 'syncml';
    } elseif (strpos($_SERVER['CONTENT_TYPE'], 'application/vnd.syncml+wbxml') !== false) {
        $serverType = 'syncml_wbxml';
    } elseif (strpos($_SERVER['CONTENT_TYPE'], 'text/xml') !== false) {
        $input = Horde_RPC::getInput();
        /* Check for SOAP namespace URI. */
        if (strpos($input, 'http://schemas.xmlsoap.org/soap/envelope/') !== false) {
            $serverType = 'soap';
        } else {
            $serverType = 'xmlrpc';
        }
    } else {
        header('HTTP/1.0 501 Not Implemented');
        exit;
    }
} else {
    $serverType = 'soap';
}

if($serverType != 'syncml' && $serverType != 'syncml_wbxml') {
	if(ini_get('mbstring.func_overload') != 0) {
		echo 'You need to set mbstring.func_overload to 0 for rpc.php.<br>';
	}

	if(version_compare(PHP_VERSION, '5.0.0') < 0) {
		echo 'eGroupWare\'s SyncML server requires PHP5. Please update to PHP5 if you want to make use of SyncML.<br>';
	}
	
	die('You should access this URL only with a SyncML enabled device.');
}

if ($serverType == 'soap' &&
    (!isset($_SERVER['REQUEST_METHOD']) ||
     $_SERVER['REQUEST_METHOD'] != 'POST')) {
    $session_control = 'none';
    if (isset($_GET['wsdl'])) {
        $params = 'wsdl';
    } else {
        $params = 'disco';
    }
}

/* Load base libraries. */
require_once HORDE_BASE . '/lib/base.php';

/* Load the RPC backend based on $serverType. */
$server = &Horde_RPC::singleton($serverType, $params);

/* Let the backend check authentication. By default, we look for HTTP
 * basic authentication against Horde, but backends can override this
 * as needed. */
$server->authorize();

/* Get the server's response. We call $server->getInput() to allow
 * backends to handle input processing differently. */
if ($input === null) {
    $input = $server->getInput();
}

$out = $server->getResponse($input, $params);

/* Return the response to the client. */
header('Content-Type: ' . $server->getResponseContentType());
header('Content-length: ' . strlen($out));
header('Accept-Charset: UTF-8');
echo $out;
