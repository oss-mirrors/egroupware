<?php
/**
 * EGroupware - eTemplate serverside textbox widget
 *
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package etemplate
 * @subpackage api
 * @link http://www.egroupware.org
 * @author Ralf Becker <RalfBecker@outdoor-training.de>
 * @copyright 2002-11 by RalfBecker@outdoor-training.de
 * @version $Id$
 */

/**
 * eTemplate textbox widget with following sub-types:
 * - textbox with optional multiline="true" and rows="123"
 * - integer or int
 * - float
 * - hidden
 * - colorpicker
 * sub-types are either passed to constructor or set via 'type' attribute!
 */
class etemplate_widget_textbox extends etemplate_widget
{
	/**
	 * Constructor
	 *
	 * @param string|XMLReader $xml string with xml or XMLReader positioned on the element to construct
	 * @throws egw_exception_wrong_parameter
	 */
	public function __construct($xml)
	{
		parent::__construct($xml);

		// normalize types
		if ($this->type !== 'textbox')
		{
			if ($this->type == 'int') $this->type = 'integer';

			$this->attrs['type'] = $this->type;
			$this->type = 'textbox';
		}
	}

	/**
	 * Validate input
	 *
	 * Following attributes get checked:
	 * - needed: value must NOT be empty
	 * - min, max: int and float widget only
	 * - maxlength: maximum length of string (longer strings get truncated to allowed size)
	 * - preg: perl regular expression incl. delimiters (set by default for int, float and colorpicker)
	 * - int and float get casted to their type
	 *
	 * @param string $cname current namespace
	 * @param array $content
	 * @param array &$validated=array() validated content
	 */
	public function validate($cname, array $content, &$validated=array())
	{
		if (!$this->is_readonly($cname))
		{
			if (!isset($this->attrs['preg']))
			{
				switch($this->type)
				{
					case 'integer':
						$this->attrs['preg'] = '/^-?[0-9]*$/';
						break;
					case 'float':
						$this->attrs['preg'] = '/^-?[0-9]*[,.]?[0-9]*$/';
						break;
					case 'colorpicker':
						$this->attrs['preg'] = '/^(#[0-9a-f]{6}|)$/i';
						break;
				}
			}
			$form_name = self::form_name($cname, $this->id);

			$value = $value_in = self::get_array($content, $form_name);
			$valid =& self::get_array($validated, $form_name, true);

			if ((string)$value === '' && $this->attrs['needed'])
			{
				self::set_validation_error($form_name,lang('Field must not be empty !!!'),'');
			}
			if ((int) $this->attrs['maxlength'] > 0 && strlen($value) > (int) $this->attrs['maxlength'])
			{
				$value = substr($value,0,(int) $this->attrs['maxlength']);
			}
			if ($this->attrs['preg'] && !preg_match($this->attrs['preg'],$value))
			{
				switch($this->type)
				{
					case 'integer':
						self::set_validation_error($form_name,lang("'%1' is not a valid integer !!!",$value),'');
						break;
					case 'float':
						self::set_validation_error($form_name,lang("'%1' is not a valid floatingpoint number !!!",$value),'');
						break;
					default:
						self::set_validation_error($form_name,lang("'%1' has an invalid format !!!",$value)/*." !preg_match('$this->attrs[preg]', '$value')"*/,'');
						break;
				}
			}
			elseif ($this->type == 'integer' || $this->type == 'float')	// cast int and float and check range
			{
				if ((string)$value !== '' || $this->attrs['needed'])	// empty values are Ok if needed is not set
				{
					$value = $this->type == 'integer' ? (int) $value : (float) str_replace(',','.',$value);	// allow for german (and maybe other) format

					if (!empty($this->attrs['min']) && $value < $this->attrs['min'])
					{
						self::set_validation_error($form_name,lang("Value has to be at least '%1' !!!",$this->attrs['min']),'');
						$value = $this->type == 'integer' ? (int) $this->attrs['min'] : (float) $this->attrs['min'];
					}
					if (!empty($this->attrs['max']) && $value > $this->attrs['max'])
					{
						self::set_validation_error($form_name,lang("Value has to be at maximum '%1' !!!",$this->attrs['max']),'');
						$value = $this->type == 'integer' ? (int) $this->attrs['max'] : (float) $this->attrs['max'];
					}
				}
			}
			$valid = $value;
			error_log(__METHOD__."() $form_name: ".array2string($value_in).' --> '.array2string($value));
		}
	}
}
etemplate_widget::registerWidget('etemplate_widget_textbox', array('textbox','int','integer','float','passwd','hidden','colorpicker'));