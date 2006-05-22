<script>
   // function that puts the selected items in listbox allenormen in listbox selallenormen
   function SelectPlace (obj,allObj)
   {
		 var objectSelectedItems = copyOptionsObject (document.popfrm[obj].options);

		 if (document.popfrm[allObj].selectedIndex != -1) 
		 {
			   for (var i=0; i < document.popfrm[allObj].length; i++) 
			   {
					 if (document.popfrm[allObj].options[i].selected == true && document.popfrm[allObj].options[i].text != '')
					 if ( !CheckForDoubles (objectSelectedItems, document.popfrm[allObj].options[i])) 
					 {
						   newOption = new Option(document.popfrm[allObj].options[i].text, document.popfrm[allObj].options[i].value, false, false);
						   document.popfrm[obj].options[document.popfrm[obj].length] = newOption;
					 }
			   }
		 }
   }
   // function to make a hard-copy of an javascript object
   function copyOptionsObject (optionsObject)
   {
		 var copyObject = new Array()
		 for (i=0; i < optionsObject.length; i++)
		 copyObject[i] = new Option (optionsObject[i].text, optionsObject[i].value, false, false);
		 return copyObject;
   }

   // function to check if the optionObject is already in the listbox
   function CheckForDoubles (optionsObject, optionObject)
   {
		 var doubleFound = false;
		 if (optionsObject.length)
		 for (var i=0; i < optionsObject.length; i++) {
			   if (optionsObject[i].text == optionObject.text && 
			   optionsObject[i].value == optionObject.value)
			   doubleFound = true;
		 }
		 return doubleFound;
   }

   // function to delete selected items in a function
   // This function uses the recursive function deleteItemsInListbox
   function DeSelectPlace (obj)
   {
		 if (document.popfrm[obj].selectedIndex != -1)
		 DeleteItemsInListbox(document.popfrm[obj],obj);
   }

   // recursive function to delete selected items in a listbox
   function DeleteItemsInListbox (listbox,obj)
   {
		 if (typeof(document.popfrm[obj]) != "undefined") {
			   var index = listbox.length;
			   if (index != 0 && listbox.selectedIndex != -1) {
					 listbox.options[listbox.selectedIndex] = null;
					 DeleteItemsInListbox (listbox);
			   }
			   return true;
		 }
   }
   function saveOptions(obj,hidden_fld) 
   { //v1.0
	  var boxLength = document.popfrm[obj].length;
	  var count = 0;
	  var strValues;
	  if (boxLength != 0) 
	  {
			for (i = 0; i < boxLength; i++) 
			{
				  if (count == 0) 
				  {
						selectAll(document.popfrm[obj],true)
						strValues = document.popfrm[obj].options[i].value;
				  }
				  else 
				  {
						selectAll(document.popfrm[obj],true)
						strValues = strValues + "," + document.popfrm[obj].options[i].value;
				  }
				  count++;
			}
	  }

	  if (strValues)  document.popfrm[hidden_fld].value=strValues;
   }
   function selectAll(cbList,bSelect) {
		 for (var i=0; i<cbList.length; i++)
		 cbList[i].selected = cbList[i].checked = bSelect
   }


</script>
<?php #_debug_array($this->set_val);?>
<?php #die();?>
<input type="hidden" name="<?php echo($this->cval['fname']);?>[value]" value="">
<tr><td><?php echo($this->cval['label']);?></td></tr>
<tr>
   <td style="margin: 0pt; padding: 0pt;" valign="top">
	  <select ondblclick="SelectPlace('<?php echo($this->cval['fname']);?>[selected]',this.name);saveOptions('<?php echo($this->cval['fname']);?>[selected]','<?php echo($this->cval['fname']);?>[value]');" style="width: 190px;" multiple="multiple" size="5" name="<?php echo($this->cval['fname']);?>[all]">
		 <?php foreach($this->fields as $field):?>
		 <option value="<?php echo($field[field_name]);?>"><?php echo($field[field_name]);?></option>
		 <?php endforeach?>
	  </select>
   </td>

   <td style="margin: 0pt; padding: 0pt; vertical-align: middle; width: 40px;" valign="middle">
	  <input onclick="SelectPlace('<?php echo($this->cval['fname']);?>[selected]','<?php echo($this->cval['fname']);?>[all]');saveOptions('<?php echo($this->cval['fname']);?>[selected]','<?php echo($this->cval['fname']);?>[value]');" class="egwbutton" style="margin: 3px 10px; width: 40px;" value=" &gt;&gt; " name="add" type="button">
	  <input onclick="DeSelectPlace('<?php echo($this->cval['fname']);?>[selected]');saveOptions('<?php echo($this->cval['fname']);?>[selected]','<?php echo($this->cval['fname']);?>[value]');" class="egwbutton" style="margin: 3px 10px; width: 40px;" value=" &lt;&lt; " name="remove" type="button">

   </td>

   <td style="margin: 0pt; padding: 0pt; width: 190px;" valign="top">
	  <select ondblclick="DeSelectPlace(this.name);saveOptions(this.name,'<?php echo($this->cval['fname']);?>[value]');" style="width: 190px; color: red;" multiple="multiple" size="5" name="<?php echo($this->cval['fname']);?>[selected]">
		 <?php if(is_array($this->set_val)):?>
		 <?php foreach($this->set_val as $value):?>
		 <option value="<?php echo($value);?>"><?php echo($value);?></option>
		 <?php endforeach?>
		 <?php endif?>
	  </select>
   </td>
</tr>
<script>
   saveOptions('<?php echo($this->cval['fname']);?>[selected]','<?php echo($this->cval['fname']);?>[value]');
</script>
