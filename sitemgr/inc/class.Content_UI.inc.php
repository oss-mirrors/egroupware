<?php
	/**************************************************************************\
	* eGroupWare SiteMgr - Web Content Management                              *
	* http://www.egroupware.org                                                *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	class Content_UI
	{
		/**
		 * Reference to Common_UI's Common_UI object
		 *
		 * @var Common_UI
		 */
		var $common_ui;
		/**
		 * Instance of template class
		 *
		 * @var Template
		 */
		var $t;
		/**
		 * Reference to Common_BO's Content_BO object
		 *
		 * @var Content_BO
		 */
		var $bo;
		/**
		 * Reference to Common_BO's Modules_BO object
		 *
		 * @var Modules_BO
		 */
		var $modulebo;
		/**
		 * Reference to ACL object of Common_BO
		 *
		 * @var ACL_BO
		 */
		var $acl;
		/**
		 * Reference to viewable object of Common_BO
		 *
		 * @var Viewable_BO
		 */
		var $viewable;
		/**
		 * Reference to Common_BO's Categories_BO object
		 *
		 * @var Categories_BO
		 */
		var $cat_bo;
		/**
		 * Reference to Common_BO's Pages_BO object
		 *
		 * @var Pages_BO
		 */
		var $pages_bo;

		var $sitelanguages;
		var $worklanguage;
		var $errormsg;


		var $public_functions = array
		(
			'manage' => True,
			'commit' => True,
			'archive' => True
		);

		function Content_UI()
		{
			$this->common_ui =& CreateObject('sitemgr.Common_UI',True);
			$this->t = $GLOBALS['egw']->template;
			$this->t->egroupware_hack = False;
			$this->bo =& $GLOBALS['Common_BO']->content;
			$this->acl =& $GLOBALS['Common_BO']->acl;
			$this->modulebo =& $GLOBALS['Common_BO']->modules;
			$this->viewable =& $GLOBALS['Common_BO']->viewable;
			$this->cat_bo = $GLOBALS['Common_BO']->cats;
			$this->pages_bo = $GLOBALS['Common_BO']->pages;

			$this->sitelanguages = $GLOBALS['Common_BO']->sites->current_site['sitelanguages'];
				$GLOBALS['sitemgr_info']['userlang'] = $GLOBALS['egw']->session->appsession('language','sitemgr-site');
			$this->worklanguage = isset($_POST['savelanguage']) && preg_match('/^[a-z]{2}(-[a-z]{2})?$/',$_POST['savelanguage']) ? $_POST['savelanguage'] :
				($GLOBALS['sitemgr_info']['userlang'] ? $GLOBALS['sitemgr_info']['userlang'] : $this->sitelanguages[0]);
			//error_log(__METHOD__."() sitemgr_info[userlanguage]={$GLOBALS['sitemgr_info']['userlang']}, common[lang]={$GLOBALS['egw_info']['user']['preferences']['common']['lang']}, _POST[savelanguage]=$_POST[savelanguage], sitelanguages=".implode(', ',$this->sitelanguages)." --> worklanguage=$this->worklanguage");
			$this->errormsg = array();
		}

		function manage()
		{
			$GLOBALS['Common_BO']->globalize(array(
				'inputblockid','inputblocktitle','inputblocksort','inputblockview',
				'inputstate','btnSaveBlock','btnApplyBlock','btnDeleteBlock','btnCreateVersion',
				'btnDeleteVersion','inputmoduleid','inputarea','btnAddBlock','element'
			));
			global $inputblockid, $inputblocktitle, $inputblocksort,$inputblockview;
			global $inputstate,$btnSaveBlock,$btnApplyBlock,$btnDeleteBlock,$btnCreateVersion;
			global $inputmoduleid, $inputarea, $btnAddBlock, $btnDeleteVersion, $element;

			global $page_id,$cat_id;
			$page_id = $_GET['page_id'];
			$cat_id = $_GET['cat_id'];
			$block_id = $_GET['block_id'];

			if ($block_id)
			{
				$focus_reload_close = 'window.focus();';
			}
			elseif ($page_id)
			{
				$page = $this->pages_bo->getPage($page_id);
				if (!$this->acl->can_write_category($page->cat_id))
				{
					$GLOBALS['egw']->redirect_link('/index.php','menuaction=sitemgr.Outline_UI.manage');
				}
				$page_or_cat_name = $page->name;
				$cat_id = $page->cat_id;
				$goto = lang('Page manager');
				$scopename = lang('Page');
			}
			elseif ($cat_id && $cat_id != CURRENT_SITE_ID)
			{
				$cat = $this->cat_bo->getCategory($cat_id);
				if (!$this->acl->can_write_category($cat_id))
				{
					$GLOBALS['egw']->redirect_link('/index.php','menuaction=sitemgr.Outline_UI.manage');
				}
				$page_or_cat_name = $cat->name;
				$page_id = 0;
				$goto = lang('Category manager');
				$scopename = lang('Category');
			}
			else
			{
				$page_id = 0;
				$scopename = lang('Site');
			}

			if ($btnAddBlock || $_GET['add_block'])
			{
				if ($_GET['add_block'])
				{
					$inputmoduleid = $_GET['add_block'];
					$inputarea = $_GET['area'];
				}
				if ($inputmoduleid)
				{
					$block =& CreateObject('sitemgr.Block_SO',True);
					$block->module_id = $inputmoduleid;
					$block->area = $inputarea;
					$block->page_id = $page_id;
					$block->cat_id = $cat_id ? $cat_id : ($page_id ? $page->cat_id : CURRENT_SITE_ID);

					$newblock = $this->bo->addblock($block);
					if ($newblock)
					{
						$this->bo->createversion($newblock);
						if ($_GET['add_block'])
						{
							$GLOBALS['egw']->redirect_link('/index.php',array(
								'menuaction' => 'sitemgr.Content_UI.manage',
								'block_id'   => $newblock
							));
						}
					}
					else
					{
						$this->errormsg[] = lang("You are not entitled to create module %1 on this scope",$inputmoduleid);
					}
				}
				else
				{
					$this->errormsg[] = lang("You did not choose a module.");
				}
			}
			elseif ($btnSaveBlock || $btnApplyBlock || $_GET['sort_order'] && $block_id)
			{
				if ($_GET['sort_order'])
				{
					$block = $this->bo->getblock($block_id,$this->worklanguage);
					$block->sort_order += intval($_GET['sort_order']);
					$element = array();
					$inputstate = False;
				}
				else
				{
					$block =& CreateObject('sitemgr.Block_SO',True);
					$block->id = $inputblockid;
					$block->title = $inputblocktitle;
					$block->sort_order = $inputblocksort;
					$block->area = $inputarea;
					$block->view = $inputblockview;

					if (isset($element))	// not all blocks have elements (eg. Administration)
					{
						foreach($element as $version_id => &$content)
						{
							if (isset($GLOBALS['egw_unset_vars']["_POST[element][$version_id][i18n][htmlcontent]"])) // && !isset($content['i18n']['htmlcontent']))
							{
								if ($this->acl->is_admin())
								{
									$content['i18n']['htmlcontent'] =& $GLOBALS['egw_unset_vars']["_POST[element][$version_id][i18n][htmlcontent]"];
									unset($GLOBALS['egw_unset_vars']["_POST[element][$version_id][i18n][htmlcontent]"]);
								}
								else
								{
									$this->errormsg[] = lang('You need to be an administrator of this website to enter javascript!');
								}
							}
							// run all htmlcontent through htmlpurifier, if user is no site-admin
							elseif(isset($content['i18n']['htmlcontent']) && !$this->acl->is_admin())
							{
								$content['i18n']['htmlcontent'] = html::purify($content['i18n']['htmlcontent']);
							}
						}
					}
				}
				if (!$this->errormsg)
				{
					$result = $this->bo->saveblockdata($block,$element,$inputstate,$this->worklanguage,$_POST['scope']);
					if ($result !== True)
					{
						//result should be an array of validationerrors
						$this->errormsg = $result;
					}
					else
					{
						$this->errormsg[] = lang('Block saved');
						$focus_reload_close = 'opener.location.reload();';
					}
					if ($_GET['sort_order'] || $block_id && $btnSaveBlock && $result === True)
					{
						echo '<html><head></head><body onload="opener.location.reload();self.close()"></body></html>';
						$GLOBALS['egw']->common->egw_exit();
					}
				}
			}
			elseif ($btnReloadBlock && $block_id)
			{
					$this->errormsg[] = lang('Block reloaded');
					$focus_reload_close = 'opener.location.reload();';
			}
			elseif ($btnDeleteBlock || $_GET['deleteBlock'] && $block_id)
			{
				if ($_GET['deleteBlock']) $inputblockid = $block_id;
				if (!$this->bo->removeblock($inputblockid))
				{
					$this->errormsg[] =  lang("You are not entitled to edit block %1",$inputblockid);
				}
				//if we delete a block we were editing, there is nothing left to do
				if ($block_id)
				{
					echo '<html><head></head><body onload="opener.location.reload();self.close()"></body></html>';
				}
			}
			elseif ($btnCreateVersion)
			{
				$this->bo->createversion($inputblockid);
			}
			elseif ($btnDeleteVersion)
			{
				$version_id = array_keys($btnDeleteVersion);
				$this->bo->deleteversion($version_id[0]);
			}

			//if we are called with a block_id GET parameter, it is from sitemgr-site edit mode or from archiv/commit
			//we are shown in a separate edit window, without navbar.
			if ($block_id)
			{
				$block = $this->bo->getblock($block_id,$this->worklanguage);

				if (!($block && $this->acl->can_write_category($block->cat_id)))
				{
					echo '<p><center><b>'.lang('Attempt to edit non-editable block').'</b></center>';
					$GLOBALS['egw']->common->egw_exit(True);
				}
				$this->t->set_file('Blocks', 'edit_block.tpl');
				$this->t->set_block('Blocks','Block');
				$this->t->set_block('Block','Moduleeditor','MeBlock');
				$this->t->set_block('Block','Moduleview','MvBlock');
				$this->t->set_block('Moduleeditor','Version','EvBlock');
				$this->t->set_block('Blocks','EditorElement','EeBlock');
				$this->t->set_block('Blocks','EditorElementLarge','EeBlockLarge');
				$this->t->set_block('Moduleview','ViewElement','VeBlock');

				$action_vars = array('menuaction' => 'sitemgr.Content_UI.manage');
				foreach(array('page_id','cat_id','block_id') as $name)
				{
					if ($$name) $action_vars[$name] = $$name;
				}
				$action_url = $GLOBALS['egw']->link('/index.php',$action_vars);

				$this->t->set_var(array(
					'action_url' => $action_url,
					'validationerror' => implode('<br />',$this->errormsg),
					'lang_save' => lang('Save'),
					'lang_reload' => lang('Reload'),
					'lang_delete' => lang('Delete'),
					'lang_confirm' => lang('Do you realy want to delete this block?'),
					'contentarea' => lang('Contentarea'),
					'lang_createversion' => lang('Create new version'),
					'standalone' => '<div id="divMain">',
					'cancel_button' => '<input type="reset" value="'.lang('Cancel').'" onClick="self.close();" />',
					'apply_button' => '<input type="submit" name="btnApplyBlock" value="'.lang('Apply').'" />',
					'focus_reload_close' => $focus_reload_close,
				));
				$this->showblock($block,True,True,True);
				$GLOBALS['egw']->common->egw_header();
				$this->t->unknowns='keep';
				$this->t->pfp('out','Block');
				$GLOBALS['egw']->common->egw_exit();
				return;
			}

			$this->t->set_file('Managecontent', 'manage_content.tpl');
			$this->t->set_file('Blocks','edit_block.tpl');
			$this->t->set_block('Managecontent','Contentarea','CBlock');
			$this->t->set_block('Blocks','Block');
			$this->t->set_block('Block','Moduleeditor','MeBlock');
			$this->t->set_block('Block','Moduleview','MvBlock');
			$this->t->set_block('Moduleeditor','Version','EvBlock');
			$this->t->set_block('Blocks','EditorElement','EeBlock');
			$this->t->set_block('Blocks','EditorElementLarge','EeBlockLarge');
			$this->t->set_block('Moduleview','ViewElement','VeBlock');

			$contentareas = $this->bo->getContentAreas();
			if (is_array($contentareas))
			{
				$this->t->set_var(array(
					'help' => lang('You can override each content blocks default title. Be aware that not in all content areas the block title will be visible.'),
					'lang_save' => lang('Save'),
					'lang_delete' => lang('Delete'),
					'contentarea' => lang('Contentarea'),
					'lang_createversion' => lang('Create new version'),
					'apply_button' => '',
					'cancel_button' => '',
				));

				foreach ($contentareas as $contentarea)
				{
					$permittedmodules = $this->modulebo->getcascadingmodulepermissions($contentarea,$cat_id);

					$this->t->set_var(Array(
						'area' => $contentarea,
						'addblockform' =>
							($permittedmodules ?
								('<form method="POST" action="' . $action_url . '"><input type="hidden" value="' . $contentarea . '" name="inputarea" />' .
									'<select style="vertical-align:middle" size="10" name="inputmoduleid">' .
									$this->inputmoduleselect($permittedmodules) .
									'</select><input type="submit" name="btnAddBlock" value="' .
									lang('Add block to content area %1',$contentarea) .
									'" /></form>') :
								lang('No modules permitted for this content area/category')
							),
						'error' => (($contentarea == $inputarea) && $this->errormsg) ? join('<br>',$this->errormsg) : '',
					));

					//we get all blocks for the page and its category, and site wide,
					//but only the current scope is editable
					//if we have just edited a block in a certain language, we request all blocks in this language
					$blocks = $this->bo->getallblocksforarea($contentarea,$cat_id,$page_id,$this->worklanguage);

					$this->t->set_var('blocks','');

					if ($blocks)
					{
						while (list(,$block) = each($blocks))
						{
							//if the block is in our scope and we are entitled we edit it
							$editable = ($block->page_id == $page_id && $block->cat_id == $cat_id);
							$this->showblock($block,$editable);
							$this->t->parse('blocks','Block', true);
						}
					}
					$this->t->parse('CBlock','Contentarea', true);
				}
			}
			else
			{
				$this->t->set_var('CBlock',$contentareas);
			}
			$this->common_ui->DisplayHeader(lang('%1 content manager', $scopename).($page_or_cat_name ? (' - ' . $page_or_cat_name) : ''));
			$this->t->pfp('out', 'Managecontent');
			$this->common_ui->DisplayFooter();
		}


		function commit()
		{
			if ($_POST['btnCommit'])
			{
				while(list($cat_id,) = @each($_POST['cat']))
				{
					$this->cat_bo->commit($cat_id);
				}
				while(list($page_id,) = @each($_POST['page']))
				{
					$this->pages_bo->commit($page_id);
				}
				while(list($block_id,) = @each($_POST['block']))
				{
					$this->bo->commit($block_id);
				}
			}
			$this->common_ui->DisplayHeader();

			$this->t->set_file('Commit','commit.tpl');
			$this->t->set_block('Commit','Category','Cblock');
			$this->t->set_block('Commit','Page','Pblock');
			$this->t->set_block('Commit','Block','Bblock');
			$this->t->set_var(array(
				'commit_manager' => lang('Commit changes'),
				'lang_categories' => lang('Categories'),
				'lang_pages' => lang('Pages'),
				'lang_blocks' => lang('Content blocks'),
				'lang_commit' => lang('Commit changes'),
				'action_url' => $GLOBALS['egw']->link('/index.php',array('menuaction'=>'sitemgr.Content_UI.commit')),
			));

			//Categories
			$cats = $this->cat_bo->getpermittedcatsCommitable();
			while (list(,$cat_id) = @each($cats))
			{
				$cat = $this->cat_bo->getCategory($cat_id,$this->sitelanguages[0]);
				$this->t->set_var(array(
					'category' => $cat->name,
					'catid' => $cat_id,
					'addedorremoved' => ($cat->state == SITEMGR_STATE_PREPUBLISH) ? 'added' : 'removed',
					'edit' => $GLOBALS['egw']->link('/index.php',array(
						'cat_id' => $cat_id,
						'menuaction' => 'sitemgr.Categories_UI.edit'
					))
				));
				$this->t->parse('Cblock','Category',True);
			}

			//Pages
			$pages = $this->pages_bo->getpageIDListCommitable();

			while (list(,$page_id) = @each($pages))
			{
				$page = $this->pages_bo->getPage($page_id);
				$this->t->set_var(array(
					'page' => $page->name,
					'pageid' => $page_id,
					'addedorremoved' => ($page->state == SITEMGR_STATE_PREPUBLISH) ? 'added' : 'removed',
					'edit' => $GLOBALS['egw']->link('/index.php',array(
						'page_id' => $page_id,
						'menuaction' => 'sitemgr.Pages_UI.edit'
					))
				));
				$this->t->parse('Pblock','Page',True);
			}

			//Content Blocks
			$blocks = $this->bo->getcommitableblocks();
			while (list($block_id,$block) = @each($blocks))
			{
				$this->t->set_var(array(
					'block' => $this->bo->getlangblocktitle($block_id,$this->sitelanguages[0]),
					'blockid' => $block_id,
					'scope' => $this->blockscope($block->cat_id,$block->page_id),
					'area' => $this->blockarea($block),
					'addedorremovedorreplaced' => ($block->cnt == 2) ? 'replaced' :
						(($block->state == SITEMGR_STATE_PREPUBLISH) ? 'added' : 'removed'),
					'edit' =>  $GLOBALS['egw']->link('/index.php',array(
						'block_id' => $block_id,
						'menuaction' => 'sitemgr.Content_UI.manage'
					))
				));
				$this->t->parse('Bblock','Block',True);
			}

			$this->t->pfp('out', 'Commit');
			$this->common_ui->DisplayFooter();
		}

		function archive()
		{
			if ($_POST['btnReactivate'] || $_POST['btnDelete'])
			{
				if (is_array($_POST['cat']) && count($_POST['cat']) > 0)
				{
					foreach($_POST['cat'] as $cat_id => $nul)
					{
						if ($_POST['btnReactivate'])
						{
							$this->cat_bo->reactivate($cat_id);
						}
						elseif ($this->acl->is_admin()) // we need to do the ACL-check as we have to force
						{
							$this->cat_bo->removeCategory($cat_id,True,True);
						}
					}
				}
				if (is_array($_POST['page']) && count($_POST['page']) > 0)
				{
					foreach($_POST['page'] as $page_id => $nul)
					{
						if ($_POST['btnReactivate'])
						{
							$this->pages_bo->reactivate($page_id);
						}
						else
						{
							$this->pages_bo->removePage($page_id);
						}
					}
				}
				if (is_array($_POST['block']) && count($_POST['block']) > 0)
				{
					foreach($_POST['block'] as $block_id => $nul)
					{
						if ($_POST['btnReactivate'])
						{
							$this->bo->reactivate($block_id);
						}
						else
						{
							$this->bo->removeBlock($block_id);
						}
					}
				}
			}

			$this->common_ui->DisplayHeader();

			$this->t->set_file('Commit','archive.tpl');
			$this->t->set_block('Commit','Category','Cblock');
			$this->t->set_block('Commit','Page','Pblock');
			$this->t->set_block('Commit','Block','Bblock');
			$this->t->set_var(array(
				'action_url' => $GLOBALS['egw']->link('/index.php',array('menuaction'=>'sitemgr.Content_UI.archive')),
				'commit_manager' => lang('Archived content'),
				'lang_categories' => lang('Categories'),
				'lang_pages' => lang('Pages'),
				'lang_blocks' => lang('Content blocks'),
				'lang_reactivate' => lang('Reactivate content'),
				'lang_delete' => lang('Delete'),
				'lang_confirm' => lang('Do you realy want to delete the selected Categories (including all pages), Pages and Blocks?'),
			));

			//Categories
			$cats = $this->cat_bo->getpermittedcatsArchived();
			//we have to append the archived cats to the currentcats, in order to be able to access them later
			$this->cat_bo->currentcats = array_merge($this->cat_bo->currentcats,$cats);
			while (list(,$cat_id) = @each($cats))
			{
				$cat = $this->cat_bo->getCategory($cat_id,$this->sitelanguages[0],True);
				$this->t->set_var(array(
					'category' => $cat->name,
					'catid' => $cat_id,
					'edit' => $GLOBALS['egw']->link('/index.php',array(
						'cat_id' => $cat_id,
						'menuaction' => 'sitemgr.Categories_UI.edit'
					))
				));
				$this->t->parse('Cblock','Category',True);
			}

			//Pages
			$pages = $this->pages_bo->getpageIDListArchived();

			while (list(,$page_id) = @each($pages))
			{
				$page = $this->pages_bo->getPage($page_id);
				$this->t->set_var(array(
					'page' => $page->name,
					'pageid' => $page_id,
					'edit' => $GLOBALS['egw']->link('/index.php',array(
						'page_id' => $page_id,
						'menuaction' => 'sitemgr.Pages_UI.edit'
					))
				));
				$this->t->parse('Pblock','Page',True);
			}

			//Content Blocks
			$blocks = $this->bo->getarchivedblocks();
			while (list($block_id,$block) = @each($blocks))
			{
				$this->t->set_var(array(
					'block' => $this->bo->getlangblocktitle($block_id,$this->sitelanguages[0]),
					'blockid' => $block_id,
					'scope' => $this->blockscope($block->cat_id,$block->page_id),
					'area' => $this->blockarea($block),
					'edit' =>  $GLOBALS['egw']->link('/index.php',array(
						'block_id' => $block_id,
						'menuaction' => 'sitemgr.Content_UI.manage'
					))
				));
				$this->t->parse('Bblock','Block',True);
			}

			$this->t->pfp('out', 'Commit');
			$this->common_ui->DisplayFooter();
		}

		function inputmoduleselect($modules)
		{
			$returnValue = '';
			static $label_sort;

			if (!isset($label_sort)) $label_sort = create_function('$a,$b', 'return strcasecmp($a["module_name"],$b["module_name"]);');
			uasort($modules,$label_sort);

			foreach($modules as $id => $module)
			{
				$returnValue.='<option title="' . lang($module['description']) . '" value="'.$id.'">'.
					$module['module_name'].'</option>'."\n";
			}
			return $returnValue;
		}

		function inputviewselect($default)
		{
			$returnValue = '';
			foreach($this->viewable as $value => $display)
			{
				$selected = ($default == $value) ? $selected = 'selected="selected" ' : '';
				$returnValue.='<option '.$selected.'value="'.$value.'">'.
					$display.'</option>'."\n";
			}
			return $returnValue;
		}

		function blockarea($block,$editable=False)
		{
			$area = $block->area;
			$areas = array(
				$area => array(
					'label' => $area,
					'extra' => 'style="text-decoration: underline;"',
				),
			);

			if ($editable)
			{
				$contentAreas = $this->bo->getContentAreas();

				if (is_array($contentAreas))
				{
					foreach($contentAreas as $k => $data)
					{
						if ($data != $area)
						{
							$areas[$data]['label'] = $data;
						}
					}
				}
			}
			$out = "<select name=\"inputarea\">\n";

			foreach($areas as $k => $data)
			{
				$out .= html::select_option($k,
					is_array($data) && isset($data['label']) ? $data['label'] : $data,
					array($area),
					True,
					is_array($data) && isset($data['title']) ? $data['title'] : '',
					is_array($data) && isset($data['extra']) ? $data['extra'] : '');
			}
			$out .= "</select>\n";
			return $out;
		}


		function blockscope($cat_id,$page_id,$editable=False)
		{
			if ($editable)
			{
				$scope = "$cat_id,$page_id";
				$scopes = array();

				// Whole Website
				if ($this->acl->can_write_category(CURRENT_SITE_ID))
				{
					$scopes[CURRENT_SITE_ID.',0'] = array();
					$scopes[CURRENT_SITE_ID.',0']['label'] = lang('Site wide');
					if (($cat_id == CURRENT_SITE_ID) && ($page_id == 0))
					{
						$scopes[CURRENT_SITE_ID.',0']['extra'] = 'style="font-style: italic; text-decoration: underline;"';
					}
					else
					{
						$scopes[CURRENT_SITE_ID.',0']['extra'] = 'style="font-style: italic;"';
					}
				}
				// Create list of all available (editable) categories
				foreach($this->cat_bo->getpermittedcatsWrite() as $cats_id)
				{
					$cat = $this->cat_bo->getCategory($cats_id);
					$padding = str_pad('',12*($cat->depth-1),'&nbsp;');
					$scopes[$cats_id.',0'] = array();
					$scopes[$cats_id.',0']['label'] = $padding.$cat->name.' ('.lang('Category').')';
					if (($cats_id == $cat_id) && ($page_id == 0))
					{
						$scopes[$cats_id.',0']['extra'] = 'style="font-weight: bold; text-decoration: underline;"';
					}
					else
					{
						$scopes[$cats_id.',0']['extra'] = 'style="font-weight: bold;"';
					}
					foreach ($this->pages_bo->getPageIDList($cats_id) as $pages_id)
					{
						$page = $this->pages_bo->getpage($pages_id);
						$padding = str_pad('',12*($cat->depth),'&nbsp;');
						$scopes[$cats_id.','.$pages_id] = array();
						$scopes[$cats_id.','.$pages_id]['label'] = $padding.$page->name;
						$scopes[$cats_id.','.$pages_id]['title'] = $page->title;
						if (($cats_id == $cat_id) && ($page_id == $pages_id))
						{
							$scopes[$cats_id.','.$pages_id]['extra'] = 'style="text-decoration: underline;"';
						}
					}
				}
			}
			elseif ($cat_id == CURRENT_SITE_ID)
			{
				$scopes[$scope] = lang('Site wide');
			}
			else
			{
				$cat = $this->cat_bo->getCategory($cat_id);
				$scopes[$scope] = lang('Category') . ' ' . $cat->name;
				if ($page_id)
				{
					$page = $this->pages_bo->getPage($page_id);
					$scopes[$scope] .= ' - ' . lang('Page') . ' ' . $page->name;
				}
			}
			if (count($scopes) > 1)
			{
				$out = "<select name=\"scope\">\n";

				foreach($scopes as $k => $data)
				{
					$out .= html::select_option($k,
						is_array($data) && isset($data['label']) ? $data['label'] : $data,
						array($scope),
						True,
						is_array($data) && isset($data['title']) ? $data['title'] : '',
						is_array($data) && isset($data['extra']) ? $data['extra'] : '');
				}
				$out .= "</select>\n";
				return $out;
			}
			else
			{
				return $scopes[$scope];
			}
		}

		//if the block is shown on its own ($standalone), we add information about its,scope
		function showblock($block,$editable,$standalone=False)
		{
			global $page_id,$cat_id, $inputblockid;
			//TODO: wrap a module storage around createmodule as in template3,
			//TODO: so that we do not create the same module object twice
			$moduleobject =& $this->modulebo->createmodule($block->module_name);

			if (count($this->sitelanguages) > 1)
			{
				$langs = array();
				foreach ($this->sitelanguages as $lang)
				{
					$langs[$lang] = $GLOBALS['Common_BO']->getlangname($lang);
				}
				$langselect = html::select('savelanguage',$this->worklanguage,$langs,false,' onchange="this.form.submit()"');
			}
			$this->t->set_var(array(
				'moduleinfo' => $block->module_name,
				'description' => lang($moduleobject->description),
				'savelang' => $langselect
			));

			//if the block is in our scope and we are entitled we edit it
			if ($editable)
			{
				$editorstandardelements = array(
					array('label' => lang('Title'),
							'form' => ('<input type="text" name="inputblocktitle" value="' .
							htmlspecialchars(isset($block->title) ? $block->title : $moduleobject->title) . '" />')
					),
					array('label' => lang('Seen by'),
							'form' => ('<select name="inputblockview">' .
							$this->inputviewselect((int)$block->view) . '</select>')
					),
					array('label' => lang('Sort order'),
							'form' => ('<input type="text" name="inputblocksort" size="2" value="' .
							(int)$block->sort_order . '">')
					)
				);
				if ($standalone)
				{
					$editorstandardelements[] = array(
						'label' => lang('Scope'),
						'form' => $this->blockscope($block->cat_id,$block->page_id,True)
					);
					$editorstandardelements[] = array(
						'label' => lang('Area'),
						'form' => $this->blockarea($block,True)
					);
				}

				$moduleobject->set_block($block);

				$this->t->set_var(Array(
					'blockid' => $block->id,
					'validationerror' => (($block->id == $inputblockid) && $this->errormsg) ? join('<br>',$this->errormsg) : '',
				));
				$this->t->set_var('standardelements','');
				while (list(,$element) = each($editorstandardelements))
				{
					$this->t->set_var(Array(
						'label' => $element['label'],
						'form' => $element['form']
					));
					$this->t->parse('standardelements','EditorElement', true);
				}

				$versions = $this->bo->getallversionsforblock($block->id,$this->worklanguage);
				$this->t->set_var('EvBlock','');
				while (list($version_id,$version) = each($versions))
				{
					//set the version of the block which is referenced by the moduleobject,
					//so that we retrieve a interface with the current version's arguments
					$block->set_version($version);
					$editormoduleelements = $moduleobject->get_user_interface();
					$this->t->set_var(array(
						'version_id' => $version_id,
						'state' => $GLOBALS['Common_BO']->inputstateselect($version['state']),
						'deleteversion' => lang('Delete Version'),
						'versionelements' => ''
					));
					while (list(,$element) = each($editormoduleelements))
					{
						$this->t->set_var(Array(
							'label' => $element['label'],
							'form' => $element['form']
						));
						$this->t->parse('versionelements',$element['large']?'EditorElementLarge':'EditorElement', true);
					}
					$this->t->parse('EvBlock','Version', true);
				}

				$this->t->parse('MeBlock','Moduleeditor');
				$this->t->set_var('MvBlock','');
			}
			//otherwise we only show it
			else
			{
				if ($block->page_id)
				{
					$blockscope = lang('Page');
				}
				elseif ($block->cat_id != CURRENT_SITE_ID)
				{
					$cat = $this->cat_bo->getCategory($block->cat_id);
					$blockscope =  lang('Category') . ' - ' . $cat->name;
				}
				else
				{
					$blockscope =  lang('Site');
				}

				$viewstandardelements = array(
					array('label' => lang('Scope'),
							'value' => $blockscope
					),
					array('label' => lang('Area'),
							'value' => $block->area
					),
					array('label' => lang('Title'),
							'value' => ($block->title ? $block->title : $moduleobject->title)
					),
					array('label' => lang('Seen by'),
							'value' => $this->viewable[(int)$block->view]
					),
					array('label' => lang('Sort order'),
							'value' => (int)$block->sort_order
					)
				);
//                $viewmoduleelements = array();
//                while (list($argument,$argdef) = @each($moduleobject->arguments))
//                {
//                  $value = $block->arguments[$argument];
//                  $viewmoduleelements[] = array(
//                    'label' => $argdef['label'],
//                    'value' => $GLOBALS['egw']->strip_html($value)
//                  );
//                }
//                $interface = array_merge($viewstandardelements,$viewmoduleelements);
				$interface = $viewstandardelements;
				$this->t->set_var('VeBlock','');
				while (list(,$element) = each($interface))
				{
					$this->t->set_var(Array(
						'label' => $element['label'],
						'value' => $element['value'])
					);
					$this->t->parse('VeBlock','ViewElement', true);
				}
				$this->t->parse('MvBlock','Moduleview');
				$this->t->set_var('MeBlock','');
			}
		}
	}
