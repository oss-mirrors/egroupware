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

	$phpgw_info['flags'] = array(
		'currentapp'  => 'xmlrpc',
		'noheader'    => True,
		'noappheader' => True,
		'nonavbar'    => True
	);

	include('../header.inc.php');
	require './phpunit.php';

	$DEBUG = 0;
	$LOCALSERVER = $HTTP_HOST;

	$suite = new TestSuite;

	class TestLocalhost extends TestCase
	{
		var $client;

		function TestLocalhost($name)
		{
			$this->TestCase($name);
		}

		function setUp()
		{
			global $DEBUG, $LOCALSERVER;
			$this->client= CreateObject('phpgwapi.xmlrpc_client','/phpgroupware/xmlrpc.php', $LOCALSERVER, 80);
			if ($DEBUG) $this->client->setDebug(1);
		}

		function stringTest()
		{
			$this->setUp();
			$sendstring="here are some \"entities\" < > & and " .
				"here's a dollar sign \$pretendvarname and a backslash too " . chr(92) . 
				" - isn't that great? \\\"hackery\\\" at it's best " .
				" also don't want to miss out on \$item[0]";
			$f = CreateObject('phpgwapi.xmlrpcmsg','examples.stringecho', array(CreateObject('phpgwapi.xmlrpcval',$sendstring, "string")));
			$r = $this->client->send($f);
			$v = $r->value();
			$this->assertEquals($sendstring, $v->scalarval());
		}

		function addingDoublesTest()
		{
			// note that rounding errors mean i
			// keep precision to sensible levels here ;-)
			$a = 12.13;
			$b=-23.98;
			$f = CreateObject('phpgwapi.xmlrpcmsg','examples.addtwodouble', array(CreateObject('phpgwapi.xmlrpcval',$a, "double"),CreateObject('phpgwapi.xmlrpcval',$b, "double")));
			$r = $this->client->send($f);
			$v = $r->value();
			$this->assertEquals($a+$b,$v->scalarval());
		}

		function addingTest()
		{
			$f = CreateObject('phpgwapi.xmlrpcmsg','examples.addtwo',array(CreateObject('phpgwapi.xmlrpcval',12, "int"),CreateObject('phpgwapi.xmlrpcval',-23, "int")));
			$r = $this->client->send($f);
			$v = $r->value();
			$this->assertEquals(12-23, $v->scalarval());
		}

		function invalidNumber()
		{
			$f=CreateObject('phpgwapi.xmlrpcmsg','examples.addtwo',array(CreateObject('phpgwapi.xmlrpcval',"fred", "int"),CreateObject('phpgwapi.xmlrpcval',"\"; exec('ls')", "int")));
			$r=$this->client->send($f);
			$v=$r->value();
			// TODO: a fault condition should be generated here
			// by the server, which we pick up on
			$this->assertEquals(0, $v->scalarval());
		}

		function booleanTest()
		{
			$f = CreateObject('phpgwapi.xmlrpcmsg','examples.invertBooleans', array(
				CreateObject('phpgwapi.xmlrpcval',array(
					CreateObject('phpgwapi.xmlrpcval',True, 'boolean'),
					CreateObject('phpgwapi.xmlrpcval',False, 'boolean'),
					CreateObject('phpgwapi.xmlrpcval',1, 'boolean'),
					CreateObject('phpgwapi.xmlrpcval',0, 'boolean'),
					CreateObject('phpgwapi.xmlrpcval',True, 'boolean'),
					CreateObject('phpgwapi.xmlrpcval',False, 'boolean')
				), 
				"array"
			)));
			$answer = '010101';
			$r=$this->client->send($f);
			$this->assert(!$r->faultCode());
			$v=$r->value();
			$sz=$v->arraysize();
			$got="";
			for($i=0; $i<$sz; $i++)
			{
				 $b=$v->arraymem($i);
				 if ($b->scalarval())	$got.="1";
				 else $got.="0";
			}
			$this->assertEquals($answer, $got);
		}

		function base64Test()
		{
			$sendstring = "Mary had a little lamb,
Whose fleece was white as snow,
And everywhere that Mary went
the lamb was sure to go.

Mary had a little lamb
She tied it to a pylon
Ten thousand volts went down its back
And turned it into nylon";
			$f = CreateObject('phpgwapi.xmlrpcmsg','examples.decode64',array(
				CreateObject('phpgwapi.xmlrpcval',$sendstring, 'base64')
			));
			$r = $this->client->send($f);
			$v = $r->value();
			$this->assertEquals($sendstring, $v->scalarval());
		}
	}

	class TestFileCases extends TestCase
	{
		function TestFileCases($name, $base = '/usr/local/apache/htdocs/phpgroupware/xmlrpc')
		{
			$this->TestCase($name);
			$this->root=$base;
		}

		function stringBug ()
		{
			$m=CreateObject('phpgwapi.xmlrpcmsg','dummy');
			$fp=fopen($this->root . '/bug_string.xml', 'r');
			$r=$m->parseResponseFile($fp);
			$v=$r->value();
			fclose($fp);
			$s=$v->structmem('sessionID');
			$this->assertEquals('S300510007I', $s->scalarval());
		}
	}

	class TestInvalidHost extends TestCase
	{
		function TestInvalidHost($name)
		{
			$this->TestCase($name);
		}

		function setUp()
		{
			global $DEBUG,$LOCALSERVER;
			$this->client=CreateObject('phpgwapi.xmlrpc_client','/NOTEXIST.php', $LOCALSERVER, 80);
			if ($DEBUG) $this->client->setDebug(1);
		}
	
		function test404()
		{
			$f = CreateObject('phpgwapi.xmlrpcmsg','examples.echo', array(
				CreateObject('phpgwapi.xmlrpcval','hello', 'string')
			));
			$r=$this->client->send($f);
			$this->assertEquals(5, $r->faultCode());
		}
	}

	$suite->addTest(new TestLocalhost("stringTest"));
	$suite->addTest(new TestLocalhost("addingTest"));
	$suite->addTest(new TestLocalhost("addingDoublesTest"));
	$suite->addTest(new TestLocalhost("invalidNumber"));
	$suite->addTest(new TestLocalhost("booleanTest"));
	$suite->addTest(new TestLocalhost("base64Test"));
	$suite->addTest(new TestInvalidHost("test404"));
//	$suite->addTest(new TestFileCases("stringBug"));
	$title = 'XML-RPC Unit Tests';
?>
<html>
<head><title><?php echo $title; ?></title></head>
<body>
<h1><?php echo $title; ?></h1>
<p>Note, tests beginning with 'f_' <i>should</i> fail.</p>
<p>
<?php
	if (isset($only))
	{
		$suite = new TestSuite($only);
	}
	$testRunner = new TestRunner;
	$testRunner->run($suite);
?>
</body>
</html>
