<?php
  /**************************************************************************\
  * phpGroupWare - Setup                                                     *
  * http://www.phpgroupware.org                                              *
  * Created by eTemplates DB-Tools written by ralfbecker@outdoor-training.de *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

	$test[] = '0.0.0';
	function tts_upgrade0_0_0()
	{
		$GLOBALS['phpgw_setup']->oProc->RenameTable('ticket','phpgw_tts_tickets');

		$GLOBALS['setup_info']['tts']['currentver'] = '0.8.1.003';
		return $GLOBALS['setup_info']['tts']['currentver'];
	}

	$test[] = '0.8.1.003';
	function tts_upgrade0_8_1_003()
	{
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_tts_tickets','ticket_state',array(
			'type' => 'int',
			'precision' => '4',
			'nullable' => False,
			'default' => '-1'
		));

		$GLOBALS['phpgw_setup']->oProc->CreateTable('phpgw_tts_states',array(
			'fd' => array(
				'state_id' => array('type' => 'auto','nullable' => False),
				'state_name' => array('type' => 'text','nullable' => False),
				'state_description' => array('type' => 'text','nullable' => False),
				'state_initial' => array('type' => 'int', 'precision'=>'4', 'nullable' => False, 'default'=>'0')
			),
			'pk' => array('state_id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		));


		$GLOBALS['phpgw_setup']->oProc->CreateTable('phpgw_tts_transitions',array(
			'fd' => array(
				'transition_id' => array('type' => 'auto','nullable' => False),
				'transition_name' => array('type' => 'text','precision' => '20','nullable' => False),
				'transition_description' => array('type' => 'text','nullable' => False),
				'transition_source_state' => array('type' => 'int','precision' => '4','nullable' => False),
				'transition_target_state' => array('type' => 'int','precision' => '4','nullable' => False)
			),
			'pk' => array('transition_id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		));

		$oProc=$GLOBALS['phpgw_setup']->oProc;

		include('default_records.inc.php');	// dont need to dublicate everything here

		$GLOBALS['setup_info']['tts']['currentver'] = '0.8.2.000';
		return $GLOBALS['setup_info']['tts']['currentver'];
	}
?>
