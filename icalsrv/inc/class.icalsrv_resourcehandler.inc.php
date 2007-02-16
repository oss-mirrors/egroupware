<?php
    /** @file
	 * ICalendar component import and export from Egroupware Resources
	 * @author Jan van Lieshout
	 * @package icalsrv
	 */

    /* ------------------------------------------------------------------------ *
	 * This code is free software; you can redistribute it and/or modify it  *
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
	 **************************************************************************/

	require_once EGW_SERVER_ROOT.'/phpgwapi/inc/horde/Horde/iCalendar.php';
	require_once EGW_SERVER_ROOT.'/icalsrv/inc/class.eicnvutils.inc.php';

	/** defines for result status of import routines
	*
	*/
	define('VELT_IMPORT_STATUS_UPDOK', '1');   // updated ok
	define('VELT_IMPORT_STATUS_DELOK', '0');   // deleted ok
	define('VELT_IMPORT_STATUS_ERROR', '-1');  // something went wrong
	define('VELT_IMPORT_STATUS_NOACC', '-2');  // no rights to write: skip?
	define('VELT_IMPORT_STATUS_NOELT', '-3');  // element not found: skip?
	define('VELT_IMPORT_STATUS_BTYPE', '-4');  // element is of bad type: skip?

	/** defines for uid_mapping_mode (import and export)
	* These are the allowed values for the variables $uid_mapping_import and
	* $uid_mapping_export. See @sec secuidmapping
	*/
	define('UMM_UID2ID', '0');   // map ical uid to egw id (decode uid on import)
	define('UMM_ID2UID', '0');   // map egw id to ical uid (encode uid on export)
	define('UMM_NEWID', '1');    // ignore ical uid, make a fresh egw id (no id decoded on import)
	define('UMM_NEWUID', '1');   // ignore egw id, make a fresh ical uid (no id encoded on export)
	define('UMM_UID2UID', '2');  // map ical uid to egw uid (match/copy ical and egw uids)
	define('UMM_FIXEDID', '3');  // ignore ical uid, use a fixed  egw id (ext. match on import)
	define('UMM_FIXEDUID', '3'); // ignore egw id/uid, use a fixed ical uid (ext. uid gen. on export)

    /**
	 * Abstract Base class with routines to transport and convert
	 * iCalendar components to and from Egroupware resources
	 * (calendar, infolog, ). 
	 * An icalsrv object is used in an application to
	 * transport Ical information to Egw data elements and vice
	 * versa. 
	 * @section secicalsrvsynopsis Synopsis
	 * See the overview in @ref mainpage
	 *
	 * @section secicalsrvmiscelts Egw Elements (EElts) vs. iCalendar Elements (VElts)
	 * The Ical information can consist of components like VEVENTS
	 * VTODOS, VFREEBUSYs etc. We will denote these Vcalendar Elements
	 * with the general term <b>VElt</b>. <br/>
	 * In the current implementation we use Horde_iCalendar objects for
	 * these VElts. In this Horde_iCalendar class a complete icalendar
	 * is represented by a VCALENDAR element object (in Horde terminology a Horde_iCalendar object)
	 * and non compound elements of such a VCalendar object like VTODO's or
	 * or VEVENTS are represented by subclasses of this (in Horde terminology
	 * Horde_iCalendar_XYZ objects). In the EgwIcal package we will denote these
	 * Non Compound vcalendar elements by the term: <b>NCVElt</b>
	 * The Egw data elements (events, task etc.) that reside in
	 * different Egw resources (calendar, infolog,...) are denoted
	 * with the general term <b>EElts</b>.
	 * In the current implementation the Egw resources dont use objects (yet?) to
	 * represent EElts (tasks, events,..) but rather manipulate them either as
	 * an array of data fields or by an integer that represents the id of the EEltData
	 * in the database.<br/>
	 * @note for this reason many of the export methods in this class can handle
	 * polymorphic parameters for EElt. That is the EElt can be passed either as
	 * an array (of type EEltData) or as an identifier (of type EEltId).
	 *
	 * @section secbabsandconc Abstract Base class and Concrete subclasses
	 * The Egw data elements (events, task etc.)  we thus call
	 * <b>EElts</b>, reside in
	 * different Egw resources (calendar, infolog,...) that handle
	 * them. The code for specifically handling such a resources
	 * is within Egw contained in specific Egw classes (like bocalupdate, boinfolog,...).
	 *
	 * The base class icalsrv_resourcehandler is a base class that implements
	 * generic code to handle these Egw resources. For example parsing
	 * or rendering from/to an iCalendar string, or handling sets of EElts and VElts
	 * For actual work with the NonCompound Vcalendar Elements (<b>NCElts</b>)
	 * it has no code by itself but relies on  code within a socalled <i>concrete</i> subclass
	 * that is dedicated towards handling a specific Egw resource.
	 * These subclass must implement the virtual methods of the base class. 
	 *
	 * In the current implementation (>= v0.9.30) that uses the pattern of
	 * <i> ical accessors as icalsrv_resourcehandler subclasses </i> 
	 * the class acts as a Abstract Base Class in the sense that it should
	 * not be instantiated directly but rather be used by
	 * instantiating one of its <i>concrete</i> subclasses.
	 *
	 * Currently there are three such concrete resource handling subclasses available:
	 * - <code>bocalupdate_vevents</code> to convert between egw calendar events and VEVENTS
	 *   and allow import and export of these.
	 * - <code>bocalupdate_vfreebusy</code> to convert between egw calendar events and VFREEBUSY
	 *   components. At the moment  only export of these is implemented.
	 * - <code>boinfolog_vtodos</code> to convert between egw infolog tasks events and VTODOS
	 *   and allow import and export of these. 
	 * See the add_rsc() routine for more info on how to use these.
	 *   
	 * @section secuidmapping UID to ID Mapping and Matching. 
	 * A short explanation about the uid_mapping used in the conversion between icalendar
	 * elements (aka VElts) on the one hand and Egw Resource Elements (aka EElts) on
	 * the other hand. Uid_mapping is the mechanismn to relate the EElts (identified either
	 * by their id or uid fields) to VElts (identified by their UID field).
	 * In EgwIcal this <i>Uid-Mapping-Mode</i> can be controlled by three variables.
	 * Here a short intro into their working. As example of a EElt and VElt an event
	 * resp. a VEVENT are taken, but for tasks and VTODO's and other EElt-VElt pairs
	 * the situation is the same of course.
	 *
	 * If $uid_mapping_export is <code>UMM_ID2UID</code>, then:
	 *  - On export for each exported event, a new UID value will generated with the id of
	 *   the related egw event id encoded. 
	 *
	 * If $uid_mapping_export is <code>UMM_UID2UID</code>, then:
	 *  - On export for each exported event, the UID field of the exported VEVENT will contain a copy
	 *   the value of the event uid field as stored in the Egw resource.
	 *  
	 * if $uid_mapping_import is <code>UMM_UID2ID</code> then:
	 * - On import the VEVENT UID field will be checked, if it
	 *  appears to be a previously exported uid value then the
	 *  encoded egw id of the old egw event is retrieved and used for
	 *  update.  If it doesnot have a uid value with a valid egw id
	 *  encoding, then the its is handled as being a new VCAL ELEMENT to be
	 *  imported, and a new egw id will be generated. The old vcal element
	 *  uid will though be saved for possible later use in the uid field of the
	 *  egw element.
	 *
	 * If $uid_mapping_import is <code>UMM_UID2UID</code>  then:
	 * - on import of a
	 *   vcal element the update routines will first try to find an
	 *   existing egw event with the same uid value as present in the
	 * UID field of the newly to be imported vcal element. If this
	 * succeeds this egw event will get updated with the info from the
	 * vcal element. If this fails a new event will be generated and
	 * the uid taken from the vcal element will be stored in its uid
	 * field.
	 * Note: this mode is strongly discouraged!
	 *
	 * @note <b>Mostly it is best to disable the UID2UID uid mapping mode always.</b> It
	 * prevents that multiple duplicates of a event will be created
	 * in Egw, that may not be accessible anymore via for example the
	 * Ical-Service interface. Only use it when you really need to
	 * reimport into Egw an already once before imported calendar because you
	 * accidentally deleted parts of it in Egw. And even in this case a better solution would
	 * be to copy these lost events in the client into a downloaded version from Egw of your
	 * original calendar and then publish  this changed clien calendar to Egw for once, with the
	 * uid_mapping_import set still to UID2ID.  (This is because UID2UID has namely no effect
	 * for <i>new</i> (i.e. not yet know by egw) events on the one hand and on the hand, the old
	 * (i.e.  already once downloaded to the client) events will be recognized already with
	 * the $uid_mapping_import = UMM_UID2ID setting.
	 *
	 * @section secicalsrvdevicetype The DeviceType system
	 * The Egwical package allows it to tune the conversion between EElts and
	 * VElts to the capabilities of the device that is tranferring iCalendar
	 * data to and from Egw. This steering of the conversion is done via
	 * the deviceType member that determines wich VElt fields are supported for
	 * for import and export. 
	 * @todo write some doc about the devicType steering and supportedFields
	 *
	 * @section seccnvmethods Auxiliary Conversion Functions
	 * Developers of Concrete resource handler subclasses, can
	 * profitably use the set of  auxiliary conversion
	 * routines that the library class @ref eicnvutils provides.
	 * These methods must be accessed via the $ecu member variable. 
	 * 
	 * @version 0.9.37-ng-a1 added deviceType2contentType method.
	 * @date 20060427
	 * @since 0.9.36 first version with NAPI-3.1 (rsc-owner_id handling)
	 * @since 0.9.30 new api: ical accessors as icalsrv_resourcehandler subclasses
	 * @since 0.9.22 new api2 using eicnvutils via $ecu
	 * @author Jan van Lieshout <jvl (at) xs4all.nl> (This version)
	 * @author Lars Kneschke <lkneschke@egroupware.org> (original code of reused parts)
	 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de> (original code of reused parts)
	 *
	 * @license see @url http://opensource.org/licenses/gpl-license.php GPL -
	 *  GNU General Public License
	 * 
	 * @todo check signatures of various EElt export methods to see if we should allow
	 * both array_of_EElt and array_of_EEltId arguments. The first might be profitable
	 * to handle large queries (only one DB search needed)
	 */
	class icalsrv_resourcehandler
	{
		/** The Bound Egw Resource that we handle
		* @private
		* @var egwrscobj $rsc
		* Registry for the egw resource object (calendar, infolog,..) that will be used
		* to transport ical elements from and to: The socalled <i>
		* Bound Resource</i>
		* This can be set by the constructor or later by set_rsc().
		*/
		var $rsc = null;

		/**
		* @private
		* @var array $rsc_vtypeslist vcalendar types the bound resource can handle
		*
		* This list gives all the vcalendar type supported by the (currently)
		* bounded resource.  As only a concrete resource handler
		* subclass has this knowledge, it should be set by such a subclass!
		*/
		var $rsc_vtypes = array();

		/** Owner of the (virtual) resource is used to add or delete Egw Elements
		* @private
		* This variable can be set via the constructor. When not used it has the
		* default value 0, meaning that the resource owned by the authenticated user is taken.
		* @var int $rsc_owner_id
		*/
		var $rsc_owner_id = 0;

		/** extra debugging switch
		* @private
		* @var int
		* Switch to print extra debugging about imported and exported events to the httpd errorlog
		* stream. (0 is off 1 is on 2 is more on ...
		*/
		var $eidebug = 1;

		/** Horde calendar used for various conversions
		* @private
		* @var Horde_iCalendar Horde_iCalendar that is used for various things.
		* Placeholder object used to access various Horde_iCalendar methods
		* In here the constructor will create a Horde_iCalendar object that can be used
		* by the various routines in the class
		*/
		var $hi;

		/** The library object with the conversion utilities
		* @private
		* @var eicnvutils
		* This object can be reused in other icalsrv objects, it is used readonly.
		* Therefore you can pass an instantion of it via the class constructor, if you
		* have one. Else the constructor will create a new version.
		*/
		var $ecu;

		/** Standard attributes values used in rendering a iCalendar string
		* @private
		* @var array
		* Hash of some standard attributes of a VCALENDAR element.
		* These used for rendering a Vcal formatted string.
		* Mostly these are only the <code>PRODID</code>, <code>VERSION</code>and
		* <code>METHOD</code> attribute. These are set to default values in the constructor.
		* You can change these before exporting some Vcal.You
		* can set them back to their default values with the
		* method @ref _set_vcalendar2egwAttributes().
		*/
		var $vcalendar2egwAttributes;

		/** mapping from iCalendar components to egw elements
		* @private
		* @var array 
		* An (nested0 hash array containing the mapping from iCalendar components
		* to egw elements. This is set by constructor. And for each component
		* (like 'VEVENT', 'VTODO' etc) the entry will point to an array that
		* gives a mapping of fields and subcomponents of that component type.
		* These arrays are set either by
		* - the constructors of the workerclass, if we are instantiating
		*  a workerclass  icalsrv object. or:
		* - the  add_rsc() routine if we add a resource and associated workerobj to
		*  a base icalsrv class.
		* @note in the concrete subclasses the supportedFields system will copy keys from
		* this table to indicate if some mapping is supported.
		*/
		var $ical2egwComponents = array();

		/** the generic type of device now using the icalsrv_resourcehandler 
		* @private
		* @var ProductType
		* Label that identifies the device capabilities for import and export of the
		* currently connected client device.
		* 
		* This ProductType will be used to control the import and export by using it
		* as argument for setSupportedFields().
		* This label is a string with a slash separating generic and the more specific
		* description.
		* Examples are: 
		<PRE>
		all                         // the default
		siemens/sx11
		nexthaus corporation/
		sonyericsson/
		multisync/
		...
		remotecalendars/
		</PRE>
		* See icalendarProdId2devicetype() and httpAgent2deviceType() to derive the ProductType
		* from a iCalendar resp. a http request.
		*/
		var $deviceType = 'all';

		/** supported fields of the importing/exporting device
		* @private
		* @var array $supportedFields
		* An array with the current supported fields of the
		* importing/exporting device.
		* To detect if a certain ical property (eg ORGANIZER)  is supported in the current
		* data import/export do a  <code>isset($this->supportedFields['ORGANIZER'])</code>.
		* To detect if a certain egw field (eg <code>status</code>)  is supported in the current
		* data import/export do a
		* <code>in_array(array_flatten(array_values($this->supportedFields)),'status')</code>
		* or something like that (not tested, implemented, or needed yet..) Maybe should
		* implement a method for this..
		* @note this variable is only set and used in real worker classes
		*/
		var $supportedFields;

		/** Switch that determines how uid fields are used for import
		*
		* @var int $uid_mapping_import
		* According to the value, on import, the uid field of a vcalendar element will be
		* determine how the search for a matching egw element is done. The choices are:
		* - no search for a related egw element id is done, Just a new element is added to the
		*   bound egw resource (<code>UMM_NEWID</code>) or  
		* - a related egw element is searched for based on a egw id decoded from the uid field of the
		*   ical element(<code>UMM_ID2UID</code> Note: <b>Default situation!</b>). 
		*   .
		*   This requires of course that at some earlier (exportd) moment an actual egw id was encoded
		*   in this uid field.
		* - a related egw element is searched for based on the full value of the uid field of the
		*   ical element by searching trough the uid fields of the egw elements (<code>UMM_UID2UID</code>
		*   Note: <b>Strongly discouraged!</b>). 
		*   .
		*   This requires that the egw resource does support correct and unique manipulation
		*   (storage etc.)   of the egw element uid fields!
		* For more info see @ref secuidmapping
		*/
		var $uid_mapping_import = UMM_UID2ID;

		/** Switch that determines the way uid fields are generated at export
		*
		* @var int $uid_mapping_export
		* According to the value, on export, a uid will be generated:
		* - completely unrelated to the related egw element id (<code>UMM_NEWUID</code>) or  
		* - with the related egw element id  encoded (<code>UMM_ID2UID</code> Default situation!) or  
		* - directly copied from the uid field of the related egw element
		*   (<code>UMM_UID2UID</code> Strongly discouraged!).
		* For more info see @ref secuidmappping
		*/
		var $uid_mapping_export = UMM_ID2UID;

		/** Switch to allow reimport of gone egw elements
		* @var boolean $reimport_missing_elements
		* Switch that determines if events not anymore in egw are allowed to be reimported
		*
		* Default this is on
		*/
		var $reimport_missing_elements = true;

		/** Constructor that inits the handler object.
		*
		* The auxiliary object @ref $hi, 
		* @ref $ecu ,  @ref $ical2egwComponents and $vcalendar2egwAttributes are all initialized.
		* Optionally an egwresource (like calendar- or infolog object) can already be passed
		* and a devicetype.
		*
		* @param egwobj $egwrsc Egroupware data resource object that
		* will be used to transport (i.e. import and export) the
		* vcalendar elements to and from. This can also later be set
		* using the set_rsc() method.
		* @param string $prodid The type identification of the device is used to transport
		* ical data to and from. This can later be set using the set_supportedfields() method. 
		* @param string $rscowernid the id of the resource owner. This is only needed for import
		* in resources not owned by the authenticated user. Default (0) the id of the
		* authenticated user is used.
		*/
		function icalsrv_resourcehandler($egwrsc = null, $devicetype='all', $rscownerid=null)
		{
			// actually this would only be needed by the abstract superclass?
			$this->hi = &new Horde_iCalendar;

			// deprecated? now only via $ecu done?
			$this->TASKMAGIC = $GLOBALS['egw_info']['server']['install_id']
				? $GLOBALS['egw_info']['server']['install_id']
				: 'local'; 

			$this->ecu =& new eicnvutils;

			$this->rsc_owner_id = ($rscownerid == '0')
				? $GLOBALS['egw_info']['user']['account_id'] : $rscownerid;

			$this->vcalendar2egwAttributes  = $this->_provided_vcalendar2egwAttributes();
			$this->ical2egwComponents = $this->_provided_ical2egwComponents();

			if(!$egwrsc === null)
			{
				($this->set_src($egwrsc) !== false) || error_log('icalsrv_resourcehandler() bad arg for $rsc');
			}
			if(!$rscownerid === null)
			{
				$this->set_src_owner_id($rscownerid);
			}
		}

		/**  @name Class Methods
		* Methods that can be called without an instance of class icalsrv_resourcehandler
		*/
		//@{

		/** Deliver the implemented ical to egw components mapping. --Class Method--
		* @private
		*
		* Produce the array of icalcomponent (types) to egw element  mappings that this are
		* implemented. This info is used to initialize the variable $ical2egwComponents
		* @return array The initial mapping provided in this implementation.
		*/
		function _provided_ical2egwComponents()
		{
			return array(
				'VCALENDAR'	=> &$this->vcalendar2egwFields,
				'VEVENT'	=> null, // in workerclass points to other array
				'VTODO'  	=> null, // in workerclass points to other array
			);
		}

		/** Deliver the implemented vcalendar attributes to egw standard values mapping. --Class Method--
		* @private
		* Produce an array that holds the mapping of
		* some VCALENDAR element attributes to Egw fields.
		* These data are also used to initialize the variable $vcalendar2egwAttributes.
		* @note this is mainly used at export time to set the 
		*  <code>PRODID</code>, <code>VERSION</code>and
		* <code>METHOD</code> attributes
		* @return array The initial vcalendar attributes mapping
		* provided in this implementation.
		*/
		function _provided_vcalendar2egwAttributes()
		{
			return array(
				'PRODID'  => '-//eGroupWare//NONSGML eGroupWare Calendar '  
					. $GLOBALS['egw_info']['apps']['calendar']['version']  . '//'
					. strtoupper($GLOBALS['egw_info']['user']['preferences']['common']['lang']
				),
				'VERSION' => '2.0',
				'METHOD'  => 'PUBLISH'
			);
		}

		/** Derive the deviceType for an iCalendar. --Class method-- 
		* @todo implement icalendarProdId2devicetype()
		* @param string $prodidstr stringvalue of a iCalendar PRODID field
		* @return ProductType|False the productype derived from the PRODID label
		* On failure: False
		*/
		function icalendarProdId2devicetype($prodidstr)
		{
			$devtype= 'all';
			if(preg_match('#remotecalendars#i',$prodidstr))
			{
				$devtype = 'remotecalendars';
			}

			return $devtype;
		}

		/** Derive the deviceType from a http request agent field. --Class method--
		* @todo implement httpUserAgent2deviceType()
		* @param string $agentid stringvalue http user agent id field of a http request
		* @return ProductType|False the productype derived from the PRODID label
		* On failure: False
		*/
		function httpUserAgent2deviceType($agentidstr)
		{
			$devtype= 'all';
			if(preg_match('#remotecalendars#i',$agentidstr)){
				$devtype = 'remotecalendars';
			}
			return $devtype;
		}

		/** Derive the deviceType from a product manufacturer and name description. --Class method--
		* @todo implement product2devicetype
		* @param string $_productManufacturer a string indicating the device manufacturer
		* @param string $_productName a further specification of the current device that is used
		* for import or export.
		* @return ProductType|False the productype derived from the input strings
		* On failure: False
		*/
		function product2deviceType($productManufacturer='all', $productName='')
		{
			return 'all';
		}

		/** Derive the contentType for export to a devictype. --Class method--
		* @todo implement deviceType2contentType
		* @param ProductType $devtype the productype of the device that will receive or
		* produce the icaldata content.
		* @return string the required http contenttype setting(as per HTTP / mimetype definition)
		* that the devicetype likes to see for the produced icaldata.
		* On failure: False
		*/
		function deviceType2contentType($devtyp='all')
		{
			switch($devtyp)
			{
				case 'remotecalendars':     // remotecalendars
					$ct ='text/calendar';
					break;
				case 'moz':
					$ct ='text/html';  // check this!!
					break;
				case 'all':
					$ct ='';
					break;
				default:
			}

			return $ct;
		}

		/**
		* Parse  Vcalstring into a Vcal Element. --Class Method--
		*  
		* The Vcalstring should form a single Vcal element, thus it should be of the form
		* <code>BEGIN:veltype ...... END:veltype </code> with veltype the name of a valid
		* Vcal Element such as VCALENDAR, VEVENT etc.
		* When a single component is contained in a VCALENDAR, this container is automatically
		* unpacked!
		*@param VcalStr $vcal a iCalendar formatted string with a more Vcalendar Element
		*@return VElt|false the created Vcal Element 
		* When a VCalendar with many sub Vcal Elements (like VEVENT's and VTODO's) is parsed,
		* only the outermost (a Vcalendar object) is returned. The sub VElts should somehow
		* be accessible through membership of this object for routines that need them. 
		* On error: false
		*/
		function &parse_vcal2velt(&$vcal)
		{
			// unfoldlines as this was removed from our horde stuff
			$vcal = preg_replace("/[\r\n]+ /",'',$vcal);

			$vcalendar =& new Horde_iCalendar;
			if(!$vcalendar->parsevCalendar($vcal))
			{
				error_log('icalsrv parse_vcal2velt:  ERROR- couldnot parse..');
				return false;
			}
			// auto remove containing vcalendar when only 1 velt
			if(count($vcalendar->_components) == 1)
			{
				$comps = &array_values($vcalendar->_components);
				return $comps[0];
			}
			else
			{
				return $vcalendar;
			}
		}

		/**
		* Render a Vcal Element as a VcalString.  --Class Method-- 
		*
		* @note if the $vobj is a iCalendar sub element, a Vcalendar container
		* is put around it.
		*@param VElt $vobj a  Vcal object that is rendered (serialized)
		* as Vcal formatted string.
		* @param array $attribs optional hash with global iCalendar
		* attributes settings. These attributes will be added and
		* possibly override the standard attributes as given by
		* the _provided_vcalendar2egwAttributes() routine.
		* @note if the $vobj argument is a iCalendar these attributes stay
		* added as a side-effect!
		*@return VcalStr|false the rendered Vcal formatted string.
		* On error: false
		*/
		function render_velt2vcal($vobj, $attribs = null)
		{
			if($attribs == null)
			$attribs = array();

			if(! in_array(strtolower(get_class($vobj)),array('horde_icalendar','icalvcb')))
			{
				// add container around non Vcalendar
				$myhi =& new Horde_iCalendar;
				$myhi->addComponent($vobj);
			}
			else
			{
				// just use horde function
				$myhi =& $vobj;
			}
			// set our wished attributes, note that this is a nasty
			// sideeffect in case of a $vobj == VCALENDAR element
			foreach (icalsrv_resourcehandler::_provided_vcalendar2egwAttributes() as $attr => $val)
			{
				$myhi->setAttribute($attr,$val,array(),false);
			}
			foreach ($attribs as $attr => $val)
			{
				$myhi->setAttribute($attr,$val,array(),false);
			}
			//return $myhi->exportvCalendar();
			// alternative with delete
			$s =& $myhi->exportvCalendar();
			$myhi = null;
			return $s;
		}

		// end of class methods group
		//@}

		/** @name Virtual Methods
		* These methods must be overridden in concrete subclasses
		*/
		//@{

		/**  Set the egw resource and ical element types  to handle.
		* @virtual
		* The egw resource and ical element types that are used to
		* handle are registered in the variable $rsc and $rsc_vtypes.
		* @note this method needs to be implemented in a concrete
		* subclass of icalsrv_resourcehandler as these only these classes
		* can check if the $egw_rsc passed is of the correct (supported) type for the handler.
		* And they are also the only ones thatknow what the vtypes associated with the resource are!
		*
		* @param obj $egw_rsc the resource that will be used to transport the ical data to and from.
		* 
		* @return boolean false  always as this abstract version should
		* never be called from this base class directly.
		*/
		function set_rsc($egw_rsc)
		{
			//$this->rsc = $egw_rsc;
			//$this->rsc_vtypes[] = 'vevent';
			error_log('icalsrv_resourcehandler.set_rsc(): ERROR: Empty Abstract Method called'
				. ' -call this method only on a concrete subclass!');
			return false;
		}

		/**  Define owner (by account_id) for new to import egw elements.
		* Just fill the member variable $this->rsc_owner_id by the appropiae value
		* @param int $account_id the id of the egw account that will be used to set ownership
		* for new to create egw elements in the egw resource.
		*/
		function set_rsc_owner_id($account_id='0')
		{
			$this->rsc_owner_id = $account_id;
		}

		/** Import a Non Compound Vcalendar Element into the bound resource.
		* @virtual
		* @param VElt $ncvelt a Non Compound vcalendar element that is to be imported
		* in the bound resource. This should not be a full compound VCALENDAR obj!
		* @param EEltId $eid a fixed id indicating a specific egw
		* element in the bound resource that should be
		* updated with by the (converted) $velt data. When left out, insertion or updating is
		* done on some form of uid-to-id mapping.
		* @return false as it is a non allowed abstract dummy
		*/
		function import_ncvelt($ncvelt,$eid=-1)
		{
			error_log('icalsrv_resourcehandler.import_ncvelt()'
				. ': ERROR: Virtual Method called - this function should be overwritten in subclass!');
			return false;
		}

		/** Export Egw element from bound resource as NonCompound Vcal Element.
		* @virtual
		* @param EEltId|EEltData $eid a egw element for the bound resource that is to be exported 
		* @return NCVElt the exported egw element converted to a Non Compound Vcalendar object
		*/
		function export_ncvelt(&$eid)
		{
			error_log('icalsrv_resourcehandler.export_ncvelt()'
				. ': ERROR: Virtual Method called - this function should be overwritten in subclass!');
			return false;
		}

		// end of virtual methods group
		//@}


		/**
		* Import all suited elements  from an iCalendar string into the
		* bound Egw resource.
		*
		* This import routine parses the Vcal string and then tries to
		* import into the egw resource bound in $this->rsc all
		* vcalendar elements of the supported type (VEVENTS or VTODOS,
		* ..) for this resource. This supported type is found in $this->?? 
		*
		* @param VcalStr $vcal a iCalendar formatted string with either a single VElt or a VCALENDAR
		* that contains multiple VElts.
		* @return array_of_EEltId|false a list of id s of the resulting
		* egw elements imported or updated in the bound egw resource.
		* On error: false
		*/
		function import_vcal(&$vcal)
		{
			if(($velt = $this->parse_vcal2velt($vcal)) == false)
			{
				error_log('icalsrv..import_vcal: error parsing');
				return false;
			}

			// rest should be done by the by import_velts()
			// that dissassembles the icalendar obj
			//$va = array($velt);
			return $this->import_velts($velt);
		}

		/**
		* Export egw elements from bound egw resource as an iCalendar string.
		*  
		* All the egw elements in the bound egw resource, refered to by
		* the ids in $eids are exported as Vcalendar elements and then
		* rendered into a iCalendar formatted string.
		* Specific global attributes settings  for this string are
		* taken from .....
		*
		* @param  array_of_EEltId|array_of_EEltData $eids a list of egw elements (ids) for
		* the bound egw resource that are to be exported.
		* @param array $attribs optional hash with global iCalendar
		* attributes settings. These attributes will be added and
		* possibly override the standard attributes as found in $this->vcalendar2egwAttributes
		* @return VcalStr a iCalendar formatted string  corresponding
		* to the VElt data converted from egw elemenent refered to by $eids
		* On error: false
		*/
		function export_vcal(&$eids, $attribs = null)
		{
			// be tolerant towards a single eid
			if(!is_array($eids))
			{
				$eids = array($eids);
			}

			// get a new horde_iCalendar to gather and render
			// this to not interfere with render_velt2vcal
			$myhi =& new Horde_iCalendar;

			foreach($eids as $eid)
			{
				if(($velt = $this->export_ncvelt($eid)) !== false)
				{
					$myhi->addComponent($velt);
				}
			}

			return $this->render_velt2vcal($myhi, $attribs);
		}

		/**
		* Import a set of Vcalendar Elements into the bound Egw resource
		*  
		* All vcalendar elements that are of an appropiate type,
		* supported by the bound resource in $this->rsc are converted
		* to egw elements and then imported into the resource. Non
		* appropiate vcalendar element types are simply ignored. Full
		* VCALENDAR objects are decomposed and each (appropiate) part is imported.
		* 
		* @param array_of_VElt $vobjs a list of Vcal ELement objects that are converted and
		* imported into Egw.
		* @return array_of_EEltId|false a list of id s of the resulting
		* egw elements imported or updated in the bound egw resource.
		* On error: false
		*/
		function import_velts(&$vobjs)
		{
			// be forgiven to argument type..
			if(!is_array($vobjs))
			{
				$vobjs = array($vobjs);
			}

			// all the velts to be imported
			$velts = array();

			// copy and unpack if vcalendar
			foreach($vobjs as $v)
			{
				if(strtolower(get_class($v)) == 'horde_icalendar')
				{
					// import all its Vtype components
					$vcalsubvelts = &$v->getComponents();
					$velts = array_merge($velts, $vcalsubvelts);
				}
				else
				{
					// add just the single VElt
					$velts[] = $v;
				}
			}

			$eelt_ids   = array();
			$vtypes =& $this->rsc_vtypes;

			//try importing all velts for each of the vtypes
			foreach($vtypes as $vtype)
			{
				// set error counts to zero
				$impstats = array(
					VELT_IMPORT_STATUS_UPDOK => 0,
					VELT_IMPORT_STATUS_DELOK => 0,
					VELT_IMPORT_STATUS_ERROR => 0,
					VELT_IMPORT_STATUS_NOACC => 0,
					VELT_IMPORT_STATUS_NOELT => 0);

				$vtype= strtolower($vtype);
				$hordevtype = 'Horde_iCalendar_' . $vtype;

				foreach($velts as $ve)
				{
					if($this->eidebug > 1)
					{
						error_log('importing a velt of type:' . get_class($ve)
							. ' of total:' . count($velts));
					}
					if(!is_a($ve, $hordevtype))
					{
						continue;
					}
					if(($eidStat = $this->import_ncvelt($ve)) > 0)
					{
						$eelt_ids[] = $eidStat;
						$impstats[VELT_IMPORT_STATUS_UPDOK]++;
					}
					else
					{
						$impstats[$eidStat]++;
					}
				}
				// check result stats for errors
				if(($impstats[VELT_IMPORT_STATUS_ERROR] > 0) || $this->eidebug)
				{
					$user_id = $GLOBALS['egw_info']['user']['account_id'];
					error_log('** user[' . $user_id . '] ' . $vtype . ' imports: ' .
					$impstats[VELT_IMPORT_STATUS_ERROR] . ' BAD,' .
					$impstats[VELT_IMPORT_STATUS_NOACC] . ' skip-(insufficient rights), ' .
					$impstats[VELT_IMPORT_STATUS_NOELT] . ' skip-(ignore reimport missings), ' . 
					$impstats[VELT_IMPORT_STATUS_UPDOK] . ' upd-ok, ' .
					$impstats[VELT_IMPORT_STATUS_DELOK] . ' del-ok');
				}
			}

			return $eelt_ids;
		}

		/** Export Egw elements from bound resource as Vcalendar Elements
		*
		* @param array_of_EEltId|array_of_EEltData $eids a list of egw elements (ids)
		* that are to be used for export.
		* @return array_of_VElt a list of  Vcal objects that were exported
		* On error: false (currently error handling not implemented!)
		*/
		function export_velts(&$eids)
		{
			if(!is_array($eids))
			{
				$eids = array($eids);
			}

			$velts = array();
			foreach($eids as $eid)
			{
				if(($velt = $this->export_ncvelt($eid)) !== false)
				{
					$velts[] = $velt;
				}
			}
			return $velts;
		}

		/** Log Egw and Velt update problems to errorlog
		* @private
		* @param string $vtype type of egw elements that is updated (event ,task,..)
		* @param string $fault description of the fault type
		* @param ind $user_id the id of the logged in user
		* @param array $new_egwelt the info converted from the vegwelt to be imported
		* @param array|false $cur_egwelt_ids settings of owner, id and uid
		* field of a possibly found
		* corresponding egw egwelt. When no such egwelt found: false.
		*/
		function _errorlog_evupd($vtype, $fault='ERROR', $user_id, &$new_egwelt, $cur_egwelt)
		{
			// ex output:
			// ** bovevents import for user(12 [pietje]): ERROR
			// info in  current $vtype: id=24, owner=34, uid='adaafa'\n
			// info in Velt for $vtype: id=24, owner=--, uid='dfafasdf'\n

			$uname =(is_numeric($user_id))
				? $user_id . '[' . $GLOBALS['egw']->accounts->id2name($user_id) . ']'
				: '--';
			if($cur_egwelt === false)
			{
				$cid = $cown = $cuid = '--';
			}
			else
			{
				$cid  = $cur_egwelt['id'];
				$cown = $cur_egwelt['owner'];
				$cuid = $cur_egwelt['uid'];
			}
			$nid  = ($vi = $new_egwelt['id']) ? $vi : '--';
			$nown = ($vi = $new_egwelt['owner']) ? $vi : '--';
			$nuid = ($vi = $new_egwelt['uid']) ? $vi : '--';

			error_log('** icalsrv_resourcehandler import for user (' . $cur_eid
				. '['. $uname . ']):' . $fault . '\n'
				. 'info in current egw ' . $vtype . ': id=' . $cid . ',owner=' . $cown . ',uid=' . $cuid .'\n'
				. 'info in velt for ' . $vtype . ': id=' . $nid . ',owner=' . $nown . ',uid=' . $nuid .'\n' );
			//		error_log('vevent info egwelt dump:' . print_r($new_egwelt,true) . '\n <<-----------<<\n');
		}
	}
?>
