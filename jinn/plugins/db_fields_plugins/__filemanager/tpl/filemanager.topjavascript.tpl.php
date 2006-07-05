<script language="JavaScript">
   <!--  

   var counter = 0;

   function childsIDplusOne(srcID,prnt)
   {
		 //alert(counter);
		 var newField = prnt.childNodes;
		 if(newField.length>0)
		 {
			   for (var i=0;i<newField.length;i++)
			   {
					 var theName = newField[i].name
					 if (theName)
					 newField[i].name = theName + counter;

					 var theID = newField[i].id
					 if (theID)
					 newField[i].id = theID + counter;
					 prnt2=newField[i].childNodes;
					 if(prnt2.length>0)
					 {
						   childsIDplusOne(srcID,newField[i]);
					 }
			   }
		 }
   }

   function moreFields(srcID)
   {
		 counter = document.getElementById('counter'+srcID).value;
		 counter++;
		 document.getElementById('counter'+srcID).value=counter;

		 var newFields = document.getElementById('templbox'+srcID).cloneNode(true);

		 newFields.id = 'cln'+srcID+counter;
		 newFields.style.display = 'block';

		 childsIDplusOne(srcID,newFields);

		 var insertHere = document.getElementById('writeroot'+srcID);
		 insertHere.parentNode.insertBefore(newFields,insertHere);

		 var newlabel =  document.getElementById('label'+srcID+counter); 
		 newlabel.innerHTML='<?=lang('File')?> '+counter;
   }

   function getLabel(type)
   {
		 if(type=="add") return "<?=lang('add')?>";
		 if(type=="replace") return "<?=lang('replace')?>";
   }

   function onBrowseServer(record, field, slot, obj_id) 
   {
		 childWindow=open("jinn/plugins/db_fields_plugins/__filemanager/popups/insert_image.php?field=" + field + "&curr_obj_id=" +obj_id,"console","resizable=no,width=580,height=440");
		 if (childWindow.opener == null)	childWindow.opener = self;
		 document.frm.CURRENT_RECORD.value=record;
		 document.frm.CURRENT_FIELD.value=field;
		 document.frm.CURRENT_SLOT.value=slot;
   }

   function onBrowseServer2(record, field, calledfrom) 
   {
		 var _str = calledfrom.id;
		 var _arr = _str.split('_');

		 //	 alert(_str);

		 document.frm.CURRENT_RECORD.value=record;
		 document.frm.CURRENT_FIELD.value=field;
		 document.frm.CURRENT_SLOT.value=_arr[1];

		 childWindow=open("jinn/plugins/db_fields_plugins/__filemanager/popups/insert_image.php?field=" + field,"console","resizable=no,width=580,height=440");
		 if (childWindow.opener == null) childWindow.opener = self;
   }

   function setSlot(record, field, slot, fileurl, thumbnail, buttontext, showfilename)
   {

		 //set the img src property for preview purposes
		 //fill a hidden form input to enable processing and saving of the chosen image path on submitting the form

		 //todo: set img style?
		 //todo: remove width/height text?
		 //todo: remove delete checkbox?

		 var cmd;

		 if(document.getElementById(record + "_IMG_EDIT_" + field + slot ))
		 {
			   cmd = "document.getElementById(\"" + record + "_IMG_EDIT_" + field + slot +"\").value = \"" + fileurl + "\";";

		 }
		 else
		 {
			   cmd = "document.frm." + record + "_IMG_EDIT_" + field + slot +".value = \"" + fileurl + "\";";
		 }

		 //	  document.getElementById(record + "_IMG_EDIT_" + field + slot).value = fileurl;

		 eval(cmd);

		 document.getElementById( record + "_IMG_" + field + slot ).src = thumbnail;

		 // remove border when we replcase it with a spacer
		 if(fileurl=='delete')
		 {
			   document.getElementById( record + "_IMG_" + field + slot ).style.border = "0px";
		 }
		 else
		 {
			   document.getElementById( record + "_IMG_" + field + slot ).style.border = "solid 1px #000000";

		 }
		 
		 if(document.getElementById( record + "_IMGLINK_" + field + slot ))
		 {
			   document.getElementById( record + "_IMGLINK_" + field + slot ).href = 'javascript:alert(\'<?=lang('To see changed previews you have to safe this record first.')?>\')';
		 }
		 //	  cmd = "document.frm." + record + "_IMG_EDIT_BUTTON_" + field + slot + ".value = \"" + buttontext + "\";";
		 //eval(cmd);

		 if(showfilename)
		 {
			   //get the filename without the path
			   var val2_arr = fileurl.split("/");
			   var idx = val2_arr.length - 1;
			   cmd = "document.getElementById(\"" + record + "_PATH_" + field + slot + "\").innerHTML = \"<b>"+ val2_arr[idx] + "</b>\";";
			   eval(cmd);
			   cmd = "document.getElementById(\"" + record + "_PATH_" + field + slot + "\").style.display = \"inline\";";
			   eval(cmd);
		 }
		 else
		 {
			   cmd = "document.getElementById(\"" + record + "_PATH_" + field + slot + "\").style.display = \"none\";";
			   eval(cmd);
		 }
   }

   function onSave(fileurl, filetype)
   {
		 //access the CURRENT_... hidden fields to find out which slot to use
		 if(filetype == "<?=$this->type_id_image?>")
		 {
			   //we need to put a dot in front of the filename to display the thumbnail
			   //fixme: can this be done easier with RegEx?
			   var val2_arr = fileurl.split("/");
			   var idx = val2_arr.length - 1;
			   val2_arr[idx] = "." + val2_arr[idx];
			   var thumb = val2_arr.join("/");
			   setSlot(document.frm.CURRENT_RECORD.value, document.frm.CURRENT_FIELD.value, document.frm.CURRENT_SLOT.value, fileurl, thumb, getLabel("replace"), false);
		 }
		 else if(filetype == "<?=$this->type_id_other?>")
		 {
			   setSlot(document.frm.CURRENT_RECORD.value, document.frm.CURRENT_FIELD.value, document.frm.CURRENT_SLOT.value, fileurl, "<?=$this->unknown?>", getLabel("replace"), true);
		 }
		 else
		 {
			   setSlot(document.frm.CURRENT_RECORD.value, document.frm.CURRENT_FIELD.value, document.frm.CURRENT_SLOT.value, fileurl, "<?=$this->unknown?>", getLabel("replace"), true);
		 }
   }

   /*   function onDelete2(record, field, slot)
   {
		 setSlot(record, field, slot, "delete", "<?=$this->spacer?>", getLabel("add"), false);
   }
   */
   function lowerCounter(srcID)
   {
		 counter = document.getElementById('counter'+srcID).value;
		 counter--;
		 document.getElementById('counter'+srcID).value=counter;
   }

   function onDelete(record, field, slot)
   {
		 setSlot(record, field, slot, "delete", "<?=$this->spacer?>", getLabel("add"), false);
   }
   -->
</script>
