<?php $id=uniqid(1)?>
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

   function showPlayer()
   {
		 document.getElementById('flvplayer').style.display= 'block';
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

	  function changeFlash(url)
	  {
			var d=document;
			(d.all)? d.all("flash<?php echo $id?>").movie = url :
			d.embeds["flash<?php echo $id?>"].src = url;
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
   <div style="color:#aaaaaa;text-align:center;vertical-align:middle;width:360px;height:260px;border: inset 2px #cccccc;background-color:black;">
	  <div style="height:20px;" id="flvmsgbox"><?php echo $this->videoPreview?><?php echo lang('No FLV available yet.')?></div>
	  <div id="flvplayer"  style="display:<?php echo $this->displayplayer?>;width:<?php echo $this->media_arr['width']?>px;height:<?php echo $this->media_arr['height']?>px">
		 <object class="flash" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"
			id="flash<?php echo $id?>"
			width="<?php echo $this->media_arr['width']?>"
			height="<?php echo $this->media_arr['height']?>"
			codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab">
			<param name="movie" value="jinn/plugins/db_fields_plugins/__flvconvertclient/stream.swf?<?php echo $this->querystring?>" />
			<param name="menu" value="<?php echo $this->media_arr['display_menu']?>" />
			<param name="quality" value="<?php echo $this->media_arr['quality']?>" />
			<param name="scale" value="<?php echo $this->media_arr['scale']?>" />
			<param name="wmode" value="<?php echo $this->media_arr['window_mode']?>" />
			<param name="loop" value="<?php echo $this->media_arr['do_loop']?>" />
			<embed src="jinn/plugins/db_fields_plugins/__flvconvertclient/stream.swf?<?php echo $this->querystring?>"
			name="flash<?php echo $id?>"
			menu="<?php echo $this->media_arr['display_menu']?>"
			quality="<?php echo $this->media_arr['quality']?>"
			scale="<?php echo $this->media_arr['scale']?>"
			wmode="<?php echo $this->media_arr['window_mode']?>"
			width="<?php echo $this->media_arr['width']?>"
			height="<?php echo $this->media_arr['height']?>"
			align="top"
			play="true"
			loop="<?php echo $this->media_arr['do_loop']?>"
			allowScriptAccess="sameDomain"
			swLiveConnect="true"
			type="application/x-shockwave-flash"
			pluginspage="http://www.macromedia.com/go/getflashplayer"/>
		 </object>
	  </div>


	  <?php echo $this->player; ?>
   </div>
   <input type="hidden" name="recordfieldinfo" id="recordfieldinfo" value="<?php echo $this->recordfieldinfo?>" />
   <input type="hidden" name="<?php echo $this->field_name?>" id="<?php echo $this->field_name?>" value="<?php echo $this->fvalue?>" />
   <input type="button" id="buttonStartC" value="<?php echo lang('Start conversion')?>" onclick="startC();" />
   <input type="button" value="<?php echo lang('Get Status')?>" onclick="getStatusC();" />
   <input type="button" value="<?php echo lang('Retrieve File')?>" onclick="getFileC();" />
   <input type="button" value="<?php echo lang('Cancel Conversion')?>" onclick="cancelC();" />
