<?php
	/**************************************************************************\
	* eGroupWare - InfoLog                                                     *
	* http://www.egroupware.org                                                *
	* Written and copyright by Ralf Becker <RalfBecker@outdoor-training.de>    *
	* originaly based on todo written by Joseph Engo <jengo@phpgroupware.org>  *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	include_once(EGW_INCLUDE_ROOT.'/infolog/inc/class.boinfolog.inc.php');

	/**
	 * This class is the UI-layer (user interface) of InfoLog
	 *
	 * @package infolog
	 * @author Ralf Becker <RalfBecker@outdoor-training.de>
	 * @copyright (c) by Ralf Becker <RalfBecker@outdoor-training.de>
	 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
	 */
	class uiinfolog
	{
		var $public_functions = array
		(
			'index'       => True,
			'edit'        => True,
			'delete'      => True,
			'close'       => True,
			'admin'       => True,
			'hook_view'   => True,
			'writeLangFile' => True
		);
		var $icons;
		var $prefs;

		function uiinfolog( )
		{
			$this->bo =& new boinfolog();

			$this->icons = array(
				'type' => array(
					'task'      => 'task.gif',      'task_alt'      => 'Task',
					'phone'     => 'phone.gif',     'phone_alt'     => 'Phonecall',
					'note'      => 'note.gif',      'note_alt'      => 'Note',
					'confirm'   => 'confirm.gif',   'confirm_alt'   => 'Confirmation',
					'reject'    => 'reject.gif',    'reject_alt'    => 'Reject',
					'email'     => 'email.gif',     'email_alt'     => 'Email' ),
				'action' => array(
					'new'       => 'new.gif',       'new_alt'       => 'Add Sub',
					'view'      => 'view.gif',      'view_alt'      => 'View Subs',
					'parent'    => 'parent.gif',    'parent_alt'    => 'View other Subs',
					'edit'      => 'edit.gif',      'edit_alt'      => 'Edit',
					'addfile'   => 'addfile.gif',   'addfile_alt'   => 'Add a file',
					'delete'    => 'delete.gif',    'delete_alt'    => 'Delete',
					'close'     => 'done.gif',      'close_alt'     => 'Close' ),
				'status' => array(
					'billed'    => 'billed.gif',    'billed_alt'    => 'billed',
					'done'      => 'done.gif',      'done_alt'      => 'done',
					'will-call' => 'will-call.gif', 'will-call_alt' => 'will-call',
					'call'      => 'call.gif',      'call_alt'      => 'call',
					'ongoing'   => 'ongoing.gif',   'ongoing_alt'   => 'ongoing',
					'offer'     => 'offer.gif',     'offer_alt'     => 'offer' )
			);

			$this->filters = array(
				'none'             =>	'no Filter',
				'done'             =>	'done',
				'my'               =>	'responsible',
				'my-open-today'    =>	'responsible open',
				'my-open-overdue'  =>	'responsible overdue',
				'my-upcoming'      =>	'responsible upcoming',
				'own'              =>	'own',
				'own-open-today'   =>	'own open',
				'own-open-overdue' =>	'own overdue',
				'own-upcoming'     =>	'own upcoming',
				'open-today'       =>	'open',
				'open-overdue'     =>	'overdue',
				'upcoming'         =>	'upcoming'
			);

			$this->messages = array(
				'edit'    => 'InfoLog - Edit',
				'add'     => 'InfoLog - New',
				'add_sub' => 'InfoLog - New Subproject',
				'sp'      => '- Subprojects from',
				're'      => 'Re:'
			);
			$this->link = &$this->bo->link;
			
			$this->tmpl =& CreateObject('etemplate.etemplate');
			$this->html =& $this->tmpl->html;

			$this->user = $GLOBALS['egw_info']['user']['account_id'];
			
			$this->prefs =& $GLOBALS['egw_info']['user']['preferences']['infolog'];
			
			$GLOBALS['uiinfolog'] =& $this;	// make ourself availible for ExecMethod of get_rows function
		}

		function get_info($info,&$readonlys,$action='',$action_id='',$show_links=false)
		{
			if (!is_array($info))
			{
				$info = $this->bo->read($info);
			}
			$id = $info['info_id'];
			$done = $info['info_status'] == 'done' || $info['info_status'] == 'billed';
			$info['sub_class'] = $this->bo->enums['priority'][$info['info_priority']] . ($done ? '_done' : '');
			if (!$done && $info['info_enddate'] < $this->bo->user_time_now)
			{
				$info['end_class'] = 'overdue';
			}
			if (!isset($info['info_anz_subs'])) $info['info_anz_subs'] = $this->bo->anzSubs($id);
			$this->bo->link_id2from($info,$action,$action_id);	// unset from for $action:$action_id
			
			$readonlys["edit[$id]"] = !$this->bo->check_access($info,EGW_ACL_EDIT);
			$readonlys["close[$id]"] = $done || ($readonlys["edit_status[$id]"] = !($this->bo->check_access($info,EGW_ACL_EDIT) || 
				in_array($this->user, (array)$info['info_responsible'])));
			$readonlys["delete[$id]"] = !$this->bo->check_access($info,EGW_ACL_DELETE);
			$readonlys["sp[$id]"] = !$this->bo->check_access($info,EGW_ACL_ADD);
			$readonlys["view[$id]"] = $info['info_anz_subs'] < 1;
			$readonlys['view[0]'] = True;	// no parent

			if (!$show_links) $show_links = $this->prefs['show_links'];

			if ($show_links != 'none' && $show_links != 'no_describtion' && ($links = $this->link->get_links('infolog',$info['info_id'])))
			{
				foreach ($links as $link)
				{
					if ($link['link_id'] != $info['info_link_id'] &&
					    ($link['app'] != $action || $link['id'] != $action_id) &&
						($show_links == 'all' || ($show_links == 'links') === ($link['app'] != $this->link->vfs_appname)))
					{
						$info['filelinks'][] = $link;
					}
				}
			}
			$info['info_type_label'] = $this->bo->enums['type'][$info['info_type']];
			$info['info_status_label'] = $this->bo->status[$info['info_type']][$info['info_status']];

			return $info;
		}

		function save_sessiondata($values)
		{
			$for = @$values['session_for'] ? $values['session_for'] : @$this->called_by;
			//echo "<p>$for: uiinfolog::save_sessiondata(".print_r($values,True).") called_by='$this->called_by', for='$for'<br />".function_backtrace()."</p>\n";
			$GLOBALS['egw']->session->appsession($for.'session_data','infolog',array(
				'search' => $values['search'],
				'start'  => $values['start'],
				'filter' => $values['filter'],
				'filter2' => $values['filter2'],
				'cat_id' => $values['cat_id'],
				'order'  => $values['order'],
				'sort'   => $values['sort'],
				'action' => $values['action'],
				'action_id' => $values['action_id'],
				'col_filter' => $values['col_filter'],
				'session_for' => $for
			));
		}

		function read_sessiondata()
		{
			$values = $GLOBALS['egw']->session->appsession(@$this->called_by.'session_data','infolog');
			if (!@$values['session_for'] && $this->called_by)
			{
				$values['session_for'] = $this->called_by;
				$this->save_sessiondata($values);
			}
			//echo "<p>called_by='$this->called_by': uiinfolog::read_sessiondata() = ".print_r($values,True)."</p>\n";
			return $values;
		}

		function get_rows($query,&$rows,&$readonlys)
		{
			//echo "<p>uiinfolog.get_rows(start=$query[start],search='$query[search]',filter='$query[filter]',cat_id=$query[cat_id],action='$query[action]/$query[action_id]',col_filter=".print_r($query['col_filter'],True).")</p>\n";
			$this->save_sessiondata($query);

			$ids = $this->bo->search($query);
			if (!is_array($ids))
			{
				$ids = array( );
			}
			$readonlys = $rows = array();
			foreach($ids as $id => $info)
			{
				$info = $this->get_info($info,$readonlys,$query['action'],$query['action_id'],$query['filter2']);
				if (!$query['filter2'] && $this->prefs['show_links'] == 'no_describtion' ||
					$query['filter2'] == 'no_describtion')
				{
					unset($info['info_des']);
				}
				$rows[] = $info;
			}
			if ($query['no_actions']) $rows['no_actions'] = true;
			if ($query['no_times']) $rows['no_times'] = true;
			//echo "<p>readonlys = "; _debug_array($readonlys);
			//echo "rows=<pre>".print_r($rows,True)."</pre>\n";

			return $query['total'];
		}

		/**
		 * Shows the infolog list
		 *
		 * @param array $values=null etemplate content
		 * @param string $action='' if set only entries liked to that $action:$action_id are shown
		 * @param string $action_id='' if set only entries liked to that $action:$action_id are shown
		 * @param mixed $called_as=0 this is how we got called, for a hook eg. the call-params of that page containing the hook
		 * @param boolean $extra_app_header=false
		 * @param boolean $return_html=false
		 * @param string $own_referer='' this is our own referer
		 */
		function index($values = 0,$action='',$action_id='',$called_as=0,$extra_app_header=False,$return_html=False,$own_referer='')
		{
			if (is_array($values))
			{
				$called_as = $values['called_as'];
				$own_referer = $values['own_referer'];
			}
			elseif ($own_referer === '')
			{
				$own_referer = $GLOBALS['egw']->common->get_referer();
				if (strstr($own_referer,'menuaction=infolog.uiinfolog.edit'))
				{
					$own_referer = $GLOBALS['egw']->session->appsession('own_session','infolog');
				}
				else
				{
					$GLOBALS['egw']->session->appsession('own_session','infolog',$own_referer);
				}
			}
			if (!$action)
			{
				$action = $values['action'] ? $values['action'] : get_var('action',array('POST','GET'));
				$action_id = $values['action_id'] ? $values['action_id'] : get_var('action_id',array('POST','GET'));
				if (!$action)
				{
					$session = $this->read_sessiondata();
					$action = $session['action'];
					$action_id = $session['action_id'];
					unset($session);
				}
			}
			//echo "<p>uiinfolog::index(action='$action/$action_id',called_as='$called_as/$values[referer]',own_referer='$own_referer') values=\n"; _debug_array($values);
			if (!is_array($values))
			{
				$values = array('nm' => $this->read_sessiondata());
				if (isset($_GET['filter']) && $_GET['filter'] != 'default' || !isset($values['nm']['filter']))
				{
					$values['nm']['filter'] = $_GET['filter'] && $_GET['filter'] != 'default' ? $_GET['filter'] :
						$this->prefs['defaultFilter'];
				}
				if (!isset($values['nm']['order']) || !$values['nm']['order'])
				{
					$values['nm']['order'] = 'info_datemodified';
					$values['nm']['sort'] = 'DESC';
				}
				$values['msg'] = $_GET['msg'];
				$values['action'] = $action;
				$values['action_id'] = $action_id;
			}
			if ($values['nm']['add'])
			{
				$values['add'] = $values['nm']['add'];
				unset($values['nm']['add']);
			}
			if ($values['add'] || $values['cancel'] || isset($values['nm']['rows']) || isset($values['main']))
			{
				if ($values['add'])
				{
					list($type) = each($values['add']);
					return $this->edit(0,$action,$action_id,$type,$called_as);
				}
				elseif ($values['cancel'] && $own_referer)
				{
					$session = $this->read_sessiondata();
					unset($session['action']);
					unset($session['action_id']);
					$this->save_sessiondata($session);
					$this->tmpl->location($own_referer);					
				}
				else
				{
					list($do,$do_id) = isset($values['main']) ? each($values['main']) : @each($values['nm']['rows']);
					list($do_id) = @each($do_id);
					//echo "<p>infolog::index: do='$do/$do_id', referer="; _debug_array($called_as);
					switch($do)
					{
						case 'edit':
						case 'edit_status':
							return $this->edit($do_id,$action,$action_id,'',$called_as);
						case 'delete':
							if (!($values['msg'] = $this->delete($do_id,$called_as,'index'))) return;
							break;
						case 'close':
							return $this->close($do_id,$called_as,$do == 'close_subs');
						case 'sp':
							return $this->edit(0,'sp',$do_id,'',$called_as);
						case 'view':
							$value = array();
							$action = 'sp';
							$action_id = $do_id;
							break;
						default:
							$value = array();
							$action = '';
							$action_id = 0;
							break;
					}
				}
			}
			switch ($action)
			{
				case 'sp':
					if (!$this->bo->read($action_id))
					{
						$action = '';
						$action_id = 0;
						break;
					}
					$values['main'][1] = $this->get_info($action_id,$readonlys['main']);
					break;
			}
			$readonlys['cancel'] = $action != 'sp';

			$this->tmpl->read('infolog.index');

			$values['nm']['options-filter'] = $this->filters;
			$values['nm']['get_rows'] = 'infolog.uiinfolog.get_rows';
			$values['nm']['options-filter2'] = (in_array($this->prefs['show_links'],array('all','no_describtion')) ? array() : array(
				''               => 'default',
			)) + array(
				'no_describtion' => 'no details',
				'all'            => 'details',
			);
			if(!isset($values['nm']['filter2'])) $values['nm']['filter2'] = $this->prefs['show_links'];
			$values['nm']['header_right'] = 'infolog.index.header_right';
			if ($extra_app_header)
			{
				$values['nm']['header_left'] = 'infolog.index.header_left';
			}
			$values['nm']['bottom_too'] = True;
			$values['nm']['never_hide'] = isset($this->prefs['never_hide']) ? 
				$this->prefs['never_hide'] : $GLOBALS['egw_info']['user']['preferences']['common']['maxmatchs'] > 15;
			$values['nm']['no_times'] = !$this->prefs['show_times'];
			$values['action'] = $persist['action'] = $values['nm']['action'] = $action;
			$values['action_id'] = $persist['action_id'] = $values['nm']['action_id'] = $action_id;
			$persist['called_as'] = $called_as;
			$persist['own_referer'] = $own_referer;

			$all_stati = array();
			foreach($this->bo->status as $typ => $stati)
			{
				if ($typ != 'defaults') $all_stati += $stati;
			}
			$GLOBALS['egw_info']['flags']['app_header'] = lang('InfoLog');
			$GLOBALS['egw_info']['flags']['params']['manual'] = array('page' => 'ManualInfologIndex');

			return $this->tmpl->exec('infolog.uiinfolog.index',$values,array(
				'info_type'     => $this->bo->enums['type'],
				'info_status'   => $all_stati
			),$readonlys,$persist,$return_html ? -1 : 0);
		}

		function close($values=0,$referer='')
		{
			$info_id = (int) (is_array($values) ? $values['info_id'] : ($values ? $values : $_GET['info_id']));
			$referer = is_array($values) ? $values['referer'] : $referer;
			
			if ($info_id)
			{
				$values = array(
					'info_id'     => $info_id,
					'info_status' => 'done',
				);
				$this->bo->write($values);
				
				$query = array('action'=>'sp','action_id'=>$info_id);
				foreach((array)$this->bo->search($query) as $info)
				{
					if ($info['info_id_parent'] == $info_id)	// search also returns linked entries!
					{
						$this->close($info['info_id'],$referer);	// we call ourselfs recursive to process subs from subs too
					}
				}
			}
			return $referer ? $this->tmpl->location($referer) : $this->index();
		}

		function delete($values=0,$referer='',$called_by='')
		{
			$info_id = (int) (is_array($values) ? $values['info_id'] : ($values ? $values : $_GET['info_id']));
			$referer = is_array($values) ? $values['referer'] : $referer;
			
			if (!is_array($values) && $info_id > 0 && !$this->bo->anzSubs($info_id))	// entries without subs get confirmed by javascript
			{
				$values = array('delete' => true);
			}
			//echo "<p>uiinfolog::delete(".print_r($values,true).",'$referer','$called_by') info_id=$info_id</p>\n";

			if (is_array($values) || $info_id <= 0)
			{
				if (($values['delete'] || $values['delete_subs']) && $info_id > 0 && $this->bo->check_access($info_id,EGW_ACL_DELETE))
				{
					$deleted = $this->bo->delete($info_id,$values['delete_subs'],$values['info_id_parent']);
				}
				if ($called_by)		// direct call from the same request
				{
					return $deleted ? lang('InfoLog entry deleted') : '';
				}
				if ($values['called_by'] == 'edit')	// we run in the edit popup => give control back to edit
				{
					$this->edit(array(
						'info_id' => $info_id,
						'button'  => array('deleted' => true),	// not delete!
						'referer' => $referer,
						'msg'     => $deleted ? lang('Infolog entry deleted') : '',
					));
				}
				return $referer ? $this->tmpl->location($referer) : $this->index();
			}
			$readonlys = $values = array();
			$values['main'][1] = $this->get_info($info_id,$readonlys['main']);

			$this->tmpl->read('infolog.delete');

			$values['nm'] = array(
				'action'         => 'sp',
				'action_id'      => $info_id,
				'options-filter' => $this->filters,
				'get_rows'       => 'infolog.uiinfolog.get_rows',
				'no_filter2'     => True,
				'never_hide'     => isset($this->prefs['never_hide']) ? 
					$this->prefs['never_hide'] : 
					$GLOBALS['egw_info']['user']['preferences']['common']['maxmatchs'] > 15,
			);
			$values['main']['no_actions'] = $values['nm']['no_actions'] = True;

			$persist['info_id'] = $info_id;
			$persist['referer'] = $referer;
			$persist['info_id_parent'] = $values['main'][1]['info_id_parent'];				
			$persist['called_by'] = $called_by;

			$GLOBALS['egw_info']['flags']['app_header'] = lang('InfoLog').' - '.lang('Delete');
			$GLOBALS['egw_info']['flags']['params']['manual'] = array('page' => 'ManualInfologDelete');

			$this->tmpl->exec('infolog.uiinfolog.delete',$values,'',$readonlys,$persist,$called_by == 'edit' ? 2 : 0);
		}

		/**
		 * Edit/Create an InfoLog Entry
		 *
		 * @param array $content=null Content from the eTemplate Exec call or info_id on inital call
		 * @param string $action='' Name of an app of 'sp' for a infolog-sub
		 * @param int $action_id=0 Id of app-entry to which a link is created
		 * @param string $type='' Type of log-entry: note,todo,task
		 * @param string $referer='' array with param/get-vars of the refering page
		 */
		function edit($content = null,$action = '',$action_id=0,$type='',$referer='')
		{
			if (is_array($content))
			{
				//echo "uiinfolog::edit: content="; _debug_array($content);
				$info_id   = $content['info_id'];
				$action    = $content['action'];
				$action_id = $content['action_id'];
				$referer   = $content['referer'];
				$no_popup  = $content['no_popup'];
				$caller    = $content['caller'];

				if (isset($content['link_to']['primary']))
				{
					$content['info_link_id'] = $content['link_to']['primary'];
				}
				list($button) = @each($content['button']);
				unset($content['button']);
				if ($button)
				{
					//echo "<p>uiinfolog::edit(info_id=$info_id) '$button' button pressed, content="; _debug_array($content);
					if (($button == 'save' || $button == 'apply') && $info_id)
					{
						if (!($edit_acl = $this->bo->check_access($info_id,EGW_ACL_EDIT)))
						{
							$old = $this->bo->read($info_id);
							$status_only = in_array($this->user, $old['info_responsible']);
						}
					}
					if (($button == 'save' || $button == 'apply') && (!$info_id || $edit_acl || $status_only))
					{
						if (is_array($content['link_to']['to_id']) && count($content['link_to']['to_id']))
						{
							$first_link_to = $content['link_to']['to_id'];
							if (strstr($content['info_link_id'],':') !== False)
							{
								$info_link_id = $content['info_link_id'];
								$content['info_link_id'] = 0;	// as field has to be int
							}
						}
						if (!($info_id = $this->bo->write($content)))
						{
							$content['msg'] = $info_id !== 0 || !$content['info_id'] ? lang('Error: saving the entry') :
								lang('Error: the entry has been updated since you opened it for editing!').'<br />'.
								lang('Copy your changes to the clipboard, %1reload the entry%2 and merge them.','<a href="'.
									htmlspecialchars($GLOBALS['egw']->link('/index.php',array(
										'menuaction' => 'infolog.uiinfolog.edit',
										'info_id'    => $content['info_id'],
										'no_popup'   => $no_popup,
										'referer'    => $referer,
									))).'">','</a>');
							$button = '';	// not exiting edit
							$info_id = $content['info_id'];
						}
						else
						{
							$content['msg'] = lang('InfoLog entry saved');
							$content['js'] = "opener.location.href='".($link=$GLOBALS['egw']->link($referer,array('msg' => $content['msg'])))."';";
						}
						if ($info_id && $first_link_to)	// writing links for a new entry
						{
							$this->link->link('infolog',$info_id,$first_link_to);

							if ($info_link_id)
							{
								list($app,$id) = explode(':',$info_link_id);
								$link = $this->link->get_link('infolog',$info_id,$app,$id);
								$content['info_link_id'] = $link['link_id'];

								$to_write = array(
									'info_id'      => $content['info_id'],
									'info_link_id' => $content['info_link_id'],
									'info_from'    => $content['info_from'],
									'info_owner'   => $content['info_owner'],
								);
								$this->bo->write($to_write,False);
							}
						}
					}
					elseif ($button == 'delete' && $info_id > 0)
					{
						if (!$referer && $action) $referer = array(
							'menuaction' => 'infolog.uiinfolog.index',
							'action' => $action,
							'action_id' => $action_id
						);
						if (!($content['msg'] = $this->delete($info_id,$referer,'edit'))) return;	// checks ACL first

						$content['js'] = "opener.location.href='".$GLOBALS['egw']->link($referer,array('msg' => $content['msg']))."';";
					}
					// called again after delete confirmation dialog
					elseif ($button == 'deleted'  && $content['msg'])
					{
						$content['js'] = "opener.location.href='".$GLOBALS['egw']->link($referer,array('msg' => $content['msg']))."';";
					}
					if ($button == 'save' || $button == 'cancel' || $button == 'delete' || $button == 'deleted')
					{
						if ($no_popup)
						{
							$GLOBALS['egw']->redirect_link($referer,array('msg' => $content['msg']));
						}
						$content['js'] .= 'window.close();';
						echo '<html><body onload="'.$content['js'].'"></body></html>';
						$GLOBALS['egw']->common->egw_exit();
					}
					if ($content['js']) $content['js'] = '<script>'.$content['js'].'</script>';
				}
			}
			else
			{
				//echo "<p>uiinfolog::edit: info_id=$info_id,  action='$action', action_id='$action_id', type='$type', referer='$referer'</p>\n";
				$action    = $action    ? $action    : get_var('action',   array('POST','GET'));
				$action_id = $action_id ? $action_id : get_var('action_id',array('POST','GET'));
				$info_id   = $content   ? $content   : get_var('info_id',  array('POST','GET'));
				$type      = $type      ? $type      : get_var('type',     array('POST','GET'));
				$cat_id    = get_var('cat_id',array('POST','GET'),0);
				$ref=$referer   = $referer !== '' ? $referer : ($_GET['referer'] ? $_GET['referer'] :
					$GLOBALS['egw']->common->get_referer('/index.php?menuaction=infolog.uiinfolog.index'));
				$referer = preg_replace('/([&?]{1})msg=[^&]+&?/','\\1',$referer);	// remove previou/old msg from referer
				$no_popup  = $_GET['no_popup'];
				//echo "<p>uiinfolog::edit: info_id=$info_id,  action='$action', action_id='$action_id', type='$type', referer='$referer'</p>\n";

				$content = $this->bo->read( $info_id || $action != 'sp' ? $info_id : $action_id );
				
				if (is_numeric($_REQUEST['cat_id']))
				{
					$content['info_cat'] = (int) $_REQUEST['cat_id'];
				}
				$today = mktime(-$this->bo->tz_offset,0,0,date('m'),date('d'),date('Y'));	// time=00:00

				if (intval($content['info_link_id']) > 0 && !$this->link->get_link($content['info_link_id']))
				{
					$content['info_link_id'] = 0;	// link has been deleted
				}

				if (!$info_id && $action_id && $action == 'sp')    // new SubProject
				{
					if (!$this->bo->check_access($action_id,EGW_ACL_ADD))
					{
						return $referer ? $this->tmpl->location($referer) : $this->index(0,$action,$action_id);
					}
					$parent = $this->bo->so->data;
					$content['info_id'] = $info_id = 0;
					$content['info_owner'] = $this->user;
					$content['info_id_parent'] = $parent['info_id'];
					/*
					if ($parent['info_type']=='task' && $parent['info_status']=='offer')
					{
						$content['info_type'] = 'confirm';   // confirmation to parent
						$content['info_responsible'] = $parent['info_owner'];
					}
					*/
					$content['info_type'] = $parent['info_type'];
					$content['info_status'] = $this->bo->status['defaults'][$content['info_type']];
					$content['info_confirm'] = 'not';
					$content['info_subject']=lang($this->messages['re']).' '.$parent['info_subject'];
					$content['info_des'] = '';
					$content['info_lastmodified'] = '';
					if ($content['info_startdate'] < $this->bo->user_time_now)	// parent-startdate is in the past => today
					{
						$content['info_startdate'] = $today;
					}
					if ($content['info_enddate'] < $this->bo->user_time_now)		// parent-enddate is in the past => empty
					{
						$content['info_enddate'] = '';
					}
				}
				else
				{
					if ($info_id && !$this->bo->check_access($info_id,EGW_ACL_EDIT))
					{
						if (in_array($this->user, (array)$content['info_responsible']))
						{
							$content['status_only'] = True;
							foreach($content as $name => $value)
							{
								$readonlys[$name] = $name != 'info_status';
							}
							// need to set all customfields extra, as they are not set if empty
							foreach($this->bo->customfields as $name => $value)
							{
								$readonlys['#'.$name] = true;
							}
						}
						else	// Permission denied
						{
							if ($no_popup)
							{
								$GLOBALS['egw']->common->egw_header();
								parse_navbar();
								echo '<p class="redItalic" align="center">'.lang('Permission denied')."</p>\n";
								$GLOBALS['egw']->common->egw_exit();
							}
							$js = "alert('".lang('Permission denied')."'); window.close();";
							echo '<html><body onload="'.$js.'"></body></html>';
							$GLOBALS['egw']->common->egw_exit();
						}
					}
				}
				$content['links'] = $content['link_to'] = array(
					'to_id' => $info_id,
					'to_app' => 'infolog',
				);
				switch ($action)
				{
					case 'sp':
						$links = $this->bo->link->get_links('infolog',$parent['info_id'],'!'.$this->bo->link->vfs_appname);
						foreach($links as $link)
						{
							$link_id = $this->link->link('infolog',$content['link_to']['to_id'],$link['app'],$link['id'],$link['remark']);

							if ($parent['info_link_id'] == $link['link_id'])
							{
								$content['info_link_id'] = $link_id;
							}
						}
						break;

					case 'addressbook':
					case 'projects':
					case 'calendar':
					default:	// to allow other apps to participate
						$content['info_link_id'] = $this->link->link('infolog',$content['link_to']['to_id'],$action,$action_id);
						$content['blur_title']   = $this->link->title($action,$action_id);

					case '':
						if ($info_id)
						{
							break;	// normal edit
						}
					case 'new':		// new entry
						$content['info_startdate'] = (int) $_GET['startdate'] ? (int) $_GET['startdate'] : $today;
						$content['info_priority'] = 1; // normal
						$content['info_owner'] = $this->user;
						if ($type != '')
						{
							$content['info_type'] = $type;
						}
						$content['info_status'] = $this->bo->status['defaults'][$content['info_type']];
						break;
				}
				// we allways need to set a non-empty/-zero primary, to make the radiobutton appear
				$content['link_to']['primary'] = $content['info_link_id'] ? $content['info_link_id'] : '#';

				if (!isset($this->bo->enums['type'][$content['info_type']]))
				{
					$content['info_type'] = 'note';
				}
			}
			if (!($readonlys['delete'] = !$info_id || !$this->bo->check_access($info_id,EGW_ACL_DELETE)))
			{
				$content['info_anz_subs'] = $this->bo->anzSubs($info_id);	// to determine js confirmation of delete or not
			}
			$GLOBALS['egw_info']['flags']['app_header'] = lang($this->messages[$info_id ? 'edit' : ($action == 'sp' ? 'add_sub' : 'add')]);

			$this->tmpl->read('infolog.edit');
			if ($this->bo->has_customfields($content['info_type']))
			{
				$content['customfields'] = $this->bo->customfields;
				$content['customfields']['###typ###'] = $content['info_type'];
			}
			else
			{
				$readonlys['description|links|delegation|customfields'] = array('customfields' => true);
			}
			$GLOBALS['egw_info']['flags']['app_header'] = lang('InfoLog').' - '.
				($content['status_only'] ? lang('Edit Status') : lang('Edit'));
			$GLOBALS['egw_info']['flags']['params']['manual'] = array('page' => ($info_id ? 'ManualInfologEdit' : 'ManualInfologAdd'));
			//echo "<p>uiinfolog.edit(info_id='$info_id',action='$action',action_id='$action_id') readonlys="; print_r($readonlys); echo ", content = "; _debug_array($content);
			$this->tmpl->exec('infolog.uiinfolog.edit',$content,array(
				'info_type'     => $this->bo->enums['type'],
				'info_priority' => $this->bo->enums['priority'],
				'info_confirm'  => $this->bo->enums['confirm'],
				'info_status'   => $this->bo->status[$content['info_type']]
			),$readonlys,array(	// preserved values
				'info_id'       => $info_id,
				'info_id_parent'=> $content['info_id_parent'],
				'info_link_id'  => $content['info_link_id'],
				'info_owner'    => $content['info_owner'],
				'info_datemodified' => $content['info_datemodified'],
				'info_modifier' => $content['info_modifier'],
				'action'        => $action,
				'action_id'     => $action_id,
				'referer'       => $referer,
				'no_popup'      => $no_popup,
				'link_to'       => array('to_id' => $content['link_to']['to_id'])	// in case tab gets not viewed
			),$no_popup ? 0 : 2);
		}

		function menuaction($action = 'get_list',$app='infolog')
		{
			return array( 'menuaction' => "$app.ui$app.$action" );
		}

		function icon($cat,$id,$status='')
		{
			if (!$status || !($icon = $this->icons[$cat][$id.'_'.$status]))
			{
				$icon = $this->icons[$cat][$id];
			}
			if ($icon && !is_readable($GLOBALS['egw']->common->get_image_dir() . '/' . $icon))
			{
				$icon = False;
			}
			if (!$status || !($alt = $this->icons[$cat][$id.'_'.$status.'_alt']))
			{
				if (!($alt = $this->icons[$cat][$id.'_alt']))
				{
					$alt = $id;
				}
			}
			return $icon ? $this->html->image('infolog',$icon,lang($alt),'border=0') : lang($alt);
		}

		function admin( )
		{
			if(get_var('cancel',Array('POST')))
			{
				$GLOBALS['egw']->redirect_link('/admin/index.php');
			}

			if(get_var('save',Array('POST')))
			{
				$this->bo->link_pathes = $this->bo->send_file_ips = array();

				$valid = get_var('valid',Array('POST'));
				$trans = get_var('trans',Array('POST'));
				$ip = get_var('ip',Array('POST'));
				while(list($key,$val) = each($valid))
				{
					if($val = stripslashes($val))
					{
						$this->bo->link_pathes[$val]   = stripslashes($trans[$key]);
						$this->bo->send_file_ips[$val] = stripslashes($ip[$key]);
					}
				}
				$this->bo->config->config_data = array(
					'link_pathes' => $this->bo->link_pathes,
					'send_file_ips' => $this->bo->send_file_ips
				);
				$this->bo->config->save_repository(True);
			}

			$GLOBALS['egw_info']['flags']['css'] = $this->html->theme2css();
			$GLOBALS['egw_info']['flags']['app_header'] = lang('InfoLog').' - '.lang('Configuration');
			$GLOBALS['egw']->common->egw_header();

			$GLOBALS['egw']->template->set_file(array('info_admin_t' => 'admin.tpl'));
			$GLOBALS['egw']->template->set_block('info_admin_t', 'admin_line');
			$GLOBALS['egw']->template->set_block('info_admin_t', 'info_admin');

			$GLOBALS['egw']->template->set_var(Array(
				'text' => lang('<b>file-attachments via symlinks</b> instead of uploads and retrieval via file:/path for direct lan-clients'),
				'action_url'  => $this->html->link('/index.php',$this->menuaction('admin')),
				'save_button' => $this->html->submit_button('save','Save'),
				'done_button' => $this->html->submit_button('cancel','Cancel'),
				'lang_valid'  => lang('valid path on clientside<br>eg. \\\\Server\\Share or e:\\'),
				'lang_trans'  => lang('path on (web-)serverside<br>eg. /var/samba/Share'),
				'lang_ip'     => lang('reg. expr. for local IP\'s<br>eg. ^192\\.168\\.1\\.')
			));

			if (!is_array($this->bo->send_file_ips))
			{
				$this->bo->send_file_ips = $this->bo->link_pathes = array();
			}
			$i = 0; @reset($this->bo->link_pathes);
			do {
				list($valid,$trans) = @each($this->bo->link_pathes);
				$GLOBALS['egw']->template->set_var(array(
					'tr_color'  => $i & 1 ? 'row_off' : 'row_on',
					'num'       => $i+1,
					'val_valid' => $this->html->input("valid[$i]",$valid),
					'val_trans' => $this->html->input("trans[$i]",$trans),
					'val_ip'    => $this->html->input("ip[$i]",$this->bo->send_file_ips[$valid])
				));
				$GLOBALS['egw']->template->parse('admin_lines','admin_line',True);
				++$i;
			} while ($valid);

			if (!$this->tmpl->xslt)
			{
				echo parse_navbar();
				$GLOBALS['egw']->template->pfp('phpgw_body','info_admin');
			}
			else
			{
				$GLOBALS['egw']->template->fp('phpgw_body','info_admin');
			}
		}
		
		/**
		 * writes langfile with all templates and messages registered here
		 *
		 * called via [write Langfile] in the etemplate-editor or as http://domain/egroupware/index.php?menuaction=infolog.uiinfolog.writeLangFile
		 */
		function writeLangFile()
		{
			$extra = $this->messages + $this->filters;
			$enums = $this->bo->enums + $this->bo->status;
			unset($enums['defaults']);
			foreach($enums as $key => $msg_arr)
			{
				$extra += $msg_arr;
			}
			return $this->tmpl->writeLangFile('infolog','en',$extra);
		}
		
		/**
		 * shows infolog in other applications
		 *
		 * @param $args['location'] location des hooks: {addressbook|projects|calendar}_view|infolog
		 * @param $args['view']     menuaction to view, if location == 'infolog'
		 * @param $args['app']      app-name, if location == 'infolog'
		 * @param $args['view_id']  name of the id-var for location == 'infolog'
		 * @param $args[$args['view_id']] id of the entry
		 * this function can be called for any app, which should include infolog: \
		 * 	$GLOBALS['egw']->hooks->process(array( \
		 * 		 * 'location' => 'infolog', \
		 * 		 * 'app'      => <your app>, \
		 * 		 * 'view_id'  => <id name>, \
		 * 		 * <id name>  => <id value>, \
		 * 		 * 'view'     => <menuaction to view an entry in your app> \
		 * 	));
		 */
		function hook_view($args)
		{
			switch ($args['location'])
			{
				case 'addressbook_view':
					$app     = 'addressbook';
					$view_id = 'ab_id';
					$view    = 'addressbook.uiaddressbook.view';
					break;
				case 'projects_view':
					$app     = 'projects';
					$view_id = 'project_id';
					$view    = 'projects.uiprojects.view';
					break;
				case 'calendar_view':
					$app     = 'calendar';
					$view_id = 'cal_id';
					$view    = 'calendar.uicalendar.view';
					break;
				default:
					$app     = $args['app'];
					$view_id = $args['view_id'];
					$view    = $args['view'];
			}
			if (!is_array($args) || $args['debug'])
			{
				echo "<p>uiinfolog::hook_view("; print_r($args); echo "): app='$app', $view_id='$args[$view_id]', view='$view'</p>\n";
			}
			if (!isset($app) || !isset($args[$view_id]))
			{
				return False;
			}
			$this->called_by = $app;	// for read/save_sessiondata, to have different sessions for the hooks

			$save_app = $GLOBALS['egw_info']['flags']['currentapp'];
			$GLOBALS['egw_info']['flags']['currentapp'] = 'infolog';

			$GLOBALS['egw']->translation->add_app('infolog');

			$GLOBALS['egw_info']['etemplate']['hooked'] = True;
			$this->index(0,$app,$args[$view_id],array(
				'menuaction' => $view,
				$view_id     => $args[$view_id]
			),True);
			$GLOBALS['egw_info']['flags']['currentapp'] = $save_app;
			unset($GLOBALS['egw_info']['etemplate']['hooked']);
		} 
	}
