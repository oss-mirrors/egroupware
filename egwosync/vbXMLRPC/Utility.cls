VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
  Persistable = 0  'NotPersistable
  DataBindingBehavior = 0  'vbNone
  DataSourceBehavior  = 0  'vbNone
  MTSTransactionMode  = 0  'NotAnMTSObject
END
Attribute VB_Name = "XMLRPCUtility"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = False
Attribute VB_Exposed = True
Attribute VB_HelpID = 9000
Attribute VB_Description = "Contains uitlity functions. Contains a number of simple functions that decode enums and other numbers into meaningful strings."
'===============================================================================
'
'  Title             : Utility.cls
'  Program           : vbXMLRPC
'  Version           : 0.9
'  Copyright         : © EnAppSys Ltd
'  Date              : 14th Oct, 2002
'  Author            : Phil Hewitt
'  Contact Address   : 52 Byelands Street,
'                      Middlesbrough,
'                      Cleveland. TS4 2HP
'                      United Kingdom
'  Contact e-mail    : support@enappsys.com
'  Technical Reviewer:
'
'  Purpose           : XML-RPC Utility Class
'  Notes             : Contains a number of Enum definitions and
'                      some simple functions that decode these and
'                      other numbers into meaningful strings.
'
'===============================================================================
'
'   This library is free software; you can redistribute it and/or
'   modify it under the terms of the GNU Lesser General Public
'   License as published by the Free Software Foundation; either
'   version 2.1 of the License, or (at your option) any later version.
'
'   This library is distributed in the hope that it will be useful,
'   but WITHOUT ANY WARRANTY; without even the implied warranty of
'   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
'   Lesser General Public License for more details.
'
'   You should have received a copy of the GNU Lesser General Public
'   License along with this library; if not, write to the Free Software
'   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
'
'===============================================================================
'
'  Modification History
'
'  Version           :
'  Date              :
'  Author            :
'  Technical Reviewer:
'  Changes           :
'
'===============================================================================

Option Explicit

Private Const MODULETITLE = APPNAME & "#Utility.cls"

'---------------------------------------------------
'   Enum containing possible valid types
Public Enum XMLRPC_ValueTypes
    XMLRPC_INT_I4 = 1   ' <i4> or <int> four-byte signed integer e.g. -12
    XMLRPC_BOOLEAN = 2  ' <boolean> 0 (false) or 1 (true) e.g. 1
    XMLRPC_STRING = 3   ' <string> ASCII string e.g. hello world
    XMLRPC_DOUBLE = 4   ' <double> double-precision signed floating point number e.g. -12.214
    XMLRPC_DATETIME = 5 ' <dateTime.iso8601> date/time e.g. 19980717T14:08:55
    XMLRPC_BASE64 = 6   ' <base64> base64-encoded binary e.g. eW91IGNhbid0IHJlYWQgdGhpcyE=
    XMLRPC_STRUCT = 7   ' <struct> an XMLRPC struct structure
    XMLRPC_ARRAY = 8    ' <array> an XMLRPC array structure
    XMLRPC_NIL = 9      ' <nil> an XMLRPC v2 nil
End Enum

'---------------------------------------------------
'   Enum containing possible SSL Types
Public Enum XMLRPC_SSLTypes
    XMLRPC_SSL_NONE = 0 ' No SSL
    XMLRPC_SSL_STRICT = 1 ' Apply Strict SSL Rules
    XMLRPC_SSL_IGNORE_CERT_CN_INVALID_AND_DATE_INVALID = 2 ' Ignore Host Name Check and Validity Dates
    XMLRPC_SSL_IGNORE_CERT_CN_INVALID_ONLY = 3 ' Ignore Host Name Check Only
    XMLRPC_SSL_IGNORE_CERT_DATE_INVALID_ONLY = 4 ' Ignore Validity Dates Only
End Enum

'---------------------------------------------------
'   Enum containing possible error numbers
Private Const BASEERRORNUMBER = vbObjectError + 123456
Public Enum XMLRPC_Errors
    vbXMLRPC_ERROR_BASE64ERROR = BASEERRORNUMBER + 0
    vbXMLRPC_ERROR_UNKNOWNVALUETYPE = BASEERRORNUMBER + 1
    vbXMLRPC_ERROR_UNEXPECTEDVALUETYPE = BASEERRORNUMBER + 2
    vbXMLRPC_ERROR_VALUETYPENOTSET = BASEERRORNUMBER + 3
    vbXMLRPC_ERROR_VALUENOTSET = BASEERRORNUMBER + 4
    vbXMLRPC_ERROR_MALFORMEDXML = BASEERRORNUMBER + 5
    vbXMLRPC_ERROR_HOSTNAMENOTSET = BASEERRORNUMBER + 6
    vbXMLRPC_ERROR_HOSTPORTNOTSET = BASEERRORNUMBER + 7
    vbXMLRPC_ERROR_HOSTURINOTSET = BASEERRORNUMBER + 8
    vbXMLRPC_ERROR_METHODNAMENOTSET = BASEERRORNUMBER + 9
    vbXMLRPC_ERROR_USERAGENTNOTSET = BASEERRORNUMBER + 10
    vbXMLRPC_ERROR_HTTPCALLFAILED = BASEERRORNUMBER + 11
    vbXMLRPC_ERROR_MEMBERNOTFOUND = BASEERRORNUMBER + 12
