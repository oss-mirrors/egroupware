<?php
/**************************************************************************\
* phpGroupWare - session data class                                        *
* http://www.phpgroupware.org                                              *
* writen by Lars Kneschke <kneschke@phpgroupware.org>                      *
*          http://www.kneschke.de/                                         *
* --------------------------------------------                             *
*  This program is free software; you can redistribute it and/or modify it *
*  under the terms of the GNU General Public License as published by the   *
*  Free Software Foundation; either version 2 of the License, or (at your  *
*  option) any later version.                                              *
\**************************************************************************/

	class phpgwSessionData
	{
		var $variableNames;
		
		// restore the values of the variables
#		function restore()
#		{
#			global $phpgw;
#
#			$serializedData = $phpgw->session->appsession('session');
#			$sessionData = unserialize($serializedData);  
#			
#			if (is_array($sessionData))
#			{
#				reset($sessionData);
#				while(list($key,$value) = each($sessionData))
#				{
#					global $$key;
#					$$key = $value;
#					$this->variableNames[$key]="registered";
#					print "restored: ".$key.", $value<br>";
#				}
#			}
#		}
		
#		// save the current values of the variables
#		function save()
#		{
#			global $phpgw;
#			
#			if (is_array($this->variableNames))
#			{
#				reset($this->variableNames);
#				while(list($key, $value) = each($this->variableNames))
#				{
#					if ($value == "registered")
#					{
#						global $$key;
#						$sessionData[$key] = $$key;
#					}
#				}
#				$phpgw->session->appsession('default','',$sessionData);
#			}
#		}
		
#		// create a list a variable names, wich data need's to be restored
#		function register($_variableName)
#		{
#			$this->variableNames[$_variableName]="registered";
#			#print "registered $_variableName<br>";
#		}
		
#		// mark variable as unregistered
#		function unregister($_variableName)
#		{
#			$this->variableNames[$_variableName]="unregistered";
#			#print "unregistered $_variableName<br>";
#		}

#		// check if we have a variable registred already
#		function is_registered($_variableName)
#		{
#			if ($this->variableNames[$_variableName] == "registered")
#			{
#				return True;
#			}
#			else
#			{
#				return False;
#			}
#		}
	}


?>
