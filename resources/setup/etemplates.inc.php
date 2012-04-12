<?php
/**
 * EGroupware - eTemplates for Application resources
 * http://www.egroupware.org
 * generated by soetemplate::dump4setup() 2012-04-12 15:50
 *
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package resources
 * @subpackage setup
 * @version $Id$
 */

$templ_version=1;

$templ_data[] = array('name' => 'resources.add','template' => '','lang' => '','group' => '0','version' => '','data' => 'a:1:{i:0;a:5:{s:4:"type";s:4:"grid";s:4:"data";a:3:{i:0;a:0:{}i:1;a:1:{s:1:"A";a:2:{s:4:"type";s:8:"template";s:4:"name";s:19:"resources.edit_tabs";}}i:2;a:1:{s:1:"A";a:2:{s:4:"type";s:8:"template";s:4:"name";s:21:"resources.add_buttons";}}}s:4:"rows";i:2;s:4:"cols";i:1;s:4:"size";s:4:"100%";}}','size' => '100%','style' => '','modified' => '1094579218',);

$templ_data[] = array('name' => 'resources.add_buttons','template' => '','lang' => '','group' => '0','version' => '','data' => 'a:1:{i:0;a:5:{s:4:"type";s:4:"grid";s:4:"data";a:2:{i:0;a:1:{s:1:"C";s:4:"100%";}i:1;a:3:{s:1:"A";a:4:{s:4:"type";s:6:"button";s:5:"label";s:4:"Save";s:4:"name";s:4:"save";s:4:"help";s:21:"Saves entry and exits";}s:1:"B";a:3:{s:4:"type";s:6:"button";s:5:"label";s:6:"Cancel";s:4:"name";s:6:"cancel";}s:1:"C";a:2:{s:4:"type";s:5:"label";s:5:"align";s:5:"right";}}}s:4:"rows";i:1;s:4:"cols";i:3;s:4:"size";s:4:"100%";}}','size' => '100%','style' => '','modified' => '1094579498',);

$templ_data[] = array('name' => 'resources.admin','template' => '','lang' => '','group' => '0','version' => '','data' => 'a:1:{i:0;a:4:{s:4:"type";s:4:"grid";s:4:"data";a:3:{i:0;a:0:{}i:1;a:1:{s:1:"A";a:3:{s:4:"type";s:8:"checkbox";s:5:"label";s:55:"Don\'t use vfs (this will need a symlink --> see README)";s:4:"name";s:12:"dont_use_vfs";}}i:2;a:1:{s:1:"A";a:4:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"2";i:1;a:3:{s:4:"type";s:6:"button";s:5:"label";s:4:"Save";s:4:"name";s:4:"save";}i:2;a:3:{s:4:"type";s:6:"button";s:5:"label";s:6:"Cancel";s:4:"name";s:6:"cancel";}}}}s:4:"rows";i:2;s:4:"cols";i:1;}}','size' => '','style' => '','modified' => '1109673054',);

$templ_data[] = array('name' => 'resources.edit','template' => '','lang' => '','group' => '0','version' => '1.5.001','data' => 'a:1:{i:0;a:4:{s:4:"type";s:4:"grid";s:4:"data";a:5:{i:0;a:1:{s:1:"A";s:3:"700";}i:1;a:1:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:7:"no_lang";s:1:"1";s:4:"name";s:3:"msg";}}i:2;a:1:{s:1:"A";a:5:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"3";i:1;a:5:{s:4:"type";s:4:"text";s:5:"label";s:4:"Name";s:4:"name";s:4:"name";s:4:"help";s:16:"Name of resource";s:6:"needed";s:1:"1";}i:2;a:3:{s:4:"type";s:4:"text";s:5:"label";s:16:"Inventory number";s:4:"name";s:16:"inventory_number";}i:3;a:7:{s:4:"type";s:6:"select";s:5:"label";s:8:"Category";s:7:"no_lang";s:1:"1";s:4:"name";s:6:"cat_id";s:6:"needed";s:1:"1";s:4:"help";s:44:"Which category does this resource belong to?";s:5:"align";s:5:"right";}}}i:3;a:1:{s:1:"A";a:2:{s:4:"type";s:8:"template";s:4:"name";s:19:"resources.edit_tabs";}}i:4;a:1:{s:1:"A";a:2:{s:4:"type";s:8:"template";s:4:"name";s:22:"resources.edit_buttons";}}}s:4:"rows";i:4;s:4:"cols";i:1;}}','size' => '','style' => '','modified' => '1221027586',);

$templ_data[] = array('name' => 'resources.edit_buttons','template' => '','lang' => '','group' => '0','version' => '','data' => 'a:1:{i:0;a:5:{s:4:"type";s:4:"grid";s:4:"data";a:2:{i:0;a:1:{s:1:"C";s:4:"100%";}i:1;a:3:{s:1:"A";a:4:{s:4:"type";s:6:"button";s:5:"label";s:4:"Save";s:4:"name";s:4:"save";s:4:"help";s:21:"Saves entry and exits";}s:1:"B";a:3:{s:4:"type";s:6:"button";s:5:"label";s:6:"Cancel";s:7:"onclick";s:15:"window.close();";}s:1:"C";a:5:{s:4:"type";s:6:"button";s:5:"label";s:6:"Delete";s:5:"align";s:5:"right";s:4:"name";s:6:"delete";s:7:"onclick";s:61:"return confirm(\'Do you really want do delte this resource?\');";}}}s:4:"rows";i:1;s:4:"cols";i:3;s:4:"size";s:4:"100%";}}','size' => '100%','style' => '','modified' => '1093597552',);

$templ_data[] = array('name' => 'resources.edit_pictures','template' => '','lang' => '','group' => '0','version' => '','data' => 'a:1:{i:0;a:4:{s:4:"type";s:4:"grid";s:4:"data";a:4:{i:0;a:3:{s:2:"c1";s:3:"nmr";s:2:"c2";s:3:"nmr";s:2:"c3";s:3:"nmr";}i:1;a:3:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:5:"label";s:26:"Use general resources icon";s:5:"align";s:5:"right";}s:1:"B";a:3:{s:4:"type";s:5:"radio";s:4:"size";s:7:"gen_src";s:4:"name";s:11:"picture_src";}s:1:"C";a:3:{s:4:"type";s:6:"select";s:7:"no_lang";s:1:"1";s:4:"name";s:12:"gen_src_list";}}i:2;a:3:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:5:"label";s:23:"Use the category\'s icon";s:5:"align";s:5:"right";}s:1:"B";a:3:{s:4:"type";s:5:"radio";s:4:"size";s:7:"cat_src";s:4:"name";s:11:"picture_src";}s:1:"C";a:1:{s:4:"type";s:5:"label";}}i:3;a:3:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:5:"label";s:15:"Use own picture";s:5:"align";s:5:"right";}s:1:"B";a:3:{s:4:"type";s:5:"radio";s:4:"size";s:7:"own_src";s:4:"name";s:11:"picture_src";}s:1:"C";a:2:{s:4:"type";s:4:"file";s:4:"name";s:8:"own_file";}}}s:4:"rows";i:3;s:4:"cols";i:3;}}','size' => '','style' => '','modified' => '1108638846',);

