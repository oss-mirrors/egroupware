VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
  Persistable = 0  'NotPersistable
  DataBindingBehavior = 0  'vbNone
  DataSourceBehavior  = 0  'vbNone
  MTSTransactionMode  = 0  'NotAnMTSObject
END
Attribute VB_Name = "XMLRPCRequest"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = False
Attribute VB_Exposed = True
Attribute VB_HelpID = 6000
Attribute VB_Description = "Models an XML-RPC request. The object that is built by the user to fire an XML-RPC request. It contains a load of properties to set and an execute method which returns the response."
'===============================================================================
'
'  Title             : Request.cls
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
'  Purpose           : XML-RPC Request Structure
'  Notes             : This is the object that is built by the user
'                      to fire an XML-RPC request. It contains a load
'                      of properties to set and an execute method
'                      which returns the response.
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
'
'   Request example
'   ===============
'
'   Here 's an example of an XML-RPC request:
'
'   POST /RPC2 HTTP/1.0
'   User-Agent: Frontier/5.1.2 (WinNT)
'   Host: betty.userland.com
'   Content-Type: text/xml
'   Content-length: 181
'
'   <?xml version="1.0"?>
'   <methodCall>
'       <methodName>examples.getStateName</methodName>
'       <params>
'           <param>
'               <value><i4>41</i4></value>
'           </param>
'       </params>
'   </methodCall>
'
'===============================================================================

Option Explicit

Private Const MODULETITLE = APPNAME & "#Request.cls"

'---------------------------------------------------
'   The local copy of the Params class
Private minsParams As New XMLRPCParams

'---------------------------------------------------
'   The user agent which gets embedded in
'   the HTTP request header
Private mstrUserAgent As String

'================================================
'   P R O P E R T I E S
'================================================

'---------------------------------------------------
'   The Username credentials
'
'   Chris Carter added credentials support
'   24/4/2004
'
Public Username As String
Attribute Username.VB_VarHelpID = 6450
Attribute Username.VB_VarDescription = "The user name for HTTP basic authentication."

'---------------------------------------------------
'   The Password credentials
'
'   Chris Carter added credentials support
'   24/4/2004
'
Public Password As String
Attribute Password.VB_VarHelpID = 6500
Attribute Password.VB_VarDescription = "The password for HTTP basic authentication."

'---------------------------------------------------
'   The Host Name
Public HostName As String
Attribute HostName.VB_VarHelpID = 6050
Attribute HostName.VB_VarDescription = "The Host Name e.g. www.hostname.org"

'---------------------------------------------------
'   The Host Port
Public HostPort As Long
Attribute HostPort.VB_VarHelpID = 6100
Attribute HostPort.VB_VarDescription = "The host HTTP port number e.g. 80"

'---------------------------------------------------
'   The Host Uniform Resource Identifier
Public HostURI As String
Attribute HostURI.VB_VarHelpID = 6150
Attribute HostURI.VB_VarDescription = "The Host Uniform Resource Identifier e.g. /RPC2"

'---------------------------------------------------
'   The Method Name
Public MethodName As String
Attribute MethodName.VB_VarHelpID = 6200
Attribute MethodName.VB_VarDescription = "The name of the method to be called e.g. SendEmail"

'---------------------------------------------------
'   The SSL Flags
Public SSLFlags As XMLRPC_SSLTypes
Attribute SSLFlags.VB_VarHelpID = 6450
Attribute SSLFlags.VB_VarDescription = "A flag from the XMLRPC_SSLTypes enumerated list which contains any SSL settings."

'================================================
'
'   PUBLIC Params
'
'   Read Only.
'   The Parameters to be set for the method call
'
Public Property Get Params() As XMLRPCParams
Attribute Params.VB_Description = "The Parameters to be set for the method call"
Attribute Params.VB_HelpID = 6250
    Set Params = minsParams
End Property

'================================================
'
'   PUBLIC UserAgent
'
'   Read Only.
'   The user agent which gets embedded in
'   the HTTP request header.
'
Public Property Get UserAgent() As String
Attribute UserAgent.VB_Description = "The user agent which gets embedded in the HTTP request header. Read only"
Attribute UserAgent.VB_HelpID = 6350
    UserAgent = mstrUserAgent
End Property

