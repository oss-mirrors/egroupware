<?php
/*
	File: xajaxCall.inc.php

	Contains the xajaxCall class

	Title: xajaxCall class

	Please see <copyright.inc.php> for a detailed description, copyright
	and license information.
*/

/*
	@package xajax
	@version $Id: xajaxCall.inc.php 362 2007-05-29 15:32:24Z calltoconstruct $
	@copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
	@copyright Copyright (c) 2008-2009 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
	@license http://www.xajaxproject.org/bsd_license.txt BSD License
*/

/*
	Class: xajaxCall
	
	Create a piece of javascript code that will invoke the <xajax.call>
	function.
	
	This class is deprecated and will be removed in future versions; please use
	<xajaxRequest> instead.
*/
class xajaxCall {

	/**#@+
	 * @access protected
	 */

	/*
		String: sFunction
		
		Required:  The name of the xajax enabled function to call
	*/
	var $sFunction;
	
	/*
		String: sReturnValue
		
		Required:  The value to return once the <xajax.call> has
		returned.  (for asynchronous calls, this is immediate)
	*/
	var $sReturnValue;
	
	/*
		Array: aParameters
		
		The associative array that will be used to store the parameters for this
		call.
		- key: The textual representation of the parameter.
		- value: A boolean value indicating whether or not to use quotes around
			this parameter.
	*/
	var $aParameters;
	
	/*
		String: sMode
		
		The mode to use for the call
		- 'synchronous'
		- 'asynchronous'
	*/
	var $sMode;
	
	/*
		String: sRequestType
		
		The request type that will be used for the call
		- 'GET'
		- 'POST'
	*/
	var $sRequestType;
	
	/*
		String: sResponseProcessor
		
		The name of the javascript function that will be invoked
		to handle the response.
	*/
	var $sResponseProcessor;
	
	/*
		String: sRequestURI
		
		The URI for where this request will be sent.
	*/
	var $sRequestURI;
	
	/*
		String: sContentType
		
		The content type to use for the request.
	*/
	var $sContentType;
	
	/*
		Constructor: xajaxCall
		
		Initializes the xajaxCall object.
		
		Parameters:
		
		sFunction - (string):  The name of the xajax enabled function
			that will be invoked when this javascript code is executed
			on the browser.  This function name should match a PHP 
			function from your script.
	*/
	function xajaxCall($sFunction = '') {
		$this->sFunction = $sFunction;
		$this->aParameters = array();
		$this->sMode = '';
		$this->sRequestType = '';
		$this->sResponseProcessor = '';
		$this->sRequestURI = '';
		$this->sContentType = '';
	}
	
	/*
		Function: setFunction
		
		Override the function name set in the constructor.
		
		Parameters:

		sFunction - (string):  The name of the xajax enabled function
			that will be invoked when this javascript code is executed
			on the browser.  This function name should match a PHP 
			function from your script.
		
		Returns:
		
		object : The <xajaxCall> object.
	*/
	function setFunction($sFunction) {
		$this->sFunction = $sFunction;
		return $this;
	}
	
	/*
		Function: clearParameters
		
		Clear the list of parameters being accumulated for this
		call.
		
		Returns:
		
		object : The <xajaxCall> object.
	*/
	function clearParameters() {
		$this->aParameters = array();
	}
	
	/*
		Function: addParameter
		
		Adds a parameter to the list that will be specified for the
		request generated by this <xajaxCall> object.
		
		Parameters:
		
		sParameter - (string):  The parameter value or name.
		bUseQuotes - (boolean):  Whether or not to put quotes around this value.
		
		If you specify the name of a javascript variable, or provide a javascript
		function call as a parameter, do not use quotes around the value.
		
		Returns:
		
		object : The <xajaxCall> object.
	*/
	function addParameter($sParameter, $bUseQuotes = true) {
		$this->aParameters[] = array($sParameter, $bUseQuotes);
		return $this;
	}
	
	/*
		Function: addFormValuesParameter
		
		Add a parameter value that is the result of calling <xajax.getFormValues>
		for the specified form.
		
		Parameters:
		
		sFormID - (string):  The id of the form for which you wish to return
			the input values.
			
		Returns:
		
		object : The <xajaxCall> object.
	*/
	function addFormValuesParameter($sFormID) {
		$this->aParameters[] = array('xajax.getFormValues("'.$sFormID.'")');
		return $this;
	}
	