$templ_data[] = array('name' => 'resources.edit_tabs','template' => '','lang' => '','group' => '0','version' => '','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:3:{i:0;a:3:{s:2:"c1";s:7:"row_off";s:1:"A";s:3:"700";s:2:"h2";s:4:"100%";}i:1;a:3:{s:1:"A";a:5:{s:4:"type";s:3:"tab";s:4:"span";s:3:"all";s:5:"label";s:40:"General|Description|Picture|Links|Custom";s:4:"name";s:34:"general|page|pictures|links|custom";s:4:"help";s:164:"General informations about resource|Informations about the location of resource|Prizeing information for booking or buying|Web-Page of resource|Pictures or resource";}s:1:"B";a:1:{s:4:"type";s:5:"label";}s:1:"C";a:1:{s:4:"type";s:5:"label";}}i:2;a:3:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:4:"span";s:3:"all";}s:1:"B";a:1:{s:4:"type";s:5:"label";}s:1:"C";a:1:{s:4:"type";s:5:"label";}}}s:4:"rows";i:2;s:4:"cols";i:3;s:4:"size";s:4:",380";s:7:"options";a:1:{i:1;s:3:"380";}}}','size' => ',380','style' => '','modified' => '1116664222',);

$templ_data[] = array('name' => 'resources.edit_tabs.accessories','template' => '','lang' => '','group' => '0','version' => '','data' => 'a:1:{i:0;a:4:{s:4:"type";s:4:"grid";s:4:"data";a:2:{i:0;a:0:{}i:1;a:1:{s:1:"A";a:3:{s:4:"type";s:9:"nextmatch";s:4:"name";s:2:"nm";s:4:"size";s:19:"resources.show.rows";}}}s:4:"rows";i:1;s:4:"cols";i:1;}}','size' => '','style' => '','modified' => '1109668181',);

$templ_data[] = array('name' => 'resources.edit_tabs.custom','template' => '','lang' => '','group' => '0','version' => '','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:3:{i:0;a:1:{s:2:"c1";s:4:",top";}i:1;a:1:{s:1:"A";a:1:{s:4:"type";s:12:"customfields";}}i:2;a:1:{s:1:"A";a:1:{s:4:"type";s:5:"label";}}}s:4:"rows";i:2;s:4:"cols";i:1;s:4:"size";s:7:"700,380";s:7:"options";a:2:{i:0;s:3:"700";i:1;s:3:"380";}}}','size' => '700,380','style' => '','modified' => '1131988154',);

$templ_data[] = array('name' => 'resources.edit_tabs.general','template' => '','lang' => '','group' => '0','version' => '','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:9:{i:0;a:2:{s:1:"C";s:2:"10";s:2:"h8";s:4:"100%";}i:1;a:4:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:19:"Description (short)";}s:1:"B";a:4:{s:4:"type";s:4:"text";s:4:"size";s:6:"50,100";s:4:"name";s:17:"short_description";s:4:"help";s:29:"Short description of resource";}s:1:"C";a:1:{s:4:"type";s:5:"label";}s:1:"D";a:2:{s:4:"type";s:5:"label";s:5:"label";s:29:"Short description of resource";}}i:2;a:4:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:8:"Location";}s:1:"B";a:4:{s:4:"type";s:4:"text";s:4:"size";s:6:"50,100";s:4:"name";s:8:"location";s:4:"help";s:20:"Location of resource";}s:1:"C";a:1:{s:4:"type";s:5:"label";}s:1:"D";a:2:{s:4:"type";s:5:"label";s:5:"label";s:28:"Where to find this resource?";}}i:3;a:4:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:19:"Storage information";}s:1:"B";a:4:{s:4:"type";s:4:"text";s:4:"size";s:6:"50,100";s:4:"name";s:12:"storage_info";s:4:"help";s:25:"Information about storage";}s:1:"C";a:1:{s:4:"type";s:5:"label";}s:1:"D";a:2:{s:4:"type";s:5:"label";s:5:"label";s:25:"Information about storage";}}i:4;a:4:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:8:"Quantity";}s:1:"B";a:4:{s:4:"type";s:4:"text";s:4:"size";s:4:"5,10";s:4:"name";s:8:"quantity";s:4:"help";s:20:"Quantity of resource";}s:1:"C";a:1:{s:4:"type";s:5:"label";}s:1:"D";a:2:{s:4:"type";s:5:"label";s:5:"label";s:20:"Quantity of resource";}}i:5;a:4:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:7:"Useable";}s:1:"B";a:4:{s:4:"type";s:4:"text";s:4:"size";s:4:"5,10";s:4:"name";s:7:"useable";s:4:"help";s:29:"How many of them are useable?";}s:1:"C";a:1:{s:4:"type";s:5:"label";}s:1:"D";a:2:{s:4:"type";s:5:"label";s:5:"label";s:38:"How many of the resources are useable?";}}i:6;a:4:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:8:"Bookable";}s:1:"B";a:3:{s:4:"type";s:8:"checkbox";s:4:"name";s:8:"bookable";s:4:"help";s:21:"Is resource bookable?";}s:1:"C";a:1:{s:4:"type";s:5:"label";}s:1:"D";a:2:{s:4:"type";s:5:"label";s:5:"label";s:26:"Is this resource bookable?";}}i:7;a:4:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:5:"label";s:7:"Buyable";s:8:"disabled";s:1:"1";}s:1:"B";a:4:{s:4:"type";s:8:"checkbox";s:4:"name";s:7:"buyable";s:4:"help";s:20:"Is resource buyable?";s:8:"disabled";s:1:"1";}s:1:"C";a:1:{s:4:"type";s:5:"label";}s:1:"D";a:3:{s:4:"type";s:5:"label";s:5:"label";s:25:"Is this resource buyable?";s:8:"disabled";s:1:"1";}}i:8;a:4:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:1:{s:4:"type";s:5:"label";}s:1:"C";a:1:{s:4:"type";s:5:"label";}s:1:"D";a:1:{s:4:"type";s:5:"label";}}}s:4:"rows";i:8;s:4:"cols";i:4;s:4:"size";s:7:"700,380";s:7:"options";a:2:{i:0;s:3:"700";i:1;s:3:"380";}}}','size' => '700,380','style' => '','modified' => '1093597682',);

