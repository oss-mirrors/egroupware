<?php
	/***************************************************************************\
	* http://www.phpgroupware.org                                               *
	* -------------------------------------------------                         *
	* This program is free software; you can redistribute it and/or modify it   *
	* under the terms of the GNU General Public License as published by the     *
	* Free Software Foundation; either version 2 of the License, or (at your    *
	* option) any later version.                                                *
	\***************************************************************************/
	/* $Id$ */
	
	class Common_UI
	{
		var $t, $acl, $theme, $do_sites_exist;
		var $public_functions = array
		(
			'DisplayPrefs' => True
		);

		function Common_UI()
		{
			global $Common_BO;
			$Common_BO = CreateObject('sitemgr.Common_BO');
			$this->do_sites_exist = $Common_BO->sites->set_currentsite(False);
			$this->t = $GLOBALS['phpgw']->template;
			$this->acl = &$Common_BO->acl;
			$this->theme = &$Common_BO->theme;
			$this->pages_bo = &$Common_BO->pages;
			$this->cat_bo = &$Common_BO->cats;
		}

		function DisplayPrefs()
		{
			$this->DisplayHeader();
			if ($this->acl->is_admin())
			{
				if ($_POST['btnlangchange'])
				{
					echo '<p>';
					while (list($oldlang,$newlang) = each($_POST['change']))
					{
						if ($newlang == "delete")
						{
							echo '<b>' . lang('Deleting all data for %1',$GLOBALS['Common_BO']->getlangname($oldlang)) . '</b><br>';
							$this->pages_bo->removealllang($oldlang);
							$this->cat_bo->removealllang($oldlang);
						}
						else
						{
							echo '<b>' . lang('Migrating data for %1 to %2',
									$GLOBALS['Common_BO']->getlangname($oldlang),
									$GLOBALS['Common_BO']->getlangname($newlang)) . 
							'</b><br>';
							$this->pages_bo->migratealllang($oldlang,$newlang);
							$this->cat_bo->migratealllang($oldlang,$newlang);
						}
					}
					echo '</p>';
				}

				if ($_POST['btnSave'])
				{
					$preferences = array(
						'home_page_id','themesel','site_languages');

					$oldsitelanguages = $GLOBALS['Common_BO']->sites->current_site['site_languages'];
					if ($oldsitelanguages && ($oldsitelanguages != $_POST['pref']['site_languages']))
					{
						$oldsitelanguages = explode(',',$oldsitelanguages);
						$newsitelanguages = explode(',',$_POST['pref']['site_languages']);
						$replacedlang = array_diff($oldsitelanguages,$newsitelanguages);
						$addedlang = array_diff($newsitelanguages,$oldsitelanguages);
						if ($replacedlang)
						{
							echo lang('You removed one ore more languages from your site languages.') . '<br>' .
							lang('What do you want to do with existing translations of categories and pages for this language?') . '<br>';
							if ($addedlang)
							{
								echo lang('You can either migrate them to a new language or delete them') . '<br>';
							}
							else
							{
								echo lang('Do you want to delete them?'). '<br>';
							}
							echo '<form action="' . 
							$GLOBALS['phpgw']->link('/index.php','menuaction=sitemgr.Common_UI.DisplayPrefs') .
							'" method="post"><table>';
							foreach ($replacedlang as $oldlang)
							{
								$oldlangname = $GLOBALS['Common_BO']->getlangname($oldlang);
								echo "<tr><td>" . $oldlangname . "</td>";
								if ($addedlang)
								{
									foreach ($addedlang as $newlang)
									{
										echo '<td><input type="radio" name="change[' . $oldlang . 
										']" value="' . $newlang . '"> Migrate to ' . 
										$GLOBALS['Common_BO']->getlangname($newlang) . "</td>";
									}
								}
								echo '<td><input type="radio" name="change[' . $oldlang . ']" value="delete"> delete</td></tr>';
							}
							echo '<tr><td><input type="submit" name="btnlangchange" value="' . 
							lang('Submit') . '"></td></tr></table></form>';
						}
					}

					$oldsitelanguages = $oldsitelanguages ? explode(',',$oldsitelanguages) : array("en");
					foreach ($oldsitelanguages as $lang)
					{
						array_push($preferences,'sitemgr-site-name-' . $lang);
					}

					$GLOBALS['Common_BO']->sites->saveprefs($_POST['pref']);

					echo '<p><b>' . lang('Changes Saved.') . '</b></p>';
					unset($preferences);
				}
				
				$sitelanguages = explode(',',$GLOBALS['Common_BO']->sites->current_site['site_languages']);
				$sitelanguages = $sitelanguages ? $sitelanguages : array("en");
				
				foreach ($sitelanguages as $lang)
				{
					$langname = $GLOBALS['Common_BO']->getlangname($lang);
					$preferences['site_name_' . $lang] = array(
						'title'=>lang('Site name'). ' ' . $langname,
						'note'=>lang('This is used chiefly for meta data and the title bar. If you change the site languages below you have to save before being able to set this preference for a new language.'),
						'default'=>lang('New sitemgr site')
					);
					 $preferences['site_desc_' . $lang] = array(
						'title'=>lang('Site descriptioin'). ' ' . $langname,
						'note'=>lang('This is used chiefly for meta data. If you change the site languages below you have to save before being able to set this preference for a new language.'),
					);
				}

				$preferences['home_page_id'] = array(
					'title'=>lang('Default home page ID number'),
					'note'=>lang('This should be a page that is readable by everyone. If you leave this blank, the site index will be shown by default.'),
					'input'=>'option',
					'options'=>$this->pages_bo->getPageOptionList()
				);
				$preferences['themesel'] = array(
					'title'=>lang('Template select'),
					'note'=>lang('Choose your site\'s theme or template.  Note that if you changed the above checkbox you need to save before choosing a theme or template.'),
					'input'=>'option',
					'options'=>$this->theme->getAvailableThemes(),
					'default'=>'NukeNews'
				);
				$preferences['site_languages'] = array(
					'title'=>lang('Languages the site user can choose from'),
					'note'=>lang('This should be a comma-separated list of language-codes.'),
					'default'=>'en'
				);

				$this->t->set_file('sitemgr_prefs','sitemgr_preferences.tpl');
				$this->t->set_var('formaction',$GLOBALS['phpgw']->link(
					'/index.php','menuaction=sitemgr.Common_UI.DisplayPrefs'));
				$this->t->set_var(Array('setup_instructions' => lang('SiteMgr Setup Instructions'),
							'options' => lang('SiteMgr Options'),
							'lang_save' => lang('Save'),
 							'lang_subdir' => lang('There are two subdirectories off of your sitemgr directory that you should move before you do anything else.  You don\'t <i>have</i> to move either of these directories, although you will probably want to.'),
							'lang_first_directory' => lang('The first directory to think about is sitemgr-link.  If you move this to the parent directory of sitemgr (your phpgroupware root directory) then you can use setup to install the app and everyone with access to the app will get an icon on their navbar that links them directly to the public web site.  If you don\'t want this icon, there\'s no reason to ever bother with the directory.'),
							'lang_second_directory' => lang('The second directory is the sitemgr-site directory.  This can be moved <i>anywhere</i>.  It can also be named <i>anything</i>.  Wherever it winds up, when you point a web browser to it, you will get the generated website.  Assuming, of course, that you\'ve accurately completed the setup fields below and also <b><i>edited the config.inc.php</i></b> file.'),
							'lang_edit_config_inc}' => lang('The config.inc.php file needs to be edited to point to the phpGroupWare directory. Copy the config.inc.php.template file to config.inc.php and then edit it.')
				));

				$this->t->set_block('sitemgr_prefs','PrefBlock','PBlock');
				reset($preferences);
				while (list($name,$details) = each($preferences))
				{
					$inputbox = '';
					switch($details['input'])
					{
						case 'checkbox':
							$inputbox = $this->inputCheck($name);
							break;
						case 'option':
							$inputbox = $this->inputOption($name,
								$details['options'],$details['default']);
							break;
						case 'inputbox':
						default:
							$inputbox = $this->inputText($name,
								$details['input_size'],$details['default']);
					}
					if ($inputbox)
					{
						$this->PrefBlock($details['title'],$inputbox,$details['note']);
					}
				}
				$this->t->pfp('out','sitemgr_prefs');
			}
			else
			{
				echo lang("You must be an administrator to setup the Site Manager.") . "<br><br>";
			}
			$this->DisplayFooter();
		}

		function inputText($name='',$size=40,$default='')
		{
			if (!is_int($size))
			{
				$size=40;
			}
			$val = $GLOBALS['Common_BO']->sites->current_site[$name];
			if (!$val)
			{
				$val = $default;
			}

			return '<input type="text" size="'.$size.
				'" name="pref['.$name.']" value="'.$val.'">';
		}

		function inputCheck($name = '')
		{
			$val = $GLOBALS['Common_BO']->sites->current_site[$name];
			if ($val)
			{
				$checked_yes = ' CHECKED';
				$checked_no = '';
			}
			else
			{
				$checked_yes = '';
				$checked_no = ' CHECKED';
			}
			return '<INPUT TYPE="radio" NAME="pref['.$name.']" VALUE="1"'.
				$checked_yes.'>Yes</INPUT>'."\n".
				'<INPUT TYPE="radio" NAME="'.$name.'" VALUE="0"'.
				$checked_no.'>No</INPUT>'."\n";
				
		}

		function inputOption($name = '', $options='', $default = '')
		{
			if (!is_array($options) || count($options)==0)
			{
				return lang('No options available.');
			}
			$val = $GLOBALS['Common_BO']->sites->current_site[$name];
			if(!$val)
			{
				$val = $default;
			}
			$returnValue = '<SELECT NAME="pref['.$name.']">'."\n";
			
			foreach($options as $option)
			{
				$selected='';
				if ($val == $option['value'])
				{
					$selected = 'SELECTED ';
				}
				$returnValue.='<OPTION '.$selected.'VALUE="'.$option['value'].'">'.
					$option['display'].'</OPTION>'."\n";
			}
			$returnValue .= '</SELECT>';
			return $returnValue;
		}

		function PrefBlock($title,$input,$note)
		{
			//$this->t->set_var('PBlock','');
			$this->t->set_var('pref-title',$title);
			$this->t->set_var('pref-input',$input);
			$this->t->set_var('pref-note',$note);
			$this->t->parse('PBlock','PrefBlock',true);
		}

		function DisplayHeader()
		{
			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();
			$this->t->set_file('sitemgr_header','sitemgr_header.tpl');
			$this->t->set_block('sitemgr_header','switch','switchhandle');
			if ($this->do_sites_exist)
			{
				$this->t->set_var(Array(
					'mainmenu' => $GLOBALS['phpgw']->link('/index.php','menuaction=sitemgr.MainMenu_UI.DisplayMenu'),
					'sitemgr-site' => $GLOBALS['phpgw']->link('/sitemgr-link/'),
					'sitemgr_administration' => lang('Web Content Manager Administration'),
					'lang_sitename' => lang('Website name'),
					'sitename' => $GLOBALS['Common_BO']->sites->current_site['site_name'],
					'view_menu' => lang('View Administrative Menu'),
					'view_site' => lang('View Generated Site')
				));
				if ($GLOBALS['Common_BO']->sites->getnumberofsites() > 1)
				{
					$this->t->set_var('sitelist',$this->siteselectlist());
					$this->t->parse('switchhandle','switch');
				}
				else
				{
					$this->t->set_var('switchhandle','');
				}
				$this->t->pparse('out','sitemgr_header');
			}
			else
			{
				echo lang('No websites defined');
				$GLOBALS['phpgw']->common->phpgw_exit(True);
			}
		}

		function DisplayFooter()
		{
echo (int)substr($GLOBALS['phpgw_info']['server']['versions']['phpgwapi'],4);
			$this->t->set_file('sitemgr_footer','sitemgr_footer.tpl');
			$this->t->pfp('out','sitemgr_footer');
		}

		function siteselectlist()
		{
			$sites = $GLOBALS['Common_BO']->sites->list_sites(False);
			$selectlist= '<option>' . lang('Switch website') . '</option>';
			while(list($site_id,$site) = @each($sites))
			{
				$selectlist .= '<option value="' . $site_id . '">' . $site['site_name'] . '</option>' . "\n";
			}
			return $selectlist;
		}
	}	
?>
