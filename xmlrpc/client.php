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

	echo '
<form action="' . $GLOBALS['phpgw']->link('/xmlrpc/client.php') . '" method="post">
<input name="stateno" VALUE="' . $stateno . '">
<input type="submit" value="go" name="submit">
</form>
<p>enter a US state number to query its name</p>';

	if ($_POST['stateno'] != '')
	{
		$f = CreateObject('phpgwapi.xmlrpcmsg','examples.getStateName',array(CreateObject('phpgwapi.xmlrpcval',$_POST['stateno'], 'int')));
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
		if (!$r->faultCode())
		{
			print 'State number ' . $_POST['stateno'] . ' is ' . $v->scalarval() . '<br>';
			// print "<HR>I got this value back<BR><PRE>" .
			//  htmlentities($r->serialize()). "</PRE><HR>\n";
		}
		else
		{
			print 'Fault: ';
			print 'Code: ' . $r->faultCode() . " Reason '" .$r->faultString()."'";

			echo "<p><b>Plese Note</b>: To be able to use this test, you have to <b>uncomment</b> the following line in <b>xmlrpc.php</b> on your server:<br>
include(PHPGW_API_INC . '/xmlrpc.interop.php');</p>\n";;
		}
	}
	$GLOBALS['phpgw']->common->phpgw_footer();
?>