$templ_data[] = array('name' => 'resources.edit_tabs.links','template' => '','lang' => '','group' => '0','version' => '','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:6:{i:0;a:7:{s:1:"A";s:3:"100";s:2:"c1";s:2:"th";s:2:"c2";s:3:"row";s:2:"c3";s:2:"th";s:2:"c4";s:11:"row_off,top";s:2:"h4";s:3:"164";s:2:"h5";s:4:"100%";}i:1;a:2:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:4:"span";s:3:"all";s:5:"label";s:16:"Create new links";}s:1:"B";a:1:{s:4:"type";s:5:"label";}}i:2;a:2:{s:1:"A";a:3:{s:4:"type";s:7:"link-to";s:4:"span";s:3:"all";s:4:"name";s:7:"link_to";}s:1:"B";a:1:{s:4:"type";s:5:"label";}}i:3;a:2:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:4:"span";s:3:"all";s:5:"label";s:14:"Existing links";}s:1:"B";a:1:{s:4:"type";s:5:"label";}}i:4;a:2:{s:1:"A";a:3:{s:4:"type";s:9:"link-list";s:4:"span";s:3:"all";s:4:"name";s:7:"link_to";}s:1:"B";a:1:{s:4:"type";s:5:"label";}}i:5;a:2:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:1:{s:4:"type";s:5:"label";}}}s:4:"rows";i:5;s:4:"cols";i:2;s:4:"size";s:7:"700,380";s:7:"options";a:2:{i:0;s:3:"700";i:1;s:3:"380";}}}','size' => '700,380','style' => '','modified' => '1109248913',);

$templ_data[] = array('name' => 'resources.edit_tabs.page','template' => '','lang' => '','group' => '0','version' => '','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:2:{i:0;a:1:{s:2:"c1";s:4:",top";}i:1;a:1:{s:1:"A";a:4:{s:4:"type";s:8:"htmlarea";s:4:"name";s:16:"long_description";s:4:"help";s:26:"Web-Site for this resource";s:4:"size";s:23:"simple,350px,700px,true";}}}s:4:"rows";i:1;s:4:"cols";i:1;s:4:"size";s:8:"100%,380";s:7:"options";a:2:{i:0;s:4:"100%";i:1;s:3:"380";}}}','size' => '100%,380','style' => '','modified' => '1093599237',);

$templ_data[] = array('name' => 'resources.edit_tabs.pictures','template' => '','lang' => '','group' => '0','version' => '','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:3:{i:0;a:2:{s:2:"c1";s:4:",top";s:2:"h2";s:4:"100%";}i:1;a:1:{s:1:"A";a:4:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"2";i:1;a:4:{s:4:"type";s:4:"vbox";s:4:"size";s:1:"2";i:1;a:2:{s:4:"type";s:5:"image";s:4:"name";s:16:"resource_picture";}i:2;a:1:{s:4:"type";s:5:"label";}}i:2;a:4:{s:4:"type";s:8:"groupbox";s:4:"size";s:1:"1";i:1;a:4:{s:4:"type";s:4:"grid";s:4:"data";a:4:{i:0;a:0:{}i:1;a:3:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:5:"label";s:26:"Use general resources icon";s:5:"align";s:5:"right";}s:1:"B";a:3:{s:4:"type";s:5:"radio";s:4:"size";s:7:"gen_src";s:4:"name";s:11:"picture_src";}s:1:"C";a:3:{s:4:"type";s:6:"select";s:7:"no_lang";s:1:"1";s:4:"name";s:12:"gen_src_list";}}i:2;a:3:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:5:"label";s:23:"Use the category\'s icon";s:5:"align";s:5:"right";}s:1:"B";a:3:{s:4:"type";s:5:"radio";s:4:"size";s:7:"cat_src";s:4:"name";s:11:"picture_src";}s:1:"C";a:1:{s:4:"type";s:5:"label";}}i:3;a:3:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:5:"label";s:15:"Use own picture";s:5:"align";s:5:"right";}s:1:"B";a:3:{s:4:"type";s:5:"radio";s:4:"size";s:7:"own_src";s:4:"name";s:11:"picture_src";}s:1:"C";a:2:{s:4:"type";s:4:"file";s:4:"name";s:8:"own_file";}}}s:4:"rows";i:3;s:4:"cols";i:3;}s:5:"label";s:14:"picture source";}}}i:2;a:1:{s:1:"A";a:1:{s:4:"type";s:5:"label";}}}s:4:"rows";i:2;s:4:"cols";i:1;s:4:"size";s:7:"700,380";s:7:"options";a:2:{i:0;s:3:"700";i:1;s:3:"380";}}}','size' => '700,380','style' => '','modified' => '1131048665',);

$templ_data[] = array('name' => 'resources.export_csv_selectors','template' => '','lang' => '','group' => '0','version' => '1.9.001','data' => 'a:1:{i:0;a:4:{s:4:"type";s:4:"grid";s:4:"data";a:3:{i:0;a:0:{}i:1;a:1:{s:1:"A";a:4:{s:4:"type";s:5:"radio";s:5:"label";s:7:"Use all";s:4:"size";s:3:"all";s:4:"name";s:9:"selection";}}i:2;a:1:{s:1:"A";a:4:{s:4:"type";s:5:"radio";s:5:"label";s:18:"Use search results";s:4:"name";s:9:"selection";s:4:"size";s:8:"selected";}}}s:4:"rows";i:2;s:4:"cols";i:1;}}','size' => '','style' => '','modified' => '1289840391',);

$templ_data[] = array('name' => 'resources.resource_select','template' => '','lang' => '','group' => '0','version' => '1.7.001','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:2:{i:0;a:0:{}i:1;a:1:{s:1:"A";a:3:{s:4:"type";s:9:"nextmatch";s:4:"size";s:29:"resources.resource_select.row";s:4:"name";s:2:"nm";}}}s:4:"rows";i:1;s:4:"cols";i:1;s:4:"size";s:4:"100%";s:7:"options";a:1:{i:0;s:4:"100%";}}}','size' => '100%','style' => '','modified' => '1254212129',);

$templ_data[] = array('name' => 'resources.resource_select.header','template' => '','lang' => '','group' => '0','version' => '','data' => 'a:1:{i:0;a:4:{s:4:"type";s:4:"grid";s:4:"data";a:2:{i:0;a:0:{}i:1;a:2:{s:1:"A";a:3:{s:4:"type";s:5:"image";s:4:"name";s:6:"navbar";s:5:"label";s:16:"Select resources";}s:1:"B";a:3:{s:4:"type";s:5:"label";s:4:"name";s:4:"$msg";s:7:"no_lang";s:1:"1";}}}s:4:"rows";i:1;s:4:"cols";i:2;}}','size' => '','style' => '','modified' => '1118502303',);

