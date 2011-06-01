<?php
	/**************************************************************************\
	* eGroupWare - Bookmarks                                                   *
	* http://www.egroupware.org                                                *
	* Based on Bookmarker Copyright (C) 1998  Padraic Renaghan                 *
	*                     http://www.renaghan.com/bookmarker                   *
	* Ported to phpgroupware by Joseph Engo                                    *
	* Ported to three-layered design by Michael Totschnig                      *
	* Ported to eTemplate & additional eGW features added by Nathan Gray       *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	define('TREE',1);
	define('_LIST',2);
	define('CREATE',3);

	class bookmarks_ui
	{
		var $templ;
		var $bo;
		var $expandedcats;

		public static $tabs = 'general|details|links|custom|history';

		// Keep status and movement path
		private $location_info = array();

		var $public_functions = array
		(
			'edit' => True,
			'create' => True,
			'_list' => True,
			'tree' => True,
			'view' => True,
			'mail' => True,
			'redirect' => True,
			'export' => True,
			'import' => True
		);

		function __construct()
		{
			$this->templ = new etemplate();
			$this->bo = new bookmarks_bo();
			$this->expandedcats = array();
			$this->location_info = $this->bo->read_session_data();
		}

		function init()
		{
			// we maintain two levels of state:
			// returnto the main interface (tree or list)
			// returnto2 temporaray interface (create, edit, view, mail)
			$returnto2 = $this->location_info['returnto2'];
			$returnto = $this->location_info['returnto'];
			if ($returnto2)
			{
				$this->$returnto2();
			}
			elseif ($returnto)
			{
				$this->$returnto();
			}
			elseif ($GLOBALS['egw_info']['user']['preferences']['bookmarks']['defaultview'] == 'tree')
			{
				$this->tree();
			}
			else
			{
				$this->_list();
			}
		}

		function app_messages()
		{
			if ($this->bo->error_msg)
			{
				$bk_output_html = lang('Error') . ': ' . $this->bo->error_msg ;
			}
			if ($this->bo->msg)
			{
				$bk_output_html .= $this->bo->msg;
			}

			return $bk_output_html;
		}

		/**
		* Create a new bookmark
		*/
		function create($content = array())
		{
			//if we redirect to edit categories, we remember form values and try to come back to create
			if ($content['edit_category'])
			{
				$this->bo->grab_form_values($this->location_info['returnto'],'create',$bookmark);
				$GLOBALS['egw']->redirect($GLOBALS['egw']->link('/index.php','menuaction=preferences.uicategories.index&cats_app=bookmarks&cats_level=True&global_cats=True'));
			}
			//save bookmark
			if ($content['save'])
			{
				unset($content['save']);
				$bm_id = $this->bo->add($content);
				if ($bm_id)
				{
					$this->location_info['bm_id'] = $bm_id;
					$this->view();
					return;
				}
			}
			//if we come back from editing categories we restore form values
			elseif ($this->location_info['returnto2'] == 'create')
			{
				$bookmark['name']        = $this->location_info['bookmark']['name'];
				$bookmark['url']         = $this->location_info['bookmark']['url'];
				$bookmark['desc']        = $this->location_info['bookmark']['desc'];
				$bookmark['keywords']    = $this->location_info['bookmark']['keywords'];
				$bookmark['category']    = $this->location_info['bookmark']['category'];
				$bookmark['rating']      = $this->location_info['bookmark']['rating'];
				$bookmark['access']      = $this->location_info['bookmark']['access'];
			}
			//if the user cancelled we go back to the view we came from
			if ($content['cancel'])
			{
				unset($this->location_info['returnto2']);
				$this->init();
				return;
			}
			//store the view, we came from originally(list,tree), and the view we are in
			$this->location_info['bookmark'] = False;
			$this->location_info['returnto2'] = 'create';
			$this->bo->save_session_data($this->location_info);

			if(!$bookmark['url']) $bookmark['url'] = 'http://';
			if(!$bookmark['access']) $bookmark['access'] = 'public';

			$bookmark['msg'] = $this->app_messages();

			$GLOBALS['egw_info']['flags']['app_header'] = lang('New Bookmark');
			$this->templ->read('bookmarks.add');
			$this->templ->exec('bookmarks.bookmarks_ui.create', $bookmark, array(), array(), array(), 2);
		}

		/**
		* Edit an existing bookmark, if you have permission
		*
		* @param $content Array of values returned from etemplate
		*/
		function edit($content = array())
		{
			if (isset($_GET['bm_id']))
			{
				$bm_id = $_GET['bm_id'];
			}
			elseif (is_array($this->location_info))
			{
				$bm_id = $this->location_info['bm_id'];
			}
			elseif ($content['bm_id']) 
			{
				$bm_id = $content['bm_id'];
			}
			//if the user cancelled we close popup
			if ($content['cancel'] || !isset($bm_id))
			{
				unset($this->location_info['returnto2']);
				echo "<html><body><script>window.close();</script></body></html>\n";
				$this->init();
				common::egw_exit();
			}
			//delete bookmark and close popup
			if($content['delete']) {
				unset($this->location_info['returnto2']);
				echo "<html><body><script>window.close();</script></body></html>\n";
				$this->init();
				common::egw_exit();
			}
			//if we redirect to edit categories, we remember form values and try to come back to edit
			if ($content['edit_category'])
			{
				unset($content['edit_category']);
				$this->bo->grab_form_values($this->location_info['returnto'],'edit',$content);
				$GLOBALS['egw']->redirect($GLOBALS['egw']->link('/index.php','menuaction=preferences.uicategories.index&cats_app=bookmarks&cats_level=True&global_cats=True'));
			}
			//save bookmark and go to list interface
			if ($content['save'] || $content['apply'])
			{
				if ($this->bo->save($bm_id,$content))
				{
					if($content['save']) {
						unset($this->location_info['returnto2']);
						echo "<html><body><script>window.close();</script></body></html>\n";
						$this->init();
						return;
					}
				}
			}

			$bookmark = $this->bo->read($bm_id);
			if (!$bookmark[EGW_ACL_EDIT])
			{
				return $this->view($content);
			}

			//if we come back from editing categories we restore form values
			if ($this->location_info['bookmark'])
			{
				$bookmark['name']     = $this->location_info['bookmark']['name'];
				$bookmark['url']      = $this->location_info['bookmark']['url'];
				$bookmark['desc']     = $this->location_info['bookmark']['desc'];
				$bookmark['keywords'] = $this->location_info['bookmark']['keywords'];
				$bookmark['category'] = $this->location_info['bookmark']['category'];
				$bookmark['rating']   = $this->location_info['bookmark']['rating'];
			}

			//store the view we are in
			$this->location_info['bookmark'] = False;
			$this->location_info['returnto2'] = 'edit';
			$this->location_info['bm_id'] = $bm_id;
			$this->bo->save_session_data($this->location_info);

			$bookmark['msg'] = $this->app_messages();

			// Hide the URL link, show the editable text field
			$bookmark['edit'] = True;

			// Set up eGW link widget
			$bookmark['link_to'] = array(
				'to_id'	=>	$bm_id,
				'to_app'=>	'bookmarks'
			);

			// Set up custom fields
			if(count(config::get_customfields('bookmarks',true)) == 0) {
				$readonly[$tabs]['custom'] = true;
			}

			// Set up history
			$bookmark['history'] = self::setup_history($bm_id);
			$sel_options['status'] = $this->bo->field2label;

			$readonlys['edit'] = True; // Already here
			$readonlys['save'] = !$bookmark[EGW_ACL_EDIT];
			$readonlys['apply'] = !$bookmark[EGW_ACL_EDIT];
			$readonlys['delete'] = !$bookmark[EGW_ACL_DELETE];

			$persist['bm_id'] = $bm_id;

			$GLOBALS['egw_info']['flags']['app_header'] = lang('Edit Bookmark - %1', $bookmark['stripped_name']);
			$this->templ->read('bookmarks.edit');
			$this->templ->exec('bookmarks.bookmarks_ui.edit', $bookmark, $sel_options, $readonlys, $persist, 2);
		}

		/**
		*	Display a list of bookmarks
		*
		*	@param content Array of values returned from eTemplate
		*/
		function _list($content = array())
		{
			if (is_array($this->location_info))
			{
				$start = $this->location_info['start'];
				$bm_cat = $this->location_info['bm_cat'];
			}
			$this->location_info['start'] = $start;
			$this->location_info['bm_cat'] = $bm_cat;
			$this->location_info['returnto'] = '_list';
			unset($this->location_info['returnto2']);
			$this->bo->save_session_data($this->location_info);

			if($content['add']) {
				$GLOBALS['egw']->redirect_link('/index.php', array('menuaction' => 'bookmarks.bookmarks_ui.create'));
			} elseif ($content['nm']['rows']) { 
				if($content['nm']['rows']['edit']) {
					$bm_id = key($content['nm']['rows']['edit']);
					$GLOBALS['egw']->redirect_link('/index.php', array(
						'menuaction'	=>	'bookmarks.bookmarks_ui.edit',
						'bm_id'		=>	$bm_id
					));
				} elseif($content['nm']['rows']['delete']) {
					$bm_id = key($content['nm']['rows']['delete']);
					$this->bo->delete($bm_id);
				}
			} 
			if($content['action']) {
				$content['nm']['nm_action'] = $conent['action'];
			}
			if ($content['nm']['nm_action']) {
				switch ($content['nm']['nm_action']) {
					case 'delete':
						$i = 0;
						foreach($content['nm']['selected'] as $id) {
							if ($this->bo->delete($id))
							{
								$i++;
							}
						}
						$this->bo->msg = lang('%1 bookmarks have been deleted',$i);
						break;
					case 'mailto':
						$this->mail(array('bm_id' => $content['nm']['selected']));
						break;
				}
			}

			$values['nm'] = $GLOBALS['egw']->session->appsession('_list', 'bookmarks');
			if(!is_array($values['nm'])) {
				$values['nm'] = array(
					'get_rows'	=>	'bookmarks.bookmarks_ui.get_rows',
					'template'	=>	'bookmarks.list.row',
					'no_filter'	=>	True,
					'no_filter2'	=>	True,
					'row_id'	=>	'bm_id',
					'default_cols'	=>	'!legacy_actions',  // switch legacy actions column and row off by default
				);
			}
			$values['nm']['actions'] = $this->get_actions();

			if($bm_cat) {
				$values['nm']['cat_id'] = $bm_cat;
			}
			if($_GET['search']) {
				$values['nm']['search'] = $_GET['search'];
			}

			$sel_options['action']['mail'] = lang('Mail');
			$sel_options['action']['delete'] = lang('Delete');

			$values['msg'] = $this->app_messages();

			$GLOBALS['egw_info']['flags']['app_header'] = lang('Bookmarks');
			$this->templ->read('bookmarks.list');
			$this->templ->exec('bookmarks.bookmarks_ui._list', $values, $sel_options, $readonlys, $persist);
		}

		/**
		* Callback for nm widget
		* 
		* @param &$query Search parameters
		* @param &$rows Results
		* @param &$readonlys Widgets to set readonly
		*/
		public function get_rows(&$query, &$rows, &$readonlys) {

			// Store current filters in the session
                        $GLOBALS['egw']->session->appsession('_list', 'bookmarks', $query);

			// Selected columns
			$columselection = $GLOBALS['egw_info']['user']['preferences']['bookmarks']['nextmatch-bookmarks.list.rows'];
			if ($columselection)
			{
				$query['selectcols'] = $columselection;
				$columselection = explode(',',$columselection);
			}
			else
			{
				$columselection = $query['selectcols'] ? explode(',',$query['selectcols']) : array();
			}
			// do we need to query the cf's?
			$query['custom_fields'] = config::get_customfields('bookmarks') && (!$columselection || in_array('customfields',$columselection));

			// switch cf column off, if we have no cf's
			if (!$query['custom_fields']) $rows['no_customfields'] = true;

			$query['total'] = $this->bo->get_rows($query, $rows, $readonlys);
			
			return $query['total'];
		}

		/**
		* Get actions for nextmatch context menu
		*
		* @return array see nextmatch_widget::egw_actions()
		*/
		protected function get_actions()
		{
			$actions = array(
			'visit' => array(
				'caption' => 'Visit',
				'icon' => 'no_favicon',
				'default' => true,
				'allowOnMultiple' => false,
				'url' => 'menuaction=bookmarks.bookmarks_ui.redirect&bm_id=$id',
				'group' => $group=1,
			),
			'edit' => array(
				'caption' => 'Open',
				'allowOnMultiple' => false,
				'url' => 'menuaction=bookmarks.bookmarks_ui.edit&bm_id=$id',
				'popup' => egw_link::get_registry('bookmarks', 'add_popup'),
				'group' => $group,
				'disableClass' => 'rowNoEdit',
			),
			'add' => array(
				'caption' => 'Add',
				'url' => 'menuaction=bookmarks.bookmarks_ui.create',
				'popup' => egw_link::get_registry('bookmarks', 'add_popup'),
				'group' => $group,
			),
			'mailto' => array(
				'caption' => 'Mail',
				'allowOnMultiple' => true,
				'icon'	=> 'mail',
				'group' => $group,
			),
			'delete' => array(
				'caption' => 'Delete',
				'confirm' => 'Delete this entry',
				'confirm_multiple' => 'Delete these entries',
				'group' => ++$group,
				'disableClass' => 'rowNoDelete',
			),
			);
			return $actions;
		}

		/**
		* Display the list of bookmarks as a tree
		*
		* @param content Array of values returned from eTemplate
		*/
		function tree($content = array())
		{

			$this->location_info['returnto'] = 'tree';
			unset($this->location_info['returnto2']);
			$this->bo->save_session_data($this->location_info);

			if ($_COOKIE['menutree'])
			{
				$this->expandedcats = array_keys($_COOKIE['menutree']);
			}
			else
			{
				$this->expandedcats = Array();
			}

			$categories = (array)$this->bo->categories->return_array( 'all', 0 , false, '', '', '', true );
			$categories = (array)$this->bo->categories->return_sorted_array( 0 , false, '', 'ASC', 'cat_name', true );

			//build cat tree
			foreach ( $categories as $key => $cat ) {
				$categories[$key]['tree'] = $cat['id'];
				$parent = $cat['parent'];
				while ( $parent != 0) {
					$categories[$key]['tree'] = $parent. '/'. $categories[$key]['tree'];

					// Don't know what this does, but it can cause tree issues (like when cat name = bookmark name)
//					if($this->bo->categories->read($parent) && $this->bo->categories->check_perms(EGW_ACL_READ, $parent)) {
//						$categories[$key]['tree'] = $parent. '/'. $categories[$key]['tree'];
//					}
					// Select a nonexisting key, in case the referenced cat doesn't exist.
					$parcatkey = count($categories) + 1;
					foreach ( $categories as $ikey => $icat ) {
						if ( $icat['id'] == $parent ) {
							$parcatkey = $ikey;
							break;
						}
					}
					$parent = $categories[$parcatkey]['parent'];
				}
			}

			// buld bm tree
			foreach ( $categories as $cat ) {
				$bookmarks = array();
				$query = array(
					'cat_id'	=>	$cat['id']
				);
				$this->bo->get_rows($query, $bookmarks);
				$bm_tree[$cat['tree']] = $cat['name'];

				foreach ( (array)$bookmarks as $bm ) {
					$id = $bm['id'];

					// begin entry
					$bm_tree[$cat['tree']. '/'. $id] = array();
					$entry = &$bm_tree[$cat['tree']. '/'. $id]['label'];
				
					// Set leaf icon
					// Doesn't work because tree requires images to be in a certain directory
					//$bm_tree[$cat['tree']. '/'. $id]['image'] = $GLOBALS['egw']->common->image('bookmarks','mail');

					// mail
					$entry .= '<a class="action" href ="'.
						$GLOBALS['egw']->link( '/index.php', 'menuaction=bookmarks.bookmarks_ui.mail&bm_id='. $id ). '">'.
						html::image( 'bookmarks', 'mail', lang( 'Mail this bookmark' ) ).
						'</a>';

					// edit
					if ($this->bo->check_perms2( $bm['owner'], $bm['access'], EGW_ACL_EDIT) ) {
						$entry .= '<a class="action" href ="'.
							$GLOBALS['egw']->link( '/index.php', 'menuaction=bookmarks.bookmarks_ui.edit&bm_id='. $id ). '">'.
							html::image( 'bookmarks', 'edit', lang( 'Edit this bookmark' ) ).
							'</a>';
					}

					//view
					$entry .= '<a class="action" href ="'.
						$GLOBALS['egw']->link( '/index.php', 'menuaction=bookmarks.bookmarks_ui.view&bm_id='. $id ). '">'.
						html::image( 'bookmarks', 'view', lang( 'View this bookmark' ) ).
						'</a>';

					//redirect
					$entry .= '<a target="_new" href ="'.
						$GLOBALS['egw']->link( '/index.php', 'menuaction=bookmarks.bookmarks_ui.redirect&bm_id='. $id ). '">'.
						$bm['name']. '</a>';
				}
			}

			$sel_options['tree'] = $bm_tree;
			$values['msg'] = $this->app_messages();

			$GLOBALS['egw_info']['flags']['app_header'] = lang('Bookmarks - Tree');
			$this->templ->read('bookmarks.tree');
			$this->templ->exec('bookmarks.bookmarks_ui.tree', $values, $sel_options, $readonlys, $persist);
		}

		/**
		* View details about a bookmark
		*
		* @param $content Array of values returned from eTemplate
		*/
		function view($content = array())
		{
			if (isset($_GET['bm_id']))
			{
				$bm_id = $_GET['bm_id'];
			}
			elseif (is_array($this->location_info))
			{
				$bm_id = $this->location_info['bm_id'];
			}
			//if the user cancelled we go back to the view we came from
			if ($content['cancel'])
			{
				unset($this->location_info['returnto2']);
				$this->init();
				return;
			}
			//delete bookmark and go back to view we came from
			if ($content['delete'])
			{
				$this->bo->delete($bm_id);
				unset($this->location_info['returnto2']);
				$this->init();
				return;
			}
			if ($content['edit'])
			{
				$GLOBALS['egw']->redirect_link('/index.php', array(
					'menuaction'	=>	'bookmarks.bookmarks_ui.edit',
					'bm_id'		=>	$bm_id
				));
				return;
			}
			if ($content['edit_category'] )
			{
				$GLOBALS['egw']->redirect_link('/index.php','menuaction=preferences.uicategories.index&cats_app=bookmarks&cats_level=True&global_cats=True');
				return;
			}

			$bookmark = $this->bo->read($bm_id);
			if (!$bookmark[EGW_ACL_READ])
			{
				$this->bo->error_msg = lang('Bookmark not readable');
				unset($this->location_info['returnto2']);
				$this->init();
				return;
			}

			//store the view we are in
			$this->location_info['returnto2'] = 'view';
			$this->location_info['bm_id'] = $bm_id;
			$this->bo->save_session_data($this->location_info);

			// Set up eGW link widget
			$bookmark['link_to'] = array(
				'to_id'	=>	$bm_id,
				'to_app'=>	'bookmarks'
			);

			// Set up custom fields
			if(count(config::get_customfields('bookmarks',true)) == 0) {
				$readonly[$tabs]['custom'] = true;
			}

			// Set up history
			$bookmark['history'] = self::setup_history($bm_id);
			$sel_options['status'] = $this->bo->field2label;

			// Set template to read-only
			foreach($bookmark as $key => $value) {
				$readonlys[$key] = True;
			}
			$readonlys['customfields'] = true;
			$readonlys['link_to'] = true;
			$readonlys['edit'] = !$bookmark[EGW_ACL_EDIT];
			$readonlys['save'] = true;
			$readonlys['apply'] = true;
			$readonlys['delete'] = !$bookmark[EGW_ACL_DELETE];
			$bookmark['msg'] = $this->app_messages($this->t);
			$GLOBALS['egw_info']['flags']['app_header'] = lang('Bookmark - %1', $bookmark['stripped_name']);
			$this->templ->read('bookmarks.edit');
			$this->templ->exec('bookmarks.bookmarks_ui.view', $bookmark, $sel_options, $readonlys, $persist, 2);
		}

		/**
		* Send one or more links via email
		*
		* @param content Array of information returned from eTemplate
		*/
		function mail($content = array())
		{
			//if the user cancelled we go back to the view we came from
			if ($content['cancel'])
			{
				unset($this->location_info['returnto2']);
				$this->init();
				return;
			} 
			elseif ($content['send'])	// Send button clicked
			{
				$validate = CreateObject('bookmarks.validator');
				// Strip space and tab from anywhere in the To field
				$to = $validate->strip_space($content['to']);

				// Trim the subject
				$subject = $GLOBALS['egw']->strip_html(trim($content['subject']));

				$message = $GLOBALS['egw']->strip_html($content['message']);

				// Do we have all necessary data?
				if (empty($to) || empty($subject) || empty($message))
				{
					$this->bo->error_msg .= '<br>'.lang('Please fill out <B>To E-Mail Address</B>, <B>Subject</B>, and <B>Message</B>!');
				}
				else
				{
					// the To field may contain one or more email addresses
					// separated by commas. Check each one for proper format.
					$to_array = explode(",", $to);

					while (list($key, $val) = each($to_array))
					{
						// Is email address in the proper format?
						if (!$validate->is_email($val))
						{
							$this->bo->error_msg .= '<br>' .
								lang('To address %1 invalid. Format must be <strong>user@domain</strong> and domain must exist!',$val).
								'<br><small>'.$validate->ERROR.'</small>';
							break;
						}
					}
				}
				if (!isset ($this->bo->error_msg))
				{
					$send     =& CreateObject('phpgwapi.send');

					$from = $GLOBALS['egw_info']['user']['fullname'] . ' <'.$GLOBALS['egw_info']['user']['email'].'>';

					// send the message
					$send->msg('email',$to,$subject,$message ."\n". $this->bo->config['mail_footer'],'','','',$from);
					$this->bo->msg .= '<br>'.lang('mail-this-link message sent to %1.',$to);
				}
			}

			if (empty($subject))
			{
				$subject = lang('Found a link you might like');
			}

			if (empty($message))
			{
				if (is_array($content['bm_id']))
				{
					foreach($content['bm_id'] as $id)
					{
						$bookmark = $this->bo->read($id);
						$links[] = array(
							'name' => $bookmark['name'],
							'url'  => $bookmark['url']
						);
					}
				}
				else
				{
					$bookmark = $this->bo->read($_GET['bm_id']);
					$links[] = array(
						'name' => $bookmark['name'],
						'url'  => $bookmark['url']
					);
				}
				$message = lang('I thought you would be interested in the following link(s):')."<br />\n";
				while (list(,$link) = @each($links))
				{
					$message .= sprintf("%s - %s<br />\n",$link['name'],$link['url']);
				}
			}

			if($GLOBALS['egw_info']['user']['apps']['felamimail']) {
				$link = egw::link('/index.php',egw_link::add('felamimail',
					$GLOBALS['egw_info']['flags']['currentapp'],
					$GLOBALS['egw_info']['flags']['currentid'])+
					array(
						'preset[to]' => $to,
						'preset[subject]' => $subject,
						'preset[body]' => $message
					)
				);
				$popup = egw_link::is_popup('felamimail','add');
				list($w,$h) = explode('x',$popup);
				$action = "egw_openWindowCentered2('$link','_blank',$w,$h,'yes','$app');";
				egw_framework::set_onload($action);

				unset($this->location_info['returnto2']);
				$this->init();
				return;
			}

			$data = $content + array(
				'to' => $to,
				'subject' => $subject,
				'message' => $message
			);
			$data['msg'] = $this->app_messages();

			$GLOBALS['egw_info']['flags']['app_header'] = lang('Bookmarks - Mail');
			$this->templ->read('bookmarks.mail');
			$this->templ->exec('bookmarks.bookmarks_ui.mail', $data);
		}

		/**
		* Used when a user clicks a bookmark to record the visit
		*/
		function redirect()
		{
			$bm_id = $_GET['bm_id'];
			$bookmark = $this->bo->read($bm_id);
			$this->bo->updatetimestamp($bm_id, time());
			// dont htmlspecialchars the url (!)
			$GLOBALS['egw']->redirect(htmlspecialchars_decode($bookmark['url']));
		}

		/**
		* Export some bookmarks
		*
		* @param content Array of values returned from eTemplate
		*/
		function export($content = array())
		{
			//if the user cancelled we go back to the view we came from
			if ($content['cancel'])
			{
				unset($this->location_info['returnto2']);
				$this->init();
				return;
			} elseif ($content['export'])
			{
				#  header("Content-type: text/plain");
				header("Content-type: application/octet-stream");

				switch($content['format']) {
					case 'ns':
						header("Content-Disposition: attachment; filename=bookmarks.html");
						echo $this->bo->export($content['category'],'ns');
						break;
					case 'xbel':
						header("Content-Disposition: attachment; filename=bookmarks.xbel");
						echo $this->bo->export($content['category'],'xbel');
						break;
					default:
						$this->bo->error_msg .= '<br />' . lang('Unknown format');
						break;
				}
			}
			else
			{
				if($_GET['bm_id']) {
					$preserve['bm_id'] = explode(',', $_GET['bm_id']);
					$values['bm_count'] = count($preserve['bm_id']);
				}
				$sel_options['format'] = array(
					'ns'	=>	lang('Netscape/Mozilla'),
					'xbel'	=>	lang('XBEL')
				);

				$values['msg'] = $this->app_messages();
				$GLOBALS['egw_info']['flags']['app_header'] = lang('Bookmarks - Export');
				$this->templ->read('bookmarks.export');
				$this->templ->exec('bookmarks.bookmarks_ui.export', $values, $sel_options);
			}
		}

		/**
		* Import bookmarks
		*
		* @param content Array values from eTemplate
		*/
		function import($content = array())
		{
			//if the user cancelled we go back to the view we came from
			if ($content['cancel'])
			{
				unset($this->location_info['returnto2']);
				$this->init();
				return;
			} elseif ($content['import'])
			{
				$this->bo->import($content['file'],$content['category']);
			}

			$values['msg'] = $this->app_messages();
			$GLOBALS['egw_info']['flags']['app_header'] = lang('Bookmarks - Import');
			$this->templ->read('bookmarks.import');
			$this->templ->exec('bookmarks.bookmarks_ui.import', $values);
		}

		/**
		* Set up history widget
		*
		* @param bm_id ID of the bookmark
		*/
		protected static function setup_history($bm_id) {
			return array(
				'id'	=>	$bm_id,
				'app'	=>	'bookmarks',
				'status-widgets'	=>	array(
					'owner'	=>	'select-account'
				)
			);
		}
	}
