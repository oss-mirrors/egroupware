<!-- many_to_one essention javascript -->

<script>

   function ajax2_render_frm_buttons()
   {
		 submit ='<input type="button" value="<?=lang('Save')?>" onclick="ajaxsaveform(\''+current_m2o_id+'\',\''+current_save_url+'\')" />';
		 submit+='<input type="button" value="<?=lang('Close')?>" onclick="closem2oform(\''+current_m2o_id+'\')" />';
		 return submit;
   }

   function ajax2_m2o_new_frm(m2o_id,object_conf_id,where_string,submitTo,save_url)
   {
		 current_m2o_id=m2o_id;
		 // current_m2o_object_conf_id=object_conf_id;
		 // current_m2o_wherestring=where_string;
		 current_save_url=save_url;

		 //var submitTo = url;
		 
		 //location.href = submitTo; //uncomment if you need for debugging
		 
		 //http('POST', submitTo, ajax_response_tinymce, document.form1);
		 
		 http('GET', submitTo, ajax2_response_render_frm,'');
   }

   function ajax2_response_render_frm(data) 
   {
		 //submit='<input type="button" value="<?=lang('Save')?>" onclick="ajaxsaveform(\''+current_m2o_id+'\',\''+current_save_url+'\')" />';
		 //submit+='<input type="button" value="<?=lang('Close')?>" onclick="closem2oform(\''+current_m2o_id+'\')" />';
		 //document.getElementById('div_m2o'+current_m2o_id).innerHTML=data+submit;	

		 // because scripts are not evaluated in new innerHTML we have to do this by hand
		 //scripts=document.getElementById('div_m2o'+current_m2o_id).getElementsByTagName("script");	
		 
		 var inner = document.getElementById('div_m2o'+current_m2o_id);

		 var node = document.createElement('div'); 			// create a new div element
		 node.innerHTML = data.justdata; 		  	 		// append the text to node
		 node.innerHTML += ajax2_render_frm_buttons();   	// append the text to node
		 inner.innerHTML = '';                     			// clear the destination node
		 inner.appendChild(node);                  			// append the node as a child and ...
   }



</script>