$templ_data[] = array('name' => 'resources.resource_select.row','template' => '','lang' => '','group' => '0','version' => '1.7.002','data' => 'a:2:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:3:{i:0;a:3:{s:2:"c1";s:2:"th";s:2:"c2";s:3:"row";s:1:"G";s:2:"3%";}i:1;a:7:{s:1:"A";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:4:"Name";s:4:"name";s:4:"name";}s:1:"B";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:8:"Category";s:4:"name";s:6:"cat_id";}s:1:"C";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:11:"Description";s:4:"name";s:17:"short_description";}s:1:"D";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:8:"Location";s:4:"name";s:8:"location";}s:1:"E";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:19:"Storage information";s:4:"name";s:12:"storage_info";}s:1:"F";a:3:{s:4:"type";s:16:"nextmatch-header";s:5:"label";s:8:"Quantity";s:4:"name";s:8:"quantity";}s:1:"G";a:1:{s:4:"type";s:5:"label";}}i:2;a:7:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:4:"name";s:12:"${row}[name]";s:7:"no_lang";s:1:"1";}s:1:"B";a:3:{s:4:"type";s:10:"select-cat";s:4:"name";s:14:"${row}[cat_id]";s:8:"readonly";s:1:"1";}s:1:"C";a:2:{s:4:"type";s:5:"label";s:4:"name";s:25:"${row}[short_description]";}s:1:"D";a:2:{s:4:"type";s:5:"label";s:4:"name";s:16:"${row}[location]";}s:1:"E";a:2:{s:4:"type";s:5:"label";s:4:"name";s:20:"${row}[storage_info]";}s:1:"F";a:8:{s:4:"type";s:4:"hbox";s:7:"no_lang";s:1:"1";s:4:"data";a:2:{i:0;a:0:{}i:1;a:3:{s:1:"A";a:2:{s:4:"type";s:3:"int";s:4:"size";s:4:"1,,2";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:2:"of";}s:1:"C";a:3:{s:4:"type";s:5:"label";s:4:"name";s:15:"${row}[useable]";s:7:"no_lang";s:1:"1";}}}s:4:"rows";i:1;s:4:"cols";i:3;s:4:"size";s:6:"2,,0,0";i:1;a:3:{s:4:"type";s:3:"int";s:4:"size";s:4:"1,,2";s:4:"name";s:19:"${row}[default_qty]";}i:2;a:5:{s:4:"type";s:3:"int";s:5:"label";s:2:"of";s:4:"span";s:9:",leftPad5";s:4:"name";s:15:"${row}[useable]";s:8:"readonly";s:1:"1";}}s:1:"G";a:6:{s:4:"type";s:6:"button";s:4:"size";s:6:"select";s:7:"onclick";s:88:"addOption(\'$row_cont[name]\',$row_cont[res_id],this.id,$row_cont[useable]); return false;";s:6:"needed";s:1:"1";s:5:"label";s:15:"select resource";s:4:"name";s:6:"${row}";}}}s:4:"rows";i:2;s:4:"cols";i:7;s:4:"size";s:15:"100%,,,selTable";s:7:"options";a:2:{i:3;s:8:"selTable";i:0;s:4:"100%";}}i:1;a:10:{s:4:"type";s:4:"vbox";i:1;a:9:{s:4:"type";s:4:"hbox";s:5:"align";s:6:"center";s:4:"data";a:2:{i:0;a:0:{}i:1;a:1:{s:1:"A";a:1:{s:4:"type";s:5:"label";}}}s:4:"rows";i:1;s:4:"cols";i:1;s:4:"size";s:1:"2";i:1;a:3:{s:4:"type";s:5:"label";s:5:"label";s:9:"Selection";s:4:"span";s:4:",big";}i:2;a:6:{s:4:"type";s:6:"button";s:4:"size";s:6:"delete";s:5:"label";s:15:"clear selection";s:7:"onclick";s:38:"removeSelectedOptions(); return false;";s:6:"needed";s:1:"1";s:5:"align";s:5:"right";}s:4:"span";s:3:",th";}s:4:"data";a:4:{i:0;a:1:{s:2:"c1";s:2:"th";}i:1;a:1:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:4:"span";s:5:"0,big";s:5:"label";s:9:"Selection";}}i:2;a:1:{s:1:"A";a:5:{s:4:"type";s:6:"select";s:4:"size";s:3:"13+";s:4:"name";s:9:"selectbox";s:4:"span";s:5:"0,sel";s:7:"no_lang";s:1:"1";}}i:3;a:1:{s:1:"A";a:4:{s:4:"type";s:6:"button";s:5:"label";s:5:"Close";s:5:"align";s:6:"center";s:7:"onclick";s:31:"oneLineSubmit();window.close();";}}}s:4:"rows";i:3;s:4:"cols";i:1;s:5:"align";s:5:"right";s:4:"size";s:1:"3";i:2;a:5:{s:4:"type";s:6:"select";s:4:"size";s:3:"13+";s:4:"name";s:9:"selectbox";s:4:"span";s:5:"0,sel";s:7:"no_lang";s:1:"1";}i:3;a:4:{s:4:"type";s:6:"button";s:5:"label";s:5:"Close";s:5:"align";s:6:"center";s:7:"onclick";s:31:"oneLineSubmit();window.close();";}s:4:"span";s:13:",selSelection";}}','size' => '100%,,,selTable','style' => '','modified' => '1126031297',);

$templ_data[] = array('name' => 'resources.resource_selectbox','template' => '','lang' => '','group' => '0','version' => '','data' => 'a:1:{i:0;a:4:{s:4:"type";s:4:"grid";s:4:"data";a:2:{i:0;a:1:{s:2:"c1";s:7:",bottom";}i:1;a:2:{s:1:"A";a:4:{s:4:"type";s:6:"select";s:4:"size";s:3:"14+";s:4:"name";s:9:"resources";s:7:"no_lang";s:1:"1";}s:1:"B";a:6:{s:4:"type";s:6:"button";s:7:"onclick";s:160:"window.open(egw::link(\'/index.php\',\'menuaction=resources.resources_ui.select\'),\'\',\'dependent=yes,width=600,height=450,scrollbars=yes,status=yes\'); return false;";s:5:"label";s:13:"Add resources";s:4:"size";s:6:"navbar";s:6:"needed";s:1:"1";s:4:"name";s:5:"popup";}}}s:4:"rows";i:1;s:4:"cols";i:2;}}','size' => '','style' => '','modified' => '1118520536',);

