<?php

   /**
    * jinn_db_bool2php_bool: converts db boolean e.g. t of f or 1 and 0 to true or false 
    * 
    * @access public
    * @return void
    */
	function db2phpbool($boolval)
	{
	   //die('hallo');
	   if($boolval=='t' OR $boolval==1)
	   {
		  return true;
	   }
	   else
	   {
		  return false;
	   }
	}
