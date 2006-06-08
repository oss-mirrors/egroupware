<?php
/**************************************************************************\
* eGroupWare - Setup                                                       *
* http://www.egroupware.org                                                *
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
	$GLOBALS['phpgw_setup']->oProc->CreateTable('phpgw_tts_tickets',array(
		'fd' => array(
			'ticket_id' 			=> array('type' => 'auto','nullable' => False),
			'ticket_group' 			=> array('type' => 'varchar','precision' => '40'),
			'ticket_priority' 		=> array('type' => 'int','precision' => '2','nullable' => False),
			'ticket_owner' 			=> array('type' => 'varchar','precision' => '10'),
			'ticket_assignedto' 	=> array('type' => 'varchar','precision' => '10'),
			'ticket_subject' 		=> array('type' => 'varchar','precision' => '255'),
			'ticket_category' 		=> array('type' => 'varchar','precision' => '25'),
			'ticket_billable_hours' => array('type' => 'decimal','precision' => '8','scale' => '2','nullable' => False),
			'ticket_billable_rate' 	=> array('type' => 'decimal','precision' => '8','scale' => '2','nullable' => False),
			'ticket_status' 		=> array('type' => 'char','precision' => '1','nullable' => False),
			'ticket_details' 		=> array('type' => 'text','nullable' => False),
		),
		'pk' => array('ticket_id'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	));

	$GLOBALS['phpgw_setup']->oProc->CreateTable('phpgw_tts_views',array(
		'fd' => array(
			'view_id' 					=> array('type' => 'int','precision' => '4','nullable' => False),
			'view_account_id' 		=> array('type' => 'varchar','precision' => '40','nullable' => True),
			'view_time' 				=> array('type' => 'int','precision' => '4','nullable' => False)
		),
		'pk' => array(),
		'ix' => array(),
		'fk' => array(),
		'uc' => array()
	));

	$db2 = $GLOBALS['phpgw_setup']->db;	// we need a 2. result-set
	$GLOBALS['phpgw_setup']->oProc->query($sql="SELECT t.*,u.account_id AS owner,a.account_id as assingedto FROM ticket t,phpgw_accounts u LEFT JOIN phpgw_accounts a ON t.t_assignedto=a.account_lid WHERE t.t_user=u.account_lid");

	while ($GLOBALS['phpgw_setup']->oProc->next_record())
	{
		$ticket = array(
			't_id' => $GLOBALS['phpgw_setup']->oProc->f('t_id'),
			't_priority' => $GLOBALS['phpgw_setup']->oProc->f('t_priority'),
			'owner' => $GLOBALS['phpgw_setup']->oProc->f('owner'),
			'assignedto' => $GLOBALS['phpgw_setup']->oProc->f('assignedto'),
			't_subject' => $GLOBALS['phpgw_setup']->oProc->f('t_subject'),
			't_timestamp_closed' => $GLOBALS['phpgw_setup']->oProc->f('t_timestamp_closed'),
			't_detail' => $GLOBALS['phpgw_setup']->oProc->f('t_detail')
		);
		$db2->query($sql="INSERT INTO phpgw_tts_tickets (ticket_id,ticket_group,ticket_priority,ticket_owner,ticket_assignedto,ticket_subject,ticket_category,ticket_billable_hours,ticket_billable_rate,ticket_status,ticket_details) VALUES ($ticket[t_id],'0',$ticket[t_priority],'$ticket[owner]','$ticket[assignedto]','$ticket[t_subject]','','0.00','0.00','".(!$ticket['t_timestamp_closed']?'O':'X')."','$ticket[t_detail]')",__LINE__,__FILE__);
	}

	$GLOBALS['phpgw_setup']->oProc->DropTable('ticket');
	$GLOBALS['phpgw_setup']->oProc->DropTable('category');
	$GLOBALS['phpgw_setup']->oProc->DropTable('department');
	
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

	$oProc->query('UPDATE phpgw_tts_tickets SET ticket_state='.(int)$states['UNDEFINED'].' WHERE ticket_state = -1');

	$GLOBALS['setup_info']['tts']['currentver'] = '0.8.2.000';
	return $GLOBALS['setup_info']['tts']['currentver'];
}


$test[] = '0.8.2.000';
function tts_upgrade0_8_2_000()
{
	$GLOBALS['setup_info']['tts']['currentver'] = '1.0.0';
	return $GLOBALS['setup_info']['tts']['currentver'];
}


$test[] = '1.0.0';
function tts_upgrade1_0_0()
{
	$GLOBALS['phpgw_setup']->oProc->AlterColumn('phpgw_tts_states','state_name',array(
		'type' => 'varchar',
		'precision' => '64',
		'nullable' => False
	));
	$GLOBALS['phpgw_setup']->oProc->AlterColumn('phpgw_tts_states','state_description',array(
		'type' => 'varchar',
		'precision' => '255',
		'nullable' => False
	));

	$GLOBALS['setup_info']['tts']['currentver'] = '1.0.001';
	return $GLOBALS['setup_info']['tts']['currentver'];
}


$test[] = '1.0.001';
function tts_upgrade1_0_001()
{
	$GLOBALS['phpgw_setup']->oProc->AlterColumn('phpgw_tts_transitions','transition_name',array(
		'type' => 'varchar',
		'precision' => '64',
		'nullable' => False
	));
	$GLOBALS['phpgw_setup']->oProc->AlterColumn('phpgw_tts_transitions','transition_description',array(
		'type' => 'varchar',
		'precision' => '255',
		'nullable' => False
	));

	$GLOBALS['setup_info']['tts']['currentver'] = '1.0.002';
	return $GLOBALS['setup_info']['tts']['currentver'];
}


// downgrade from former HEAD version (josip)
$test[] = '1.0.003';
function tts_upgrade1_0_003()
{
	$GLOBALS['phpgw_setup']->oProc->DropColumn('phpgw_tts_tickets',null,'ticket_caller_name');
	$GLOBALS['phpgw_setup']->oProc->DropColumn('phpgw_tts_tickets',null,'ticket_caller_telephone');
	$GLOBALS['phpgw_setup']->oProc->DropColumn('phpgw_tts_tickets',null,'ticket_caller_telephone_2');
	$GLOBALS['phpgw_setup']->oProc->DropColumn('phpgw_tts_tickets',null,'ticket_caller_email');
	$GLOBALS['phpgw_setup']->oProc->DropColumn('phpgw_tts_tickets',null,'ticket_caller_ticket_id');
	$GLOBALS['phpgw_setup']->oProc->DropColumn('phpgw_tts_tickets',null,'ticket_caller_password');
	$GLOBALS['phpgw_setup']->oProc->DropColumn('phpgw_tts_tickets',null,'ticket_caller_address');
	$GLOBALS['phpgw_setup']->oProc->DropColumn('phpgw_tts_tickets',null,'ticket_caller_address_2');
	$GLOBALS['phpgw_setup']->oProc->DropColumn('phpgw_tts_tickets',null,'ticket_caller_audio_file');
	$GLOBALS['phpgw_setup']->oProc->DropColumn('phpgw_tts_tickets',null,'ticket_caller_satisfaction');
	$GLOBALS['phpgw_setup']->oProc->DropColumn('phpgw_tts_tickets',null,'ticket_escalation');
	$GLOBALS['phpgw_setup']->oProc->DropColumn('phpgw_tts_tickets',null,'ticket_escalation_time');

	$GLOBALS['phpgw_setup']->oProc->DropTable('phpgw_tts_categories_groups');
	$GLOBALS['phpgw_setup']->oProc->DropTable('phpgw_tts_escalation');
	$GLOBALS['phpgw_setup']->oProc->DropTable('phpgw_tts_tickets_wnt');
	$GLOBALS['phpgw_setup']->oProc->DropTable('phpgw_tts_views_wnt');
	
	$GLOBALS['setup_info']['tts']['currentver'] = '1.0.002';
	return $GLOBALS['setup_info']['tts']['currentver'];
}


$test[] = '1.0.002';
function tts_upgrade1_0_002()
{
	$GLOBALS['egw_setup']->oProc->AddColumn('phpgw_tts_tickets','ticket_due',array(
		'type' => 'timestamp'
	));

	// change the prio-scale from 1..10 --> 4..0
	$GLOBALS['egw_setup']->oProc->query("UPDATE phpgw_tts_tickets SET ticket_priority=4.4-((ticket_priority-1)/2)",__LINE__,__FILE__);

	$GLOBALS['egw_setup']->oProc->AddColumn('phpgw_tts_transitions','transition_email',array(
		'type' => 'char',
		'precision' => '1',
		'nullable' => False,
		'default' => 'Y'
	));

	$GLOBALS['egw_setup']->oProc->AddColumn('phpgw_tts_states','state_open',array(
		'type' => 'char',
		'precision' => '1',
		'nullable' => False,
		'default' => 'O'
	));
	
	// set this for all states we consider "closed" to 'X' (guesswork right now)
	$GLOBALS['egw_setup']->oProc->query("UPDATE phpgw_tts_states SET state_open='X' WHERE state_name IN ('RESOLVED','DONE','CLOSED','FINISHED')",__LINE__,__FILE__);

	return $GLOBALS['setup_info']['tts']['currentver'] = '1.2.008';
}


$test[] = '1.2.008';
function tts_upgrade1_2_008()
{
	$GLOBALS['setup_info']['tts']['currentver'] = '1.2.009';
	return $GLOBALS['setup_info']['tts']['currentver'];
}

?>
