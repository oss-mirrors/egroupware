<?php
/**
 * Wiki - facilitate the update to new autoloading names
 *
 * @link http://www.egroupware.org
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @package wiki
 * @copyright (c) 2009 by Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

/**
 * Intermediate class to facilitate the update.
 *
 * It will be called only once and can be removed after 1.8 is released.
 */
class bowiki extends wiki_bo
{
	function search_link($location)
	{
		// register all hooks
		ExecMethod('phpgwapi.hooks.register_hooks','wiki');

		return parent::search_link($location);
	}
}