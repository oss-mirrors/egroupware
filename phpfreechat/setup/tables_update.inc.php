<?php
/**
 * eGroupWare - Setup
 * http://www.egroupware.org
 * Created by eTemplates DB-Tools written by ralfbecker@outdoor-training.de
 *
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package phpfreechat
 * @subpackage setup
 * @version $Id$
 */

function phpfreechat_upgrade1_6_001()
{
	return $GLOBALS['setup_info']['phpfreechat']['currentver'] = '1.8';
}
