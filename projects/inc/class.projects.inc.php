<?php
  /**************************************************************************\
  * phpGroupWare - Projects                                                  *
  * http://www.phpgroupware.org                                              *
  * Written by Bettina Gille [ceb@phpgroupware.org]                          *
  * -----------------------------------------------                          *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/
  /* $Id$ */

    class projects {
	    var $db;
	    var $projects;
	    var $grants;
	    var $total_records;

	function projects() {
		global $phpgw;
		$this->db		= $phpgw->db;
		$this->total_records	= $this->db->num_rows();
		$this->grants 		= $phpgw->acl->get_grants('projects');
		$this->projects		= $this->read_projects($start, $limit, $query, $filter, $sort, $order);

	}

	function check_perms($has, $needed) {
	    return (!!($has & $needed) == True);
	}

	function read_projects( $start, $limit, $query = '', $filter = '', $sort = '', $order = '') {

	    global $phpgw, $phpgw_info, $total_records, $grants;

	    $this->db2 = $this->db;

	    if (!$sort) { $sort = "ASC";  }

	    if ($order) { $ordermethod = "order by $order $sort"; }
	    else { $ordermethod = "order by start_date asc"; }

            if (! $filter) { $filter = 'none'; }

            if ($filter != 'private') {
                if ($filter != 'none') { $filtermethod = " access like '%,$filter,%' "; }
                else {
                $filtermethod = " (coordinator=" . $phpgw_info['user']['account_id'] . " OR (access='public' ";
                    if (is_array($this->grants)) {
                        $grants = $this->grants;
                        while (list($user) = each($grants)) {
                                        $public_user_list[] = $user;
                        }
                        reset($public_user_list);
                        $filtermethod .= ' AND coordinator in(' . implode(',',$public_user_list) . ')))';
                    }
                    else {
                        $filtermethod .= '))';
                    }
                }
            }
            else {
                $filtermethod = ' coordinator=' . $phpgw_info['user']['account_id'] . ' ';
            }

	    if ($query) { $querymethod = " AND (title like '%$query%' OR num like '%$query%' OR descr like '%$query%') "; }

    	    $sql = "SELECT p.id,p.num,p.entry_date,p.start_date,p.end_date,p.coordinator,p.customer,p.status, "
                            . "p.descr,p.title,p.budget,a.account_lid,a.account_firstname,a.account_lastname FROM "
                            . "phpgw_p_projects AS p,phpgw_accounts AS a WHERE a.account_id=p.coordinator $querymethod AND $filtermethod "
                            . "$ordermethod";

	    $this->db2->query($sql,__LINE__,__FILE__);
	    $this->total_records = $this->db2->num_rows();
	    $this->db->query($sql. " " . $this->db->limit($start,$limit),__LINE__,__FILE__);

	    $i = 0;
	    while ($this->db->next_record()) {
        	$projects[$i]['id']		= $this->db->f('id');
        	$projects[$i]['number']		= $this->db->f('num');
        	$projects[$i]['access']		= $this->db->f('access');
        	$projects[$i]['category']	= $this->db->f('category');
        	$projects[$i]['entry_date']	= $this->db->f('entry_date');
        	$projects[$i]['start_date']	= $this->db->f('start_date');
        	$projects[$i]['end_date']	= $this->db->f('end_date');
        	$projects[$i]['coordinator']	= $this->db->f('coordinator');
        	$projects[$i]['customer']	= $this->db->f('customer');
        	$projects[$i]['status']		= $this->db->f('status');
        	$projects[$i]['description']	= $this->db->f('descr');
        	$projects[$i]['title']		= $this->db->f('title');
        	$projects[$i]['budget']		= $this->db->f('budget');
        	$projects[$i]['lid']		= $this->db->f('account_lid');
        	$projects[$i]['firstname']	= $this->db->f('account_firstname');
        	$projects[$i]['lastname']	= $this->db->f('account_lastname');
        	$i++;
    	    }
	    return $projects;
	}

	function select_project_list($selected = '') {
	    global $phpgw;

		$projects = $this->read_projects($start, $limit, $query, $filter, $sort, $order);

		for ($i=0;$i<count($projects);$i++) {
                    $pro_select .= '<option value="' . $projects[$i]['id'] . '"';
                        if ($projects[$i]['id'] == $selected) {
                            $pro_select .= ' selected';
                        }
                        $pro_select .= '>' . $phpgw->strip_html($projects[$i]['title']) . ' [ ' . $projects[$i]['number'] . ' ]';
                        $pro_select .=  '</option>';
                }
                return $pro_select;
	}

    }