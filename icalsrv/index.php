<?php
  /**
   * @file EGroupware - IcalSrv index.php file 
   * @package IcalSrv 
   * @version 0.0.1
   * @author jvl @date 20060407
   * homepage @url http://www.egroupware.org
   */
   /************************************************************************** \
   * eGroupWare - IcalSRV - iCalendar over http service                       *
   * http://www.egroupware.org                                                *
   * --------------------------------------------                             *
   *  This program is free software; you can redistribute it and/or modify it *
   *  under the terms of the GNU General Public License as published by the   *
   *  Free Software Foundation; either version 2 of the License, or (at your  *
   *  option) any later version.                                              *
   \**************************************************************************/

  /* $Id$ */
$GLOBALS['egw_info']['flags']
   = array(
		   'currentapp'	=> 'login', 
		   'noheader'		=> True,
		   'nonavbar'		=> True
		   );
include('../header.inc.php');
$GLOBALS['egw']->redirect_link('/about.php','app=icalsrv');
