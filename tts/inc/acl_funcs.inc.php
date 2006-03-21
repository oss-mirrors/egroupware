<?PHP

function check_ticket_right ($ticket_assignedto, $ticket_owner, $ticket_group, $right = PHPGW_ACL_READ) {
	static $acl;

	// If we haven't read the acls before, we should do so now
	if (! isset($acl)) {
		$acl = CreateObject('phpgwapi.acl', $GLOBALS['phpgw_info']['user']['account_id']);
		$acl = $GLOBALS['phpgw']->acl->get_grants('tts');
	}
	// Allowed by ACL
	if ($acl[$ticket_group] & $right) {
		return (true);
	} 

	// Allowed by Ownership
	if ($ticket_owner != 0
	 && $GLOBALS['phpgw_info']['user']['account_id'] == $ticket_owner) {
		return (true);
	}

	// Always allow assigning to own primary group
	if ($right = PHPGW_ACL_ADD && ($ticket_group != -1
	 && $GLOBALS['phpgw_info']['user']['account_primary_group'] == $ticket_group)) {
		return (true);
	}


	// Allowed by Assignment
	if (($right == PHPGW_ACL_READ
	 || $right == PHPGW_ACL_ADD)
	 && ($ticket_assignedto != 0
	 && $GLOBALS['phpgw_info']['user']['account_id'] == $ticket_assignedto)) {
		return (true);
	}

	// Not allowed
	return (false);
}
?>
