<?php
/**************************************************************************\
* eGroupWare - ProjectManager - UI list and edit projects-elements         *
* http://www.egroupware.org                                                *
* Written and (c) 2005 by Ralf Becker <RalfBecker@outdoor-training.de>     *
* --------------------------------------------                             *
*  This program is free software; you can redistribute it and/or modify it *
*  under the terms of the GNU General Public License as published by the   *
*  Free Software Foundation; either version 2 of the License, or (at your  *
*  option) any later version.                                              *
\**************************************************************************/

/* $Id$ */

include_once(EGW_INCLUDE_ROOT.'/projectmanager/inc/class.boprojectelements.inc.php');

/**
 * ProjectManage UI: list and edit projects-elements
 *
 * @package projectmanager
 * @author RalfBecker-AT-outdoor-training.de
 * @copyright (c) 2005 by RalfBecker-AT-outdoor-training.de
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 */
class uiprojectelements extends boprojectelements  
{
	/**
	 * @var array $public_functions Functions to call via menuaction
	 */
	var $public_functions = array(
		'index' => true,
		'edit'  => true,
		'view'  => true,
	);
	/**
	 * @var etemplate-object $tpl instance of the etemplate class
	 */
	var $tpl;
	/**
	 * @var array $status_labels labels for status-filter
	 */
	var $status_labels;

	/**
	 * Constructor, calls the constructor of the extended class
	 */
	function uiprojectelements()
	{
		$this->tpl =& CreateObject('etemplate.etemplate');

		if ((int) $_REQUEST['pm_id'])
		{
			$pm_id = (int) $_REQUEST['pm_id'];
			// store the current project (only for index, as popups may be called by other parent-projects)
		}
		else
		{
			$pm_id = $GLOBALS['egw']->session->appsession('pm_id','projectmanager');
		}
		if (!$pm_id)
		{
			$this->tpl->location(array(
				'menuaction' => 'projectmanager.uiprojectmanager.index',
				'msg'        => lang('You need to select a project first'),
			));
		}
		$this->boprojectelements($pm_id);
	
		// check if we have at least read-access to this project
		if (!$this->project->check_acl(EGW_ACL_READ))
		{
			$this->tpl->location(array(
				'menuaction' => 'projectmanager.uiprojectmanager.index',
				'msg'        => lang('Permission denied !!!'),
			));
		}
		
		$this->status_labels = array(
			'all'     => lang('all'),
			'used'    => lang('used'),
			'new'     => lang('new'),
			'ignored' => lang('ignored'),
		);
	}
	
	
	/**
	 * View a project-element, just calls edit with view-param set
	 */
	function view()
	{
		$this->edit(null,true);
	}
	
