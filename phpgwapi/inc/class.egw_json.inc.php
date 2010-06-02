<?php
/**
 * eGroupWare API: JSON - Contains functions and classes for doing JSON requests.
 *
 * @link http://www.egroupware.org
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package api$request['menuaction'], $parameters
 * @subpackage ajax
 * @author Andreas Stoeckel <as@stylite.de>
 * @version $Id$
 */

/**
 * Class handling JSON requests to the server 
 */
class egw_json_request
{
	/**
	 * Parses the raw input data supplied with the input_data parameter and calls the menuaction
	 * passing all parameters supplied in the request to it.
	 * 
	 * @param string menuaction to call
	 * @param string $input_data is the RAW input data as it was received from the client
	 * @returns NULL if parsing the request failed, or the result of the callback function if the request has been successfully decoded.
	 */
	public function parseRequest($menuaction, $input_data)
	{
		if (empty($input_data))
		{
			$this->handleRequest($menuaction, array());
 		}
		else
		{
			//Decode the JSON input data into associative arrays		
			if (($json = json_decode(stripslashes($input_data[0]), true)))
			{
				//Get the request array
				if (isset($json['request']))
				{
					$request = $json['request'];
				
					//Check whether any parameters were supplied along with the request
					$parameters = array();
					if (isset($request['parameters']))
						$parameters = array_stripslashes($request['parameters']);

					//Call the supplied callback function along with the menuaction and the passed parameters
					$this->handleRequest($menuaction, $parameters);
				}
			}
		}	

		return NULL;
	}

	/**
	 * Request handler
	 * 
	 * @param string $menuaction
	 * @param array $parameters
	 */
	public function handleRequest($menuaction, array $parameters)
	{
		if (strpos($menuaction,'::') !== false && strpos($menuaction,'.') === false)	// static method name app_something::method
		{
			@list($className,$functionName,$handler) = explode('::',$menuaction);
			list($appName) = explode('_',$className);
		}
		else
		{
			@list($appName, $className, $functionName, $handler) = explode('.',$menuaction);
		}
		error_log("xajax.php: appName=$appName, className=$className, functionName=$functionName, handler=$handler");

		switch($handler)
		{
/*			case '/etemplate/process_exec':
				$menuaction = $appName.'.'.$className.'.'.$functionName;
				$appName = $className = 'etemplate';
				$functionName = 'process_exec';
				$menuaction = 'etemplate.etemplate.process_exec';

				$argList = array(
					$argList[0]['etemplate_exec_id'],
					$argList[0]['submit_button'],
					$argList[0],
					'xajaxResponse',
				);
				//error_log("xajax_doXMLHTTP() /etemplate/process_exec handler: arg0='$menuaction', menuaction='$_GET[menuaction]'");
				break;*/
			case 'etemplate':	// eg. ajax code in an eTemplate widget
				$menuaction = ($appName = 'etemplate').'.'.$className.'.'.$functionName;
				break;
			case 'template':
				$menuaction = $appName.'.'.$className.'.'.$functionName;
				list($template) = explode('_', $className);
				break;
		}

		if(substr($className,0,4) != 'ajax' && substr($className,-4) != 'ajax' &&
			$menuaction != 'etemplate.etemplate.process_exec' && substr($functionName,0,4) != 'ajax' ||
			!preg_match('/^[A-Za-z0-9_]+(\.[A-Za-z0-9_]+\.|::)[A-Za-z0-9_]+$/',$menuaction))
		{
			// stopped for security reasons
			error_log($_SERVER['PHP_SELF']. ' stopped for security reason. '.$menuaction.' is not valid. class- or function-name must start with ajax!!!');
			// send message also to the user
			throw new Exception($_SERVER['PHP_SELF']. ' stopped for security reason. '.$menuaction.' is not valid. class- or function-name must start with ajax!!!');
			exit;
		}

		if (isset($template))
		{
			require_once(EGW_SERVER_ROOT.'/phpgwapi/templates/'.$template.'/class.'.$className.'.inc.php');
			$ajaxClass = new $className;
		}
		else
		{
			$ajaxClass = CreateObject($appName.'.'.$className);		
		}
		
		$parameters = translation::convert($parameters, 'utf-8');

		call_user_func_array(array($ajaxClass, $functionName), $parameters);
	}
}

