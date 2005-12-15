<?php
/**************************************************************************\
* eGroupWare - Chatty                                                      *
* http://www.egroupware.org                                                *
* Copyright (C) 2005  TITECA-BEAUPORT Olivier   oliviert@maphilo.com       *
* Inspired by :															   *
*  Concisus - API Utils - XMLRPC Server Class                              *
* http://concis.us                                                         *
* Written by:                                                              *
*  - Raphael Derosso Pereira <raphaelpereira@users.sourceforge.net>        *
*  - Vinicius Cubas Brand <viniciuscb@users.sourceforge.net>               *
*  Sponsored by Thyamad - http://www.thyamad.com                           *
* --------------------------------------------                             *
*  This program is free software; you can redistribute it and/or modify it *
*  under the terms of the GNU General Public License as published by the   *
*  Free Software Foundation; either version 2 of the License, or (at your  *
*  option) any later version.                                              *
\**************************************************************************/



	class xmlrpc_server extends IXR_Server
	{
		/**
		 * Method: call
		 *
		 *	Overwritten method that handles method calls the way
		 *	supported by Concisus
		 *
		 * Parameters:
		 *
		 *	method - The method name
		 *	args   - The arguments
		 *
		 */
		function call ($methodname, $args)
		{
			//die($methodname);
			$method = $this->callbacks[$methodname];
			list($package,$class,$method) = explode('.',$method);
			
			if(! $package || ! $class || ! $method)
			{
				$invalid_data = True;
			}
			
			$object =& CreateObject(sprintf('%s.%s',$package,$class));

			//TODO these are not in public_functions. Must define what are the
			//procedure for xmlrpc methods

			if((is_array($object->public_functions) && $object->public_functions[$method]) 
				&& method_exists($object,$method) && ! $invalid_data)
			{
				$result = $object->$method($args[0]);
				return $result;
			}
			else
			{
				if(!$package || !$class || !$method || !$object || !method_exists($object,$method))
				{
					return new IXR_Error(-32601, 'server error. requested function "'.$methodname.'" does not exist.');
				}

				if(!is_array($object->public_functions) || ! array_key_exists($method,$object->public_functions))
				{
					//Did not find an error code for "unauthorized" in any rfc
					return new IXR_Error(-401, 'application error. Access unauthorized for method "'.$methodname.'".');
				}
			}
			

		}
	}
