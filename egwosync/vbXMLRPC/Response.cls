VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
  Persistable = 0  'NotPersistable
  DataBindingBehavior = 0  'vbNone
  DataSourceBehavior  = 0  'vbNone
  MTSTransactionMode  = 0  'NotAnMTSObject
END
Attribute VB_Name = "XMLRPCResponse"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = False
Attribute VB_Exposed = True
Attribute VB_HelpID = 7000
Attribute VB_Description = "Models an XML-RPC response. The response structure from the call to the server. It handles all the possible problems and builds the response tree with data in."
'===============================================================================
'
'  Title             : Response.cls
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
'  Purpose           : XML-RPC Response Structure
'  Notes             : This is the response structure from
'                      the call to the server. It handles
'                      all the possible problems and builds
'                      the response tree with data in.
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
'
'   The following is extracted from http://www.xmlrpc.com/spec
'   and http://plant.blogger.com/api/index.html
'
'   Response example 1 - Simple
'   ===========================
'
'   Here's an example of an XML-RPC response:
'
'   HTTP/1.1 200 OK
'   Connection: Close
'   Content-Length: 158
'   Content-Type: text/xml
'   Date: Fri, 17 Jul 1998 19:55:08 GMT
'   Server: UserLand Frontier/5.1.2-WinNT
'
'   <?xml version="1.0"?>
'   <methodResponse>
'       <params>
'           <param>
'               <value><string>South Dakota</string></value>
'           </param>
'       </params>
'   </methodResponse>
'
'   Response example 2 - Struct
'   ===========================
'
'   HTTP/1.1 200 OK
'   Connection: Close
'   Content-Length: 125
'   Content-Type: text/xml
'   Date: Mon, 6 Aug 20001 19:55:08 GMT
'   Server: Java.Net Wa - Wa / Linux
'
'   <?xml version="1.0" encoding="ISO-8859-1"?>
'   <methodResponse>
'       <params>
'           <param>
'               <value>
'                   <struct>
'                       <member>
'                           <name>nickname</name>
'                           <value>Ev.</value>
'                       </member>
'                       <member>
'                           <name>userid</name>
'                           <value>1</value>
'                       </member>
'                       <member>
'                           <name>url</name>
'                           <value>http://www.evhead.com</value>
'                       </member>
'                       <member>
'                           <name>email</name>
'                           <value>ev@pyra.com</value>
'                       </member>
'                       <member>
'                           <name>lastname</name>
'                           <value>Williams</value>
'                       </member>
'                       <member>
'                           <name>firstname</name>
'                           <value>Evan</value>
'                       </member>
'                   </struct>
'               </value>
'           </param>
'       </params>
'   </methodResponse>
'
'   Response example 3 - Array
'   ==========================
'
'   <array>
'       <data>
'           <value><i4>12</i4></value>
'           <value><string>Egypt</string></value>
'           <value><boolean>0</boolean></value>
'           <value><i4>-31</i4></value>
'       </data>
'   </array>
'
'   Response example 4 - Fault
'   ==========================
'
'   HTTP/1.1 200 OK
'   Connection: Close
'   Content-Length: 426
'   Content-Type: text/xml
'   Date: Fri, 17 Jul 1998 19:55:02 GMT
'   Server: UserLand Frontier/5.1.2-WinNT
'
'   <?xml version="1.0"?>
'   <methodResponse>
'       <fault>
'           <value>
'               <struct>
'                   <member>
'                       <name>faultCode</name>
'                       <value><int>4</int></value>
'                   </member>
'                   <member>
'                       <name>faultString</name>
'                       <value><string>Too many parameters.</string></value>
'                   </member>
'               </struct>
'           </value>
'       </fault>
'   </methodResponse>
'
'===============================================================================

Option Explicit

Private Const MODULETITLE = APPNAME & "#Response.cls"

'---------------------------------------------------
'   Enum containing possible response statuses
Public Enum XMLRPC_ResponseStatus
    XMLRPC_NOTINITIALISED = 0
    XMLRPC_HTTPERROR = 1
    XMLRPC_FAULTRETURNED = 2
    XMLRPC_PARAMSRETURNED = 3
    XMLRPC_XMLPARSERERROR = 4
End Enum

'---------------------------------------------------
'   Module Level copy of HTTP Headers
Private mstrHTTPHeaders As String

'---------------------------------------------------
'   Module Level copy of XML Response
Private mstrXMLResponse As String

'---------------------------------------------------
'   XML Response converted into DOM Tree
Private minsParser As New XMLDOMParser

'---------------------------------------------------
'   Module level copy of Params
'   (Empty if not initialised or a fault)
Private minsParams As New XMLRPCParams

'---------------------------------------------------
'   Module level copy of Fault
'   (Empty if no Fault)
Private minsFault As New XMLRPCFault

'---------------------------------------------------
'   Status of response
Private menmStatus As XMLRPC_ResponseStatus

'---------------------------------------------------
'   Module level copy of any Parsing Error
Private mstrXMLParseError As String

