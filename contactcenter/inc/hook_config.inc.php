<?php
  /****************************************************************************\
   * eGroupWare - Contact Center Config Functions                             *
   * http://www.egroupware.org                                                *
   *  - Raphael Derosso Pereira <raphael@think-e.com.br>                      *
   *  sponsored by Think.e - http://www.think-e.com.br                        *
   * ------------------------------------------------------------------------ *
   *  This program is free software; you can redistribute it and/or modify it *
   *  under the terms of the GNU General Public License as published by the   *
   *  Free Software Foundation; either version 2 of the License, or (at your  *
   *  option) any later version.                                              *
  \****************************************************************************/


	function people_email_type($config)
	{
			/* Get the catalog options */
			$pCatalog = CreateObject('contactcenter.bo_people_catalog');
			$types = $pCatalog->get_all_connections_types();
		
			if (is_array($types) and count($types))
			{
				$options_email = '';
				foreach($types as $id => $name)
				{
					$options_email .= '<option value="'.$id.'"';

					if ($config['cc_people_email'] == $id)
					{
						$options_email .= ' selected ';
					}

					$options_email .= '>'.lang($name)."</option>\n";
				}
			}
			else
			{
				$options_email = '<option value="_NONE_">'.lang('YOU MUST REGISTER CONNECTIONS TYPES!').'</option>';
			}

			return $options_email;
	}

	function people_phone_type($config)
	{
			/* Get the catalog options */
			$pCatalog = CreateObject('contactcenter.bo_people_catalog');
			$types = $pCatalog->get_all_connections_types();
		
			if (is_array($types) and count($types))
			{
				$options_phone = '';
				foreach($types as $id => $name)
				{
					$options_phone .= '<option value="'.$id.'"';

					if ($config['cc_people_phone'] == $id)
					{
						$options_phone .= ' selected ';
					}

					$options_phone .= '>'.lang($name)."</option>\n";
				}
			}
			else
			{
				$options_phone = '<option value="_NONE_">'.lang('YOU MUST REGISTER CONNECTIONS TYPES!').'</option>';
			}

			return $options_phone;
	}