'================================================
'
'   PUBLIC XMLToSend
'
'   Read Only.
'   The XML that will be sent to the server. For Debug purposes.
'
Public Property Get XMLToSend() As String
Attribute XMLToSend.VB_Description = "The XML that will be sent to the server. For Debug purposes."
Attribute XMLToSend.VB_HelpID = 6400
    Const METHODTITLE = MODULETITLE & "#XMLToSend"
    Dim lintCount As Integer
    
    XMLToSend = "<?xml version=""1.0""?>" & vbCrLf & _
                "<methodCall>" & vbCrLf & _
                Indent(1) & "<methodName>" & MethodName & "</methodName>" & vbCrLf & _
                minsParams.EncodeXML(1) & _
                "</methodCall>" & vbCrLf
                 
End Property

'================================================
'   M E T H O D S
'================================================

'================================================
'
'   PRIVATE Class_Initialize
'
'   Standard VB call when class is initialised.
'   Sets up the user agent string with the App
'   Name and the version number.
'
Private Sub Class_Initialize()
    mstrUserAgent = APPNAME & " V" & App.Major & "." & App.Minor & "." & App.Revision
    SSLFlags = XMLRPC_SSL_NONE

'---------------------------------------------------
'   Chris Carter added credentials support
'   24/4/2004
    Username = vbNullString
    Password = vbNullString
End Sub

'================================================
'
'   PUBLIC Submit
'
'   Checks the properties and then submits
'   the request to the server.
'
Public Function Submit() As XMLRPCResponse
Attribute Submit.VB_Description = "Checks the properties and then submits the request to the server"
Attribute Submit.VB_HelpID = 6300
    Const METHODTITLE = MODULETITLE & "#Submit"

'---------------------------------------------------
'   Must have a host name
    If HostName = "" Then
        Err.Raise vbXMLRPC_ERROR_HOSTNAMENOTSET, _
                  METHODTITLE, _
                  ginsUtility.GetErrorCode(vbXMLRPC_ERROR_HOSTNAMENOTSET), _
                  App.HelpFile, _
                  ginsUtility.ErrorHelpContext(vbXMLRPC_ERROR_HOSTNAMENOTSET)
    
'---------------------------------------------------
'   Must have a host port
    ElseIf HostPort = 0 Then
        Err.Raise vbXMLRPC_ERROR_HOSTPORTNOTSET, _
                  METHODTITLE, _
                  ginsUtility.GetErrorCode(vbXMLRPC_ERROR_HOSTPORTNOTSET), _
                  App.HelpFile, _
                  ginsUtility.ErrorHelpContext(vbXMLRPC_ERROR_HOSTPORTNOTSET)
'---------------------------------------------------
'   Must have a host URI
    ElseIf HostURI = "" Then
        Err.Raise vbXMLRPC_ERROR_HOSTURINOTSET, _
                  METHODTITLE, _
                  ginsUtility.GetErrorCode(vbXMLRPC_ERROR_HOSTURINOTSET), _
                  App.HelpFile, _
                  ginsUtility.ErrorHelpContext(vbXMLRPC_ERROR_HOSTURINOTSET)
    
'---------------------------------------------------
'   Must have a method name
    ElseIf MethodName = "" Then
        Err.Raise vbXMLRPC_ERROR_METHODNAMENOTSET, _
                  METHODTITLE, _
                  ginsUtility.GetErrorCode(vbXMLRPC_ERROR_METHODNAMENOTSET), _
                  App.HelpFile, _
                  ginsUtility.ErrorHelpContext(vbXMLRPC_ERROR_METHODNAMENOTSET)
    
'---------------------------------------------------
'   Must have a user agent
    ElseIf mstrUserAgent = "" Then
        Err.Raise vbXMLRPC_ERROR_USERAGENTNOTSET, _
                  METHODTITLE, _
                  ginsUtility.GetErrorCode(vbXMLRPC_ERROR_USERAGENTNOTSET), _
                  App.HelpFile, _
                  ginsUtility.ErrorHelpContext(vbXMLRPC_ERROR_USERAGENTNOTSET)
    End If
    
'---------------------------------------------------
'   All tests passed submit the request (gulp)
    Set Submit = SendRequestAndGetResponse
    
End Function

'================================================
'
'   PRIVATE SendRequestAndGetResponse
'
'   Does the work of getting the data from the server.
'
Private Function SendRequestAndGetResponse() As XMLRPCResponse

    Const METHODTITLE = "SendRequestAndGetResponse"
    
    Dim llngInternetSessionHandle   As Long ' WinInet Session Handle
    Dim llngInternetConnectHandle   As Long ' WinInet Connection Handle
    Dim llngHTTPOpenRequestHandle   As Long ' WinInet Request Handle
    Dim lstrHeaders                 As String ' Extra Headers to send
