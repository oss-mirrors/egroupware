<?php
  /**************************************************************************\
  * phpGroupWare - E-Mail Message Processing Functions                             *
  * http://www.phpgroupware.org                                              *
  */
  /**************************************************************************\
  * phpGroupWare API - E-Mail Message Processing Functions                         *
  * This file written by Angelo Tony Puglisi (Angles) <angles@phpgroupware.org>      *
  * Handles specific operations in manipulating email messages                         *
  * Copyright (C) 2001 Angelo Tony Puglisi (Angles)                                           *
  * -------------------------------------------------------------------------*
  * This library is part of the phpGroupWare API                             *
  * http://www.phpgroupware.org/api                                          * 
  * ------------------------------------------------------------------------ *
  * This library is free software; you can redistribute it and/or modify it  *
  * under the terms of the GNU Lesser General Public License as published by *
  * the Free Software Foundation; either version 2.1 of the License,         *
  * or any later version.                                                    *
  * This library is distributed in the hope that it will be useful, but      *
  * WITHOUT ANY WARRANTY; without even the implied warranty of               *
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                     *
  * See the GNU Lesser General Public License for more details.              *
  * You should have received a copy of the GNU Lesser General Public License *
  * along with this library; if not, write to the Free Software Foundation,  *
  * Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA            *
  \**************************************************************************/

  /* $Id$ */

  class mail_msg extends mail_msg_base
  {

	function all_folders_listbox($mailbox,$pre_select="",$skip="",$indicate_new=false)
	{
		global $phpgw, $phpgw_info;

		// init some important variables
		$outstr = '';
		//$unseen_prefix = ' &lt;';
		//$unseen_suffix = ' new&gt;';	
		//$unseen_prefix = ' &#091;';
		//$unseen_suffix = ' new&#093;';
		//$unseen_prefix = ' &#040;';
		//$unseen_suffix = ' new&#041;';
		//$unseen_prefix = ' &#045; ';
		//$unseen_suffix = ' new';
		//$unseen_prefix = ' &#045;';
		//$unseen_suffix = '&#045;';	
		//$unseen_prefix = '&nbsp;&nbsp;&#040;';
		//$unseen_suffix = ' new&#041;';
		//$unseen_prefix = '&nbsp;&nbsp;&#091;';
		//$unseen_suffix = ' new&#093;';
		$unseen_prefix = '&nbsp;&nbsp;&#060;';
		$unseen_suffix = ' new&#062;';

		if (isset($phpgw_info["flags"]["newsmode"]) && $phpgw_info["flags"]["newsmode"])
		{
			while($pref = each($phpgw_info["user"]["preferences"]["nntp"]))
			{
				$phpgw->db->query("SELECT name FROM newsgroups WHERE con=".$pref[0]);
				while($phpgw->db->next_record())
				{
					$outstr = $outstr .'<option value="' . urlencode($phpgw->db->f("name")) . '">' . $phpgw->db->f("name")
					  . '</option>';
				}
			}
		}
		else
		{
			$folder_list = $this->get_folder_list($mailbox);

			for ($i=0; $i<count($folder_list);$i++)
			{
				$folder_long = $folder_list[$i]['folder_long'];
				$folder_short = $folder_list[$i]['folder_short'];
				if ($folder_short == $pre_select)
				{
					$sel = ' selected';
				}
				else
				{
					$sel = '';
				}
				if ($folder_short != $skip)
				{
					$outstr = $outstr .'<option value="' .urlencode($folder_short) .'"'.$sel.'>' .$folder_short;
					// do we show the number of new (unseen) messages for this folder
					if (($indicate_new)
					&& ($this->care_about_unseen($folder_short)))
					{
						$server_str = $this->get_mailsvr_callstr();
						$mailbox_status = $phpgw->dcom->status($mailbox, $server_str .$folder_long,SA_UNSEEN);
						if ($mailbox_status->unseen > 0)
						{
							$outstr = $outstr . $unseen_prefix . $mailbox_status->unseen . $unseen_suffix;
						}
					}
					$outstr = $outstr . "</option>\r\n";
				}
			}
		}
		return $outstr;
	}


  }
  // end class mail_msg
?>