<?php
/**
 * Addressbook - vCard / iCal parser
 *
 * @link http://www.egroupware.org
 * @author Lars Kneschke <lkneschke@egroupware.org>
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @author Joerg Lehrke <jlehrke@noc.de>
 * @package addressbook
 * @subpackage export
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

require_once EGW_SERVER_ROOT.'/phpgwapi/inc/horde/lib/core.php';
require_once(EGW_SERVER_ROOT.'/phpgwapi/inc/horde/Horde/SyncML/State.php');

/**
 * Addressbook - vCard parser
 *
 */
class addressbook_vcal extends addressbook_bo
{
	/**
	 * product manufacturer from setSupportedFields (lowercase!)
	 *
	 * @var string
	 */
	var $productManufacturer = 'file';
	/**
	 * product name from setSupportedFields (lowercase!)
	 *
	 * @var string
	 */
	var $productName;
	/**
	 * VCard version
	 *
	 * @var string
	 */
	var $version;
	/**
	 * Client CTCap Properties
	 *
	 * @var array
	 */
	var $clientProperties;
	/**
	* Set Logging
	*
	* @var string
	* off = 0;
	*/
	var $log = 0;
	var $logfile="/tmp/log-vcard";
	/**
	* Constructor
	*
	* @param string $contact_app			the current application
	* @param string	$_contentType			the content type (version)
	* @param array $_clientProperties		client properties
	*/
	function __construct($contact_app='addressbook', $_contentType='text/x-vcard', &$_clientProperties = array())
	{
		parent::__construct($contact_app);
		if($this->log)$this->logfile = $GLOBALS['egw_info']['server']['temp_dir']."/log-vcard";
		if($this->log)error_log(__LINE__.__METHOD__.__FILE__.array2string($_contentType)."\n",3,$this->logfile);
		switch($_contentType)
		{
			case 'text/vcard':
				$this->version = '3.0';
				break;
			default:
				$this->version = '2.1';
			break;
		}
		$this->clientProperties = $_clientProperties;
	}
	/**
	* import a vard into addressbook
	*
	* @param string	$_vcard		the vcard
	* @param int/string	$_abID=null		the internal addressbook id or !$_abID for a new enty
	* @param boolean $merge=false	merge data with existing entry
	* @return int contact id
	*/
	function addVCard($_vcard, $_abID=null, $merge=false)
	{
		if(!$contact = $this->vcardtoegw($_vcard))
		{
			return false;
		}

		if($_abID)
		{
			if ($merge)
			{
				$old_contact = $this->read($_abID);
				if ($old_contact)
				{
					foreach ($contact as $key => $value)
					{
						if (!empty($old_contact[$key]))
						{
							$contact[$key] = $old_contact[$key];
						}
					}
				}
			}
			// update entry
			$contact['id'] = $_abID;
		}
		return $this->save($contact);
	}