	/*
		Function: setMode
		
		Sets the mode that will be specified for this <xajax.call>

		Parameters:

			$sMode - (string): The mode to be set.
				- 'synchronous'
				- 'asynchronous'
		
		Returns:
		
		object : The <xajaxCall> object.
	*/
	function setMode($sMode) {
		$this->sMode = $sMode;
		return $this;
	}
	
	/*
		Function: setRequestType
		
		Sets the request type which will be specified for the
		generated <xajax.call>.
		
		Parameters:
		
		- 'GET'
		- 'POST'
		
		Returns:
		
		object : The <xajaxCall> object.
	*/
	function setRequestType($sRequestType) {
		$this->sRequestType = $sRequestType;
		return $this;
	}
	
	/*
		Function: setResponseProcessor
		
		Sets the name of the javascript function that will be used
		to process this response.  This is an advanced function, use
		with caution.
		
		Parameters:
		
		Returns:
		
		object : The <xajaxCall> object.
	*/
	function setResponseProcessor($sResponseProcessor) {
		$this->sResponseProcessor = $sResponseProcessor;
		return $this;
	}
	
	/*
		Function: setRequestURI
		
		Override the default URI with the specified one.
		
		Parameters:
		
		sRequestURI - (string):  The URI that the generated request will be sent
			to.
		
		Returns:
		
		object : The <xajaxCall> object.
	*/
	function setRequestURI($sRequestURI) {
		$this->sRequestURI = $sRequestURI;
		return $this;
	}
	
	/*
		Function: setContentType
		
		Sets the content type that will be used by the generated request.
		
		Parameters:
		
		Returns:
		
		object : The <xajaxCall> object.
	*/
	function setContentType($sContentType) {
		$this->sContentType = $sContentType;
	}
	
	/*
		Function: setReturnValue
		
		Sets the value that will be returned after the generated call.
		Set to an empty string if no return value is desired.
		
		Parameters:
		
		Returns:
		
		object : The <xajaxCall> object.
	*/
	function setReturnValue($sReturnValue) {
		$this->sReturnValue = $sReturnValue;
	}
	
	/*
		Function: generate
		
		Construct a <xajax.call> statement in javascript that can be used
		to make a xajax request with the parameters and settings previously
		configured for this <xajaxCall> object.
		
		The output from this function can be used as an event handler in your
		javascript code.
		
		Returns:
		
		string - The javascript statement that will invoked the <xajax.call>
			function on the browser, causing a xajax request to be sent to
			the server.
	*/
	function generate() {
		$output = 'xajax.call("';
		$output .= $this->sFunction;
		$output .= '", {';
		$separator = '';
		if (0 < count($this->aParameters)) {
			$output .= 'parameters: [';
			foreach ($this->aParameters as $aParameter) {
				$output .= $separator;
				$bUseQuotes = $aParameter[1];
				if ($bUseQuotes)
					$output .= '"';
				$output .= $aParameter[0];
				if ($bUseQuotes)
					$output .= '"';
				$separator = ',';
			}
			$output .= ']';
		}
		if (0 < strlen($this->sMode)) {
			$output .= $separator;
			$output .= 'mode:"';
			$output .= $this->sMode;
			$output .= '"';
			$separator = ',';
		}
		if (0 < strlen($this->sRequestType)) {
			$output .= $separator;
			$output .= 'requestType:"';
			$output .= $this->sRequestType;
			$output .= '"';
			$separator = ',';
		}
		if (0 < strlen($this->sResponseProcessor)) {
			$output .= $separator;
			$output .= 'responseProcessor:';
			$output .= $this->sResponseProcessor;
			$separator = ',';
		}
		if (0 < strlen($this->sRequestURI)) {
			$output .= $separator;
			$output .= 'requestURI:"';
			$output .= $this->sRequestURI;
			$output .= '"';
			$separator = ',';
		}
		if (0 < strlen($this->sContentType)) {
			$output .= $separator;
			$output .= 'contentType:"';
			$output .= $this->sContentType;
			$output .= '"';
			$separator = ',';
		}
		$output .= '}); ';
		if (0 < strlen($this->sReturnValue)) {
			$output .= 'return ';
			$output .= $this->sReturnValue;
		} else {
			$output .= 'return false;';
		}
		
		return $output;
	}
}
