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
		var $debug = False;
		var $is_modular = False;

		var $public_functions = array(
			'compose' => True,
			'get_is_modular' => True,
			'set_is_modular' => True
		);

		function uicompose()
		{
			
		}

		function get_is_modular()
		{
			return $this->is_modular;
		}
		
		function set_is_modular($feed_bool=False)
		{
			// is_bool() is in the php3 compat library
			if ((isset($feed_bool))
			&& (is_bool($feed_bool)))
			{
				// only change this if the arg is boolean
				$this->is_modular = $feed_bool;
			}
			return $this->is_modular;
		}
		
		function compose($reuse_feed_args='')
		{
			if (empty($reuse_feed_args))
			{
				$reuse_feed_args = array();
			}
			
			$this->bo = CreateObject("email.bocompose");
			$this->bo->compose($reuse_feed_args);
			
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
					'T_compose_out' => 'compose.tpl'
				)
			);
			$GLOBALS['phpgw']->template->set_block('T_compose_out','B_checkbox_sig','V_checkbox_sig');
			
			// fill template vars
			$tpl_vars = Array(
				'to_box_value'		=> $this->bo->xi['to_box_value'],
				'cc_box_value'		=> $this->bo->xi['cc_box_value'],
				'bcc_box_value'		=> $this->bo->xi['bcc_box_value'],
				'subj_box_value'	=> $this->bo->xi['subject'],
				'body_box_value'	=> $this->bo->xi['body'],
				'form1_action'		=> $this->bo->xi['send_btn_action'],
				
				'form1_name'		=> $this->bo->xi['form1_name'],
				'form1_method'		=> $this->bo->xi['form1_method'],
				'js_addylink'		=> $this->bo->xi['js_addylink'],
				'buttons_bgcolor'	=> $this->bo->xi['buttons_bgcolor'],
				'btn_addybook_type'	=> $this->bo->xi['btn_addybook_type'],
				'btn_addybook_value'	=> $this->bo->xi['btn_addybook_value'],
				'btn_addybook_onclick'	=> $this->bo->xi['btn_addybook_onclick'],
				'btn_send_type'		=> $this->bo->xi['btn_send_type'],
				'btn_send_value'	=> $this->bo->xi['btn_send_value'],
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
				'attachfile_js_link'	=> $this->bo->xi['attachfile_js_link'],
				'attachfile_js_text'	=> $this->bo->xi['attachfile_js_text'],
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
			
			if ($this->is_modular == True)
			{
				// we do NOT output any html, we are being used as a module in another app
				// instead, we will pass the parsed html to the calling app
				return $GLOBALS['phpgw']->template->fp('out','T_compose_out');
			}
			else
			{
				// we are the BO and the UI, we take care of outputting the HTML to the client browser
				$GLOBALS['phpgw']->template->pfp('out','T_compose_out');
			}
		}
	}
?>