	/**
	* return a vcard
	*
	* @param int/string	$_id the id of the contact
	* @param string $_charset='UTF-8' encoding of the vcard, default UTF-8
	* @param boolean $extra_charset_attribute=true GroupDAV/CalDAV dont need the charset attribute and some clients have problems with it
	* @return string containing the vcard
	*/
	function getVCard($_id,$_charset='UTF-8',$extra_charset_attribute=true)
	{
		require_once(EGW_SERVER_ROOT.'/phpgwapi/inc/horde/Horde/iCalendar/vcard.php');

		#Horde::logMessage("vCalAddressbook clientProperties:\n" . print_r($this->clientProperties, true), __FILE__, __LINE__, PEAR_LOG_DEBUG);

		$vCard = new Horde_iCalendar_vcard($this->version);

		if (!is_array($this->supportedFields))
		{
			$this->setSupportedFields();
		}
		$sysCharSet = $GLOBALS['egw']->translation->charset();

		// KAddressbook always requires non-ascii chars to be qprint encoded.
		if ($this->productName == 'kde') $extra_charset_attribute = true;

		if (!($entry = $this->read($_id)))
		{
			return false;
		}

		$this->fixup_contact($entry);

		foreach ($this->supportedFields as $vcardField => $databaseFields)
		{
			$values = array();
			$options = array();
			$hasdata = 0;
			// seperate fields from their options/attributes
			$vcardFields = explode(';', $vcardField);
			$vcardField = $vcardFields[0];
			$i = 1;
			while (isset($vcardFields[$i]))
			{
				list($oname, $oval) = explode('=', $vcardFields[$i]);
				if (!$oval && ($this->version == '3.0'))
				{
					// declare OPTION as TYPE=OPTION
					$options['TYPE'][] = $oname ;
				}
				else
				{
					$options[$oname] = $oval;
				}
				$i++;
			}
			if (is_array($options['TYPE']))
			{
				$oval = implode(",", $options['TYPE']);
				unset($options['TYPE']);
				$options['TYPE'] = $oval;
			}
			if (isset($this->clientProperties[$vcardField]['Size']))
			{
				$size = $this->clientProperties[$vcardField]['Size'];
				$noTruncate = $this->clientProperties[$vcardField]['NoTruncate'];
				//Horde::logMessage("vCalAddressbook $vcardField Size: $size, NoTruncate: " .
				//	($noTruncate ? 'TRUE' : 'FALSE'), __FILE__, __LINE__, PEAR_LOG_DEBUG);
			}
			else
			{
				$size = -1;
				$noTruncate = false;
			}
			foreach ($databaseFields as $databaseField)
			{
				$value = "";

				if (!empty($databaseField))
				{
					$value = trim($entry[$databaseField]);
				}

				switch($databaseField)
				{
					case 'private':
						$value = $value ? 'PRIVATE' : 'PUBLIC';
						$hasdata++;
						break;

					case 'bday':
						if (!empty($value))
						{
							if ($size == 8)
							{
								$value = str_replace('-','',$value);
							}
							elseif (isset($options['TYPE']) && (
								$options['TYPE'] == 'BASIC'))
							{
								unset($options['TYPE']);
								// used by old SyncML implementations
								$value = str_replace('-','',$value).'T000000Z';
							}
							$hasdata++;
						}
						break;

					case 'jpegphoto':
						if (!empty($value) &&
								(($size < 0) || (strlen($value) < $size)))
						{
							if (!isset($options['TYPE']))
							{
								$options['TYPE'] = 'JPEG';
							}
							if (!isset($options['ENCODING']))
							{
								$options['ENCODING'] = 'BASE64';
							}
							$hasdata++;
						}
						else
						{
							$value = '';
						}
						break;

					case 'cat_id':
						if (!empty($value) && ($values = $this->get_categories($value)))
						{
							$values = (array) $GLOBALS['egw']->translation->convert($values, $sysCharSet, $_charset);
							$value = implode(',', $values); // just for the CHARSET recognition
							if ($extra_charset_attribute && preg_match('/([\177-\377])/', $value))
							{
								$options['CHARSET'] = $_charset;
								// KAddressbook requires non-ascii chars to be qprint encoded, other clients eg. nokia phones have trouble with that
								if ($this->productName == 'kde')
								{
									$options['ENCODING'] = 'QUOTED-PRINTABLE';
								}
								else
								{
									$options['ENCODING'] = '';
								}
							}
 							$hasdata++;
						}
						break;

					default:
						if (($size > 0) && strlen(implode(',', $values) . $value) > $size)
						{
							if ($noTruncate)
							{
								error_log(__FILE__ . __LINE__ . __METHOD__ . " vCalAddressbook $vcardField omitted due to maximum size $size");
								// Horde::logMessage("vCalAddressbook $vcardField omitted due to maximum size $size",
								//		__FILE__, __LINE__, PEAR_LOG_WARNING);
								continue;
							}
							// truncate the value to size
							$cursize = strlen(implode('', $values));
							$left = $size - $cursize - count($databaseFields) + 1;
							if ($left > 0)
							{
								$value = substr($value, 0, $left);
							}
							else
							{
								$value = '';
							}
							error_log(__FILE__ . __LINE__ . __METHOD__ . " vCalAddressbook $vcardField truncated to maximum size $size");
							//Horde::logMessage("vCalAddressbook $vcardField truncated to maximum size $size",
							//		__FILE__, __LINE__, PEAR_LOG_INFO);
						}
						if (!empty($value) // required field
							|| in_array($vcardField,array('FN','ORG','N'))
							|| ($size >= 0 && !$noTruncate))
						{
							$value = $GLOBALS['egw']->translation->convert(trim($value), $sysCharSet, $_charset);
							$values[] = $value;
							if ($extra_charset_attribute)
							{
								if (preg_match('/([\177-\377])/', $value))
								{
									$options['CHARSET'] = $_charset;
									// KAddressbook requires non-ascii chars to be qprint encoded, other clients eg. nokia phones have trouble with that
									if ($this->productName == 'kde')
									{
										$options['ENCODING'] = 'QUOTED-PRINTABLE';
									}
									else
									{
										$options['ENCODING'] = '';
									}
								}
								// protect the CardDAV
								if (preg_match('/([\000-\012\015\016\020-\037\075])/', $value))
								{
									$options['ENCODING'] = 'QUOTED-PRINTABLE';
								}
							}
							else
							{
								// avoid that these options are inserted from horde code
								$options['CHARSET'] = '';
								$options['ENCODING'] = '';
							}
							if ($vcardField == 'TEL' && $entry['tel_prefer'] &&
								($databaseField == $entry['tel_prefer']))
							{
								if ($options['TYPE'])
								{
									$options['TYPE'] .= ',';
								}
								$options['TYPE'] .= 'PREF';
							}
							$hasdata++;
						}
						else
						{
							$values[] = '';
						}
						break;
				}
			}

			if ($hasdata <= 0)
			{
				// don't add the entry if there is no data for this field,
				// except it's a mendatory field
				continue;
			}

			$vCard->setAttribute($vcardField, $value, $options, true, $values);
			//$vCard->setParameter($vcardField, $options);
		}

		$result = $vCard->exportvCalendar();
		if($this->log)error_log(__LINE__.__METHOD__.__FILE__."'$this->productManufacturer','$this->productName'"."\n",3,$this->logfile);
                if($this->log)error_log(__LINE__.__METHOD__.__FILE__."\n".array2string($result)."\n",3,$this->logfile);
		return $result;
	}

	function search($_vcard, $contentID=null, $relax=false)
	{
		$result = false;

		if (($contact = $this->vcardtoegw($_vcard)))
		{
			if ($contentID)
			{
				$contact['contact_id'] = $contentID;
			}
			$result = $this->find_contact($contact, $relax);
		}
		return $result;
	}