	/**
	 * Edit or view a project-element
	 *
	 * @var array $content content-array if called by process-exec
	 * @var boolean $view only view project, default false, only used on first call !is_array($content)
	 */
	function edit($content=null,$view=false)
	{
		if (is_array($content))
		{
			//_debug_array($content);
			$this->data = $content['data'];
			$update_necessary = $save_necessary = 0;
			$datasource = $this->datasource($this->data['pe_app']);
			$ds = $datasource->read($this->data['pe_app_id']);
			if (!$content['view'])
			{
				if ($content['pe_completion'] !== '') $content['pe_completion'] .= '%';

				foreach($datasource->name2id as $name => $id)
				{
					//echo "checking $name=$id<br>\n";
					// check if update is necessary, because a field has be set or changed
					if ($content[$name] && ($content[$name] != $this->data[$name] || !($this->data['pe_overwrite'] & $id)))
					{
						//echo "need to update $name as content[$name] changed to '".$content[$name]."' != '".$this->data[$name]."'<br>\n";
						$this->data[$name] = $content[$name];
						$this->data['pe_overwrite'] |= $id;
						$update_necessary |= $id;
					}
					// check if a field is no longer set, or it's not set and datasource changed 
					// => set it from the datasource
					elseif (($this->data['pe_overwrite'] & $id) && !$content[$name] ||
						    !($this->data['pe_overwrite'] & $id) && (int)$this->data[$name] != (int)$ds[$name])
					{
						//echo "need to update $name as content[$name] is unset or datasource changed cont='".$content[$name]."', data='".$this->data[$name]."', ds='".$ds[$name]."'<br>\n";
						// if we have a change in the datasource, set pe_synced
						if ($this->data[$name] != $ds[$name])
						{
							$this->data['pe_synced'] = $this->now_su;
						}
						$this->data[$name] = $ds[$name];
						$this->data['pe_overwrite'] &= ~$id;
						$update_necessary |= $id;
					}
				}
				$content['cat_id'] = (int) $content['cat_id'];	// as All='' and cat_id column is int
				
				// calculate the new summary and if a percentage give the share in hours
				$this->project_summary['pe_total_shares'] -= round(60 * ($content['old_pe_share'] ? $content['old_pe_share'] : $this->data['pe_default_share']));
				if (substr($content['pe_share'],-1) == '%')
				{
					$content['pe_share'] = round($this->project_summary['pe_total_shares'] * (float) $content['pe_share'] / (100 - (float) $content['pe_share']) / 60.0,1);
				}
				$this->project_summary['pe_total_shares'] += round(60 * ($content['pe_share'] ? $content['pe_share'] : $this->data['pe_default_share']));

				foreach(array('pe_status','cat_id','pe_remark','pe_constraints','pe_share') as $name)
				{
					if ($content[$name] != $this->data[$name])
					{
						//echo "need to update $name as content[$name] changed to '".$content[$name]."' != '".$this->data[$name]."'<br>\n";
						$this->data[$name] = $content[$name];
						$save_necessary = true;

						if ($name == 'pe_remark') $this->data['update_remark'] = true;
					}
				}
			}
			//echo "uiprojectelements::edit(): save_necessary=".(int)$save_necessary.", update_necessary=$update_necessary, data="; _debug_array($this->data);

			$view = $content['view'] && !($content['edit'] && $this->check_acl(EGW_ACL_EDIT));

			if (($content['save'] || $content['apply']) && $this->check_acl(EGW_ACL_EDIT))
			{
				if ($update_necessary || $save_necessary)
				{
					if ($this->save(null,true,$update_necessary) != 0)
					{
						$msg = lang('Error: saving the project-element (%1) !!!',$this->db->Error);
						unset($content['save']);	// dont exit
					}
					else
					{
						$msg = lang('Project-Element saved');
						$js = "opener.location.href='".$GLOBALS['phpgw']->link('/index.php',array(
							'menuaction' => $content['caller'],//'projectmanager.uiprojectelements.index',
							'msg'        => $msg,
						))."';";
					}
				}
				else
				{
					$msg = lang('no save necessary');
				}
			}
			if ($content['delete'] && $this->check_acl(EGW_ACL_DELETE))
			{
				// all delete are done by index
				$js = "opener.location.href='".$GLOBALS['phpgw']->link('/index.php',array(
					'menuaction' => $content['caller'],//'projectmanager.uiprojectelements.index',
					'delete'     => $this->data['pe_id'],
				))."';";
				/*
				return $this->index(array('nm'=>array('rows'=>array(
					'delete' => array($this->data['pe_id']=>true)
				))));
				*/
			}
			if ($content['save'] || $content['cancel'] || $content['delete'])
			{
				$js .= 'window.close();';
				echo '<html><body onload="'.$js.'"></body></html>';
				$GLOBALS['egw']->common->egw_exit();
				/*
				$this->tpl->location(array(
					'menuaction' => 'projectmanager.uiprojectelements.index',
					'msg'        => $msg,
				));
				*/
			}
		}
		else
		{
			if ((int) $_GET['pe_id'])
			{
				$this->read((int) $_GET['pe_id']);
			}
			if ($this->data['pe_id'])
			{
				if (!$this->check_acl(EGW_ACL_READ))
				{
					$this->tpl->location(array(
						'menuaction' => 'projectmanager.uiprojectelements.index',
						'msg' => lang('Permission denied !!!'),
					));
				}
				if (!$this->check_acl(EGW_ACL_EDIT)) $view = true;
			}
			$datasource = $this->datasource($this->data['pe_app']);
			$js = 'window.focus();';
		}
		$preserv = $this->data + array(
			'view' => $view,
			'data' => $this->data,
			'caller' => !$content['caller'] && preg_match('/menuaction=([^&]+)/',$_SERVER['HTTP_REFERER'],$matches) ?
				 $matches[1] : $content['caller'],
			'old_pe_share' => $this->data['pe_share'],
		);
		foreach($datasource->name2id as $name => $id)
		{
			if (!($this->data['pe_overwrite'] & $id)) 	// empty not explicitly set values
			{
				$this->data[$name] = '';
			}
		}
		$content = $this->data + array(
			'ds'  => $ds ? $ds : $datasource->read($this->data['pe_app_id']),
			'msg' => $msg,
			'js'  => '<script>'.$js.'</script>',
			'default_share' => round(($share = $this->data['pe_planned_time'] ? $this->data['pe_planned_time']/60 : $this->default_share) / 60,1).lang('h'),
		);
		// calculate percentual shares
		if ($this->project_summary['pe_total_shares'])
		{
			if ($this->data['pe_share'])
			{
				$content['share_percentage'] = lang('h') . '/' . round($this->project_summary['pe_total_shares']/60,1).lang('h').' = '.
					round(100.0 * 60*$this->data['pe_share'] / $this->project_summary['pe_total_shares'],1) . '%';
			}
			$content['default_share'] .= '/' . round(($this->project_summary['pe_total_shares']-60.0*(float)$content['pe_share']+$share)/60,1).lang('h').' = '.
				round(100.0 * $share / $this->project_summary['pe_total_shares'],1) . '%';
		}
		//_debug_array($content);
		$sel_options = array(
			'pe_constraints' => $this->titles(array(	// only titles of elements displayed in a gantchart
				"pe_status != 'ignore'",
				'(pe_planned_start IS NOT NULL OR pe_real_start IS NOT NULL)',
				'(pe_planned_end IS NOT NULL OR pe_real_end IS NOT NULL)',
				'pe_id != '.(int)$this->data['pe_id'],	// dont show own title	
			)),
			'milestone'     => $this->milestones->titles(array('pm_id' => $this->data['pm_id'])), 
		);
		$readonlys = array(
			'delete' => !$this->data['pe_id'] || !$this->check_acl(EGW_ACL_DELETE),
			'edit' => !$view || !$this->check_acl(EGW_ACL_EDIT),
		);
		// check if user has the necessary rights to view or edit the budget
		$readonlys['dates|times|budget|constraints']['budget'] = !$this->check_acl(EGW_ACL_BUDGET);
		$readonlys['pe_planned_budget'] = $readonlys['pe_used_budget'] = $readonlys['pe_activity_id'] = 
			$readonlys['pe_cost_per_time'] = !$this->check_acl(EGW_ACL_EDIT_BUDGET);

		if ($view)
		{
			foreach($this->db_cols as $name)
			{
				$readonlys[$name] = true;
			}
			$readonlys['pe_remark'] = true;
			$readonlys['save'] = $readonlys['apply'] = true;
			$readonlys['pe_constraints[start]'] = $readonlys['pe_constraints[end]'] = $readonlys['pe_constraints[milestone]'] = true;
		}
		$GLOBALS['egw_info']['flags']['app_header'] = lang('projectmanager') . ' - ' . 
			($this->data['pm_id'] ? ($view ? lang('View project-elements') : lang('Edit project-elements')) : lang('Add project-elements'));
		$this->tpl->read('projectmanager.elements.edit');
		$this->tpl->exec('projectmanager.uiprojectelements.edit',$content,$sel_options,$readonlys,$preserv,2);
	}

