<?php
	/**************************************************************************\
	* phpGroupWare - eTemplate Extension - Select Widgets                      *
	* http://www.phpgroupware.org                                              *
	* Written by Ralf Becker <RalfBecker@outdoor-training.de>                  *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	/*!
	@class select_widget
	@author ralfbecker
	@abstract Several select-boxes with predefined phpgw specific content.
	@discussion This widget replaces the old sbox class
	@discussion This widget is independent of the UI as it only uses etemplate-widgets and has therefor no render-function
	*/
	class select_widget
	{
		var $public_functions = array(
			'pre_process' => True
		);
		var $human_name = array(	// this are the names for the editor
			'select-percent'  => 'Select Percentage',
			'select-priority' => 'Select Priority',
			'select-access'   => 'Select Access',
			'select-country'  => 'Select Country',
			'select-state'    => 'Select State',	// US-states
			'select-cat'      => 'Select Category',// Category-Selection, size: -1=Single+All, 0=Single, >0=Multiple with size lines
			'select-account'  => 'Select Account',	// label=accounts(default),groups,both
																// size: -1=Single+not assigned, 0=Single, >0=Multiple
			'select-month'    => 'Select Month'
		);
		var $monthnames = array(
			0  => '',
			1  => 'January',
			2  => 'February',
			3  => 'March',
			4  => 'April',
			5  => 'May',
			6  => 'June',
			7  => 'July',
			8  => 'August',
			9  => 'September',
			10 => 'October',
			11 => 'November',
			12 => 'December'
		);

		var $countrys = array(
			''  =>'',
			'AF'=>'AFGHANISTAN',
			'AL'=>'ALBANIA',
			'DZ'=>'ALGERIA',
			'AS'=>'AMERICAN SAMOA',
			'AD'=>'ANDORRA',
			'AO'=>'ANGOLA',
			'AI'=>'ANGUILLA',
			'AQ'=>'ANTARCTICA',
			'AG'=>'ANTIGUA AND BARBUDA',
			'AR'=>'ARGENTINA',
			'AM'=>'ARMENIA',
			'AW'=>'ARUBA',
			'AU'=>'AUSTRALIA',
			'AT'=>'AUSTRIA',
			'AZ'=>'AZERBAIJAN',
			'BS'=>'BAHAMAS',
			'BH'=>'BAHRAIN',
			'BD'=>'BANGLADESH',
			'BB'=>'BARBADOS',
			'BY'=>'BELARUS',
			'BE'=>'BELGIUM',
			'BZ'=>'BELIZE',
			'BJ'=>'BENIN',
			'BM'=>'BERMUDA',
			'BT'=>'BHUTAN',
			'BO'=>'BOLIVIA',
			'BA'=>'BOSNIA AND HERZEGOVINA',
			'BW'=>'BOTSWANA',
			'BV'=>'BOUVET ISLAND',
			'BR'=>'BRAZIL',
			'IO'=>'BRITISH INDIAN OCEAN TERRITORY',
			'BN'=>'BRUNEI DARUSSALAM',
			'BG'=>'BULGARIA',
			'BF'=>'BURKINA FASO',
			'BI'=>'BURUNDI',
			'KH'=>'CAMBODIA',
			'CM'=>'CAMEROON',
			'CA'=>'CANADA',
			'CV'=>'CAPE VERDE',
			'KY'=>'CAYMAN ISLANDS',
			'CF'=>'CENTRAL AFRICAN REPUBLIC',
			'TD'=>'CHAD',
			'CL'=>'CHILE',
			'CN'=>'CHINA',
			'CX'=>'CHRISTMAS ISLAND',
			'CC'=>'COCOS (KEELING) ISLANDS',
			'CO'=>'COLOMBIA',
			'KM'=>'COMOROS',
			'CG'=>'CONGO',
			'CD'=>'CONGO, THE DEMOCRATIC REPUBLIC OF THE',
			'CK'=>'COOK ISLANDS',
			'CR'=>'COSTA RICA',
			'CI'=>'COTE D IVOIRE',
			'HR'=>'CROATIA',
			'CU'=>'CUBA',
			'CY'=>'CYPRUS',
			'CZ'=>'CZECH REPUBLIC',
			'DK'=>'DENMARK',
			'DJ'=>'DJIBOUTI',
			'DM'=>'DOMINICA',
			'DO'=>'DOMINICAN REPUBLIC',
			'TP'=>'EAST TIMOR',
			'EC'=>'ECUADOR',
			'EG'=>'EGYPT',
			'SV'=>'EL SALVADOR',
			'GQ'=>'EQUATORIAL GUINEA',
			'ER'=>'ERITREA',
			'EE'=>'ESTONIA',
			'ET'=>'ETHIOPIA',
			'FK'=>'FALKLAND ISLANDS (MALVINAS)',
			'FO'=>'FAROE ISLANDS',
			'FJ'=>'FIJI',
			'FI'=>'FINLAND',
			'FR'=>'FRANCE',
			'GF'=>'FRENCH GUIANA',
			'PF'=>'FRENCH POLYNESIA',
			'TF'=>'FRENCH SOUTHERN TERRITORIES',
			'GA'=>'GABON',
			'GM'=>'GAMBIA',
			'GE'=>'GEORGIA',
			'DE'=>'GERMANY',
			'GH'=>'GHANA',
			'GI'=>'GIBRALTAR',
			'GR'=>'GREECE',
			'GL'=>'GREENLAND',
			'GD'=>'GRENADA',
			'GP'=>'GUADELOUPE',
			'GU'=>'GUAM',
			'GT'=>'GUATEMALA',
			'GN'=>'GUINEA',
			'GW'=>'GUINEA-BISSAU',
			'GY'=>'GUYANA',
			'HT'=>'HAITI',
			'HM'=>'HEARD ISLAND AND MCDONALD ISLANDS',
			'VA'=>'HOLY SEE (VATICAN CITY STATE)',
			'HN'=>'HONDURAS',
			'HK'=>'HONG KONG',
			'HU'=>'HUNGARY',
			'IS'=>'ICELAND',
			'IN'=>'INDIA',
			'ID'=>'INDONESIA',
			'IR'=>'IRAN, ISLAMIC REPUBLIC OF',
			'IQ'=>'IRAQ',
			'IE'=>'IRELAND',
			'IL'=>'ISRAEL',
			'IT'=>'ITALY',
			'JM'=>'JAMAICA',
			'JP'=>'JAPAN',
			'JO'=>'JORDAN',
			'KZ'=>'KAZAKSTAN',
			'KE'=>'KENYA',
			'KI'=>'KIRIBATI',
			'KP'=>'KOREA, DEMOCRATIC PEOPLES REPUBLIC OF',
			'KR'=>'KOREA, REPUBLIC OF',
			'KW'=>'KUWAIT',
			'KG'=>'KYRGYZSTAN',
			'LA'=>'LAO PEOPLES DEMOCRATIC REPUBLIC',
			'LV'=>'LATVIA',
			'LB'=>'LEBANON',
			'LS'=>'LESOTHO',
			'LR'=>'LIBERIA',
			'LY'=>'LIBYAN ARAB JAMAHIRIYA',
			'LI'=>'LIECHTENSTEIN',
			'LT'=>'LITHUANIA',
			'LU'=>'LUXEMBOURG',
			'MO'=>'MACAU',
			'MK'=>'MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF',
			'MG'=>'MADAGASCAR',
			'MW'=>'MALAWI',
			'MY'=>'MALAYSIA',
			'MV'=>'MALDIVES',
			'ML'=>'MALI',
			'MT'=>'MALTA',
			'MH'=>'MARSHALL ISLANDS',
			'MQ'=>'MARTINIQUE',
			'MR'=>'MAURITANIA',
			'MU'=>'MAURITIUS',
			'YT'=>'MAYOTTE',
			'MX'=>'MEXICO',
			'FM'=>'MICRONESIA, FEDERATED STATES OF',
			'MD'=>'MOLDOVA, REPUBLIC OF',
			'MC'=>'MONACO',
			'MN'=>'MONGOLIA',
			'MS'=>'MONTSERRAT',
			'MA'=>'MOROCCO',
			'MZ'=>'MOZAMBIQUE',
			'MM'=>'MYANMAR',
			'NA'=>'NAMIBIA',
			'NR'=>'NAURU',
			'NP'=>'NEPAL',
			'NL'=>'NETHERLANDS',
			'AN'=>'NETHERLANDS ANTILLES',
			'NC'=>'NEW CALEDONIA',
			'NZ'=>'NEW ZEALAND',
			'NI'=>'NICARAGUA',
			'NE'=>'NIGER',
			'NG'=>'NIGERIA',
			'NU'=>'NIUE',
			'NF'=>'NORFOLK ISLAND',
			'MP'=>'NORTHERN MARIANA ISLANDS',
			'NO'=>'NORWAY',
			'OM'=>'OMAN',
			'PK'=>'PAKISTAN',
			'PW'=>'PALAU',
			'PS'=>'PALESTINIAN TERRITORY, OCCUPIED',
			'PA'=>'PANAMA',
			'PG'=>'PAPUA NEW GUINEA',
			'PY'=>'PARAGUAY',
			'PE'=>'PERU',
			'PH'=>'PHILIPPINES',
			'PN'=>'PITCAIRN',
			'PL'=>'POLAND',
			'PT'=>'PORTUGAL',
			'PR'=>'PUERTO RICO',
			'QA'=>'QATAR',
			'RE'=>'REUNION',
			'RO'=>'ROMANIA',
			'RU'=>'RUSSIAN FEDERATION',
			'RW'=>'RWANDA',
			'SH'=>'SAINT HELENA',
			'KN'=>'SAINT KITTS AND NEVIS',
			'LC'=>'SAINT LUCIA',
			'PM'=>'SAINT PIERRE AND MIQUELON',
			'VC'=>'SAINT VINCENT AND THE GRENADINES',
			'WS'=>'SAMOA',
			'SM'=>'SAN MARINO',
			'ST'=>'SAO TOME AND PRINCIPE',
			'SA'=>'SAUDI ARABIA',
			'SN'=>'SENEGAL',
			'SC'=>'SEYCHELLES',
			'SL'=>'SIERRA LEONE',
			'SG'=>'SINGAPORE',
			'SK'=>'SLOVAKIA',
			'SI'=>'SLOVENIA',
			'SB'=>'SOLOMON ISLANDS',
			'SO'=>'SOMALIA',
			'ZA'=>'SOUTH AFRICA',
			'GS'=>'SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS',
			'ES'=>'SPAIN',
			'LK'=>'SRI LANKA',
			'SD'=>'SUDAN',
			'SR'=>'SURINAME',
			'SJ'=>'SVALBARD AND JAN MAYEN',
			'SZ'=>'SWAZILAND',
			'SE'=>'SWEDEN',
			'CH'=>'SWITZERLAND',
			'SY'=>'SYRIAN ARAB REPUBLIC',
			'TW'=>'TAIWAN, PROVINCE OF CHINA',
			'TJ'=>'TAJIKISTAN',
			'TZ'=>'TANZANIA, UNITED REPUBLIC OF',
			'TH'=>'THAILAND',
			'TG'=>'TOGO',
			'TK'=>'TOKELAU',
			'TO'=>'TONGA',
			'TT'=>'TRINIDAD AND TOBAGO',
			'TN'=>'TUNISIA',
			'TR'=>'TURKEY',
			'TM'=>'TURKMENISTAN',
			'TC'=>'TURKS AND CAICOS ISLANDS',
			'TV'=>'TUVALU',
			'UG'=>'UGANDA',
			'UA'=>'UKRAINE',
			'AE'=>'UNITED ARAB EMIRATES',
			'GB'=>'UNITED KINGDOM',
			'US'=>'UNITED STATES',
			'UM'=>'UNITED STATES MINOR OUTLYING ISLANDS',
			'UY'=>'URUGUAY',
			'UZ'=>'UZBEKISTAN',
			'VU'=>'VANUATU',
			'VE'=>'VENEZUELA',
			'VN'=>'VIET NAM',
			'VG'=>'VIRGIN ISLANDS, BRITISH',
			'VI'=>'VIRGIN ISLANDS, U.S.',
			'WF'=>'WALLIS AND FUTUNA',
			'EH'=>'WESTERN SAHARA',
			'YE'=>'YEMEN',
			'YU'=>'YUGOSLAVIA',
			'ZM'=>'ZAMBIA',
			'ZW'=>'ZIMBABWE'
		);

		var $states = array(
			//''		=> lang('Select one'),
			'--'	=> 'non US',
			'AL'	=>	'Alabama',
			'AK'	=>	'Alaska',
			'AZ'	=>	'Arizona',
			'AR'	=>	'Arkansas',
			'CA'	=>	'California',
			'CO'	=>	'Colorado',
			'CT'	=>	'Connecticut',
			'DE'	=>	'Delaware',
			'DC'	=>	'District of Columbia',
			'FL'	=>	'Florida',
			'GA'	=>	'Georgia',
			'HI'	=>	'Hawaii',
			'ID'	=>	'Idaho',
			'IL'	=>	'Illinois',
			'IN'	=>	'Indiana',
			'IA'	=>	'Iowa',
			'KS'	=>	'Kansas',
			'KY'	=>	'Kentucky',
			'LA'	=>	'Louisiana',
			'ME'	=>	'Maine',
			'MD'	=>	'Maryland',
			'MA'	=>	'Massachusetts',
			'MI'	=>	'Michigan',
			'MN'	=>	'Minnesota',
			'MO'	=>	'Missouri',
			'MS'	=>	'Mississippi',
			'MT'	=>	'Montana',
			'NC'	=>	'North Carolina',
			'ND'	=>	'Noth Dakota',
			'NE'	=>	'Nebraska',
			'NH'	=>	'New Hampshire',
			'NJ'	=>	'New Jersey',
			'NM'	=>	'New Mexico',
			'NV'	=>	'Nevada',
			'NY'	=>	'New York',
			'OH'	=>	'Ohio',
			'OK'	=>	'Oklahoma',
			'OR'	=>	'Oregon',
			'PA'	=>	'Pennsylvania',
			'RI'	=>	'Rhode Island',
			'SC'	=>	'South Carolina',
			'SD'	=>	'South Dakota',
			'TN'	=>	'Tennessee',
			'TX'	=>	'Texas',
			'UT'	=>	'Utah',
			'VA'	=>	'Virginia',
			'VT'	=>	'Vermont',
			'WA'	=>	'Washington',
			'WI'	=>	'Wisconsin',
			'WV'	=>	'West Virginia',
			'WY'	=>	'Wyoming'
		);

		function select_widget($ui)
		{
		}

		function pre_process($name,&$value,&$cell,&$readonlys,&$extension_data,&$tmpl)
		{
			//echo "<p>nextmatch_widget.pre_process: value = "; _debug_array($value);
			// save values in persistent extension_data to be able use it in post_process
			//$extension_data = $value;

			list($rows,$type) = explode(',',$cell['size']);

			switch ($cell['type'])
			{
				case 'select-percent':
					for ($i=0; $i <= 100; $i+=10)
					{
						$cell['sel_options'][$i] = "$i%";
					}
					$value = intval(($value+5) / 10) * 10;
					$cell['no_lang'] = True;
					break;

				case 'select-priority':
					$cell['sel_options'] = array('','low','normal','high');
					break;

				case 'select-access':
					$cell['sel_options'] = array(
						'private' => 'Private',
						'public' => 'Global public',
						'group' => 'Group public'
					);
					break;

				case 'select-country':
					$cell['sel_options'] = $this->countrys;
					$cell['no_lang'] = True;
					break;

				case 'select-state':
					$cell['sel_options'] = $this->states;
					$cell['no_lang'] = True;
					break;

				case 'select-cat':
					if (!is_object($GLOBALS['phpgw']->categories))
					{
						$GLOBALS['phpgw']->categories = CreateObject('phpgwapi.categories');
					}
					if ($type != 'all')
					{
						$cats = $GLOBALS['phpgw']->categories->return_array($type,0);
					}
					else
					{
						$cats = $GLOBALS['phpgw']->categories->return_sorted_array(0);
					}
					while (list(,$cat) = @each($cats))
					{
						for ($j=0,$s=''; $j < $cat['level']; $j++)
						{
							$s .= '&nbsp;';
						}
						$s .= $GLOBALS['phpgw']->strip_html($cat['name']);
						if ($cat['app_name'] == 'phpgw')
						{
							$s .= '&nbsp;&lt;' . lang('Global') . '&gt;';
						}
						if ($cat['owner'] == '-1')
						{
							$s .= '&nbsp;&lt;' . lang('Global') . '&nbsp;' . lang($this->app_name) . '&gt;';
						}
						$cell['sel_options'][$cat['id']] = $s;
					}
					$cell['no_lang'] = True;
					break;

				case 'select-account':
					$accs = $GLOBALS['phpgw']->accounts->get_list(empty($type) ? 'accounts' : $type); // default is accounts

					while (list(,$acc) = each($accs))
					{
						$cell['sel_options'][$acc['account_id']] = $this->accountInfo($a['account_id'],$a,$longnames,$type=='both');
					}
					$cell['no_lang'] = True;
					break;

				case 'select-month':
					$cell['sel_options'] = $this->monthnames;
					break;
			}
			return True;	// extra Label Ok
		}

		function accountInfo($id,$account_data=0,$longnames=0,$show_type=0)
		{
			if (!$id)
			{
				return '&nbsp;';
			}

			if (!is_array($account_data))
			{
				$accounts = createobject('phpgwapi.accounts',$id);
				$accounts->db = $GLOBALS['phpgw']->db;
				$accounts->read_repository();
				$account_data = $accounts->data;
			}
			$info = $show_type ? '('.$account_data['account_type'].') ' : '';

			switch ($longnames)
			{
				case 2: $info .= '&lt;'.$account_data['account_lid'].'&gt; '; // fall-through
				case 1: $info .= $account_data['account_firstname'].' '.$account_data['account_lastname']; break;
				default: $info .= $account_data['account_lid']; break;
			}
			return $info;
		}
	}