$templ_data[] = array('name' => 'resources.show','template' => '','lang' => '','group' => '0','version' => '1.9.002','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:6:{i:0;a:4:{s:1:"A";s:4:"100%";s:2:"h1";s:6:",!@msg";s:2:"h4";s:34:",!@nm[selectcols]=/legacy_actions/";s:2:"h2";s:2:",1";}i:1;a:1:{s:1:"A";a:4:{s:4:"type";s:5:"label";s:4:"span";s:11:"all,message";s:4:"name";s:3:"msg";s:5:"align";s:6:"center";}}i:2;a:1:{s:1:"A";a:3:{s:4:"type";s:8:"template";s:5:"align";s:5:"right";s:4:"name";s:8:"nm_right";}}i:3;a:1:{s:1:"A";a:4:{s:4:"type";s:9:"nextmatch";s:4:"size";s:19:"resources.show.rows";s:7:"no_lang";s:1:"1";s:4:"name";s:2:"nm";}}i:4;a:1:{s:1:"A";a:6:{s:5:"align";s:5:"right";s:4:"span";s:3:"all";s:4:"type";s:4:"hbox";s:4:"size";s:1:"2";i:1;a:5:{s:5:"label";s:13:"Select action";s:7:"onclick";s:184:"if (!egw_globalObjectManager.getObjectById(\'resources.show.rows\').executeActionImplementation(this, \'popup\')) alert(egw::lang(\'You need to select some entries first!\')); return false;;";s:4:"name";s:14:"legacy_actions";s:4:"type";s:10:"buttononly";s:4:"help";s:13:"Select action";}i:2;a:8:{s:5:"label";s:9:"Check all";s:6:"needed";s:1:"1";s:7:"onclick";s:95:"egw_globalObjectManager.getObjectById(\'resources.show.rows\').toggleAllSelected(); return false;";s:4:"name";s:9:"check_all";s:4:"type";s:6:"button";s:4:"size";s:9:"arrow_ltr";s:4:"help";s:9:"Check all";s:4:"span";s:14:",checkAllArrow";}}}i:5;a:1:{s:1:"A";a:5:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"3";i:1;a:4:{s:4:"type";s:6:"button";s:5:"label";s:3:"Add";s:7:"onclick";s:206:"window.open(egw::link(\'/index.php\',\'menuaction=resources.resources_ui.edit\'),\'\',\'dependent=yes,width=800,height=600,location=no,menubar=no,toolbar=no,scrollbars=yes,status=yes\'); return false; return false;";s:4:"name";s:3:"add";}i:2;a:4:{s:4:"type";s:6:"button";s:5:"label";s:13:"Add accessory";s:4:"name";s:7:"add_sub";s:7:"onclick";s:235:"window.open(egw::link(\'/index.php\',\'menuaction=resources.resources_ui.edit&content=0&accessory_of=$cont[view_accs_of]\'),\'\',\'dependent=yes,width=800,height=600,location=no,menubar=no,toolbar=no,scrollbars=yes,status=yes\'); return false;";}i:3;a:3:{s:4:"type";s:6:"button";s:5:"label";s:4:"Back";s:4:"name";s:4:"back";}}}}s:4:"rows";i:5;s:4:"cols";i:1;s:4:"size";s:4:"100%";s:7:"options";a:1:{i:0;s:4:"100%";}}}','size' => '100%','style' => '.image16 img {
 max-width: 16px;
 max-height: 16px;
}','modified' => '1331039067',);

$templ_data[] = array('name' => 'resources.show.actions','template' => '','lang' => '','group' => '0','version' => '','data' => 'a:1:{i:0;a:5:{s:4:"type";s:4:"grid";s:4:"data";a:3:{i:0;a:3:{s:1:"F";s:2:"3%";s:2:"c1";s:3:"nmh";s:2:"c2";s:3:"nmr";}i:1;a:6:{s:1:"A";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:4:"Name";s:4:"name";s:4:"name";}s:1:"B";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:17:"Short description";s:4:"name";s:17:"short_description";}s:1:"C";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:7:"Useable";s:4:"name";s:7:"useable";}s:1:"D";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:8:"Category";s:4:"name";s:6:"cat_id";}s:1:"E";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:8:"Location";s:4:"name";s:8:"location";}s:1:"F";a:3:{s:4:"type";s:8:"template";s:5:"align";s:5:"right";s:4:"name";s:29:"resources.show.actions_header";}}i:2;a:6:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:7:"no_lang";s:1:"1";s:4:"name";s:12:"${row}[name]";}s:1:"B";a:3:{s:4:"type";s:5:"label";s:7:"no_lang";s:1:"1";s:4:"name";s:25:"${row}[short_description]";}s:1:"C";a:3:{s:4:"type";s:5:"label";s:7:"no_lang";s:1:"1";s:4:"name";s:14:"${row}[usable]";}s:1:"D";a:3:{s:4:"type";s:5:"label";s:7:"no_lang";s:1:"1";s:4:"name";s:16:"${row}[category]";}s:1:"E";a:3:{s:4:"type";s:5:"label";s:7:"no_lang";s:1:"1";s:4:"name";s:16:"${row}[location]";}s:1:"F";a:3:{s:4:"type";s:6:"button";s:5:"align";s:5:"right";s:4:"name";s:20:"lukas[$row_cont[id]]";}}}s:4:"rows";i:2;s:4:"cols";i:6;s:4:"size";s:4:"100%";}}','size' => '100%','style' => '','modified' => '1098891355',);

$templ_data[] = array('name' => 'resources.show.actions_header','template' => '','lang' => '','group' => '0','version' => '','data' => 'a:1:{i:0;a:4:{s:4:"type";s:4:"grid";s:4:"data";a:2:{i:0;a:0:{}i:1;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:6:"Action";}s:1:"B";a:3:{s:4:"type";s:6:"button";s:4:"size";s:9:"check.png";s:4:"name";s:30:"javascript:check_all(\'select\')";}}}s:4:"rows";i:1;s:4:"cols";i:2;}}','size' => '','style' => '','modified' => '1094025049',);

$templ_data[] = array('name' => 'resources.show.nm_right','template' => '','lang' => '','group' => '0','version' => '1.9.002','data' => 'a:1:{i:0;a:4:{s:4:"type";s:4:"grid";s:4:"data";a:2:{i:0;a:0:{}i:1;a:1:{s:1:"A";a:4:{s:4:"type";s:10:"buttononly";s:5:"label";s:3:"Add";s:7:"onclick";s:206:"window.open(egw::link(\'/index.php\',\'menuaction=resources.resources_ui.edit\'),\'\',\'dependent=yes,width=800,height=600,location=no,menubar=no,toolbar=no,scrollbars=yes,status=yes\'); return false; return false;";s:4:"name";s:3:"add";}}}s:4:"rows";i:1;s:4:"cols";i:1;}}','size' => '','style' => '','modified' => '1331036827',);

