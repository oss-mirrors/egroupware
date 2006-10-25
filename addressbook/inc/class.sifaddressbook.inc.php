<?php
/**
 * Addressbook - SIF parser
 *
 * @link http://www.egroupware.org
 * @author Lars Kneschke <lkneschke@egroupware.org>
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @package addressbook
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$ 
 */

require_once EGW_SERVER_ROOT.'/addressbook/inc/class.bocontacts.inc.php';

class sifaddressbook extends bocontacts 
{
	var $sifMapping = array(
		'Anniversary'			=> '',
		'AssistantName'			=> 'assistent',
		'AssistantTelephoneNumber'	=> 'tel_assistent',
		'BillingInformation'		=> '',
		'Birthday'			=> 'bday',
		'Body'				=> 'note',
		'Business2TelephoneNumber'	=> '',
		'BusinessAddressCity'		=> 'adr_one_locality',
		'BusinessAddressCountry'	=> 'adr_one_countryname',
		'BusinessAddressPostalCode'	=> 'adr_one_postalcode',
		'BusinessAddressPostOfficeBox'	=> 'adr_one_street2',
		'BusinessAddressState'		=> 'adr_one_region',
		'BusinessAddressStreet'		=> 'adr_one_street',
		'BusinessFaxNumber'		=> 'tel_fax',
		'BusinessTelephoneNumber'	=> 'tel_work',
		'CallbackTelephoneNumber'	=> '',
		'CarTelephoneNumber'		=> 'tel_car',
		'Categories'			=> 'cat_id',
		'Children'			=> '',
		'Companies'			=> '',
		'CompanyMainTelephoneNumber'	=> '',
		'CompanyName'			=> 'org_name',
		'ComputerNetworkName'		=> '',
		'Department'			=> 'org_unit',
		'Email1Address'			=> 'email',
		'Email1AddressType'		=> '',
		'Email2Address'			=> 'email_home',
		'Email2AddressType'		=> '',
		'Email3Address'			=> '',
		'Email3AddressType'		=> '',
		'FileAs'			=> 'n_fileas',
		'FirstName'			=> 'n_given',
		'Hobby'				=> '',
		'Home2TelephoneNumber'		=> '',
		'HomeAddressCity'		=> 'adr_two_locality',
		'HomeAddressCountry'		=> 'adr_two_countryname',
		'HomeAddressPostalCode'		=> 'adr_two_postalcode',
		'HomeAddressPostOfficeBox'	=> 'adr_two_street2',
		'HomeAddressState'		=> 'adr_two_region',
		'HomeAddressStreet'		=> 'adr_two_street',
		'HomeFaxNumber'			=> 'tel_fax_home',
		'HomeTelephoneNumber'		=> 'tel_home',
		'Importance'			=> '',
		'Initials'			=> '',
		'JobTitle'			=> 'title',
		'Language'			=> '',
		'LastName'			=> 'n_family',
		'ManagerName'			=> '',
		'MiddleName'			=> 'n_middle',
		'Mileage'			=> '',
		'MobileTelephoneNumber'		=> 'tel_cell',
		'NickName'			=> '',
		'OfficeLocation'		=> 'room',
		'OrganizationalIDNumber'	=> '',
		'OtherAddressCity'		=> '',
		'OtherAddressCountry'		=> '',
		'OtherAddressPostalCode'	=> '',
		'OtherAddressPostOfficeBox'	=> '',
		'OtherAddressState'		=> '',
		'OtherAddressStreet'		=> '',
		'OtherFaxNumber'		=> '',
		'OtherTelephoneNumber'		=> 'tel_other',
		'PagerNumber'			=> 'tel_pager',
		'PrimaryTelephoneNumber'	=> 'tel_prefer',
		'Profession'			=> 'role',
		'RadioTelephoneNumber'		=> '',
		'Sensitivity'			=> 'private',
		'Spouse'			=> '',
		'Subject'			=> '',
		'Suffix'			=> 'n_suffix',
		'TelexNumber'			=> '',
		'Title'				=> 'n_prefix',
		'WebPage'			=> 'url',
		'YomiCompanyName'		=> '',
		'YomiFirstName'			=> '',
		'YomiLastName'			=> '',
		'HomeWebPage'			=> 'url_home',
		'Folder'			=> '',
	);

	function startElement($_parser, $_tag, $_attributes) {
	}

	function endElement($_parser, $_tag) {
		if(!empty($this->sifMapping[$_tag])) {
			$this->contact[$this->sifMapping[$_tag]] = $this->sifData;
		}
		unset($this->sifData);
	}
	
	function characterData($_parser, $_data) {
		$this->sifData .= $_data;
	}
	
