<?php
	/**************************************************************************\
	* phpGroupWare - Registration                                              *
	* http://www.phpgroupware.org                                              *
	* This application written by Joseph Engo <jengo@phpgroupware.org>         *
	* Modified by Jason Wies (Zone) <zone@users.sourceforge.net>               *
	* Modified by Loic Dachary <loic@gnu.org>                                  *
	* --------------------------------------------                             *
	* Funding for this program was provided by http://www.checkwithmom.com     *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	class uireg
	{
		var $template;
		var $bomanagefields;
		var $fields;
		var $bo;
		var $public_functions = array(
			'step1'   => True,
			'step2'   => True,
			'lostpw1' => True,
			'lostpw3' => True,
			'lostpw4' => True,
			'ready_to_activate' => True,
			'email_sent_lostpw' => True,
			'tos'     => True
		);

		function uireg()
		{
			$this->template = $GLOBALS['phpgw']->template;
			$this->bo = createobject ('registration.boreg');
			$this->bomanagefields = createobject ('registration.bomanagefields');
			$this->fields = $this->bomanagefields->get_field_list ();
		}

		function set_header_footer_blocks()
		{
			$this->template->set_file(array(
				'_layout' => 'layout.tpl'
			));
			$this->template->set_block('_layout','header');
			$this->template->set_block('_layout','footer');
		}

		function header()
		{
			$this->set_header_footer_blocks();
			$this->template->set_var('lang_header',lang('phpGroupWare - Account registration'));
			$this->template->pfp('out','header');
		}

		function footer()
		{
			$this->template->pfp('out','footer');
		}

		function step1($errors = '',$r_reg = '',$o_reg = '')
		{
			global $config;

			if ($errors && $config['username_is'] == 'http')
			{
				$this->simple_screen ('error_general.tpl', $GLOBALS['phpgw']->common->error_list ($errors));
			}

			$show_username_prompt = True;
			/* Note that check_select_username () may not return */
			$select_username = $this->bo->check_select_username ();
			if (!$select_username || is_string ($select_username))
			{
				$this->simple_screen ('error_general.tpl', $GLOBALS['phpgw']->common->error_list (array ($select_username)));
			}

			$this->header();
			$this->template->set_file(array(
				'_loginid_select' => 'loginid_select.tpl'
			));
			$this->template->set_block('_loginid_select','form');

			if ($errors)
			{
				$this->template->set_var('errors',$GLOBALS['phpgw']->common->error_list($errors));
			}

			$this->template->set_var('form_action',$GLOBALS['phpgw']->link('/registration/main.php','menuaction=registration.boreg.step1'));
			$this->template->set_var('lang_username',lang('Username'));
			$this->template->set_var('lang_submit',lang('Submit'));

			$this->template->pfp('out','form');

			$this->footer();
		}

		function step2($errors = '',$r_reg = '',$o_reg = '',$missing_fields='')
		{
			global $config;

			$show_password_prompt = True;
			$select_password = $this->bo->check_select_password ();
			if (is_string ($select_password))
			{
				$this->simple_screen ('error_general.tpl', $select_password);
			}
			elseif (!$select_password)
			{
				$show_password_prompt = False;
			}

			$this->header();
			$this->template->set_file(array(
				'_personal_info' => 'personal_info.tpl'
			));
			$this->template->set_block('_personal_info','form');

			if ($errors)
			{
				$this->template->set_var('errors',$GLOBALS['phpgw']->common->error_list($errors));
			}

			if ($missing_fields)
			{
				while (list(,$field) = each($missing_fields))
				{
					$missing[$field] = True;
					$this->template->set_var('missing_' . $field,'<font color="#CC0000">*</font>');
				}
			}

			if (is_array($r_reg))
			{
				while (list($name,$value) = each($r_reg))
				{
					$post_values[$name] = $value;
					$this->template->set_var('value_' . $name,$value);
				}
			}

			if (is_array($o_reg))
			{
				while (list($name,$value) = each($o_reg))
				{
					$post_values[$name] = $value;
					$this->template->set_var('value_' . $name,$value);
				}
			}

			$this->template->set_var('form_action',$GLOBALS['phpgw']->link('/registration/main.php','menuaction=registration.boreg.step2'));
			$this->template->set_var('lang_password',lang('Password'));
			$this->template->set_var('lang_reenter_password',lang('Re-enter password'));
			$this->template->set_var('lang_submit',lang('Submit'));

			if (!$show_password_prompt)
			{
				$this->template->set_block ('form', 'password', 'empty');
			}

			$this->template->set_block ('form', 'other_fields_proto', 'other_fields_list');

			reset ($this->fields);
			while (list ($num, $field_info) = each ($this->fields))
			{
				$input_field = $this->get_input_field ($field_info, $post_values);
				$var = array (
					'missing_indicator' => $missing[$field_info['field_name']] ? '<font color="#CC0000">*</font>' : '',
					'bold_start'  => $field_info['field_required'] == 'Y' ? '<b>' : '',
					'bold_end'    => $field_info['field_required'] == 'Y' ? '</b>' : '',
					'lang_displayed_text' => lang ($field_info['field_text']),
					'input_field' => $input_field
				);

				$this->template->set_var ($var);

				$this->template->parse ('other_fields_list', 'other_fields_proto', True);
			}

			if ($config['display_tos'])
			{
			$this->template->set_var('tos_link',$GLOBALS['phpgw']->link('/registration/main.php','menuaction=registration.uireg.tos'));
			$this->template->set_var('lang_tos_agree',lang('I have read the terms and conditions and agree by them.'));
				if ($r_reg['tos_agree'])
				{
					$this->template->set_var('value_tos_agree', 'checked');
				}
			}
			else
			{
				$this->template->set_block ('form', 'tos', 'blank');
			}

			$this->template->pfp('out','form');
			$this->footer();
		}

		//
		// username
		//
		function lostpw1($errors = '',$r_reg = '')
		{
			$this->header();
			$this->template->set_file(array(
				'_lostpw_select' => 'lostpw_select.tpl'
			));
			$this->template->set_block('_lostpw_select','form');

			if ($errors)
			{
				$this->template->set_var('errors',$GLOBALS['phpgw']->common->error_list($errors));
			}

			$this->template->set_var('form_action',$GLOBALS['phpgw']->link('/registration/main.php','menuaction=registration.boreg.lostpw1'));
			$this->template->set_var('lang_explain',lang('After you enter your username, instructions to change your password will be sent to you by e-mail to the address you gave when you registered.'));
			$this->template->set_var('lang_username',lang('Username'));
			$this->template->set_var('lang_submit',lang('Submit'));

			$this->template->pfp('out','form');
			$this->footer();
		}

		//
		// change password
		//
		function lostpw3($errors = '',$r_reg = '',$lid = '')
		{
			$this->header();
			$this->template->set_file(array(
				'_lostpw_change' => 'lostpw_change.tpl'
			));
			$this->template->set_block('_lostpw_change','form');

			if ($errors)
			{
				$this->template->set_var('errors',$GLOBALS['phpgw']->common->error_list($errors));
			}

			$this->template->set_var('form_action',$GLOBALS['phpgw']->link('/registration/main.php','menuaction=registration.boreg.lostpw3'));
			$this->template->set_var('value_username', $lid);
			$this->template->set_var('lang_changepassword',lang("Change password for user"));
			$this->template->set_var('lang_enter_password',lang('Enter your new password'));
			$this->template->set_var('lang_reenter_password',lang('Re-enter your password'));
			$this->template->set_var('lang_change',lang('Change'));

			$this->template->pfp('out','form');
			$this->footer();
		}

		//
		// password was changed
		//
		function lostpw4()
		{
			$this->header();
			$this->template->set_file(array(
				'screen' => 'lostpw_changed.tpl'
			));
			$this->template->set_var('login_url',$GLOBALS['phpgw_info']['server']['webserver_url']);

			$this->template->pfp('out','screen');
			$this->footer();
		}

		function get_input_field ($field_info, $post_values)
		{
			global $r_regs, $o_regs;

			$post_value = $post_values[$field_info['field_name']];

			$name = $field_info['field_name'];
			$values = explode (",", $field_info['field_values']);
			$required = $field_info['field_required'];
			$type = $field_info['field_type'];

			if (!$type)
			{
				$type = 'text';
			}

			if ($type == 'gender')
			{
				$values = array (
					'Male',
					'Female'
				);

				$type = 'dropdown';
			}

			if ($required == 'Y')
			{
				$a = 'r_reg';
			}
			else
			{
				$a = 'o_reg';
			}

			if ($type == 'text' || $type == 'email' || $type == 'first_name' ||
				$type == 'last_name' || $type == 'address' || $type == 'city' ||
				$type == 'zip' || $type == 'phone')
			{
				$rstring = '<input type=text name="' . $a . '[' . $name . ']" value="' . $post_value . '">';
			}

			if ($type == 'textarea')
			{
				$rstring = '<textarea name="' . $a . '[' . $name . ']" value="' . $post_value . '" cols="40" rows="5">' . $post_value . '</textarea>';
			}

			if ($type == 'dropdown')
			{
				if (!is_array ($values))
				{
					$rstring = "Error: Dropdown list '$name' has no values";
				}
				else
				{
					$rstring = '<select name="' . $a . '[' . $name . ']"><option value=""> </option>';
					while (list (,$value) = each ($values))
					{
						$value = trim ($value);

						unset ($selected);
						if ($value == $post_value)
						{
							$selected = "selected";
						}

						$rstring .= '<option value="' . $value . '" ' . $selected . '>' . $value . '</option>';
					}

					$rstring .= "</select>";
				}
			}

			if ($type == 'checkbox')
			{
				unset ($checked);
				if ($post_value)
					$checked = "checked";

				$rstring = '<input type=checkbox name="' . $a . '[' . $name . ']" ' . $checked . '>';
			}

			if ($type == 'birthday' || $type == 'state' || $type == 'country')
			{
				$sbox = createobject ('phpgwapi.sbox');
			}

			if ($type == 'state')
			{
				$rstring = $sbox->list_states ($a . '[' . $name . ']', $post_value);
			}

			if ($type == 'country')
			{
				$rstring = $sbox->form_select ($post_value, $a . '[' . $name . ']');
			}

			if ($type == 'birthday')
			{
				$rstring = $sbox->getmonthtext ($a . '[' . $name . '_month]', $post_values[$name . '_month']);
				$rstring .= $sbox->getdays ($a . '[' . $name . '_day]', $post_values[$name . '_day']);
				$rstring .= $sbox->getyears ($a . '[' . $name . '_year]', $post_values[$name . '_year'], 1900, date ('Y') + 1);
			}

			return $rstring;
		}

		function simple_screen($template_file, $text = '')
		{
			$this->header();
			$this->template->set_file(array(
				'screen' => $template_file
			));

			if ($text)
			{
				$this->template->set_var ('extra_text', $text);
			}

			$this->template->pfp('out','screen');
			$this->footer();
			exit;
		}

		function ready_to_activate()
		{
			global $config, $reg_id;

			if ($config['activate_account'] == 'email')
			{
				$this->simple_screen('confirm_email_sent.tpl');
			}
			else
			{
				/* ($config['activate_account'] == 'immediately') */
				$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/registration/main.php','menuaction=registration.boreg.step4&reg_id=' . $reg_id));
			}
		}

		function email_sent_lostpw()
		{
			$this->simple_screen('confirm_email_sent_lostpw.tpl');
		}

		function welcome_screen()
		{
			$this->header();
			$this->template->set_file(array(
				'screen' => 'welcome_message.tpl'
			));
			$this->template->set_var('login_url',$GLOBALS['phpgw_info']['server']['webserver_url']);

			$this->template->pfp('out','screen');
			$this->footer();
		}

		function tos()
		{
			$this->simple_screen('tos.tpl');
		}
	}