End Enum

'================================================
'
'   PUBLIC GetErrorCode
'
'   Gets a verbose explanation of the passed
'   XML-RPC error code.
'
Public Function GetErrorCode(ByVal ErrorNumber As Long) As String
Attribute GetErrorCode.VB_Description = "Gets a verbose explanation of the passed XML-RPC error code."
Attribute GetErrorCode.VB_HelpID = 9050
    Select Case ErrorNumber
    Case vbXMLRPC_ERROR_BASE64ERROR
        GetErrorCode = "Base64 Error"
    Case vbXMLRPC_ERROR_UNKNOWNVALUETYPE
        GetErrorCode = "Unknown value type"
    Case vbXMLRPC_ERROR_UNEXPECTEDVALUETYPE
        GetErrorCode = "Unexpected value type"
    Case vbXMLRPC_ERROR_VALUETYPENOTSET
        GetErrorCode = "Value type not set"
    Case vbXMLRPC_ERROR_VALUENOTSET
        GetErrorCode = "Value not set"
    Case vbXMLRPC_ERROR_MALFORMEDXML
        GetErrorCode = "Malformed XML"
    Case vbXMLRPC_ERROR_HOSTNAMENOTSET
        GetErrorCode = "Host Name not set"
    Case vbXMLRPC_ERROR_HOSTPORTNOTSET
        GetErrorCode = "Host Port not set"
    Case vbXMLRPC_ERROR_HOSTURINOTSET
        GetErrorCode = "Host URI not set"
    Case vbXMLRPC_ERROR_METHODNAMENOTSET
        GetErrorCode = "Method Name not set"
    Case vbXMLRPC_ERROR_USERAGENTNOTSET
        GetErrorCode = "User Agent not set"
    Case vbXML_ERROR_HTTPERROR
        GetErrorCode = "WININET.dll error"
    Case vbXMLRPC_ERROR_MEMBERNOTFOUND
        GetErrorCode = "Member Not Found"
    Case Is > 0
        GetErrorCode = Error(ErrorNumber)
    Case Else
        GetErrorCode = "Unknown Error"
    End Select
End Function

'================================================
'
'   PUBLIC GetVariantType
'
'   Returns the name of the type
'   of the passed variant.
'
Public Function GetVariantType(ByVal ToCheck As Variant) As String
Attribute GetVariantType.VB_Description = "Returns the name of the type of the passed variant."
Attribute GetVariantType.VB_HelpID = 9150

    Select Case VarType(ToCheck)
    Case vbArray: GetVariantType = "Array"
    Case vbBoolean: GetVariantType = "Boolean"
    Case vbByte: GetVariantType = "Byte"
    Case vbCurrency: GetVariantType = "Currency"
    Case vbDataObject: GetVariantType = "Data Object"
    Case vbDate: GetVariantType = "Date"
    Case vbDecimal: GetVariantType = "Decimal"
    Case vbDouble: GetVariantType = "Double"
    Case vbEmpty: GetVariantType = "Empty"
    Case vbError: GetVariantType = "Error"
    Case vbInteger: GetVariantType = "Integer"
    Case vbLong: GetVariantType = "Long"
    Case vbNull: GetVariantType = "Null"
    Case vbObject: GetVariantType = "Object"
    Case vbSingle: GetVariantType = "Single"
    Case vbString: GetVariantType = "String"
    Case vbUserDefinedType: GetVariantType = "User DefinedType"
    Case vbVariant: GetVariantType = "Variant"
    Case Else: GetVariantType = "Unknown"
    End Select
    
End Function

