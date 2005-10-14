<?php
	/**************************************************************************\
	* eGroupWare - news headlines                                              *
	* http://www.egroupware.org                                                *
	* Written by Mark Peters <mpeters@satx.rr.com>                             *
	* Based on pheadlines 0.1 19991104 by Dan Steinman <dan@dansteinman.com>   *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	class uiheadlines
	{
		var $public_functions = array(
			'admin' => True,
			'index' => True,
			'view'  => True,
			'add'   => True,
			'edit'  => True,
			'delete' => True,
			'preferences' => True,
			'preferences_layout' => True,
			'grabnewssites' => True
		);

		function uiheadlines()
		{
			$this->bo =& CreateObject('headlines.boheadlines');
		}

		function index()
		{
			if(!count($GLOBALS['egw_info']['user']['preferences']['headlines']))
			{
				$GLOBALS['egw']->redirect_link('/index.php','menuaction=headlines.uiheadlines.preferences');
			}
			else
			{
				$GLOBALS['egw']->common->egw_header();
				echo parse_navbar();
			}

			$this->bo->getsites();

//			$headlines =& CreateObject('headlines.headlines');
			$GLOBALS['egw']->template->set_file(array(
				'layout_row' => 'layout_row.tpl',
				'form'       => $GLOBALS['egw_info']['user']['preferences']['headlines']['headlines_layout'] . '.tpl'
			));
			$GLOBALS['egw']->template->set_block('form','channel');
			$GLOBALS['egw']->template->set_block('form','row');

			$j = 0;
			$i = count($this->bo->sites);
			if(is_array($this->bo->sites))
			{
				foreach($this->bo->sites as $site)
				{
					$j++;
					$this->bo->readtable($site);

					$GLOBALS['egw']->template->set_var('channel_url',$this->bo->base_url);
					$GLOBALS['egw']->template->set_var('channel_title',$this->bo->display);

					$links = $this->bo->getLinks($site);
					if($links == False)
					{
						$var = Array(
							'item_link'  => '',
							'item_label' => '',
							'error'      => lang('Unable to retrieve links').'.'
						);
						$GLOBALS['egw']->template->set_var($var);
						$s .= $GLOBALS['egw']->template->parse('o_','row');
					}
					else
					{
						while(list($title,$link) = each($links))
						{
							$var = Array(
								'item_link'  => stripslashes($link),
								'item_label' => stripslashes($title),
								'error'      => ''
							);
							$GLOBALS['egw']->template->set_var($var);
							$s .= $GLOBALS['egw']->template->parse('o_','row');
						}
					}
					$GLOBALS['egw']->template->set_var('rows',$s);
					unset($s);

					$GLOBALS['egw']->template->set_var('section_' . $j,$GLOBALS['egw']->template->parse('o','channel'));

					if($j == 3 || $i == 1)
					{
						$GLOBALS['egw']->template->pfp('out','layout_row');
						$GLOBALS['egw']->template->set_var('section_1', '');
						$GLOBALS['egw']->template->set_var('section_2', '');
						$GLOBALS['egw']->template->set_var('section_3', '');
						$j = 0;
					}
					$i--;
				}
			}
			$GLOBALS['egw']->common->egw_footer();
		}

		function add()
		{
			if($_POST['cancel'])
			{
				$GLOBALS['egw']->redirect_link('/index.php','menuaction=headlines.uiheadlines.admin&cd=28');
			}

			if($_POST['save'])
			{
				$n_display   = get_var('n_display','POST');
				$n_base_url  = get_var('n_base_url','POST');
				$n_newsfile  = get_var('n_newsfile','POST');
				$n_cachetime = get_var('n_cachetime','POST');
				$n_listings  = get_var('n_listings','POST');
				$n_base_url  = get_var('n_base_url','POST');
				$n_newstype  = get_var('n_newstype','POST');

				$errors = $this->bo->edit(array(
					'display'   => $n_display,
					'base_url'  => $n_base_url,
					'newsfile'  => $n_newsfile,
					'cachetime' => $n_cachetime,
					'listings'  => $n_listings,
					'base_url'  => $n_base_url,
					'newstype'  => $n_newstype
				));

				if(is_array($errors) && !isset($errors['con']))
				{
					$GLOBALS['egw']->template->set_var('messages',$GLOBALS['egw']->common->error_list($errors));
				}
				else
				{
					$GLOBALS['egw']->redirect_link('/index.php','menuaction=headlines.uiheadlines.admin&cd=28');
				}
			}

			$GLOBALS['egw_info']['flags']['app_header'] = lang('Headlines Administration');
			$GLOBALS['egw']->common->egw_header();
			echo parse_navbar();

			// This is done for a reason (jengo)
			$GLOBALS['egw']->template->set_root($GLOBALS['egw']->common->get_tpl_dir('headlines'));

			$GLOBALS['egw']->template->set_file(array(
				'admin_form' => 'admin_form.tpl'
			));
			$GLOBALS['egw']->template->set_block('admin_form','form');
			$GLOBALS['egw']->template->set_block('admin_form','buttons');

			$GLOBALS['egw']->template->set_var('lang_header',lang('Create new headline'));
			$GLOBALS['egw']->template->set_var('th_bg',$GLOBALS['egw_info']['theme']['th_bg']);
			$GLOBALS['egw']->template->set_var('row_on',$GLOBALS['egw_info']['theme']['row_on']);
			$GLOBALS['egw']->template->set_var('row_off',$GLOBALS['egw_info']['theme']['row_off']);
			$GLOBALS['egw']->template->set_var('lang_display',lang('Display'));
			$GLOBALS['egw']->template->set_var('lang_base_url',lang('Base URL'));
			$GLOBALS['egw']->template->set_var('lang_news_file',lang('News File'));
			$GLOBALS['egw']->template->set_var('lang_minutes',lang('Minutes between refresh'));
			$GLOBALS['egw']->template->set_var('lang_listings',lang('Listings Displayed'));
			$GLOBALS['egw']->template->set_var('lang_type',lang('News Type'));
			$GLOBALS['egw']->template->set_var('lang_save',lang('Save'));
			$GLOBALS['egw']->template->set_var('lang_cancel',lang('Cancel'));

			$GLOBALS['egw']->template->set_var('input_display','<input name="n_display" value="' . $n_display . '" size="40">');
			$GLOBALS['egw']->template->set_var('input_base_url','<input name="n_base_url" value="' . $n_base_url . '" size="40">');
			$GLOBALS['egw']->template->set_var('input_news_file','<input name="n_newsfile" value="' . $n_newsfile . '" size="40">');
			$GLOBALS['egw']->template->set_var('input_minutes','<input name="n_cachetime" value="' . $n_cachetime . '" size="4">');
			$GLOBALS['egw']->template->set_var('input_listings','<input name="n_listings" value="' . $n_listings . '" size="2">');

			$news_type = array('rdf','fm','lt','sf','rdf-chan');
			while(list(,$item) = each($news_type))
			{
				$_select .= '<option value="' . $item . '"' . ($n_newstype == $item?' checked':'')
					. '>' . $item . '</option>';
			}
			$GLOBALS['egw']->template->set_var('input_type','<select name="n_newstype">' . $_select . '</select>');

			$GLOBALS['egw']->template->set_var('action_url',$GLOBALS['egw']->link('/index.php','menuaction=headlines.uiheadlines.add'));

			$GLOBALS['egw']->template->parse('buttons','buttons');
			$GLOBALS['egw']->template->pfp('out','form');
			$GLOBALS['egw']->common->egw_footer();
		}

		function edit()
		{
			if(!$_GET['con'])
			{
				$GLOBALS['egw']->redirect_link('/index.php','menuaction=headlines.uiheadlines.admin');
			}

			if($_POST['cancel'])
			{
				$GLOBALS['egw']->redirect_link('/index.php','menuaction=headlines.uiheadlines.admin');
			}

			if($_POST['save'])
			{
				$n_display   = get_var('n_display','POST');
				$n_base_url  = get_var('n_base_url','POST');
				$n_newsfile  = get_var('n_newsfile','POST');
				$n_cachetime = get_var('n_cachetime','POST');
				$n_listings  = get_var('n_listings','POST');
				$n_base_url  = get_var('n_base_url','POST');
				$n_newstype  = get_var('n_newstype','POST');

				$errors = $this->bo->edit(array(
					'con'       => $_GET['con'],
					'display'   => $n_display,
					'base_url'  => $n_base_url,
					'newsfile'  => $n_newsfile,
					'cachetime' => $n_cachetime,
					'listings'  => $n_listings,
					'base_url'  => $n_base_url,
					'newstype'  => $n_newstype
				));

				if(is_array($errors) && !isset($errors['con']))
				{
					$GLOBALS['egw']->template->set_var('messages',$GLOBALS['egw']->common->error_list($errors));
				}
				else
				{
					$GLOBALS['egw']->redirect_link('/index.php','menuaction=headlines.uiheadlines.admin');
				}
			}

			$sitedata = $this->bo->read((int)$_GET['con']);

			$GLOBALS['egw_info']['flags']['app_title'] = lang('Headlines Administration');
			$GLOBALS['egw']->common->egw_header();
			echo parse_navbar();

			// This is done for a reason (jengo)
			$GLOBALS['egw']->template->set_root($GLOBALS['egw']->common->get_tpl_dir('headlines'));

			$GLOBALS['egw']->template->set_file(array(
				'admin_form' => 'admin_form.tpl'
			));
			$GLOBALS['egw']->template->set_block('admin_form','form');
			$GLOBALS['egw']->template->set_block('admin_form','buttons');

			$GLOBALS['egw']->template->set_var('lang_header',lang('Update headline'));
			$GLOBALS['egw']->template->set_var('th_bg',$GLOBALS['egw_info']['theme']['th_bg']);
			$GLOBALS['egw']->template->set_var('row_on',$GLOBALS['egw_info']['theme']['row_on']);
			$GLOBALS['egw']->template->set_var('row_off',$GLOBALS['egw_info']['theme']['row_off']);
			$GLOBALS['egw']->template->set_var('lang_display',lang('Display'));
			$GLOBALS['egw']->template->set_var('lang_base_url',lang('Base URL'));
			$GLOBALS['egw']->template->set_var('lang_news_file',lang('News File'));
			$GLOBALS['egw']->template->set_var('lang_minutes',lang('Minutes between refresh'));
			$GLOBALS['egw']->template->set_var('lang_listings',lang('Listings Displayed'));
			$GLOBALS['egw']->template->set_var('lang_type',lang('News Type'));
			$GLOBALS['egw']->template->set_var('lang_save',lang('Save'));
			$GLOBALS['egw']->template->set_var('lang_cancel',lang('Cancel'));

			$GLOBALS['egw']->template->set_var('input_display','<input name="n_display" value="'    . $sitedata['display']   . '" size="40">');
			$GLOBALS['egw']->template->set_var('input_base_url','<input name="n_base_url" value="'  . $sitedata['base_url']  . '" size="40">');
			$GLOBALS['egw']->template->set_var('input_news_file','<input name="n_newsfile" value="' . $sitedata['newsfile']  . '" size="40">');
			$GLOBALS['egw']->template->set_var('input_minutes','<input name="n_cachetime" value="'  . $sitedata['cachetime'] . '" size="4">');
			$GLOBALS['egw']->template->set_var('input_listings','<input name="n_listings" value="'  . $sitedata['listings']  . '" size="2">');

			$news_type = array('rdf','fm','lt','sf','rdf-chan');
			while(list(,$item) = each($news_type))
			{
				$_select .= '<option value="' . $item . '"' . ($sitedata['newstype'] == $item ? ' selected':'')
				. '>' . $item . '</option>';
			}
			$GLOBALS['egw']->template->set_var('input_type','<select name="n_newstype">' . $_select . '</select>');

			$GLOBALS['egw']->template->set_var('action_url',$GLOBALS['egw']->link('/index.php','menuaction=headlines.uiheadlines.edit&con=' . $_GET['con']));

			$GLOBALS['egw']->template->parse('buttons','buttons');
			$GLOBALS['egw']->template->pfp('out','form');

			$GLOBALS['egw']->common->egw_footer();
		}

		function delete()
		{
			$con = (int)get_var('con',array('POST','GET'));

			if($_POST['no'])
			{
				$GLOBALS['egw']->redirect_link('/index.php','menuaction=headlines.uiheadlines.admin&cd=16');
			}

			if($con && $_POST['yes'])
			{
				$this->bo->delete($con);
				$GLOBALS['egw']->redirect_link('/index.php','menuaction=headlines.uiheadlines.admin&cd=16');
			}

			$GLOBALS['egw_info']['flags']['app_header'] = lang('Headlines Administration - Delete headline');
			$GLOBALS['egw']->common->egw_header();
			echo parse_navbar();

			// This is done for a reason (jengo)
			$GLOBALS['egw']->template->set_root($GLOBALS['egw']->common->get_tpl_dir('headlines'));

			$GLOBALS['egw']->template->set_file(array(
				'delete_form' => 'admin_delete.tpl'
			));

			$GLOBALS['egw']->template->set_var('lang_message',lang('Are you sure you want to delete this news site ?'));
			$GLOBALS['egw']->template->set_var('lang_no',lang('No'));
			$GLOBALS['egw']->template->set_var('lang_yes',lang('Yes'));

			$GLOBALS['egw']->template->set_var('action_url',$GLOBALS['egw']->link('/index.php','menuaction=headlines.uiheadlines.delete'));
			$GLOBALS['egw']->template->set_var('con',$con);

			$GLOBALS['egw']->template->pfp('out','delete_form');

			$GLOBALS['egw']->common->egw_footer();
		}

		function view()
		{
			$con = (int)get_var('con',array('POST','GET'));

			if(!$con || $_POST['cancel'])
			{
				$GLOBALS['egw']->redirect_link('/index.php','menuaction=headlines.uiheadlines.admin');
			}

			$GLOBALS['egw_info']['flags']['app_header'] = lang('Headlines Administration');
			$GLOBALS['egw']->common->egw_header();
			echo parse_navbar();

			$sitedata = $this->bo->read($con);

			// This is done for a reason (jengo)
			$GLOBALS['egw']->template->set_root($GLOBALS['egw']->common->get_tpl_dir('headlines'));

			$GLOBALS['egw']->template->set_file(array(
				'admin_form' => 'admin_form.tpl'
			));
			$GLOBALS['egw']->template->set_block('admin_form','form');
			$GLOBALS['egw']->template->set_block('admin_form','listing_row');
			$GLOBALS['egw']->template->set_block('admin_form','listing_rows');
			$GLOBALS['egw']->template->set_block('admin_form','cancel');

			$GLOBALS['egw']->template->set_var('lang_header',lang('View headline'));
			$GLOBALS['egw']->template->set_var('th_bg',$GLOBALS['egw_info']['theme']['th_bg']);
			$GLOBALS['egw']->template->set_var('row_on',$GLOBALS['egw_info']['theme']['row_on']);
			$GLOBALS['egw']->template->set_var('row_off',$GLOBALS['egw_info']['theme']['row_off']);
			$GLOBALS['egw']->template->set_var('lang_display',lang('Display'));
			$GLOBALS['egw']->template->set_var('lang_base_url',lang('Base URL'));
			$GLOBALS['egw']->template->set_var('lang_news_file',lang('News File'));
			$GLOBALS['egw']->template->set_var('lang_minutes',lang('Minutes between refresh'));
			$GLOBALS['egw']->template->set_var('lang_listings',lang('Listings Displayed'));
			$GLOBALS['egw']->template->set_var('lang_type',lang('News Type'));
			$GLOBALS['egw']->template->set_var('lang_cancel',lang('Cancel'));

			$GLOBALS['egw']->template->set_var('input_display',$sitedata['display']);
			$GLOBALS['egw']->template->set_var('input_base_url',$sitedata['base_url']);
			$GLOBALS['egw']->template->set_var('input_news_file',$sitedata['newsfile']);
			$GLOBALS['egw']->template->set_var('input_minutes',$sitedata['cachetime'].' ('.$GLOBALS['egw']->common->show_date($sitedata['lastread']).')');
			$GLOBALS['egw']->template->set_var('input_listings',$sitedata['listings']);
			$GLOBALS['egw']->template->set_var('input_type',$sitedata['newstype']);

			$sitecache = $this->bo->readcache($con);

			$GLOBALS['egw']->template->set_var('th_bg2',$GLOBALS['egw_info']['theme']['th_bg']);
			$GLOBALS['egw']->template->set_var('lang_current_cache',lang('Current headlines in cache'));

			if(count($sitecache) == 0)
			{
				$GLOBALS['egw']->nextmatchs->template_alternate_row_color($GLOBALS['egw']->template);
				$GLOBALS['egw']->template->set_var('value',lang('None'));
				$GLOBALS['egw']->template->parse('listing_rows','listing_row',True);
			}

			foreach($sitecache as $x => $cache)
			{
				$GLOBALS['egw']->nextmatchs->template_alternate_row_color($GLOBALS['egw']->template);
				$GLOBALS['egw']->template->set_var('value','<a href="' . $cache['link'] . '" target="_new">' . $cache['title'] . '</a>');
				$GLOBALS['egw']->template->parse('listing_rows','listing_row',True);
			}
			$GLOBALS['egw']->template->parse('cancel','cancel');

			$GLOBALS['egw']->template->pfp('out','form');
			$GLOBALS['egw']->common->egw_footer();
		}

		function preferences()
		{
			if($_POST['cancel'] || $_POST['save'])
			{
				if($_POST['save'])
				{
					if(is_array($GLOBALS['egw_info']['user']['preferences']['headlines']))
					{
						foreach($GLOBALS['egw_info']['user']['preferences']['headlines'] as $n => $name)
						{
							if($n != 'headlines_layout')
							{
								$GLOBALS['egw']->preferences->delete('headlines',$n);
							}
						}
					}

					if(is_array($_POST['headlines']))
					{
						foreach($_POST['headlines'] as $n)
						{
							$GLOBALS['egw']->preferences->add('headlines',$n,'True');
						}
					}

					//			$GLOBALS['egw']->preferences->add('headlines', 'mainscreen_showheadlines',True);
					$GLOBALS['egw']->preferences->save_repository(True);
				}
				$GLOBALS['egw']->redirect_link('/index.php','menuaction=headlines.uiheadlines.index');
			}

			$GLOBALS['egw_info']['flags']['app_header'] = lang('Headline preferences');
			$GLOBALS['egw']->common->egw_header();
			echo parse_navbar();

			$GLOBALS['egw']->template->set_file(array('form' => 'preferences.tpl'));

			$GLOBALS['egw']->template->set_var('form_action',$GLOBALS['egw']->link('/index.php','menuaction=headlines.uiheadlines.preferences'));
			$GLOBALS['egw']->template->set_var('th_bg',$GLOBALS['egw_info']['theme']['th_bg']);
			$GLOBALS['egw']->template->set_var('lang_header',lang('select headline news sites'));

			$GLOBALS['egw']->db->query('SELECT con,display FROM phpgw_headlines_sites ORDER BY display asc',__LINE__,__FILE__);
			while($GLOBALS['egw']->db->next_record())
			{
				$html_select .= '<option value="' . $GLOBALS['egw']->db->f('con') . '"';

				if($GLOBALS['egw_info']['user']['preferences']['headlines'][$GLOBALS['egw']->db->f('con')])
				{
					$html_select .= ' selected';
				}
				$html_select .= '>' . $GLOBALS['egw']->db->f('display') . '</option>'."\n";
			}
			$GLOBALS['egw']->template->set_var('select_options',$html_select);

			$GLOBALS['egw']->template->set_var('tr_color_1',$GLOBALS['egw']->nextmatchs->alternate_row_color());
			$GLOBALS['egw']->template->set_var('tr_color_2',$GLOBALS['egw']->nextmatchs->alternate_row_color());

			$GLOBALS['egw']->template->set_var('lang_save',lang('Save'));
			$GLOBALS['egw']->template->set_var('lang_cancel',lang('Cancel'));

			$GLOBALS['egw']->template->pparse('out','form');
			$GLOBALS['egw']->common->egw_footer();
		}

		function preferences_layout()
		{
			if($_POST['cancel'] || $_POST['save'] || $_POST['headlines_layout'])
			{
				if (!$_POST['cancel'])
				{
					$GLOBALS['egw']->preferences->add('headlines','headlines_layout',$_POST['headlines_layout']);
					$GLOBALS['egw']->preferences->add('headlines','mainscreen_showheadlines',$_POST['mainscreen'] ? 1 : '');
					$GLOBALS['egw']->preferences->save_repository();
				}
				$GLOBALS['egw']->redirect($GLOBALS['egw']->link('/headlines/index.php'));
			}
			$GLOBALS['egw_info']['flags']['app_header'] = lang('Headlines layout');
			$GLOBALS['egw']->common->egw_header();
			echo parse_navbar();

			$GLOBALS['egw']->template->set_file(array(
				'layout1' => 'basic_sample.tpl',
				'layout2' => 'color_sample.tpl',
				'layout3' => 'gray_sample.tpl',
				'body'    => 'preferences_layout.tpl'
			));

			$GLOBALS['egw']->template->set_var('th_bg',$GLOBALS['egw_info']['theme']['th_bg']);
			$GLOBALS['egw']->template->set_var('action_url',$GLOBALS['egw']->link('/headlines/preferences_layout.php'));
			$GLOBALS['egw']->template->set_var('save_label',lang('Save'));
			$GLOBALS['egw']->template->set_var('cancel_label',lang('Cancel'));

			$GLOBALS['egw']->template->set_var('template_label',lang('Choose layout'));

			if ($_POST['save'])
			{
				$selected[$_POST['headlines_layout']] = ' selected';
			}
			else
			{
				$selected[$GLOBALS['egw_info']['user']['preferences']['headlines']['headlines_layout']] = ' selected';
				if($GLOBALS['egw_info']['user']['preferences']['headlines']['mainscreen_showheadlines'])
				{
					$GLOBALS['egw']->template->set_var('mainscreen_checked',' checked');
				}
			}

			$s  = '<option value="basic"' . $selected['basic'] . '>' . lang('Basic') . '</option>';
			$s .= '<option value="color"' . $selected['color'] . '>' . lang('Color') . '</option>';
			$s .= '<option value="gray"'  . $selected['gray'] . '>' . lang('Gray') . '</option>';
			$GLOBALS['egw']->template->set_var('template_options',$s);

			$GLOBALS['egw']->template->set_var('lang_mainscreen', lang('show headlines on homepage'));
			$GLOBALS['egw']->template->set_var('sample',lang('Sample'));
			$GLOBALS['egw']->template->set_var('basic',lang('Basic'));
			$GLOBALS['egw']->template->parse('layout_1','layout1');
			$GLOBALS['egw']->template->set_var('color',lang('Color'));
			$GLOBALS['egw']->template->parse('layout_2','layout2');
			$GLOBALS['egw']->template->set_var('gray',lang('Gray'));
			$GLOBALS['egw']->template->parse('layout_3','layout3');

			$GLOBALS['egw']->template->pfp('out','body');
			$GLOBALS['egw']->common->egw_footer();
		}

		function admin()
		{
			$GLOBALS['egw_info']['flags']['app_header'] = lang('Headline Sites');
			$GLOBALS['egw']->common->egw_header();
			echo parse_navbar();

			// This is done for a reason (jengo)
			$GLOBALS['egw']->template->set_root($GLOBALS['egw']->common->get_tpl_dir('headlines'));
			$GLOBALS['egw']->template->set_file(array(
				'admin' => 'admin.tpl'
			));
			$GLOBALS['egw']->template->set_block('admin','list');
			$GLOBALS['egw']->template->set_block('admin','row');
			$GLOBALS['egw']->template->set_block('admin','row_empty');

			$GLOBALS['egw']->template->set_var('th_bg',$GLOBALS['egw_info']['theme']['th_bg']);
			$GLOBALS['egw']->template->set_var('lang_site',lang('Site'));
			$GLOBALS['egw']->template->set_var('lang_edit',lang('Edit'));
			$GLOBALS['egw']->template->set_var('lang_delete',lang('Delete'));
			$GLOBALS['egw']->template->set_var('lang_view',lang('View'));
			$GLOBALS['egw']->template->set_var('lang_add',lang('Add'));

			$GLOBALS['egw']->db->query('SELECT COUNT(*) FROM phpgw_headlines_sites',__LINE__,__FILE__);
			$GLOBALS['egw']->db->next_record();

			if(!$GLOBALS['egw']->db->f(0))
			{
				$GLOBALS['egw']->template->set_var('lang_row_empty',lang('No headlines found'));
				$GLOBALS['egw']->nextmatchs->template_alternate_row_color($GLOBALS['egw']->template);
				$GLOBALS['egw']->template->parse('rows','row_empty');
			}

			$GLOBALS['egw']->db->query('SELECT con,display FROM phpgw_headlines_sites ORDER BY display',__LINE__,__FILE__);
			while($GLOBALS['egw']->db->next_record())
			{
				$GLOBALS['egw']->nextmatchs->template_alternate_row_color($GLOBALS['egw']->template);

				$GLOBALS['egw']->template->set_var('row_display',$GLOBALS['egw']->db->f('display'));
				$GLOBALS['egw']->template->set_var('row_edit',$GLOBALS['egw']->link('/index.php','menuaction=headlines.uiheadlines.edit&con='.$GLOBALS['egw']->db->f('con')));
				$GLOBALS['egw']->template->set_var('row_delete',$GLOBALS['egw']->link('/index.php','menuaction=headlines.uiheadlines.delete&con='.$GLOBALS['egw']->db->f('con')));
				$GLOBALS['egw']->template->set_var('row_view',$GLOBALS['egw']->link('/index.php','menuaction=headlines.uiheadlines.view&con='.$GLOBALS['egw']->db->f('con')));

				$GLOBALS['egw']->template->parse('rows','row',True);
			}

			$GLOBALS['egw']->template->set_var('add_url',$GLOBALS['egw']->link('/index.php','menuaction=headlines.uiheadlines.add'));
			$GLOBALS['egw']->template->set_var('grab_more_url',$GLOBALS['egw']->link('/index.php','menuaction=headlines.uiheadlines.grabnewssites'));
			$GLOBALS['egw']->template->set_var('lang_grab_more',lang('Grab New News Sites'));

			$GLOBALS['egw']->template->pfp('out','list');

			$GLOBALS['egw']->common->egw_footer();
		}

		function grabnewssites()
		{
			$this->bo->getList();
			$GLOBALS['egw']->redirect_link('/index.php','menuaction=headlines.uiheadlines.admin');
		}
	}
?>