/**
 * Class used to send ajax responses
 */
class egw_json_response
{
	/**
	 * A response can only contain one generic data part. 
	 * This variable is used to store, whether a data part had already been added to the response.
	 *  
	 * @var boolean
	 */
	private $hasData = false;

	/**
	 * Holds the actual response data which is then encoded to JSON 
	 * once the "getJSON" function is called
	 * 
	 * @var array
	 */
	protected $responseArray = array();

	/**
	 * Holding instance of class for singelton egw_json_response::get()
	 * 
	 * @var egw_json_response
	 */
	private static $response = null;

	/**
	 * Singelton for class
	 * 
	 * @return egw_json_response
	 */
	public static function get()
	{
		if (!isset(self::$response))
		{
			self::$response = new egw_json_response();
		}
		return self::$response;
	}

	/**
	 * Private function used to send the HTTP header of the JSON response
	 */
	private function sendHeader()
	{
		//Send the character encoding header
		header('content-type: application/json; charset='.translation::charset());
	}

	/**
	 * Private function which is used to send the result via HTTP
	 */
	public function sendResult()
	{
		$this->sendHeader();
		echo $this->getJSON();
	}
	
	/**
	 * xAjax compatibility function
	 */
	public function printOutput()
	{
		// do nothing, as output is triggered by destructor
	}

	/**
	 * Adds any type of data to the response array
	 */
	protected function addGeneric($key, $data)
	{
		$this->responseArray[] = array(
			'type' => $key,
			'data' => $data,
		);
	}

	/**
	 * Adds a "data" response to the json response. 
	 * 
	 * This function may only be called once for a single JSON response object.
	 * 
	 * @param object|array|string $data can be of any data type and will be added JSON Encoded to your response.
	 */
	public function data($data)
	{
		/* Only allow adding the data response once */
		if (!$this->hasData)
		{
			$this->addGeneric('data', $data);
			$this->hasData = true;
		}
		else
		{
			throw new Exception("Adding more than one data response to a JSON response is not allowed.");
		}
	}

	/**
	 * Adds an "alert" to the response which can be handeled on the client side. 
	 * 
	 * The default implementation simply displays the text supplied here with the JavaScript function "alert".
	 * 
	 * @param string $message contains the actual message being sent to the client.
	 * @param string $details (optional) can be used to inform the user on the client side about additional details about the error. This might be information how the error can be resolved/why it was raised or simply some debug data.
	 */
	public function alert($message, $details = '')
	{
		if (is_string($message) && is_string($details))
		{
			$this->addGeneric('alert', array(
				"message" => $message,
				"details" => $details));
		}
		else
		{
			throw new Exception("Invalid parameters supplied.");
		}
	}

	/**
	 * Allows to add a generic java script to the response which will be executed upon the request gets received.
	 * 
	 * @deprecated
	 * @param string $script the script code which should be executed upon receiving
	 */	
	public function script($script)
	{
		if (is_string($script))
		{
			$this->addGeneric('script', $script);
		}
		else
		{
			throw new Exception("Invalid parameters supplied.");
		}
	}

	/**
	 * Adds an html assign to the response, which is excecuted upon the request is received.
	 *  
	 * @deprecated just for compatibility with XAJAX
	 * @param string $id id of dom element to modify
	 * @param string $key attribute name of dom element which should be modified
	 * @param string $value the value which should be assigned to the given attribute
	 */
	public function assign($id, $key, $value)
	{
		if (is_string($id) && is_string($key) && (is_string($value) || is_numeric($value)))
		{
			$this->addGeneric('assign', array(
				'id' => $id,
				'key' => $key,
				'value' => $value,
			));
		}
		else
		{
			throw new Exception("Invalid parameters supplied");
		}
	}
	
	/**
	 * Redirect to given url
	 * 
	 * @param string $url
	 */
	public function redirect($url)
	{
		//self::script("location.href = '$url';");
		$this->addGeneric('redirect', $url);
	}

	/**
	 * Returns the actual JSON code generated by calling the above "add" function.
	 * 
	 * @return string
	 */	
	public function getJSON()
	{
		/* Wrap the result array into a parent "response" Object */
		$res = array('response' => $this->responseArray);
		
		return json_encode($res, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
	}

	/**
	 * Destructor
	 */
	public function __destruct()
	{
		$this->sendResult();
	}
}