	/**
	 * query projects for nextmatch in the projects-list
	 *
	 * reimplemented from so_sql to disable action-buttons based on the acl and make some modification on the data
	 *
	 * @param array $query
	 * @param array &$rows returned rows/cups
	 * @param array &$readonlys eg. to disable buttons based on acl
	 */
	function get_rows($query,&$rows,&$readonlys)
	{
		$GLOBALS['phpgw']->session->appsession('projectelements_list','projectmanager',$query);
	
		if ($this->status_filter[$query['filter']])
		{
			$query['col_filter']['pe_status'] = $this->status_filter[$query['filter']];
		}
		else
		{
			unset($query['col_filter']['pe_status']);
		}
		if ($query['cat_id'])
		{
			$query['col_filter']['cat_id'] = $query['cat_id'];
		}
		$total = parent::get_rows($query,$rows,$readonlys,true);
		
		// adding the project itself always as first line
		$self = $this->update('projectmanager',$this->pm_id);
		$self['pe_app']    = 'projectmanager';
		$self['pe_app_id'] = $this->pm_id;
		$self['pe_icon']   = 'projectmanager/navbar';
		$self['pe_modified'] = $this->project->data['pm_modified'];
		$self['pe_modifier'] = $this->project->data['pm_modifier'];
		$rows = array_merge(array($self),$rows);
		
		$readonlys = array();
		foreach($rows as $n => $val)
		{
			$row =& $rows[$n];
			if ($n && !$this->check_acl(EGW_ACL_EDIT,$row))
			{
				$readonlys["edit[$row[pe_id]]"] = true;
			}
			if ($n && !$this->check_acl(EGW_ACL_DELETE,$row))
			{
				$readonlys["delete[$row[pe_id]]"] = true;
			}
			if (!$n)
			{
				// no link for own project
				if (!$this->project->check_acl(EGW_ACL_EDIT,$this->project->data))
				{
					$readonlys['edit'] = true;
				}
			}
			elseif ($row['pe_app'] == 'projectmanager')
			{
				// for projectmanager entries link to "their" elements list
				$row['view_link'] = array(
					'menuaction' => 'projectmanager.uiprojectelements.index',
					'pm_id'      => $row['pe_app_id'],
				);
				$row['view_help'] = lang("Select this project and show it's elements");
			}
			else
			{
				$row['view_link'] = $this->link->view($row['pe_app'],$row['pe_app_id']);
				$row['view_help'] = lang('View this element in %1',lang($row['pe_app']));
			}
		}
		$rows['no_budget'] = !$this->project->check_acl(EGW_ACL_BUDGET);

		if ($this->debug)
		{
			echo "<p>uiprojectelements::get_rows(".print_r($query,true).") rows ="; _debug_array($rows);
			_debug_array($readonlys);
		}
		return $total;		
	}

