<?php

   class uitest
   {
	  var $public_functions = array(
		 'testsavant2'=>true
	  );
	  function uitest()
	  {}

	  function testsavant2()
	  {
		 unset($GLOBALS['phpgw_info']['flags']['noheader']);
		 unset($GLOBALS['phpgw_info']['flags']['nonavbar']);
		 unset($GLOBALS['phpgw_info']['flags']['noappheader']);
		 unset($GLOBALS['phpgw_info']['flags']['noappfooter']);

		 $GLOBALS['phpgw']->common->phpgw_header();

		 $tplsav2= CreateObject('phpgwapi.tplsavant2');
		 
		 $tplsav2->assign('test1',lang('this is test 1'));

		 $test2 = $tplsav2->fetch('test2.tpl.php');
		 $tplsav2->assign('test2', $test2);

		 $test3 = $tplsav2->fetch('test3.tpl.php');
		 
		 $tplsav2->assign('test3', $test3);
		 $tplsav2->display('testfinaltemplate.tpl.php');
			
		 _debug_array($tplsav2->_path);

		 echo '<p>this is a normal echo</p>';


	  }

   }




?>