'    Dim llngContentLength           As Long
    Dim linsResponse                As New XMLRPCResponse ' Response object
    Dim linsHTTPCall                As New XMLHTTPCall
    Dim linsXMLUtility              As New XMLUtility
    
'---------------------------------------------------
'   First build XML to send. Any errors
'   will stop the process before we start
'   talking to the server
    linsHTTPCall.DataToPost = XMLToSend
    
    linsHTTPCall.UserAgent = mstrUserAgent
    linsHTTPCall.HostName = HostName
    linsHTTPCall.HostPort = HostPort
    linsHTTPCall.HostURI = HostURI
    linsHTTPCall.HeadersToSend.Add "Content-Type: text/xml"
    linsHTTPCall.RequestType = HTTP_POST
    linsHTTPCall.HTTPVersion = HTTP_1_0

'---------------------------------------------------
'   Chris Carter added credentials support
'   24/4/2004
    If Username <> vbNullString Then
        linsHTTPCall.Username = Username
    End If
    If Password <> vbNullString Then
        linsHTTPCall.Password = Password
    End If
    
'---------------------------------------------------
'   Setting an additional flag to be secure
'   if necessary (A test !!!)
    Select Case SSLFlags
    Case XMLRPC_SSL_STRICT
        linsHTTPCall.HTTPOpenRequestFlags = (INTERNET_FLAG_RELOAD + _
                                             INTERNET_FLAG_SECURE)
    Case XMLRPC_SSL_IGNORE_CERT_CN_INVALID_ONLY
        linsHTTPCall.HTTPOpenRequestFlags = (INTERNET_FLAG_RELOAD + _
                                             INTERNET_FLAG_SECURE + _
                                             INTERNET_FLAG_IGNORE_CERT_CN_INVALID)
    Case XMLRPC_SSL_IGNORE_CERT_DATE_INVALID_ONLY
        linsHTTPCall.HTTPOpenRequestFlags = (INTERNET_FLAG_RELOAD + _
                                             INTERNET_FLAG_SECURE + _
                                             INTERNET_FLAG_IGNORE_CERT_DATE_INVALID)
    Case XMLRPC_SSL_IGNORE_CERT_CN_INVALID_AND_DATE_INVALID
        linsHTTPCall.HTTPOpenRequestFlags = (INTERNET_FLAG_RELOAD + _
                                             INTERNET_FLAG_SECURE + _
                                             INTERNET_FLAG_IGNORE_CERT_CN_INVALID + _
                                             INTERNET_FLAG_IGNORE_CERT_DATE_INVALID)
    Case Else
        linsHTTPCall.HTTPOpenRequestFlags = INTERNET_FLAG_RELOAD
    End Select
    
    On Error GoTo SendRequestAndGetResponse_Err
    linsHTTPCall.SubmitRequest

'---------------------------------------------------
'   Get header Info
    linsResponse.HTTPStatusCode = HTTP_STATUS_OK

SendRequestAndGetResponse_InterimResume:
    On Error GoTo 0
    linsResponse.HTTPHeaders = linsHTTPCall.HeadersReceived.HeadersString
    
'---------------------------------------------------
'   If there is an HTTP problem with the call then
'   skip the part that gets the data
    If linsResponse.HTTPStatusCode = HTTP_STATUS_OK Then
    
'---------------------------------------------------
'       Get XMLResponse
        linsResponse.XMLResponse = linsHTTPCall.DataReturned
    
    End If

    Set SendRequestAndGetResponse = linsResponse

    Exit Function
    
SendRequestAndGetResponse_Err:

    If Err.Number = vbXML_ERROR_HTTPERROR Then
        linsResponse.HTTPStatusCode = Val(Mid$(Err.Description, 11, 5))
        Resume SendRequestAndGetResponse_InterimResume
    Else
        Err.Raise vbXMLRPC_ERROR_HTTPCALLFAILED, _
                  METHODTITLE, _
                  "XML Parser has reported a " & linsXMLUtility.GetErrorCode(Err.Number) & ", description is : " & Err.Description, _
                  App.HelpFile, _
                  ginsUtility.ErrorHelpContext(vbXMLRPC_ERROR_HTTPCALLFAILED)
    End If

End Function

