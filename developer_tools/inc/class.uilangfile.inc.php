<?php
  /**************************************************************************\
  * phpGroupWare - Translation Editor                                        *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

	class uilangfile
	{
		var $helpme;
		var $public_functions = array(
			'index'    => True,
			'add'      => True,
			'edit'     => True,
			'create'   => True,
			'save'     => True,
			'addphrase'=> True,
			'download' => True
		);
		var $bo;

		function uilangfile()
		{
			global $phpgw;
			$this->template = $phpgw->template;
			$this->bo = CreateObject('developer_tools.bolangfile');
			$this->nextmatchs = CreateObject('phpgwapi.nextmatchs');
		}

		function add()
		{
			global $phpgw,$appname;
		}

		function addphrase()
		{
			global $phpgw,$app_name,$sourcelang,$targetlang,$entry,$submit;

			$this->bo->read_sessiondata();
			if($submit)
			{
				$this->bo->addphrase($entry);
				if ($sourcelang == $targetlang)
				{
					$this->bo->target_langarray = $this->bo->source_langarray;
				}
				$this->bo->save_sessiondata($this->bo->source_langarray,$this->bo->target_langarray);

				Header('Location: ' . $phpgw->link('/index.php','menuaction=developer_tools.uilangfile.edit&app_name=' . $app_name
					. '&sourcelang=' . $sourcelang . '&targetlang=' . $targetlang));
			}
			else
			{
				$phpgw->common->phpgw_header();
				echo parse_navbar();

				$this->template->set_file(array('form' => 'addphrase.tpl'));
				$this->template->set_var('message_id_field','<input size ="40" name="entry[message_id]">');
				$this->template->set_var('translation_field','<input size ="40" name="entry[content]">');
				$this->template->set_var('app_name','<input type="hidden" name="entry[app_name]" value="'.$app_name.'">');

				$this->template->set_var('lang_message',lang('Add new phrase'));
				$this->template->set_var('form_action',$phpgw->link('/index.php','menuaction=developer_tools.uilangfile.addphrase&app_name='.$app_name
					. '&sourcelang=' . $sourcelang . '&targetlang=' . $targetlang
				));

				$this->template->set_var('lang_message_id',lang('message_id in English'));
				$this->template->set_var('lang_translation',lang('Phrase in English'));
				$this->template->set_var('lang_button',lang('add'));

				$this->template->pfp('out','form');
				$phpgw->common->phpgw_footer();
			}
		}

		function edit()
		{
			global $phpgw,$phpgw_info,$app_name,$newlang,$sourcelang,$targetlang,$dlsource,$writesource,$dltarget,$writetarget,$add_phrase,$update,$revert;

			if($add_phrase)
			{
				Header('Location: ' . $phpgw->link('/index.php','menuaction=developer_tools.uilangfile.addphrase&app_name='.$app_name
					. '&sourcelang=' . $sourcelang . '&targetlang=' . $targetlang));
			}
			elseif ($revert)
			{
				$this->bo->clear_sessiondata();
			}
			$this->bo->read_sessiondata();

			if($dlsource)
			{
				$this->download('source',$sourcelang);
			}
			if($dltarget)
			{
				$this->download('target',$targetlang);
			}

			if($writesource)
			{
				$this->save('source',$sourcelang);
			}
			if($writetarget)
			{
				$this->save('target',$targetlang);
			}

			$phpgw->common->phpgw_header();
			echo parse_navbar();
			include(PHPGW_APP_INC . '/header.inc.php');

			$this->template->set_file(array('langfile' => 'langfile.tpl'));
			$this->template->set_block('langfile','header','header');
			$this->template->set_block('langfile','postheader','postheader');
			$this->template->set_block('langfile','detail','detail');
			$this->template->set_block('langfile','prefooter','prefooter');
			$this->template->set_block('langfile','srcwrite','srcwrite');
			$this->template->set_block('langfile','tgtwrite','tgtwrite');
			$this->template->set_block('langfile','srcdownload','srcdownload');
			$this->template->set_block('langfile','tgtdownload','tgtdownload');
			$this->template->set_block('langfile','footer','footer');

			$this->template->set_var('action_url',$phpgw->link('/index.php','menuaction=developer_tools.uilangfile.edit'));
			$this->template->set_var('revert_url',$phpgw->link('/index.php','menuaction=developer_tools.uilangfile.edit'));
			$this->template->set_var('cancel_link',$phpgw->link('/index.php','menuaction=developer_tools.uilangfile.index'));
			$this->template->set_var('lang_remove',lang('Remove'));
			$this->template->set_var('lang_application',lang('Application'));
			$this->template->set_var('lang_source',lang('Source Language'));
			$this->template->set_var('lang_target',lang('Target Language'));
			$this->template->set_var('lang_submit',lang('Submit'));
			$this->template->set_var('lang_update',lang('Update'));
			$this->template->set_var('lang_revert',lang('Revert'));
			$this->template->set_var('lang_cancel',lang('Cancel'));

			$languages = $this->bo->list_langs();

			if(!$sourcelang)
			{
				$sourcelang = 'en';
			}
			if(!$targetlang)
			{
				$targetlang = 'en';
			}

			while (list($x,$lang) = @each($languages))
			{
				$sourcelangs .= '      <option value="' . $lang['lang_id'] . '"';
				if ($sourcelang)
				{
					if ($lang['lang_id'] == $sourcelang)
					{
						$sourcelangs .= ' selected';
					}
				}
				elseif ($lang['lang_id'] == 'EN')
				{
					$sourcelangs .= ' selected';
				}
				$sourcelangs .= '>' . $lang['lang_name'] . '</option>' . "\n";
			}
			@reset($languages);

			while (list($x,$lang) = @each($languages))
			{
				$targetlangs .= '      <option value="' . $lang['lang_id'] . '"';
				if ($targetlang)
				{
					if ($lang['lang_id'] == $targetlang)
					{
						$targetlangs .= ' selected';
					}
				}
				elseif ($lang['lang_id'] == 'EN')
				{
					$targetlangs .= ' selected';
				}
				$targetlangs .= '>' . $lang['lang_name'] . '</option>' . "\n";
			}
			$this->template->set_var('sourcelangs',$sourcelangs);
			$this->template->set_var('targetlangs',$targetlangs);
			$this->template->set_var('app_name',$app_name);
			$this->template->pfp('out','header');

			$db_perms = $phpgw->acl->get_user_applications($phpgw_info["user"]["account_id"]);
			@ksort($db_perms);
			@reset($db_perms);
			while (list($userapp) = each($db_perms))
			{
				if ($phpgw_info['apps'][$userapp]['enabled'])
				{
					$userapps .= '<option value="' . $userapp . '"';
					if ($application_name == $userapp)
					{
						$userapps .= ' selected';
					}
					elseif ($phpgw_info['user']['preferences']['default_app'] == $userapp)
					{
						$userapps .= ' selected';
					}
					$userapps .= '>' . lang($phpgw_info['apps'][$userapp]['title']) . '</option>' . "\n";
				}
			}
			$this->template->set_var('userapps',$userapps);

			if ($update)
			{
				$transapp     = $GLOBALS['transapp'];
				$translations = $GLOBALS['translations'];
				$deleteme     = $GLOBALS['delete'];
				while (list($_mess,$_app) = each($transapp))
				{
					if($_mess)
					{
						$this->bo->source_langarray[$_mess]['app_name'] = $_app;
						$this->bo->target_langarray[$_mess]['app_name'] = $_app;
					}
				}
				while (list($_mess,$_cont) = each($translations))
				{
					if($_mess && $_cont)
					{
						$this->bo->target_langarray[$_mess]['message_id'] = $_mess;
						$this->bo->target_langarray[$_mess]['content'] = $_cont;
						if($sourcelang == $targetlang)
						{
							$this->bo->source_langarray[$_mess]['content'] = $_cont;
						}
					}
				}
				while (list($_mess,$_checked) = @each($deleteme))
				{
					if($_checked == 'on')
					{
						unset($this->bo->source_langarray[$_mess]);
						unset($this->bo->target_langarray[$_mess]);
					}
				}
				@ksort($this->bo->source_langarray);
				@ksort($this->bo->target_langarray);
				/* $this->bo->save_sessiondata($this->bo->source_langarray,$this->bo->target_langarray); */
				unset($transapp);
				unset($translations);
				if($deleteme)
				{
					$this->bo->save_sessiondata($this->bo->source_langarray,$this->bo->target_langarray);
				}
				unset($deleteme);
			}

			if($sourcelang && $targetlang)
			{
				$this->template->set_var('lang_appname',lang('Application'));
				$this->template->set_var('lang_message',lang('Message'));
				$this->template->set_var('lang_original',lang('Original'));
				$this->template->set_var('lang_translation',lang('Translation'));
				$this->template->set_var('lang_addphrase',lang('Add Phrase'));
				$this->template->set_var('th_bg',$phpgw_info['theme']['th_bg']);
				$this->template->set_var('phrase_link',$phpgw->link('/index.php','menuaction=developer_tools.uilangfile.addphrase&app_name='.$app_name
					. '&sourcelang=' . $sourcelang . '&targetlang=' . $targetlang
				));
				$this->template->pfp('out','postheader');

				$langarray = $this->bo->add_app($app_name,$sourcelang);
				$translation = $this->bo->load_app($app_name,$targetlang);
				$this->template->set_var('src_file',$this->bo->src_file);

				while(list($key,$data) = @each($langarray))
				{
					$mess_id  = $data['message_id'];
					$content  = $data['content'];
					$transapp = $data['app_name'];
					$transy   = $translation[$mess_id]['content'];
					$this->template->set_var('mess_id',$phpgw->strip_html($mess_id));
					$this->template->set_var('source_content',$phpgw->strip_html($content));
					$this->template->set_var('content',$phpgw->strip_html($transy));
					$this->template->set_var('transapp',$this->lang_option($app_name,$transapp,$mess_id));
					$this->template->set_var('tr_color',$this->nextmatchs->alternate_row_color());
					$this->template->pfp('out','detail');
				}
				$this->template->set_var('sourcelang',$sourcelang);
				$this->template->set_var('targetlang',$targetlang);
				$this->template->set_var('app_name',$app_name);
				$this->template->set_var('lang_write',lang('Write'));
				$this->template->set_var('lang_download',lang('Download'));

				$this->template->pfp('out','prefooter');
				$this->template->pfp('out','srcdownload');

				if($this->bo->loaded_apps[$sourcelang]['writeable'])
				{
					$this->template->pfp('out','srcwrite');
				}

				$this->template->set_var('tgt_file',$this->bo->tgt_file);
				$this->template->set_var('targetlang',$targetlang);
				$this->template->pfp('out','tgtdownload');
				if($this->bo->loaded_apps[$targetlang]['writeable'])
				{
					$this->template->pfp('out','tgtwrite');
				}

				$this->template->pfp('out','footer');
			}
			/* _debug_array($this->bo->loaded_apps); */
			$this->bo->save_sessiondata($this->bo->source_langarray,$this->bo->target_langarray);
			$phpgw->common->phpgw_footer();
		}

		function create()
		{
			global $phpgw,$appname;
		}

		function save($which,$lang)
		{
			global $phpgw, $app_name;

			$this->bo->write_file($which,$app_name,$lang);
			Header('Location: ' . $phpgw->link('/index.php','menuaction=developer_tools.uilangfile.edit&app_name='.$app_name
				. '&sourcelang=' . $sourcelang . '&targetlang=' . $targetlang));
		}

		function download($which,$lang)
		{
			global $phpgw;

			switch ($which)
			{
				case 'source':
					$langarray = $this->bo->source_langarray;
					break;
				case 'target':
					$langarray = $this->bo->target_langarray;
					break;
				default:
					break;
			}
			$browser = CreateObject('phpgwapi.browser');
			$browser->content_header('phpgw_' . $lang . '.lang');
			while(list($mess_id,$data) = @each($langarray))
			{
				echo $mess_id . "\t" . $data['app_name'] . "\t" . $lang . "\t" . $data['content'] . "\n";
			}
			$phpgw->common->phpgw_exit();
		}

		function index()
		{
			global $phpgw,$phpgw_info,$start,$sort,$order,$query;

			$this->bo->save_sessiondata('','');
			$phpgw->common->phpgw_header();
			echo parse_navbar();
			include(PHPGW_APP_INC . '/header.inc.php');

			$this->template->set_file(array('applications' => 'applications.tpl'));
			$this->template->set_block('applications','list','list');
			$this->template->set_block('applications','row','row');

			$offset = $phpgw_info['user']['preferences']['common']['maxmatchs'];

			$apps = $this->bo->list_apps();
			$total = $this->bo->total;

			if(!$sort)
			{
				$sort = 'ASC';
			}

			if($sort == 'ASC')
			{
				ksort($apps);
			}
			else
			{
				krsort($apps);
			}

			if ($start && $offset)
			{
				$limit = $start + $offset;
			}
			elseif ($start && !$offset)
			{
				$limit = $start;
			}
			elseif(!$start && !$offset)
			{
				$limit = $total;
			}
			else
			{
				$start = 0;
				$limit = $offset;
			}

			if ($limit > $total)
			{
				$limit = $total;
			}

			$i = 0;
			$applications = array();
			while(list($app,$data) = @each($apps))
			{
				if($i >= $start && $i<= $limit)
				{
					$applications[$app] = $data;
				}
				$i++;
			}

			$this->template->set_var('lang_installed',lang('Installed applications'));
			$this->template->set_var('bg_color',$phpgw_info['theme']['bg_color']);
			$this->template->set_var('th_bg',$phpgw_info['theme']['th_bg']);

			$this->template->set_var('sort_title',$this->nextmatchs->show_sort_order($sort,'title','title','/index.php',lang('Title'),'&menuaction=developer_tools.uilangfile.index'));
			$this->template->set_var('lang_showing',$this->nextmatchs->show_hits($total,$start));
			$this->template->set_var('left',$this->nextmatchs->left('/index.php',$start,$total,'&menuaction=developer_tools.uilangfile.index'));
			$this->template->set_var('right',$this->nextmatchs->right('/index.php',$start,$total,'&menuaction=developer_tools.uilangfile.index'));

			$this->template->set_var('lang_edit',lang('Edit'));
			$this->template->set_var('lang_translate',lang('Translate'));
			$this->template->set_var('new_action',$phpgw->link('/index.php','menuaction=developer_tools.uilangfile.create'));
			$this->template->set_var('create_new',lang('Create New Language File'));

			@reset($applications);
			while (list($key,$app) = @each($applications))
			{
				$tr_color = $this->nextmatchs->alternate_row_color($tr_color);

				if($app['name'])
				{
					$name = $app['name'];
				}
				else
				{
					$name = '&nbsp;';
				}

				$this->template->set_var('tr_color',$tr_color);
				$this->template->set_var('name',$name);

				$this->template->set_var('edit','<a href="' . $phpgw->link('/index.php','menuaction=developer_tools.uilangfile.edit&app_name=' . urlencode($app['name'])) . '"> ' . lang('Edit') . ' </a>');
				$this->template->set_var('translate','<a href="' . $phpgw->link('/index.php','menuaction=developer_tools.uilangfile.translate&app_name=' . urlencode($app['name'])) . '"> ' . lang('Translate') . ' </a>');

				$this->template->set_var('status',$status);

				$this->template->parse('rows','row',True);
			}

			$this->template->pparse('out','list');
		}

		function lang_option($app_name,$current,$name)
		{
			$list = array(
				$app_name     => $app_name,
				'common'      => 'common',
				'login'       => 'login',
				'admin'       => 'admin',
				'preferences' => 'preferences'
			);

			$select  = "\n" .'<select name="transapp[' . $name . ']">' . "\n";
			while (list($key,$val) = each($list))
			{
				$select .= '<option value="' . $key . '"';
				if ($key == $current && $current != '')
				{
					$select .= ' selected';
				}
				$select .= '>' . $val . '</option>'."\n";
			}

			$select .= '</select>'."\n";

			return $select;
		}

	}
?>