<script>
   var current_m2o_id;
   var current_m2o_wherestring;
   var current_m2o_object_conf_id;
   var current_save_url;
   var current_localkey;

   var http_request = false;

   function makeGETRequest(url,onreadystatefunction) 
   {
		 http_request = false;

		 if (window.XMLHttpRequest) 
		 { 
			   // Mozilla, Safari,...
			   http_request = new XMLHttpRequest();
			   if (http_request.overrideMimeType) {
					 http_request.overrideMimeType('text/xml');
			   }
		 } 
		 else if (window.ActiveXObject) 
		 { 
			   // IE
			   try 
			   {
					 http_request = new ActiveXObject("Msxml2.XMLHTTP");
			   } 
			   catch (e) 
			   {
					 try 
					 {
						   http_request = new ActiveXObject("Microsoft.XMLHTTP");
					 } catch (e) 
					 {}
			   }
		 }
		 if (!http_request) 
		 {
			   alert('Cannot create XMLHTTP instance');
			   return false;
		 }
		 http_request.onreadystatechange = eval(onreadystatefunction);
		 http_request.open('GET', url, true);
		 http_request.send(null);
   }


   function makePOSTRequest(url, parameters,onreadystatefunction) 
   {
		 http_request = false;

		 if (window.XMLHttpRequest) 
		 { 
			   // Mozilla, Safari,...
			   http_request = new XMLHttpRequest();
			   if (http_request.overrideMimeType) {
					 http_request.overrideMimeType('text/xml');
			   }
		 } 
		 else if (window.ActiveXObject) 
		 { 
			   // IE
			   try 
			   {
					 http_request = new ActiveXObject("Msxml2.XMLHTTP");
			   } 
			   catch (e) 
			   {
					 try 
					 {
						   http_request = new ActiveXObject("Microsoft.XMLHTTP");
					 } 
					 catch (e) 
					 {}
			   }
		 }

		 if (!http_request) 
		 {
			   alert('Cannot create XMLHTTP instance');
			   return false;
		 }

		 http_request.onreadystatechange = eval(onreadystatefunction);
		 http_request.open('POST', url, true);
		 http_request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		 http_request.setRequestHeader("Content-length", parameters.length);
		 http_request.setRequestHeader("Connection", "close");
		 http_request.send(parameters);

   }

   /**
    * m2o_edit_record 
    * 
    * @param m2o_id $m2o_id 
    * @param object_conf_id $object_conf_id 
    * @param where_string $where_string 
    * @param url $url 
    * @param save_url $save_url 
    * @access public
    * @return void
    */
   function m2o_edit_record(m2o_id,object_conf_id,where_string,url,save_url)
   {
		 current_m2o_id=m2o_id;
		 current_m2o_object_conf_id=object_conf_id;
		 current_m2o_wherestring=where_string;
		 current_save_url=save_url;

		 makeGETRequest(url,'ajaxrenderform');
		 //document.getElementById('div_m2o_debug'+current_m2o_id).innerHTML=url;	
   }

   function m2o_delete_record(m2o_id,url)
   {
		 current_m2o_id=m2o_id;
		 makeGETRequest(url,'ajaxafterdelform');
   }

   function closem2oform(id)
   {
		 document.getElementById('div_m2o'+id).innerHTML='';	
   }

   /**
   * processReqChange: do some magic xmlhttpreq stuff and read XML data and change elements
   * todo rename 
   */
   function ajaxrenderform()
   {
		 if (http_request.readyState == 4 && http_request.status == 200 && http_request.responseXML.documentElement) 
		 {
			   response = http_request.responseXML.documentElement;
			   if(response.getElementsByTagName('field').length > 0 ) 
			   {
					 data=response.getElementsByTagName('field')[0].firstChild.data;

					 submit='<input type="button" value="<?=lang('Save')?>" onclick="ajaxsaveform(\''+current_m2o_id+'\',\''+current_save_url+'\')" />';
					 submit+='<input type="button" value="<?=lang('Close')?>" onclick="closem2oform(\''+current_m2o_id+'\')" />';
					 document.getElementById('div_m2o'+current_m2o_id).innerHTML=data+submit;	

					 // because scripts are not evaluated in new innerHTML we have to do this by hand
					 scripts=document.getElementById('div_m2o'+current_m2o_id).getElementsByTagName("script");	

					 for( var i = 0; i<scripts.length;i++) 
					 { 
						   setTimeout(scripts[i].childNodes[0].data,1000); 

						   //FIXME why does tinyMCE not work???
						   //setTimeout(scripts[i].childNodes[0].data+';document.getElementById(\'M2OXXXweergavenaam\').style.background="green"',1000); 
						   //var str='tinyMCE.init({mode:"exact",elements:"M2OXXXweergavenaam",theme:"default"});';
						   //setTimeout(str,1000); 
						   //alert(scripts[i]);
					 }
			   }
		 }
   }

   /**
   * readForm 
   * 
   * @param parent_id $parent_id 
   * @access public
   * @return void
   */
   function readForm(parent_id)
   {
		 input_arr=document.getElementById(parent_id).getElementsByTagName("input");	
		 textarea_arr=document.getElementById(parent_id).getElementsByTagName("textarea");	
		 select_arr=document.getElementById(parent_id).getElementsByTagName("select");	

		 var poststr='';
		 var debugstr='';

		 for( var i = 0; i<input_arr.length;i++) 
		 { 
			   if (input_arr[i].type == "checkbox") 
			   {
					 if(poststr!='')
					 {
						   poststr+="&"; 
					 }
					 if (input_arr[i].checked) 
					 {
						   poststr += input_arr[i].name + "=" + input_arr[i].value;
					 } 
					 else 
					 {
						   poststr += input_arr[i].name;
					 }
			   }
			   else if (input_arr[i].type == "radio") 
			   {
					 if (input_arr[i].checked) 
					 {
						   if(poststr!='')
						   {
								 poststr+="&"; 
						   }
						   poststr += input_arr[i].name + "=" + input_arr[i].value;
					 }
			   }
			   else
			   {
					 if(poststr!='')
					 {
						   poststr+="&"; 
					 }
					 poststr += input_arr[i].name+"="+encodeURI(input_arr[i].value);
			   }
		 }

		 for( var i = 0; i<textarea_arr.length;i++) 
		 { 
			   if(poststr!='')
			   {
					 poststr+="&"; 
			   }
			   poststr += textarea_arr[i].name+"="+encodeURI(textarea_arr[i].value);
		 }

		 for( var i = 0; i<select_arr.length;i++) 
		 { 
			   if(poststr!='')
			   {
					 poststr+="&"; 
			   }
			   poststr += select_arr[i].name+"="+select_arr[i].options[select_arr[i].selectedIndex].value;
		 }

		 //alert(poststr);
		 return poststr;
   }

   function ajaxafterdelform() 
   {
		 if (http_request.readyState == 4) 
		 {
			   if (http_request.status == 200) 
			   {
					 					 //alert(http_request.responseText);
					 result = http_request.responseText;
					 //					 document.getElementById('div_m2o'+current_m2o_id).innerHTML+=result;
//					 document.getElementById('div_m2o'+current_m2o_id).innerHTML='';

					 ajaxrefreshlist();

			   } 
			   else 
			   {
					 alert('There was a problem with the request.');
			   }
		 }
   }
   
   function ajaxaftersaveform() 
   {
		 if (http_request.readyState == 4) 
		 {
			   if (http_request.status == 200) 
			   {
		//			 					 alert(http_request.responseText);
					 result = http_request.responseText;
					 //					 document.getElementById('div_m2o'+current_m2o_id).innerHTML+=result;
					 document.getElementById('div_m2o'+current_m2o_id).innerHTML='';

					 ajaxrefreshlist();

			   } 
			   else 
			   {
					 alert('There was a problem with the request.');
			   }
		 }
   }

   function ajaxsaveform(m2o_id,url) 
   {
		 current_m2o_id=m2o_id;
		 poststr=readForm('div_m2o'+m2o_id);

		 makePOSTRequest(url,poststr,'ajaxaftersaveform');
   }

   function ajaxReorderList(m2o_id,col)
   {
		 //url='<?=$this->xmlhttp_get_m2o_list?>&m2o_rule_arr_enc='+document.getElementById('m2o_enc_rule_'+current_m2o_id).value+'&localkey='+document.getElementById('m2o_localkey_'+current_m2o_id).value+'&orderby='+col+'+'+direction;
		 //makeGETRequest(url,'ajaxrenderlist');
   }

   function ajaxrefreshlist()
   {
		 url='<?=$this->xmlhttp_get_m2o_list?>&m2o_rule_arr_enc='+document.getElementById('m2o_enc_rule_'+current_m2o_id).value+'&localkey='+document.getElementById('m2o_localkey_'+current_m2o_id).value;

		 makeGETRequest(url,'ajaxrenderlist');

		 //document.getElementById('div_m2o_debug'+current_m2o_id).innerHTML=url;	
   }

   function ajaxrenderlist()
   {
		 if (http_request.readyState == 4 && http_request.status == 200 && http_request.responseXML.documentElement) 
		 {
			   response = http_request.responseXML.documentElement;
			   if(response.getElementsByTagName('field').length > 0 ) 
			   {
					 data=response.getElementsByTagName('field')[0].firstChild.data;

					 document.getElementById('div_m2o_list'+current_m2o_id).innerHTML=data;	
			   }
		 }
   }
</script>

