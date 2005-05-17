<?php
	/**************************************************************************\
	* eGroupWare - Setup                                                       *
	* http://www.egroupware.org                                                *
	* --------------------------------------------                             *
	* This program is free software; you can redistribute it and/or modify it  *
	* under the terms of the GNU General Public License as published by the    *
	* Free Software Foundation; either version 2 of the License, or (at your   *
	* option) any later version.                                               *
	\**************************************************************************/

	$test[] = '0.1.8';
	function filescenter_upgrade0_1_8()
	{
		if ($GLOBALS['DEBUG']) { echo '<br>Setting file permissions for public dir... '; }

		$GLOBALS['phpgw_setup']->oProc->update('phpgw_vfs2_files',array('shared' => 'Y'),array('directory' => '/', 'name' => 'public'),__LINE__,__FILE__);

		if ($GLOBALS['DEBUG']) { echo '<b> done! </b>'; }

		$GLOBALS['setup_info']['filescenter']['currentver'] = '0.1.9.001th';
		return $GLOBALS['setup_info']['filescenter']['currentver'];
	}
	
?>