$templ_data[] = array('name' => 'resources.show.rows','template' => '','lang' => '','group' => '0','version' => '1.9.001','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:3:{i:0;a:4:{s:1:"C";s:2:"3%";s:2:"c1";s:3:"nmh";s:2:"c2";s:24:"nmr $row_cont[class],top";s:1:"G";s:2:"5%";}i:1;a:7:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:4:{s:4:"type";s:4:"vbox";s:4:"size";s:1:"2";i:1;a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:4:"Name";s:4:"name";s:4:"name";}i:2;a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:17:"Short description";s:4:"name";s:17:"short_description";}}s:1:"C";a:4:{s:4:"type";s:4:"vbox";s:4:"size";s:1:"2";i:1;a:4:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:7:"Useable";s:4:"name";s:7:"useable";s:4:"help";s:36:"How many of this resource are usable";}i:2;a:4:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:8:"Quantity";s:4:"name";s:8:"quantity";s:4:"help";s:32:"How many of this resource exists";}}s:1:"D";a:4:{s:4:"type";s:4:"vbox";s:4:"size";s:1:"2";i:1;a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:8:"Category";s:4:"name";s:6:"cat_id";}i:2;a:2:{s:4:"type";s:5:"label";s:5:"label";s:13:"Administrator";}}s:1:"E";a:4:{s:4:"type";s:4:"vbox";s:4:"size";s:1:"2";i:1;a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:8:"Location";s:4:"name";s:8:"location";}i:2;a:2:{s:4:"type";s:5:"label";s:5:"label";s:19:"Storage information";}}s:1:"F";a:2:{s:4:"type";s:22:"nextmatch-customfields";s:4:"name";s:12:"customfields";}s:1:"G";a:7:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"4";i:1;a:3:{s:4:"type";s:16:"nextmatch-header";s:5:"label";s:7:"Actions";s:4:"name";s:14:"legacy_actions";}i:2;a:1:{s:4:"type";s:5:"label";}i:3;a:2:{s:4:"type";s:5:"label";s:6:"needed";s:1:"1";}s:5:"align";s:5:"right";i:4;a:11:{s:4:"type";s:6:"button";s:4:"size";s:9:"check.png";s:5:"label";s:9:"Check all";s:5:"align";s:5:"right";s:4:"name";s:9:"check_all";s:4:"help";s:9:"Check all";i:1;a:1:{s:4:"type";s:5:"label";}i:2;a:1:{s:4:"type";s:5:"label";}i:3;a:1:{s:4:"type";s:5:"label";}s:6:"needed";s:1:"1";s:7:"onclick";s:76:"toggle_all(this.form,form::name(\'nm[rows][checkbox][]\'),true); return false;";}}}i:2;a:7:{s:1:"A";a:5:{s:4:"type";s:6:"button";s:5:"align";s:6:"center";s:4:"name";s:21:"${row}[picture_thumb]";s:4:"size";s:24:"$row_cont[picture_thumb]";s:7:"onclick";s:216:"window.open(egw::link(\'/index.php\',\'menuaction=resources.resources_ui.show&res_id=$row_cont[res_id]\'),\'\',\'dependent=yes,width=800,height=600,location=no,menubar=no,toolbar=no,scrollbars=yes,status=yes\');return false;";}s:1:"B";a:4:{s:4:"type";s:4:"vbox";s:4:"size";s:1:"2";i:1;a:3:{s:4:"type";s:5:"label";s:7:"no_lang";s:1:"1";s:4:"name";s:12:"${row}[name]";}i:2;a:3:{s:4:"type";s:5:"label";s:7:"no_lang";s:1:"1";s:4:"name";s:25:"${row}[short_description]";}}s:1:"C";a:5:{s:4:"type";s:4:"vbox";s:4:"size";s:1:"2";s:5:"align";s:5:"right";i:1;a:4:{s:4:"type";s:5:"label";s:7:"no_lang";s:1:"1";s:5:"align";s:5:"right";s:4:"name";s:15:"${row}[useable]";}i:2;a:4:{s:4:"type";s:5:"label";s:7:"no_lang";s:1:"1";s:5:"align";s:5:"right";s:4:"name";s:16:"${row}[quantity]";}}s:1:"D";a:4:{s:4:"type";s:4:"vbox";s:4:"size";s:1:"2";i:1;a:4:{s:4:"type";s:10:"select-cat";s:7:"no_lang";s:1:"1";s:4:"name";s:14:"${row}[cat_id]";s:8:"readonly";s:1:"1";}i:2;a:4:{s:4:"type";s:14:"select-account";s:7:"no_lang";s:1:"1";s:4:"name";s:13:"${row}[admin]";s:8:"readonly";s:1:"1";}}s:1:"E";a:4:{s:4:"type";s:4:"vbox";s:4:"size";s:1:"2";i:1;a:3:{s:4:"type";s:5:"label";s:7:"no_lang";s:1:"1";s:4:"name";s:16:"${row}[location]";}i:2;a:3:{s:4:"type";s:5:"label";s:7:"no_lang";s:1:"1";s:4:"name";s:20:"${row}[storage_info]";}}s:1:"F";a:2:{s:4:"type";s:17:"customfields-list";s:4:"name";s:4:"$row";}s:1:"G";a:7:{s:4:"type";s:4:"grid";s:5:"align";s:5:"right";s:4:"data";a:3:{i:0;a:0:{}i:1;a:4:{s:1:"A";a:8:{s:4:"type";s:6:"button";s:4:"size";s:17:"navbar,trams16x16";s:5:"label";s:18:"Book this resource";s:5:"align";s:6:"center";s:4:"help";s:18:"Book this resource";s:7:"onclick";s:223:"window.open(egw::link(\'/index.php\',\'menuaction=calendar.calendar_uiforms.edit&participants=r$cont[res_id]\'),\'\',\'dependent=yes,width=750,height=400,location=no,menubar=no,toolbar=no,scrollbars=yes,status=yes\'); return false;";s:4:"name";s:23:"bookable[$cont[res_id]]";s:4:"span";s:8:",image16";}s:1:"B";a:7:{s:4:"type";s:6:"button";s:4:"size";s:15:"edit,trans16x16";s:5:"label";s:4:"Edit";s:5:"align";s:6:"center";s:4:"help";s:15:"Edit this entry";s:7:"onclick";s:213:"window.open(egw::link(\'/index.php\',\'menuaction=resources.resources_ui.edit&res_id=$cont[res_id]\'),\'\',\'dependent=yes,width=800,height=600,location=no,menubar=no,toolbar=no,scrollbars=yes,status=yes\'); return false;";s:4:"name";s:19:"edit[$cont[res_id]]";}s:1:"C";a:7:{s:4:"type";s:6:"button";s:4:"size";s:14:"new,trans16x16";s:5:"label";s:38:"Create new accessory for this resource";s:5:"align";s:6:"center";s:4:"name";s:22:"new_acc[$cont[res_id]]";s:4:"help";s:38:"Create new accessory for this resource";s:7:"onclick";s:228:"window.open(egw::link(\'/index.php\',\'menuaction=resources.resources_ui.edit&res_id=0&accessory_of=$cont[res_id]\'),\'\',\'dependent=yes,width=800,height=600,location=no,menubar=no,toolbar=no,scrollbars=yes,status=yes\'); return false;";}s:1:"D";a:7:{s:4:"type";s:6:"button";s:4:"size";s:15:"view,trans16x16";s:5:"label";s:4:"View";s:5:"align";s:5:"right";s:4:"name";s:19:"view[$cont[res_id]]";s:4:"help";s:15:"View this entry";s:7:"onclick";s:213:"window.open(egw::link(\'/index.php\',\'menuaction=resources.resources_ui.show&res_id=$cont[res_id]\'),\'\',\'dependent=yes,width=800,height=600,location=no,menubar=no,toolbar=no,scrollbars=yes,status=yes\'); return false;";}}i:2;a:4:{s:1:"A";a:7:{s:4:"type";s:6:"button";s:4:"size";s:27:"calendar/planner,trans16x16";s:5:"label";s:25:"Show calendar of resource";s:5:"align";s:6:"center";s:4:"help";s:25:"Show calendar of resource";s:4:"name";s:23:"calendar[$cont[res_id]]";s:7:"onclick";s:129:"location=egw::link(\'/index.php\',\'menuaction=calendar.calendar_uiviews.planner&sortby=user&owner=0,r$cont[res_id]\'); return false;";}s:1:"B";a:7:{s:4:"type";s:6:"button";s:5:"label";s:6:"Delete";s:5:"align";s:6:"center";s:4:"name";s:21:"delete[$cont[res_id]]";s:4:"help";s:17:"Delete this entry";s:7:"onclick";s:36:"return confirm(\'Delete this entry\');";s:4:"size";s:17:"delete,trans16x16";}s:1:"C";a:6:{s:4:"type";s:6:"button";s:4:"size";s:19:"view_acc,trans16x16";s:5:"label";s:34:"View accessories for this resource";s:5:"align";s:6:"center";s:4:"name";s:23:"view_acc[$cont[res_id]]";s:4:"help";s:34:"View accessories for this resource";}s:1:"D";a:4:{s:4:"type";s:8:"checkbox";s:5:"align";s:5:"right";s:4:"name";s:10:"checkbox[]";s:4:"size";s:13:"$cont[res_id]";}}}s:4:"rows";i:2;s:4:"cols";i:4;s:4:"name";s:6:"${row}";s:7:"options";a:0:{}}}}s:4:"rows";i:2;s:4:"cols";i:7;s:4:"size";s:4:"100%";s:7:"options";a:1:{i:0;s:4:"100%";}}}','size' => '100%','style' => '','modified' => '1300835999',);

