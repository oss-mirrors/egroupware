<?php
	/**************************************************************************\
	* phpGroupWare - email UI Class for Message Lists				*
	* http://www.phpgroupware.org							*
	* Written by Angelo (Angles) Puglisi <angles@phpgroupware.org>		*
	* --------------------------------------------							*
	*  This program is free software; you can redistribute it and/or modify it 		*
	*  under the terms of the GNU General Public License as published by the	*
	*  Free Software Foundation; either version 2 of the License, or (at your  		*
	*  option) any later version.								*
	\**************************************************************************/

	/* $Id$ */

	class uifolder
	{
		var $bo;		
		var $debug = False;
		var $is_modular = False;

		var $public_functions = array(
			'folder' => True,
			'get_is_modular' => True,
			'set_is_modular' => True
		);

		function uifolder()
		{
			
		}

		function get_is_modular()
		{
			return $this->is_modular;
		}
		
		function set_is_modular($feed_bool=False)
		{
			/* This also does not work on php3 - milosch */
			if ((bool)$feed_bool == False)
			{
				$this->is_modular = False;
			}
			else
			{
				$this->is_modular = True;
			}
			return $this->is_modular;
		}
		
		function folder($reuse_feed_args='')
		{
			if (empty($reuse_feed_args))
			{
				$reuse_feed_args = array();
			}
			
			$this->bo = CreateObject("email.bofolder");
			$this->bo->folder($reuse_feed_args);
			
			if ($this->is_modular == True)
			{
				// we do NOT echo or print output any html, we are being used as a module by another app
				// all we do in this case is pass the parsed html to the calling app
			}
			else
			{
				// we are the BO and the UI, we take care of outputting the HTML to the client browser
				unset($GLOBALS['phpgw_info']['flags']['noheader']);
				unset($GLOBALS['phpgw_info']['flags']['nonavbar']);
				$GLOBALS['phpgw_info']['flags']['noappheader'] = True;
				$GLOBALS['phpgw_info']['flags']['noappfooter'] = True;
				$GLOBALS['phpgw']->common->phpgw_header();
				// NOTE: as of Dec 10, 2001 a call from menuaction defaults to NOT modular
				// HOWEVER still this class must NOT invoke $GLOBALS['phpgw']->common->phpgw_header()
				// even though we had to output the header (go figure... :)
			}
			$GLOBALS['phpgw']->template->set_file(
				Array(
					'T_folder_out' => 'folder.tpl'
				)
			);
			$GLOBALS['phpgw']->template->set_block('T_folder_out','B_folder_list','V_folder_list');
			$GLOBALS['phpgw']->template->set_block('T_folder_out','B_action_report','V_action_report');
			



			if ($this->bo->xi['action_report'] != '')
			{
				$GLOBALS['phpgw']->template->set_var('action_report',$this->bo->xi['action_report']);
				$GLOBALS['phpgw']->template->parse('V_action_report','B_action_report');
			}
			else
			{
				$GLOBALS['phpgw']->template->set_var('V_action_report','');
			}


			for ($i=0; $i<count($this->bo->xi['folder_list_display']);$i++)
			{
				$GLOBALS['phpgw']->template->set_var('list_backcolor',$this->bo->xi['folder_list_display'][$i]['list_backcolor']);
				$GLOBALS['phpgw']->template->set_var('folder_link',$this->bo->xi['folder_list_display'][$i]['folder_link']);
				$GLOBALS['phpgw']->template->set_var('folder_name',$this->bo->xi['folder_list_display'][$i]['folder_name']);
				$GLOBALS['phpgw']->template->set_var('msgs_unseen',$this->bo->xi['folder_list_display'][$i]['msgs_unseen']);
				$GLOBALS['phpgw']->template->set_var('msgs_total',$this->bo->xi['folder_list_display'][$i]['msgs_total']);
				$GLOBALS['phpgw']->template->parse('V_folder_list','B_folder_list',True);
			}



			$GLOBALS['phpgw']->template->set_var('all_folders_listbox',$this->bo->xi['all_folders_listbox']);
			
			// ----  Set Up Form Variables  ---
			$GLOBALS['phpgw']->template->set_var('form_action',$this->bo->xi['form_action']);
			//$GLOBALS['phpgw']->template->set_var('all_folders_listbox',$GLOBALS['phpgw']->msg->all_folders_listbox('','','',False));
			//$GLOBALS['phpgw']->template->set_var('select_name_rename','source_folder');
			
			$GLOBALS['phpgw']->template->set_var('form_create_txt',$this->bo->xi['form_create_txt']);
			$GLOBALS['phpgw']->template->set_var('form_delete_txt',$this->bo->xi['form_delete_txt']);
			$GLOBALS['phpgw']->template->set_var('form_rename_txt',$this->bo->xi['form_rename_txt']);
			$GLOBALS['phpgw']->template->set_var('form_create_expert_txt',$this->bo->xi['form_create_expert_txt']);
			$GLOBALS['phpgw']->template->set_var('form_delete_expert_txt',$this->bo->xi['form_delete_expert_txt']);
			$GLOBALS['phpgw']->template->set_var('form_rename_expert_txt',$this->bo->xi['form_rename_expert_txt']);
			$GLOBALS['phpgw']->template->set_var('form_submit_txt',$this->bo->xi['form_submit_txt']);
			
			// ----  Set Up Other Variables  ---	
			$GLOBALS['phpgw']->template->set_var('title_backcolor',$this->bo->xi['title_backcolor']);
			$GLOBALS['phpgw']->template->set_var('title_textcolor',$this->bo->xi['title_textcolor']);
			$GLOBALS['phpgw']->template->set_var('title_text',$this->bo->xi['title_text']);
			$GLOBALS['phpgw']->template->set_var('label_name_text',$this->bo->xi['label_name_text']);
			//$GLOBALS['phpgw']->template->set_var('label_messages_text',$this->bo->xi['label_messages_text']);
			$GLOBALS['phpgw']->template->set_var('label_new_text',$this->bo->xi['label_new_text']);
			$GLOBALS['phpgw']->template->set_var('label_total_text',$this->bo->xi['label_total_text']);
			
			$GLOBALS['phpgw']->template->set_var('view_long_txt',$this->bo->xi['view_long_txt']);
			$GLOBALS['phpgw']->template->set_var('view_long_lnk',$this->bo->xi['view_long_lnk']);
			$GLOBALS['phpgw']->template->set_var('view_short_txt',$this->bo->xi['view_short_txt']);
			$GLOBALS['phpgw']->template->set_var('view_short_lnk',$this->bo->xi['view_short_lnk']);
			
			$GLOBALS['phpgw']->template->set_var('the_font',$this->bo->xi['the_font']);
			$GLOBALS['phpgw']->template->set_var('th_backcolor',$this->bo->xi['th_backcolor']);
			

			if ($this->is_modular == True)
			{
				// we do NOT output any html, we are being used as a module in another app
				// instead, we will pass the parsed html to the calling app
				
				// Template->fp  means "Finish Parse", which does this
				// 1) parses temnplate and replaces template tokens with vars we have set here
				// 2) "finish" is like clean up, takes care of what to do with "unknowns",
				//	which are things in the template that look like {replace_me} tokens, but
				//	for which a replacement value has not been set, finishes allows you to do this with them:
				// "keep" them;  "remove"  then;  or  "comment" them
				// Template->fp  defaults to "remove" unknowns, although you may set Template->unknowns as you wish
				// COMMENT NEXT LINE OUT for producvtion use, (unknowns should be "remove"d in production use)
				$GLOBALS['phpgw']->template->set_unknowns("comment");
				// production use, use this:	$GLOBALS['phpgw']->template->set_unknowns("remove");
				return $GLOBALS['phpgw']->template->fp('out','T_folder_out');
			}
			else
			{
				// we are the BO and the UI, we take care of outputting the HTML to the client browser
				// Template->pparse means "print parse" which parses the template and uses php print command
				// to output the HTML, note "unknowns" are never handled ("finished") in that method.
				//$GLOBALS['phpgw']->template->pparse('out','T_folder_out');
				
				// COMMENT NEXT LINE OUT for producvtion use, (unknowns should be "remove"d in production use)
				$GLOBALS['phpgw']->template->set_unknowns("comment");
				// production use, use this:	$GLOBALS['phpgw']->template->set_unknowns("remove");
				// Template->pfp will (1) parse and substitute, (2) "finish" - handle unknowns, (3) echo the output
				$GLOBALS['phpgw']->template->pfp('out','T_folder_out');
				// note, for some reason, eventhough it seems we *should* call common->phpgw_footer(),
				// if we do that, the client browser will get TWO page footers, so we do not call it here
			}
		}
	}
?>
