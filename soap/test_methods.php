<?php
/**************************************************************************\
* phpGroupWare - addressbook                                               *
* http://www.phpgroupware.org                                              *
* Written by Joseph Engo <jengo@phpgroupware.org>                          *
* --------------------------------------------                             *
*  This program is free software; you can redistribute it and/or modify it *
*  under the terms of the GNU General Public License as published by the   *
*  Free Software Foundation; either version 2 of the License, or (at your  *
*  option) any later version.                                              *
\**************************************************************************/

/* $Id$ */

	$phpgw_info['flags'] = array(
		'disable_Template_class' => True,
		'currentapp' => 'soap',
		'noheader' => True,
		'noappheader' => True
	);

	include('../header.inc.php');
	include('./vars.php');
?>
	<script language="JavaScript">
	<!--
	function quickjump(dropdown) {
		var i = dropdown.selectedIndex
		location = dropdown.options[i].value
	}
	//-->
	</script>
<?php
	// get list of all endpoints from xmethods
	$soapmsg = CreateObject('phpgwapi.soapmsg',"getAllEndpoints","","http://soapinterop.org/ilab");
	// invoke the client
	$client = CreateObject('phpgwapi.soap_client',"http://www.xmethods.net/perl/soaplite.cgi");
	// send message and get response
	if($return = $client->send($soapmsg,"","http://soapinterop.org/ilab#getAllEndpoints")){
		//print "REQUEST:<br><xmp>".$client->outgoing_payload."</xmp><br>";
		//print "<strong>RESPONSE:</strong><br><xmp>".$client->incoming_payload."</xmp><br>";
		//print "<b>CLIENT DEBUG:</b><br><xmp>$client->debug_str</xmp>";
		if($return->name != "fault")
		{
			//print "<xmp>";
			//print_r($return);
			//print "</xmp>";
			if($endpointArray = @array_shift($return->decode()))
			{
				foreach($endpointArray as $k => $v)
				{
					$servers[str_replace("+","",$v["name"])] = $v;
				}
			}
			else
			{
				print "request:<br><xmp>".$client->request."</xmp><br>";
			}
		}
		else
		{
			print "got fault<br>";
			print "<b>Client Debug:</b><br><xmp>$client->debug_str</xmp>";
			die();
		}
	}
	else
	{
		print "send failed - could not get list of servers from xmethods<br>";
	}

// method test params
	$fields = array(
		'n_given'  => 'n_given',
		'n_family' => 'n_family',
		'org_name' => 'org_name',
		'email'    => 'email'
	);

$method_params["fetchaddr"]["id"] = 106;
$method_params["fetchaddr"]["fields"] = $fields;
$method_params["echoString"]["inputString"] = "test string";
$method_params["echoStringArray"]["inputStringArray"] = array("good","bad");
$method_params["echoInteger"]["inputInteger"] = 34345;
//$method_params["echoIntegerArray"]["inputIntegerArray"] = array(-1,342,325325);
$method_params["echoIntegerArray"]["inputIntegerArray"] = array(1,234324324,2);
$method_params["echoFloat"]["inputFloat"] = 342.23;
//$method_params["echoFloatArray"]["inputFloatArray"] = array(1.3223,34.2,325.325);
	$method_params["echoFloatArray"]["inputFloatArray"] = array(
		CreateObject('phpgwapi.soapval',"nan","float",32432.234),
		CreateObject('phpgwapi.soapval',"inf","float",-23423.23),
		CreateObject('phpgwapi.soapval',"neginf","float",-INF)
	);
	$method_params["echoStruct"]["inputStruct"] = CreateObject('phpgwapi.soapval',
		"inputStruct",
		"SOAPStruct",
		array("varString"=>"arg","varInt"=>34,"varFloat"=>325.325)
	);
	$method_params["echoStructArray"]["inputStructArray"] = array(
		CreateObject('phpgwapi.soapval',
			"item",
			"SOAPStruct",array(
				"varString"=>"arg",
				"varInt"=>34,
				"varFloat"=>325.325
			)
		),
		CreateObject('phpgwapi.soapval',
			"item",
			"SOAPStruct",array(
				"varString"=>"arg",
				"varInt"=>34,
				"varFloat"=>325.325
			)
		),
		CreateObject('phpgwapi.soapval',
			"item",
			"SOAPStruct",array(
				"varString"=>"arg",
				"varInt"=>34,
				"varFloat"=>325.325
			)
		)
	);
	$method_params["echoVoid"] = "";
	$method_params["echoBase64"]["inputBase64"] = CreateObject('phpgwapi.soapval',
		"inputBase64",
		"base64",
		"TmVicmFza2E="
	);
	$method_params["echoDate"]["inputDate"] = CreateObject('phpgwapi.soapval',
		"inputDate",
		"timeInstant",
		"2001-04-25T09:31:41-07:00"
	);

$servers["SOAPx4 - Milosch"] = array(
	"soapaction" => "urn:soapinterop",
	"endpoint" => "http://milosch.dyndns.org/phpgroupware/soap/index.php",
	"methodNamespace" => "http://soapinterop.org",
	"soapactionNeedsMethod" => 0,
	"name" => "SOAPx4  - interop test suite (dev)");
$servers["SOAPx4 - interop test suite"] = array(
	"soapaction" =>"urn:soapinterop",
	"endpoint" =>"http://dietrich.ganx4.com/soapx4/phpgwapi.php",
	"path" =>"",
	"methodNamespace" => "http://soapinterop.org",
	"soapactionNeedsMethod" => 0,
	"name" => "SOAPx4 - interop test suite");

?>

<form action='test_methods.php' method='post'>
<select name='nserver'>
<option>Choose Server...
	<?php
	print isset($nserver) ? "<option value='$PHP_SELF?nserver=$nserver'>$nserver" : "";
	foreach($servers as $k => $v){
		print "<option value='$k'>$k\n";
	}
	?>
</select>
<select name='method' onChange='submit(this.form)'>
	<option>method:
	<?php
	foreach(array_keys($method_params) as $func){
		print "<option value='$func'>$func\n ";
	}
	?>
</select>
</form>

<?php
	if($method && $nserver)
	{
		$server = $servers[$nserver];
		print "<b>METHOD: $method</b><br>";
		$soap_message = CreateObject('phpgwapi.soapmsg',
			$method,
			$method_params[$method],
			$server["methodNamespace"]
		);
		/* print_r($soap_message);exit; */
		$soap = CreateObject('phpgwapi.soap_client',$server["endpoint"]);
		/* print_r($soap);exit; */
		if($return = $soap->send($soap_message,$server["soapaction"]))
		{
			// check for valid response
			if(get_class($return) == "soapval")
			{
				print "Correctly decoded server's response<br>";
				// fault?
				if(eregi("fault",$return->name))
				{
					$status = "failed";
				}
				else
				{
					$status = "passed";
				}
			}
			else
			{
				print "Client could not decode server's response<br>";
			}
		}
		else
		{
			print "Was unable to send or receive.";
		}

		//$soap->incoming_payload .= "\n\n<!-- SOAPx4 CLIENT DEBUG\n$client->debug_str\n\nRETURN VAL DEBUG: $return->debug_str-->";
		print "<strong>Request:</strong><br><xmp>$soap->outgoing_payload</xmp><br>";
		print "<strong>Response:</strong><br><xmp>$soap->incoming_payload</xmp>";
	}

	unset($soapmsg);
	unset($soap);
?>
