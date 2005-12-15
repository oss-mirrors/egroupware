/*
	Copyright (c) 2004-2005, The Dojo Foundation
	All Rights Reserved

	Licensed under the Academic Free License version 2.1 or above OR the
	modified BSD license. For more information on Dojo licensing, see:

		http://dojotoolkit.org/community/licensing.shtml
*/
/* Copyright (c) 2004-2005 The Dojo Foundation, Licensed under the Academic Free License version 2.1 or above */	/**************************************************************************\
	* eGroupWare - Chatty                                                      *
	* http://www.egroupware.org                                                *
	* Copyright (C) 2005  TITECA-BEAUPORT Olivier    oliviert@maphilo.com      *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/
dojo.provide("dojo.chatty.IoManager");
dojo.require("dojo.io");

	dojo.chatty.IoManager = function(){
	
	var serverUrl = "";
	var transport = "XMLHTTPTransport";
	var windowManager = null;
	var timeSync = 10;
	
	
	
	/**
	 * Method: setContext
	 *
	 *	set the necessary var for IoManager to properly operate
	 *
	 * Parameters:
	 *
	 *	url - the url to call
	 *	winManager - reference to the windowManager
	 *  timeout - the timeout to synchronise data
 	 *
	 */
	this.setContext = function(url, winManager, timeout){
		this.serverUrl = url;
		this.windowManager = winManager;
		this.timeSync = timeout;
	}
	
	this.sync = function(postdata){
		var _this = this;

		var toPostData = this.encode("chatty.sync", postdata);
		dojo.io.bind({
			url: this.serverUrl,
			handle: function(type, data, evt){
					if(type == "load"){	
						var jsData = _this.decode(data);
						_this.windowManager.syncHandler(jsData);
					} 
				},
		sync: false,		
		postContent: toPostData,
		method: "post",
		encoding: "utf8",
		mimetype: "text/xml",
		transport: this.transport,
		contentType:"text/xml"
		}
		);
	}

	this.request = function(postdata, method, callback){
		var _this = this;
		var toPostData = this.encode(method, postdata);
		dojo.io.bind({
			url: this.serverUrl,
			handle: function(type, data, evt){
					if(type == "load"){	
						var jsData = _this.decode(data);
						_this.windowManager[callback](jsData);
					} 
				},
		sync: false,		
		postContent: toPostData,
		method: "post",
		encoding: "utf8",
		mimetype: "text/xml",
		transport: this.transport,
		contentType:"text/xml"
		}
		);
	}
	/**
	 * Method: encode
	 *
	 *	Encode the data in XMLRPC protocol and returns the appropriate string
	 *
	 * Parameters:
	 *
	 *	method - The name of the method to be called
	 *	params - The params to be passed to this method
	 *
	 */
	this.encode = function (method, params)
	{
		var i,msg = '';
		
		params = params || {};
		
		// Step 1: Create Header
		msg += '<?xml version="1.0"?>\n';
		msg += '<methodCall>\n';
		msg += '<methodName>' + method+ '</methodName>\n';
		msg += '<params>\n';

		// Step 2: Add Parameters
		//for (i in params)
		//{
			msg += '<param>\n';

			msg += '<value>' + this._processParameter(params) + '</value>\n';
			
			msg += '</param>\n';
		//}
		  
		// Step 3: Finallize
		msg += '</params>\n';
		msg += '</methodCall>';

		return msg;
	}

	/**
	 * Method: decode
	 *
	 *	Decodes received XML in XMLRPC response format to JS data structure
	 *
	 * Parameter:
	 *
	 *	response - The response from server, that can be either a DOM XML document
	 *	           or a XML string to be parsed
	 *
	 */
	this.decode = function (response)
	{
		if (typeof(response) != 'object' ||
			typeof(response.firstChild) != 'object')
		{
			throw({result: 'Invalid XMLRPC data', location: 'thyXMLRPCProtocol.decode'});
		}
		
		var i, data;
		for (i=0; i<response.childNodes.length; i++)
		{
			var node = response.childNodes[i];
			if (node.nodeType != 1) continue;
			
			if (node.nodeName == 'methodResponse')
			{
				data = this._decodeMethodResponse(node);
			}
		}

		if (!data || typeof(data) != 'object')
		{
			//TODO: ERROR Handling
			throw({result: 'Invalid XMLRPC data', location: 'thyXMLRPCProtocol.decode'});
		}

		return data;
	}

	/**
	 * Method: getContentType
	 *
	 *	Returns the content type of XML document
	 *
	 */
	this.getContentType = function()	{
		return 'text/xml';
	}

	/**
	 * Method: getServerURL
	 *
	 *	Returns the server URL
	 *
	 */
	this.getServerURL = function()
	{
		return this.serverUrl;
	}

	/*************************************************************************\
	 *                         Group: Private Methods                        *
	\*************************************************************************/

	/**
	 * Method: _encodeXMLEntities
	 *
	 *	Converts the passed string to another one with all XML entities encoded correctly
	 *
	 * Parameter:
	 *
	 *	toEncode - The string to be encoded
	 *
	 */
	this._encodeXMLEntities = function (toEncode){
		var toReturn = toEncode.replace(/&/g,'&amp;');
		toReturn = toReturn.replace(/</g,'&lt;');
		toReturn = toReturn.replace(/>/g,'&gt;');
		return toReturn;
	}


		/**
	 * Method: _removeNonElementNodes
	 *
	 *	Remove all childNodes that are not NonElement Nodes
	 *
	 * Parameter:
	 *
	 *	node - The node
	 *
	 */
	this._removeNonElementNodes = function (node)
	{
		var i;
		for (i=node.childNodes.length-1; i>=0; i--)
		{
			if (node.childNodes[i].nodeType != 1)
			{
				node.removeChild(node.childNodes[i]);
			}
		}
	}

	/**
	 * Method: _decodeMethodResponse
	 *
	 *	Parses methodResponse element and resturns its value
	 *
	 * Parameter:
	 *
	 *	node - The methodResponse node
	 *
	 */
	this._decodeMethodResponse = function (node)
	{
		var i;
		for (i=0; i<node.childNodes.length; i++)
		{
			var childNode = node.childNodes[i];

			// Check just for Element nodes, ignore Text Nodes \\
			if (childNode.nodeType != 1) continue;

			// Check for XMLRPC fault response \\
			if (childNode.nodeName == 'fault')
			{
				var j,valueNode;
				for (j=0; j<childNode.childNodes.length; j++)
				{
					if (childNode.childNodes[j].nodeType == 1 && childNode.childNodes[j].nodeName == 'value')
					{
						valueNode = childNode.childNodes[j];
						break;
					}
				}
				value = this._decodeValue(valueNode);
				break;
			}

			// Decode non-error response \\
			var paramsNode = node.firstChild;
			var paramNode, valueNode, j=1;

			while (paramsNode.nodeType != 1)
			{
				paramsNode = node.childNodes[j];
				j++;
			}
			
			paramNode = paramsNode.firstChild;
			j = 1;
			while (paramNode.nodeType != 1)
			{
				paramNode = paramsNode.childNodes[j];
				j++;
			}

			valueNode = paramNode.firstChild;
			j = 1;
			while (valueNode.nodeType != 1)
			{
				valueNode = paramNode.childNodes[j];
				j++;
			}
			
			value = this._decodeValue(valueNode);
		}

		return value;
	}

	/**
	 * Method: _decodeValue
	 *
	 *	Parses <value> elements
	 *
	 */
	this._decodeValue = function (node)
	{
		var i, value;
		for (i=0; i<node.childNodes.length; i++)
		{
			var childNode = node.childNodes[i];
			if (childNode.nodeType != 1) continue;

			switch (childNode.nodeName)
			{
				case 'struct': return this._decodeStruct(childNode);
				case 'array' : return this._decodeArray(childNode);
				case 'int':
				case 'i4':
				case 'double': return this._decodeNumber(childNode);
				case 'string': return this._decodeString(childNode);
				default: return null;
			}
		}
	}

	/**
	 * Method: _decodeStruct
	 *
	 *	Parses <struct> elements
	 *
	 */
	this._decodeStruct = function (node)
	{
		var i,j,childNode, memberNode;
		var name, value, struct = {};
		
		for (i=0; i<node.childNodes.length; i++)
		{
			childNode = node.childNodes[i];
			if (childNode.nodeType != 1) continue;

			for (j=0; j<childNode.childNodes.length; j++)
			{
				memberNode = childNode.childNodes[j];
				if (memberNode.nodeType != 1) continue;

				switch (memberNode.nodeName)
				{
					case 'name' : name = this._decodeString(memberNode); break;
					case 'value': value = this._decodeValue(memberNode); break;
				}
			}

			//if (name == '' || name == null || value == null) continue;
			if (name == '' || name == null) continue;
			struct[name] = value;
		}
		
		return struct;
	}

	/**
	 * Method: _decodeArray
	 *
	 *	Parses <array> elements
	 *
	 */
	this._decodeArray = function (node)
	{
		var i,j,childNode, valueNode;
		var value, arr = [];
		
		for (i=0; i<node.childNodes.length; i++)
		{
			childNode = node.childNodes[i];
			if (childNode.nodeType != 1) continue;

			for (j=0; j<childNode.childNodes.length; j++)
			{
				valueNode = childNode.childNodes[j];
				if (valueNode.nodeType != 1) continue;

				value = this._decodeValue(valueNode);

				if (value == null) continue;
				arr.push(value);
			}
		}
		
		return arr;
	}
	
	/**
	 * Method: _decodeNumber
	 *
	 *	Parses <i4>, <int> and <double> elements
	 *
	 */
	this._decodeNumber = function (node)
	{
		if (!node.firstChild) return null;

		var num = new Number(node.firstChild.nodeValue);
		return num.valueOf();
	}

	/**
	 * Method: _decodeString
	 *
	 *	Parses <string> elements
	 *
	 */
	this._decodeString = function (node)
	{
		var i, str = '';
		for (i=0; i<node.childNodes.length; i++)
		{
			str += new String(node.childNodes[i].nodeValue);
		}

		return str;
	}

	/**
	 * Method: _processParameter
	 *
	 *	Convert JS entities to XMLRPC entities
	 *
	 */
	this._processParameter = function (param)
	{
		var value = '';
		switch (typeof(param))
		{
			case 'object':
				var i;
				value += '<struct>';
				for (i in param)
				{
					value += '<member>\n<name>'+this._encodeXMLEntities(i)+'</name>\n';
					value += '<value>'+this._processParameter(param[i])+'</value>\n';
					value += '</member>\n';
				}
				value += '</struct>';
				break;
				
			case 'array':
				var i;
				value += '<array>\n<data>\n';
				for (i=0; i<param.length; i++)
				{
					value += '<value>'+this._processParameter(param[i])+'</value>\n';
				}
				value += '</data>\n</array>\n';
				break;
				
			case 'boolean':
				value += '<boolean>';
				
				if (param) value += '1';
				else value += '0';
				
				value += '</boolean>\n';
				break;
				
			case 'null':
				// FIXME: Is this right?
				value += '<boolean>0</boolean>\n';
				break;
				
			case 'number':
				// Integer
				if (Math.round(param) == param)
				{
					value += '<i4>'+param+'</i4>\n';
					break;
				}

				// Double
				value += '<double>'+param+'</double>';
				break;

			case 'string':
				value += '<string>'+this._encodeXMLEntities(param)+'</string>\n';
				break;
				
			case 'date':
				// FIXME: This must be implemented yet

			case 'function':
				// FIXME: Is this necessary?
		}

		return value;
	}



	
	}