	function siftoegw($_sifdata) {
		$sifData	= base64_decode($_sifdata);

		#$tmpfname = tempnam('/tmp/sync/contents','sifc_');

		#$handle = fopen($tmpfname, "w");
		#fwrite($handle, $sifdata);
		#fclose($handle);

		$this->xml_parser = xml_parser_create('UTF-8');
		xml_set_object($this->xml_parser, $this);
		xml_parser_set_option($this->xml_parser, XML_OPTION_CASE_FOLDING, false);
		xml_set_element_handler($this->xml_parser, "startElement", "endElement");
		xml_set_character_data_handler($this->xml_parser, "characterData");
		$this->strXmlData = xml_parse($this->xml_parser, $sifData);
		if(!$this->strXmlData) {
			error_log(sprintf("XML error: %s at line %d",
				xml_error_string(xml_get_error_code($this->xml_parser)),
				xml_get_current_line_number($this->xml_parser)));
			return false;
		}

		foreach($this->contact as $key => $value) {
			$value = $GLOBALS['egw']->translation->convert($value, 'utf-8');
			switch($key) {
				case 'cat_id':
					if(!empty($value)) {
						$isAdmin = $GLOBALS['egw']->acl->check('run',1,'admin');
						$egwCategories =& CreateObject('phpgwapi.categories',$GLOBALS['egw_info']['user']['account_id'],'addressbook');
						$categories = explode('; ',$value);
						$cat_id = '';
						foreach($categories as $categorieName) {
							$categorieName = trim($categorieName);
							if(!($cat_id = $egwCategories->name2id($categorieName)) && $isAdmin) {
								$cat_id = $egwCategories->add(array('name' => $categorieName, 'descr' => $categorieName));
							}
							if($cat_id) {
								if(!empty($finalContact[$key])) $finalContact[$key] .= ',';
								 $finalContact[$key] .= $cat_id;
							}
						}
					}
					break;
					
				case 'private':
					$finalContact[$key] = (int) $value > 0;	// eGW private is 0 (public) or 1 (private), SIF seems to use 0 and 2
					break;

				default:
					$finalContact[$key] = $value;
					break;
			}
		}
		return $finalContact;
	}
	
	/**
	 * Search an exactly matching entry (used for slow sync)
	 *
	 * @param string $_sifdata
	 * @return boolean/int/string contact-id or false, if not found
	 */
	function search($_sifdata) 
	{
		if(!$contact = $this->siftoegw($_sifdata)) 
		{
			return false;
		}
		
		if(($foundContacts = bocontacts::search($contact)))
		{
			error_log(print_r($foundContacts,true));
			return $foundContacts[0]['id'];
		}
		return false;
	}

	/**
	* import a vard into addressbook
	*
	* @return int contact id
	* @param string	$_vcard		the vcard
	* @param int/string	$_abID=null		the internal addressbook id or !$_abID for a new enty
	*/
	function addSIF($_sifdata, $_abID=null)
	{
		#error_log('ABID: '.$_abID);
		#error_log(base64_decode($_sifdata));
		
		if(!$contact = $this->siftoegw($_sifdata)) {
			return false;
		}

		if($_abID) {
			// update entry
			$contact['id'] = $_abID;
		}
		return $this->save($contact);
	}

	/**
	* return a vcard
	*
	* @param int	$_id		the id of the contact
	* @param int	$_vcardProfile	profile id for mapping from vcard values to egw addressbook
	* @return string containing the vcard
	*/
	function getSIF($_id)
	{
		$fields = array_unique(array_values($this->sifMapping));
		sort($fields);

		if(!($entry = $this->read($_id)))
		{
			return false;
		}
		$sifContact = '<contact>';
		#error_log(print_r($entry,true));
		$sysCharSet	= $GLOBALS['egw']->translation->charset();

		foreach($this->sifMapping as $sifField => $egwField)
		{
			if(empty($egwField)) continue;
			
			#error_log("$sifField => $egwField");
			#error_log('VALUE1: '.$entry[0][$egwField]);
			$value = $GLOBALS['egw']->translation->convert($entry[$egwField], $sysCharSet, 'utf-8');
			#error_log('VALUE2: '.$value);

			switch($sifField)
			{
				// TODO handle multiple categories
				case 'Categories':
					if(!empty($value)) {
						$egwCategories =& CreateObject('phpgwapi.categories',$GLOBALS['egw_info']['user']['account_id'],'addressbook');
						$categories = explode(',',$value);
						$value = '';
						foreach($categories as $cat_id) {
							if(($catData = $egwCategories->return_single($cat_id)))
							{
								if(!empty($value)) $value .= '; ';
								$value .= $catData[0]['name'];
							}
						}
					}
					$sifContact .= "<$sifField>$value</$sifField>";							
					break;
					
				case 'Sensitivity':
					$value = 2 * $value;	// eGW private is 0 (public) or 1 (private)
					$sifContact .= "<$sifField>$value</$sifField>";							
					break;
					
				case 'Folder':
					# skip currently. This is the folder where Outlook stores the contact.
					#$sifContact .= "<$sifField>/</$sifField>";
					break;
					
				default:
					$sifContact .= "<$sifField>$value</$sifField>";
					break;
			}
		}
		$sifContact .= "</contact>";

		return base64_encode($sifContact);
	}
}
