<?php
  /**************************************************************************\
  *eGroupWare - Setup                                                        *
  * http://www.egroupware.org                                                *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

$states = array(
	'NEW'       => 'A new ticket has been reported.',
	'ACCEPTED'  => 'The ticket has been accepted by the owner, who is about to work on it.',
	'REOPENED'  => 'The ticket has been reopened for further work.',
	'RESOLVED'  => 'The ticket has been successfully resolved.',
	'VERIFIED'  => 'The ticket has been verified and has to be worked on.',
	'CLOSED'    => 'The ticket has been closed without resolution.',
	'TOVALIDATE'=> 'The ticket has been worked on and the work requires validation.',
	'NEEDSWORK' => 'The ticket has been worked on, but requires more work.',
	'INVALID'   => 'The owner of the ticket was not able to confirm the issue.',
	'DUPLICATE' => 'The ticket was found a duplicate of another ticket.',
);

foreach($states as $state => $desc)
{
	$oProc->query("INSERT INTO phpgw_tts_states(state_name,state_description,state_initial) VALUES('$state','$desc',".(int)($state=='NEW').')');
	$states[$state] = $oProc->m_odb->get_last_insert_id('phpgw_tts_states','state_id');
}


$qs = "INSERT INTO phpgw_tts_transitions(transition_name,transition_description,transition_source_state,transition_target_state,transition_email) ";

$transitions = array(
	"'ACCEPT','Accept the ticket into verification process.',${states['NEW']},${states['ACCEPTED']}",
	"'VERIFY','Verify the ticket to work on it.',${states['ACCEPTED']},${states['VERIFIED']}",
	"'INVALIDATE','The ticket is invalid and cannot be worked on.',${states['ACCEPTED']},${states['INVALID']}",
	"'DUPLICATE','The ticket is a duplicate of another ticket and should be closed.',${states['ACCEPTED']},${states['DUPLICATE']}",
	"'CLOSE','Close the invalid ticket.',$[states['INVALID']},${states['CLOSED']}",
	"'MOREWORK','I worked on the ticket, but did not finish.',${states['VERIFIED']},${states['NEEDSWORK']}",
	"'COMPLETED','I worked on the ticket and the work requires validation.',${states['VERIFIED']},${states['TOVALIDATE']}",
	"'COMPLETED','I worked on the ticket and the work requires validation.',${states['NEEDSWORK']},${states['TOVALIDATE']}",
	"'NOT COMPLETED','The validation of the ticket was unsuccessfull. The ticket requires more work.',${states['TOVALIDATE']},${states['NEEDSWORK']}",
	"'RESOLVE','The ticket resolution was successfully validated. Close the ticket.',${states['TOVALIDATE']},${states['RESOLVED']}",
	"'REOPEN','The closed ticket requires more work. Reopen it.',${states['CLOSED']},${states['REOPENED']}",
	"'REOPEN','The closed ticket requires more work. Reopen it.',${states['RESOLVED']},${states['REOPENED']}",
	"'NO DUPLICATE','The ticket is essentially not a duplicate of another ticket. Reopen it.',${states['DUPLICATE']},${states['REOPENED']}",
	"'ACCEPT','Accept the ticket into verification process.',${states['REOPENED']},${states['ACCEPTED']}"
	);

foreach ($transitions as $t)
{
    $oProc->query($qs. "VALUES(" . $t . ",'Y')");
}
	    
?>
