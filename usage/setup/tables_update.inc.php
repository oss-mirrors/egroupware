<?php
/**
 * eGroupWare - Setup
 * http://www.egroupware.org
 * Created by eTemplates DB-Tools written by ralfbecker@outdoor-training.de
 *
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package usage
 * @subpackage setup
 * @version $Id$
 */


function tracker_upgrade1_7_001()
{
	return $GLOBALS['setup_info']['usage']['currentver'] = '14.1';
}
