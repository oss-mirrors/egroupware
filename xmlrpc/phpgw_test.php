<?php
/**************************************************************************\
* phpGroupWare - XML-RPC Test App                                          *
* http://www.phpgroupware.org                                              *
* --------------------------------------------                             *
*  This program is free software; you can redistribute it and/or modify it *
*  under the terms of the GNU General Public License as published by the   *
*  Free Software Foundation; either version 2 of the License, or (at your  *
*  option) any later version.                                              *
\**************************************************************************/

/* $Id$ */

	$GLOBALS['phpgw_info'] = array();
	$GLOBALS['phpgw_info']['flags'] = array(
		'currentapp'  => 'xmlrpc',
		'noheader'    => False,
		'noappheader' => False,
		'nonavbar'    => False
	);

	include('../header.inc.php');

	$method = get_var('method',array('POST'),'system.listMethods');

	echo '
<form action="' . $GLOBALS['phpgw']->link('/xmlrpc/phpgw_test.php') . '" method="post">
<input name="method" VALUE="' . $method . '">
<input name="param" VALUE="' . $param . '">
<input type="submit" value="go" name="submit">
</form>
<p>
Enter a method to execute and one parameter';

	if ($_POST['method'])
	{
		$f = CreateObject('phpgwapi.xmlrpcmsg',$method,array(
			CreateObject('phpgwapi.xmlrpcval',$_POST['param'], 'string')
		));
		print '<pre style="text-align: left;">' . htmlentities($f->serialize()) . "</pre>\n";
		$xmlrpc = eregi_replace('https*://[^/]*/','',$GLOBALS['phpgw_info']['server']['webserver_url']).'/xmlrpc.php';
		$c = CreateObject('phpgwapi.xmlrpc_client',$xmlrpc, $_SERVER['HTTP_HOST'], 80);
		$c->setDebug(1);
		$r = $c->send($f);
		if (!$r)
		{
			die('send failed');
		}
		$v = $r->value();
		if ($r->faultCode())
		{
			print 'Fault: ';
			print 'Code: ' . $r->faultCode() . " Reason '" .$r->faultString()."'<br>";
		}
	}

	$GLOBALS['phpgw']->common->phpgw_footer();
?>