'================================================
'
'   PUBLIC GetXMLRPCType
'
'   Returns a text representation of the
'   XMLRPC type from the passed XMLRPC Value Type.
'
Public Function GetXMLRPCType(ByVal XMLRPCType As XMLRPC_ValueTypes) As String
Attribute GetXMLRPCType.VB_Description = "Returns a text representation of the XMLRPC type from the passed XMLRPC Value Type."
Attribute GetXMLRPCType.VB_HelpID = 9200

    Select Case XMLRPCType
    Case XMLRPC_INT_I4: GetXMLRPCType = "I4 / Int"
    Case XMLRPC_BOOLEAN: GetXMLRPCType = "Boolean"
    Case XMLRPC_STRING: GetXMLRPCType = "String"
    Case XMLRPC_DOUBLE: GetXMLRPCType = "Double"
    Case XMLRPC_DATETIME: GetXMLRPCType = "DateTime"
    Case XMLRPC_BASE64: GetXMLRPCType = "Base64"
    Case XMLRPC_STRUCT: GetXMLRPCType = "Struct"
    Case XMLRPC_ARRAY: GetXMLRPCType = "Array"
    Case XMLRPC_NIL: GetXMLRPCType = "Nil"
    Case Else: GetXMLRPCType = "Unknown"
    End Select
    
End Function

'================================================
'
'   FRIEND XMLErrorName
'
'   Returns a name from the passed message
'   and position in the file.
'
'   Note it is a friend to allow functions
'   within the DLL to call it but not outside.
'
Friend Function XMLErrorName(ByVal Message As String, ByVal Position As Long) As String
    XMLErrorName = Message & " at character number " & Position
End Function

'================================================
'
'   FRIEND ErrorHelpContext
'
'   Returns an error context from the
'   passed XMLRPC error.
'
'   Note it is a friend to allow functions
'   within the DLL to call it but not outside.
'
Friend Function ErrorHelpContext(ByVal ErrorNumber As XMLRPC_Errors) As Long
    Select Case ErrorNumber
    Case vbXMLRPC_ERROR_BASE64ERROR, _
         vbXMLRPC_ERROR_UNKNOWNVALUETYPE, _
         vbXMLRPC_ERROR_UNEXPECTEDVALUETYPE, _
         vbXMLRPC_ERROR_VALUETYPENOTSET, _
         vbXMLRPC_ERROR_VALUENOTSET, _
         vbXMLRPC_ERROR_MALFORMEDXML, _
         vbXMLRPC_ERROR_HOSTNAMENOTSET, _
         vbXMLRPC_ERROR_HOSTPORTNOTSET, _
         vbXMLRPC_ERROR_HOSTURINOTSET, _
         vbXMLRPC_ERROR_METHODNAMENOTSET, _
         vbXMLRPC_ERROR_USERAGENTNOTSET, _
         vbXMLRPC_ERROR_HTTPCALLFAILED, _
         vbXMLRPC_ERROR_MEMBERNOTFOUND
        ErrorHelpContext = 1100 + (ErrorNumber - BASEERRORNUMBER)
    Case Else
        ErrorHelpContext = 1200
    End Select

End Function

