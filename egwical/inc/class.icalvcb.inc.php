<?php
  /**
   * @file
   * simple class that provides manipulating a collection of iCalendar objects (aka VElts)
   *
   * $Id$
   * @author Jan van Lieshout                                                *
   * @package egwical
   * ------------------------------------------------------------------------ *
   * This library is free software; you can redistribute it and/or modify it  *
   * under the terms of the GNU Lesser General Public License as published by *
   * the Free Software Foundation; either version 2.1 of the License,         *
   * or any later version.                                                    *
   * This library is distributed in the hope that it will be useful, but      *
   * WITHOUT ANY WARRANTY; without even the implied warranty of               *
   * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                     *
   * See the GNU Lesser General Public License for more details.              *
   * You should have received a copy of the GNU Lesser General Public License *
   * along with this library; if not, write to the Free Software Foundation,  *
   * Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA            *
   */

   require_once EGW_SERVER_ROOT.'/phpgwapi/inc/horde/Horde/iCalendar.php';

  /**
   * Simple Vcalendar Component Buffer
   *
   * This buffer can be used to collect Vcalendar Elements (VElts)
   * like VEvent and VTodo and manipulate them as a collection.
   *
   * Basic operations supported are:
   * - adding vcalendar elements to the collection
   * - finding a subset of vcalendar elements of a specific type in
   *   the collection.
   * - delivering all elements of the collection into as a sinlge
   *   vcalendar element.
   * - rendering the whole collection as a iCalendar format string
   *
   * @note currently this is basically a simple wrapper around
   *  Horde_iCalendar.
   * @note the Vcalendar Component Buffer will --as the name already says--
   * only contains Vcalendar Components, that is VEVENTs, VTODOs etc.
   * If you try to add it a full compound VCALENDAR element to it, using the add_velt()
   * method, this will be decomposed in all its components and these will be added to the
   * VCB.
   *
   * @auth jvl
   * @version 0.9.37-a2
   */ 

   class icalvcb extends Horde_iCalendar
   {

// 	 /** The buffer variable that can hold a series of Vcal Elements (VElts)
// 	  * @private
// 	  * @var Horde_iCalendar
// 	  */
// 	 var $hi = null;
	 
	 /** constructor,
	  * Initialize the buffer
	  */
	 function icalvcb()
	 {
	   //	   parent::Horde_iCalendar();
	   //	   $this->hi =& new Horde_iCalendar;
	 }


	 /**
	  * Remove all Vcal Elements from the VCB
	  * @return boolean true if all went oke
	  */
	 function clear()
	 {
	   //	   $this->hi->clear();
	   $this->clear();

	 }
	  

	 /** Add a single Vcal Element to the VCB
	  * 
	  * @note when the Vcalendar object to be added is a full compound
	  * VCALENDAR element then this element will be decomposed in all its components
	  * and these will be added to the VCB.
	  * @param VElt $vobj a  Vcal object that is added to the buffer
	  * @return boolean true if all went oke
	  */
	 function add_velt(&$vobj)
	 {
	   if(strtolower(get_class($vobj)) == 'horde_icalendar'){
		 // import all its Vtype components
		 foreach($vobj->getComponents() as $v){
		   $this->addComponent($v);
		 }
	   } else {
		 // add just the single VElt
		 $this->addComponent($vobj);
	   }
	   return true;
	 }
	 

	  /**
	   * Add Multiple Vcal Elements to the VCB.
	   *
	   * A convenience wrapper around @ref add_velt() to handle multiple Vcal Elements at once.
	   * @param array_of_VElt $vobjs a list of  Vcal objects that are added to the buffer
	   * @return int number of objects added.
	   */
	  function add_velts(&$vobjs)
	  {
		foreach($vobjs as $v){
		  $this->add_velt($v);
		}
		return count($vobjs);
	  }


	 /**
	  * Find Vcal Elements in the VCB of a specific type
	  *
	  * @param VEltType $vtype class name of a specific Vcal Element: 'VCALENDAR',
	  * 'VTODO', 'VEVENT' etc. Default is the special type ALL that will deliver all VElts in the
	  * buffer.
	  * @return array_of_VElts|false the list of all Vcal Elements found in the VCB of the given
	  * type. Note that the search is only carried out between the top Vcal Elements: no search
	  * for sub vcal elements within others is done! When nothing found an empty array is returned. 
	  * On error false is returned.
	  */
	 function find_velts($vtype='all')
	 {
	   $velts = array();
	   $doall = ($vtype == 'all');
	   $velttype = 'Horde_iCalendar_' . strtolower($vtype);
	   foreach($this->getComponents() as $velt){
		 if ($doall || is_a($velt, $velttype)){
		   $velts[] = $velt;
		 }
	   }
		return $velts;
	 }
	 
	  /**
	   * Render all Vcal Elements from the ICB as one big VcalString. 
	   *
	   * Note this function uses the egwical_resourcehandler render routine
	   * render_velt2vcal() so that the attributes processing is done
	   * @param array $vcal_attributes  hash of attributes to be set in the rendered Vcal string
	   * @return Vcalstr|false the rendered Vcal formatted string.
	   * On error: false
	   */
	 function render_vcal(&$vcal_attributes)
	 {
	   return egwical_resourcehandler::render_velt2vcal($this,$vcal_attributes);
	 }

	  /**
	   * Parse  Vcalstring into a Vcal Elements and add these to buffer
	   *
	   * @Note this function uses the egwical_resourcehandler parse routine
	   * parse_vcal2velt()
	   *  
	   *@param VcalStr $vcal a iCalendar formatted string with a Vcalendar Element
	   *@return int|false the nof created Vcal Elements 
	   * On error: false
	   */
	  function parse_vcal(&$vcal)
	  {
		return $this->add_velt(egwical_resourcehandler::parse_vcal2velt($vcal));
	  }



	 /** Deliver all Velts in buffer as a single icalendar element
	  *
	  * @warning the returned VCALENDAR is a reference to the actual
	  * buffer, not a clone, so later changes onto  the buffer will
	  * propagate in the returned obj.
	  * @return VElt|false all the velts from the buffer wrapped in a
	  * container VCALENDAR object. On error false is delivered.
	  */
	 function to_velt()
	 {
	   return $this->hi;
	 }


	 /** Deliver number of Velts in buffer (on first level).
	  *
	  * @return int the number of (first level)velts in the buffer
	  */
	 function count()
	 {
	   return $this->getComponentCount();
	 }



   }


?>