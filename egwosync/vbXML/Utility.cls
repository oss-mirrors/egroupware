VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
  Persistable = 0  'NotPersistable
  DataBindingBehavior = 0  'vbNone
  DataSourceBehavior  = 0  'vbNone
  MTSTransactionMode  = 0  'NotAnMTSObject
END
Attribute VB_Name = "XMLUtility"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = False
Attribute VB_Exposed = True
Attribute VB_HelpID = 14000
Attribute VB_Description = "Contains utility functions for getting meaningfull strings from passed numbers and other useful functions."
'===============================================================================
'
'  Title             : Utility.cls
'  Program           : vbXML
'  Version           : 0.5
'  Copyright         : © EnAppSys Ltd
'  Date              : 8th September, 2002
'  Author            : Phil Hewitt
'  Contact Address   : 52 Byelands Street,
'                      Middlesbrough,
'                      Cleveland. TS4 2HP
'                      United Kingdom
'  Contact e-mail    : support@enappsys.com
'  Technical Reviewer:
'
'  Purpose           : XML Utility Class
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
'   Enum containing possible error numbers
Private Const BASEERRORNUMBER = vbObjectError + 345678
Public Enum XML_Errors
    vbXML_ERROR_WININETERROR = BASEERRORNUMBER
    vbXML_ERROR_HTTPERROR = BASEERRORNUMBER + 1
    vbXML_ERROR_MALFORMEDXML = BASEERRORNUMBER + 2
    vbXML_ERROR_UNSUPPORTED = BASEERRORNUMBER + 3
    vbXML_ERROR_MALFORMEDDTD = BASEERRORNUMBER + 4
    vbXML_ERROR_NODTDTOVALIDATEWITH = BASEERRORNUMBER + 5
    vbXML_ERROR_INVALIDXML = BASEERRORNUMBER + 6
    vbXML_ERROR_UNKNOWNREQUESTTYPE = BASEERRORNUMBER + 7
    vbXML_ERROR_UNKNOWNPROTOCOLTYPE = BASEERRORNUMBER + 8
    vbXML_ERROR_ATTRIBUTENOTFOUND = BASEERRORNUMBER + 9
    vbXML_ERROR_TAGNOTFOUND = BASEERRORNUMBER + 10
    vbXML_ERROR_DTDELEMENTNOTFOUND = BASEERRORNUMBER + 11
    vbXML_ERROR_UNDEFINEDNAMESPACE = BASEERRORNUMBER + 12
    vbXML_ERROR_PORTOUTOFRANGE = BASEERRORNUMBER + 13
End Enum

'================================================
'
'   PUBLIC GetErrorCode
'
'   Gets a verbose explanation of the passed
'   XML error code.
'
Public Function GetErrorCode(ByVal ErrorNumber As Long) As String
Attribute GetErrorCode.VB_Description = "Gets a verbose explanation of the passed XML error code."
Attribute GetErrorCode.VB_HelpID = 14050
    Select Case ErrorNumber
    Case vbXML_ERROR_WININETERROR
        GetErrorCode = "WININET.dll error"
    Case vbXML_ERROR_HTTPERROR
        GetErrorCode = "HTTP Error"
    Case vbXML_ERROR_MALFORMEDXML
        GetErrorCode = "Malformed XML"
    Case vbXML_ERROR_UNSUPPORTED
        GetErrorCode = "Unsupported Feature of XML Standard"
    Case vbXML_ERROR_MALFORMEDDTD
        GetErrorCode = "Malformed DTD"
    Case vbXML_ERROR_NODTDTOVALIDATEWITH
        GetErrorCode = "No DTD to Validate File With"
    Case vbXML_ERROR_INVALIDXML
        GetErrorCode = "Invalid XML"
    Case vbXML_ERROR_UNKNOWNREQUESTTYPE
        GetErrorCode = "Unknown Request Type"
    Case vbXML_ERROR_UNKNOWNPROTOCOLTYPE
        GetErrorCode = "Unknown Protocol Type"
    Case vbXML_ERROR_ATTRIBUTENOTFOUND
        GetErrorCode = "Attribute Not Found"
    Case vbXML_ERROR_TAGNOTFOUND
        GetErrorCode = "Tag Not Found in Sub-Nodes"
    Case vbXML_ERROR_DTDELEMENTNOTFOUND
        GetErrorCode = "DTD Element not found in DTD Elements collecion"
    Case vbXML_ERROR_UNDEFINEDNAMESPACE
        GetErrorCode = "Namespace is not defined in this document at this level"
    Case vbXML_ERROR_PORTOUTOFRANGE
        GetErrorCode = "Port should be in the range 1 to 65535"
    Case Is > 0
        GetErrorCode = Error(ErrorNumber)
    Case Else
        GetErrorCode = "Unknown Error"
    End Select
