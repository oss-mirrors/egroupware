<?php
/**************************************************************************\
* phpGroupWare - SOAP Server                                               *
* http://www.phpgroupware.org                                              *
* Written by Miles Lott <milosch@phpgroupware.org>                         *
* --------------------------------------------                             *
*  This program is free software; you can redistribute it and/or modify it *
*  under the terms of the GNU General Public License as published by the   *
*  Free Software Foundation; either version 2 of the License, or (at your  *
*  option) any later version.                                              *
\**************************************************************************/

/* $Id$ */
	$login  = 'anonymous';
	$passwd = 'anonymous1';

	$phpgw_info['flags'] = array(
		'disable_Template_class' => True,
		'login' => True,
		'currentapp' => 'login',
		'noheader' => True
	);

	include('../header.inc.php');
	$sessionid = $phpgw->session->create($login,$passwd);

	$soapTypes = array(
		'i4'           => 1,
		'int'          => 1,
		'boolean'      => 1,
		'string'       => 1,
		'double'       => 1,
		'float'        => 1,
		'dateTime'     => 1,
		'timeInstant'  => 1,
		'dateTime'     => 1,
		'base64Binary' => 1,
		'base64'       => 1,
		'array'        => 2,
		'Array'        => 2,
		'SOAPStruct'   => 3,
		'ur-type'      => 2
	);

	$typemap = array(
		'http://soapinterop.org/xsd' => array('SOAPStruct'),
		'http://schemas.xmlsoap.org/soap/encoding/' => array('base64'),
		'http://www.w3.org/1999/XMLSchema' => array_keys($soapTypes)
	);

	$namespaces = array(
		'http://schemas.xmlsoap.org/soap/envelope/' => 'SOAP-ENV',
		'http://www.w3.org/1999/XMLSchema-instance' => 'xsi',
		'http://www.w3.org/1999/XMLSchema' => 'xsd',
		'http://schemas.xmlsoap.org/soap/encoding/' => 'SOAP-ENC',
		'http://soapinterop.org/xsd' =>'si'
	);

	$xmlEntities = array(
		'quot' => '"',
		'amp'  => "&",
		'lt'   => "<",
		'gt'   => ">",
		'apos' => "'"
	);

	$soap_defencoding = 'UTF-8';

	$server = CreateObject('phpgwapi.soap_server');
	/* _debug_array($server);exit; */

	include('./soaplib.soapinterop.php');

	$server->service($HTTP_RAW_POST_DATA);
?>