'---------------------------------------------------
'   Module level copy of any HTTP Status Code
Private mintHTTPStatusCode As Integer

'================================================
'   P R O P E R T I E S
'================================================

'================================================
'
'   PUBLIC XMLResponse
'
'   The XML response from the server.
'   Should be read only to the user by having a
'   FRIEND setting on the Let method but having
'   it public allows testing of the code by
'   allowing the insertion of external XML.
'
Public Property Get XMLResponse() As String
Attribute XMLResponse.VB_Description = "The XML response from the server. Can be set by the user to allow testing of error responses."
Attribute XMLResponse.VB_HelpID = 7350
    XMLResponse = mstrXMLResponse
End Property
Public Property Let XMLResponse(ByVal XMLResponse As String)
    Const MethodName = MODULETITLE & "#XMLResponse (Property Let)"
    
    mstrXMLResponse = XMLResponse
    
    On Error GoTo XMLResponse_ErrHandler
    minsParser.ParseString XMLResponse, True
    On Error GoTo 0
    
    Set minsParams = New XMLRPCParams
    Set minsFault = New XMLRPCFault
    mstrXMLParseError = ""
    
    DecodeXML
    
XMLResponse_Exit:
    
    On Error GoTo 0
    Exit Property
    
XMLResponse_ErrHandler:

    XMLParseError = Err.Description
    Resume XMLResponse_Exit

End Property

'================================================
'
'   PUBLIC HTTPHeaders
'
'   Read-Only because the Let is FRIEND
'
'   The Headers returned from the server.
'
Public Property Get HTTPHeaders() As String
Attribute HTTPHeaders.VB_Description = "The HTTP Headers returned from the server. Read-Only."
Attribute HTTPHeaders.VB_HelpID = 7100
    HTTPHeaders = mstrHTTPHeaders
End Property
Friend Property Let HTTPHeaders(ByVal HTTPHeaders As String)
    mstrHTTPHeaders = HTTPHeaders
End Property

'================================================
'
'   PUBLIC Status
'
'   Read-Only.
'
'   The status of the response from the server.
'   Use this property to decide which property
'   to access next.
'
Public Property Get Status() As XMLRPC_ResponseStatus
Attribute Status.VB_Description = "The status of the response from the server. Use this property to decide which property to access next. Read-Only."
Attribute Status.VB_HelpID = 7250
    Status = menmStatus
End Property

'================================================
'
'   PUBLIC Params
'
'   Read-Only.
'
'   If the Status is XMLRPC_PARAMSRETURNED this
'   will return the converted XML as a Params
'   structure. Obviously data in here means the
'   call to the server was successful and the
'   data returned parsed OK.
'
Public Property Get Params() As XMLRPCParams
Attribute Params.VB_Description = "If the Status is XMLRPC_PARAMSRETURNED this\r\nwill return the converted XML as a Params structure. Obviously data in here means the call to the server was successful and the data returned parsed OK. Read-Only."
Attribute Params.VB_HelpID = 7200
    Set Params = minsParams
End Property

'================================================
'
'   PUBLIC Fault
'
'   Read-Only.
'
'   If the Status is XMLRPC_FAULTRETURNED this
'   will return the converted XML as a fault
'   structure. Obviously data in here means the
'   call to the server returned data indicating
'   there was a problem executing the remote
'   proceedure call.
'
Public Property Get Fault() As XMLRPCFault
Attribute Fault.VB_Description = "If the Status is XMLRPC_FAULTRETURNED this will return the converted XML as a fault structure. Obviously data in here means the call to the server returned data indicating there was a problem executing the remote proceedure call. Read-Only."
Attribute Fault.VB_HelpID = 7050
    Set Fault = minsFault
End Property

'================================================
'
'   PUBLIC XMLParseError
'
'   Read-Only.
'
'   If the Status is XMLRPC_XMLPARSERERROR this
'   will return the error encountered when parsing
'   the XML. Obviously data in here means the
'   call to the server returned data in an
'   unexpected format. Examine the XMLResponse
'   property to find out the issue.
'
Public Property Get XMLParseError() As String
Attribute XMLParseError.VB_Description = "If the Status is XMLRPC_XMLPARSERERROR this will return the error encountered when parsing the XML. Obviously data in here means the call to the server returned data in an unexpected format. Examine the XMLReponse property to find out the issue. Read-Onl"
Attribute XMLParseError.VB_HelpID = 7300
    XMLParseError = mstrXMLParseError
End Property
Private Property Let XMLParseError(ByVal XMLParseError As String)
    mstrXMLParseError = XMLParseError
    menmStatus = XMLRPC_XMLPARSERERROR
End Property

