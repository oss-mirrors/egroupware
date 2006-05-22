<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xml:lang="<?=$this->lang?>" xmlns="http://www.w3.org/1999/xhtml">
   <head>
	  <title><?=$this->website_title?></title>
	  <meta http-equiv="content-type" content="text/html; charset=<?=$this->charset?>" />
	  <meta name="keywords" content="eGroupWare" />
	  <meta name="description" content="eGroupware" />
	  <meta name="keywords" content="eGroupWare" />
	  <meta name="copyright" content="pim snel pim@lingewoud.nl eGroupWare http://www.egroupware.org (c) 2005" />
	  <meta name="author" content="pim snel pim@lingewoud.nl eGroupWare http://www.egroupware.org" />
	  <meta name="robots" content="none" />
	  <link rel="icon" href="<?=$this->img_icon?>" type="image/x-ico" />
	  <link rel="shortcut icon" href="<?=$this->img_shortcut?>" />
	  <link href="<?=$this->theme_css?>" type="text/css" rel="StyleSheet" />
	  <script type="text/javascript" src="<?=$GLOBALS['phpgw_info']['server']['webserver_url']?>/phpgwapi/js/tabs/./tabs.js"></script>

	  <script type="text/javascript" >

		 var tab = new Tabs(6,'activetab','inactivetab','tab','tabcontent','','','tabpage');

		 // init all tabs
		 function initAll()
		 {
			   tab.init();
			   if(document.popfrm.currenttab.value)
			   {
					 tab.display(document.popfrm.currenttab.value);
			   }

			   <?php if($_POST[submitted]):?>
			   opener.window.location.href=opener.window.location.href;
			   <?php endif?>
		 } 

		 // store current tab so we can open it directly
		 function setCurrent(tab)
		 {
			   document.popfrm.currenttab.value=tab;
		 }

		 var elbg='#ff9933'; //background color for in the widget

		 var NewRelID=1; // counter for all new created relations
		 var active_rel = new Array(); // current active relation type1 in list
		 var active_el= new Array(); // all the relation values in the editor widget

		 var editor_active= false; // is the editor active yes or no

		 active_el['type1']= new Array();
		 active_el['type2']= new Array();
		 active_el['type3']= new Array();
		 active_el['type4']= new Array();

		 resetActive_El_arr('type1');
		 resetActive_El_arr('type2');
		 resetActive_El_arr('type3');
		 resetActive_El_arr('type4');

		 /**
		 * resetActive_El_arr: (re)set the array with values from the editor widget
		 */
		 function resetActive_El_arr(type)
		 {
			   active_el[type][0]= ''; 
			   active_el[type][1]= new Array();
			   active_el[type][2]= new Array();
			   active_el[type][3]= new Array();
			   active_el[type][4]= new Array();
		 }

		 /**
		 * createNewRel: clear the editor widget
		 */
		 function createNewRel(type)
		 {
			   document.getElementById('categoriesnew'+type.substring(4)).style.display="block";

			   setRelationInactive(active_rel[type]);

			   if(document.getElementById(active_el[type][1][0]))
			   {
					 document.getElementById(active_el[type][1][0]).style.background='';
			   }
			   if(document.getElementById(active_el[type][2][0]))
			   {
					 document.getElementById(active_el[type][2][0]).style.background='';
			   }

			   if(type=='type1') 
			   {
					 document.getElementById('ul'+type+'level3').innerHTML='';
					 document.getElementById('ul'+type+'level4').innerHTML='';
			   }
			   else if(type=='type2')
			   {
					 if(document.getElementById(active_el[type][3][0]))
					 {
						   document.getElementById(active_el[type][3][0]).style.background='';
					 }
					 document.getElementById('ul'+type+'level2').innerHTML='';
					 document.getElementById('ul'+type+'level4').innerHTML='';
					 document.getElementById('ul'+type+'level5').innerHTML='';
			   }
			   else if (type=='type3' || type=='type4')
			   {
					 document.getElementById('ul'+type+'level3').innerHTML='';
					 if(document.getElementById(active_el[type][4][0]))
					 {
						   document.getElementById(active_el[type][4][0]).style.background='';
					 }
			   }

			   resetActive_El_arr(type);
			   editor_active=true;

		 }

		 function saveCurrRelations()
		 {
			   if(editor_active)
			   {
					 if(document.popfrm.currenttab.value==3)
					 {
						   if(!saveRelationType1())
						   {
								 return false;
						   }
					 }
					 if(document.popfrm.currenttab.value==4)
					 {
						   if(!saveRelationType2())
						   {
								 return false;
						   }
					 }
					 if(document.popfrm.currenttab.value==5)
					 {
						   if(!saveRelationType3())
						   {
								 return false;
						   }
					 }
					 if(document.popfrm.currenttab.value==6)
					 {
						   if(!saveRelationType4())
						   {
								 return false;
						   }
					 }
			   }

			   document.popfrm.submit();
		 }

		 /**
		 * saveRelation: store the values from the editor in the list of saved relations
		 */
		 function saveRelationType1()
		 {
			   if( active_el['type1'][1].length == 0 || active_el['type1'][2].length == 0  || active_el['type1'][3].length == 0 || active_el['type1'][4].length == 0)
			   {
					 alert('<?=lang('Please comlete all relation elements.')?>');   
					 return false;
			   }

			   var new_displ_inner='';

			   var lkey=active_el['type1'][1][0];
			   var ftab=active_el['type1'][2][0];
			   var fkey=active_el['type1'][3][0];
			   lkey=lkey.substring(7);
			   ftab=ftab.substring(7);
			   fkey=fkey.substring(7);

			   //if is relation exist (has id) edit existing hiddens
			   if(active_el['type1'][0]!='')
			   {
					 NID=active_el['type1'][0];

					 document.getElementById('a_'+NID).innerHTML= '<?=lang('One-to-Many')?>: '+ lkey +' &gt;&gt; ' + ftab + '.'+ fkey;

					 document.getElementById('REL1XXX_LOCAL_KEY'+NID).value=lkey;
					 document.getElementById('REL1XXX_FOREIGN_TABLE'+NID).value=ftab;
					 document.getElementById('REL1XXX_FOREIGN_KEY'+NID).value=fkey;

					 for(i=0; i< (active_el['type1'][4].length); i++)
					 {
						   var disp=active_el['type1'][4][i];
						   disp=disp.substring(7);

						   new_displ_inner+='<input type="hidden" id="REL1XXX_DISPLAY'+i+NID +'" name="REL1XXX_DISPLAY'+i+active_el['type1'][0]+'" value="'+disp+'" />';
					 }
					 document.getElementById('DIVDISPLAY'+NID).innerHTML=new_displ_inner;
					 document.getElementById('REL1XXX_DISPL_COUNT'+NID).value=active_el['type1'][4].length;

			   }
			   //if relation is new create hiddens
			   else
			   {
					 NID='N'+NewRelID;
					 new_li_inner = ' <li id="li_'+NID+'"> <a href="javascript:void(0);" onclick="deleteRelation(\''+NID+'\',\'type1\')"><img src="<?=$this->delete_img?>" alt="" /></a>	<a class="" id="a_'+NID+'" onclick="changerelation(\''+NID+'\',\'type1\')" href="javascript:void(0);" ><?=lang('One-to-Many')?>: '+lkey+' &gt;&gt; '+ftab+'.'+fkey+'</a> </li> ';

					 new_hidden_inner='';
					 new_hidden_inner+='<div style="display:none" id="div'+NID+'">';
						new_hidden_inner+='<input type="hidden" id="REL1XXX_ID'+ NID +'" name="REL1XXX_ID'+ NID +'" value="'+NID+'" />';
						new_hidden_inner+='<input type="hidden" id="REL1XXX_TYPE'+ NID +'" name="REL1XXX_TYPE'+ NID +'" value="'+1+'" />'; // FIXME type must be in a var also
						new_hidden_inner+='<input type="hidden" id="REL1XXX_LOCAL_KEY'+ NID +'" name="REL1XXX_LOCAL_KEY'+ NID +'" value="'+lkey+'" />';
						new_hidden_inner+='<input type="hidden" id="REL1XXX_FOREIGN_TABLE'+ NID +'" name="REL1XXX_FOREIGN_TABLE'+ NID +'" value="'+ftab+'" />';
						new_hidden_inner+='<input type="hidden" id="REL1XXX_FOREIGN_KEY'+ NID +'" name="REL1XXX_FOREIGN_KEY'+ NID +'" value="'+fkey+'" />';
						new_hidden_inner+='</div>';

					 new_hidden_inner+='<div style="display:none" id="DIVDISPLAY'+NID+'">';
						new_hidden_inner+='</div>';


					 for(i=0; i< (active_el['type1'][4].length); i++)
					 {
						   var disp=active_el['type1'][4][i];
						   disp=disp.substring(7);

						   new_displ_inner+='<input type="hidden" id="REL1XXX_DISPLAY'+i+NID+'" name="REL1XXX_DISPLAY'+i+NID+'" value="'+disp+'" />';
					 }
					 new_hidden_inner+='<input type="hidden" id="REL1XXX_DISPL_COUNT'+NID +'" name="REL1XXX_DISPL_COUNT'+NID +'" value="'+active_el['type1'][4].length+'" />';

					 document.getElementById('newhiddens').innerHTML+=new_hidden_inner;
					 document.getElementById('ul_o2m').innerHTML+=new_li_inner;
					 document.getElementById('DIVDISPLAY'+ NID).innerHTML=new_displ_inner;

					 NewRelID++;
			   }
			   closeRelation('type1');
			   return true;
		 }

		 /**
		 * saveRelation: store the values from the editor in the list of saved relations
		 */
		 function saveRelationType2()
		 {
			   type='type2';
			   if( active_el[type][1].length == 0 || active_el[type][2].length == 0  || active_el[type][3].length == 0 || active_el[type][4].length == 0)
			   {
					 alert('<?=lang('Please comlete all relation elements.')?>');   
					 return false;
			   }

			   //foreign table
			   var ftab=active_el[type][1][0];
			   ftab=ftab.substring(7);

			   //connection table
			   var ctab=active_el[type][3][0];
			   ctab=ctab.substring(7);

			   //local connection field
			   var lconfld=active_el[type][4][0];
			   lconfld=lconfld.substring(7);

			   //foreign connection field
			   var fconfld=active_el[type][5][0];
			   fconfld=fconfld.substring(7);

			   var new_displ_inner='';

			   //if is relation exist (has id)
			   //edit existing hiddens
			   if(active_el[type][0]!='')
			   {
					 NID=active_el[type][0];

					 //document.getElementById('a_'+NID).innerHTML= '<?=lang('Many-to-Many')?>: '+ lkey +' &gt;&gt; ' + ftab + '.'+ fkey;
					 //document.getElementById('a_'+NID).innerHTML= '<?=lang('Many-to-Many')?>: '+ ftab;

					 document.getElementById('REL2XXX_FOREIGN_TABLE'+NID).value=ftab;

					 document.getElementById('REL2XXX_CONNECT_KEY_LOCAL'+NID).value=lconfld;
					 document.getElementById('REL2XXX_CONNECT_KEY_FOREIGN'+NID).value=fconfld;
					 document.getElementById('REL2XXX_CONNECT_TABLE'+NID).value=ctab;

					 for(i=0; i< (active_el[type][2].length); i++)
					 {
						   var disp=active_el[type][2][i];
						   disp=disp.substring(7);

						   new_displ_inner+='<input type="hidden" id="REL2XXX_DISPLAY'+i+NID +'" name="REL2XXX_DISPLAY'+i+NID+'" value="'+disp+'" />';
					 }
					 document.getElementById('DIVDISPLAY'+NID).innerHTML=new_displ_inner;
					 document.getElementById('REL2XXX_DISPL_COUNT'+NID).value=active_el[type][2].length;

			   }
			   //if relation is new
			   //create hiddens
			   else
			   {
					 NID='N'+NewRelID;
					 //new_li_inner = ' <li id="li_'+NID+'"> <a href="javascript:void(0);" onclick="deleteRelation(\''+NID+'\',\''+type+'\')"><img src="<?=$this->delete_img?>" alt="" /></a>	<a class="" id="a_'+NID+'" onclick="changerelation(\''+NID+'\',\'type2\')" href="javascript:void(0);" ><?=lang('Many-to-Many')?>: '+lkey+' &gt;&gt; '+ftab+'.'+fkey+'</a> </li> ';
					 //new_li_inner = ' <li id="li_'+NID+'"> <a href="javascript:void(0);" onclick="deleteRelation(\''+NID+'\',\''+type+'\')"><img src="<?=$this->delete_img?>" alt="" /></a>	<a class="" id="a_'+NID+'" onclick="changerelation(\''+NID+'\',\'type2\')" href="javascript:void(0);" ><?=lang('Many-to-Many')?>: '+ftab+'</a> </li> ';

					 new_hidden_inner='';
					 new_hidden_inner+='<'+'div style="display:none" id="div'+NID+'">';
					 new_hidden_inner+='<input type="hidden" id="REL2XXX_ID'+ NID +'" name="REL2XXX_ID'+ NID +'" value="'+NID+'" />';
					 new_hidden_inner+='<input type="hidden" id="REL2XXX_TYPE'+ NID +'" name="REL2XXX_TYPE'+ NID +'" value="'+2+'" />'; // FIXME type must be in a var also
					 //new_hidden_inner+='<input type="hidden" id="REL2XXX_LOCAL_KEY'+ NID +'" name="REL2XXX_LOCAL_KEY'+ NID +'" value="'+lkey+'" />';
					 //new_hidden_inner+='<input type="hidden" id="REL2XXX_FOREIGN_KEY'+ NID +'" name="REL2XXX_FOREIGN_KEY'+ NID +'" value="'+fkey+'" />';
					 new_hidden_inner+='<input type="hidden" id="REL2XXX_FOREIGN_TABLE'+ NID +'" name="REL2XXX_FOREIGN_TABLE'+ NID +'" value="'+ftab+'" />';
					 new_hidden_inner+='<input type="hidden" id="REL2XXX_CONNECT_KEY_LOCAL'+ NID +'" name="REL2XXX_CONNECT_KEY_LOCAL'+ NID +'" value="'+lconfld+'" />';
					 new_hidden_inner+='<input type="hidden" id="REL2XXX_CONNECT_KEY_FOREIGN'+ NID +'" name="REL2XXX_CONNECT_KEY_FOREIGN'+ NID +'" value="'+fconfld+'" />';
					 new_hidden_inner+='<input type="hidden" id="REL2XXX_CONNECT_TABLE'+ NID +'" name="REL2XXX_CONNECT_TABLE'+ NID +'" value="'+ctab+'" />';
					 new_hidden_inner+='<'+'div>';

					 new_hidden_inner+='<'+'div style="display:none" id="DIVDISPLAY'+NID+'">';
					 new_hidden_inner+='<'+'/div>';

					 for(i=0; i< (active_el[type][2].length); i++)
					 {
						   var disp=active_el[type][2][i];
						   disp=disp.substring(7);

						   new_displ_inner+='<input type="hidden" id="REL2XXX_DISPLAY'+i+NID+'" name="REL2XXX_DISPLAY'+i+NID+'" value="'+disp+'" />';
					 }

					 new_hidden_inner+='<input type="hidden" id="REL2XXX_DISPL_COUNT'+NID +'" name="REL2XXX_DISPL_COUNT'+NID +'" value="'+active_el[type][2].length+'" />';
					 document.getElementById('newhiddens').innerHTML+=new_hidden_inner;
//					 document.getElementById('ul_m2m').innerHTML+=new_li_inner;
					 document.getElementById('DIVDISPLAY'+ NID).innerHTML=new_displ_inner;

					 NewRelID++;
			   }

			   //when relation is saved a new name for the connectiontable will be generated and if not exist will be created in database
			   new_hidden_inner+='<input type="hidden" id="REL2XXX_RESETCONNECTTABLE'+NID +'" name="REL2XXX_RESETCONNECTTABLE'+NID +'" value="1" />';
			   document.getElementById('newhiddens').innerHTML+=new_hidden_inner;

			   closeRelation(type);
		//	   alert(new_displ_inner);
			   return true;
		 }

		 /**
		 * saveRelation: store the values from the editor in the list of saved relations
		 */
		 function saveRelationType3()
		 {
			   type='type3';
			   if( active_el[type][1].length == 0 || active_el[type][2].length == 0  || active_el[type][3].length == 0 || active_el[type][4].length == 0)
			   {
					 alert('<?=lang('Please comlete all relation elements.')?>');   
					 return false;
			   }

			   var lkey=active_el[type][1][0];
			   var ftab=active_el[type][2][0];
			   var fkey=active_el[type][3][0];
			   var objconf=active_el[type][4][0];
			   lkey=lkey.substring(7);
			   ftab=ftab.substring(7);
			   fkey=fkey.substring(7);
			   objconf=objconf.substring(7);


			   //if is relation exist (has id)
			   //edit existing hiddens
			   if(active_el[type][0]!='')
			   {
					 NID=active_el[type][0];

					 document.getElementById('a_'+NID).innerHTML= '<?=lang('One-to-One')?>: '+ lkey +' &gt;&gt; ' + ftab + '.'+ fkey;

					 document.getElementById('REL3XXX_LOCAL_KEY'+NID).value=lkey;
					 document.getElementById('REL3XXX_FOREIGN_TABLE'+NID).value=ftab;
					 document.getElementById('REL3XXX_FOREIGN_KEY'+NID).value=fkey;
					 document.getElementById('REL3XXX_OBJECT_CONF'+NID).value=objconf;
			   }
			   //if relation is new
			   //create hiddens
			   else
			   {
					 NID='N'+NewRelID;
					 new_li_inner = ' <li id="li_'+NID+'"> <a href="javascript:void(0);" onclick="deleteRelation(\''+NID+'\',\''+type+'\')"><img src="<?=$this->delete_img?>" alt="" /></a>	<a class="" id="a_'+NID+'" onclick="changerelation(\''+NID+'\',\'type3\')" href="javascript:void(0);" ><?=lang('One-to-One')?>: '+lkey+' &gt;&gt; '+ftab+'.'+fkey+'</a> </li> ';

					 new_hidden_inner='';
					 new_hidden_inner+='<div style="display:none" id="div'+NID+'">';
						new_hidden_inner+='<input type="hidden" id="REL3XXX_ID'+ NID +'" name="REL3XXX_ID'+ NID +'" value="'+NID+'" />';
						new_hidden_inner+='<input type="hidden" id="REL3XXX_TYPE'+ NID +'" name="REL3XXX_TYPE'+ NID +'" value="'+3+'" />'; // FIXME type must be in a var also
						new_hidden_inner+='<input type="hidden" id="REL3XXX_LOCAL_KEY'+ NID +'" name="REL3XXX_LOCAL_KEY'+ NID +'" value="'+lkey+'" />';
						new_hidden_inner+='<input type="hidden" id="REL3XXX_FOREIGN_TABLE'+ NID +'" name="REL3XXX_FOREIGN_TABLE'+ NID +'" value="'+ftab+'" />';
						new_hidden_inner+='<input type="hidden" id="REL3XXX_FOREIGN_KEY'+ NID +'" name="REL3XXX_FOREIGN_KEY'+ NID +'" value="'+fkey+'" />';
						new_hidden_inner+='<input type="hidden" id="REL3XXX_OBJECT_CONF'+ NID +'" name="REL3XXX_OBJECT_CONF'+ NID +'" value="'+objconf+'" />';
						new_hidden_inner+='</div>';

					 document.getElementById('newhiddens').innerHTML+=new_hidden_inner;
					 document.getElementById('ul_o2o').innerHTML+=new_li_inner;

					 NewRelID++;
			   }

			   closeRelation(type);
			   return true;
		 }


		 /**
		 * saveRelation: store the values from the editor in the list of saved relations
		 */
		 function saveRelationType4()
		 {
			   type='type4';
			   if( active_el[type][1].length == 0 || active_el[type][2].length == 0  || active_el[type][3].length == 0 || active_el[type][4].length == 0)
			   {
					 alert('<?=lang('Please comlete all relation elements.')?>');   
					 return false;
			   }

			   var lkey=active_el[type][1][0];
			   var ftab=active_el[type][2][0];
			   var fkey=active_el[type][3][0];
			   var objconf=active_el[type][4][0];
			   lkey=lkey.substring(7);
			   ftab=ftab.substring(7);
			   fkey=fkey.substring(7);
			   objconf=objconf.substring(7);


			   //if is relation exist (has id)
			   //edit existing hiddens
			   if(active_el[type][0]!='')
			   {
					 NID=active_el[type][0];

					 document.getElementById('a_'+NID).innerHTML= '<?=lang('Many-to-One')?>: '+ lkey +' &gt;&gt; ' + ftab + '.'+ fkey;

					 document.getElementById('REL4XXX_LOCAL_KEY'+NID).value=lkey;
					 document.getElementById('REL4XXX_FOREIGN_TABLE'+NID).value=ftab;
					 document.getElementById('REL4XXX_FOREIGN_KEY'+NID).value=fkey;
					 document.getElementById('REL4XXX_OBJECT_CONF'+NID).value=objconf;
			   }
			   //if relation is new
			   //create hiddens
			   else
			   {
					 NID='N'+NewRelID;
					 new_li_inner = ' <li id="li_'+NID+'"> <a href="javascript:void(0);" onclick="deleteRelation(\''+NID+'\',\''+type+'\')"><img src="<?=$this->delete_img?>" alt="" /></a>	<a class="" id="a_'+NID+'" onclick="changerelation(\''+NID+'\',\'type4\')" href="javascript:void(0);" ><?=lang('Many-to-One')?>: '+lkey+' &gt;&gt; '+ftab+'.'+fkey+'</a> </li> ';

					 new_hidden_inner='';
					 new_hidden_inner+='<div style="display:none" id="div'+NID+'">';
						new_hidden_inner+='<input type="hidden" id="REL4XXX_ID'+ NID +'" name="REL4XXX_ID'+ NID +'" value="'+NID+'" />';
						new_hidden_inner+='<input type="hidden" id="REL4XXX_TYPE'+ NID +'" name="REL4XXX_TYPE'+ NID +'" value="'+4+'" />'; // FIXME type must be in a var also
						new_hidden_inner+='<input type="hidden" id="REL4XXX_LOCAL_KEY'+ NID +'" name="REL4XXX_LOCAL_KEY'+ NID +'" value="'+lkey+'" />';
						new_hidden_inner+='<input type="hidden" id="REL4XXX_FOREIGN_TABLE'+ NID +'" name="REL4XXX_FOREIGN_TABLE'+ NID +'" value="'+ftab+'" />';
						new_hidden_inner+='<input type="hidden" id="REL4XXX_FOREIGN_KEY'+ NID +'" name="REL4XXX_FOREIGN_KEY'+ NID +'" value="'+fkey+'" />';
						new_hidden_inner+='<input type="hidden" id="REL4XXX_OBJECT_CONF'+ NID +'" name="REL4XXX_OBJECT_CONF'+ NID +'" value="'+objconf+'" />';
						new_hidden_inner+='</div>';

					 document.getElementById('newhiddens').innerHTML+=new_hidden_inner;
					 document.getElementById('ul_m2o').innerHTML+=new_li_inner;

					 NewRelID++;
			   }

			   closeRelation(type);
			   return true;
		 }

		 function closeRelation(type)
		 {
			   document.getElementById('categoriesnew'+type.substring(4)).style.display="none";
			   editor_active=false;
		 }

		 /**
		 *  deleteRelation: remove relation from list and remove its hidden values
		 * @param string relid id of element containing the relation to delete 
		 * @param string type so we know which widget we must reset
		 */
		 function deleteRelation(relid,type)
		 {
			   document.getElementById('div'+relid).innerHTML='';
			   document.getElementById('li_'+relid).style.display='none';
			   createNewRel(type);
		 }

		 /**
		 * setActive: make an element in the editor active and also put this element 
		 * value in the active_el array so we can store the values later to hiddens
		 *
		 * @param maxactive does not allow more then one element active so it will replace it
		 */
		 function setActive(elid,type,level,maxactive)
		 {
			   var off=0;

			   if(maxactive==1 && document.getElementById(active_el[type][level]))
			   {
					 document.getElementById(active_el[type][level][0]).style.background='';
					 active_el[type][level]= new Array();
			   }
			   else
			   {
					 new_level_arr = new Array();
					 ii=0;

					 for(i=0; i< (active_el[type][level].length); i++)
					 {

						   if(active_el[type][level][i]!=elid )
						   {
								 new_level_arr[ii]=active_el[type][level][i];
								 ii++;
						   }
						   else
						   {
								 off=1;
						   }
					 }
					 active_el[type][level]=new_level_arr;
			   }

			   if(off==0)
			   {
					 // fixme ?? some time elid does not exist
					 document.getElementById(elid).style.background=elbg;
					 active_el[type][level].push(elid);
			   }
			   else
			   {
					 document.getElementById(elid).style.background='';
			   }
		 }

		 /**
		 * load_fields: load field from table using xmlhttprequest
		 *
		 * todo the obj.id must be an argument and not put here by php
		 */
		 function load_fields(table,type)
		 {
			   if(type=='type1')
			   {
					 func='processReqChangeType1';
					 url = '<?=$this->xmlhttpreq_link_fields?>&table='+table;
					 loadXMLDoc(url,func);
			   }
			   else if(type=='type2')
			   {
					 /*
					 func='processReqChangeType2_F1';
					 url = '<?=$this->xmlhttpreq_link_fields?>&table='+table+'&primary=yes';

					 // branch for native XMLHttpRequest object
					 if (window.XMLHttpRequest) 
					 {
						   req = new XMLHttpRequest();
						   req.onreadystatechange = eval(func);
						   req.open("GET", url, true);
						   req.send(null);
					 } 
					 // branch for IE/Windows ActiveX version
					 else if (window.ActiveXObject) 
					 {
						   req = new ActiveXObject("Microsoft.XMLHTTP");
						   if (req) 
						   {
								 req.onreadystatechange = eval(func);
								 req.open("GET", url, true);
								 req.send();
						   }
					 }
					 */

					 //return true;
					 func='processReqChangeType2_F2';
					 url = '<?=$this->xmlhttpreq_link_fields?>&table='+table;
					 loadXMLDoc(url,func);

					 /*// branch for native XMLHttpRequest object
					 if (window.XMLHttpRequest) 
					 {
						   req2 = new XMLHttpRequest();
						   req2.onreadystatechange = eval(func);
						   req2.open("GET", url, true);
						   req2.send(null);
					 } 
					 // branch for IE/Windows ActiveX version
					 else if (window.ActiveXObject) 
					 {
						   req2 = new ActiveXObject("Microsoft.XMLHTTP");
						   if (req2) 
						   {
								 req2.onreadystatechange = eval(func);
								 req2.open("GET", url, true);
								 req2.send();
						   }
					 }
					 */

			   }
			   else if(type=='type3')
			   {
					 func='processReqChangeType3';
					 url = '<?=$this->xmlhttpreq_link_fields?>&table='+table;
					 loadXMLDoc(url,func);
			   }

			   else if(type=='type4')
			   {
					 func='processReqChangeType4';
					 url = '<?=$this->xmlhttpreq_link_fields?>&table='+table;
					 loadXMLDoc(url,func);
			   }

			   return true;
		 }

		
		 function ajax_t2_load_conntablefields(table)
		 {
			   //alert(table);
			   func='processReq_t2_load_conntablefields';
			   url = '<?=$this->xmlhttpreq_link_fields?>&table='+table;
			   loadXMLDoc(url,func);
		 }


		 /**
		 * loadXMLDoc: do some magic xmlhttpreq stuff
		 * todo make switch for different requests
		 */
		 function loadXMLDoc(url,func)
		 {
			   // branch for native XMLHttpRequest object
			   if (window.XMLHttpRequest) 
			   {
					 req = new XMLHttpRequest();
					 req.onreadystatechange = eval(func);
					 req.open("GET", url, true);
					 req.send(null);
			   } 
			   // branch for IE/Windows ActiveX version
			   else if (window.ActiveXObject) 
			   {
					 req = new ActiveXObject("Microsoft.XMLHTTP");
					 if (req) 
					 {
						   req.onreadystatechange = eval(func);
						   req.open("GET", url, true);
						   req.send();
					 }
			   }
		 }

		 // verwijderen ? 
		 var tmp_field_arr = new Array();


		 /**
		 * processReqChange: do some magic xmlhttpreq stuff and read XML data and change elements
		 * todo rename 
		 */
		 function processReq_t2_load_conntablefields()
		 {
			   var type='type2';
			   active_el[type][4]= new Array();
			   active_el[type][5]= new Array();

			   var newinner='';
			   var newinner2='';
			   // only if req shows "complete"
			   if (req.readyState == 4 && req.status == 200 && req.responseXML.documentElement) 
			   {
					 response = req.responseXML.documentElement;
					 if(response.getElementsByTagName('field').length > 0 ) 
					 {
						   var newinner='';
						   var newinner2='';
						   var data='';
						   for(i=0; i< (response.getElementsByTagName('field').length); i++)
						   {
								 data=response.getElementsByTagName('field')[i].firstChild.data;
								 newinner+='<li><a id="'+type+'l4'+data+'" onclick="setActive(\''+type+'l4'+data+'\',\''+type+'\',4,1)" href="javascript:void(0);" >'+data+'</a></li>';
								 newinner2+='<li><a id="'+type+'l5'+data+'" onclick="setActive(\''+type+'l5'+data+'\',\''+type+'\',5,1)" href="javascript:void(0);" >'+data+'</a></li>';
						   }

						   document.getElementById('ul'+type+'level4').innerHTML=newinner;
						   document.getElementById('ul'+type+'level5').innerHTML=newinner2;
					 }
			   }
		 }




		 /**
		 * processReqChange: do some magic xmlhttpreq stuff and read XML data and change elements
		 * todo rename 
		 */
		 function processReqChangeType1()
		 {
			   var type='type1';
			   active_el[type][3]= new Array();
			   active_el[type][4]= new Array();

			   var newinner='';
			   var newinner2='';
			   // only if req shows "complete"
			   if (req.readyState == 4 && req.status == 200 && req.responseXML.documentElement) 
			   {
					 response = req.responseXML.documentElement;
					 if(response.getElementsByTagName('field').length > 0 ) 
					 {
						   var newinner='';
						   var newinner2='';
						   var data='';
						   for(i=0; i< (response.getElementsByTagName('field').length); i++)
						   {
								 data=response.getElementsByTagName('field')[i].firstChild.data;
								 newinner+='<li><a id="'+type+'l3'+data+'" onclick="setActive(\''+type+'l3'+data+'\',\''+type+'\',3,1)" href="javascript:void(0);" >'+data+'</a></li>';
								 newinner2+='<li><a id="'+type+'l4'+data+'" onclick="setActive(\''+type+'l4'+data+'\',\''+type+'\',4,9)" href="javascript:void(0);" >'+data+'</a></li>';
						   }

						   document.getElementById('ul'+type+'level3').innerHTML=newinner;
						   document.getElementById('ul'+type+'level4').innerHTML=newinner2;
					 }
			   }
		 }

		 /**
		 * processReqChange: do some magic xmlhttpreq stuff and read XML data and change elements
		 * todo rename 
		 */
		 // vul velden in l2
		 function processReqChangeType2_F1()
		 {
			   var type='type2';
			   active_el[type][3]= new Array();

			   var newinner='';
			   // only if req shows "complete"
			   if (req.readyState == 4 && req.status == 200 && req.responseXML.documentElement) 
			   {
					 response = req.responseXML.documentElement;
					 if(response.getElementsByTagName('field').length > 0 ) 
					 {
						   var newinner='';
						   var data='';
						   for(i=0; i< (response.getElementsByTagName('field').length); i++)
						   {
								 data=response.getElementsByTagName('field')[i].firstChild.data;
								 newinner+='<li><a id="'+type+'l3'+data+'" onclick="setActive(\''+type+'l3'+data+'\',\''+type+'\',3,1)" href="javascript:void(0);" >'+data+'</a></li>';
						   }
						   document.getElementById('ul'+type+'level3').innerHTML=newinner;
					 }
			   }
		 }

		 /**
		 * processReqChange: do some magic xmlhttpreq stuff and read XML data and change elements
		 * todo rename 
		 */
		 //vul hidden h1
		 function processReqChangeType2_F2()
		 {
			   var type='type2';
			   active_el[type][4]= new Array();

			   var newinner2='';
			   // only if req shows "complete"
			   if (req.readyState == 4 && req.status == 200 && req.responseXML.documentElement) 
			   {
					 response = req.responseXML.documentElement;
					 if(response.getElementsByTagName('field').length > 0 ) 
					 {
						   var newinner2='';
						   var data='';
						   for(i=0; i< (response.getElementsByTagName('field').length); i++)
						   {
								 data=response.getElementsByTagName('field')[i].firstChild.data;
								 newinner2+='<li><a id="'+type+'l2'+data+'" onclick="setActive(\''+type+'l2'+data+'\',\''+type+'\',2,9)" href="javascript:void(0);" >'+data+'</a></li>';
						   }

						   document.getElementById('ul'+type+'level2').innerHTML=newinner2;
					 }
			   }
		 }

		 /**
		 * processReqChange: do some magic xmlhttpreq stuff and read XML data and change elements
		 * todo rename 
		 */
		 function processReqChangeType3()
		 {
			   var type='type3';
			   active_el[type][3]= new Array();

			   var newinner='';
			   // only if req shows "complete"
			   if (req.readyState == 4 && req.status == 200 && req.responseXML.documentElement) 
			   {
					 response = req.responseXML.documentElement;
					 if(response.getElementsByTagName('field').length > 0 ) 
					 {
						   var newinner='';
						   var data='';
						   for(i=0; i< (response.getElementsByTagName('field').length); i++)
						   {
								 data=response.getElementsByTagName('field')[i].firstChild.data;
								 newinner+='<li><a id="'+type+'l3'+data+'" onclick="setActive(\''+type+'l3'+data+'\',\''+type+'\',3,1)" href="javascript:void(0);" >'+data+'</a></li>';
						   }

						   document.getElementById('ul'+type+'level3').innerHTML=newinner;
					 }
			   }


		 }
		 /**
		 * processReqChange: do some magic xmlhttpreq stuff and read XML data and change elements
		 * todo rename 
		 */
		 function processReqChangeType4()
		 {
			   var type='type4';
			   active_el[type][3]= new Array();

			   var newinner='';
			   // only if req shows "complete"
			   if (req.readyState == 4 && req.status == 200 && req.responseXML.documentElement) 
			   {
					 response = req.responseXML.documentElement;
					 if(response.getElementsByTagName('field').length > 0 ) 
					 {
						   var newinner='';
						   var data='';
						   for(i=0; i< (response.getElementsByTagName('field').length); i++)
						   {
								 data=response.getElementsByTagName('field')[i].firstChild.data;
								 newinner+='<li><a id="'+type+'l3'+data+'" onclick="setActive(\''+type+'l3'+data+'\',\''+type+'\',3,1)" href="javascript:void(0);" >'+data+'</a></li>';
						   }

						   document.getElementById('ul'+type+'level3').innerHTML=newinner;
					 }
			   }
		 }

		 function setRelationInactive(oldid)
		 {
			   if(document.getElementById(oldid))
			   {
					 document.getElementById(oldid).style.background='white';
			   }
		 }

		 function setRelationActive(newid)
		 {
			   document.getElementById(newid).style.background=elbg;
		 }


		 /**
		 * changerelation: open a relation from the list in the editor 
		 */
		 function changerelation(relid,type)
		 {
			   createNewRel(type);

			   setRelationInactive(active_rel[type]);
			   setRelationActive('li_'+relid);

			   active_rel[type]='li_'+relid;

			   if(type=='type1')
			   {
					 active_el[type][0]=document.getElementById('REL1XXX_ID'+relid).value; // for existing relations we fillthe zero element with the relid

					 setActive(type+'l1'+document.getElementById('REL1XXX_LOCAL_KEY'+relid).value,'type1',1,1);
					 setActive(type+'l2'+document.getElementById('REL1XXX_FOREIGN_TABLE'+relid).value,'type1',2,1);

					 if(load_fields(document.getElementById('REL1XXX_FOREIGN_TABLE'+relid).value,'type1'))
					 {
						   window.setTimeout('setActive(\''+type+'l3\'+document.getElementById(\'REL1XXX_FOREIGN_KEY'+relid+'\').value,\'type1\',3,1)', 2000);     

						   for(i=0; i< (document.getElementById('REL1XXX_DISPL_COUNT'+relid).value); i++)
						   {
								 window.setTimeout('setActive(\''+type+'l4\'+document.getElementById(\'REL1XXX_DISPLAY'+i+relid+'\').value,\'type1\',4,9)', 2000);     
						   }
					 }
			   }
			   else if(type=='type2')
			   {
					 active_el[type][0]=document.getElementById('REL2XXX_ID'+relid).value; // for existing relations we fillthe zero element with the relid

					 // 1. set active foreign table
					 setActive(type+'l1'+document.getElementById('REL2XXX_FOREIGN_TABLE'+relid).value,'type2',1,1);
					 
					 // 2.a get foreign display fields
					 load_fields(document.getElementById('REL2XXX_FOREIGN_TABLE'+relid).value,'type2')

					 // 2.b set active foreign display fields 
					 for(i=0; i< (document.getElementById('REL2XXX_DISPL_COUNT'+relid).value); i++)
					 {
						   window.setTimeout('setActive(\''+type+'l2\'+document.getElementById(\'REL2XXX_DISPLAY'+i+relid+'\').value,\'type2\',2,9)', 2000);     
					 }

					 // 3.a get connection tables
					 window.setTimeout('setActive(\''+type+'l3\'+document.getElementById(\'REL2XXX_CONNECT_TABLE'+relid+'\').value,\'type2\',3,1)', 2000);     
					 
					 // 4.a get connection fields
					 window.setTimeout('ajax_t2_load_conntablefields(document.getElementById(\'REL2XXX_CONNECT_TABLE'+relid+'\').value)',2000);

					 // 4.b set local connection field
					 window.setTimeout('setActive(\''+type+'l4\'+document.getElementById(\'REL2XXX_CONNECT_KEY_LOCAL'+relid+'\').value,\'type2\',4,1)',3000);

					 // 4.c set foreign connection field
					 window.setTimeout('setActive(\''+type+'l5\'+document.getElementById(\'REL2XXX_CONNECT_KEY_FOREIGN'+relid+'\').value,\'type2\',5,1)',3000);
			   }
			   else if(type=='type3')
			   {
					 active_el[type][0]=document.getElementById('REL3XXX_ID'+relid).value; // for existing relations we fillthe zero element with the relid

					 setActive(type+'l1'+document.getElementById('REL3XXX_LOCAL_KEY'+relid).value,'type3',1,1);
					 setActive(type+'l2'+document.getElementById('REL3XXX_FOREIGN_TABLE'+relid).value,'type3',2,1);
					 setActive(type+'l4'+document.getElementById('REL3XXX_OBJECT_CONF'+relid).value,'type3',4,1);

					 load_fields(document.getElementById('REL3XXX_FOREIGN_TABLE'+relid).value,'type3')

					 window.setTimeout('setActive(\''+type+'l3\'+document.getElementById(\'REL3XXX_FOREIGN_KEY'+relid+'\').value,\'type3\',3,1)', 2000);     
			   }
			   else if(type=='type4')
			   {
					 active_el[type][0]=document.getElementById('REL4XXX_ID'+relid).value; // for existing relations we fillthe zero element with the relid

					 setActive(type+'l1'+document.getElementById('REL4XXX_LOCAL_KEY'+relid).value,'type4',1,1);
					 setActive(type+'l2'+document.getElementById('REL4XXX_FOREIGN_TABLE'+relid).value,'type4',2,1);
					 setActive(type+'l4'+document.getElementById('REL4XXX_OBJECT_CONF'+relid).value,'type4',4,1);

					 load_fields(document.getElementById('REL4XXX_FOREIGN_TABLE'+relid).value,'type4')

					 window.setTimeout('setActive(\''+type+'l3\'+document.getElementById(\'REL4XXX_FOREIGN_KEY'+relid+'\').value,\'type4\',3,1)', 2000);     
			   }
		 }

	  </script>

	  <style type="text/css">
		 body 
		 {
			   background:none;
			   color: #333;
			   margin:10px;
			   font-family: sans-serif;
		 }

		 a:link, a:visited 
		 {
			   text-decoration:none;
			   color: #FF4000;
		 }

		 a:hover 
		 {
			   color:#002c99;
		 }

		 h1
		 {
			   margin:0 0 10px 0;	   
		 }

		 h2
		 {
			   margin:5px 0px 5px 0px; 
		 }

		 /* browser widget */
		 .defrelation
		 {
			   display: none;
		 }
		 .relationeditbuttons
		 {
			   bottom:5px;	
			   position: absolute;	
		 }

		 div#categoriesnew1,
		 div#categoriesnew2,
		 div#categoriesnew3,
		 div#categoriesnew4
		 {
			   text-align: left; 
			   position: relative; 
			   height: 283px; 
			   margin-bottom: 0px; 
			   padding:10px;
			   /*		   display: block;*/
			   background-color:#cccccc;
		 }

		 .levels
		 {
			   background-color: transparent; 
			   border:solid 0px blue;
			   margin: 0;	
			   padding: 0;	
			   position: absolute;	
			   top: 0;	
			   height: 200px;
		 }

		 #type1level-1,
		 #type2level-1,
		 #type3level-1,
		 #type4level-1
		 {
			   left: 10px;
			   top:5px;
		 }
		 #type1level-2,
		 #type2level-2,
		 #type3level-2,
		 #type4level-2
		 {
			   left: 245px;
			   top:5px;
		 }
		 #type1level-3,
		 #type2level-3,
		 #type3level-3,
		 #type4level-3
		 {
			   left: 488px;
			   top:5px;
		 }

		 #type1level-4,
		 #type3level-4,
		 #type4level-4
		 {
			   left: 723px;
			   top:5px;
		 }


		 #categoriesnew1 ul,
		 #categoriesnew2 ul,
		 #categoriesnew3 ul,
		 #categoriesnew4 ul
		 {
			   border: 1px solid #C5C5C5; 
			   width: 200px; 
			   height: 200px; 
			   overflow: auto; 
			   margin: 0; 
			   padding: 3px;
			   background-color: #fff
		 }

		 #categoriesnew1 li,
		 #categoriesnew2 li,
		 #categoriesnew3 li,
		 #categoriesnew4 li 
		 {
			   margin: 0px; 
			   width: 200px; 
			   padding:0px;
			   line-height:150%;
		 }

		 .levels ul a
		 {
			   display: block; 
			   padding: 0px 0px 0px 0px; 
			   margin: 0; 
			   text-decoration: none; 
			   width: 200px; 
			   color: #000;
		 }

		 .levels ul a:hover
		 {
			   color: #006699;
		 }

		 .levels h3
		 {
			   font-weight: normal; 
			   margin: 0 0 0px 0; 
			   padding: 0 0 5px 0; 
			   display: block; 
			   font-size: 16px;
			   font-weight:bold;
		 }

		 .levels h4
		 {
			   font-weight: normal; 
			   margin: 0 0 0px 0; 
			   padding: 0 0 5px 0; 
			   display: block; 
			   font-size: 12px;
			   height:30px;
			   vertical-align:bottom;
		 }

		 /* rel uls */

		 #div_m2m,
		 #div_m2o,
		 #div_o2o,
		 #div_o2m
		 {
			   margin:0px 0px 5px 0px;
		 }

		 #ul_o2m, 
		 #ul_m2o, 
		 #ul_o2o, 
		 #ul_m2m
		 {
			   overflow:auto;
			   background-color:#fff;
			   border: 1px solid #C5C5C5; 
			   margin: 0; 
			   padding:0px;
			   height:60px;
			   width:530px;
		 }

		 #ul_o2m li,
		 #ul_m2o li,
		 #ul_o2o li,
		 #ul_m2m li
		 {
			   margin: 0px; 
			   vertical-align:center;
			   padding: 1px;
			   width:500px;
			   list-style:none;
		 }

		 #ul_o2m a,
		 #ul_m2o a,
		 #ul_o2o a,
		 #ul_m2m a
		 {
			   color:black;
		 }

		 /* tabs */
		 div.activetab
		 { 
			   display:block; 
			   background-color:#EEEEEE;
			   padding:10px;

		 }
		 div.inactivetab
		 { 
			   display:none; 
		 }


		 #topnav {
			   margin:0;
			   padding: 0 0 0 12px;
		 }

		 #topnav ul 
		 {
			   list-style: none;
			   margin: 0;
			   padding: 0;
			   border: none;
		 } 

		 #topnav li,
		 li.inactivetab
		 {
			   display: block;
			   margin: 0;
			   padding: 0;
			   float:left;
			   width:auto;
		 }

		 #topnav A 
		 {
			   color:#444;
			   display:block;
			   width:auto;
			   text-decoration:none;
			   background: #BBBBBB;
			   margin:0;
			   padding: 2px 10px;
			   border-left: 1px solid #fff;
			   border-top: 1px solid #fff;
			   border-right: 1px solid #aaa;
		 }

		 #topnav A:hover, 
		 #topnav A:active 
		 {
			   background: #EEEEEE;
		 }

		 #topnav A.activetab:visited,#topnav A.activetab:link,#topnav A.here:link, #topnav A.here:visited {
			   position:relative;
			   z-index:102;
			   background: #EEEEEE;
			   font-weight:bold;
		 }

		 #subnav
		 {
			   position:relative;
			   top:-1px;
			   z-index:101;
			   margin:0;
			   background: #EEEEEE;
			   border-top:1px solid #fff;
			   border-bottom:1px solid #aaa;
		 }

		 #subnav br, #topnav br 
		 {
			   clear:both;
		 } 

		 td
		 {
			   vertical-align:top;
		 }

		 #type2level-4
		 {
			   left: 723px;
			   top:5px;
			   height: 80px;

		 }
		 #type2level-4 ul
		 {
			   border: 1px solid #C5C5C5; 
			   width: 200px; 
			   height: 80px; 
			   overflow: auto; 
			   margin: 0; 
			   padding: 3px;
			   background-color: #fff
		 }

		 #type2level-5
		 {
			   left: 723px;
			   height: 80px;
			   top:  150px;
		 }

		 #type2level-5 ul
		 {
			   border: 1px solid #C5C5C5; 
			   width: 200px; 
			   height: 80px; 
			   overflow: auto; 
			   margin: 0; 
			   padding: 3px;
			   background-color: #fff
		 }


		 

	  </style>
   </head>

   <body onload="initAll()">

	  <?php

		 /* if configuration is already set use these values */
		 if($_POST[submitted])
		 {
			//
		 }
	  ?>
	  <form name="popfrm" action="<?=$this->action?>" method="post" enctype="multipart/form-data">

		 <input type="hidden" name="submitted" value="true">
		 <input type="hidden" name="currenttab" id="currenttab" value="<?=($_POST[currenttab]?$_POST[currenttab]:3)?>">
		 <input type="hidden" name="object_id" value="<?=$_GET[object_id]?>">

		 <div id="titel">
			<h1><?=lang('Relation Widgets of %1',$this->object_name)?></h1>
		 </div>

		 <div id="topnav">
			<ul>
			   <li><a href="#" id="tab3" class="activetab" tabindex="0" accesskey="3" onfocus="tab.display(3);" onclick="tab.display(3); setCurrent(3); return(false);"><?=lang('Add one-to-many relation(s)')?></a></li>
			   <li><a href="#" id="tab4" class="activetab" tabindex="0" accesskey="4" onfocus="tab.display(4);" onclick="tab.display(4); setCurrent(4); return(false);"><?=lang('Add many-to-many relation(s)')?></a></li>
			   <li><a href="#" id="tab5" class="activetab" tabindex="0" accesskey="5" onfocus="tab.display(5);" onclick="tab.display(5); setCurrent(5); return(false);"><?=lang('Add one-to-one relation(s)')?></a></li>
			   <li><a href="#" id="tab6" class="activetab" tabindex="0" accesskey="6" onfocus="tab.display(6);" onclick="tab.display(6); setCurrent(6); return(false);"><?=lang('Add many-to-one relation(s)')?></a></li>
			</ul>
			<br />
		 </div>

		 <div id="subnav">

			<!-- old relation method -->
			<div id="tabcontent1" class="inactivetab" >
			</div>
			<!-- endtab-->

			<div id="tabcontent2" class="inactivetab">
			</div>


			<div id="tabcontent3" class="inactivetab">
			   <h2><?=lang('One-to-many relations')?></h2>

			   <div id="div_o2m">
				  <ul id="ul_o2m">
					 <?php foreach($this->type1_arr as $o2m_rel):?>
					 <li id="li_<?=$o2m_rel[id]?>" >
					 <a href="javascript:void(0);" onclick="deleteRelation('<?=$o2m_rel[id]?>','type1')"><img src="<?=$this->delete_img?>" alt="" /></a>								 <a class="" id="a_<?=$o2m_rel[id]?>" onclick="changerelation('<?=$o2m_rel[id]?>','type1')" href="javascript:void(0);" ><?=lang('One-to-Many')?>: <?=$o2m_rel[local_key]?> &gt;&gt; <?=$o2m_rel[foreign_table]?>.<?=$o2m_rel[foreign_key]?></a> 
					 </li> 
					 <?php endforeach?>
				  </ul>
			   </div>
			   <input type="button"  style="margin:0px 0px 5px 0px"  onclick="createNewRel('type1');" value="<?=lang('Create new relation');?>" />

			   <div id="categoriesnew1" class="defrelation">
				  <div id="type1level-1" class="levels">
					 <h3><?=lang('Local Fields')?></h3>
					 <h4><?=lang('Select local key')?></h4>
					 <ul>
						<?php foreach($this->fields_arr as $lfield):?>
						<li><a class="" id="type1l1<?=$lfield?>" onclick="setActive('type1l1<?=$lfield?>','type1',1,1)" href="javascript:void(0);" ><?=$lfield?></a></li> 
						<?php endforeach?>
					 </ul>
				  </div>
				  <div id="type1level-2" class="levels">
					 <h3><?=lang('Foreigh Tables')?></h3>
					 <h4><?=lang('Select foreign table')?></h4>
					 <ul>
						<?php foreach($this->avail_table_arr as $ftable):?>
						<li><a class="" id="type1l2<?=$ftable?>" onclick="setActive('type1l2<?=$ftable?>','type1',2,1);load_fields('<?=$ftable?>','type1');" href="javascript:void(0);" ><?=$ftable?></a></li> 
						<?php endforeach?>
					 </ul>

				  </div>
				  <div id="type1level-3" class="levels">
					 <h3><?=lang('Foreign Fields')?></h3>
					 <h4><?=lang('Select foreign key')?></h4>
					 <ul id="ultype1level3">
					 </ul>
				  </div>
				  <div id="type1level-4" class="levels">
					 <h3><?=lang('Display Fields')?></h3>
					 <h4><?=lang('Select fields to display from foreign table')?></h4>
					 <ul id="ultype1level4">
					 </ul>
				  </div>
				  <div class="relationeditbuttons">
					 <!--					 <input type="button"  style=""  onclick="saveRelationType1();" value="<?=lang('Save Relation');?>" />-->
					 <input type="button"  style=""  onclick="closeRelation('type1');" value="<?=lang('Cancel');?>" />
				  </div>	
			   </div>
			</div>

		   <!--	VEELOPVEEL-->
			<div id="tabcontent4" class="inactivetab">
			   <h2><?=lang('Many-to-many relations')?></h2>

			   <div id="div_m2m">
				  <ul id="ul_m2m">
					 <?php foreach($this->type2_arr as $m2m_rel):?>

					 <li id="li_<?=$m2m_rel[id]?>" >
					 <a href="javascript:void(0);" onclick="deleteRelation('<?=$m2m_rel[id]?>','type2')"><img src="<?=$this->delete_img?>" alt="" /></a>
					 <a class="" id="a_<?=$m2m_rel[id]?>" onclick="changerelation('<?=$m2m_rel[id]?>','type2')" href="javascript:void(0);" ><strong><?=lang('Many-to-Many')?>:</strong> <?=$this->table_name?> &lt;&lt;-- &gt;&gt; <?=$m2m_rel[foreign_table]?></a> 
					 </li> 
					 <?php endforeach?>
				  </ul>
			   </div>
			   <input type="button" class="egwbutton" style="margin:0px 0px 5px 0px" onclick="createNewRel('type2');" value="<?=lang('Create new relation');?>" />

			   <div id="categoriesnew2" class="defrelation">

				  <div id="type2level-1" class="levels">
					 <h3><?=lang('Foreigh Table')?></h3>
					 <h4><?=lang('Select foreign table')?></h4>
					 <ul>
						<?php foreach($this->avail_table_arr as $ftable):?>
						<li><a class="" id="type2l1<?=$ftable?>" onclick="setActive('type2l1<?=$ftable?>','type2',1,1);load_fields('<?=$ftable?>','type2');" href="javascript:void(0);" ><?=$ftable?></a></li> 
						<?php endforeach?>
					 </ul>
				  </div>

				  <div id="type2level-2" class="levels">
					 <h3><?=lang('Display Foreign Fields')?></h3>
					 <h4><?=lang('Select fields to display')?></h4>
					 <ul id="ultype2level2">
					 </ul>
				  </div>

				  <!--
				  //set local primary in hidden
				  -->

				  <div id="type2level-3" class="levels">
					 <h3><?=lang('Connection Table')?></h3>
					 <h4><?=lang('Select connection table')?></h4>
				  <ul>
					 <?php foreach($this->avail_table_arr as $ftable):?>
					 <li><a class="" id="type2l3<?=$ftable?>" onclick="setActive('type2l3<?=$ftable?>','type2',3,1);ajax_t2_load_conntablefields('<?=$ftable?>');" href="javascript:void(0);" ><?=$ftable?></a></li> 
					 <?php endforeach?>

					 <!--	 <li><a class="" id="type2l3<?=$ftable?>" onclick="setActive('type2l3<?=$ftable?>','type2',3,1);ajax_t2_load_conntablefields('[new]');" href="javascript:void(0);" >[<?=lang('create new');?>]</a></li>-->
				  </ul>
			   </div>
			   
			   <ul id="ultype2level3" style="display:none;"> 
			   </ul>

				  <div id="type2level-4" class="levels">
					 <h3><?=lang('Connection Fields')?></h3>
					 <h4><?=lang('Select local connection key')?></h4>
					 <ul id="ultype2level4">
						<?php foreach($this->primary_arr as $lfield):?>
						<!--<li><a class="" id="type2l1<?=$lfield?>" onclick="setActive('type2l1<?=$lfield?>','type2',1,1)" href="javascript:void(0);" ><?=$lfield?></a></li>-->
						<?php endforeach?>
					 </ul>
				  </div>
				  
				  <div id="type2level-5" class="levels">
					 <h4><?=lang('Select foreign connection key')?></h4>
					 <ul id="ultype2level5">
						<?php foreach($this->primary_arr as $lfield):?>
						<!--						<li><a class="" id="type2l1<?=$lfield?>" onclick="setActive('type2l1<?=$lfield?>','type2',1,1)" href="javascript:void(0);" ><?=$lfield?></a></li> -->
						<?php endforeach?>
					 </ul>

				  </div>
			
				  <!--<div id="type2level-3" class="levels">
					 <h3><?=lang('Foreign Fields')?></h3>
					 <h4><?=lang('Select foreign key')?></h4>
					 <ul id="ultype2level3">
					 </ul>
				  </div>
				  -->

				  <div class="relationeditbuttons">
					 <input type="button"  style=""  onclick="closeRelation('type2');" value="<?=lang('Cancel');?>" />
				  </div>	
			   </div>
			</div>


			<!-- ONE TO ONE TAB -->
			<div id="tabcontent5" class="inactivetab">
			   <h2><?=lang('One-to-one relations')?></h2>

			   <div id="div_o2o">
				  <ul id="ul_o2o">
					 <?php foreach($this->type3_arr as $o2o_rel):?>

					 <li id="li_<?=$o2o_rel[id]?>" >
					 <a href="javascript:void(0);" onclick="deleteRelation('<?=$o2o_rel[id]?>','type3')"><img src="<?=$this->delete_img?>" alt="" /></a>
					 <a class="" id="a_<?=$o2o_rel[id]?>" onclick="changerelation('<?=$o2o_rel[id]?>','type3')" href="javascript:void(0);" ><?=lang('One-to-One')?>: <?=$o2o_rel[local_key]?> &gt;&gt; <?=$o2o_rel[foreign_table]?>.<?=$o2o_rel[foreign_key]?></a> 
					 </li> 
					 <?php endforeach?>
				  </ul>
			   </div>
			   <input type="button" class="egwbutton" style="margin:0px 0px 5px 0px"  onclick="createNewRel('type3');" value="<?=lang('Create new relation');?>" />

			   <div id="categoriesnew3" class="defrelation">
				  <div id="type3level-1" class="levels">
					 <h3><?=lang('Local Fields')?></h3>
					 <h4><?=lang('Select local key')?></h4>
					 <ul>
						<?php foreach($this->fields_arr as $lfield):?>

						<li><a class="" id="type3l1<?=$lfield?>" onclick="setActive('type3l1<?=$lfield?>','type3',1,1)" href="javascript:void(0);" ><?=$lfield?></a></li> 
						<?php endforeach?>
					 </ul>
				  </div>
				  <div id="type3level-2" class="levels">
					 <h3><?=lang('Foreigh Tables')?></h3>
					 <h4><?=lang('Select foreign table')?></h4>
					 <ul>
						<?php foreach($this->avail_table_arr as $ftable):?>
						<li><a class="" id="type3l2<?=$ftable?>" onclick="setActive('type3l2<?=$ftable?>','type3',2,1);load_fields('<?=$ftable?>','type3');" href="javascript:void(0);" ><?=$ftable?></a></li> 
						<?php endforeach?>
					 </ul>

				  </div>
				  <div id="type3level-3" class="levels">
					 <h3><?=lang('Foreign Fields')?></h3>
					 <h4><?=lang('Select foreign key')?></h4>
					 <ul id="ultype3level3">
					 </ul>
				  </div>
				  <div id="type3level-4" class="levels">
					 <h3><?=lang('Object Configuration')?></h3>
					 <h4><?=lang('Select object configuration to use')?></h4>
					 <ul id="ultype3level4">
						<?php foreach($this->avail_objects_arr as $obj):?>
						<li><a class="" id="type3l4<?=$obj[unique_id]?>" onclick="setActive('type3l4<?=$obj[unique_id]?>','type3',4,1);" href="javascript:void(0);" ><?=$obj[name]?></a></li> 
						<?php endforeach?>
					 </ul>
				  </div>
				  <div class="relationeditbuttons">
					 <!--					 <input type="button"  style=""  onclick="saveRelationType3();" value="<?=lang('Save Relation');?>" />-->
					 <input type="button"  style=""  onclick="closeRelation('type3');" value="<?=lang('Cancel');?>" />
				  </div>	



			   </div>
			</div>



			<!-- MANY TO ONE TAB -->
			<div id="tabcontent6" class="inactivetab">
			   <h2><?=lang('Many-to-one relations')?></h2>

			   <div id="div_m2o">
				  <ul id="ul_m2o">
					 <?php foreach($this->type4_arr as $m2o_rel):?>

					 <li id="li_<?=$m2o_rel[id]?>" >
					 <a href="javascript:void(0);" onclick="deleteRelation('<?=$m2o_rel[id]?>','type4')"><img src="<?=$this->delete_img?>" alt="" /></a>
					 <a class="" id="a_<?=$m2o_rel[id]?>" onclick="changerelation('<?=$m2o_rel[id]?>','type4')" href="javascript:void(0);" ><?=lang('Many-to-One')?>: <?=$m2o_rel[local_key]?> &gt;&gt; <?=$m2o_rel[foreign_table]?>.<?=$m2o_rel[foreign_key]?></a> 
					 </li> 
					 <?php endforeach?>
				  </ul>
			   </div>
			   <input type="button" class="egwbutton" style="margin:0px 0px 5px 0px"  onclick="createNewRel('type4');" value="<?=lang('Create new relation');?>" />

			   <div id="categoriesnew4" class="defrelation">
				  <div id="type4level-1" class="levels">
					 <h3><?=lang('Local Fields')?></h3>
					 <h4><?=lang('Select local key')?></h4>
					 <ul>
						<?php foreach($this->fields_arr as $lfield):?>

						<li><a class="" id="type4l1<?=$lfield?>" onclick="setActive('type4l1<?=$lfield?>','type4',1,1)" href="javascript:void(0);" ><?=$lfield?></a></li> 
						<?php endforeach?>
					 </ul>
				  </div>
				  <div id="type4level-2" class="levels">
					 <h3><?=lang('Foreigh Tables')?></h3>
					 <h4><?=lang('Select foreign table')?></h4>
					 <ul>
						<?php foreach($this->avail_table_arr as $ftable):?>
						<li><a class="" id="type4l2<?=$ftable?>" onclick="setActive('type4l2<?=$ftable?>','type4',2,1);load_fields('<?=$ftable?>','type4');" href="javascript:void(0);" ><?=$ftable?></a></li> 
						<?php endforeach?>
					 </ul>

				  </div>
				  <div id="type4level-3" class="levels">
					 <h3><?=lang('Foreign Fields')?></h3>
					 <h4><?=lang('Select foreign key')?></h4>
					 <ul id="ultype4level3">
					 </ul>
				  </div>
				  <div id="type4level-4" class="levels">
					 <h3><?=lang('Object Configuration')?></h3>
					 <h4><?=lang('Select object configuration to use')?></h4>
					 <ul id="ultype4level4">
						<?php foreach($this->avail_objects_arr as $obj):?>
						<li><a class="" id="type4l4<?=$obj[unique_id]?>" onclick="setActive('type4l4<?=$obj[unique_id]?>','type4',4,1);" href="javascript:void(0);" ><?=$obj[name]?></a></li> 
						<?php endforeach?>
					 </ul>
				  </div>

				  <div class="relationeditbuttons">
					 <!--					 <input type="button"  style=""  onclick="saveRelationType4();" value="<?=lang('Save Relation');?>" />-->
					 <input type="button"  style=""  onclick="closeRelation('type4');" value="<?=lang('Cancel');?>" />
				  </div>	

			   </div>



			</div>

		 </div>

		 <br/>
		 <div align="center">
			<input class="egwbutton"  onClick="saveCurrRelations()" type="button" value="<?=lang('save')?>"  />
			<input class="egwbutton"  type="button" value="<?=lang('close')?>" onClick="self.close()" />
		 </div>


		 <!-- ALL HIDDEN DATA WE NEED -->
		 <input type="hidden" name="LOCAL_TABLE" id="LOCAL_TABLE" value="<?=$this->table_name?>" />

		 <!-- Type 1 -->
		 <?php foreach($this->type1_arr as $o2m_rel):?>
		 <div style="display:none" id="div<?=$o2m_rel[id]?>">
			<input type="hidden" name="REL1XXX_ID<?=$o2m_rel[id]?>" id="REL1XXX_ID<?=$o2m_rel[id]?>" value="<?=$o2m_rel[id]?>" />
			<input type="hidden" name="REL1XXX_TYPE<?=$o2m_rel[id]?>" id="REL1XXX_TYPE<?=$o2m_rel[id]?>" value="<?=$o2m_rel[type]?>" />
			<input type="hidden" name="REL1XXX_LOCAL_KEY<?=$o2m_rel[id]?>" id="REL1XXX_LOCAL_KEY<?=$o2m_rel[id]?>" value="<?=$o2m_rel[local_key]?>" />
			<input type="hidden" name="REL1XXX_FOREIGN_TABLE<?=$o2m_rel[id]?>" id="REL1XXX_FOREIGN_TABLE<?=$o2m_rel[id]?>" value="<?=$o2m_rel[foreign_table]?>" />
			<input type="hidden" name="REL1XXX_FOREIGN_KEY<?=$o2m_rel[id]?>" id="REL1XXX_FOREIGN_KEY<?=$o2m_rel[id]?>" value="<?=$o2m_rel[foreign_key]?>" />
			<?php 
			   $i=0;
			   $fshowfields = unserialize($o2m_rel[foreign_showfields]);
			?>

			<div id="DIVDISPLAY<?=$o2m_rel[id]?>" style="display:none">
			   <?php foreach($fshowfields as $sf):?>
			   <input type="hidden" name="REL1XXX_DISPLAY<?=$i.$o2m_rel[id]?>" id="REL1XXX_DISPLAY<?=$i.$o2m_rel[id]?>" value="<?=$sf?>" />
			   <?php $i++?>
			   <?php endforeach?>
			</div>
			<input type="hidden" name="REL1XXX_DISPL_COUNT<?=$o2m_rel[id]?>" id="REL1XXX_DISPL_COUNT<?=$o2m_rel[id]?>" value="<?=count($fshowfields)?>" />

		 </div>
		 <?php endforeach?>

		 <!-- Type 2 -->
		 <?php foreach($this->type2_arr as $m2m_rel):?>
		 <?php 
			//workaround
			if(!$m2m_rel[connect_table])
			{
			   $tmp_arr=explode($m2m_rel[connect_key_local]); 
			   $m2m_rel[connect_table]=$tmp_arr[0];
			}
		 ?>
		 <div style="display:none" id="div<?=$m2m_rel[id]?>">
			<input type="hidden" name="REL2XXX_ID<?=$m2m_rel[id]?>" id="REL2XXX_ID<?=$m2m_rel[id]?>" value="<?=$m2m_rel[id]?>" />
			<input type="hidden" name="REL2XXX_TYPE<?=$m2m_rel[id]?>" id="REL2XXX_TYPE<?=$m2m_rel[id]?>" value="<?=$m2m_rel[type]?>" />
			<input type="hidden" name="REL2XXX_LOCAL_KEY<?=$m2m_rel[id]?>" id="REL2XXX_LOCAL_KEY<?=$m2m_rel[id]?>" value="<?=$m2m_rel[local_key]?>" />
			<input type="hidden" name="REL2XXX_FOREIGN_TABLE<?=$m2m_rel[id]?>" id="REL2XXX_FOREIGN_TABLE<?=$m2m_rel[id]?>" value="<?=$m2m_rel[foreign_table]?>" />
			<input type="hidden" name="REL2XXX_FOREIGN_KEY<?=$m2m_rel[id]?>" id="REL2XXX_FOREIGN_KEY<?=$m2m_rel[id]?>" value="<?=$m2m_rel[foreign_key]?>" />
			<input type="hidden" name="REL2XXX_CONNECT_KEY_LOCAL<?=$m2m_rel[id]?>" id="REL2XXX_CONNECT_KEY_LOCAL<?=$m2m_rel[id]?>" value="<?=$m2m_rel[connect_key_local]?>" />
			<input type="hidden" name="REL2XXX_CONNECT_KEY_FOREIGN<?=$m2m_rel[id]?>" id="REL2XXX_CONNECT_KEY_FOREIGN<?=$m2m_rel[id]?>" value="<?=$m2m_rel[connect_key_foreign]?>" />
			<input type="hidden" name="REL2XXX_CONNECT_TABLE<?=$m2m_rel[id]?>" id="REL2XXX_CONNECT_TABLE<?=$m2m_rel[id]?>" value="<?=$m2m_rel[connect_table]?>" />

			<?php 
			   $i=0;
			   $fshowfields = unserialize($m2m_rel[foreign_showfields]);
			?>

			<div id="DIVDISPLAY<?=$m2m_rel[id]?>" style="display:none">
			   <?php foreach($fshowfields as $sf):?>
			   <input type="hidden" name="REL2XXX_DISPLAY<?=$i.$m2m_rel[id]?>" id="REL2XXX_DISPLAY<?=$i.$m2m_rel[id]?>" value="<?=$sf?>" />
			   <?php $i++?>
			   <?php endforeach?>
			</div>
			<input type="hidden" name="REL2XXX_DISPL_COUNT<?=$m2m_rel[id]?>" id="REL2XXX_DISPL_COUNT<?=$m2m_rel[id]?>" value="<?=count($fshowfields)?>" />

		 </div>
		 <?php endforeach?>

		 <!-- Type 3 -->
		 <?php foreach($this->type3_arr as $o2o_rel):?>
		 <div style="display:none" id="div<?=$o2o_rel[id]?>">
			<input type="hidden" name="REL3XXX_ID<?=$o2o_rel[id]?>" id="REL3XXX_ID<?=$o2o_rel[id]?>" value="<?=$o2o_rel[id]?>" />
			<input type="hidden" name="REL3XXX_TYPE<?=$o2o_rel[id]?>" id="REL3XXX_TYPE<?=$o2o_rel[id]?>" value="<?=$o2o_rel[type]?>" />
			<input type="hidden" name="REL3XXX_LOCAL_KEY<?=$o2o_rel[id]?>" id="REL3XXX_LOCAL_KEY<?=$o2o_rel[id]?>" value="<?=$o2o_rel[local_key]?>" />
			<input type="hidden" name="REL3XXX_FOREIGN_TABLE<?=$o2o_rel[id]?>" id="REL3XXX_FOREIGN_TABLE<?=$o2o_rel[id]?>" value="<?=$o2o_rel[foreign_table]?>" />
			<input type="hidden" name="REL3XXX_FOREIGN_KEY<?=$o2o_rel[id]?>" id="REL3XXX_FOREIGN_KEY<?=$o2o_rel[id]?>" value="<?=$o2o_rel[foreign_key]?>" />
			<input type="hidden" name="REL3XXX_OBJECT_CONF<?=$o2o_rel[id]?>" id="REL3XXX_OBJECT_CONF<?=$o2o_rel[id]?>" value="<?=$o2o_rel[object_conf]?>" />

		 </div>
		 <?php endforeach?>

		 <!-- Type 4 -->
		 <?php foreach($this->type4_arr as $m2o_rel):?>
		 <div style="display:none" id="div<?=$m2o_rel[id]?>">
			<input type="hidden" name="REL4XXX_ID<?=$m2o_rel[id]?>" id="REL4XXX_ID<?=$m2o_rel[id]?>" value="<?=$m2o_rel[id]?>" />
			<input type="hidden" name="REL4XXX_TYPE<?=$m2o_rel[id]?>" id="REL4XXX_TYPE<?=$m2o_rel[id]?>" value="<?=$m2o_rel[type]?>" />
			<input type="hidden" name="REL4XXX_LOCAL_KEY<?=$m2o_rel[id]?>" id="REL4XXX_LOCAL_KEY<?=$m2o_rel[id]?>" value="<?=$m2o_rel[local_key]?>" />
			<input type="hidden" name="REL4XXX_FOREIGN_TABLE<?=$m2o_rel[id]?>" id="REL4XXX_FOREIGN_TABLE<?=$m2o_rel[id]?>" value="<?=$m2o_rel[foreign_table]?>" />
			<input type="hidden" name="REL4XXX_FOREIGN_KEY<?=$m2o_rel[id]?>" id="REL4XXX_FOREIGN_KEY<?=$m2o_rel[id]?>" value="<?=$m2o_rel[foreign_key]?>" />
			<input type="hidden" name="REL4XXX_OBJECT_CONF<?=$m2o_rel[id]?>" id="REL4XXX_OBJECT_CONF<?=$m2o_rel[id]?>" value="<?=$m2o_rel[object_conf]?>" />
		 </div>
		 <?php endforeach?>

		 <div style="display:none" id="newhiddens"></div>
		 <!-- END ALL HIDDEN DATA WE NEED -->

	  </form>
	  <div id="debug"></div>
   </body>
</html>
