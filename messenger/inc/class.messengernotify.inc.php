<?php
	/**************************************************************************\
	* eGroupWare - Calendar                                                    *
	* http://www.egroupware.org                                                *
	* Based on Webcalendar by Craig Knudsen <cknudsen@radix.net>               *
	*          http://www.radix.net/~cknudsen                                  *
	* Created by Edo van Bruggen0 <edovanbruggen@raketnet.nl>                  *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/


	class messengernotify
	{
		function notify()
		{
			$db;
			$table = 'phpgw_messenger_messages';
			$owner;
			$this->db    = $GLOBALS['egw']->db;
			$this->owner = $GLOBALS['egw_info']['user']['account_id'];
			$config =& CreateObject('phpgwapi.config');
			$config->read_repository();
			$GLOBALS['egw_info']['server']['messenger'] = $config->config_data;
			unset($config);
			$messages = array();

			$count = 0;

			$this->db->limit_query('SELECT * FROM ' . $table . " WHERE message_owner=" . $this->owner
				. " AND message_status ='N'","",__LINE__,__FILE__);
			while($this->db->next_record())
			{
				if($this->db->f('message_status') =='N')
				{
					$count++;
				}
			}
			if($count > 0)
			{
				if($count == 1)
				{
					return "You have ".$count." new message.";
				}
				else
				{
					return "You have ".$count." new messages.";
				}
			}
			return False;
		}
	}
?>
