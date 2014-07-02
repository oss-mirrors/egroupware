<?php
/**
 * EGgroupware - Usage statistic
 *
 * @link http://www.egroupware.org
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @package usage
 * @subpackage sitemgr
 * @copyright (c) 2009 by Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

/**
 * Usage statistics block
 *
 */
class module_usage extends Module
{
	/**
	 * Constructor
	 *
	 */
	function __construct()
	{
		$this->arguments = array();
		$this->title = lang('Usage statistics');
		$this->description = lang('This module receivs and displays EGroupware usage statistic.');

	}

	/**
	 * Return block content
	 *
	 * @param array &$arguments
	 * @param array $properties
	 * @return string
	 */
	function get_content(&$arguments,$properties)
	{
		try
		{
			$usage = new usage_bo();
			if (isset($_POST['exec']))
			{
				$content = $usage->receive();
			}
			$content .= $usage->statistic();
		}
		catch(Exception $e)
		{
			$content .= "<groupbox><legend style='font-size:150%; font-weight: bold;'> ".lang('Error').": </legend><h1>".$e->getMessage()."</h1></groupbox>\n";
		}
		return $content;
	}
}
