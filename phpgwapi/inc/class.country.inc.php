<?php
  /**************************************************************************\
  * eGroupWare - Calendar Holidays                                           *
  * http://www.egroupware.org                                              *
  * Written by Mark Peters <skeeter@phpgroupware.org>                        *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

	class country
	{
		var $country_array;

		function country()
		{
			$this->country_array = array(
				'  ' => 'Select One',
				'AF' => 'AFGHANISTAN', 
				'AL' => 'ALBANIA', 
				'DZ' => 'ALGERIA', 
				'AS' => 'AMERICAN SAMOA', 
				'AD' => 'ANDORRA', 
				'AO' => 'ANGOLA', 
				'AI' => 'ANGUILLA', 
				'AQ' => 'ANTARCTICA', 
				'AG' => 'ANTIGUA AND BARBUDA', 
				'AR' => 'ARGENTINA', 
				'AM' => 'ARMENIA', 
				'AW' => 'ARUBA', 
				'AU' => 'AUSTRALIA', 
				'AT' => 'AUSTRIA', 
				'AZ' => 'AZERBAIJAN', 
				'BS' => 'BAHAMAS', 
				'BH' => 'BAHRAIN', 
				'BD' => 'BANGLADESH', 
				'BB' => 'BARBADOS', 
				'BY' => 'BELARUS', 
				'BE' => 'BELGIUM', 
				'BZ' => 'BELIZE', 
				'BJ' => 'BENIN', 
				'BM' => 'BERMUDA', 
				'BT' => 'BHUTAN', 
				'BO' => 'BOLIVIA', 
				'BA' => 'BOSNIA AND HERZEGOVINA', 
				'BW' => 'BOTSWANA', 
				'BV' => 'BOUVET ISLAND', 
				'BR' => 'BRAZIL', 
				'IO' => 'BRITISH INDIAN OCEAN TERRITORY', 
				'BN' => 'BRUNEI DARUSSALAM', 
				'BG' => 'BULGARIA', 
				'BF' => 'BURKINA FASO', 
				'BI' => 'BURUNDI', 
				'KH' => 'CAMBODIA', 
				'CM' => 'CAMEROON', 
				'CA' => 'CANADA', 
				'CV' => 'CAPE VERDE', 
				'KY' => 'CAYMAN ISLANDS', 
				'CF' => 'CENTRAL AFRICAN REPUBLIC', 
				'TD' => 'CHAD', 
				'CL' => 'CHILE', 
				'CN' => 'CHINA', 
				'CX' => 'CHRISTMAS ISLAND', 
				'CC' => 'COCOS (KEELING) ISLANDS', 
				'CO' => 'COLOMBIA', 
				'KM' => 'COMOROS', 
				'CG' => 'CONGO', 
				'CD' => 'CONGO, THE DEMOCRATIC REPUBLIC OF THE', 
				'CK' => 'COOK ISLANDS', 
				'CR' => 'COSTA RICA', 
				'CI' => 'COTE D IVOIRE', 
				'HR' => 'CROATIA', 
				'CU' => 'CUBA', 
				'CY' => 'CYPRUS', 
				'CZ' => 'CZECH REPUBLIC', 
				'DK' => 'DENMARK', 
				'DJ' => 'DJIBOUTI', 
				'DM' => 'DOMINICA', 
				'DO' => 'DOMINICAN REPUBLIC', 
				'TP' => 'EAST TIMOR', 
				'EC' => 'ECUADOR', 
				'EG' => 'EGYPT', 
				'SV' => 'EL SALVADOR', 
				'GQ' => 'EQUATORIAL GUINEA', 
				'ER' => 'ERITREA', 
				'EE' => 'ESTONIA', 
				'ET' => 'ETHIOPIA', 
				'FK' => 'FALKLAND ISLANDS (MALVINAS)', 
				'FO' => 'FAROE ISLANDS', 
				'FJ' => 'FIJI', 
				'FI' => 'FINLAND', 
				'FR' => 'FRANCE', 
				'GF' => 'FRENCH GUIANA', 
				'PF' => 'FRENCH POLYNESIA', 
				'TF' => 'FRENCH SOUTHERN TERRITORIES', 
				'GA' => 'GABON', 
				'GM' => 'GAMBIA', 
				'GE' => 'GEORGIA', 
				'DE' => 'GERMANY', 
				'GH' => 'GHANA', 
				'GI' => 'GIBRALTAR', 
				'GR' => 'GREECE', 
				'GL' => 'GREENLAND', 
				'GD' => 'GRENADA', 
				'GP' => 'GUADELOUPE', 
				'GU' => 'GUAM', 
				'GT' => 'GUATEMALA', 
				'GN' => 'GUINEA', 
				'GW' => 'GUINEA-BISSAU', 
				'GY' => 'GUYANA', 
				'HT' => 'HAITI', 
				'HM' => 'HEARD ISLAND AND MCDONALD ISLANDS', 
				'VA' => 'HOLY SEE (VATICAN CITY STATE)', 
				'HN' => 'HONDURAS', 
				'HK' => 'HONG KONG', 
				'HU' => 'HUNGARY', 
				'IS' => 'ICELAND', 
				'IN' => 'INDIA', 
				'ID' => 'INDONESIA', 
				'IR' => 'IRAN, ISLAMIC REPUBLIC OF', 
				'IQ' => 'IRAQ', 
				'IE' => 'IRELAND', 
				'IL' => 'ISRAEL', 
				'IT' => 'ITALY', 
				'JM' => 'JAMAICA', 
				'JP' => 'JAPAN', 
				'JO' => 'JORDAN', 
				'KZ' => 'KAZAKSTAN', 
				'KE' => 'KENYA', 
				'KI' => 'KIRIBATI', 
				'KP' => 'KOREA DEMOCRATIC PEOPLES REPUBLIC OF', 
				'KR' => 'KOREA REPUBLIC OF', 
				'KW' => 'KUWAIT', 
				'KG' => 'KYRGYZSTAN', 
				'LA' => 'LAO PEOPLES DEMOCRATIC REPUBLIC', 
				'LV' => 'LATVIA', 
				'LB' => 'LEBANON', 
				'LS' => 'LESOTHO', 
				'LR' => 'LIBERIA', 
				'LY' => 'LIBYAN ARAB JAMAHIRIYA', 
				'LI' => 'LIECHTENSTEIN', 
				'LT' => 'LITHUANIA', 
				'LU' => 'LUXEMBOURG', 
				'MO' => 'MACAU', 
				'MK' => 'MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF', 
				'MG' => 'MADAGASCAR', 
				'MW' => 'MALAWI', 
				'MY' => 'MALAYSIA', 
				'MV' => 'MALDIVES', 
				'ML' => 'MALI', 
				'MT' => 'MALTA', 
				'MH' => 'MARSHALL ISLANDS', 
				'MQ' => 'MARTINIQUE', 
				'MR' => 'MAURITANIA', 
				'MU' => 'MAURITIUS', 
				'YT' => 'MAYOTTE', 
				'MX' => 'MEXICO', 
				'FM' => 'MICRONESIA, FEDERATED STATES OF', 
				'MD' => 'MOLDOVA, REPUBLIC OF', 
				'MC' => 'MONACO', 
				'MN' => 'MONGOLIA', 
				'MS' => 'MONTSERRAT', 
				'MA' => 'MOROCCO', 
				'MZ' => 'MOZAMBIQUE', 
				'MM' => 'MYANMAR', 
				'NA' => 'NAMIBIA', 
				'NR' => 'NAURU', 
				'NP' => 'NEPAL', 
				'NL' => 'NETHERLANDS', 
				'AN' => 'NETHERLANDS ANTILLES', 
				'NC' => 'NEW CALEDONIA', 
				'NZ' => 'NEW ZEALAND', 
				'NI' => 'NICARAGUA', 
				'NE' => 'NIGER', 
				'NG' => 'NIGERIA', 
				'NU' => 'NIUE', 
				'NF' => 'NORFOLK ISLAND', 
				'MP' => 'NORTHERN MARIANA ISLANDS', 
				'NO' => 'NORWAY', 
				'OM' => 'OMAN', 
				'PK' => 'PAKISTAN', 
				'PW' => 'PALAU', 
				'PS' => 'PALESTINIAN TERRITORY, OCCUPIED', 
				'PA' => 'PANAMA', 
				'PG' => 'PAPUA NEW GUINEA', 
				'PY' => 'PARAGUAY', 
				'PE' => 'PERU', 
				'PH' => 'PHILIPPINES', 
				'PN' => 'PITCAIRN', 
				'PL' => 'POLAND', 
				'PT' => 'PORTUGAL', 
				'PR' => 'PUERTO RICO', 
				'QA' => 'QATAR', 
				'RE' => 'REUNION', 
				'RO' => 'ROMANIA', 
				'RU' => 'RUSSIAN FEDERATION', 
				'RW' => 'RWANDA', 
				'SH' => 'SAINT HELENA', 
				'KN' => 'SAINT KITTS AND NEVIS', 
				'LC' => 'SAINT LUCIA', 
				'PM' => 'SAINT PIERRE AND MIQUELON', 
				'VC' => 'SAINT VINCENT AND THE GRENADINES', 
				'WS' => 'SAMOA', 
				'SM' => 'SAN MARINO', 
				'ST' => 'SAO TOME AND PRINCIPE', 
				'SA' => 'SAUDI ARABIA', 
				'SN' => 'SENEGAL', 
				'SC' => 'SEYCHELLES', 
				'SL' => 'SIERRA LEONE', 
				'SG' => 'SINGAPORE', 
				'SK' => 'SLOVAKIA', 
				'SI' => 'SLOVENIA', 
				'SB' => 'SOLOMON ISLANDS', 
				'SO' => 'SOMALIA', 
				'ZA' => 'SOUTH AFRICA', 
				'GS' => 'SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS', 
				'ES' => 'SPAIN', 
				'LK' => 'SRI LANKA', 
				'SD' => 'SUDAN', 
				'SR' => 'SURINAME', 
				'SJ' => 'SVALBARD AND JAN MAYEN', 
				'SZ' => 'SWAZILAND', 
				'SE' => 'SWEDEN', 
				'CH' => 'SWITZERLAND', 
				'SY' => 'SYRIAN ARAB REPUBLIC', 
				'TW' => 'TAIWAN', 
				'TJ' => 'TAJIKISTAN', 
				'TZ' => 'TANZANIA, UNITED REPUBLIC OF', 
				'TH' => 'THAILAND', 
				'TG' => 'TOGO', 
				'TK' => 'TOKELAU', 
				'TO' => 'TONGA', 
				'TT' => 'TRINIDAD AND TOBAGO', 
				'TN' => 'TUNISIA', 
				'TR' => 'TURKEY', 
				'TM' => 'TURKMENISTAN', 
				'TC' => 'TURKS AND CAICOS ISLANDS', 
				'TV' => 'TUVALU', 
				'UG' => 'UGANDA', 
				'UA' => 'UKRAINE', 
				'AE' => 'UNITED ARAB EMIRATES', 
				'GB' => 'UNITED KINGDOM', 
				'US' => 'UNITED STATES', 
				'UM' => 'UNITED STATES MINOR OUTLYING ISLANDS', 
				'UY' => 'URUGUAY', 
				'UZ' => 'UZBEKISTAN', 
				'VU' => 'VANUATU', 
				'VE' => 'VENEZUELA', 
				'VN' => 'VIET NAM', 
				'VG' => 'VIRGIN ISLANDS, BRITISH', 
				'VI' => 'VIRGIN ISLANDS, U.S.', 
				'WF' => 'WALLIS AND FUTUNA', 
				'EH' => 'WESTERN SAHARA', 
				'YE' => 'YEMEN', 
				'YU' => 'YUGOSLAVIA', 
				'ZM' => 'ZAMBIA', 
				'ZW' => 'ZIMBABWE'
			); 
		}

		function form_select($selected,$name='')
		{
			if($name=='')
			{
				$name = 'country';
			}
			$str = '<select name="'.$name.'">'."\n";
			reset($this->country_array);
			while(list($key,$value) = each($this->country_array))
			{
				$str .= ' <option value="'.$key.'"'.($selected == $key?' selected':'').'>'.$value.'</option>'."\n";
			}
			$str .= '</select>'."\n";
			return $str;
		}

		function get_full_name($selected)
		{
			return($this->country_array[$selected]);
		}
	}
?>
