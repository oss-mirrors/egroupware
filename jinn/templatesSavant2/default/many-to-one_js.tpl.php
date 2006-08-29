<!-- many_to_one essention javascript -->

<script>

   var current_m2o_id;
   var current_m2o_wherestring;
   var current_m2o_object_conf_id;
   var current_save_url;
   var current_localkey;

   var http_request = false;

   var block_parent_save = false;

   function ajax2_render_frm_buttons()
   {
		 submit ='<input type="button" value="<?=lang('Save')?>" onclick="ajax2_post_form(\''+current_m2o_id+'\',\''+current_save_url+'\')" />';
		 submit+='<input type="button" value="<?=lang('Close')?>" onclick="ajax2_close_frm(\''+current_m2o_id+'\')" />';
		 return submit;
   }

   /*
   * @param m2o_id $m2o_id 
   * @param object_conf_id $object_conf_id 
   * @param where_string $where_string 
   * @param url $url 
   * @param save_url $save_url 
   * @access public
   * @return void
   */
   function ajax2_m2o_edit_frm(m2o_id,object_conf_id,where_string,submitTo,save_url)
   {
		 current_m2o_id=m2o_id;
		 current_save_url=save_url;

		 //don't know if these are needed
		 current_m2o_object_conf_id=object_conf_id;
		 current_m2o_wherestring=where_string;

		 submitTo += '&m2oid='+current_m2o_id;
	
		 //UNCOMMENT IF YOU NEED FOR DEBUGGING
		 //location.href = submitTo; 
		 
		 http('GET', submitTo, ajax2_response_render_frm,'');

   }

   function ajax2_response_render_frm(data) 
   {
		 var inner = document.getElementById('div_m2o'+current_m2o_id);

		 var node = document.createElement('div'); 			

		 node.innerHTML = data.justdata; 		  	 	
		 node.innerHTML += ajax2_render_frm_buttons(); 
		 node.style.padding="10px";
		 node.style.border="dashed 1px red";
		 node.style.margin="10px";

		 inner.innerHTML = '';                     	
		 inner.appendChild(node);                  
		 block_parent_save = true;
   }

   function ajax2_post_form(m2o_id,submitTo)
   {
		 if(typeof tinyMCE != "undefined")
		 {
			   // .... (no skip cleanup, skip callback)
			   tinyMCE.triggerSave(false,true);
		 }

 		 current_m2o_id=m2o_id;
		 //location.href = submitTo; 
		 
		 http('POST', submitTo, ajax2_after_form_post, document.getElementById('frm'+current_m2o_id));
   }

   function ajax2_after_form_post(data)
   {
		 //document.getElementById('div_m2o'+current_m2o_id).innerHTML='';
		 ajax2_close_frm(current_m2o_id);
		 ajax2_refreshlist();
   }

   function ajax2_delete_record(m2o_id,submitTo)
   {
		 current_m2o_id=m2o_id;
		 http('GET', submitTo, ajax2_response_delete_record,'');
   }

   function ajax2_response_delete_record(data)
   {
		 ajax2_refreshlist();
   }

   function ajax2_refreshlist()
   {
		 submitTo='<?=$this->xmlhttp_get_m2o_list2?>&m2o_rule_arr_enc='+document.getElementById('m2o_enc_rule_'+current_m2o_id).value+'&localkey='+document.getElementById('m2o_localkey_'+current_m2o_id).value;

		 http('GET',submitTo,ajax2_render_list,'');
   }
   
   function ajax2_render_list(data)
   {
		 document.getElementById('div_m2o_list'+current_m2o_id).innerHTML=data.list;	
   }

   function ajax2_close_frm(id)
   {
		 document.getElementById('div_m2o'+id).innerHTML='';	
		 block_parent_save = false;
   }
   
   function ajaxReorderList(m2o_id,col)
   {
		 alert('<?=lang('Not yet implemented');?>');
   }

</script>