End Function

'================================================
'
'   FRIEND ErrorHelpContext
'
'   Returns an error context from the
'   passed XML error.
'
'   Note it is a friend to allow functions
'   within the DLL to call it but not outside.
'
Friend Function ErrorHelpContext(ByVal ErrorNumber As XML_Errors) As Long
    Select Case ErrorNumber
    Case vbXML_ERROR_WININETERROR, _
         vbXML_ERROR_HTTPERROR, _
         vbXML_ERROR_MALFORMEDXML, _
         vbXML_ERROR_UNSUPPORTED, _
         vbXML_ERROR_MALFORMEDDTD, _
         vbXML_ERROR_NODTDTOVALIDATEWITH, _
         vbXML_ERROR_INVALIDXML, _
         vbXML_ERROR_UNKNOWNREQUESTTYPE, _
         vbXML_ERROR_UNKNOWNPROTOCOLTYPE, _
         vbXML_ERROR_ATTRIBUTENOTFOUND, _
         vbXML_ERROR_TAGNOTFOUND, _
         vbXML_ERROR_DTDELEMENTNOTFOUND, _
         vbXML_ERROR_UNDEFINEDNAMESPACE, _
         vbXML_ERROR_PORTOUTOFRANGE
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
Attribute GetHTTPError.VB_HelpID = 14100

    GetHTTPError = "HTTP Code " & ErrorNumber & " : "
    Select Case ErrorNumber
    Case HTTP_STATUS_CONTINUE: GetHTTPError = GetHTTPError & "CONTINUE - The request can be continued."
    Case HTTP_STATUS_SWITCH_PROTOCOLS: GetHTTPError = GetHTTPError & "SWITCH PROTOCOLS - The server has switched protocols in an upgrade header."
    Case HTTP_STATUS_OK: GetHTTPError = GetHTTPError & "OK - The request completed successfully."
    Case HTTP_STATUS_CREATED: GetHTTPError = GetHTTPError & "CREATED - The request has been fulfilled and resulted in the creation of a new resource."
    Case HTTP_STATUS_ACCEPTED: GetHTTPError = GetHTTPError & "ACCEPTED - The request has been accepted for processing, but the processing has not been completed."
    Case HTTP_STATUS_PARTIAL: GetHTTPError = GetHTTPError & "PARTIAL - The returned meta information in the entity-header is not the definitive set available from the origin server."
    Case HTTP_STATUS_NO_CONTENT: GetHTTPError = GetHTTPError & "NO CONTENT - The server has fulfilled the request, but there is no new information to send back."
    Case HTTP_STATUS_RESET_CONTENT: GetHTTPError = GetHTTPError & "RESET CONTENT - The request has been completed, and the client program should reset the document view that caused the request to be sent to allow the user to easily initiate another input action."
    Case HTTP_STATUS_PARTIAL_CONTENT: GetHTTPError = GetHTTPError & "PARTIAL CONTENT - The server has fulfilled the partial GET request for the resource."
    Case HTTP_STATUS_AMBIGUOUS: GetHTTPError = GetHTTPError & "AMBIGUOUS - The server couldn't decide what to return."
    Case HTTP_STATUS_MOVED: GetHTTPError = GetHTTPError & "MOVED - The requested resource has been assigned to a new permanent URI (Uniform Resource Identifier), and any future references to this resource should be done using one of the returned URIs."
    Case HTTP_STATUS_REDIRECT: GetHTTPError = GetHTTPError & "REDIRECT - The requested resource resides temporarily under a different URI (Uniform Resource Identifier)."
    Case HTTP_STATUS_REDIRECT_METHOD: GetHTTPError = GetHTTPError & "REDIRECT METHOD - The response to the request can be found under a different URI (Uniform Resource Identifier) and should be retrieved using a GET HTTP verb on that resource."
    Case HTTP_STATUS_NOT_MODIFIED: GetHTTPError = GetHTTPError & "NOT MODIFIED - The requested resource has not been modified."
    Case HTTP_STATUS_USE_PROXY: GetHTTPError = GetHTTPError & "USE PROXY - The requested resource must be accessed through the proxy given by the location field."
    Case HTTP_STATUS_REDIRECT_KEEP_VERB: GetHTTPError = GetHTTPError & "REDIRECT KEEP VERB - The redirected request keeps the same HTTP verb. HTTP/1.1 behavior."
    Case HTTP_STATUS_BAD_REQUEST: GetHTTPError = GetHTTPError & "BAD REQUEST - The request could not be processed by the server due to invalid syntax."
    Case HTTP_STATUS_DENIED: GetHTTPError = GetHTTPError & "DENIED - The requested resource requires user authentication."
    Case HTTP_STATUS_PAYMENT_REQ: GetHTTPError = GetHTTPError & "PAYMENT REQ - Not currently implemented in the HTTP protocol."
    Case HTTP_STATUS_FORBIDDEN: GetHTTPError = GetHTTPError & "FORBIDDEN - The server understood the request, but is refusing to fulfill it."
    Case HTTP_STATUS_NOT_FOUND: GetHTTPError = GetHTTPError & "NOT FOUND - The server has not found anything matching the requested URI (Uniform Resource Identifier)."
    Case HTTP_STATUS_BAD_METHOD: GetHTTPError = GetHTTPError & "BAD METHOD - The HTTP verb used is not allowed."
    Case HTTP_STATUS_NONE_ACCEPTABLE: GetHTTPError = GetHTTPError & "NONE ACCEPTABLE - No responses acceptable to the client were found."
    Case HTTP_STATUS_PROXY_AUTH_REQ: GetHTTPError = GetHTTPError & "PROXY AUTH REQ - Proxy authentication required."
    Case HTTP_STATUS_REQUEST_TIMEOUT: GetHTTPError = GetHTTPError & "REQUEST TIMEOUT - The server timed out waiting for the request."
    Case HTTP_STATUS_CONFLICT: GetHTTPError = GetHTTPError & "CONFLICT - The request could not be completed due to a conflict with the current state of the resource. The user should resubmit with more information."
    Case HTTP_STATUS_GONE:   GetHTTPError = "GONE - The requested resource is no longer available at the server, and no forwarding address is known."
    Case HTTP_STATUS_LENGTH_REQUIRED: GetHTTPError = GetHTTPError & "LENGTH REQUIRED - The server refuses to accept the request without a defined content length."
    Case HTTP_STATUS_PRECOND_FAILED: GetHTTPError = GetHTTPError & "PRECOND FAILED - The precondition given in one or more of the request header fields evaluated to false when it was tested on the server."
    Case HTTP_STATUS_REQUEST_TOO_LARGE: GetHTTPError = GetHTTPError & "REQUEST TOO LARGE - The server is refusing to process a request because the request entity is larger than the server is willing or able to process."
    Case HTTP_STATUS_URI_TOO_LONG: GetHTTPError = GetHTTPError & "URI TOO LONG - The server is refusing to service the request because the request URI (Uniform Resource Identifier) is longer than the server is willing to interpret."
    Case HTTP_STATUS_UNSUPPORTED_MEDIA: GetHTTPError = GetHTTPError & "UNSUPPORTED MEDIA - The server is refusing to service the request because the entity of the request is in a format not supported by the requested resource for the requested method."
    Case HTTP_STATUS_RETRY_WITH: GetHTTPError = GetHTTPError & "RETRY WITH - The request should be retried after doing the appropriate action."
    Case HTTP_STATUS_SERVER_ERROR: GetHTTPError = GetHTTPError & "SERVER ERROR - The server encountered an unexpected condition that prevented it from fulfilling the request."
    Case HTTP_STATUS_NOT_SUPPORTED: GetHTTPError = GetHTTPError & "NOT SUPPORTED - The server does not support the functionality required to fulfill the request."
    Case HTTP_STATUS_BAD_GATEWAY: GetHTTPError = GetHTTPError & "BAD GATEWAY - The server, while acting as a gateway or proxy, received an invalid response from the upstream server it accessed in attempting to fulfill the request."
    Case HTTP_STATUS_SERVICE_UNAVAIL: GetHTTPError = GetHTTPError & "SERVICE UNAVAIL - The service is temporarily overloaded."
    Case HTTP_STATUS_GATEWAY_TIMEOUT: GetHTTPError = GetHTTPError & "GATEWAY TIMEOUT - The request was timed out waiting for a gateway."
    Case HTTP_STATUS_VERSION_NOT_SUP: GetHTTPError = GetHTTPError & "VERSION NOT SUP - The server does not support, or refuses to support, the HTTP protocol version that was used in the request message."
    Case Else: GetHTTPError = GetHTTPError & "Unknown"
    End Select
    
