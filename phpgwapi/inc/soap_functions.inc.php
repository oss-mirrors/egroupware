<?php
	/**************************************************************************\
	* phpGroupWare API - SOAP functions                                        *
	* This file written by dietrich@ganx4.com                                  *
	* shared functions and vars for use with soap client/server                *
	* -------------------------------------------------------------------------*
	* This library is free software; you can redistribute it and/or modify it  *
	* under the terms of the GNU Lesser General Public License as published by *
	* the Free Software Foundation; either version 2.1 of the License,         *
	* or any later version.                                                    *
	* This library is distributed in the hope that it will be useful, but      *
	* WITHOUT ANY WARRANTY; without even the implied warranty of               *
	* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                     *
	* See the GNU Lesser General Public License for more details.              *
	* You should have received a copy of the GNU Lesser General Public License *
	* along with this library; if not, write to the Free Software Foundation,  *
	* Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA            *
	\**************************************************************************/

	/* $Id$ */

	$GLOBALS['soapTypes'] = array(
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

	while(list($key,$val) = each($GLOBALS['soapTypes']))
	{
		$GLOBALS['soapKeys'][] = $val;
	}

	$GLOBALS['typemap'] = array(
		'http://soapinterop.org/xsd'                => array('SOAPStruct'),
		'http://schemas.xmlsoap.org/soap/encoding/' => array('base64'),
		'http://www.w3.org/1999/XMLSchema'          => $GLOBALS['soapKeys']
	);

	$GLOBALS['namespaces'] = array(
		'http://schemas.xmlsoap.org/soap/envelope/' => 'SOAP-ENV',
		'http://www.w3.org/1999/XMLSchema-instance' => 'xsi',
		'http://www.w3.org/1999/XMLSchema'          => 'xsd',
		'http://schemas.xmlsoap.org/soap/encoding/' => 'SOAP-ENC',
		'http://soapinterop.org/xsd'                => 'si'
	);

	/*
	NOTE: already defined in xml_functions
	$xmlEntities = array(
		'quot' => '"',
		'amp'  => '&',
		'lt'   => '<',
		'gt'   => '>',
		'apos' => "'"
	);
	*/

	$GLOBALS['soap_defencoding'] = 'UTF-8';

	function system_login($m1,$m2,$m3)
	{
		$server_name = trim($m1);
		$username    = trim($m2);
		$password    = trim($m3);

		list($sessionid,$kp3) = $GLOBALS['phpgw']->session->create_server($username.'@'.$server_name,$password,'text');

		if(!$sessionid && !$kp3)
		{
			if($server_name)
			{
				$user = $username.'@'.$server_name;
			}
			else
			{
				$user = $username;
			}
			$sessionid = $GLOBALS['phpgw']->session->create($user,$password,'text');
			$kp3 = $GLOBALS['phpgw']->session->kp3;
			$domain = $GLOBALS['phpgw']->session->account_domain;
		}
		if($sessionid && $kp3)
		{
			$rtrn = array(
				CreateObject('phpgwapi.soapval','domain','string',$domain),
				CreateObject('phpgwapi.soapval','sessionid','string',$sessionid),
				CreateObject('phpgwapi.soapval','kp3','string',$kp3)
			);
		}
		else
		{
			$rtrn = array(CreateObject('phpgwapi.soapval','GOAWAY','string',$username));
		}
		$r = CreateObject('phpgwapi.soapmsg','system_loginResponse',$rtrn);
		return $r;
	}

	function system_logout($m1,$m2)
	{
		$sessionid   = $m1;
		$kp3         = $m2;

		$username = $GLOBALS['phpgw']->session->account_lid;
		$later = $GLOBALS['phpgw']->session->destroy();

		if($later)
		{
			$rtrn = array(
				CreateObject('phpgwapi.soapval','GOODBYE','string',$username)
			);
		}
		else
		{
			$rtrn = array(
				CreateObject('phpgwapi.soapval','OOPS','string','WHAT?')
			);
		}
		$r = CreateObject('phpgwapi.soapmsg','system_logoutResponse',$rtrn);
		return $r;
	}
?>
