<?php
   /**************************************************************************\
   * eGroupWare - Calendar's Sidebox-Menu for idots-template                  *
   * http://www.egroupware.org                                                *
   * Written by Pim Snel <pim@lingewoud.nl>                                   *
   * --------------------------------------------                             *
   *  This program is free software; you can redistribute it and/or modify it *
   *  under the terms of the GNU General Public License as published by the   *
   *  Free Software Foundation; either version 2 of the License, or (at your  *
   *  option) any later version.                                              *
   \**************************************************************************/

   /* $Id$ */

   /**
   * File is depricated, the hook moved into the class uical !!!
   */

   // register the new hooks, if the admin missed it ;-)
   // thanks Ralf

   if (!is_object($GLOBALS['phpgw']->hooks))
   {
	  $GLOBALS['phpgw']->hooks = CreateObject('phpgwapi.hooks');
   }
   include_once(PHPGW_INCLUDE_ROOT . '/jinn/setup/setup.inc.php');

   $GLOBALS['phpgw']->hooks->register_hooks('jinn',$setup_info['jinn']['hooks']);

   ExecMethod($setup_info['jinn']['hooks']['sidebox_menu']); 

