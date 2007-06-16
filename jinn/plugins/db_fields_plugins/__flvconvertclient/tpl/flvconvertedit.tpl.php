<script>
//   var buttonBgColor='#b9d5e3';
   function setIdToMetaField()
   {
		 var obj=document.getElementsByName('<?php echo $this->metafieldname?>');

		 if(obj.length > 0)
		 {
			   obj[0].setAttribute('id','<?php echo $this->metafieldname?>');
		 }
   }
   function fixid()
   {
		 if(document.getElementById('<?php echo $this->metafieldname?>')==undefined)
		 {
			   setIdToMetaField();
			   //xajax_doXMLHTTP("jinn.ajaxjinn.plg_forw",'flvconvertclient.fixmetaid');
		 }
   }

   function showMessage(msg,type)
   {
		 document.getElementById('flvmsgbox').innerHTML= type+': '+msg;
   }

   function startC()
   {
		 var movieurl = get_source_movie();
		 if(movieurl=='' || !movieurl)
		 {
			   alert('<?php echo lang('Please first upload a source movie to convert')?>');
		 }
		 else
		 {
			   fixid();
			   xajax_doXMLHTTP("jinn.ajaxjinn.plg_forw",'flvconvertclient.addToQueue',getRecordFieldInfo(),movieurl);
			   document.getElementById('buttonStartC').disabled=true;
			   document.getElementById('buttonStartC').style.borderStyle='inset';
		 }
   }

   function removeFLV()
   {
   }

   function cancelC()
   {
		 document.getElementById('buttonStartC').disabled=false;
		 document.getElementById('buttonStartC').style.borderStyle='outset';
   }

   function getFileC()
   {
		 fixid();
		 xajax_doXMLHTTP("jinn.ajaxjinn.plg_forw",'flvconvertclient.getFile',getRecordFieldInfo(),getMetaFieldValue());
   }

   function getRecordFieldInfo()
   {
		 return document.getElementById('recordfieldinfo').value;
   }
   function getMetaFieldValue()
   {
		 fixid();
		 return document.getElementById('<?php echo $this->metafieldname ?>').value;
   }

   function getStatusC()
   {
		 xajax_doXMLHTTP("jinn.ajaxjinn.plg_forw",'flvconvertclient.getStatus',getRecordFieldInfo(),getMetaFieldValue());
   }

   function get_source_movie()
   {
		 var srcfields=document.getElementsByName('<?php echo $this->sourcefield?>');
		 var srcfmfields=document.getElementsByName('<?php echo $this->fmsourcefield?>');

		 if(srcfields.length < 1 && srcfmfields.length < 1) 
		 {
			   alert('<?php echo lang('Configured source field is not correct')?>');
			   return;
		 }

		 if(srcfmfields.length > 0 && !srcfmfields[0].value=='' &&  srcfmfields[0].value!='delete')
		 {
			   return srcfmfields[0].value;
		 }
		 if(srcfields.length > 0 && !srcfields[0].value=='' && srcfmfields[0].value!='delete')
		 {
			   return srcfields[0].value;
		 }
   }
</script>
<div style="color:#aaaaaa;text-align:center;vertical-align:middle;width:360px;height:240px;border: inset 2px #cccccc;background-color:black;">
   <span id="flvmsgbox"><?php echo $this->videoPreview?><?php echo lang('No FLV available yet.')?></span>
</div>
<input type="hidden" name="recordfieldinfo" id="recordfieldinfo" value="<?php echo $this->recordfieldinfo?>" />
<input type="hidden" name="<?php echo $this->field_name?>" id="<?php echo $this->field_name?>" value="<?php echo $this->fvalue?>" />
<input type="button" id="buttonStartC" value="<?php echo lang('Start conversion')?>" onclick="startC();" />
<input type="button" value="<?php echo lang('Get Status')?>" onclick="getStatusC();" />
<input type="button" value="<?php echo lang('Retrieve File')?>" onclick="getFileC();" />
<input type="button" value="<?php echo lang('Cancel Conversion')?>" onclick="cancelC();" />