	/**
	 * List existing projects-elements
	 *
	 * @param array $content=null
	 * @param string $msg=''
	 */
	function index($content=null,$msg='')
	{
		// store the current project (only for index, as popups may be called by other parent-projects)
		$GLOBALS['egw']->session->appsession('pm_id','projectmanager',$this->project->data['pm_id']);

		if ($_GET['msg']) $msg = $_GET['msg'];

		if ($content['nm']['rows']['edit'])
		{
			$this->tpl->location(array(
				'menuaction' => 'projectmanager.uiprojectmanager.edit',
				'pm_id'      => $this->pm_id,
			));
		}
		elseif ($content['sync_all'] && $this->project->check_acl(EGW_ACL_ADD))
		{
			$msg = lang('%1 element(s) updated',$this->sync_all());
		}
		elseif ($content['nm']['add'] && $this->project->check_acl(EGW_ACL_ADD) && 
			($param = $this->link->add($content['nm']['add_app'],'projectmanager',$this->pm_id)))
		{
			$this->tpl->location($param);
		}
		elseif((int) $_GET['delete'] || $content['nm']['rows']['delete'])
		{
			if ($content['nm']['rows']['delete'])
			{
				list($pe_id) = each($content['nm']['rows']['delete']);
			}
			else
			{
				$pe_id = (int) $_GET['delete'];
			}
			if ($this->read($pe_id) && !$this->check_acl(EGW_ACL_DELETE))
			{
				$msg = lang('Permission denied !!!');
			}
			else
			{
				$msg = $this->delete($pe_id) ? lang('Project-Element deleted') : 
					lang('Error: deleting project-element !!!');
			}
		}
		$content = array(
			'nm' => $GLOBALS['phpgw']->session->appsession('projectelements_list','projectmanager'),
			'msg'      => $msg,
		);
		$sel_options = array(
			'add_app' => $this->link->app_list('add'),
		);
		if (!is_array($content['nm']))
		{
			$content['nm'] = array(
				'get_rows'       =>	'projectmanager.uiprojectelements.get_rows',
				'filter'         => 'used',// I initial value for the filter
				'filter_label'   => lang('Filter'),// I  label for filter    (optional)
				'options-filter' => $this->status_labels,
				'filter_no_lang' => True,// I  set no_lang for filter (=dont translate the options)
				'no_filter2'     => True,// I  disable the 2. filter (params are the same as for filter)
//				'bottom_too'     => True,// I  show the nextmatch-line (arrows, filters, search, ...) again after the rows
				'order'          =>	'pe_modified',// IO name of the column to sort after (optional for the sortheaders)
				'sort'           =>	'DESC',// IO direction of the sort: 'ASC' or 'DESC'
			);
		}
		// add "buttons" only with add-rights
		if ($this->project->check_acl(EGW_ACL_ADD))
		{
			$content['nm']['header_right'] = 'projectmanager.elements.list.add';
			$content['nm']['header_left']  = 'projectmanager.elements.list.add-new';
		}
		else
		{
			unset($content['nm']['header_right']);
			unset($content['nm']['header_left']);
			$readonlys['sync_all'] = true;
		}
		$content['nm']['link_to'] = array(
			'to_id'    => $this->pm_id,
			'to_app'   => 'projectmanager',
			'no_files' => true,
			'search_label' => 'Add existing',
			'link_label'   => 'Add',
		);			
		$GLOBALS['phpgw_info']['flags']['app_header'] = lang('projectmanager').' - '.lang('Elementlist') .
			': ' . $this->project->data['pm_number'] . ': ' .$this->project->data['pm_title'] ;
		$this->tpl->read('projectmanager.elements.list');
		$this->tpl->exec('projectmanager.uiprojectelements.index',$content,$sel_options,$readonlys);
	}
}