	function setSupportedFields($_productManufacturer='', $_productName='')
	{
		$state = &$_SESSION['SyncML.state'];
		if (isset($state))
		{
			$deviceInfo = $state->getClientDeviceInfo();
		}

		// store product manufacturer and name, to be able to use it elsewhere
		if ($_productManufacturer)
		{
			$this->productManufacturer = strtolower($_productManufacturer);
			$this->productName = strtolower($_productName);
		}

		if(isset($deviceInfo) && is_array($deviceInfo))
		{
			if(!isset($this->productManufacturer) ||
				$this->productManufacturer == '' ||
				$this->productManufacturer == 'file')
			{
				$this->productManufacturer = strtolower($deviceInfo['manufacturer']);
			}
			if(!isset($this->productName) || $this->productName == '')
			{
				$this->productName = strtolower($deviceInfo['model']);
			}
		}

		//Horde::logMessage('setSupportedFields(' . $this->productManufacturer . ', ' . $this->productName .')',
		//	__FILE__, __LINE__, PEAR_LOG_DEBUG);

		/**
		 * ToDo Lars:
		 * + changes / renamed fields in 1.3+:
		 *   - access           --> private (already done by Ralf)
		 *   - tel_msg          --> tel_assistent
		 *   - tel_modem        --> tel_fax_home
		 *   - tel_isdn         --> tel_cell_private
		 *   - tel_voice/ophone --> tel_other
		 *   - address2         --> adr_one_street2
		 *   - address3         --> adr_two_street2
		 *   - freebusy_url     --> freebusy_uri (i instead l !)
		 *   - fn               --> n_fn
		 *   - last_mod         --> modified
		 * + new fields in 1.3+:
		 *   - n_fileas
		 *   - role
		 *   - assistent
		 *   - room
		 *   - calendar_uri
		 *   - url_home
		 *   - created
		 *   - creator (preset with owner)
		 *   - modifier
		 *   - jpegphoto
		 */
		$defaultFields[0] = array(	// multisync
			'ADR' 		=> array('','','adr_one_street','adr_one_locality','adr_one_region',
							'adr_one_postalcode','adr_one_countryname'),
			'CATEGORIES' 	=> array('cat_id'),
			'CLASS'		=> array('private'),
			'EMAIL'		=> array('email'),
			'N'		=> array('n_family','n_given','','',''),
			'FN'		=> array('n_fn'),
			'NOTE'		=> array('note'),
			'ORG'		=> array('org_name',''),
			'TEL;CELL'	=> array('tel_cell'),
			'TEL;FAX'	=> array('tel_fax'),
			'TEL;HOME'	=> array('tel_home'),
			'TEL;WORK'	=> array('tel_work'),
			'TITLE'		=> array('title'),
			'UID'       => array('uid'),
		);

		$defaultFields[1] = array(	// all entries, nexthaus corporation, groupdav, ...
			'ADR;WORK'	=> array('','','adr_one_street','adr_one_locality','adr_one_region',
							'adr_one_postalcode','adr_one_countryname'),
			'ADR;HOME'	=> array('','','adr_two_street','adr_two_locality','adr_two_region',
							'adr_two_postalcode','adr_two_countryname'),
			'BDAY'		=> array('bday'),
			'CATEGORIES'	=> array('cat_id'),
			'EMAIL;INTERNET;WORK' => array('email'),
			'EMAIL;INTERNET;HOME' => array('email_home'),
			'N'		=> array('n_family','n_given','n_middle','n_prefix','n_suffix'),
			'FN'		=> array('n_fn'),
			'NOTE'		=> array('note'),
			'ORG'		=> array('org_name','org_unit'),
			'TEL;CELL;WORK'	=> array('tel_cell'),
			'TEL;CELL;HOME'	=> array('tel_cell_private'),
			'TEL;FAX;WORK'	=> array('tel_fax'),
			'TEL;FAX;HOME'	=> array('tel_fax_home'),
			'TEL;HOME'	=> array('tel_home'),
			'TEL;PAGER;WORK' => array('tel_pager'),
			'TEL;WORK'	=> array('tel_work'),
			'TITLE'		=> array('title'),
			'URL;WORK'	=> array('url'),
			'ROLE'		=> array('role'),
			'URL;HOME'	=> array('url_home'),
			'FBURL'		=> array('freebusy_uri'),
			'PHOTO'		=> array('jpegphoto'),
			'UID'       => array('uid'),
		);

		$defaultFields[2] = array(	// sony ericson
			'ADR;HOME' 		=> array('','','adr_one_street','adr_one_locality','adr_one_region',
							'adr_one_postalcode','adr_one_countryname'),
			'BDAY'		=> array('bday'),
			'CATEGORIES' 	=> array('cat_id'),
			'CLASS'		=> array('private'),
			'EMAIL'		=> array('email'),
			'N'		=> array('n_family','n_given','','',''),
			'FN'		=> array('n_fn'),
			'NOTE'		=> array('note'),
			'ORG'		=> array('org_name',''),
			'TEL;CELL;WORK'	=> array('tel_cell'),
			'TEL;FAX;WORK'	=> array('tel_fax'),
			'TEL;HOME'	=> array('tel_home'),
			'TEL;WORK'	=> array('tel_work'),
			'TITLE'		=> array('title'),
			'URL;WORK'	=> array('url'),
			'UID'       => array('uid'),
		);

		$defaultFields[3] = array(	// siemens
			'ADR;WORK'	=> array('','','adr_one_street','adr_one_locality','adr_one_region',
							'adr_one_postalcode','adr_one_countryname'),
			'ADR;HOME'	=> array('','','adr_two_street','adr_two_locality','adr_two_region',
							'adr_two_postalcode','adr_two_countryname'),
			'BDAY'		=> array('bday'),
			'EMAIL;INTERNET;WORK' => array('email'),
			'EMAIL;INTERNET;HOME' => array('email_home'),
			'N'		=> array('n_family','n_given','','',''),
			'FN'		=> array('n_fn'),
			'NOTE'		=> array('note'),
			'ORG'		=> array('org_name'), // only one company field is supported
			'TEL;CELL;WORK'	=> array('tel_cell'),
			'TEL;FAX;WORK'	=> array('tel_fax'),
			'TEL;HOME'	=> array('tel_home'),
			'TEL;PAGER;WORK' => array('tel_pager'),
			'TEL;WORK'	=> array('tel_work'),
			'TITLE'		=> array('title'),
			'URL;WORK'	=> array('url'),
			'UID'       => array('uid'),
		);

		$defaultFields[4] = array(	// nokia 6600
			'ADR;WORK'	=> array('','','adr_one_street','adr_one_locality','adr_one_region',
							'adr_one_postalcode','adr_one_countryname'),
			'ADR;HOME'	=> array('','','adr_two_street','adr_two_locality','adr_two_region',
							'adr_two_postalcode','adr_two_countryname'),
			'BDAY;TYPE=BASIC'		=> array('bday'),
			'EMAIL;INTERNET;WORK' => array('email'),
			'EMAIL;INTERNET;HOME' => array('email_home'),
			'N'		=> array('n_family','n_given','','',''),
			'FN'		=> array('n_fn'),
			'NOTE'		=> array('note'),
			'ORG'		=> array('org_name',''),
			'TEL;CELL;WORK'	=> array('tel_cell'),
			'TEL;CELL;HOME'	=> array('tel_cell_private'),
			'TEL;FAX;WORK'	=> array('tel_fax'),
			'TEL;FAX;HOME'	=> array('tel_fax_home'),
			'TEL;HOME'	=> array('tel_home'),
			'TEL;PAGER;WORK' => array('tel_pager'),
			'TEL;WORK'	=> array('tel_work'),
			'TITLE'		=> array('title'),
			'URL;WORK'	=> array('url'),
			'URL;HOME'	=> array('url_home'),
			'UID'       => array('uid'),
		);

		$defaultFields[5] = array(	// nokia e61
			'ADR;WORK'	=> array('','','adr_one_street','adr_one_locality','adr_one_region',
							'adr_one_postalcode','adr_one_countryname'),
			'ADR;HOME'	=> array('','','adr_two_street','adr_two_locality','adr_two_region',
							'adr_two_postalcode','adr_two_countryname'),
			'BDAY;TYPE=BASIC'		=> array('bday'),
			'EMAIL;INTERNET;WORK' => array('email'),
			'EMAIL;INTERNET;HOME' => array('email_home'),
			'N'		=> array('n_family','n_given','','n_prefix','n_suffix'),
			'FN'		=> array('n_fn'),
			'NOTE'		=> array('note'),
			'ORG'		=> array('org_name',''),
			'TEL;CELL;WORK'	=> array('tel_cell'),
			'TEL;CELL;HOME'	=> array('tel_cell_private'),
			'TEL;FAX;WORK'	=> array('tel_fax'),
			'TEL;FAX;HOME'	=> array('tel_fax_home'),
			'TEL;HOME'	=> array('tel_home'),
			'TEL;PAGER;WORK' => array('tel_pager'),
			'TEL;WORK'	=> array('tel_work'),
			'TITLE'		=> array('title'),
			'URL;WORK'	=> array('url'),
			'URL;HOME'	=> array('url_home'),
			'UID'       => array('uid'),
		);

		$defaultFields[6] = array(	// funambol: fmz-thunderbird-plugin
			'ADR;WORK'      => array('','','adr_one_street','adr_one_locality','adr_one_region',
									'adr_one_postalcode','adr_one_countryname'),
			'ADR;HOME'      => array('','','adr_two_street','adr_two_locality','adr_two_region',
									'adr_two_postalcode','adr_two_countryname'),
			'EMAIL'         => array('email'),
			'EMAIL;HOME'    => array('email_home'),
			'N'             => array('n_family','n_given','','',''),
			'FN'		=> array('n_fn'),
			'NOTE'          => array('note'),
			'ORG'           => array('org_name','org_unit'),
			'TEL;CELL'      => array('tel_cell'),
			'TEL;HOME;FAX'  => array('tel_fax'),
			'TEL;HOME;VOICE' => array('tel_home'),
			'TEL;PAGER'     => array('tel_pager'),
			'TEL;WORK;VOICE' => array('tel_work'),
			'TITLE'         => array('title'),
			'URL;WORK'      => array('url'),
			'URL'           => array('url_home'),
		);

		$defaultFields[7] = array(	// SyncEvolution
			'N'=>		array('n_family','n_given','n_middle','n_prefix','n_suffix'),
			'TITLE'		=> array('title'),
			'ROLE'		=> array('role'),
			'ORG'		=> array('org_name','org_unit','room'),
			'ADR;WORK'	=> array('','adr_one_street2','adr_one_street','adr_one_locality','adr_one_region', 'adr_one_postalcode','adr_one_countryname'),
			'ADR;HOME'	=> array('','adr_two_street2','adr_two_street','adr_two_locality','adr_two_region', 'adr_two_postalcode','adr_two_countryname'),
			'TEL;WORK;VOICE'	=> array('tel_work'),
			'TEL;HOME;VOICE'	=> array('tel_home'),
			'TEL;CELL;WORK'	=> array('tel_cell'),
			'TEL;FAX;WORK'	=> array('tel_fax'),
			'TEL;FAX;HOME'	=> array('tel_fax_home'),
			'TEL;PAGER;WORK' => array('tel_pager'),
			'TEL;CAR'	=> array('tel_car'),
			'TEL;VOICE'	=> array('tel_other'),
			'EMAIL;INTERNET;WORK'	=> array('email'),
			'EMAIL;INTERNET;HOME'	=> array('email_home'),
			'URL;WORK'		=> array('url'),
			'BDAY'		=> array('bday'),
			'CATEGORIES'	=> array('cat_id'),
			'NOTE'		=> array('note'),
			'X-EVOLUTION-ASSISTANT'		=> array('assistent'),
			'PHOTO'		=> array('jpegphoto'),
			'UID'       => array('uid'),
		);

		$defaultFields[8] = array_merge($defaultFields[1],array(	// KDE Addressbook, only changes from all=1
			'ORG' => array('org_name'),
			'X-KADDRESSBOOK-X-Department' => array('org_unit'),
		));

		$defaultFields[9] = array(	// nokia e90
			'ADR;WORK'	=> array('','adr_one_street2','adr_one_street','adr_one_locality','adr_one_region',
							'adr_one_postalcode','adr_one_countryname'),
			'ADR;HOME'	=> array('','adr_two_street2','adr_two_street','adr_two_locality','adr_two_region',
							'adr_two_postalcode','adr_two_countryname'),
			'BDAY;TYPE=BASIC'		=> array('bday'),
			'X-CLASS'	=> array('private'),
			'EMAIL;INTERNET;WORK' => array('email'),
			'EMAIL;INTERNET;HOME' => array('email_home'),
			'N'		=> array('n_family','n_given','n_middle','n_prefix','n_suffix'),
			'FN'		=> array('n_fn'),
			'NOTE'		=> array('note'),
			'ORG'		=> array('org_name','org_unit'),
			'TEL;CELL;WORK'	=> array('tel_cell'),
			'TEL;CELL;HOME'	=> array('tel_cell_private'),
			'TEL;FAX;WORK'	=> array('tel_fax'),
			'TEL;FAX;HOME'	=> array('tel_fax_home'),
			'TEL;CAR'	=> array('tel_car'),
			'TEL;PAGER;WORK' => array('tel_pager'),
			'TEL;VOICE;WORK' => array('tel_work'),
			'TEL;VOICE;HOME' => array('tel_home'),
			'TITLE'		=> array('title'),
			'URL;WORK'	=> array('url'),
			'URL;HOME'	=> array('url_home'),
			'X-ASSISTANT'		=> array('assistent'),
			'X-ASSISTANT-TEL'	=> array('tel_assistent'),
			'PHOTO'		=> array('jpegphoto'),
			'UID'       => array('uid'),
		);

		$defaultFields[10] = array(	// nokia 9300
			'ADR;WORK'	=> array('','','adr_one_street','adr_one_locality','adr_one_region',
							'adr_one_postalcode','adr_one_countryname'),
			'ADR;HOME'	=> array('','','adr_two_street','adr_two_locality','adr_two_region',
							'adr_two_postalcode','adr_two_countryname'),
			'BDAY'		=> array('bday'),
			'EMAIL;INTERNET' => array('email'),
			'N'		=> array('n_family','n_given','n_middle','n_prefix','n_suffix'),
			'FN'		=> array('n_fn'),
			'NOTE'		=> array('note'),
			'ORG'		=> array('org_name','org_unit'),
			'TEL;CELL'	=> array('tel_cell'),
			'TEL;WORK;FAX'	=> array('tel_fax'),
			'TEL;FAX'	=> array('tel_fax_home'),
			'TEL;PAGER' => array('tel_pager'),
			'TEL;WORK;VOICE' => array('tel_work'),
			'TEL;HOME;VOICE' => array('tel_home'),
			'TITLE'		=> array('contact_role'),
			'URL'	=> array('url'),
			'UID'       => array('uid'),
		);

		$defaultFields[11] = array(	// funambol: iphone, blackberry, wm pocket pc
			'ADR;WORK'      => array('','','adr_one_street','adr_one_locality','adr_one_region',
									'adr_one_postalcode','adr_one_countryname'),
			'ADR;HOME'      => array('','','adr_two_street','adr_two_locality','adr_two_region',
									'adr_two_postalcode','adr_two_countryname'),
			'BDAY'		=> array('bday'),
			'CATEGORIES'	=> array('cat_id'),
			'EMAIL;INTERNET;WORK'         => array('email'),
			'EMAIL;INTERNET;HOME'    => array('email_home'),
			'N'		=> array('n_family','n_given','n_middle','n_prefix','n_suffix'),
			'FN'		=> array('n_fn'),
			'NOTE'          => array('note'),
			'ORG'           => array('org_name','org_unit'),
			'TEL;CELL'      => array('tel_cell'),
			'TEL;FAX;HOME'  => array('tel_fax_home'),
			'TEL;FAX;WORK'  => array('tel_fax'),
			'TEL;VOICE;HOME' => array('tel_home'),
			'TEL;VOICE;WORK' => array('tel_work'),
			'TEL;PAGER'     => array('tel_pager'),
			'TEL;CAR'	=> array('tel_car'),
			'TITLE'         => array('title'),
			'URL;WORK'      => array('url'),
			'URL;HOME'	=> array('url_home'),
			'PHOTO'		=> array('jpegphoto'),
			'UID'       => array('uid'),
		);

		$defaultFields[12] = array(	// Synthesis 4 iPhone
			'ADR;WORK'	=> array('','','adr_one_street','adr_one_locality','adr_one_region',
							'adr_one_postalcode','adr_one_countryname'),
			'ADR;HOME'	=> array('','','adr_two_street','adr_two_locality','adr_two_region',
							'adr_two_postalcode','adr_two_countryname'),
			'BDAY'		=> array('bday'),
			'CATEGORIES'	=> array('cat_id'),
			'EMAIL;WORK;INTERNET' => array('email'),
			'EMAIL;HOME;INTERNET' => array('email_home'),
			'N'		=> array('n_family','n_given','n_middle','n_prefix','n_suffix'),
			'FN'		=> array('n_fn'),
			'NOTE'		=> array('note'),
			'ORG'		=> array('org_name','org_unit'),
			'TEL;VOICE;CELL'	=> array('tel_cell'),
			'TEL;WORK;FAX'		=> array('tel_fax'),
			'TEL;HOME;FAX'		=> array('tel_fax_home'),
			'TEL;WORK;VOICE'	=> array('tel_work'),
			'TEL;HOME;VOICE'	=> array('tel_home'),
			'TEL;PAGER'		=> array('tel_pager'),
			'TEL;X-CustomLabel-car'	=> array('tel_car'),
			'TITLE'		=> array('title'),
			'URL;WORK'	=> array('url'),
			'ROLE'		=> array('role'),
			'URL;HOME'	=> array('url_home'),
			'PHOTO'		=> array('jpegphoto'),
			'UID'       => array('uid'),
		);

		$defaultFields[13] = array(	// sonyericsson
			'ADR;WORK'	=> array('','','adr_one_street','adr_one_locality','adr_one_region',
							'adr_one_postalcode','adr_one_countryname'),
			'ADR;HOME'	=> array('','','adr_two_street','adr_two_locality','adr_two_region',
							'adr_two_postalcode','adr_two_countryname'),
			'BDAY'		=> array('bday'),
			'EMAIL;WORK'	=> array('email'),
			'EMAIL;HOME'	=> array('email_home'),
			'N'		=> array('n_family','n_given','n_middle','n_prefix','n_suffix'),
			'NOTE'		=> array('note'),
			'ORG'		=> array('org_name',''),
			'TEL;CELL;WORK'	=> array('tel_cell'),
			'TEL;CELL;HOME'	=> array('tel_cell_private'),
			'TEL;FAX'	=> array('tel_fax'),
			'TEL;HOME'	=> array('tel_home'),
			'TEL;WORK'	=> array('tel_work'),
			'TITLE'		=> array('title'),
			'URL'		=> array('url'),
			'UID'       => array('uid'),
			//'PHOTO'		=> array('jpegphoto'),
		);

		$defaultFields[14] = array(	// Funambol Outlook Sync Client
			'ADR;WORK'      => array('','','adr_one_street','adr_one_locality','adr_one_region',
									'adr_one_postalcode','adr_one_countryname'),
			'ADR;HOME'      => array('','','adr_two_street','adr_two_locality','adr_two_region',
									'adr_two_postalcode','adr_two_countryname'),
			'BDAY'		=> array('bday'),
			'CATEGORIES'	=> array('cat_id'),
			'EMAIL;INTERNET'         => array('email'),
			'EMAIL;INTERNET;HOME'    => array('email_home'),
			'N'		=> array('n_family','n_given','n_middle','n_prefix','n_suffix'),
			'FN'			=> array('n_fn'),
			'NOTE'          => array('note'),
			'ORG'           => array('org_name','org_unit','room'),
			'ROLE'			=> array('role'),
			'CLASS'			=> array('private'),
			'NICKNAME'		=> array('label'),
			'TEL;CELL'      => array('tel_cell'),
			'TEL;HOME;FAX'  => array('tel_fax_home'),
			'TEL;WORK;FAX'  => array('tel_fax'),
			'TEL;VOICE;HOME' => array('tel_home'),
			'TEL;VOICE;WORK' => array('tel_work'),
			'TEL;PAGER'     => array('tel_pager'),
			'TEL;CAR;VOICE'	=> array('tel_car'),
			'TITLE'         => array('title'),
			'URL'      		=> array('url'),
			'URL;HOME'		=> array('url_home'),
			'PHOTO'			=> array('jpegphoto'),
		);

		//error_log("Client: $_productManufacturer $_productName");
		switch ($this->productManufacturer)
		{
			case 'funambol':
			case 'funambol inc.':
				switch ($this->productName)
				{
					case 'thunderbird':
					case 'mozilla plugin':
						$this->supportedFields = $defaultFields[6];
						break;

					case 'pocket pc plug-in':
					case 'blackberry plug-in':
					case 'iphone':
						$this->supportedFields = $defaultFields[11];
						break;

					case 'outlook sync client v.':
						$this->supportedFields = $defaultFields[14];
						break;

					default:
						error_log("Funambol product '$this->productName', assuming same as thunderbird");
						$this->supportedFields = $defaultFields[6];
						break;
				}
				break;

			case 'nexthaus corporation':
			case 'nexthaus corp':
				switch ($this->productName)
				{
					case 'syncje outlook edition':
						$this->supportedFields = $defaultFields[1];
						break;
					default:
						error_log("Nexthaus product '$this->productName', assuming same as 'syncje outlook'");
						$this->supportedFields = $defaultFields[1];
						break;
				}
				break;

			case 'nokia':
				switch ($this->productName)
				{
					case 'e61':
						$this->supportedFields = $defaultFields[5];
						break;
					case 'e51':
					case 'e90':
					case 'e71':
					case 'n95':
						$this->supportedFields = $defaultFields[9];
						break;
					case '9300':
						$this->supportedFields = $defaultFields[10];
						break;
					case '6600':
						$this->supportedFields = $defaultFields[4];
						break;
					case 'nokia 6131':
						$this->supportedFields = $defaultFields[4];
						break;
					default:
						error_log("Unknown Nokia phone 'this->$productName', assuming same as '6600'");
						$this->supportedFields = $defaultFields[4];
						break;
				}
				break;


			// multisync does not provide anymore information then the manufacturer
			// we suppose multisync with evolution
			case 'the multisync project':
				switch ($this->productName)
				{
					default:
						$this->supportedFields = $defaultFields[0];
						break;
				}
				break;

			case 'siemens':
				switch ($this->productName)
				{
					case 'sx1':
						$this->supportedFields = $defaultFields[3];
						break;
					default:
						error_log("Unknown Siemens phone '$this->productName', assuming same as 'sx1'");
						$this->supportedFields = $defaultFields[3];
						break;
				}
				break;

			case 'sonyericsson':
			case 'sony ericsson':
				switch ($this->productName)
				{
					case 'p910i':
					case 'd750i':
						$this->supportedFields = $defaultFields[2];
						break;
					case 'w760i':
					case 'w890i':
						$this->supportedFields = $defaultFields[13];
						break;
					default:
						if ($this->productName[0] == 'w')
						{
							error_log("unknown Sony Ericsson phone '$this->productName', assuming same as 'W760i'");
							$this->supportedFields = $defaultFields[13];
						}
						else
						{
							error_log("unknown Sony Ericsson phone '$this->productName', assuming same as 'D750i'");
							$this->supportedFields = $defaultFields[2];
						}
						break;
				}
				break;

			case 'synthesis ag':
				switch ($this->productName)
				{
					case 'sysync client pocketpc pro':
					case 'sysync client pocketpc std':
						$this->supportedFields = $defaultFields[1];
						$this->supportedFields['TEL;CELL;CAR;VOICE'] = array('tel_car');
						break;
					case 'sysync client iphone contacts':
					case 'sysync client iphone contacts+todoz':
						$this->supportedFields = $defaultFields[12];
						break;
					default:
						error_log("Synthesis connector '$this->productName', using default fields");
						$this->supportedFields = $defaultFields[0];
						break;
				}
				break;

			case 'patrick ohly':	// SyncEvolution
				$this->supportedFields = $defaultFields[7];
				break;

			case 'file':	// used outside of SyncML, eg. by the calendar itself ==> all possible fields
				$this->supportedFields = $defaultFields[1];
				break;

			case 'groupdav':		// all GroupDAV access goes through here
				switch ($this->productName)
				{
					case 'kde':             // KDE Addressbook
						$this->supportedFields = $defaultFields[1];
						error_log(__FILE__ . ":groupdav kde 1");
						break;
					default:
						$this->supportedFields = $defaultFields[1];
				}
				break;

			// the fallback for SyncML
			default:
				error_log(__FILE__ . __METHOD__ ."\nClient not found:'" . $this->productManufacturer . "' '" . $this->productName . "'");
				$this->supportedFields = $defaultFields[0];
				break;
		}
	}