End Function

'================================================
'
'   PUBLIC EncodeEscapeCodes
'
'   Replaces certain characters with escape codes
'
Public Function EncodeEscapeCodes(ByVal Data As String) As String
Attribute EncodeEscapeCodes.VB_Description = "Replaces certain characters with escape codes."
Attribute EncodeEscapeCodes.VB_HelpID = 14250
    
'---------------------------------------------------
'
'   This section of code catches & that
'   is to be encoded
'
    Data = Replace(Data, "&", "&amp;")
    
'---------------------------------------------------
'
'   This section of code catches ",',<,> that
'   is to be encoded
'
    Data = Replace(Data, """", "&quot;")
    Data = Replace(Data, ">", "&gt;")
    Data = Replace(Data, "<", "&lt;")
    Data = Replace(Data, "'", "&apos;")
    
    EncodeEscapeCodes = Data
    
End Function

'================================================
'
'   PUBLIC DecodeEscapeCodes
'
'   Replaces any escape codes with characters
'
Public Function DecodeEscapeCodes(ByVal Data As String) As String
Attribute DecodeEscapeCodes.VB_Description = "Replaces any escape codes with characters."
Attribute DecodeEscapeCodes.VB_HelpID = 14200
    
    Dim lintCount As Integer
    
    If InStr(Data, "&") <> 0 Then
    
'---------------------------------------------------
'
'       This section of code catches ",',<,> that
'       has been encoded
'
        Data = Replace(Data, "&quot;", """")
        Data = Replace(Data, "&gt;", ">")
        Data = Replace(Data, "&lt;", "<")
        Data = Replace(Data, "&apos;", "'")
        
        If InStr(Data, "&") <> 0 Then

'---------------------------------------------------
'
'           This section of code comes from :
'           http://www.w3.org/TR/REC-html32.html#dtd
'
'           (C) International Organization for Standardization 1986
'           Permission to copy in any form is granted for use with
'           conforming SGML systems and applications as defined in
'           ISO 8879, provided this notice is included in all copies.
'           This has been extended for use with HTML to cover the full
'           set of codes in the range 160-255 decimal.
'
            Data = Replace(Data, "&nbsp;", "&#160;") ' no-break space
            Data = Replace(Data, "&iexcl;", "&#161;") ' inverted exclamation mark
            Data = Replace(Data, "&cent;", "&#162;") ' cent sign
            Data = Replace(Data, "&pound;", "&#163;") ' pound sterling sign
            Data = Replace(Data, "&curren;", "&#164;") ' general currency sign
            Data = Replace(Data, "&yen;", "&#165;") ' yen sign
            Data = Replace(Data, "&brvbar;", "&#166;") ' broken (vertical) bar
            Data = Replace(Data, "&sect;", "&#167;") ' section sign
            Data = Replace(Data, "&uml;", "&#168;") ' umlaut (dieresis)
            Data = Replace(Data, "&copy;", "&#169;") ' copyright sign
            Data = Replace(Data, "&ordf;", "&#170;") ' ordinal indicator, feminine
            Data = Replace(Data, "&laquo;", "&#171;") ' angle quotation mark, left
            Data = Replace(Data, "&not;", "&#172;") ' not sign
            Data = Replace(Data, "&shy;", "&#173;") ' soft hyphen
            Data = Replace(Data, "&reg;", "&#174;") ' registered sign
            Data = Replace(Data, "&macr;", "&#175;") ' macron
            Data = Replace(Data, "&deg;", "&#176;") ' degree sign
            Data = Replace(Data, "&plusmn;", "&#177;") ' plus-or-minus sign
            Data = Replace(Data, "&sup2;", "&#178;") ' superscript two
            Data = Replace(Data, "&sup3;", "&#179;") ' superscript three
            Data = Replace(Data, "&acute;", "&#180;") ' acute accent
            Data = Replace(Data, "&micro;", "&#181;") ' micro sign
            Data = Replace(Data, "&para;", "&#182;") ' pilcrow (paragraph sign)
            Data = Replace(Data, "&middot;", "&#183;") ' middle dot
            Data = Replace(Data, "&cedil;", "&#184;") ' cedilla
            Data = Replace(Data, "&sup1;", "&#185;") ' superscript one
            Data = Replace(Data, "&ordm;", "&#186;") ' ordinal indicator, masculine
            Data = Replace(Data, "&raquo;", "&#187;") ' angle quotation mark, right
            Data = Replace(Data, "&frac14;", "&#188;") ' fraction one-quarter
            Data = Replace(Data, "&frac12;", "&#189;") ' fraction one-half
            Data = Replace(Data, "&frac34;", "&#190;") ' fraction three-quarters
            Data = Replace(Data, "&iquest;", "&#191;") ' inverted question mark
            Data = Replace(Data, "&Agrave;", "&#192;") ' capital A, grave accent
            Data = Replace(Data, "&Aacute;", "&#193;") ' capital A, acute accent
            Data = Replace(Data, "&Acirc;", "&#194;") ' capital A, circumflex accent
            Data = Replace(Data, "&Atilde;", "&#195;") ' capital A, tilde
            Data = Replace(Data, "&Auml;", "&#196;") ' capital A, dieresis or umlaut mark
            Data = Replace(Data, "&Aring;", "&#197;") ' capital A, ring
            Data = Replace(Data, "&AElig;", "&#198;") ' capital AE diphthong (ligature)
            Data = Replace(Data, "&Ccedil;", "&#199;") ' capital C, cedilla
            Data = Replace(Data, "&Egrave;", "&#200;") ' capital E, grave accent
            Data = Replace(Data, "&Eacute;", "&#201;") ' capital E, acute accent
            Data = Replace(Data, "&Ecirc;", "&#202;") ' capital E, circumflex accent
            Data = Replace(Data, "&Euml;", "&#203;") ' capital E, dieresis or umlaut mark
            Data = Replace(Data, "&Igrave;", "&#204;") ' capital I, grave accent
            Data = Replace(Data, "&Iacute;", "&#205;") ' capital I, acute accent
            Data = Replace(Data, "&Icirc;", "&#206;") ' capital I, circumflex accent
            Data = Replace(Data, "&Iuml;", "&#207;") ' capital I, dieresis or umlaut mark
            Data = Replace(Data, "&ETH;", "&#208;") ' capital Eth, Icelandic
            Data = Replace(Data, "&Ntilde;", "&#209;") ' capital N, tilde
            Data = Replace(Data, "&Ograve;", "&#210;") ' capital O, grave accent
            Data = Replace(Data, "&Oacute;", "&#211;") ' capital O, acute accent
            Data = Replace(Data, "&Ocirc;", "&#212;") ' capital O, circumflex accent
            Data = Replace(Data, "&Otilde;", "&#213;") ' capital O, tilde
            Data = Replace(Data, "&Ouml;", "&#214;") ' capital O, dieresis or umlaut mark
            Data = Replace(Data, "&times;", "&#215;") ' multiply sign
            Data = Replace(Data, "&Oslash;", "&#216;") ' capital O, slash
            Data = Replace(Data, "&Ugrave;", "&#217;") ' capital U, grave accent
            Data = Replace(Data, "&Uacute;", "&#218;") ' capital U, acute accent
            Data = Replace(Data, "&Ucirc;", "&#219;") ' capital U, circumflex accent
            Data = Replace(Data, "&Uuml;", "&#220;") ' capital U, dieresis or umlaut mark
            Data = Replace(Data, "&Yacute;", "&#221;") ' capital Y, acute accent
            Data = Replace(Data, "&THORN;", "&#222;") ' capital THORN, Icelandic
            Data = Replace(Data, "&szlig;", "&#223;") ' small sharp s, German (sz ligature)
            Data = Replace(Data, "&agrave;", "&#224;") ' small a, grave accent
            Data = Replace(Data, "&aacute;", "&#225;") ' small a, acute accent
            Data = Replace(Data, "&acirc;", "&#226;") ' small a, circumflex accent
            Data = Replace(Data, "&atilde;", "&#227;") ' small a, tilde
            Data = Replace(Data, "&auml;", "&#228;") ' small a, dieresis or umlaut mark
            Data = Replace(Data, "&aring;", "&#229;") ' small a, ring
            Data = Replace(Data, "&aelig;", "&#230;") ' small ae diphthong (ligature)
            Data = Replace(Data, "&ccedil;", "&#231;") ' small c, cedilla
            Data = Replace(Data, "&egrave;", "&#232;") ' small e, grave accent
            Data = Replace(Data, "&eacute;", "&#233;") ' small e, acute accent
            Data = Replace(Data, "&ecirc;", "&#234;") ' small e, circumflex accent
            Data = Replace(Data, "&euml;", "&#235;") ' small e, dieresis or umlaut mark
            Data = Replace(Data, "&igrave;", "&#236;") ' small i, grave accent
            Data = Replace(Data, "&iacute;", "&#237;") ' small i, acute accent
            Data = Replace(Data, "&icirc;", "&#238;") ' small i, circumflex accent
            Data = Replace(Data, "&iuml;", "&#239;") ' small i, dieresis or umlaut mark
            Data = Replace(Data, "&eth;", "&#240;") ' small eth, Icelandic
            Data = Replace(Data, "&ntilde;", "&#241;") ' small n, tilde
            Data = Replace(Data, "&ograve;", "&#242;") ' small o, grave accent
            Data = Replace(Data, "&oacute;", "&#243;") ' small o, acute accent
            Data = Replace(Data, "&ocirc;", "&#244;") ' small o, circumflex accent
            Data = Replace(Data, "&otilde;", "&#245;") ' small o, tilde
            Data = Replace(Data, "&ouml;", "&#246;") ' small o, dieresis or umlaut mark
            Data = Replace(Data, "&divide;", "&#247;") ' divide sign
            Data = Replace(Data, "&oslash;", "&#248;") ' small o, slash
            Data = Replace(Data, "&ugrave;", "&#249;") ' small u, grave accent
            Data = Replace(Data, "&uacute;", "&#250;") ' small u, acute accent
            Data = Replace(Data, "&ucirc;", "&#251;") ' small u, circumflex accent
            Data = Replace(Data, "&uuml;", "&#252;") ' small u, dieresis or umlaut mark
            Data = Replace(Data, "&yacute;", "&#253;") ' small y, acute accent
            Data = Replace(Data, "&thorn;", "&#254;") ' small thorn, Icelandic
            Data = Replace(Data, "&yuml;", "&#255;") ' small y, dieresis or umlaut mark
    
            If InStr(Data, "&") <> 0 Then
    
'---------------------------------------------------
'
'               This section of code replaces
'               all escape codes in the range 32 to 255
'
                For lintCount = 255 To 32 Step -1
                    Data = Replace(Data, "&#" & Format(lintCount) & ";", Chr$(lintCount))
                Next lintCount
    
            End If
            
        End If
        
    End If
    
'---------------------------------------------------
'
'   This section of code catches an encoded &
'   It is done last to prevent someting like
'   &amp;gt; being converted to >, instead it
'   will be converted to &gt;
'
'   The reason being that the string mught contain
'   something that the author wanted to be escaped
'   in the final version
'
    Data = Replace(Data, "&amp;", "&")
    
    DecodeEscapeCodes = Data
    
End Function

'================================================
'
'   PUBLIC GetElementType
'
'   Gets a verbose explanation of the passed
'   XML element type.
'
Public Function GetNodeType(ByVal NodeType As XML_NodeType) As String
Attribute GetNodeType.VB_Description = "Gets a description of the node type."
Attribute GetNodeType.VB_HelpID = 14150
    Select Case NodeType
    Case XML_TAG
        GetNodeType = "Tag"
    Case XML_VALUE
        GetNodeType = "Value"
    Case XML_SPECIAL
        GetNodeType = "Special Tag"
    Case Else
        GetNodeType = "Unknown Node Type"
    End Select
End Function

'================================================
'
'   PUBLIC DecodeRFC822Date
'
'   Decodes an RFC822 Date.
'
Public Function DecodeRFC822Date(ByVal Data As String) As Date
Attribute DecodeRFC822Date.VB_Description = "Decodes an RFC822 Date"
Attribute DecodeRFC822Date.VB_HelpID = 14300
    Dim lstrYear As String
    Dim lstrMonth As String
    Dim lstrDoM As String
    Dim lstrHour As String
    Dim lstrMinute As String
    Dim lstrSecond As String
    Dim lstrTZ As String
    Dim lstrWorking As String
    
'   First get rid of any day of week ...
    If InStr(Data, ",") <> 0 Then
        lstrWorking = Trim(Right$(Data, Len(Data) - InStr(Data, ",")))
    Else
        lstrWorking = Data
    End If
    
'   Second get the day of the month
    lstrDoM = Left$(lstrWorking, InStr(lstrWorking, " ") - 1)
    lstrWorking = Right$(lstrWorking, Len(lstrWorking) - InStr(lstrWorking, " "))
    
'   Third get the month
    lstrMonth = Left$(lstrWorking, InStr(lstrWorking, " ") - 1)
    lstrWorking = Right$(lstrWorking, Len(lstrWorking) - InStr(lstrWorking, " "))
    Select Case UCase$(lstrMonth)
    Case "JAN"
        lstrMonth = "01"
    Case "FEB"
        lstrMonth = "02"
    Case "MAR"
        lstrMonth = "03"
    Case "APR"
        lstrMonth = "04"
    Case "MAY"
        lstrMonth = "05"
    Case "JUN"
        lstrMonth = "06"
    Case "JUL"
        lstrMonth = "07"
    Case "AUG"
        lstrMonth = "08"
    Case "SEP"
        lstrMonth = "09"
    Case "OCT"
        lstrMonth = "10"
    Case "NOV"
        lstrMonth = "11"
    Case "DEC"
        lstrMonth = "12"
    Case Else
        lstrMonth = "00"
    End Select
    
'   Fourth get the year
    lstrYear = Left$(lstrWorking, InStr(lstrWorking, " ") - 1)
    lstrWorking = Right$(lstrWorking, Len(lstrWorking) - InStr(lstrWorking, " "))
    
'   Fifth get the hour
    lstrHour = Left$(lstrWorking, InStr(lstrWorking, ":") - 1)
    lstrWorking = Right$(lstrWorking, Len(lstrWorking) - InStr(lstrWorking, ":"))
    
'   Sixth get the minute (and second)
    If InStr(lstrWorking, ":") = 0 Then
        If InStr(lstrWorking, " ") = 0 Then
            lstrMinute = lstrWorking
        Else
            lstrMinute = Left$(lstrWorking, InStr(lstrWorking, " ") - 1)
            lstrWorking = Right$(lstrWorking, Len(lstrWorking) - InStr(lstrWorking, " "))
        End If
        lstrSecond = "00"
    Else
        lstrMinute = Left$(lstrWorking, InStr(lstrWorking, ":") - 1)
        lstrWorking = Right$(lstrWorking, Len(lstrWorking) - InStr(lstrWorking, ":"))
        If InStr(lstrWorking, " ") = 0 Then
            lstrSecond = lstrWorking
        Else
            lstrSecond = Left$(lstrWorking, InStr(lstrWorking, " ") - 1)
            lstrWorking = Right$(lstrWorking, Len(lstrWorking) - InStr(lstrWorking, " "))
        End If
    End If
    
'   Finally get the timezone
    If lstrWorking <> "" Then
        lstrTZ = lstrWorking
    End If
    
    DecodeRFC822Date = DateSerial(Val(lstrYear), _
                                  Val(lstrMonth), _
                                  Val(lstrDoM)) + _
                       TimeSerial(Val(lstrHour), _
                                  Val(lstrMinute), _
                                  Val(lstrSecond))
End Function

'================================================
'
'   PUBLIC EncodeRFC822Date
'
'   Encodes an RFC822 Date.
'
Public Function EncodeRFC822Date(ByVal DateToEncode As Date, ByVal TimeZone As String) As String
Attribute EncodeRFC822Date.VB_Description = "Encodes an RFC822 Date"
Attribute EncodeRFC822Date.VB_HelpID = 14350
    
    EncodeRFC822Date = Format(DateToEncode, "ddd, dd mmm yyyy hh:nn:ss") & " " & TimeZone
    
End Function



