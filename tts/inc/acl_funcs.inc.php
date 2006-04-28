<?PHP

function check_read_right ($ticket_owner = -1, $current_assignee = -1, $current_group = 1)
{
	$acl = CreateObject('phpgwapi.acl', $GLOBALS['phpgw_info']['user']['account_id']);
	$acl = $GLOBALS['phpgw']->acl->get_grants('tts');

	if ($acl[$current_group] & PHPGW_ACL_READ) {
		/*
		 * Allowed by ACL
		 */
		return (true);
	} 

	if ($GLOBALS['phpgw_info']['user']['account_id'] == $ticket_owner) {
		/*
		 * Allowed by Ownership
		 */
		return (true);
	}

	if ($GLOBALS['phpgw_info']['user']['account_id'] == $current_assignee) {
		/*
		 * Allowed by Assignment
		 */
		return (true);
	}

	// Not allowed
	return (false);
}

function check_assign_right ($check_user = -1, $check_group = 1, $current_group = 1)
{
	$new_ticket = false;
	if ($current_group == 1) {
		/*
		 * for new tickets, defaults to user's primary group
		 */
		$new_ticket = true;
		$current_group = $GLOBALS['phpgw_info']['user']['account_primary_group'];
	}
	if ($check_user > 0) {
		if ($check_user == $GLOBALS['phpgw_info']['user']['account_id'] && $new_ticket) {
			return (true);
		}
		/*
		 * Get the ACL's for user $check_user
		 */
		$acl = CreateObject('phpgwapi.acl', $check_user);
		$acl = $acl->get_grants('tts');
		if ($acl[$current_group] & PHPGW_ACL_ADD) {
			/*
			 * Allowed by ACL
			 */
			return (true);
		} else {
			return (false);
		}
	} elseif ($check_group < 0) {
		// Always allow assigning to own primary group
		if ($check_group == $GLOBALS['phpgw_info']['user']['account_primary_group'] && $new_ticket) {
			return (true);
		}

		/*
		 * Get the ACL's for group $check_group
		 */
		$acl = CreateObject('phpgwapi.acl');
		if (!($accounts = $GLOBALS['phpgw']->acl->get_ids_for_location($check_group, PHPGW_ACL_ADD, 'tts'))) {
			return (false);
		} else {
			return (in_array($current_group, $accounts));
		}
	} else {
		return (false); // Probably a bug :-/
	}
}
?>
