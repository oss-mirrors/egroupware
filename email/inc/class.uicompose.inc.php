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
	
	class uicompose
	{
		var $bo;		
		var $debug = 0;
		var $widgets;

		var $public_functions = array(
			'compose' => True
		);

		function uicompose()
		{
			return;
		}
		
		/*!
		@function compose
		@abstract calls bocompose and makes the compose page
		@author Angles
		@description ?
		@access public
		*/
		function compose($reuse_feed_args='')
		{
			if ((is_string($reuse_feed_args))
			&& ($reuse_feed_args == ''))
			{
				// we were passed an empty string, make it an empty array just to be consistant
				$reuse_feed_args = array();
				
			}
			// ok, class.spell will pass $special_instructions as $reuse_feed_args string data, 
			// this must be passed onto bocompose->compose()
			
			$this->bo = CreateObject("email.bocompose");
			// concept of $reuse_feed_args is depreciated HOWEVER the spell code will 
			// pass "special_instructions" back to bocompose, so leave this here
			$this->bo->compose($reuse_feed_args);
			
			// we are the BO and the UI, we take care of outputting the HTML to the client browser
			unset($GLOBALS['phpgw_info']['flags']['noheader']);
			unset($GLOBALS['phpgw_info']['flags']['nonavbar']);
			$GLOBALS['phpgw_info']['flags']['noappheader'] = True;
			$GLOBALS['phpgw_info']['flags']['noappfooter'] = True;
			$GLOBALS['phpgw']->common->phpgw_header();
			
			$GLOBALS['phpgw']->template->set_file(
				Array(
					'T_compose_out' => 'compose.tpl'
				)
			);
			$GLOBALS['phpgw']->template->set_block('T_compose_out','B_checkbox_sig','V_checkbox_sig');
			
			if ($this->debug > 2) { echo 'GLOBALS[phpgw_info] dump:<pre>'; print_r($GLOBALS['phpgw_info']) ; echo '</pre>'; }
			
			//= = = = TESTING NEW TOOLBAR WIDGET = = = 
			$this->widgets = CreateObject('email.html_widgets');
			$GLOBALS['phpgw']->template->set_var('widget_toolbar',$this->widgets->get_toolbar());
			
			// fill template vars
			$tpl_vars = Array(
				'to_box_value'		=> $this->bo->xi['to_box_value'],
				'cc_box_value'		=> $this->bo->xi['cc_box_value'],
				'bcc_box_value'		=> $this->bo->xi['bcc_box_value'],
				'subj_box_value'	=> $this->bo->xi['subject'],
				'body_box_value'	=> $this->bo->xi['body'],
				'form1_action'		=> $this->bo->xi['send_btn_action'],
				//The addybook's window width
				'jsaddybook_width'	=> $this->bo->xi['jsaddybook_width'],
				//The addybook's window height
				'jsaddybook_height'	=> $this->bo->xi['jsaddybook_height'],
				'form1_name'		=> $this->bo->xi['form1_name'],
				'form1_method'		=> $this->bo->xi['form1_method'],
				'js_addylink'		=> $this->bo->xi['js_addylink'],
				'buttons_bgcolor'	=> $this->bo->xi['buttons_bgcolor'],
				'to_boxs_bgcolor'	=> $this->bo->xi['to_boxs_bgcolor'],
				'to_boxs_font'		=> $this->bo->xi['to_boxs_font'],
				'to_box_desc'		=> $this->bo->xi['to_box_desc'],
				'to_box_name'		=> $this->bo->xi['to_box_name'],
				'cc_box_desc'		=> $this->bo->xi['cc_box_desc'],
				'cc_box_name'		=> $this->bo->xi['cc_box_name'],
				'bcc_box_desc'		=> $this->bo->xi['bcc_box_desc'],
				'bcc_box_name'		=> $this->bo->xi['bcc_box_name'],
				'subj_box_desc'		=> $this->bo->xi['subj_box_desc'],
				'subj_box_name'		=> $this->bo->xi['subj_box_name'],
				'checkbox_sig_desc'	=> $this->bo->xi['checkbox_sig_desc'],
				'checkbox_sig_name'	=> $this->bo->xi['checkbox_sig_name'],
				'checkbox_sig_value'	=> $this->bo->xi['checkbox_sig_value'],
				//Step One addition for req read notifications
				'checkbox_req_notify_desc'	=> $this->bo->xi['checkbox_req_notify_desc'],
				'checkbox_req_notify_name'	=> $this->bo->xi['checkbox_req_notify_name'],
				'checkbox_req_notify_value'	=> $this->bo->xi['checkbox_req_notify_value'],
				'app_images'		=> $this->bo->xi['image_dir'],
				'toolbar_font'			=> $this->bo->xi['toolbar_font'],
				'addressbook_button'	=> $this->bo->xi['addressbook_button'],
				'send_button'			=> $this->bo->xi['send_button'],
				'spellcheck_button'		=> $this->bo->xi['spellcheck_button'],
				'attachfile_js_button'		=> $this->bo->xi['attachfile_js_button'], 
				'body_box_name'		=> $this->bo->xi['body_box_name']
			);
			$GLOBALS['phpgw']->template->set_var($tpl_vars);
			if ($this->bo->xi['do_checkbox_sig'])
			{
				$GLOBALS['phpgw']->template->parse('V_checkbox_sig','B_checkbox_sig');
			}
			else
			{
				$GLOBALS['phpgw']->template->set_var('V_checkbox_sig','');
			}
			
			$GLOBALS['phpgw']->msg->end_request();
			
			// we are the BO and the UI, we take care of outputting the HTML to the client browser
			$GLOBALS['phpgw']->template->pfp('out','T_compose_out');
		}
	}
?>
