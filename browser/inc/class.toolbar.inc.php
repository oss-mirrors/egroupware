<?php
  /**************************************************************************\
  * eGroupWare - Calendar                                                    *
  * http://www.egroupware.org                                                *
  * Based on Webcalendar by Craig Knudsen <cknudsen@radix.net>               *
  *          http://www.radix.net/~cknudsen                                  *
  * Modified by Mark Peters <skeeter@phpgroupware.org>                       *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

	/* $Id$ */

	class toolbar	
	{

	
	
		function toolbar()
		{
			$toolbar = Array();
			$toolbar['Back'] = Array(
				'title' => "Back",
				'image'   => 'back.png',
				'url'=> 'javascript:back();'
				);
				
			$toolbar['Forward'] = Array(
				'title' => "Forward",
				'image'   => 'forward.png',
				'url'=> 'javascript:forward();'
				);
			$toolbar['Reload'] = Array(
				'title' => "Reload",
				'image'   => 'reload.png',
				'url'=> 'javascript:reload();'
				);
			$toolbar['Home'] = Array(
				'title' => "Home",
				'image'   => 'gohome.png',
				'url'=> 'javascript:home();'
				);		
			return $toolbar;
		}

		
		
	}	
?>
