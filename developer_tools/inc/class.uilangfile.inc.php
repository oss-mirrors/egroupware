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
			'index'     => True,
			'edit'      => True,
			'create'    => True,
			'save'      => True,
			'load'      => True,
			'addphrase' => True,
			'missingphrase'=> True,
			'download'  => True
		);
		var $bo;
		var $template;
		var $nextmatchs;

		function uilangfile()
		{
			$this->template = $GLOBALS['phpgw']->template;
			$this->bo = CreateObject('developer_tools.bolangfile');
			$this->nextmatchs = CreateObject('phpgwapi.nextmatchs');
			$GLOBALS['phpgw']->translation->add_app('developer_tools');
			$GLOBALS['phpgw']->translation->add_app('common');
			$GLOBALS['phpgw']->translation->add_app('transy');
		}

		function load()
		{
			$app_name   = $GLOBALS['HTTP_POST_VARS']['app_name'];
			$sourcelang = $GLOBALS['HTTP_POST_VARS']['sourcelang'];
			$targetlang = $GLOBALS['HTTP_POST_VARS']['targetlang'];

			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();

			echo '<br>' . lang('Loading source langfile') . ': ' . $sourcelang . '... ';
			echo $this->bo->loaddb($app_name,$sourcelang);
			echo '<br>' . lang('Loading target langfile') . ': ' . $targetlang . '... ';
			echo $this->bo->loaddb($app_name,$targetlang);

			echo '<br><a href="' . $GLOBALS['phpgw']->link('/index.php','menuaction=developer_tools.uilangfile.edit&app_name=' . $app_name
				. '&sourcelang=' . $sourcelang . '&targetlang=' . $targetlang) . '">' . lang('ok') . '</a>';
		}

		function addphrase()
		{
			$app_name   = $GLOBALS['HTTP_POST_VARS']['app_name'];
			$sourcelang = $GLOBALS['HTTP_POST_VARS']['sourcelang'];
			$targetlang = $GLOBALS['HTTP_POST_VARS']['targetlang'];
			$entry      = $GLOBALS['HTTP_POST_VARS']['entry'];
			$submit     = $GLOBALS['HTTP_POST_VARS']['submit'];

			$this->bo->read_sessiondata();
			if($submit)
			{
				$this->bo->addphrase($entry);
				if ($sourcelang == $targetlang)
				{
					$this->bo->target_langarray = $this->bo->source_langarray;
				}
				$this->bo->save_sessiondata($this->bo->source_langarray,$this->bo->target_langarray);

				Header('Location: ' . $GLOBALS['phpgw']->link('/index.php','menuaction=developer_tools.uilangfile.edit&app_name=' . $app_name
					. '&sourcelang=' . $sourcelang . '&targetlang=' . $targetlang));
			}
			else
			{
				$GLOBALS['phpgw']->common->phpgw_header();
				echo parse_navbar();

				$this->template->set_file(array('form' => 'addphrase.tpl'));
				$this->template->set_var('message_id_field','<input size ="40" name="entry[message_id]">');
				$this->template->set_var('translation_field','<input size ="40" name="entry[content]">');
				$this->template->set_var('app_name','<input type="hidden" name="entry[app_name]" value="'.$app_name.'">');

				$this->template->set_var('lang_message',lang('Add new phrase'));
				$this->template->set_var('form_action',$GLOBALS['phpgw']->link('/index.php','menuaction=developer_tools.uilangfile.addphrase'));
				$this->template->set_var('sourcelang',$sourcelang);
				$this->template->set_var('targetlang',$targetlang);
				$this->template->set_var('app_name',$app_name);

				$this->template->set_var('lang_message_id',lang('message_id in English'));
				$this->template->set_var('lang_translation',lang('Phrase in English'));
				$this->template->set_var('lang_button',lang('add'));

				$this->template->pfp('out','form');
				$GLOBALS['phpgw']->common->phpgw_footer();
			}
		}

		function missingphrase()
		{
			$app_name    = $GLOBALS['HTTP_POST_VARS']['app_name'] ? $GLOBALS['HTTP_POST_VARS']['app_name'] : $GLOBALS['HTTP_GET_VARS']['app_name'];
			$newlang     = $GLOBALS['HTTP_POST_VARS']['newlang'];
			$sourcelang  = $GLOBALS['HTTP_POST_VARS']['sourcelang'];
			$targetlang  = $GLOBALS['HTTP_POST_VARS']['targetlang'];
			$dlsource    = $GLOBALS['HTTP_POST_VARS']['dlsource'];
			$writesource = $GLOBALS['HTTP_POST_VARS']['writesource'];
			$dltarget    = $GLOBALS['HTTP_POST_VARS']['dltarget'];
			$writetarget = $GLOBALS['HTTP_POST_VARS']['writetarget'];
			$update      = $GLOBALS['HTTP_POST_VARS']['update'];
			$entry       = $GLOBALS['HTTP_POST_VARS']['entry'];
			$submit      = $GLOBALS['HTTP_POST_VARS']['submit'];
			$this->bo->read_sessiondata();

			$this->template->set_file(array('langfile' => 'langmissing.tpl'));
			$this->template->set_block('langfile','header','header');
			$this->template->set_block('langfile','postheader','postheader');
			$this->template->set_block('langfile','detail','detail');
			$this->template->set_block('langfile','prefooter','prefooter');
			$this->template->set_block('langfile','srcwrite','srcwrite');
			$this->template->set_block('langfile','tgtwrite','tgtwrite');
			$this->template->set_block('langfile','srcdownload','srcdownload');
			$this->template->set_block('langfile','tgtdownload','tgtdownload');
			$this->template->set_block('langfile','footer','footer');
			if(!$sourcelang)
			{
				$sourcelang = 'en';
			}
			if(!$targetlang)
			{
				$targetlang = 'en';
			}
			$missingarray = $this->bo->missing_app($app_name,$sourcelang);
			if ($update)
			{
				$deleteme     = $GLOBALS['HTTP_POST_VARS']['delete'];
				while (list($_mess,$_checked) = @each($deleteme))
				{
					if($_checked == 'on')
					{
						$this->bo->movephrase($_mess);
						/* _debug_array($missingarray[$_mess]); */
						unset($missingarray[strtolower($_mess)]);
						/* _debug_array($missingarray[$_mess]); */
					}
				}
				unset($deleteme);
				/*
				if ($deleteme!='')
				{
					echo 'tEST';
					Header('Location: ' .$GLOBALS['phpgw']->link('/index.php','menuaction=developer_tools.uilangfile.edit&app_name=' . $app_name)),TRUE);
					exit;
				}*/
			}
			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();
			include(PHPGW_APP_INC . '/header.inc.php');

			$this->template->set_var('lang_remove',lang('add phrase'));
			$this->template->set_var('lang_application',lang('Application'));
			// $this->template->set_var('lang_source',lang('Source Language'));
			// $this->template->set_var('lang_target',lang('Target Language'));
			// $this->template->set_var('lang_submit',lang('Submit'));
			$this->template->set_var('lang_update',lang('Update'));
			$this->template->set_var('lang_write',lang('Write'));
			// $this->template->set_var('lang_cancel',lang('Cancel'));
			$this->template->set_var('lang_view',lang('Back'));
			$languages = $this->bo->list_langs();
			while (list($x,$_lang) = @each($languages))
			{
				$sourcelangs .= '      <option value="' . $_lang['lang_id'] . '"';
				if ($sourcelang)
				{
					if ($_lang['lang_id'] == $sourcelang)
					{
						$sourcelangs .= ' selected';
					}
				}
				elseif ($_lang['lang_id'] == 'EN')
				{
					$sourcelangs .= ' selected';
				}
				$sourcelangs .= '>' . $_lang['lang_name'] . '</option>' . "\n";
			}
			@reset($languages);
			while (list($x,$_lang) = @each($languages))
			{
				$targetlangs .= '      <option value="' . $_lang['lang_id'] . '"';
				if ($targetlang)
				{
					if ($_lang['lang_id'] == $targetlang)
					{
						$targetlangs .= ' selected';
					}
				}
				elseif ($_lang['lang_id'] == 'EN')
				{
					$targetlangs .= ' selected';
				}
				$targetlangs .= '>' . $_lang['lang_name'] . '</option>' . "\n";
			}
			$this->template->set_var('action_url',$GLOBALS['phpgw']->link('/index.php','menuaction=developer_tools.uilangfile.missingphrase'));
			$this->template->set_var('sourcelangs',$sourcelangs);
			$this->template->set_var('targetlangs',$targetlangs);
			$this->template->set_var('app_name',$app_name);
			$this->template->pfp('out','header');
			if($sourcelang && $targetlang)
			{
				$this->template->set_var('lang_appname',lang('Application'));
				$this->template->set_var('lang_message',lang('Message'));
				$this->template->set_var('lang_original',lang('Original'));
				//$this->template->set_var('lang_translation',lang('Translation'));
				$this->template->set_var('th_bg',$GLOBALS['phpgw_info']['theme']['th_bg']);
				$this->template->set_var('view_link',
					$GLOBALS['phpgw']->link(
						'/index.php',
						'menuaction=developer_tools.uilangfile.edit&app_name='.$app_name.'&sourcelang=' . $sourcelang . '&targetlang=' . $targetlang
					)
				);
				$this->template->pfp('out','postheader');
				$translation = $this->bo->load_app($app_name,$targetlang);
				// $this->template->set_var('src_file',$this->bo->src_file);
				while(list($key,$data) = @each($missingarray))
				{
					$mess_id  = $data['message_id'];
					$content  = $data['content'];
					$transapp = $data['app_name'];
					// $transy   = $content;
					$this->template->set_var('mess_id',$GLOBALS['phpgw']->strip_html($mess_id));
					$this->template->set_var('source_content',$GLOBALS['phpgw']->strip_html($content));
					// $this->template->set_var('content',$GLOBALS['phpgw']->strip_html($transy));
					$this->template->set_var('transapp',$this->lang_option($app_name,$transapp,$mess_id));
					$this->template->set_var('tr_color',$this->nextmatchs->alternate_row_color());
					$this->template->pfp('out','detail');
				}
				// $this->template->set_var('sourcelang',$sourcelang);
				// $this->template->set_var('targetlang',$targetlang);
				// $this->template->set_var('app_name',$app_name);
				$this->template->pfp('out','prefooter');
				// $this->template->pfp('out','srcdownload');
				if($this->bo->loaded_apps[$sourcelang]['writeable'])
				{
					$this->template->pfp('out','srcwrite');
				}

				// $this->template->set_var('tgt_file',$this->bo->tgt_file);
				// $this->template->set_var('targetlang',$targetlang);
				// $this->template->pfp('out','tgtdownload');
				// if($this->bo->loaded_apps[$targetlang]['writeable'])
				// {
				//     $this->template->pfp('out','tgtwrite');
				// }
				$this->template->pfp('out','footer');
			}
			/* _debug_array($this->bo->loaded_apps); */
			$this->bo->save_sessiondata($this->bo->source_langarray,$this->bo->target_langarray);
		}

		function edit()
		{
			$app_name    = $GLOBALS['HTTP_POST_VARS']['app_name'] ? $GLOBALS['HTTP_POST_VARS']['app_name'] : $GLOBALS['HTTP_GET_VARS']['app_name'];
			$newlang     = $GLOBALS['HTTP_POST_VARS']['newlang'];
			$sourcelang  = $GLOBALS['HTTP_POST_VARS']['sourcelang'] ? $GLOBALS['HTTP_POST_VARS']['sourcelang'] : $GLOBALS['HTTP_GET_VARS']['sourcelang'];
			$targetlang  = $GLOBALS['HTTP_POST_VARS']['targetlang'] ? $GLOBALS['HTTP_POST_VARS']['targetlang'] : $GLOBALS['HTTP_GET_VARS']['targetlang'];
			$dlsource    = $GLOBALS['HTTP_POST_VARS']['dlsource'];
			$writesource = $GLOBALS['HTTP_POST_VARS']['writesource'];
			$dltarget    = $GLOBALS['HTTP_POST_VARS']['dltarget'];
			$writetarget = $GLOBALS['HTTP_POST_VARS']['writetarget'];
			$add_phrase  = $GLOBALS['HTTP_POST_VARS']['add_phrase'];
			$update      = $GLOBALS['HTTP_POST_VARS']['update'];
			$revert      = $GLOBALS['HTTP_POST_VARS']['revert'];
			$entry       = $GLOBALS['HTTP_POST_VARS']['entry'];
			$submit      = $GLOBALS['HTTP_POST_VARS']['submit'];

			if($add_phrase)
			{
				Header('Location: ' . $GLOBALS['phpgw']->link('/index.php','menuaction=developer_tools.uilangfile.addphrase&app_name='.$app_name
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

			$GLOBALS['phpgw']->common->phpgw_header();
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

			$this->template->set_var('action_url',$GLOBALS['phpgw']->link('/index.php','menuaction=developer_tools.uilangfile.edit'));
			$this->template->set_var('revert_url',$GLOBALS['phpgw']->link('/index.php','menuaction=developer_tools.uilangfile.edit'));
			$this->template->set_var('cancel_link',$GLOBALS['phpgw']->link('/index.php','menuaction=developer_tools.uilangfile.index'));
			$this->template->set_var('loaddb_url',$GLOBALS['phpgw']->link('/index.php','menuaction=developer_tools.uilangfile.load'));
			$this->template->set_var('lang_remove',lang('Remove'));
			$this->template->set_var('lang_loaddb',lang('Write to lang table'));
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

			while (list($x,$_lang) = @each($languages))
			{
				$sourcelangs .= '      <option value="' . $_lang['lang_id'] . '"';
				if ($sourcelang)
				{
					if ($_lang['lang_id'] == $sourcelang)
					{
						$sourcelangs .= ' selected';
					}
				}
				elseif ($_lang['lang_id'] == 'EN')
				{
					$sourcelangs .= ' selected';
				}
				$sourcelangs .= '>' . $_lang['lang_name'] . '</option>' . "\n";
			}
			@reset($languages);

			while (list($x,$_lang) = @each($languages))
			{
				$targetlangs .= '      <option value="' . $_lang['lang_id'] . '"';
				if ($targetlang)
				{
					if ($_lang['lang_id'] == $targetlang)
					{
						$targetlangs .= ' selected';
					}
				}
				elseif ($_lang['lang_id'] == 'EN')
				{
					$targetlangs .= ' selected';
				}
				$targetlangs .= '>' . $_lang['lang_name'] . '</option>' . "\n";
			}
			$this->template->set_var('sourcelangs',$sourcelangs);
			$this->template->set_var('targetlangs',$targetlangs);
			$this->template->set_var('app_name',$app_name);
			$this->template->pfp('out','header');

			$db_perms = $GLOBALS['phpgw']->acl->get_user_applications($GLOBALS['phpgw_info']['user']['account_id']);
			@ksort($db_perms);
			@reset($db_perms);
			while (list($userapp) = each($db_perms))
			{
				if ($GLOBALS['phpgw_info']['apps'][$userapp]['enabled'])
				{
					$userapps .= '<option value="' . $userapp . '"';
					if ($application_name == $userapp)
					{
						$userapps .= ' selected';
					}
					elseif ($GLOBALS['phpgw_info']['user']['preferences']['default_app'] == $userapp)
					{
						$userapps .= ' selected';
					}
					$userapps .= '>' . lang($GLOBALS['phpgw_info']['apps'][$userapp]['title']) . '</option>' . "\n";
				}
			}
			$this->template->set_var('userapps',$userapps);

			if ($update)
			{
				$transapp     = $GLOBALS['HTTP_POST_VARS']['transapp'];
				$translations = $GLOBALS['HTTP_POST_VARS']['translations'];
				$deleteme     = $GLOBALS['HTTP_POST_VARS']['delete'];
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
				$this->template->set_var('lang_missingphrase',lang('Search for missing phrase'));
				$this->template->set_var('lang_addphrase',lang('Add Phrase'));
				$this->template->set_var('th_bg',$GLOBALS['phpgw_info']['theme']['th_bg']);
				$this->template->set_var('sourcelang',$sourcelang);
				$this->template->set_var('targetlang',$targetlang);
				$this->template->set_var('app_name',$app_name);
				$this->template->set_var('missing_link',$GLOBALS['phpgw']->link('/index.php','menuaction=developer_tools.uilangfile.missingphrase'));
				$this->template->set_var('phrase_link',$GLOBALS['phpgw']->link('/index.php','menuaction=developer_tools.uilangfile.addphrase'));
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
					$this->template->set_var('mess_id',$GLOBALS['phpgw']->strip_html($mess_id));
					$this->template->set_var('source_content',$GLOBALS['phpgw']->strip_html($content));
					$this->template->set_var('content',$GLOBALS['phpgw']->strip_html($transy));
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
		}

		function save($which,$userlang)
		{
			$app_name = $GLOBALS['HTTP_POST_VARS']['app_name'];
			$sourcelang = $GLOBALS['HTTP_POST_VARS']['sourcelang'];
			$targetlang = $GLOBALS['HTTP_POST_VARS']['targetlang'];

			$this->bo->write_file($which,$app_name,$userlang);
			Header('Location: ' . $GLOBALS['phpgw']->link('/index.php','menuaction=developer_tools.uilangfile.edit&app_name='.$app_name
				. '&sourcelang=' . $sourcelang . '&targetlang=' . $targetlang));
		}

		function download($which,$userlang)
		{
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
			$browser->content_header('phpgw_' . $userlang . '.lang');
			while(list($mess_id,$data) = @each($langarray))
			{
				echo $mess_id . "\t" . $data['app_name'] . "\t" . $userlang . "\t" . $data['content'] . "\n";
			}
			$GLOBALS['phpgw']->common->phpgw_exit();
		}

		function index()
		{
			$start = $GLOBALS['HTTP_POST_VARS']['start'];
			$sort  = $GLOBALS['HTTP_POST_VARS']['sort'];
			$order = $GLOBALS['HTTP_POST_VARS']['order'];
			$query = $GLOBALS['HTTP_POST_VARS']['query'];

			$this->bo->save_sessiondata('','');
			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();
			include(PHPGW_APP_INC . '/header.inc.php');

			$this->template->set_file(array('applications' => 'applications.tpl'));
			$this->template->set_block('applications','list','list');
			$this->template->set_block('applications','row','row');

			$offset = $GLOBALS['phpgw_info']['user']['preferences']['common']['maxmatchs'];

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
			$this->template->set_var('bg_color',$GLOBALS['phpgw_info']['theme']['bg_color']);
			$this->template->set_var('th_bg',$GLOBALS['phpgw_info']['theme']['th_bg']);

			$this->template->set_var('sort_title',$this->nextmatchs->show_sort_order($sort,'title','title','/index.php',lang('Title'),'&menuaction=developer_tools.uilangfile.index'));
			$this->template->set_var('lang_showing',$this->nextmatchs->show_hits($total,$start));
			$this->template->set_var('left',$this->nextmatchs->left('/index.php',$start,$total,'&menuaction=developer_tools.uilangfile.index'));
			$this->template->set_var('right',$this->nextmatchs->right('/index.php',$start,$total,'&menuaction=developer_tools.uilangfile.index'));

			$this->template->set_var('lang_edit',lang('Edit'));
			//$this->template->set_var('lang_translate',lang('Translate'));
			$this->template->set_var('new_action',$GLOBALS['phpgw']->link('/index.php','menuaction=developer_tools.uilangfile.create'));
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

				$this->template->set_var('edit','<a href="' . $GLOBALS['phpgw']->link('/index.php','menuaction=developer_tools.uilangfile.edit&app_name=' . urlencode($app['name'])) . '"> ' . lang('Edit') . ' </a>');
			//	$this->template->set_var('translate','<a href="' . $GLOBALS['phpgw']->link('/index.php','menuaction=developer_tools.uilangfile.translate&app_name=' . urlencode($app['name'])) . '"> ' . lang('Translate') . ' </a>');

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