'================================================
'
'   PUBLIC GetHTTPError
'
'   Returns the HTTP Error text from
'   the passed Error Number.
'
Public Function GetHTTPError(ByVal ErrorNumber As Integer) As String
Attribute GetHTTPError.VB_Description = "Returns the HTTP Error text from the passed Error Number."
Attribute GetHTTPError.VB_HelpID = 9100

    Select Case ErrorNumber
    Case HTTP_STATUS_CONTINUE: GetHTTPError = "CONTINUE - The request can be continued."
    Case HTTP_STATUS_SWITCH_PROTOCOLS: GetHTTPError = "SWITCH PROTOCOLS - The server has switched protocols in an upgrade header."
    Case HTTP_STATUS_OK: GetHTTPError = "OK - The request completed successfully."
    Case HTTP_STATUS_CREATED: GetHTTPError = "CREATED - The request has been fulfilled and resulted in the creation of a new resource."
    Case HTTP_STATUS_ACCEPTED: GetHTTPError = "ACCEPTED - The request has been accepted for processing, but the processing has not been completed."
    Case HTTP_STATUS_PARTIAL: GetHTTPError = "PARTIAL - The returned meta information in the entity-header is not the definitive set available from the origin server."
    Case HTTP_STATUS_NO_CONTENT: GetHTTPError = "NO CONTENT - The server has fulfilled the request, but there is no new information to send back."
    Case HTTP_STATUS_RESET_CONTENT: GetHTTPError = "RESET CONTENT - The request has been completed, and the client program should reset the document view that caused the request to be sent to allow the user to easily initiate another input action."
    Case HTTP_STATUS_PARTIAL_CONTENT: GetHTTPError = "PARTIAL CONTENT - The server has fulfilled the partial GET request for the resource."
    Case HTTP_STATUS_AMBIGUOUS: GetHTTPError = "AMBIGUOUS - The server couldn't decide what to return."
    Case HTTP_STATUS_MOVED: GetHTTPError = "MOVED - The requested resource has been assigned to a new permanent URI (Uniform Resource Identifier), and any future references to this resource should be done using one of the returned URIs."
    Case HTTP_STATUS_REDIRECT: GetHTTPError = "REDIRECT - The requested resource resides temporarily under a different URI (Uniform Resource Identifier)."
    Case HTTP_STATUS_REDIRECT_METHOD: GetHTTPError = "REDIRECT METHOD - The response to the request can be found under a different URI (Uniform Resource Identifier) and should be retrieved using a GET HTTP verb on that resource."
    Case HTTP_STATUS_NOT_MODIFIED: GetHTTPError = "NOT MODIFIED - The requested resource has not been modified."
    Case HTTP_STATUS_USE_PROXY: GetHTTPError = "USE PROXY - The requested resource must be accessed through the proxy given by the location field."
    Case HTTP_STATUS_REDIRECT_KEEP_VERB: GetHTTPError = "REDIRECT KEEP VERB - The redirected request keeps the same HTTP verb. HTTP/1.1 behavior."
    Case HTTP_STATUS_BAD_REQUEST: GetHTTPError = "BAD REQUEST - The request could not be processed by the server due to invalid syntax."
    Case HTTP_STATUS_DENIED: GetHTTPError = "DENIED - The requested resource requires user authentication."
    Case HTTP_STATUS_PAYMENT_REQ: GetHTTPError = "PAYMENT REQ - Not currently implemented in the HTTP protocol."
    Case HTTP_STATUS_FORBIDDEN: GetHTTPError = "FORBIDDEN - The server understood the request, but is refusing to fulfill it."
    Case HTTP_STATUS_NOT_FOUND: GetHTTPError = "NOT FOUND - The server has not found anything matching the requested URI (Uniform Resource Identifier)."
    Case HTTP_STATUS_BAD_METHOD: GetHTTPError = "BAD METHOD - The HTTP verb used is not allowed."
    Case HTTP_STATUS_NONE_ACCEPTABLE: GetHTTPError = "NONE ACCEPTABLE - No responses acceptable to the client were found."
    Case HTTP_STATUS_PROXY_AUTH_REQ: GetHTTPError = "PROXY AUTH REQ - Proxy authentication required."
    Case HTTP_STATUS_REQUEST_TIMEOUT: GetHTTPError = "REQUEST TIMEOUT - The server timed out waiting for the request."
    Case HTTP_STATUS_CONFLICT: GetHTTPError = "CONFLICT - The request could not be completed due to a conflict with the current state of the resource. The user should resubmit with more information."
    Case HTTP_STATUS_GONE:   GetHTTPError = "GONE - The requested resource is no longer available at the server, and no forwarding address is known."
    Case HTTP_STATUS_LENGTH_REQUIRED: GetHTTPError = "LENGTH REQUIRED - The server refuses to accept the request without a defined content length."
    Case HTTP_STATUS_PRECOND_FAILED: GetHTTPError = "PRECOND FAILED - The precondition given in one or more of the request header fields evaluated to false when it was tested on the server."
    Case HTTP_STATUS_REQUEST_TOO_LARGE: GetHTTPError = "REQUEST TOO LARGE - The server is refusing to process a request because the request entity is larger than the server is willing or able to process."
    Case HTTP_STATUS_URI_TOO_LONG: GetHTTPError = "URI TOO LONG - The server is refusing to service the request because the request URI (Uniform Resource Identifier) is longer than the server is willing to interpret."
    Case HTTP_STATUS_UNSUPPORTED_MEDIA: GetHTTPError = "UNSUPPORTED MEDIA - The server is refusing to service the request because the entity of the request is in a format not supported by the requested resource for the requested method."
    Case HTTP_STATUS_RETRY_WITH: GetHTTPError = "RETRY WITH - The request should be retried after doing the appropriate action."
    Case HTTP_STATUS_SERVER_ERROR: GetHTTPError = "SERVER ERROR - The server encountered an unexpected condition that prevented it from fulfilling the request."
    Case HTTP_STATUS_NOT_SUPPORTED: GetHTTPError = "NOT SUPPORTED - The server does not support the functionality required to fulfill the request."
    Case HTTP_STATUS_BAD_GATEWAY: GetHTTPError = "BAD GATEWAY - The server, while acting as a gateway or proxy, received an invalid response from the upstream server it accessed in attempting to fulfill the request."
    Case HTTP_STATUS_SERVICE_UNAVAIL: GetHTTPError = "SERVICE UNAVAIL - The service is temporarily overloaded."
    Case HTTP_STATUS_GATEWAY_TIMEOUT: GetHTTPError = "GATEWAY TIMEOUT - The request was timed out waiting for a gateway."
    Case HTTP_STATUS_VERSION_NOT_SUP: GetHTTPError = "VERSION NOT SUP - The server does not support, or refuses to support, the HTTP protocol version that was used in the request message."
    Case Else: GetHTTPError = "Unknown"
    End Select
    
End Function

