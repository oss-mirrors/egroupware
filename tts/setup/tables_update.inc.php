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

      $oProc->query("insert into phpgw_tts_states(state_id,state_name,state_description) values(-1,'".lang('UNDEFINED')."',		'".lang('The ticket was existent before the Petri Net infrastructure was defined and should be assigned a state.')."')");
      $oProc->query("insert into phpgw_tts_states(state_id,state_name,state_description,state_initial) values(1,'".lang('NEW')."',		'".lang('A new ticket has been reported.')."',1)");
		$oProc->query("insert into phpgw_tts_states(state_id,state_name,state_description) values(2,'".lang('ACCEPTED')."',	'".lang('The ticket has been accepted by the owner, who is about to work on it.')."')");
		$oProc->query("insert into phpgw_tts_states(state_id,state_name,state_description) values(3,'".lang('REOPENED')."',	'".lang('The ticket has been reopened for further work.')."')");
      $oProc->query("insert into phpgw_tts_states(state_id,state_name,state_description) values(4,'".lang('RESOLVED')."',	'".lang('The ticket has been successfully resolved.')."')");
      $oProc->query("insert into phpgw_tts_states(state_id,state_name,state_description) values(5,'".lang('VERIFIED')."',	'".lang('The ticket has been verified and has to be worked on.')."')");
      $oProc->query("insert into phpgw_tts_states(state_id,state_name,state_description) values(6,'".lang('CLOSED')."',		'".lang('The ticket has been closed without resolution.')."')");
      $oProc->query("insert into phpgw_tts_states(state_id,state_name,state_description) values(7,'".lang('TOVALIDATE')."',	'".lang('The ticket has been worked on and the work requires validation.')."')");
      $oProc->query("insert into phpgw_tts_states(state_id,state_name,state_description) values(8,'".lang('NEEDSWORK')."',	'".lang('The ticket has been worked on, but requires more work.')."')");
      $oProc->query("insert into phpgw_tts_states(state_id,state_name,state_description) values(9,'".lang('INVALID')."',		'".lang('The owner of the ticket was not able to confirm the issue.')."')");
      $oProc->query("insert into phpgw_tts_states(state_id,state_name,state_description) values(10,'".lang('DUPLICATE')."',	'".lang('The ticket was found a duplicate of another ticket.')."')");

      $oProc->query("select setval('seq_phpgw_tts_states',10)");

      $oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
                         ." values('".lang('TONEW')."',			'".lang('Put the preexistent ticket into the state NEW.')."',-1,1)"); 
      $oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
                         ." values('".lang('TOACCEPT')."',			'".lang('Put the preexistent ticket into the state ACCEPT.')."',-1,2)"); 
      $oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
                         ." values('".lang('TOREOPENED')."',			'".lang('Put the preexistent ticket into the state REOPENED.')."',-1,3)"); 
      $oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
                         ." values('".lang('TORESOLVED')."',			'".lang('Put the preexistent ticket into the state RESOLVED.')."',-1,4)"); 
      $oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
                         ." values('".lang('TOVERIFIED')."',			'".lang('Put the preexistent ticket into the state VERIFIED.')."',-1,5)"); 
      $oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
                         ." values('".lang('TOCLOSED')."',			'".lang('Put the preexistent ticket into the state CLOSED.')."',-1,6)"); 
      $oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
                         ." values('".lang('TOTOVALIDATE')."',			'".lang('Put the preexistent ticket into the state TOVALIDATE.')."',-1,7)"); 
      $oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
                         ." values('".lang('TONEEDSWORK')."',			'".lang('Put the preexistent ticket into the state NEEDSWORK.')."',-1,8)"); 
      $oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
                         ." values('".lang('TOINVALID')."',			'".lang('Put the preexistent ticket into the state INVALID.')."',-1,9)"); 
      $oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
                         ." values('".lang('TODUPLICATE')."',			'".lang('Put the preexistent ticket into the state DUPLICATE.')."',-1,10)"); 

      $oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
                         ." values('".lang('ACCEPT')."',			'".lang('Accept the ticket into verification process.')."',1,2)"); 
      $oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
                         ." values('".lang('VERIFY')."',			'".lang('Verify the ticket to work on it.')."',2,5)");
      $oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
                         ." values('".lang('INVALIDATE')."',		'".lang('The ticket is invalid and cannot be worked on.')."',2,9)");
      $oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
                         ." values('".lang('DUPLICATE')."',		'".lang('The ticket is a duplicate of another ticket and should be closed.')."',2,10)");
      $oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
                         ." values('".lang('CLOSE')."',			'".lang('Close the invalid ticket.')."',9,6)");
      $oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
                         ." values('".lang('MOREWORK')."',		'".lang('I worked on the ticket, but did not finish.')."',5,8)");
      $oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
                         ." values('".lang('COMPLETED')."',		'".lang('I worked on the ticket and the work requires validation.')."',5,7)");
      $oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
                         ." values('".lang('COMPLETED')."',		'".lang('I worked on the ticket and the work requires validation.')."',8,7)");
      $oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
                         ." values('".lang('NOT COMPLETED')."',	'".lang('The validation of the ticket was unsuccessfull. The ticket requires more work.')."',7,8)");
      $oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
                         ." values('".lang('RESOLVE')."',			'".lang('The ticket resolution was successfully validated. Close the ticket.')."',7,4)");
      $oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
                         ." values('".lang('REOPEN')."',			'".lang('The closed ticket requires more work. Reopen it.')."',6,3)");
      $oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
                         ." values('".lang('REOPEN')."',			'".lang('The closed ticket requires more work. Reopen it.')."',4,3)");
      $oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
                         ." values('".lang('NO DUPLICATE')."',	'".lang('The ticket is essentially not a duplicate of another ticket. Reopen it.')."',10,3)");
      $oProc->query("insert into phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state)"
                         ." values('".lang('ACCEPT')."',			'".lang('Accept the ticket into verification process.')."',3,2)");


		$GLOBALS['setup_info']['tts']['currentver'] = '0.8.2.000';
		return $GLOBALS['setup_info']['tts']['currentver'];
	}
?>
