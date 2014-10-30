<?php
/**
 * @deprecated can be removed with next release, as setup.inc.php registers now sambaadmin.bosambaadmin.changePassword direct
 */
$boSambaAdmin = CreateObject('sambaadmin.bosambaadmin');

$boSambaAdmin->edit_user($GLOBALS['hook_values']);
