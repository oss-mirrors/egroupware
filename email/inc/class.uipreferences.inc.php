<?php
  /**************************************************************************\
  * phpGroupWare - E-Mail                                                    *
  * http://www.phpgroupware.org                                              *
  * Based on Aeromail by Mark Cushman <mark@cushman.net>                     *
  *          http://the.cushman.net/                                         *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

	class uipreferences
	{
		var $public_functions = array(
			'preferences' => True,
		);

		var $bo;
		var $nextmatchs;
		var $template;
		var $theme;
		var $prefs;

		function uipreferences()
		{
			$this->nextmatchs = CreateObject('phpgwapi.nextmatchs');
			$this->template = $GLOBALS['phpgw']->template;
			$this->theme = $GLOBALS['phpgw_info']['theme'];
			// enabled BO object for additional prefs capabilities
			$this->bo = CreateObject('email.bopreferences');
			$temp_prefs = $GLOBALS['phpgw']->preferences->create_email_preferences();
			$this->prefs = $temp_prefs['email'];
		}
		
		/*!
		@function create_prefs_block
		@abstract create 2 columns TR's (TableRows) from preference data as standardized in email 
		bopreferences class vars ->std_prefs[]  and ->cust_prefs[], various HTML widgets supported
		@param $feed_prefs : array : preference data as standardized in email bopreferences class 
		vars ->std_prefs[]  and ->cust_prefs[]
		@result : string : HTML data accumulated for parsed prefernce widget TR's
		@discussion  email bopreferences class vars ->std_prefs[]  and ->cust_prefs[], as filled by
		email bopreferences->init_available_prefs(), represent a standardized preferences schema,
		this function generates TR's from that data, using elements "id", "widget", "other_props", 
		"lang_blurb", and "values" from that array structure. This function uses that data to fill 
		a template block that contatains the requested widget and the appropriate data. <br>
		Available HTML widgets are: <br>
			* textarea	<br>
			* textbox	<br>
			* passwordbox	<br>
			* combobox	<br>
			* checkbox	<br>
		If prefs data "other_props" contains "hidden", as with password data, then the actual 
		preference value is not shown and the "text blurb" is appended with "(hidden)".
		Array can contain any number of preference "records", all generated TR's are cumulative.
		@author	Angles
		@access	Private
		*/
		function create_prefs_block($feed_prefs=array())
		{
			$return_block = '';
			if (count($feed_prefs) == 0)
			{
				return $return_block;
			}
			// initialial backcolor, will be alternated between row_on and row_off
			$back_color = $this->theme['row_off'];
			$c_prefs = count($feed_prefs);
			// ---  Prefs Loops  ---
			for($i=0;$i<$c_prefs;$i++)
			{
				$this_item = $feed_prefs[$i];
				$back_color = $this->nextmatchs->alternate_row_color($back_color);
				
				$var = Array(
					'back_color'	=> $back_color,
					'lang_blurb'	=> $this_item['lang_blurb'],
					'pref_id'	=> $this_item['id'],
					'extra_text'	=> ''
				);
				$this->template->set_var($var);
				
				// DEBUG
				// echo 'pref item loop ['.$i.']:  &nbsp; '; var_dump($this_item); echo '<br><br>';
				
				// we don't want to show a hidden value
				if (!stristr($this_item['other_props'], 'hidden'))
				{
					$this_item_value = $this->prefs[$this_item['id']];
				}
				else
				{
					// if the data is hidden (ex. a password), we do not show the value (obviously)
					$this_item_value = '';
					// tell user we are hiding the value (that's whay the box is empty)
					$prev_lang_blurb = $this->template->get_var('lang_blurb');
					$this->template->set_var('lang_blurb', $prev_lang_blurb.'&nbsp('.lang('hidden').')');
				}
				
				// ** possible widget are: **
				// textarea
				// textbox
				// passwordbox
				// combobox
				// checkbox
				if ($this_item['widget'] == 'textarea')
				{
					$this_item_value = $this->prefs[$this_item['id']];
					$this->template->set_var('pref_value', $this_item_value);
					$this->template->parse('V_tr_textarea','B_tr_textarea');
					$done_widget = $this->template->get_var('V_tr_textarea');	
				}
				elseif ($this_item['widget'] == 'textbox')
				{
					$this->template->set_var('pref_value', $this_item_value);
					$this->template->parse('V_tr_textbox','B_tr_textbox');
					$done_widget = $this->template->get_var('V_tr_textbox');	
				}
				elseif ($this_item['widget'] == 'passwordbox')
				{
					// this_item_value should have been set to blank above
					// if $this_item['other_props'] contains the word "hidden"
					$this->template->set_var('pref_value', $this_item_value);
					$this->template->parse('V_tr_passwordbox','B_tr_passwordbox');
					$done_widget = $this->template->get_var('V_tr_passwordbox');	
				}
				elseif ($this_item['widget'] == 'combobox')
				{
					// set up combobox available options as KEYS array with empty VALUES
					reset($this_item['values']);
					$combo_availables = Array();
					$x = 0; 
					while ( list ($key,$prop) = each ($this_item['values']))
					{
						$combo_availables[$key]	= '';
						$x++;
					}
					// fill the pref item in $combo_availables[this_item_value] to " selected"
					$combo_available[$this->prefs[$this_item['id']]] = ' selected';
					// make the combobox HTML tags string
					$combobox_html = '';
					reset($this_item['values']);
					$x = 0;
					while ( list ($key,$prop) = each ($this_item['values']))
					{
						$combobox_html .= 
							'<option value="'.$key.'"'.$combo_available[$key].'>'.$prop.'</option>' ."\r\n";
						$x++;
					}
					$this_item_value = $combobox_html;
					$this->template->set_var('pref_value', $this_item_value);
					$this->template->parse('V_tr_combobox','B_tr_combobox');
					$done_widget = $this->template->get_var('V_tr_combobox');	
				}
				elseif ($this_item['widget'] == 'checkbox')
				{
					if (isset($this->prefs[$this_item['id']]))
					{
						$this_item_value = 'checked';
					}
					else
					{
						$this_item_value = '';
					}
					$this->template->set_var('pref_value', $this_item_value);
					$this->template->parse('V_tr_checkbox','B_tr_checkbox');
					$done_widget = $this->template->get_var('V_tr_checkbox');	
				}
				else
				{
					//$this->pref_errors .= 'call for unsupported widget:'.$this_item['widget'].'<br>';
					$this->template->set_var('back_color', $back_color);
					$this->template->set_var('section_title', 'call for unsupported widget:'.$this_item['widget']);
					$this->template->parse('V_tr_sec_title','B_tr_sec_title');
					$done_widget = $this->template->get_var('V_tr_sec_title');	
				}
				// for each loop, add the finished widget row to the return_block variable
				$return_block .= $done_widget;
			}
			return $return_block;
		}
		
		/*!
		@function preferences
		@abstract call this function to display the typical UI html page for email preferences
		@author	Angles, skeeter
		@access	Public
		*/
		function preferences()
		{
			unset($GLOBALS['phpgw_info']['flags']['noheader']);
			unset($GLOBALS['phpgw_info']['flags']['nonavbar']);
			$GLOBALS['phpgw_info']['flags']['noappheader'] = True;
			$GLOBALS['phpgw_info']['flags']['noappfooter'] = True;
			$GLOBALS['phpgw']->common->phpgw_header();
			
			$this->template->set_file(
				Array(
					'T_prefs_ui_out'	=> 'class_prefs_ui.tpl',
					'T_pref_blocks'		=> 'class_prefs_blocks.tpl'
				)
			);
			$this->template->set_block('T_pref_blocks','B_tr_blank','V_tr_blank');
			$this->template->set_block('T_pref_blocks','B_tr_sec_title','V_tr_sec_title');
			$this->template->set_block('T_pref_blocks','B_tr_textarea','V_tr_textarea');
			$this->template->set_block('T_pref_blocks','B_tr_textbox','V_tr_textbox');
			$this->template->set_block('T_pref_blocks','B_tr_passwordbox','V_tr_passwordbox');
			$this->template->set_block('T_pref_blocks','B_tr_combobox','V_tr_combobox');
			$this->template->set_block('T_pref_blocks','B_tr_checkbox','V_tr_checkbox');
			
			$var = Array(
				'pref_errors'		=> '',
				'page_title'		=> lang('E-Mail preferences'),
				'form_action'		=> $GLOBALS['phpgw']->link('/index.php',
					Array(
						'menuaction'	=> 'email.bopreferences.preferences'
					)
				),
				'th_bg'			=> $this->theme['th_bg'],
				'left_col_width'	=> '50%',
				'right_col_width'	=> '50%',
				'checked_flag'		=> 'True',
				'btn_submit_name'	=> $this->bo->submit_token,
				'btn_submit_value'	=> lang('submit')
			);
			$this->template->set_var($var);
			
			// this will fill the $this->bo->std_prefs[] and cust_prefs[]  "schema" arrays
			$this->bo->init_available_prefs();			
			// DEBUG
			//$this->bo->debug_dump_prefs();
			//return;
			
			// initialize a local var to hold the cumulative main block data
			$prefs_ui_rows = '';
			
			// ---  Standars Prefs  ---
			// section title for standars prefs
			$this->template->set_var('section_title', lang('Standard').' '.lang('E-Mail preferences'));
			// parse the block,
			$this->template->parse('V_tr_sec_title','B_tr_sec_title');
			// get the parsed data and put into a local variable
			$done_widget = $this->template->get_var('V_tr_sec_title');	
			// add the finished widget row to the main block variable
			$prefs_ui_rows .= $done_widget;
			// generate Std Prefs HTML Block
			$prefs_ui_rows .= $this->create_prefs_block($this->bo->std_prefs);
			
			// blank row
			$this->template->set_var('back_color', $this->theme['bg_color']);
			$this->template->parse('V_tr_blank','B_tr_blank');
			$done_widget = $this->template->get_var('V_tr_blank');	
			$prefs_ui_rows .= $done_widget;
			
			// ---  Custom Prefs  ---
			$this->template->set_var('section_title', lang('Custom').' '.lang('E-Mail preferences'));
			$this->template->parse('V_tr_sec_title','B_tr_sec_title');
			$done_widget = $this->template->get_var('V_tr_sec_title');	
			$prefs_ui_rows .= $done_widget;
			// generate Custom Prefs HTML Block
			$prefs_ui_rows .= $this->create_prefs_block($this->bo->cust_prefs);
			
			// blank row
			$this->template->set_var('back_color', $this->theme['bg_color']);
			$this->template->parse('V_tr_blank','B_tr_blank');
			$done_widget = $this->template->get_var('V_tr_blank');	
			$prefs_ui_rows .= $done_widget;
			
			// ---  Commit HTML Prefs rows to Main Template
			// put all widget rows data into the template var
			$this->template->set_var('prefs_ui_rows', $prefs_ui_rows);
			// output the template
			$this->template->pparse('out','T_prefs_ui_out');
		}
	}
?>
