<?php
/**
 * eGroupWare - Wizard for user CSV import
 *
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package calendar
 * @subpackage importexport
 * @link http://www.egroupware.org
 * @author Nathan Gray
 * @version $Id$
 */

class calendar_wizard_import_csv extends importexport_wizard_basic_import_csv
{

	/**
	 * constructor
	 */
	function __construct()
	{
		parent::__construct();

		$this->steps += array(
			'wizard_step50' => lang('Manage mapping'),
		);
		// No conditions yet
		unset($this->steps['wizard_step55']);

		// Field mapping
		$tracking = new calendar_tracking();
		$this->mapping_fields = $tracking->field2label;

		// Actions
		$this->actions = array(
			'none'		=>	lang('none'),
			'update'	=>	lang('update'),
			'insert'	=>	lang('insert'),
		);

		// Conditions
		$this->conditions = array(
		);
	}

	function wizard_step50(&$content, &$sel_options, &$readonlys, &$preserv)
	{
		$result = parent::wizard_step50($content, $sel_options, $readonlys, $preserv);
		$content['msg'] .= "\n*" ;
		
		return $result;
	}
}
