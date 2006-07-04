<script>
   var multicounter = <?php echo($this->nr);?>;
   function deleteSlot(id)
   {
		 fieldset = document.getElementById('fieldset'+id);
		 doDelete(fieldset.getElementsByTagName('select'));
		 doDelete(fieldset.getElementsByTagName('checkbox'));
		 doDelete(fieldset.getElementsByTagName('input'));
		 fieldset.style.visibility = 'hidden';
		 fieldset.style.display="none";
   }
   function moreFields(srcID)
   {
		 multicounter = multicounter+1;
		 slotvar = document.getElementById(srcID);
		 mslot = document.getElementById('multislots')
		 fieldset= document.createElement("fieldset");
		 legend = document.createElement("legend");
		 legend.innerHTML="Slot "+multicounter;
		 fieldset.innerHTML=slotvar.innerHTML;
		 fieldset.appendChild(legend);
		 fieldset.innerHTML = doOnClickChange(slotvar,multicounter);
		 mslot.appendChild(fieldset);
   }
   function doDelete(input)
   {
		 for (var k=0; k<input.length; k++) 
		 {
			   name_new= "Delete"+'['+multicounter+']'+name[1];
			   input[k].name = name_new;
		 }
   }

   function doOnClickChange(div, multicounter)
   {
		 textarr = div.innerHTML.split("MLT001");
		 if(multicounter < 10)
		 {
			   res = textarr.join("MLT00"+multicounter);
		 }
		 if(multicounter >9 && multicounter <100)
		 {
			   res = textarr.join("MLT0"+multicounter);
		 }
		 if(multicounter >99) 
		 {
			   res = textarr.join("MLT"+multicounter);
		 }
		 return res;
   }
   function doNameChange(input)
   {
		 for (var k=0; k<input.length; k++) 
		 {
			   name = input[k].name.split("[1]");
			   if(input[k].type=="text")
			   {
					 input[k].value='';
			   }
			   name_new= name[0]+'['+multicounter+']'+name[1];
			   input[k].name = name_new;
	   }
   }
</script>
<tr>
   <td id="multislots" colspan="2">
	  <?php if(is_array($this->slots)):?>
	  <?php foreach($this->slots as $slot):?>
	  <?php echo($slot);?>
	  <?php endforeach?>
	  <?php endif ?>
   </td>
</tr>
<tr>
   <td>
	  <input style="margin:5px 0px 5px 0px;" class="egwbutton"  type="button" value="add slot" onClick="moreFields('slot1')">
   </td>
</tr>