'================================================
'
'   PUBLIC HTTPStatusCode
'
'   Read-Only.
'
'   Contains the HTTP resonse from the server.
'   If it is not HTTP_STATUS_OK then the status
'   is set to XMLRPC_HTTPERROR. Examine the
'   HTTPHeaders property to for more information.
'
Public Property Get HTTPStatusCode() As Integer
Attribute HTTPStatusCode.VB_Description = "Contains the HTTP resonse from the server. If it is not HTTP_STATUS_OK then the status is set to XMLRPC_HTTPERROR. Examine the HTTPHeaders property to for more information. Read-Only."
Attribute HTTPStatusCode.VB_HelpID = 7150
    HTTPStatusCode = mintHTTPStatusCode
End Property
Friend Property Let HTTPStatusCode(ByVal HTTPStatusCode As Integer)
    mintHTTPStatusCode = HTTPStatusCode
    If mintHTTPStatusCode <> HTTP_STATUS_OK Then menmStatus = XMLRPC_HTTPERROR
End Property

'================================================
'   M E T H O D S
'================================================

'================================================
'
'   PRIVATE Class_Initialize
'
'   Standard VB call when class is initialised.
'   Sets the status to XMLRPC_NOTINITIALISED.
'
'   Also sets up the DTD validation on the parser
'
Private Sub Class_Initialize()
    menmStatus = XMLRPC_NOTINITIALISED
    
    minsParser.DTD.ParseString "<!ELEMENT i4 (#PCDATA)>" & vbCrLf & _
                               "<!ELEMENT int (#PCDATA)>" & vbCrLf & _
                               "<!ELEMENT boolean (#PCDATA)>" & vbCrLf & _
                               "<!ELEMENT string (#PCDATA)>" & vbCrLf & _
                               "<!ELEMENT double (#PCDATA)>" & vbCrLf & _
                               "<!ELEMENT dateTime.iso8601 (#PCDATA)>" & vbCrLf & _
                               "<!ELEMENT base64 (#PCDATA)>" & vbCrLf & _
                               "<!ELEMENT nil EMPTY>" & vbCrLf & _
                               "<!ELEMENT data (value*)>" & vbCrLf & _
                               "<!ELEMENT array (data)>" & vbCrLf & _
                               "<!ELEMENT name (#PCDATA)>" & vbCrLf & _
                               "<!ELEMENT member (name, value)>" & vbCrLf & _
                               "<!ELEMENT struct (member*)>" & vbCrLf & _
                               "<!ELEMENT value (#PCDATA | i4 | int | boolean | string | dateTime.iso8601 | double | base64 | struct | array | nil)>" & vbCrLf & _
                               "<!ELEMENT param (value)>" & vbCrLf & _
                               "<!ELEMENT params (param*)>" & vbCrLf & _
                               "<!ELEMENT methodName (#PCDATA)>" & vbCrLf & _
                               "<!ELEMENT methodCall (methodName, params?)>" & vbCrLf & _
                               "<!ELEMENT fault (value)>" & vbCrLf & _
                               "<!ELEMENT methodResponse (params | fault)>"
    minsParser.RootElement = "methodResponse"
    minsParser.ValidationRule = XML_VALIDATE_WITH_EXTERNAL_DTD
    
End Sub

'================================================
'
'   PRIVATE DecodeXML
'
'   Decodes the minsNode XML DOM-Like Tree Fragment.
'   Note it is a private funtcviotn called when the
'   XMLResponse property is set.
'
'   If there is a problem it will change the
'   XMLParseError property to store the information
'
'   Effectively the passed DOM tree models
'   the following fragment of XML:
'
'   <?xml version="1.0"?>
'   <methodResponse>
'       <params>
'           <param>
'               <value><string>South Dakota</string></value>
'           </param>
'       </params>
'   </methodResponse>
'
Private Sub DecodeXML()
    Const METHODTITLE = MODULETITLE & "#DecodeXML"
    
    Dim lstrReturn As String
    
    If minsParser(1).Value = "params" Then
    
'---------------------------------------------------
'       pass control to XMLRPCParams to
'       process the params node
        lstrReturn = minsParams.DecodeXML(minsParser(1))
        
'---------------------------------------------------
'       IF successfull then set status to
'       XMLRPC_PARAMSRETURNED ELSE
'       pass the error back
        If lstrReturn = "" Then
            menmStatus = XMLRPC_PARAMSRETURNED
        Else
            XMLParseError = lstrReturn
        End If
    ElseIf minsParser(1).Value = "fault" Then
    
'---------------------------------------------------
'       pass control to XMLRPCFault to
'       process the params node
        lstrReturn = minsFault.DecodeXML(minsParser(1))
        
'---------------------------------------------------
'       IF successfull then set status to
'       XMLRPC_FAULTRETURNED ELSE pass the error back
        If lstrReturn = "" Then
            menmStatus = XMLRPC_FAULTRETURNED
        Else
            XMLParseError = lstrReturn
        End If
        
'---------------------------------------------------
'       Otherwise not <params> or <fault>,
'       pass back the error
'       (This should have been picked up in DTD)
    Else
        XMLParseError = ginsUtility.XMLErrorName("Found '" & minsParser(1).Value & "' expecting 'params' or 'fault'", minsParser(1).Position)
    End If
    
End Sub