$templ_data[] = array('name' => 'resources.showdetails','template' => '','lang' => '','group' => '0','version' => '1.5.001','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:8:{i:0;a:8:{s:2:"c1";s:3:"nmh";s:1:"B";s:4:"100%";s:2:"c5";s:2:"th";s:2:"h3";s:2:"1%";s:2:"c6";s:11:"row_off,top";s:1:"A";s:3:"43%";s:2:"h1";s:5:"240px";s:2:"h5";s:10:",@!link_to";}i:1;a:2:{s:1:"A";a:3:{s:4:"type";s:5:"image";s:4:"name";s:16:"resource_picture";s:5:"align";s:6:"center";}s:1:"B";a:4:{s:4:"type";s:4:"grid";s:4:"data";a:9:{i:0;a:1:{s:2:"c5";s:4:",top";}i:1;a:2:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:4:"size";s:1:"b";s:5:"label";s:5:"Name:";}s:1:"B";a:4:{s:4:"type";s:5:"label";s:4:"size";s:1:"b";s:7:"no_lang";s:1:"1";s:4:"name";s:4:"name";}}i:2;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:17:"Inventory number:";}s:1:"B";a:3:{s:4:"type";s:5:"label";s:7:"no_lang";s:1:"1";s:4:"name";s:16:"inventory_number";}}i:3;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:9:"Category:";}s:1:"B";a:3:{s:4:"type";s:10:"select-cat";s:4:"name";s:6:"cat_id";s:8:"readonly";s:1:"1";}}i:4;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:13:"Responsible: ";}s:1:"B";a:3:{s:4:"type";s:14:"select-account";s:4:"name";s:9:"cat_admin";s:8:"readonly";s:1:"1";}}i:5;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:10:"Quantity: ";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:4:"name";s:8:"quantity";}}i:6;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:8:"Useable:";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:4:"name";s:7:"useable";}}i:7;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:9:"Location:";}s:1:"B";a:3:{s:4:"type";s:5:"label";s:7:"no_lang";s:1:"1";s:4:"name";s:8:"location";}}i:8;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:20:"Storage information:";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:4:"name";s:12:"storage_info";}}}s:4:"rows";i:8;s:4:"cols";i:2;}}i:2;a:2:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:1:{s:4:"type";s:5:"label";}}i:3;a:2:{s:1:"A";a:4:{s:4:"type";s:4:"html";s:4:"span";s:1:"2";s:4:"name";s:11:"description";s:8:"readonly";s:1:"1";}s:1:"B";a:1:{s:4:"type";s:5:"label";}}i:4;a:2:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:1:{s:4:"type";s:5:"label";}}i:5;a:2:{s:1:"A";a:6:{s:4:"type";s:5:"label";s:4:"span";s:1:"2";s:4:"data";a:2:{i:0;a:1:{s:1:"A";s:4:"100%";}i:1;a:4:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:13:"Related links";}s:1:"B";a:6:{s:4:"type";s:6:"button";s:5:"label";s:17:"Buy this resource";s:5:"align";s:5:"right";s:4:"size";s:7:"buyable";s:4:"help";s:17:"Buy this resource";s:4:"name";s:11:"btn_buyable";}s:1:"C";a:4:{s:4:"type";s:6:"button";s:5:"label";s:18:"Book this resource";s:4:"name";s:12:"btn_bookable";s:4:"size";s:8:"bookable";}s:1:"D";a:5:{s:4:"type";s:6:"button";s:5:"label";s:4:"edit";s:4:"name";s:8:"btn_edit";s:4:"size";s:4:"edit";s:4:"help";s:4:"edit";}}}s:4:"rows";i:1;s:4:"cols";i:4;s:5:"label";s:13:"Related links";}s:1:"B";a:1:{s:4:"type";s:5:"label";}}i:6;a:2:{s:1:"A";a:4:{s:4:"type";s:9:"link-list";s:4:"span";s:3:"all";s:4:"name";s:7:"link_to";s:8:"readonly";s:1:"1";}s:1:"B";a:1:{s:4:"type";s:5:"label";}}i:7;a:2:{s:1:"A";a:8:{s:4:"type";s:4:"hbox";s:5:"label";s:6:"Cancel";s:4:"span";s:1:"2";s:4:"size";s:1:"2";i:1;a:9:{s:4:"type";s:4:"hbox";s:5:"label";s:4:"Edit";s:4:"span";s:1:"2";s:4:"name";s:8:"btn_edit";s:4:"size";s:1:"4";i:1;a:5:{s:4:"type";s:6:"button";s:5:"label";s:4:"Edit";s:4:"span";s:1:"2";s:4:"name";s:8:"btn_edit";s:4:"help";s:16:"Buy this article";}i:2;a:3:{s:4:"type";s:6:"button";s:5:"label";s:6:"Cancel";s:7:"onclick";s:14:"window.close()";}i:3;a:6:{s:4:"type";s:6:"button";s:5:"label";s:8:"Calendar";s:4:"span";s:1:"2";s:4:"name";s:12:"btn_calendar";s:4:"help";s:25:"Show calendar of resource";s:7:"onclick";s:120:"opener.location=egw::link(\'/index.php\',\'menuaction=calendar.calendar_uiviews.month&owner=r$cont[res_id]\'); return false;";}i:4;a:5:{s:4:"type";s:6:"button";s:5:"label";s:4:"Book";s:7:"onclick";s:209:"window.open(egw::link(\'/index.php\',\'menuaction=calendar.calendar_uiforms.edit&participants=r$cont[res_id]\'),\'\',\'dependent=yes,width=750,height=400,location=no,menubar=no,toolbar=no,scrollbars=yes,status=yes\');";s:4:"name";s:8:"btn_book";s:4:"help";s:18:"Book this resource";}}i:2;a:4:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"1";s:5:"align";s:5:"right";i:1;a:4:{s:4:"type";s:6:"button";s:5:"label";s:6:"Delete";s:7:"onclick";s:61:"return confirm(\'Do you really want do delte this resource?\');";s:4:"name";s:10:"btn_delete";}}i:3;a:1:{s:4:"type";s:5:"label";}i:4;a:4:{s:4:"type";s:6:"button";s:5:"label";s:6:"Delete";s:4:"name";s:10:"btn_delete";s:5:"align";s:5:"right";}}s:1:"B";a:1:{s:4:"type";s:5:"label";}}}s:4:"rows";i:7;s:4:"cols";i:2;s:4:"size";s:7:"750,480";s:7:"options";a:2:{i:0;s:3:"750";i:1;s:3:"480";}}}','size' => '750,480','style' => '','modified' => '1129667646',);

