<?php
  /***************************************************************************\
  * eGroupWare - Contacts Center                                              *
  * http://www.egroupware.org                                                 *
  *                                                                           *
  * Storage Object Classes                                                    *
  * Written by:                                                               *
  *  - Raphael Derosso Pereira <raphael@think-e.com.br>                       *
  *  sponsored by Think.e - http://www.think-e.com.br                         *
  * ------------------------------------------------------------------------- *
  *  This program is free software; you can redistribute it and/or modify it  *
  *  under the terms of the GNU General Public License as published by the    *
  *  Free Software Foundation; either version 2 of the License, or (at your   *
  *  option) any later version.                                               *
  \***************************************************************************/


	include_once("class.so_main.inc.php");

	class so_city extends so_main {

		function so_city ( $id = false )
		{
			$this->init();
			
			$this->main_fields = array(
				'id_city' => array(
					'name'  => 'id_city',
					'type'  => 'primary',
					'state' => 'empty',
					'value' => &$this->id
				),
				'id_state' => array(
					'name'  => 'id_state',
					'type'  => 'foreign',
					'association' => array(
						'table' => 'phpgw_cc_state',
						'field' => 'id_state'
					),
					'state' => 'empty',
					'value' => false
				),
				'id_country' => array(
					'name'  => 'id_country',
					'type'  => 'foreign',
					'association' => array(
						'table' => 'phpgw_common_country_list',
						'field' => 'id_country'
					),
					'state' => 'empty',
					'value' => false
				),
				'city_owner' => array(
					'name'  => 'city_owner',
					'type'  => false,
					'state' => 'empty',
					'value' => false
				),
				'city_name' => array(
					'name'  => 'city_name',
					'type'  => false,
					'state' => 'empty',
					'value' => false
				),
				'city_timezone' => array(
					'name'  => 'city_timezone',
					'type'  => false,
					'state' => 'empty',
					'value' => false
				),
				'city_geo_latitude' => array(
					'name'  => 'city_geo_latitude',
					'type'  => false,
					'state' => 'empty',
					'value' => false
				),
				'city_geo_longitude' => array(
					'name'  => 'city_geo_longitude',
					'type'  => false,
					'state' => 'empty',
					'value' => false
				),
				'city_geo_altitude' => array(
					'name'  => 'city_geo_altitude',
					'type'  => false,
					'state' => 'empty',
					'value' => false
				)
			);
			
			$this->db_tables = array(
				'phpgw_cc_city' => array(
					'type'   => 'main',
					'keys'   => array(
						'primary' => array(&$this->main_fields['id_city']),
						'foreign' => array(&$this->main_fields['id_state'])
					),
					'fields' => & $this->main_fields
				)
			);
			
			if($id)
			{
				if (!$this->checkout($id))
				{
					$this->reset_values();
					$this->state = 'new';
				}
			}
			else
			{
				$this->state = 'new';
			}
		}
		

		/*********************************************************************\
		 *                   Methods to Get Information                      *
		\*********************************************************************/
		
		/*!
		
			@function get_id_state
			@abstract Returns the ID of the State where this
				city is located
			@author Raphael Derosso Pereira
		
		*/
		function get_id_state (  )
		{
			return $this->main_fields['id_state']['value'];
		}
	
		/*!
		
			@function get_id_country
			@abstract Returns the ID of the Country where this
				city is located
			@author Raphael Derosso Pereira
		
		*/
		function get_id_country (  )
		{
			return $this->main_fields['id_country']['value'];
		}

		/*!
		
			@function get_city_name
			@abstract Returns the Name of the City
			@author Raphael Derosso Pereira
		
		*/
		function get_city_name (  )
		{
			return $this->main_fields['city_name']['value'];
		}

		/*!
		
			@function get_city_owner
			@abstract Returns the City Owner (Creator)
			@author Raphael Derosso Pereira
		
		*/
		function get_city_owner (  )
		{
			return $this->main_fields['city_owner']['value'];
		}

		/*!
		
			@function get_city_timezone
			@abstract Returns the City Timezone
			@author Raphael Derosso Pereira
		
		*/
		function get_city_timezone (  )
		{
			return $this->main_fields['city_timezone']['value'];
		}

		/*!
		
			@function get_city_geo_latitude
			@abstract Returns the City Geographic Latitude
			@author Raphael Derosso Pereira
		
		*/
		function get_city_geo_latitude (  )
		{
			return $this->main_fields['city_geo_latitude']['value'];
		}

		/*!
		
			@function get_city_geo_longitude
			@abstract Returns the City Geographic Longitude
			@author Raphael Derosso Pereira
		
		*/
		function get_city_geo_longitude (  )
		{
			return $this->main_fields['city_geo_longitude']['value'];
		}

		/*!
		
			@function get_city_geo_altitude
			@abstract Returns the City Geographic Altitude
			@author Raphael Derosso Pereira
		
		*/
		function get_city_geo_altitude (  )
		{
			return $this->main_fields['city_geo_altitude']['value'];
		}


		/*********************************************************************\
		 *                   Methods to Alter Information                    *
		\*********************************************************************/
		
		/*!
		
			@function set_id_country
			@abstract Sets the City's Country ID
			@author Raphael Derosso Pereira
		
			@param integer $id_country The Country ID
		*/
		function set_id_country ( $id_country )
		{
			$this->main_fields['id_country']['value'] = $id_country;
			$this->manage_fields($this->main_fields['id_country'], 'changed');
		}
		
		/*!
		
			@function set_id_state
			@abstract Sets the City's State ID
			@author Raphael Derosso Pereira
		
			@param integer $id_state The State ID
		*/
		function set_id_state ( $id_state )
		{
			$this->main_fields['id_state']['value'] = $id_state;
			$this->manage_fields($this->main_fields['id_state'], 'changed');
		}
		
		/*!
		
			@function set_city_owner
			@abstract Sets the City's Owner ID
			@author Raphael Derosso Pereira
		
			@param string $name The City Owner ID
		*/
		function set_city_owner ( $id )
		{
			$this->main_fields['city_owner']['value'] = $id;
			$this->manage_fields($this->main_fields['city_owner'], 'changed');
		}
		
		/*!
		
			@function set_city_name
			@abstract Sets the City's Name
			@author Raphael Derosso Pereira
		
			@param string $name The City Name
		*/
		function set_city_name ( $name )
		{
			$this->main_fields['city_name']['value'] = $name;
			$this->manage_fields($this->main_fields['city_name'], 'changed');
		}
		
		/*!
		
			@function set_city_timezone
			@abstract Sets the City's Timezone
			@author Raphael Derosso Pereira
		
			@param string $timezone The City Timezone
		*/
		function set_city_timezone ( $timezone )
		{
			$this->main_fields['city_timezone']['value'] = $timezone;
			$this->manage_fields($this->main_fields['city_timezone'], 'changed');
		}
		
		/*!
		
			@function set_city_geo_latitude
			@abstract Sets the City's Geographic Latitude as defined
			@author Raphael Derosso Pereira
		
			@param string $geo The City Geographic Latitude
		*/
		function set_city_geo_latitude ( $geo )
		{
			$this->main_fields['city_geo_latitude']['value'] = $geo;
			$this->manage_fields($this->main_fields['city_geo_latitude'], 'changed');
		}
		
		/*!
		
			@function set_city_geo_longitude
			@abstract Sets the City's Geographic Longitude as defined
			@author Raphael Derosso Pereira
		
			@param string $geo The City Geographic Longitude
		*/
		function set_city_geo_longitude ( $geo )
		{
			$this->main_fields['city_geo_longitude']['value'] = $geo;
			$this->manage_fields($this->main_fields['city_geo_longitude'], 'changed');
		}
		
		/*!
		
			@function set_city_geo_altitude
			@abstract Sets the City's Geographic Altitude as defined
			@author Raphael Derosso Pereira
		
			@param string $geo The City Geographic Altitude
		*/
		function set_city_geo_altitude ( $geo )
		{
			$this->main_fields['city_geo_altitude']['value'] = $geo;
			$this->manage_fields($this->main_fields['city_geo_altitude'], 'changed');
		}
	}
	
?>
