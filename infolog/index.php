<?php
	/**************************************************************************\
	* phpGroupWare - Info Log                                                  *
	* http://www.phpgroupware.org                                              *
	* Written by Ralf Becker <RalfBecker@outdoor-training.de>                  *
	* originaly based on todo written by Joseph Engo <jengo@phpgroupware.org>  *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	$phpgw_info['flags'] = array(
		'currentapp'              => 'infolog', 
		'enable_nextmatchs_class' => True,
		'enable_categories_class' => True
	);
	include('../header.inc.php');

	$phpgw->infolog = createobject('infolog.infolog');
	$db = $phpgw->db;
	$db2 = $phpgw->db;

	$t = CreateObject('phpgwapi.Template',PHPGW_APP_TPL); 
	$t->set_file(array( 'info_list_t' => 'list.tpl' ));
	$t->set_block('info_list_t','info_list','list');

	$common_hidden_vars =
			'<input type="hidden" name="sort" value="' . $sort . '">'
		 . '<input type="hidden" name="order" value="' . $order . '">'
		 . '<input type="hidden" name="query" value="' . $query . '">'
		 . '<input type="hidden" name="start" value="' . $start . '">'
		 . '<input type="hidden" name="filter" value="' . $filter . '">'
		 . '<input type="hidden" name="cat_id" value="' . $cat_id . '">';
 
	if ($action)
		$common_hidden_vars	.= '<input type="hidden" name="action" value="'.$action.'">';
	
	switch ($action) {
		case 'sp':		// Sub-List
			$common_hidden_vars	.= '<input type="hidden" name="info_id" value="' . $info_id . '">';
			$t->set_var(lang_info_action,lang('Info Log - Subprojects from'));
			break;
		case 'proj':
			$common_hidden_vars	.= '<input type="hidden" name="proj_id" value="' . $proj_id . '">';
			$proj = $phpgw->infolog->readProj($proj_id);
			$t->set_var(lang_info_action,lang('Info Log').' - '.$proj['title']);
			break;
		case 'addr':
			$common_hidden_vars	.= '<input type="hidden" name="addr_id" value="' . $addr_id . '">';
			$addr = $phpgw->infolog->readAddr($addr_id);
			$t->set_var(lang_info_action,lang('Info Log').' - '.$phpgw->infolog->addr2name($addr));
			break;
		default:
			$t->set_var(lang_info_action,lang('Info Log'));
			break;
	}	
	$t->set_var($phpgw->infolog->setStyleSheet( ));
	$t->set_var(actionurl,$phpgw->link('/infolog/edit.php','action=new'));
	$t->set_var('cat_form',$phpgw->link('/infolog/index.php'));
	$t->set_var('lang_category',lang('Category'));
	$t->set_var('lang_all',lang('All'));
	$t->set_var('lang_select',lang('Select'));
	$t->set_var('categories',$phpgw->categories->formated_list('select','all',$cat_id,'True'));
	$t->set_var(common_hidden_vars,$common_hidden_vars);

	// ===========================================
	// list header variable template-declarations
	// ===========================================
	$t->set_var( $phpgw->infolog->infoHeaders( 1,$sort,$order ));
	$t->set_var(h_lang_sub,lang('Sub'));
	$t->set_var(h_lang_action,lang('Action'));
	// -------------- end header declaration -----------------

	if (! $start) {
		$start = 0;
	}

	if ($order) {
		$ordermethod = 'order by ' . $order . ' ' . $sort;
	} else {
		$ordermethod = 'order by info_datecreated desc';		// newest first
	}
	if (!$filter) 	{
		$filter = 'none';
	}
	$filtermethod = $phpgw->infolog->aclFilter($filter);
	
	if ($cat_id) {
		$filtermethod .= " AND info_cat='$cat_id' "; 
	}
	if ($action == 'addr') $filtermethod .= " AND info_addr_id=$addr_id ";
	if ($action == 'proj') $filtermethod .= " AND info_proj_id=$proj_id ";
														// we search in _from, _subject and _des for $query
	if ($query) $sql_query = "AND (info_from like '%$query%' OR info_subject like '%$query%' OR info_des like '%$query%') ";

	$pid = 'AND info_id_parent='.($action == 'sp' ? $info_id : 0);  
	if ($phpgw->infolog->listChilds && $action != 'sp')
	   $pid = '';
	
	$db->query("SELECT COUNT(*) FROM phpgw_infolog WHERE $filtermethod $pid $sql_query",__LINE__,__FILE__);
	$db->next_record();
	$total = $db->f(0);

	if ($total <= $start) $start = 0;
	
	if ($total > $phpgw_info['user']['preferences']['common']['maxmatchs']) {
		$to = $start + $phpgw_info['user']['preferences']['common']['maxmatchs']; if ($to > $total) $to = $total;
		$total_matchs = lang('showing x - x of x',($start + 1),$to,$total);
	} else {
		$total_matchs = lang('showing x',$total);
	}
  	$t->set_var('total_matchs',$total_matchs);

	// ==========================================
	// project description if subprojectlist
	// ==========================================


	$t->set_block('info_list_t','projdetails','projdetailshandle');
	
	switch ($action) {
		case 'sp':		// details of parent
			$t->set_var( $phpgw->infolog->infoHeaders(  ));
			$t->set_var( $phpgw->infolog->formatInfo( $info_id ));
			$t->parse('projdetailshandle','projdetails',True);
			break;
		case 'addr':
			break;
		case 'proj':
			break;
	}

	// ===========================================
	// nextmatch variable template-declarations
	// ===========================================
	$next_matchs = $phpgw->nextmatchs->show_tpl('/infolog/index.php',$start,$total,
						 "&order=$order&filter=$filter&sort=$sort&query=$query&action=$action&info_id=$info_id&cat_id=$cat_id",
						 '95%',$phpgw_info['theme']['th_bg']);
	$t->set_var(next_matchs,$next_matchs);
	// ---------- end nextmatch template --------------------

	$limit = $db->limit($start);

	$db->query($q="SELECT * FROM phpgw_infolog WHERE $filtermethod $pid $sql_query $ordermethod $limit",__LINE__,__FILE__);
	
	while ($db->next_record()) {
		// ========================================
		// check if actual project has subprojects
		// ========================================
		$db2->query("select count(*) as cnt FROM phpgw_infolog where info_id_parent=" .$db->f('info_id'),__LINE__,__FILE__);
		$db2->next_record();
		if ($db2->f('cnt') > 0) {
			$subproact = 1;
		} else {
			$subproact = 0;
		}
		// -----------------------------------------

		$phpgw->nextmatchs->template_alternate_row_color(&$t);

		$t->set_var( $phpgw->infolog->formatInfo( $db->Record,$proj_id,$addr_id ));

		if ($phpgw->infolog->check_access($db->f('info_id'),PHPGW_ACL_EDIT)) {
			$t->set_var('edit','<a href="' . $phpgw->link('/infolog/edit.php','info_id=' . $db->f('info_id')
				. '&sort=' . $sort . '&order=' . $order . '&query=' . $query . '&start=' . $start . '&filter=' . $filter)
				. '">' . $phpgw->infolog->icon('action','edit') . '</a>');
		} else {
			$t->set_var('edit','');
		}

		if ($phpgw->infolog->check_access($db->f('info_id'),PHPGW_ACL_DELETE)) {
			$t->set_var('delete','<a href="' . $phpgw->link('/infolog/delete.php','info_id=' . $db->f('info_id')
				. '&sort=' . $sort . '&order=' . $order . '&query=' . $query . '&start=' . $start . '&filter=' . $filter)
				. '">' . $phpgw->infolog->icon('action','delete') . '</a>');
		} else {
			$t->set_var('delete','');
		}
		$t->set_var('subadd', '');		// defaults no icons
		$t->set_var('viewsub', '');
		$t->set_var('viewparent', '');

		if ($subproact > 0) {	// if subprojects exist, display VIEW SUB icon
			$t->set_var('viewsub', '<a href="' . $phpgw->link('/infolog/index.php','info_id=' . $db->f('info_id')
					. "&filter=$filter&action=sp") . '">' . $phpgw->infolog->icon('action','view') . '</a>');
		} else {			  			// else display ADD SUB-Icon
			if ($phpgw->infolog->check_access($db->f('info_id'),PHPGW_ACL_ADD)) {
				 $t->set_var('subadd', '<a href="' . $phpgw->link('/infolog/edit.php','info_id=' . $db->f('info_id') .
											  '&filter=' . $filter . '&action=sp') . '">' . $phpgw->infolog->icon('action','new') . '</a>');
			}			
      }	 							// if parent --> display VIEW SUBS of Parent
		if ($db->f('info_id_parent') && $action != 'sp') {
			$t->set_var('viewparent', '<a href="' . $phpgw->link('/infolog/index.php','info_id=' . $db->f('info_id_parent') .
					"&filter=$filter&action=sp") . '">' . $phpgw->infolog->icon('action','parent') . '</a>');
		}
		
		$t->parse('list','info_list',True);
		// -------------- end record declaration ------------------------
	}

	// =========================================================
	// back2project list href declaration for subproject list
	// =========================================================
      
	if ($action) {
		$t->set_var('lang_back2projects', '<br><a href="' . 
											$phpgw->link('/infolog/index.php',"filter=$filter").
											'">'.lang('Back to Projectlist').'</a>');
	}

	// get actual date and year for matrixview arguments
/*	$year = date('Y');
	$month = date('m');
	$t->set_var('lang_matrixviewhref', '<br><a href="' . $phpgw->link('/infolog/graphview.php',"month=$month&year=$year&filter=$filter").
																	 '">'.lang('View Matrix of actual Month').'</a>'); */
	// ============================================
	// template declaration for Add Form
	// ============================================

	$t->set_var(lang_add,lang('Add'));
	$t->pfp('out','info_list_t',true);

	// -------------- end Add form declaration ------------------------

	$phpgw->common->phpgw_footer();
?>