$templ_data[] = array('name' => 'resources.sitemgr_book','template' => '','lang' => '','group' => '0','version' => '1.9.001','data' => 'a:1:{i:0;a:6:{s:4:"type";s:3:"box";s:4:"data";a:2:{i:0;a:0:{}i:1;a:1:{s:1:"A";a:1:{s:4:"type";s:5:"label";}}}s:4:"rows";i:1;s:4:"cols";i:1;s:4:"size";s:1:"1";i:1;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:12:{i:0;a:6:{s:2:"h2";s:6:",!@msg";s:2:"h7";s:15:",!@contact_form";s:2:"h8";s:15:",!@show_contact";s:2:"h9";s:15:",!@show_contact";s:2:"h4";s:20:",!@short_description";s:2:"h5";s:19:",!@long_description";}i:1;a:2:{s:1:"A";a:2:{s:4:"type";s:6:"hidden";s:4:"name";s:6:"res_id";}s:1:"B";a:1:{s:4:"type";s:5:"label";}}i:2;a:2:{s:1:"A";a:3:{s:4:"type";s:4:"html";s:4:"span";s:11:"all,message";s:4:"name";s:3:"msg";}s:1:"B";a:1:{s:4:"type";s:5:"label";}}i:3;a:2:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:4:"name";s:4:"name";s:4:"span";s:5:",name";}s:1:"B";a:4:{s:4:"type";s:5:"image";s:4:"name";s:7:"picture";s:5:"align";s:5:"right";s:4:"span";s:15:",resource_thumb";}}i:4;a:2:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:4:"name";s:17:"short_description";s:4:"span";s:15:"all,description";}s:1:"B";a:1:{s:4:"type";s:5:"label";}}i:5;a:2:{s:1:"A";a:3:{s:4:"type";s:4:"html";s:4:"span";s:15:"all,description";s:4:"name";s:16:"long_description";}s:1:"B";a:1:{s:4:"type";s:5:"label";}}i:6;a:2:{s:1:"A";a:4:{s:4:"type";s:5:"label";i:1;a:3:{s:4:"type";s:4:"date";s:5:"label";s:4:"Date";s:4:"name";s:4:"date";}i:2;a:2:{s:4:"type";s:13:"date-timeonly";s:4:"name";s:4:"time";}s:5:"label";s:4:"Date";}s:1:"B";a:6:{s:4:"type";s:4:"hbox";s:4:"size";s:6:"4,,0,0";i:1;a:3:{s:4:"type";s:4:"date";s:4:"name";s:4:"date";s:8:"onchange";s:219:"var request = new egw_json_request(\'resources.resources_reserve.ajax_update_planner\',[document.getElementById(\'exec[res_id]\').value, Math.round(window.calendar.date.getTime()/1000)]);request.sendRequest(); return false;";}i:2;a:2:{s:4:"type";s:11:"select-hour";s:4:"name";s:10:"time[hour]";}i:3;a:2:{s:4:"type";s:5:"label";s:5:"label";s:1:":";}i:4;a:3:{s:4:"type";s:13:"select-number";s:4:"name";s:12:"time[minute]";s:4:"size";s:21:",0,59,$cont[duration]";}}}i:7;a:2:{s:1:"A";a:3:{s:4:"type";s:8:"template";s:4:"span";s:3:"all";s:4:"name";s:13:"@contact_form";}s:1:"B";a:1:{s:4:"type";s:5:"label";}}i:8;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:4:"Name";}s:1:"B";a:4:{s:4:"type";s:4:"hbox";s:4:"size";s:6:"2,,0,0";i:1;a:3:{s:4:"type";s:4:"text";s:4:"name";s:7:"n_given";i:1;a:1:{s:4:"type";s:4:"hbox";}}i:2;a:2:{s:4:"type";s:4:"text";s:4:"name";s:8:"n_family";}}}i:9;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:5:"Email";}s:1:"B";a:4:{s:4:"type";s:9:"url-email";s:4:"size";s:2:"48";s:4:"name";s:5:"email";s:6:"needed";s:1:"1";}}i:10;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:10:"Party size";}s:1:"B";a:4:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"2";i:1;a:2:{s:4:"type";s:3:"int";s:4:"name";s:8:"quantity";}i:2;a:4:{s:4:"type";s:6:"button";s:5:"label";s:4:"Book";s:5:"align";s:5:"right";s:4:"name";s:4:"book";}}}i:11;a:2:{s:1:"A";a:5:{s:4:"type";s:3:"box";s:4:"span";s:3:"all";s:4:"name";s:11:"planner_div";s:4:"size";s:1:"1";i:1;a:3:{s:4:"type";s:4:"html";s:4:"span";s:3:"all";s:4:"name";s:7:"planner";}}s:1:"B";a:1:{s:4:"type";s:5:"label";}}}s:4:"rows";i:11;s:4:"cols";i:2;s:4:"size";s:4:"100%";s:7:"options";a:1:{i:0;s:4:"100%";}}}}','size' => '','style' => '.resource_thumb img  {
float:right;
position:absolute;
}','modified' => '1328133598',);

$templ_data[] = array('name' => 'resources.test','template' => '','lang' => '','group' => '0','version' => '','data' => 'a:1:{i:0;a:4:{s:4:"type";s:4:"grid";s:4:"data";a:2:{i:0;a:0:{}i:1;a:1:{s:1:"A";a:3:{s:4:"type";s:16:"resources_select";s:4:"size";s:1:"5";s:4:"name";s:4:"test";}}}s:4:"rows";i:1;s:4:"cols";i:1;}}','size' => '','style' => '','modified' => '1249373358',);