	function vcardtoegw($_vcard)
	{
		// the horde class does the charset conversion. DO NOT CONVERT HERE.
		// be as flexible as possible


		$databaseFields = array(
			'ADR;WORK'			=> array('','','adr_one_street','adr_one_locality','adr_one_region',
									'adr_one_postalcode','adr_one_countryname'),
			'ADR;HOME'			=> array('','','adr_two_street','adr_two_locality','adr_two_region',
									'adr_two_postalcode','adr_two_countryname'),
			'BDAY'				=> array('bday'),
			'X-CLASS'			=> array('private'),
			'CLASS'				=> array('private'),
			'CATEGORIES'		=> array('cat_id'),
			'EMAIL;WORK'		=> array('email'),
			'EMAIL;HOME'		=> array('email_home'),
			'N'					=> array('n_family','n_given','n_middle',
									'n_prefix','n_suffix'),
			'FN'				=> array('n_fn'),
			'NOTE'				=> array('note'),
			'ORG'				=> array('org_name','org_unit','room'),
			'TEL;CELL;WORK'		=> array('tel_cell'),
			'TEL;CELL;HOME'		=> array('tel_cell_private'),
			'TEL;CAR'			=> array('tel_car'),
			'TEL;OTHER;VOICE'	=> array('tel_other'),
			'TEL;VOICE;WORK'	=> array('tel_work'),
			'TEL;FAX;WORK'		=> array('tel_fax'),
			'TEL;HOME;VOICE'	=> array('tel_home'),
			'TEL;FAX;HOME'		=> array('tel_fax_home'),
			'TEL;PAGER'			=> array('tel_pager'),
			'TITLE'				=> array('title'),
			'URL;WORK'			=> array('url'),
			'URL;HOME'			=> array('url_home'),
			'ROLE'				=> array('role'),
			'NICKNAME'			=> array('label'),
			'FBURL'				=> array('freebusy_uri'),
			'PHOTO'				=> array('jpegphoto'),
			'X-ASSISTANT'		=> array('assistent'),
			'X-ASSISTANT-TEL'	=> array('tel_assistent'),
			'UID'				=> array('uid'),
		);

		//Horde::logMessage("vCalAddressbook vcardtoegw:\n$_vcard", __FILE__, __LINE__, PEAR_LOG_DEBUG);

		require_once(EGW_SERVER_ROOT.'/phpgwapi/inc/horde/Horde/iCalendar.php');

		$container = false;
		$vCard = Horde_iCalendar::newComponent('vcard', $container);

		if (!$vCard->parsevCalendar($_vcard, 'VCARD'))
		{
			return False;
		}
		$vcardValues = $vCard->getAllAttributes();

		if (isset($GLOBALS['egw_info']['user']['preferences']['syncml']['minimum_uid_length']))
		{
			$minimum_uid_length = $GLOBALS['egw_info']['user']['preferences']['syncml']['minimum_uid_length'];
		}
		else
		{
			$minimum_uid_length = 8;
		}

		#print "<pre>$_vcard</pre>";

		#error_log(print_r($vcardValues, true));
		//Horde::logMessage("vCalAddressbook vcardtoegw: " . print_r($vcardValues, true), __FILE__, __LINE__, PEAR_LOG_DEBUG);

		$email = 1;
		$tel = 1;
		$cell = 1;
		$url = 1;
		$pref_tel = false;

		foreach($vcardValues as $key => $vcardRow)
		{
			$rowName  = strtoupper($vcardRow['name']);
			if ($vcardRow['value'] == ''  && implode('', $vcardRow['values']) == '')
			{
				unset($vcardRow);
				continue;
			}
			$rowTypes = array();

			$vcardRow['uparams'] = array();
			foreach ($vcardRow['params'] as $pname => $params)
			{
				$pname = strtoupper($pname);
				$vcardRow['uparams'][$pname] = $params;
			}


			// expand 3.0 TYPE paramters to 2.1 qualifiers
			$vcardRow['tparams'] = array();
			foreach ($vcardRow['uparams'] as $pname => $params)
			{
				switch ($pname)
				{
					case 'TYPE':
						if (is_array($params))
						{
							$rowTypes = array();
							foreach ($params as $param)
							{
								$rowTypes[] = strtoupper($param);
							}
						}
						else
						{
							$rowTypes[] = strtoupper($params);
						}
						foreach ($rowTypes as $type)
						{
							switch ($type)
							{

								case 'OTHER':
								case 'WORK':
								case 'HOME':
									$vcardRow['tparams'][$type] = '';
									break;
								case 'CELL':
								case 'PAGER':
								case 'FAX':
								case 'VOICE':
								case 'CAR':
								case 'PREF':
								case 'X-CUSTOMLABEL-CAR':
									if ($vcardRow['name'] == 'TEL')
									{
										$vcardRow['tparams'][$type] = '';
									}
								default:
									break;
							}
						}
						break;
					default:
						break;
				}
			}

			$vcardRow['uparams'] += $vcardRow['tparams'];
			ksort($vcardRow['uparams']);

			foreach ($vcardRow['uparams'] as $pname => $params)
			{
				switch ($pname)
				{
					case 'PREF':
						if ($rowName == 'TEL' && !$pref_tel)
						{
							$pref_tel = $key;
						}
						break;
					case 'FAX':
					case 'PAGER':
					case 'VOICE':
					case 'OTHER':
					case 'CELL':
					case 'WORK':
					case 'HOME':
						$rowName .= ';' . $pname;
						break;
					case 'CAR':
					case 'X-CUSTOMLABEL-CAR':
						if ($rowName == 'TEL')
						{
							$rowName = 'TEL;CAR';
						}
						break;
					default:
						if (strpos($pname, 'X-FUNAMBOL-') === 0)
						{
							// Propriatary Funambol extension will be ignored
							$rowName .= ';' . $pname;
						}
						break;
				}
			}

			if($rowName == 'EMAIL')
			{
				$rowName .= ';X-egw-Ref' . $email++;
			}

			if(($rowName == 'TEL;CELL') ||
					($rowName == 'TEL;CELL;VOICE'))
			{
				$rowName = 'TEL;CELL;X-egw-Ref' . $cell++;
			}

			if(($rowName == 'TEL') ||
					($rowName == 'TEL;VOICE'))
			{
				$rowName = 'TEL;X-egw-Ref' . $tel++;
			}

			if($rowName == 'URL')
			{
				$rowName = 'URL;X-egw-Ref' . $url++;
			}

			$rowNames[$key] = $rowName;
		}



               if($this->log)error_log(__LINE__.__METHOD__.__FILE__."\n".array2string($rowNames)."\n",3,$this->logfile);
             
	       // All rowNames of the vCard are now concatenated with their qualifiers.
               // If qualifiers are missing we apply a default strategy.
               // E.g. ADR will be either ADR;WORK, if no ADR;WORK is given,
               // or else ADR;HOME, if not available elsewhere.

		//error_log(print_r($rowNames, true));


		$finalRowNames = array();

		foreach ($rowNames as $vcardKey => $rowName)
		{
			switch($rowName)
			{
				case 'VERSION':
					break;
				case 'ADR':
					if (!in_array('ADR;WORK', $rowNames)
							&& !isset($finalRowNames['ADR;WORK']))
					{
						$finalRowNames['ADR;WORK'] = $vcardKey;
					}
					elseif (!in_array('ADR;HOME', $rowNames)
							&& !isset($finalRowNames['ADR;HOME']))
					{
						$finalRowNames['ADR;HOME'] = $vcardKey;
					}
					break;
				case 'TEL;FAX':
					if (!in_array('TEL;FAX;WORK', $rowNames)
							&& !isset($finalRowNames['TEL;FAX;WORK']))
					{
						$finalRowNames['TEL;FAX;WORK'] = $vcardKey;
					}
					elseif (!in_array('TEL;FAX;HOME', $rowNames)
						&& !isset($finalRowNames['TEL;FAX;HOME']))
					{
						$finalRowNames['TEL;FAX;HOME'] = $vcardKey;
					}
					break;
				case 'TEL;WORK':
					if (!in_array('TEL;VOICE;WORK', $rowNames)
							&& !isset($finalRowNames['TEL;VOICE;WORK']))
					{
						$finalRowNames['TEL;VOICE;WORK'] = $vcardKey;
					}
					break;
				case 'TEL;HOME':
					if (!in_array('TEL;HOME;VOICE', $rowNames)
							&& !isset($finalRowNames['TEL;HOME;VOICE']))
					{
						$finalRowNames['TEL;HOME;VOICE'] = $vcardKey;
					}
					break;
				case 'TEL;OTHER':
				    if (!in_array('TEL;OTHER;VOICE', $rowNames)
							&& !isset($finalRowNames['TEL;OTHER;VOICE']))
					{
						$finalRowNames['TEL;OTHER;VOICE'] = $vcardKey;
					}
					break;
				case 'TEL;CAR;VOICE':
				case 'TEL;CAR;CELL':
				case 'TEL;CAR;CELL;VOICE':
					if (!isset($finalRowNames['TEL;CAR']))
					{
						$finalRowNames['TEL;CAR'] = $vcardKey;
					}
					break;
				case 'TEL;X-egw-Ref1':
					if (!in_array('TEL;VOICE;WORK', $rowNames)
							&& !in_array('TEL;WORK', $rowNames)
							&& !isset($finalRowNames['TEL;VOICE;WORK']))
					{
						$finalRowNames['TEL;VOICE;WORK'] = $vcardKey;
						break;
					}
				case 'TEL;X-egw-Ref2':
					if (!in_array('TEL;HOME;VOICE', $rowNames)
							&& !in_array('TEL;HOME', $rowNames)
							&& !isset($finalRowNames['TEL;HOME;VOICE']))
					{
						$finalRowNames['TEL;HOME;VOICE'] = $vcardKey;
					}
					break;
				case 'TEL;CELL;X-egw-Ref1':
					if (!in_array('TEL;CELL;WORK', $rowNames)
							&& !isset($finalRowNames['TEL;CELL;WORK']))
					{
						$finalRowNames['TEL;CELL;WORK'] = $vcardKey;
						break;
					}
				case 'TEL;CELL;X-egw-Ref2':
					if (!in_array('TEL;CELL;HOME', $rowNames)
							&& !isset($finalRowNames['TEL;CELL;HOME']))
					{
						$finalRowNames['TEL;CELL;HOME'] = $vcardKey;
						break;
					}
				case 'TEL;CELL;X-egw-Ref3':
					if (!in_array($rowNames['TEL;CAR'])
							&& !in_array('TEL;CAR;VOICE', $rowNames)
							&& !in_array('TEL;CAR;CELL', $rowNames)
							&& !in_array('TEL;CAR;CELL;VOICE', $rowNames)
							&& !isset($finalRowNames['TEL;CAR']))
					{
						$finalRowNames['TEL;CAR'] = $vcardKey;
					}
					break;
				case 'EMAIL;X-egw-Ref1':
					if (!in_array('EMAIL;WORK', $rowNames) &&
							!isset($finalRowNames['EMAIL;WORK']))
					{
						$finalRowNames['EMAIL;WORK'] = $vcardKey;
						break;
					}
				case 'EMAIL;X-egw-Ref2':
					if (!in_array('EMAIL;HOME', $rowNames) &&
							!isset($finalRowNames['EMAIL;HOME']))
					{
						$finalRowNames['EMAIL;HOME'] = $vcardKey;
					}
					break;
				case 'URL;X-egw-Ref1':
					if (!in_array('URL;WORK', $rowNames) &&
							!isset($finalRowNames['URL;WORK']))
					{
						$finalRowNames['URL;WORK'] = $vcardKey;
						break;
					}
				case 'URL;X-egw-Ref2':
					if (!in_array('URL;HOME', $rowNames) &&
							!isset($finalRowNames['URL;HOME']))
					{
						$finalRowNames['URL;HOME'] = $vcardKey;
					}
					break;
				case 'X-EVOLUTION-ASSISTANT':
					if (!isset($finalRowNames['X-ASSISTANT']))
					{
						$finalRowNames['X-ASSISTANT'] = $vcardKey;
					}
					break;
				default:
					if (!isset($finalRowNames[$rowName]))
					{
						$finalRowNames[$rowName] = $vcardKey;
					}
				break;
			}
		}


		if($this->log)error_log(__LINE__.__METHOD__.__FILE__."\n".array2string($finalRowNames)."\n",3,$this->logfile);

		//error_log(print_r($finalRowNames, true));


		$contact = array();

		foreach ($finalRowNames as $key => $vcardKey)
		{
			if (isset($databaseFields[$key]))
			{
				$fieldNames = $databaseFields[$key];
				foreach ($fieldNames as $fieldKey => $fieldName)
				{
					if (!empty($fieldName))
					{
						$value = trim($vcardValues[$vcardKey]['values'][$fieldKey]);
						if ($pref_tel && (($vcardKey == $pref_tel) ||
								($vcardValues[$vcardKey]['name'] == 'TEL') &&
								($vcardValues[$vcardKey]['value'] == $vcardValues[$pref_tel]['value'])))
						{
							$contact['tel_prefer'] = $fieldName;
						}
						switch($fieldName)
						{
							case 'bday':
								$contact[$fieldName] = $vcardValues[$vcardKey]['values']['year'] .
									'-' . $vcardValues[$vcardKey]['values']['month'] .
									'-' . $vcardValues[$vcardKey]['values']['mday'];
								break;

							case 'private':
								$contact[$fieldName] = (int) ( strtoupper($value) == 'PRIVATE');
								break;

							case 'cat_id':
								$contact[$fieldName] = implode(',',$this->find_or_add_categories($vcardValues[$vcardKey]['values']));
								break;

							case 'jpegphoto':
								$contact[$fieldName] = $vcardValues[$vcardKey]['value'];
								break;

							case 'note':
								// note may contain ','s but maybe this needs to be fixed in vcard parser...
								$contact[$fieldName] = $vcardValues[$vcardKey]['value'];
								break;

							case 'uid':
								if (strlen($value) < $minimum_uid_length) {
									// we don't use it
									break;
								}
							default:
								$contact[$fieldName] = $value;
							break;
						}
					}
				}
			}
		}

		$this->fixup_contact($contact);

		if($this->log)error_log(__LINE__.__METHOD__.__FILE__."'$this->productManufacturer','$this->productName'"."\n",3,$this->logfile);
		if($this->log)error_log(__LINE__.__METHOD__.__FILE__."\n".array2string($contact)."\n",3,$this->logfile);
		return $contact;
	}

	/**
	 * Exports some contacts: download or write to a file
	 *
	 * @param array $ids contact-ids
	 * @param string $file filename or null for download
	 */
	function export($ids, $file=null)
	{
		if (!$file)
		{
			$browser =& CreateObject('phpgwapi.browser');
			$browser->content_header('addressbook.vcf','text/x-vcard');
		}
		if (!($fp = fopen($file ? $file : 'php://output','w')))
		{
			return false;
		}
		foreach ($ids as $id)
		{
			fwrite($fp,$this->getVCard($id));
		}
		fclose($fp);

		if (!$file)
		{
			$GLOBALS['egw']->common->egw_exit();
		}
		return true;
	}
}
