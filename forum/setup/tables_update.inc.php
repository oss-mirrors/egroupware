<?php
	/**************************************************************************\
	* phpGroupWare - Setup                                                     *
	* http://www.phpgroupware.org                                              *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	$test[] = '0.9.13';
	function forum_upgrade0_9_13()
	{
		global $setup_info, $phpgw_setup;

		$phpgw_setup->oProc->RenameTable('f_body','phpgw_forum_body');
		$phpgw_setup->oProc->RenameTable('f_categories','phpgw_forum_categories');
		$phpgw_setup->oProc->RenameTable('f_forums','phpgw_forum_forums');
		$phpgw_setup->oProc->RenameTable('f_threads','phpgw_forum_threads');

		$setup_info['forum']['currentver'] = '0.9.13.001';
		return $setup_info['forum']['currentver'];
	}

	$test[] = '0.9.13.001';
	function forum_upgrade0_9_13_001()
	{
		// If for some odd reason this fields are blank, the upgrade will fail without these
		$GLOBALS['phpgw_setup']->db->query("update phpgw_forum_threads set subject=' ' where subject=''",__LINE__,__FILE__);
		$GLOBALS['phpgw_setup']->db->query("update phpgw_forum_threads set host=' ' where host=''",__LINE__,__FILE__);

/*
		$GLOBALS['phpgw_setup']->db->query("select * from phpgw_forum_threads",__LINE__,__FILE__);
		while ($GLOBALS['phpgw_setup']->db->next_record())
		{
			$data[$GLOBALS['phpgw_setup']->db->f('id')] = $GLOBALS['phpgw_setup']->db->f('postdate');
		}
		$GLOBALS['phpgw_setup']->db->query("update phpgw_forum_threads set postdate=''",__LINE__,__FILE__);
*/

		$GLOBALS['phpgw_setup']->oProc->AlterColumn('phpgw_forum_threads','postdate',array('type' => 'timestamp','nullable' => False,'default' => 'current_timestamp'));

/*		if (is_array($data))
		{
			reset($data);

			while (list($id,$date) = each($data))
			{
				$GLOBALS['phpgw_setup']->db->query("update phpgw_forum_threads set postdate='$date' where id='$id'",__LINE__,__FILE__);
			}
		} */

		$GLOBALS['setup_info']['forum']['currentver'] = '0.9.13.002';
		return $GLOBALS['setup_info']['forum']['currentver'];
	}
















/*
	$test[] = '0.9.13.001';
	function forum_upgrade0_9_13_001()
	{
		global $setup_info, $phpgw_setup;

		$GLOBALS['phpgw_setup']->oProc->RenameTable('phpgw_forum_threads','phpgw_forum_threads_old');
		$GLOBALS['phpgw_setup']->oProc->RenameTable('phpgw_forum_body','phpgw_forum_body_old');

		if ($GLOBALS['phpgw_setup']->oProc->Type == 'pgsql')
		{
			$GLOBALS['phpgw_setup']->oProc->DropSequenceForTable('phpgw_forum_threads');
			$GLOBALS['phpgw_setup']->oProc->DropSequenceForTable('phpgw_forum_body');
		}

		$GLOBALS['phpgw_setup']->oProc->CreateTable('phpgw_forum_threads',array(
			'fd' => array(
				'id' => array('type' => 'auto','nullable' => False),
				'postdate' => array('type' => 'timestamp','nullable' => False,'default' => 'current_timestamp'),
				'main' => array('type' => 'int', 'precision' => 8,'nullable' => False),
				'parent' => array('type' => 'int', 'precision' => 8,'nullable' => False),
				'cat_id' => array('type' => 'int', 'precision' => 8,'nullable' => False),
				'for_id' => array('type' => 'int', 'precision' => 8,'nullable' => False),
				'author' => array('type' => 'varchar', 'precision' => 255,'nullable' => False),
				'subject' => array('type' => 'varchar', 'precision' => 255,'nullable' => False),
				'email' => array('type' => 'varchar', 'precision' => 255,'nullable' => False),
				'host' => array('type' => 'varchar', 'precision' => 255,'nullable' => False),
				'stat' => array('type' => 'int', 'precision' => 2,'nullable' => False),
				'thread' => array('type' => 'int', 'precision' => 8,'nullable' => False),
				'depth' => array('type' => 'int', 'precision' => 8,'nullable' => False),
				'pos' => array('type' => 'int', 'precision' => 8,'nullable' => False),
				'n_replies' => array('type' => 'int', 'precision' => 8,'nullable' => False)
			),
			'pk' => array('id'),
			'ix' => array(),
			'fk' => array(),
			'uc' => array()
		));

		$i = 0;

		$db1 = $GLOBALS['phpgw_setup']->db;
		$db2 = $GLOBALS['phpgw_setup']->db;
		$db3 = $GLOBALS['phpgw_setup']->db;

		$db1->query("select * from phpgw_forum_threads_old",__LINE__,__FILE__);
		while ($db1->next_record())
		{
//			echo '<br>Loop number: ' . ++$i . ' -> inserting subject: ' . $db1->f('subject');

			$db2->query("insert into phpgw_forum_threads (postdate,main,parent,cat_id,for_id,author,subject,"
				. "email,host,stat,thread,depth,pos,n_replies) values ('"
				. $db1->f('postdate') . "','"
				. $db1->f('main') . "','"
				. $db1->f('parent') . "','"
				. $db1->f('cat_id') . "','"
				. $db1->f('for_id') . "','"
				. $db1->f('author') . "','"
				. $db1->f('subject') . "','"
				. $db1->f('email') . "','"
				. $db1->f('host') . "','"
				. $db1->f('stat') . "','"
				. $db1->f('thread') . "','"
				. $db1->f('depth') . "','"
				. $db1->f('pos') . "','"
				. $db1->f('n_replies') . "')",__LINE__,__FILE__);

			$db2->query("select max(id) from phpgw_forum_threads",__LINE__,__FILE__);
			$db2->next_record();
			$last_id = $db2->f(0);

			$db2->query("select * from phpgw_forum_body_old where id='" . $db1->f('id') . "'",__LINE__,__FILE__);
			$db2->next_record();

			$db3->query("insert into phpgw_forum_body (cat_id,for_id,message) values ('"
				. $last_id . "','" . $db1->
		}
		$GLOBALS['phpgw_setup']->oProc->DropTable('phpgw_forum_threads_old');

		$setup_info['forum']['currentver'] = '0.9.13.002';
		return $setup_info['forum']['currentver'];
	}



	$test[] = '0.9.13.001';
	function forum_upgrade0_9_13_001()
	{
		global $setup_info, $phpgw_setup;

		$phpgw_setup->oProc->AlterColumn('phpgw_forum_threads','postdate',array('type' => 'timestamp','nullable' => False,'default' => 'current_timestamp'));
		$setup_info['forum']['currentver'] = '0.9.13.002';
		return $setup_info['forum']['currentver'];
	}
?>

*/

