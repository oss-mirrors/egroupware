<?php
  /**************************************************************************\
  * phpGroupWare - Setup                                                     *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

	class phpgw_setup_html extends phpgw_setup_lang
	{
		var $db;

		/*!
		@function generate_header
		@abstract generate header.inc.php file output - NOT a generic html header function
		*/	
		function generate_header()
		{
			global $setting, $phpgw_setup, $phpgw_info, $header_template;

			$header_template->set_file(array('header' => 'header.inc.php.template'));
			while(list($k,$v) = each($setting))
			{
				$header_template->set_var(strtoupper($k),$v);
			}
			return $header_template->parse('out','header');
		}

		function setup_tpl_dir($app_name='setup')
		{
			global $phpgw_info;

			// hack to get tpl dir
			if (is_dir(PHPGW_SERVER_ROOT))
			{
				$srv_root = PHPGW_SERVER_ROOT . SEP . "$app_name" . SEP;
			}
			else
			{
				$srv_root = '';
			}

			$tpl_typical = 'templates' . SEP . 'default';
			$tpl_root = "$srv_root" ."$tpl_typical";
			return $tpl_root;
		}

		function show_header($title = '',$nologoutbutton = False, $logoutfrom = 'config', $configdomain = '')
		{
			global $phpgw_info, $setup_tpl, $HTTP_SERVER_VARS;

			$setup_tpl->set_var('lang_charset',lang('charset'));
			if ($nologoutbutton)
			{
				$btn_logout = '&nbsp;';
			}
			else
			{
				$btn_logout = '<a href="'.basename($HTTP_SERVER_VARS['REQUEST_URI']).'?FormLogout='.$logoutfrom.'" class="link">'.lang('Logout').'</a>';
			}

			$setup_tpl->set_var('lang_setup', lang('setup'));
			$setup_tpl->set_var('page_title',$title);
			if ($configdomain == '')
			{
				$setup_tpl->set_var('configdomain',"");
			}
			else
			{
				$setup_tpl->set_var('configdomain',' - ' . lang('Domain') . ': '.$configdomain);
			}
			$setup_tpl->set_var('pgw_ver',$phpgw_info['server']['versions']['phpgwapi']);
			$setup_tpl->set_var('logoutbutton',$btn_logout);
			$setup_tpl->pparse('out','T_head');
			//$setup_tpl->set_var('T_head','');
		}

		function show_footer()
		{
			global $phpgw_info, $setup_tpl;

			$setup_tpl->pparse('out','T_footer');
			unset($setup_tpl);
		}

		function show_alert_msg($alert_word='Setup alert',$alert_msg='setup alert (generic)')
		{
			global $phpgw_info, $setup_tpl;

			$setup_tpl->set_var('V_alert_word',$alert_word);
			$setup_tpl->set_var('V_alert_msg',$alert_msg);
			$setup_tpl->pparse('out','T_alert_msg');
		}

		function make_frm_btn_simple($pre_frm_blurb='',$frm_method='POST',$frm_action='',$input_type='submit',$input_value='',$post_frm_blurb='')
		{
			// are these golbals necessary?
			global $phpgw_info, $setup_tpl;

			// a simple form has simple components
			$simple_form = 
				$pre_frm_blurb  ."\n"
				.'<form method="'.$frm_method.'" action="'.$frm_action.'">'  ."\n"
				.'<input type="'.$input_type.'" value="'.$input_value.'">'  ."\n"
				.'</form>'  ."\n"
				.$post_frm_blurb  ."\n";
			return $simple_form;
		}

		function make_href_link_simple($pre_link_blurb='',$href_link='',$href_text='default text',$post_link_blurb='')
		{
			// are these golbals necessary?
			global $phpgw_info, $setup_tpl;

			// a simple href link has simple components
			$simple_link =
				 $pre_link_blurb
				.'<a href="' .$href_link .'">' .$href_text .'</a> '
				.$post_link_blurb  ."\n";
			return $simple_link;
		}

		function login_form()
		{
			global $phpgw_info, $phpgw_domain, $setup_tpl;
			
			// begin use TEMPLATE login_main.tpl
			$setup_tpl->set_var('ConfigLoginMSG',$phpgw_info['setup']['ConfigLoginMSG']);
			$setup_tpl->set_var('HeaderLoginMSG',$phpgw_info['setup']['HeaderLoginMSG']);

			if ($phpgw_info['setup']['stage']['header'] == '10')
			{
				// begin use SUB-TEMPLATE login_stage_header,
				// fills V_login_stage_header used inside of login_main.tpl
				$setup_tpl->set_var('lang_select',lang_select());
				if (count($phpgw_domain) > 1)
				{
					// use BLOCK B_multi_domain inside of login_stage_header
					$setup_tpl->parse('V_multi_domain','B_multi_domain');
					// in this case, the single domain block needs to be nothing
					$setup_tpl->set_var('V_single_domain','');
				}
				else
				{
					reset($phpgw_domain);
					$default_domain = each($phpgw_domain);
					$setup_tpl->set_var('default_domain_zero',$default_domain[0]);
					
					// use BLOCK B_single_domain inside of login_stage_header
					$setup_tpl->parse('V_single_domain','B_single_domain');
					// // in this case, the multi domain block needs to be nothing
					$setup_tpl->set_var('V_multi_domain','');
				}
				// end use SUB-TEMPLATE login_stage_header
				// put all this into V_login_stage_header for use inside login_main
				$setup_tpl->parse('V_login_stage_header','T_login_stage_header');
			}
			else
			{
				// begin SKIP SUB-TEMPLATE login_stage_header
				$setup_tpl->set_var('V_multi_domain','');
				$setup_tpl->set_var('V_single_domain','');
				$setup_tpl->set_var('V_login_stage_header','');
			}
			// end use TEMPLATE login_main.tpl
			// now out the login_main template
			$setup_tpl->pparse('out','T_login_main');
		}

		function get_template_list()
		{
			global $phpgw_info;
			$d = dir(PHPGW_SERVER_ROOT . '/phpgwapi/templates');
			//$list['user_choice']['name'] = 'user_choice';
			//$list['user_choice']['title'] = 'Users Choice';
			while($entry=$d->read())
			{
				if ($entry != 'CVS' && $entry != '.' && $entry != '..')
				{
					$list[$entry]['name'] = $entry;
					$f = PHPGW_SERVER_ROOT . '/phpgwapi/templates/' . $entry . '/details.inc.php';
					if (file_exists ($f))
					{
						include($f);
						$list[$entry]['title'] = 'Use ' . $phpgw_info['template'][$entry]['title'] . 'interface';
					}
					else
					{
						$list[$entry]['title'] = $entry;
					}
				}
			}
			$d->close();
			reset ($list);
			return $list;
		}

		function list_themes()
		{
			$dh = dir(PHPGW_SERVER_ROOT . '/phpgwapi/themes');
			while ($file = $dh->read())
			{
				if (eregi("\.theme$", $file))
				{
					$list[] = substr($file,0,strpos($file,'.'));
				}
			}
			$dh->close();
			reset ($list);
			return $list;
		}
	}
?>
