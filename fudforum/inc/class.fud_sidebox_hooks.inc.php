<?php
	/****************************************************************************\
	* phpGroupWare - FUDforum 2.6.0 equivalent                                   *
	* http://fud.prohost.org/                                                    *
	* Written by Ilia Alshanetsky <ilia@prohost.org>                             *
	* -------------------------------------------                                *
	*  This program is free software; you can redistribute it and/or modify it   *
	*  under the terms of the GNU General Public License as published by the     *
	*  Free Software Foundation; either version 2 of the License, or (at your    *
	*  option) any later version.                                                *
	\****************************************************************************/

class fud_sidebox_hooks
{
	function all_hooks($args)
	{
		if (empty($GLOBALS['t'])) {
			fud_use('db.inc');
		}
		$GLOBALS['adm_file'] = array();
		$GLOBALS['fudh_uopt'] = (int) q_singleval("SELECT users_opt FROM phpgw_fud_users WHERE id!=1 AND egw_id=".(int)$GLOBALS['phpgw_info']['user']['account_id']);
		if (!empty($GLOBALS['phpgw_info']['user']['apps']['admin'])) {
			$GLOBALS['fudh_uopt'] |= 1048576;
		}
		fud_use('usercp.inc');

		/* regular user links */
		if (!empty($GLOBALS['t'])) {
			display_sidebox('fudforum', lang('Preferences'), $GLOBALS['usr_file']);
		}

		/* admin stuff */
		if ($GLOBALS['adm_file']) {
			display_sidebox('fudforum', lang('Administration'), $GLOBALS['adm_file']);
		}
	}
}

?>