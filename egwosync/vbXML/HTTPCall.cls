VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
  Persistable = 0  'NotPersistable
  DataBindingBehavior = 0  'vbNone
  DataSourceBehavior  = 0  'vbNone
  MTSTransactionMode  = 0  'NotAnMTSObject
END
Attribute VB_Name = "XMLHTTPCall"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = False
Attribute VB_Exposed = True
Attribute VB_HelpID = 11000
Attribute VB_Description = "Models an HTTP call used to fetch a document off the web."
'===============================================================================
'
'  Title             : HTTPCall.cls
'  Program           : vbXML
'  Version           : 0.5
'  Copyright         : © Probably Microsoft(tm) and © EnAppSys Ltd
'  Date              : 8th September, 2002
'  Author            : Phil Hewitt
'  Contact Address   : 52 Byelands Street,
'                      Middlesbrough,
'                      Cleveland. TS4 2HP
'                      United Kingdom
'  Contact e-mail    : support@enappsys.com
'  Technical Reviewer:
'
'  Purpose           : Contains DECLARES and constants for accessing WININET.dll
'  Notes             : The WinInet code and comments at the top of this
'                      file are extracted from an MSDN article entitled:
'
'                      "Win32 Internet HTTP Functions in Visual Basic"
'                      by James Braum
'                      Microsoft Developer Network Technology Group
'
'                      September 1996
'
'                      URL : http://msdn.microsoft.com/library/en-us/dnwebtool/html/msdn_vbhttp.asp
'
'                      The abstraction functions at the bottom are coded
'                      by Phil Hewitt
'
'===============================================================================
'
'   This library is free software; you can redistribute it and/or
'   modify it under the terms of the GNU Lesser General private
'   License as published by the Free Software Foundation; either
'   version 2.1 of the License, or (at your option) any later version.
'
'   This library is distributed in the hope that it will be useful,
'   but WITHOUT ANY WARRANTY; without even the implied warranty of
'   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
'   Lesser General private License for more details.
'
'   You should have received a copy of the GNU Lesser General private
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

Private Const MODULETITLE = APPNAME & "#HTTPCall.cls"

Private mstrUserAgent As String
Private mstrProxyName As String
Private mstrProxyBypass As String
Private mstrHostName As String
Private mlngHostPort As Long
Private mstrHostURI As String
Private minsHeadersToSend As New XMLHTTPHeaders
Private minsHeadersReceived As New XMLHTTPHeaders
Private menmRequestType As HTTP_Request_Types
Private menmHTTPVersion As HTTP_Versions
Private menmInternetOpenType As WININET_InternetOpen_Types
Private mlngInternetOpenFlags As Long
Private mlngHTTPOpenRequestFlags As Long
Private mstrDataToPost As String
Private mstrDataReturned As String

'---------------------------------------------------
'   Chris Carter added credentials support
'   24/4/2004
Private mstrUsername As String
Private mstrPassword As String

'===================================================
'
'   Kernal32 declarations for error discovery in WININET.dll
'
Private Const FORMAT_MESSAGE_FROM_HMODULE = &H800

Private Declare Function FormatMessage Lib "kernel32" Alias "FormatMessageA" _
            (ByVal dwFlags As Long, lpSource As Any, _
             ByVal dwMessageId As Long, ByVal dwLanguageId As Long, _
             ByVal lpBuffer As String, ByVal nSize As Long, _
             Arguments As Long) As Long
Private Declare Function GetModuleHandle Lib "kernel32" Alias "GetModuleHandleA" _
            (ByVal lpModuleName As String) As Long
Private Declare Function LoadLibrary Lib "kernel32" Alias "LoadLibraryA" _
            (ByVal lpLibFileName As String) As Long
Private Declare Function FreeLibrary Lib "kernel32" _
            (ByVal hLibModule As Long) As Long

'===================================================
'   HTTP_Request_Types
'
Public Enum HTTP_Request_Types
'    HTTP_OPTIONS = 0
    HTTP_GET = 1
'    HTTP_HEAD = 2
    HTTP_POST = 3
'    HTTP_PUT = 4
'    HTTP_DELETE = 5
'    HTTP_TRACE = 6
'    HTTP_CONNECT = 7
End Enum

'===================================================
'   HTTP_Versions
'
Public Enum HTTP_Versions
    HTTP_1_0 = 0
    HTTP_1_1 = 1
End Enum

'===================================================
'   WININET_InternetOpen_Types
'
Public Enum WININET_InternetOpen_Types
    INTERNET_OPEN_TYPE_DIRECT = 1
    INTERNET_OPEN_TYPE_PRECONFIG = 0
    INTERNET_OPEN_TYPE_PRECONFIG_WITH_NO_AUTOPROXY = 4
    INTERNET_OPEN_TYPE_PROXY = 3
End Enum

'===================================================
'   HTTP Status codes
'
'   These constants come from :
'   http://msdn.microsoft.com/workshop/networking/wininet/reference/constants/statuscodes.asp
'
Public Enum HTTP_Status_Codes
    HTTP_STATUS_CONTINUE = 100 ' The request can be continued.
    HTTP_STATUS_SWITCH_PROTOCOLS = 101 ' The server has switched protocols in an upgrade header.
    HTTP_STATUS_OK = 200 ' The request completed successfully.
    HTTP_STATUS_CREATED = 201 ' The request has been fulfilled and resulted in the creation of a new resource.
    HTTP_STATUS_ACCEPTED = 202 ' The request has been accepted for processing, but the processing has not been completed.
    HTTP_STATUS_PARTIAL = 203 ' The returned meta information in the entity-header is not the definitive set available from the origin server.
    HTTP_STATUS_NO_CONTENT = 204 ' The server has fulfilled the request, but there is no new information to send back.
    HTTP_STATUS_RESET_CONTENT = 205 ' The request has been completed, and the client program should reset the document view that caused the request to be sent to allow the user to easily initiate another input action.
    HTTP_STATUS_PARTIAL_CONTENT = 206 ' The server has fulfilled the partial GET request for the resource.
    HTTP_STATUS_AMBIGUOUS = 300 ' The server couldn't decide what to return.
    HTTP_STATUS_MOVED = 301 ' The requested resource has been assigned to a new permanent URI (Uniform Resource Identifier), and any future references to this resource should be done using one of the returned URIs.
    HTTP_STATUS_REDIRECT = 302 ' The requested resource resides temporarily under a different URI (Uniform Resource Identifier).
    HTTP_STATUS_REDIRECT_METHOD = 303 ' The response to the request can be found under a different URI (Uniform Resource Identifier) and should be retrieved using a GET HTTP verb on that resource.
    HTTP_STATUS_NOT_MODIFIED = 304 ' The requested resource has not been modified.
    HTTP_STATUS_USE_PROXY = 305 ' The requested resource must be accessed through the proxy given by the location field.
    HTTP_STATUS_REDIRECT_KEEP_VERB = 307 ' The redirected request keeps the same HTTP verb. HTTP/1.1 behavior.
    HTTP_STATUS_BAD_REQUEST = 400 ' The request could not be processed by the server due to invalid syntax.
    HTTP_STATUS_DENIED = 401 ' The requested resource requires user authentication.
    HTTP_STATUS_PAYMENT_REQ = 402 ' Not currently implemented in the HTTP protocol.
    HTTP_STATUS_FORBIDDEN = 403 ' The server understood the request, but is refusing to fulfill it.
    HTTP_STATUS_NOT_FOUND = 404 ' The server has not found anything matching the requested URI (Uniform Resource Identifier).
    HTTP_STATUS_BAD_METHOD = 405 ' The HTTP verb used is not allowed.
    HTTP_STATUS_NONE_ACCEPTABLE = 406 ' No responses acceptable to the client were found.
    HTTP_STATUS_PROXY_AUTH_REQ = 407 ' Proxy authentication required.
    HTTP_STATUS_REQUEST_TIMEOUT = 408 ' The server timed out waiting for the request.
    HTTP_STATUS_CONFLICT = 409 ' The request could not be completed due to a conflict with the current state of the resource. The user should resubmit with more information.
    HTTP_STATUS_GONE = 410 ' The requested resource is no longer available at the server, and no forwarding address is known.
    HTTP_STATUS_LENGTH_REQUIRED = 411 ' The server refuses to accept the request without a defined content length.
    HTTP_STATUS_PRECOND_FAILED = 412 ' The precondition given in one or more of the request header fields evaluated to false when it was tested on the server.
    HTTP_STATUS_REQUEST_TOO_LARGE = 413 ' The server is refusing to process a request because the request entity is larger than the server is willing or able to process.
    HTTP_STATUS_URI_TOO_LONG = 414 ' The server is refusing to service the request because the request URI (Uniform Resource Identifier) is longer than the server is willing to interpret.
    HTTP_STATUS_UNSUPPORTED_MEDIA = 415 ' The server is refusing to service the request because the entity of the request is in a format not supported by the requested resource for the requested method.
    HTTP_STATUS_RETRY_WITH = 449 ' The request should be retried after doing the appropriate action.
    HTTP_STATUS_SERVER_ERROR = 500 ' The server encountered an unexpected condition that prevented it from fulfilling the request.
    HTTP_STATUS_NOT_SUPPORTED = 501 ' The server does not support the functionality required to fulfill the request.
    HTTP_STATUS_BAD_GATEWAY = 502 ' The server, while acting as a gateway or proxy, received an invalid response from the upstream server it accessed in attempting to fulfill the request.
    HTTP_STATUS_SERVICE_UNAVAIL = 503 ' The service is temporarily overloaded.
    HTTP_STATUS_GATEWAY_TIMEOUT = 504 ' The request was timed out waiting for a gateway.
    HTTP_STATUS_VERSION_NOT_SUP = 505 ' The server does not support, or refuses to support, the HTTP protocol version that was used in the request message.
End Enum

'===================================================
'   Enum WININET_InternetOpen_Flags
'
'   These constants come from :
'   Microsoft Knowledge Base Article - Q185519
'   and
'   http://msdn.microsoft.com/workshop/networking/wininet/reference/constants/api_flags.asp
'   and
'   http://msdn.microsoft.com/workshop/networking/wininet/reference/functions/internetopen.asp
'
Public Enum WININET_InternetOpen_Flags

'---------------------------------------------------
'   Makes only asynchronous requests on handles
'   descended from the handle returned from this
'   function. Only the InternetOpen function uses
'   this flag.
    INTERNET_FLAG_ASYNC = &H10000000

'---------------------------------------------------
'   Does not make network requests. All entities
'   are returned from the cache. If the requested
'   item is not in the cache, a suitable error,
'   such as ERROR_FILE_NOT_FOUND, is returned.
'   Only the InternetOpen function uses this flag.
    INTERNET_FLAG_FROM_CACHE = &H1000000

'---------------------------------------------------
'   Identical to INTERNET_FLAG_FROM_CACHE. Does
'   not make network requests. All entities are
'   returned from the cache. If the requested item
'   is not in the cache, a suitable error, such as
'   ERROR_FILE_NOT_FOUND, is returned. Only the
'   InternetOpen function uses this flag.
    INTERNET_FLAG_OFFLINE = INTERNET_FLAG_FROM_CACHE

End Enum

'===================================================
'   Enum WININET_HTTPOpenRequest_Flags
'
'   These constants come from :
'   Microsoft Knowledge Base Article - Q185519
'   and
'   http://msdn.microsoft.com/workshop/networking/wininet/reference/constants/api_flags.asp
'   and
'   http://msdn.microsoft.com/workshop/networking/wininet/reference/functions/httpopenrequest.asp
'
Public Enum WININET_HTTPOpenRequest_Flags

'---------------------------------------------------
'   Returns the resource from the cache if the
'   network request for the resource fails due
'   to an ERROR_INTERNET_CONNECTION_RESET or
'   ERROR_INTERNET_CANNOT_CONNECT error. This
'   flag is used by HttpOpenRequest.
    INTERNET_FLAG_CACHE_IF_NET_FAIL = &H10000

'---------------------------------------------------
'   Forces a reload if there was no Expires time
'   and no LastModified time returned from the
'   server when determining whether to reload
'   the item from the network. This flag can
'   be used by GopherFindFirstFile, GopherOpenFile,
'   FtpFindFirstFile, FtpGetFile, FtpOpenFile,
'   FtpPutFile, HttpOpenRequest, and InternetOpenUrl.
    INTERNET_FLAG_HYPERLINK = &H400

'---------------------------------------------------
'   Disables Win32 Internet function checking of
'   SSL/PCT-based certificates that are returned
'   from the server against the host name given
'   in the request. Win32 Internet functions use
'   a simple check against certificates by
'   comparing for matching host names and simple
'   wildcarding rules. This flag can be used by
'   HttpOpenRequest and InternetOpenUrl
'   (for HTTP requests).
    INTERNET_FLAG_IGNORE_CERT_CN_INVALID = &H1000

'---------------------------------------------------
'   Disables Win32 Internet function checking of
'   SSL/PCT-based certificates for proper validity
'   dates. This flag can be used by HttpOpenRequest
'   and InternetOpenUrl (for HTTP requests).
    INTERNET_FLAG_IGNORE_CERT_DATE_INVALID = &H2000

'---------------------------------------------------
'   Disables the ability of the Win32 Internet
'   functions to detect this special type of
'   redirect. When this flag is used, Win32
'   Internet functions transparently allow
'   redirects from HTTPS to HTTP URLs. This
'   flag can be used by HttpOpenRequest and
'   InternetOpenUrl (for HTTP requests).
    INTERNET_FLAG_IGNORE_REDIRECT_TO_HTTP = &H8000

'---------------------------------------------------
'   Disables the ability of the Win32 Internet
'   functions to detect this special type of
'   redirect. When this flag is used, Win32
'   Internet functions transparently allow
'   redirects from HTTP to HTTPS URLs. This flag
'   can be used by HttpOpenRequest and
'   InternetOpenUrl (for HTTP requests).
    INTERNET_FLAG_IGNORE_REDIRECT_TO_HTTPS = &H4000

'---------------------------------------------------
'   Uses keep-alive semantics, if available, for
'   the connection. This flag is used by
'   HttpOpenRequest and InternetOpenUrl (for
'   HTTP requests). This flag is required for
'   Microsoft Network (MSN), NT LAN Manager (NTLM),
'   and other types of authentication.
    INTERNET_FLAG_KEEP_CONNECTION = &H400000

'---------------------------------------------------
'   Causes a temporary file to be created if the
'   file cannot be cached. This flag can be used
'   by GopherFindFirstFile, GopherOpenFile,
'   FtpFindFirstFile, FtpGetFile, FtpOpenFile,
'   FtpPutFile, HttpOpenRequest, and InternetOpenUrl.
    INTERNET_FLAG_NEED_FILE = &H10

'---------------------------------------------------
'   Identical to the preferred value,
'   INTERNET_FLAG_NEED_FILE. Causes a temporary
'   file to be created if the file cannot be cached.
'   This flag can be used by GopherFindFirstFile,
'   GopherOpenFile, FtpFindFirstFile, FtpGetFile,
'   FtpOpenFile, FtpPutFile, HttpOpenRequest, and
'   InternetOpenUrl.
    INTERNET_FLAG_MUST_CACHE_REQUEST = INTERNET_FLAG_NEED_FILE

'---------------------------------------------------
'   Does not attempt authentication automatically.
'   This flag can be used by HttpOpenRequest and
'   InternetOpenUrl (for HTTP requests).
    INTERNET_FLAG_NO_AUTH = &H40000

'---------------------------------------------------
'   Does not automatically handle redirection in
'   HttpSendRequest. This flag can also be used by
'   InternetOpenUrl for HTTP requests.
    INTERNET_FLAG_NO_AUTO_REDIRECT = &H200000

'---------------------------------------------------
'   Does not add the returned entity to the cache.
'   This flag is used by GopherFindFirstFile,
'   GopherOpenFile, HttpOpenRequest, and InternetOpenUrl.
    INTERNET_FLAG_NO_CACHE_WRITE = &H4000000

'---------------------------------------------------
'   Does not add the returned entity to the cache.
'   This is identical to the preferred value,
'   INTERNET_FLAG_NO_CACHE_WRITE.
    INTERNET_FLAG_DONT_CACHE = INTERNET_FLAG_NO_CACHE_WRITE

'---------------------------------------------------
'   Does not automatically add cookie headers to
'   requests, and does not automatically add
'   returned cookies to the cookie database. This
'   flag can be used by HttpOpenRequest and
'   InternetOpenUrl (for HTTP requests).
    INTERNET_FLAG_NO_COOKIES = &H80000

'---------------------------------------------------
'   Disables the cookie dialog box. This flag can
'   be used by HttpOpenRequest and InternetOpenUrl
'   (HTTP requests only).
    INTERNET_FLAG_NO_UI = &H200

'---------------------------------------------------
'   Forces the request to be resolved by the
'   origin server, even if a cached copy exists on
'   the proxy. The InternetOpenUrl function (on
'   HTTP and HTTPS requests only) and
'   HttpOpenRequest function use this flag.
    INTERNET_FLAG_PRAGMA_NOCACHE = &H100

'---------------------------------------------------
'   Forces a download of the requested file, object,
'   or directory listing from the origin server, not
'   from the cache. The GopherFindFirstFile,
'   GopherOpenFile, FtpFindFirstFile, FtpGetFile,
'   FtpOpenFile, FtpPutFile, HttpOpenRequest, and
'   InternetOpenUrl functions utilize this flag.
    INTERNET_FLAG_RELOAD = &H80000000

'---------------------------------------------------
'   Reloads HTTP resources if the resource has
'   been modified since the last time it was
'   downloaded. All FTP and Gopher resources are
'   reloaded. This flag can be used by
'   GopherFindFirstFile, GopherOpenFile,
'   FtpFindFirstFile, FtpGetFile, FtpOpenFile,
'   FtpPutFile, HttpOpenRequest, and InternetOpenUrl.
    INTERNET_FLAG_RESYNCHRONIZE = &H800

'---------------------------------------------------
'   Uses secure transaction semantics. This
'   translates to using Secure Sockets Layer/Private
'   Communications Technology (SSL/PCT) and is only
'   meaningful in HTTP requests. This flag is used
'   by HttpOpenRequest and InternetOpenUrl, but
'   this is redundant if https:// appears in the URL.
    INTERNET_FLAG_SECURE = &H800000

End Enum

'===================================================
'   InternetOpen
'   ============
'
'   InternetOpen initializes our application's
'   use of WinInet functions. A long handle is
'   returned.
'
'   The Win32 Internet functions do not currently
'   provide support for Unicode. However, support
'   will be provided in future versions. Therefore,
'   all the functions are aliased to call the ANSI
'   version.
'
'===================================================

Private Declare Function InternetOpen Lib "wininet.dll" Alias _
   "InternetOpenA" (ByVal sAgent As String, ByVal lAccessType _
   As Long, ByVal sProxyName As String, ByVal sProxyBypass As _
   String, ByVal lFlags As Long) As Long

'===================================================
'   InternetConnect
'   ===============
'
'   InternetConnect returns a handle to an
'   HTTP session. The two constants tell the
'   function to listen on port 80, the default
'   port that HTTP servers listen to, before
'   establishing the connection.
'
'===================================================

Private Declare Function InternetConnect Lib "wininet.dll" Alias _
   "InternetConnectA" (ByVal InternetSession As Long, _
   ByVal sServerName As String, ByVal nServerPort As Long, _
   ByVal sUsername As String, ByVal sPassword As String, _
   ByVal lService As Long, ByVal lFlags As Long, _
   ByVal lContext As Long) As Long
Private Const INTERNET_DEFAULT_HTTP_PORT = 80
Private Const INTERNET_SERVICE_HTTP = 3

'===================================================
'   HttpOpenRequest
'   ===============
'
'   HttpOpenRequest returns an HTTP request
'   handle.
'
'   HttpOpenRequest takes eight parameters
'   (described below) and returns the HTTP
'   request handle, if successful. The handle
'   holds the request until you send it with
'   HttpSendRequest, which stores the HTTP
'   headers to be sent as part of the request.
'
'   hHttpSession
'   ------------
'   hHttpSession is the handle returned from
'   a previous call to InternetConnect.
'
'   sVerb
'   -----
'   sVerb is a string that contains a method,
'   or verb, to use for the request. Two common
'   HTTP 1.0 verbs (methods) are GET and POST.
'   These verbs are case-sensitive. GET retrieves
'   a file. POST tells the server to accept some
'   information that you are passing to it—for
'   example, when you are submitting a response
'   to a form. If the sVerb parameter is NULL,
'   GET will be used.
'
'   If the If-Modified-Since header field is used,
'   the GET becomes a conditional get. It will
'   only retrieve the file if the file has been
'   modified since a particular date specified
'   in the header field. You can structure your
'   requests to only retrieve the file if it has
'   been changed since some predetermined point
'   in time. An example of this will be
'   illustrated below.
'
'   sObjectName
'   -----------
'   sObjectName is the object we are interested
'   in. This is usually a file, but it can also
'   be a search specifier or an executable.
'
'   sVersion
'   --------
'   sVersion is the HTTP version. Leaving this
'   NULL is equivalent to specifying HTTP/1.0.
'
'   sReferer
'   --------
'   sReferer is a string that contains a URL of
'   a document containing a URL in the sObjectName.
'   This parameter can be left NULL to specify
'   no "referer" [sic].
'
'   sAcceptTypes
'   ------------
'   sAcceptTypes is a string containing a list
'   of valid acceptance types. Leaving this NULL
'   will indicate that only text documents will
'   be accepted.
'
'   lFlags
'   ------
'   lFlags is an action flag. Use the
'   INTERNET_FLAG_RELOAD flag to instruct the
'   function to retrieve data from the server
'   even if it is locally cached.
'
'   lContext
'   --------
'   lContext is an application-defined value.
'   For the simple examples presented here,
'   just use 0.
'
'===================================================

Private Declare Function HttpOpenRequest Lib "wininet.dll" Alias _
      "HttpOpenRequestA" (ByVal hHttpSession As Long, ByVal sVerb As _
      String, ByVal sObjectName As String, ByVal sVersion As String, _
      ByVal sReferer As String, ByVal something As Long, ByVal lFlags _
      As Long, ByVal lContext As Long) As Long

'===================================================
'   HttpAddRequestHeaders
'   =====================
'
'   The HttpAddRequestHeaders function allows
'   you to add or modify headers before sending
'   them to the server.
'
'   hHttpRequest
'   ------------
'   hHttpRequest is the handle returned from the
'   call to HttpOpenRequest. This is contrary to
'   the WinInet SDK documentation, which indicates
'   that this is the handle returned from the call
'   to InternetConnect. So please note that you
'   should pass the handle returned from the
'   HttpOpenRequest call, or the function call will
'   return NULL.
'
'   sHeaders
'   --------
'   The sHeaders parameter contains the headers
'   you want to append to the request. Each header
'   must be terminated with a carriage return/line
'   feed (CR/LF) pair. You can send more than one
'   header with this string. A common header that
'   you can send is the "If-Modified-Since" header,
'   which will add conditional behavior to the GET
'   verb specified in the HttpOpenRequest function.
'   After opening a request, you can append a
'   header that tells the server not to return the
'   contents of the file in the body of the message
'   if the file has not been modified since a certain
'   date (see note below). This is how a Web browser
'   could manage caching pages: Before sending the
'   request, the client looks to see if it already
'   has a copy of the page. If it does, it checks
'   the time that it was loaded into the cache
'   directory. The browser then appends the
'   appropriate request header. If the file has not
'   been updated since the last time it was cached
'   on the client's machine, the server will respond
'   with status code 304, which indicates
'   "not modified." The browser then displays the
'   page by reading it from the cache instead of
'   bringing it across the wire.
'
'   Note:   All HTTP/1.0 dates are represented as
'   Greenwich Mean Time (GMT). An HTTP Date in
'   augmented Backus-Naur Form (BNF) is:
'
'   HTTP-date      = rfc1123-date | rfc850-date | asctime-date
'   rfc1123-date   = wkday "," SP date1 SP time SP "GMT"
'   rfc850-date    = weekday "," SP date2 SP time SP "GMT"
'   asctime-date   = wkday SP date3 SP time SP 4DIGIT
'   date1          = 2DIGIT SP month SP 4DIGIT
'                    ; day month year (e.g., 02 Jun 1982)
'   date2          = 2DIGIT "-" month "-" 2DIGIT
'                    ; day-month-year (e.g., 02-Jun-82)
'   date3          = month SP ( 2DIGIT | ( SP 1DIGIT ))
'                    ; month day (e.g., Jun  2)
'   time           = 2DIGIT ":" 2DIGIT ":" 2DIGIT
'                    ; 00:00:00 - 23:59:59
'   wkday          = "Mon" | "Tue" | "Wed"
'                  | "Thu" | "Fri" | "Sat" | "Sun"
'   weekday        = "Monday" | "Tuesday" | "Wednesday"
'                  | "Thursday" | "Friday" | "Saturday" | "Sunday"
'   month          = "Jan" | "Feb" | "Mar" | "Apr"
'                  | "May" | "Jun" | "Jul" | "Aug"
'                  | "Sep" | "Oct" | "Nov" | "Dec"
'
'   (See also: http://www.w3.org/)
'
'   lHeadersLength
'   --------------
'   lHeadersLength is the length of the sHeaders string.
'
'   lModifiers
'   ----------
'   lModifiers are several different flags you can
'   use to modify the behavior of the function. The
'   constants are defined above. You can add the
'   flags together to get the desired action. Here
'   are three flags that you can use:
'
'   HTTP_ADDREQ_FLAG_REPLACE is used to remove
'       existing headers or overwrite them with
'       new ones. If the header already exists
'       and the value you are specifying is empty,
'       that header will be removed. If you are
'       supplying a new value to an existing
'       header, this new value will replace
'       the old value.
'
'   HTTP_ADDREQ_FLAG_ADD adds the header to the
'       request message if it does not already exist.
'
'   HTTP_ADDREQ_FLAG_ADD_IF_NEW adds a header.
'       If a header already exists, however,
'       the function will return an error.
'
'   There are three other flags that can be used
'   if you are interested in coalescing headers.
'   Refer to the WinInet SDK documentation
'   (http://www.microsoft.com/intdev/sdk/docs/wininet)
'   if this interests you.
'
'
'===================================================

Private Declare Function HttpAddRequestHeaders Lib "wininet.dll" Alias _
      "HttpAddRequestHeadersA" (ByVal hHttpRequest As Long, _
      ByVal sHeaders As String, ByVal lHeadersLength As Long, ByVal _
      lModifiers As Long) As Integer
Private Const HTTP_ADDREQ_FLAG_ADD_IF_NEW = &H10000000
Private Const HTTP_ADDREQ_FLAG_ADD = &H20000000
Private Const HTTP_ADDREQ_FLAG_REPLACE = &H80000000

'===================================================
'   HttpSendRequest
'   ===============
'
'   After you have opened your request and
'   added request headers, if any, you are ready
'   to send your request to the server. The
'   HttpSendRequest function does exactly what
'   you would expect: It sends your request.
'   It also lets you append additional request
'   headers before you send it, so you do not
'   have to make an additional call to
'   HttpAddRequestHeaders. (I would, however,
'   recommend using HttpAddRequestHeaders so
'   you can explicitly check the return value
'   to ensure that the request headers were
'   successfully appended or modified.) After
'   the request is sent, the status line,
'   response headers, and any entity header
'   meta-information are read. You can interrogate
'   these with the HttpQueryInfo function, which
'   is discussed below.
'
'   The function will return TRUE if the request
'   was successfully sent.
'
'   hHttpRequest
'   ------------
'   hHttpRequest is the handle returned
'   from HttpOpenRequest.
'
'   sHeaders and lHeadersLength
'   ---------------------------
'   sHeaders and lHeadersLength are two
'   parameters that can be used for appending
'   additional headers to the request, instead
'   of making a separate call to
'   HttpAddRequestHeaders.
'
'   sOptional and lOptionalLength
'   -----------------------------
'   sOptional and lOptionalLength are parameters
'   for sending additional data along with the
'   request. This is not used for GET requests,
'   so we leave these two parameters NULL in
'   our sample application.
'
'===================================================

Private Declare Function HttpSendRequest Lib "wininet.dll" Alias _
      "HttpSendRequestA" (ByVal hHttpRequest As Long, ByVal sHeaders _
      As String, ByVal lHeadersLength As Long, sOptional As Any, _
      ByVal lOptionalLength As Long) As Integer

'===================================================
'   HttpQueryInfo
'   =============
'
'   This function lets you look at the status
'   line, response headers, and entity header
'   meta-information returned from the request
'   sent by HttpSendRequest.
'
'   The function returns TRUE if successful.
'
'   hHttpRequest
'   ------------
'   As usual, hHttpRequest is the handle returned
'   from the call to HttpOpenRequest.
'
'   lInfoLevel
'   ----------
'   lInfoLevel is the response header or
'   entity-header metainformation we are
'   interested in interrogating, along with a
'   request modifier flag. If you do not include
'   an optional flag, the response headers will
'   be queried. The sample application contains
'   a number of constants you can use for
'   querying these attributes.
'   HTTP_QUERY_CONTENT_LENGTH is one such constant
'   that can be used to get metainformation about
'   the resource identified in the request.
'
'   sBuffer
'   -------
'   sBuffer is the buffer into which the results
'   are copied. Notice that the declaration
'   specifies that the parameter will be passed
'   by reference. When you actually call the
'   function, you can call it by value with the
'   ByVal keyword in front of the variable in
'   the function call.
'
'   lBufferlength
'   -------------
'   lBufferlength is the length of the buffer.
'   When the function successfully returns
'   strings, this value will be set to the
'   actual length of the response that was
'   copied into the buffer. If the function
'   fails, this parameter indicates the size
'   that the buffer must actually be in order
'   to receive the string.
'
'   lIndex
'   ------
'
'   lIndex is used when you have multiple
'   headers with the same name. Leave this set
'   to 0 unless you have some need for using
'   multiple headers.
'
'===================================================

Private Declare Function HttpQueryInfo Lib "wininet.dll" _
   Alias "HttpQueryInfoA" (ByVal hHttpRequest As Long, _
   ByVal lInfoLevel As Long, ByRef sBuffer As Any, _
   ByRef lBufferLength As Long, ByRef lIndex As Long) As Integer
Private Const HTTP_QUERY_FLAG_REQUEST_HEADERS = &H80000000

'---------------------------------------------------
'   These constants come from :
'   http://msdn.microsoft.com/workshop/networking/wininet/reference/constants/queryinfo_flags.asp
'
Private Const HTTP_QUERY_ACCEPT = 24 ' Retrieves the acceptable media types for the response.
Private Const HTTP_QUERY_ACCEPT_CHARSET = 25 ' Retrieves the acceptable character sets for the response.
Private Const HTTP_QUERY_ACCEPT_ENCODING = 26 ' Retrieves the acceptable content-coding values for the response.
Private Const HTTP_QUERY_ACCEPT_LANGUAGE = 27 ' Retrieves the acceptable natural languages for the response.
Private Const HTTP_QUERY_ACCEPT_RANGES = 42 ' Retrieves the types of range requests that are accepted for a resource.
Private Const HTTP_QUERY_AGE = 48 ' Retrieves the Age response-header field, which contains the sender's estimate of the amount of time since the response was generated at the origin server.
Private Const HTTP_QUERY_ALLOW = 7 ' Receives the HTTP verbs supported by the server.
Private Const HTTP_QUERY_AUTHORIZATION = 28 ' Retrieves the authorization credentials used for a request.
Private Const HTTP_QUERY_CACHE_CONTROL = 49 ' Retrieves the cache control directives.
Private Const HTTP_QUERY_CONNECTION = 23 ' Retrieves any options that are specified for a particular connection and must not be communicated by proxies over further connections.
Private Const HTTP_QUERY_CONTENT_BASE = 50 ' Retrieves the base URI (Uniform Resource Identifier) for resolving relative URLs within the entity.
Private Const HTTP_QUERY_CONTENT_DESCRIPTION = 4 ' Obsolete. Maintained for legacy application compatibility only.
Private Const HTTP_QUERY_CONTENT_DISPOSITION = 47 ' Obsolete. Maintained for legacy application compatibility only.
Private Const HTTP_QUERY_CONTENT_ENCODING = 29 ' Retrieves any additional content codings that have been applied to the entire resource.
Private Const HTTP_QUERY_CONTENT_ID = 3 ' Retrieves the content identification.
Private Const HTTP_QUERY_CONTENT_LANGUAGE = 6 ' Retrieves the language that the content is in.
Private Const HTTP_QUERY_CONTENT_LENGTH = 5 ' Retrieves the size of the resource, in bytes.
Private Const HTTP_QUERY_CONTENT_LOCATION = 51 ' Retrieves the resource location for the entity enclosed in the message.
Private Const HTTP_QUERY_CONTENT_MD5 = 52 ' Retrieves an MD5 digest of the entity-body for the purpose of providing an end-to-end message integrity check (MIC) for the entity-body. For more information, see RFC1864, The Content-MD5 Header Field, at http://ftp.isi.edu/in-notes/rfc1864.txt .
Private Const HTTP_QUERY_CONTENT_RANGE = 53 ' Retrieves the location in the full entity-body where the partial entity-body should be inserted and the total size of the full entity-body.
Private Const HTTP_QUERY_CONTENT_TRANSFER_ENCODING = 2 ' Receives the additional content coding that has been applied to the resource.
Private Const HTTP_QUERY_CONTENT_TYPE = 1 ' Receives the content type of the resource (such as text/html).
Private Const HTTP_QUERY_COOKIE = 44 ' Retrieves any cookies associated with the request.
Private Const HTTP_QUERY_COST = 15 ' No longer supported.
Private Const HTTP_QUERY_CUSTOM = 65535 ' Causes HttpQueryInfo to search for the header name specified in lpvBuffer and store the header information in lpvBuffer.
Private Const HTTP_QUERY_DATE = 9 ' Receives the date and time at which the message was originated.
Private Const HTTP_QUERY_DERIVED_FROM = 14 ' No longer supported.
Private Const HTTP_QUERY_ECHO_HEADERS = 73 ' Not currently implemented.
Private Const HTTP_QUERY_ECHO_HEADERS_CRLF = 74 ' Not currently implemented.
Private Const HTTP_QUERY_ECHO_REPLY = 72 ' Not currently implemented.
Private Const HTTP_QUERY_ECHO_REQUEST = 71 ' Not currently implemented.
Private Const HTTP_QUERY_ETAG = 54 ' Retrieves the entity tag for the associated entity.
Private Const HTTP_QUERY_EXPECT = 68 ' Retrieves the Expect header, which indicates whether the client application should expect 100 series responses.
Private Const HTTP_QUERY_EXPIRES = 10 ' Receives the date and time after which the resource should be considered outdated.
Private Const HTTP_QUERY_FORWARDED = 30 ' Obsolete. Maintained for legacy application compatibility only.
Private Const HTTP_QUERY_FROM = 31 ' Retrieves the e-mail address for the human user who controls the requesting user agent if the From header is given.
Private Const HTTP_QUERY_HOST = 55 ' Retrieves the Internet host and port number of the resource being requested.
Private Const HTTP_QUERY_IF_MATCH = 56 ' Retrieves the contents of the If-Match request-header field.
Private Const HTTP_QUERY_IF_MODIFIED_SINCE = 32 ' Retrieves the contents of the If-Modified-Since header.
Private Const HTTP_QUERY_IF_NONE_MATCH = 57 ' Retrieves the contents of the If-None-Match request-header field.
Private Const HTTP_QUERY_IF_RANGE = 58 ' Retrieves the contents of the If-Range request-header field. This header allows the client application to check if the entity related to a partial copy of the entity in the client application's cache has not been updated. If the entity has not been updated, send the parts that the client application is missing. If the entity has been updated, send the entire updated entity.
Private Const HTTP_QUERY_IF_UNMODIFIED_SINCE = 59 ' Retrieves the contents of the If-Unmodified-Since request-header field.
Private Const HTTP_QUERY_LAST_MODIFIED = 11 ' Receives the date and time at which the server believes the resource was last modified.
Private Const HTTP_QUERY_LINK = 16 ' Obsolete. Maintained for legacy application compatibility only.
Private Const HTTP_QUERY_LOCATION = 33 ' Retrieves the absolute URI (Uniform Resource Identifier) used in a Location response-header.
Private Const HTTP_QUERY_MAX = 75 ' Not a query flag. Indicates the maximum value of an Private Const HTTP_QUERY_* value.
Private Const HTTP_QUERY_MAX_FORWARDS = 60 ' Retrieves the number of proxies or gateways that can forward the request to the next inbound server.
Private Const HTTP_QUERY_MESSAGE_ID = 12 ' No longer supported.
Private Const HTTP_QUERY_MIME_VERSION = 0 ' Receives the version of the MIME protocol that was used to construct the message.
Private Const HTTP_QUERY_ORIG_URI = 34 ' Obsolete. Maintained for legacy application compatibility only.
Private Const HTTP_QUERY_PRAGMA = 17 ' Receives the implementation-specific directives that might apply to any recipient along the request/response chain.
Private Const HTTP_QUERY_PROXY_AUTHENTICATE = 41 ' Retrieves the authentication scheme and realm returned by the proxy.
Private Const HTTP_QUERY_PROXY_AUTHORIZATION = 61 ' Retrieves the header that is used to identify the user to a proxy that requires authentication. This header can only be retrieved before the request is sent to the server.
Private Const HTTP_QUERY_PROXY_CONNECTION = 69 ' Retrieves the Proxy-Connection header.
Private Const HTTP_QUERY_PUBLIC = 8 ' Receives methods available at this server.
Private Const HTTP_QUERY_RANGE = 62 ' Retrieves the byte range of an entity.
Private Const HTTP_QUERY_RAW_HEADERS = 21 ' Receives all the headers returned by the server. Each header is terminated by "\0". An additional "\0" terminates the list of headers.
Private Const HTTP_QUERY_RAW_HEADERS_CRLF = 22 ' Receives all the headers returned by the server. Each header is separated by a carriage return/line feed (CR/LF) sequence.
Private Const HTTP_QUERY_REFERER = 35 ' Receives the URI (Uniform Resource Identifier) of the resource where the requested URI was obtained.
Private Const HTTP_QUERY_REFRESH = 46 ' Obsolete. Maintained for legacy application compatibility only.
Private Const HTTP_QUERY_REQUEST_METHOD = 45 ' Receives the HTTP verb that is being used in the request, typically GET or POST.
Private Const HTTP_QUERY_RETRY_AFTER = 36 ' Retrieves the amount of time the service is expected to be unavailable.
Private Const HTTP_QUERY_SERVER = 37 ' Retrieves information about the software used by the origin server to handle the request.
Private Const HTTP_QUERY_SET_COOKIE = 43 ' Receives the value of the cookie set for the request.
Private Const HTTP_QUERY_STATUS_CODE = 19 ' Receives the status code returned by the server. For a list of possible values, see HTTP Status Codes.
Private Const HTTP_QUERY_STATUS_TEXT = 20 ' Receives any additional text returned by the server on the response line.
Private Const HTTP_QUERY_TITLE = 38 ' Obsolete. Maintained for legacy application compatibility only.
Private Const HTTP_QUERY_TRANSFER_ENCODING = 63 ' Retrieves the type of transformation that has been applied to the message body so it can be safely transferred between the sender and recipient.
Private Const HTTP_QUERY_UNLESS_MODIFIED_SINCE = 70 ' Retrieves the Unless-Modified-Since header.
Private Const HTTP_QUERY_UPGRADE = 64 ' Retrieves the additional communication protocols that are supported by the server.
Private Const HTTP_QUERY_URI = 13 ' Receives some or all of the Uniform Resource Identifiers (URIs) by which the Request-URI resource can be identified.
Private Const HTTP_QUERY_USER_AGENT = 39 ' Retrieves information about the user agent that made the request.
Private Const HTTP_QUERY_VARY = 65 ' Retrieves the header that indicates that the entity was selected from a number of available representations of the response using server-driven negotiation.
Private Const HTTP_QUERY_VERSION = 18 ' Receives the last response code returned by the server.
Private Const HTTP_QUERY_VIA = 66 ' Retrieves the intermediate protocols and recipients between the user agent and the server on requests, and between the origin server and the client on responses.
Private Const HTTP_QUERY_WARNING = 67 ' Retrieves additional information about the status of a response that might not be reflected by the response status code.
Private Const HTTP_QUERY_WWW_AUTHENTICATE = 40 ' Retrieves the authentication scheme and realm returned by the server.

'===================================================
'   InternetReadFile
'   ================
'
'   InternetReadFile is the function that will
'   start reading the file once you have sent
'   the request and decided to actually retrieve
'   the body content.
'
'   hFile
'   -----
'   hFile is the handle returned from the call
'   to HttpOpenRequest.
'
'   sBuffer
'   -------
'   sBuffer is the buffer.
'
'   lNumberOfBytesToRead
'   --------------------
'   lNumberOfBytesToRead is the number of bytes
'   returned; lNumberOfBytesRead is the actual
'   number of bytes that were read.
'
'===================================================

Private Declare Function InternetReadFile Lib "wininet.dll" _
      (ByVal hFile As Long, ByVal sBuffer As String, _
      ByVal lNumberOfBytesToRead As Long, lNumberOfBytesRead As Long) _
      As Integer

'===================================================
'   InternetCloseHandle
'   ===================
'
'   The InternetCloseHandle function closes
'   handles and frees resources associated
'   with WinInet functions. The function
'   returns TRUE if the handle is successfully
'   closed.
'
'   hInet
'   -----
'   hInet is the handle you want to close.
'
'===================================================

Private Declare Function InternetCloseHandle Lib "wininet.dll" _
   (ByVal hInet As Long) As Integer

'================================================
'   P R O P E R T I E S
'================================================

'================================================
'
'   PUBLIC InternetOpenType
'
'   Sets / Returns the Internet Open Type
'   flags for InternetOpen
'
Public Property Let InternetOpenType(ByVal InternetOpenType As WININET_InternetOpen_Types)
Attribute InternetOpenType.VB_Description = "Type Flag to be used in the wininet.dll ""InternetOpen"" function."
Attribute InternetOpenType.VB_HelpID = 11550
    menmInternetOpenType = InternetOpenType
End Property
Public Property Get InternetOpenType() As WININET_InternetOpen_Types
    InternetOpenType = menmInternetOpenType
End Property

'================================================
'
'   PUBLIC InternetOpenFlags
'
'   Sets / Returns the Internet Open Flags
'   flags for InternetOpen
'
Public Property Let InternetOpenFlags(ByVal InternetOpenFlags As Long)
Attribute InternetOpenFlags.VB_Description = "Flags to be used in the wininet.dll ""InternetOpen"" function."
Attribute InternetOpenFlags.VB_HelpID = 11500
    mlngInternetOpenFlags = InternetOpenFlags
End Property
Public Property Get InternetOpenFlags() As Long
    InternetOpenFlags = mlngInternetOpenFlags
End Property

'================================================
'
'   PUBLIC HTTPOpenRequestFlags
'
'   Sets / Returns the HTTP Open Request Flags
'   flags for HTTPOpenRequest
'
Public Property Let HTTPOpenRequestFlags(ByVal HTTPOpenRequestFlags As Long)
Attribute HTTPOpenRequestFlags.VB_Description = "Flags to be used in the wininet.dll ""HTTPOpenRequest"" function."
Attribute HTTPOpenRequestFlags.VB_HelpID = 11400
    mlngHTTPOpenRequestFlags = HTTPOpenRequestFlags
End Property
Public Property Get HTTPOpenRequestFlags() As Long
    HTTPOpenRequestFlags = mlngHTTPOpenRequestFlags
End Property

'================================================
'
'   PUBLIC UserAgent
'
'   Sets / Returns the User Agent
'
Public Property Let UserAgent(ByVal UserAgent As String)
Attribute UserAgent.VB_Description = "The user agent to be sent as part of the call to the remote server."
Attribute UserAgent.VB_HelpID = 11750
    mstrUserAgent = UserAgent
End Property
Public Property Get UserAgent() As String
    UserAgent = mstrUserAgent
End Property

'================================================
'
'   PUBLIC HostName
'
'   Sets / Returns the Host Name
'
Public Property Let HostName(ByVal HostName As String)
Attribute HostName.VB_Description = "The name of the remote host."
Attribute HostName.VB_HelpID = 11250
    mstrHostName = HostName
End Property
Public Property Get HostName() As String
    HostName = mstrHostName
End Property

'================================================
'
'   PUBLIC HostPort
'
'   Sets / Returns the Host Port
'
Public Property Let HostPort(ByVal HostPort As Long)
Attribute HostPort.VB_Description = "The HTTP port of the remote host."
Attribute HostPort.VB_HelpID = 11300
    If HostPort < 1 Then
        Err.Raise vbXML_ERROR_PORTOUTOFRANGE, _
                  MODULETITLE & "HostPort", _
                  "Trying to set HostPort to value of " + HostPort + " - " + ginsUtility.GetErrorCode(vbXML_ERROR_PORTOUTOFRANGE), _
                  App.HelpFile, _
                  ginsUtility.ErrorHelpContext(vbXML_ERROR_PORTOUTOFRANGE)
    ElseIf HostPort > 65535 Then
        Err.Raise vbXML_ERROR_PORTOUTOFRANGE, _
                  MODULETITLE & "HostPort", _
                  "Trying to set HostPort to value of " + HostPort + " - " + ginsUtility.GetErrorCode(vbXML_ERROR_PORTOUTOFRANGE), _
                  App.HelpFile, _
                  ginsUtility.ErrorHelpContext(vbXML_ERROR_PORTOUTOFRANGE)
    End If
    mlngHostPort = HostPort
End Property
Public Property Get HostPort() As Long
    HostPort = mlngHostPort
End Property

'================================================
'
'   PUBLIC URL
'
'   Sets / Returns the URI
'
Public Property Let HostURI(ByVal HostURI As String)
Attribute HostURI.VB_Description = "The URI of the document on the remote host."
Attribute HostURI.VB_HelpID = 11350
    mstrHostURI = HostURI
End Property
Public Property Get HostURI() As String
    HostURI = mstrHostURI
End Property


'================================================
'
'   PUBLIC Username
'
'   Sets / Returns the username credentials
'
'   Chris Carter added credentials support
'   24/4/2004
'
Public Property Let Username(Username As String)
Attribute Username.VB_Description = "The user name for HTTP basic authentication."
Attribute Username.VB_HelpID = 11800
    mstrUsername = Username
End Property
Public Property Get Username() As String
    Username = mstrUsername
End Property

'================================================
'
'   PUBLIC Password
'   Sets / Returns the password credentials
'
'   Chris Carter added credentials support
'   24/4/2004
'
Public Property Let Password(Password As String)
Attribute Password.VB_Description = "The password for HTTP basic authentication."
Attribute Password.VB_HelpID = 11850
    mstrPassword = Password
End Property
Public Property Get Password() As String
    Password = mstrPassword
End Property

'================================================
'
'   PUBLIC HeadersToSend
'
'   Read-Only
'
'   Returns the HTTP Headers to be appended
'   to the HTTP Call.
'
Public Property Get HeadersToSend() As XMLHTTPHeaders
Attribute HeadersToSend.VB_Description = "HTTP headers to be sent to the remove server."
Attribute HeadersToSend.VB_HelpID = 11200
    Set HeadersToSend = minsHeadersToSend
End Property

'================================================
'
'   PUBLIC HeadersReceived
'
'   Read-Only
'
'   Returns the HTTP Headers appended
'   to the HTTP Response.
'
Public Property Get HeadersReceived() As XMLHTTPHeaders
Attribute HeadersReceived.VB_Description = "HTTP headers received from the remote server."
Attribute HeadersReceived.VB_HelpID = 11150
    Set HeadersReceived = minsHeadersReceived
End Property

'================================================
'
'   PUBLIC RequestType
'
'   Sets / Returns the HTTP Request Type.
'
Public Property Let RequestType(RequestType As HTTP_Request_Types)
Attribute RequestType.VB_Description = "The HTTP request type."
Attribute RequestType.VB_HelpID = 11600
    menmRequestType = RequestType
End Property
Public Property Get RequestType() As HTTP_Request_Types
    RequestType = menmRequestType
End Property

'================================================
'
'   PUBLIC HTTPVersion
'
'   Sets / Returns the HTTP Version.
'
Public Property Let HTTPVersion(HTTPVersion As HTTP_Versions)
Attribute HTTPVersion.VB_Description = "The HTTP version of the call to the remote host."
Attribute HTTPVersion.VB_HelpID = 11450
    menmHTTPVersion = HTTPVersion
End Property
Public Property Get HTTPVersion() As HTTP_Versions
    HTTPVersion = menmHTTPVersion
End Property

'================================================
'
'   PUBLIC DataToPost
'
'   Sets / Returns the Data To Post.
'
Public Property Let DataToPost(DataToPost As String)
Attribute DataToPost.VB_Description = "Data to be POSTed to the remote server."
Attribute DataToPost.VB_HelpID = 11100
    mstrDataToPost = DataToPost
End Property
Public Property Get DataToPost() As String
    DataToPost = mstrDataToPost
End Property

'================================================
'
'   PUBLIC DataReturned
'
'   Read-Only
'
'   Returns the Data returned by the call.
'
Public Property Get DataReturned() As String
Attribute DataReturned.VB_Description = "Data returned from the remote server by the call."
Attribute DataReturned.VB_HelpID = 11050
    DataReturned = mstrDataReturned
End Property

'================================================
'
'   PUBLIC URL
'
'   Sets / Returns the URL.
'
Public Property Let URL(URL As String)
Attribute URL.VB_Description = "The URL of the call to the remote server."
Attribute URL.VB_HelpID = 11700
    If LCase$(Left$(URL, 7)) = "http://" Then
        URL = Right$(URL, Len(URL) - 7)
    End If
    mstrHostName = Left$(URL, InStr(URL, "/") - 1)
    mstrHostURI = Right$(URL, Len(URL) - Len(mstrHostName))
End Property
Public Property Get URL() As String
    URL = "http://" & mstrHostName & "/" & mstrHostURI
End Property

'================================================
'   M E T H O D S
'================================================

'===================================================
'
'   PRIVATE Class_Initialize
'
'   Standard Class Constructor
'
Private Sub Class_Initialize()

    mlngHTTPOpenRequestFlags = INTERNET_FLAG_RELOAD
    mlngInternetOpenFlags = 0
    menmInternetOpenType = INTERNET_OPEN_TYPE_PRECONFIG
    mlngHostPort = INTERNET_DEFAULT_HTTP_PORT
    menmHTTPVersion = HTTP_1_0
    menmRequestType = HTTP_GET
    mstrUserAgent = APPNAME + " v" & App.Major & "." & App.Minor & "." & App.Revision

'---------------------------------------------------
'   Chris Carter added credentials support
'   24/4/2004
    mstrUsername = vbNullString
    mstrPassword = vbNullString
    
End Sub

'===================================================
'
'   PRIVATE WININET_InternetOpen
'
'   Abstracts WININET "InternetOpenA" only taking
'   a UserAgent as a parameter.
'
'   Returns a handle to the internet session
'
'   IF there is a problem with the call
'   it raises this as an error
'
Private Function WININET_InternetOpen() As Long
    
'---------------------------------------------------
'   The return code, a handle to the internet session
    Dim llngInternetSessionHandle As Long
    Dim lstrUserAgent As String
    Dim lstrProxyName As String
    Dim lstrProxyBypass As String
    
    lstrUserAgent = IIf(mstrUserAgent <> "", mstrUserAgent, vbNullString)
    lstrProxyName = IIf(mstrProxyName <> "", mstrProxyName, vbNullString)
    lstrProxyBypass = IIf(mstrProxyBypass <> "", mstrProxyBypass, vbNullString)
    
    llngInternetSessionHandle = InternetOpen(lstrUserAgent, _
                                             CLng(menmInternetOpenType), _
                                             lstrProxyName, _
                                             lstrProxyBypass, _
                                             mlngInternetOpenFlags)

'---------------------------------------------------
'   If there is an error the handle will be zero
'   Find the error and throw it
    If llngInternetSessionHandle = 0 Then
        Err.Raise vbXML_ERROR_WININETERROR, _
                  MODULETITLE & "#WININET_InternetOpen", _
                  getWININETError(Err.LastDllError), _
                  App.HelpFile, _
                  ginsUtility.ErrorHelpContext(vbXML_ERROR_WININETERROR)
    Else
        WININET_InternetOpen = llngInternetSessionHandle
    End If
End Function

'===================================================
'
'   PRIVATE WININET_InternetConnect
'
'   Abstracts WININET "InternetConnectA" only taking
'   a handle to the session, the host name and port
'
'   Returns a handle to the internet connection
'
'   IF there is a problem with the call
'   it raises this as an error
'
'---------------------------------------------------
'   Chris Carter added credentials support
'   24/4/2004
'
Private Function WININET_InternetConnect(ByVal InternetSessionHandle As Long) As Long

'---------------------------------------------------
'   The return code, a handle to the internet connection
    Dim llngInternetConnectHandle As Long
    
    llngInternetConnectHandle = InternetConnect(InternetSessionHandle, _
                                                mstrHostName, _
                                                mlngHostPort, _
                                                mstrUsername, _
                                                mstrPassword, _
                                                INTERNET_SERVICE_HTTP, 0, 0)

'---------------------------------------------------
'   If there is an error the handle will be zero
'   Find the error and throw it
    If llngInternetConnectHandle = 0 Then
        Err.Raise vbXML_ERROR_WININETERROR, _
                  MODULETITLE & "#WININET_InternetConnect", _
                  getWININETError(Err.LastDllError), _
                  App.HelpFile, _
                  ginsUtility.ErrorHelpContext(vbXML_ERROR_WININETERROR)
    Else
        WININET_InternetConnect = llngInternetConnectHandle
    End If
End Function

'===================================================
'
'   PRIVATE WININET_HTTPOpenRequest
'
'   Abstracts WININET "HttpOpenRequestA" only taking
'   a handle to the connection, the host URI and port
'
'   Returns a handle to the HTTP Open Request
'
'   IF there is a problem with the call
'   it raises this as an error
'
Private Function WININET_HTTPOpenRequest(ByVal InternetConnectHandle As Long) As Long

    Const METHODTITLE = MODULETITLE & "#WININET_HTTPOpenRequest"
    
'---------------------------------------------------
'   The return code, a handle to the HTTP Open Request
    Dim llngHTTPOpenRequestHandle As Long
    
'---------------------------------------------------
'   The Request Type
    Dim lstrRequestType As String

'---------------------------------------------------
'   The HTTP Version
    Dim lstrHTTPVersion As String
    
    Select Case menmRequestType
'    Case HTTP_OPTIONS
'        lstrRequestType = "OPTIONS"
    Case HTTP_GET
        lstrRequestType = "GET"
'    Case HTTP_HEAD
'        lstrRequestType = "HEAD"
    Case HTTP_POST
        lstrRequestType = "POST"
'    Case HTTP_PUT
'        lstrRequestType = "PUT"
'    Case HTTP_DELETE
'        lstrRequestType = "DELETE"
'    Case HTTP_TRACE
'        lstrRequestType = "TRACE"
'    Case HTTP_CONNECT
'        lstrRequestType = "CONNECT"
    Case Else
        Err.Raise vbXML_ERROR_UNKNOWNREQUESTTYPE, _
                  METHODTITLE, _
                  "Unknown request type '" & menmRequestType & "'", _
                  App.HelpFile, _
                  ginsUtility.ErrorHelpContext(vbXML_ERROR_UNKNOWNREQUESTTYPE)
    End Select
    
    Select Case menmHTTPVersion
    Case HTTP_1_0
        lstrHTTPVersion = "HTTP/1.0"
    Case HTTP_1_1
        lstrHTTPVersion = "HTTP/1.1"
    Case Else
        Err.Raise vbXML_ERROR_UNKNOWNPROTOCOLTYPE, _
                  METHODTITLE, _
                  "Unknown protocol type '" & menmHTTPVersion & "'", _
                  App.HelpFile, _
                  ginsUtility.ErrorHelpContext(vbXML_ERROR_UNKNOWNPROTOCOLTYPE)
    End Select
    
    llngHTTPOpenRequestHandle = HttpOpenRequest(InternetConnectHandle, _
                                                lstrRequestType, _
                                                mstrHostURI, _
                                                lstrHTTPVersion, _
                                                vbNullString, _
                                                0, _
                                                mlngHTTPOpenRequestFlags, _
                                                0)
    
'---------------------------------------------------
'   If there is an error the handle will be zero
'   Find the error and throw it
    If llngHTTPOpenRequestHandle = 0 Then
        Err.Raise vbXML_ERROR_WININETERROR, _
                  MODULETITLE & "#WININET_HTTPOpenRequest", _
                  getWININETError(Err.LastDllError), _
                  App.HelpFile, _
                  ginsUtility.ErrorHelpContext(vbXML_ERROR_WININETERROR)
    Else
        WININET_HTTPOpenRequest = llngHTTPOpenRequestHandle
    End If
End Function

'===================================================
'
'   PRIVATE WININET_HTTPSendRequest
'
'   Abstracts WININET "HttpSendRequestA" only taking
'   a handle to the HTTP Open Request, the headers
'   and the XML to be sent
'
'   IF there is a problem with the call
'   it raises this as an error
'
Private Sub WININET_HTTPSendRequest(ByVal HTTPOpenRequestHandle As Long)

    Dim lstrHeaders As String        ' The Headers to send
    Dim llngHeadersLength As Long    ' Length of the Headers
    Dim llngDataToSendLength As Long ' Length of the data to send (only valid if post)
    Dim lblnReturn As Boolean        ' Return value from function
    
    lstrHeaders = minsHeadersToSend.HeadersString
    llngHeadersLength = Len(lstrHeaders)
    llngDataToSendLength = Len(mstrDataToPost)

'---------------------------------------------------
'   Call the routine
    If mstrDataToPost <> "" Then
        If lstrHeaders <> "" Then
            lblnReturn = HttpSendRequest(HTTPOpenRequestHandle, _
                                         lstrHeaders, _
                                         llngHeadersLength, _
                                         ByVal mstrDataToPost, _
                                         llngDataToSendLength)
        Else
            lblnReturn = HttpSendRequest(HTTPOpenRequestHandle, _
                                         vbNullString, _
                                         0, _
                                         ByVal mstrDataToPost, _
                                         llngDataToSendLength)
        End If
    Else
        If lstrHeaders <> "" Then
            lblnReturn = HttpSendRequest(HTTPOpenRequestHandle, _
                                         lstrHeaders, _
                                         llngHeadersLength, _
                                         0, _
                                         0)
        Else
            lblnReturn = HttpSendRequest(HTTPOpenRequestHandle, _
                                         vbNullString, _
                                         0, _
                                         0, _
                                         0)
        End If
    End If

'---------------------------------------------------
'   If the call returns false then there has
'   been a problem. Find the error and throw it
    If lblnReturn = False Then
        Err.Raise vbXML_ERROR_WININETERROR, _
                  MODULETITLE & "#WININET_HTTPSendRequest", _
                  getWININETError(Err.LastDllError), _
                  App.HelpFile, _
                  ginsUtility.ErrorHelpContext(vbXML_ERROR_WININETERROR)
    End If
End Sub

'===================================================
'
'   PRIVATE WININET_HTTPHeaderStatusCode
'
'   Abstracts WININET "HttpQueryInfoA" only taking
'   a handle to the HTTP Open Request
'
'   Returns the HTTP Status Code
'
'   IF there is a problem with the call
'   it raises this as an error
'
Private Function WININET_HTTPHeaderStatusCode(ByVal HTTPOpenRequestHandle As Long) As Long
    
    Dim lstrBuffer As String     ' The buffer to return information in
    Dim llngBufferLength As Long ' The length of the data in the return string
    Dim lblnReturn As Boolean    ' Return value from function
    
'---------------------------------------------------
'   Set the buffer up to a length easily
'   bigger than the headers
    lstrBuffer = String$(1024, 0)
    llngBufferLength = Len(lstrBuffer)
    
'---------------------------------------------------
'   Call the routine
    lblnReturn = HttpQueryInfo(HTTPOpenRequestHandle, _
                               HTTP_QUERY_STATUS_CODE, _
                               ByVal lstrBuffer, llngBufferLength, 0)

'---------------------------------------------------
'   If the call returns false then there has
'   been a problem. Find the error and throw it
    If lblnReturn = False Then
        Err.Raise vbXML_ERROR_WININETERROR, _
                  MODULETITLE & "#WININET_HTTPHeaderStatusCode", _
                  getWININETError(Err.LastDllError), _
                  App.HelpFile, _
                  ginsUtility.ErrorHelpContext(vbXML_ERROR_WININETERROR)
    Else
        WININET_HTTPHeaderStatusCode = Val(Left$(lstrBuffer, llngBufferLength))
    End If
End Function

'===================================================
'
'   PRIVATE WININET_HTTPRawHeaders
'
'   Abstracts WININET "HttpQueryInfoA" only taking
'   a handle to the HTTP Open Request
'
'   Returns the raw headers
'
'   IF there is a problem with the call
'   it raises this as an error
'
Private Function WININET_HTTPRawHeaders(ByVal HTTPOpenRequestHandle As Long) As String
    
    Dim lstrBuffer As String     ' The buffer to return information in
    Dim llngBufferLength As Long ' The length of the data in the return string
    Dim lblnReturn As Boolean    ' Return value from function
    
'---------------------------------------------------
'   Set the buffer up to a length easily
'   bigger than the headers
    lstrBuffer = String$(1024, 0)
    llngBufferLength = Len(lstrBuffer)
    
'---------------------------------------------------
'   Call the routine
    lblnReturn = HttpQueryInfo(HTTPOpenRequestHandle, _
                               HTTP_QUERY_RAW_HEADERS_CRLF, _
                               ByVal lstrBuffer, llngBufferLength, 0)
    
'---------------------------------------------------
'   If the call returns false then there has
'   been a problem. Find the error and throw it
    If lblnReturn = False Then
        Err.Raise vbXML_ERROR_WININETERROR, _
                  MODULETITLE & "#WININET_HTTPRawHeaders", _
                  getWININETError(Err.LastDllError), _
                  App.HelpFile, _
                  ginsUtility.ErrorHelpContext(vbXML_ERROR_WININETERROR)
    Else
        WININET_HTTPRawHeaders = Left$(lstrBuffer, llngBufferLength)
    End If
End Function

'===================================================
'
'   PRIVATE WININET_InternetReadFile
'
'   Abstracts WININET "InternetReadFile" only
'   taking a handle to the HTTP Open Request
'
'   Returns the data in the body of the request
'
'   IF there is a problem with the call
'   it raises this as an error
'
Private Function WININET_InternetReadFile(ByVal HTTPOpenRequestHandle As Long) As String
    
    Dim lstrBuffer As String     ' The buffer to return information in
    Dim llngBufferLength As Long ' The length of the data in the return string
    Dim lstrReturn As String     ' The string to return
    Dim llngBytesRead As Long    ' The bytes read last time
    Dim lblnReturn As Boolean    ' Return value from function
    
'---------------------------------------------------
'   Set the buffer up to reasonable length
    lstrBuffer = String$(1024, 0)
    llngBufferLength = Len(lstrBuffer)

'---------------------------------------------------
'   Set the return string to nothing
    lstrReturn = ""
    
'---------------------------------------------------
'   Set the bytes read last time to zero
    llngBytesRead = 0
    
    Do
    
'---------------------------------------------------
'       Call the routine
        lblnReturn = InternetReadFile(HTTPOpenRequestHandle, _
                                      ByVal lstrBuffer, llngBufferLength, _
                                      llngBytesRead)
    
'---------------------------------------------------
'       If the call returns false then there has
'       been a problem. Find the error and throw it
        If lblnReturn = False Then
            Err.Raise vbXML_ERROR_WININETERROR, _
                      MODULETITLE & "#WININET_InternetReadFile", _
                      getWININETError(Err.LastDllError), _
                      App.HelpFile, _
                      ginsUtility.ErrorHelpContext(vbXML_ERROR_WININETERROR)
                      
'---------------------------------------------------
'       ELSE increment the response
        Else
            lstrReturn = lstrReturn & Left$(lstrBuffer, llngBytesRead)
        End If
    
    Loop Until llngBytesRead = 0
    
'---------------------------------------------------
'   Return the content of the body of the request
    WININET_InternetReadFile = lstrReturn
    
End Function

'===================================================
'
'   PRIVATE WININET_InternetCloseHandle
'
'   Abstracts WININET "InternetCloseHandle" only
'   taking the handle to close
'
'   IF there is a problem with the call
'   it raises this as an error
'
Private Sub WININET_InternetCloseHandle(ByVal Handle As Long)

    Dim lblnReturn As Boolean    ' Return value from function

'---------------------------------------------------
'   Call the routine
    lblnReturn = InternetCloseHandle(Handle)
    
'---------------------------------------------------
'   If the call returns false then there has
'   been a problem. Find the error and throw it
    If lblnReturn = False Then
        Err.Raise vbXML_ERROR_WININETERROR, _
                  MODULETITLE & "#WININET_InternetCloseHandle", _
                  getWININETError(Err.LastDllError), _
                  App.HelpFile, _
                  ginsUtility.ErrorHelpContext(vbXML_ERROR_WININETERROR)
    End If

End Sub

'===================================================
'
'   PRIVATE getWININETError
'
'   Gets the WININET Error
'
Private Function getWININETError(ByVal ErrorNumber As Long) As String

    Dim llngModuleHandle As Long
    Dim lblnLibraryLoaded As Boolean
    Dim lstrBuffer As String * 257
    Dim llngResponseLength As Long
    Dim lstrReturn As String
    
    llngModuleHandle = GetModuleHandle("wininet.dll")
    
    If llngModuleHandle = 0 Then
        llngModuleHandle = LoadLibrary("wininet.dll")
        lblnLibraryLoaded = True
    End If
    
    llngResponseLength = FormatMessage(FORMAT_MESSAGE_FROM_HMODULE, _
                                       ByVal llngModuleHandle, _
                                       ErrorNumber, 0&, _
                                       ByVal lstrBuffer, 256&, 0&)
    
    If llngResponseLength > 0 Then
        lstrReturn = SuperTrim(Format(ErrorNumber) & " - " & _
                          Left(lstrBuffer, llngResponseLength - 2))
    Else
        lstrReturn = Format(ErrorNumber) & " - " & "Unknown - Suspect not a WININET Error"
    End If
    
    If lblnLibraryLoaded Then FreeLibrary llngModuleHandle
    
    getWININETError = lstrReturn

End Function

'================================================
'
'   PUBLIC SubmitRequest
'
'   Gets the requested URL
'
Public Sub SubmitRequest()
Attribute SubmitRequest.VB_Description = "Submits the request for the document to the remote server."
Attribute SubmitRequest.VB_HelpID = 11650
    Const METHODTITLE = MODULETITLE & "#Submit"

    Dim llngInternetSessionHandle   As Long ' WinInet Session Handle
    Dim llngInternetConnectHandle   As Long ' WinInet Connection Handle
    Dim llngHTTPOpenRequestHandle   As Long ' WinInet Request Handle
    Dim lintHTTPStatusCode          As Integer
    
'---------------------------------------------------
'   Open Session
    llngInternetSessionHandle = WININET_InternetOpen
    
'---------------------------------------------------
'   Open Connection
    llngInternetConnectHandle = WININET_InternetConnect _
                                    (llngInternetSessionHandle)

'---------------------------------------------------
'   Open Request
    llngHTTPOpenRequestHandle = WININET_HTTPOpenRequest _
                                    (llngInternetConnectHandle)
'---------------------------------------------------
'   Send Request
    WININET_HTTPSendRequest llngHTTPOpenRequestHandle

'---------------------------------------------------
'   Get header Info
    lintHTTPStatusCode = WININET_HTTPHeaderStatusCode(llngHTTPOpenRequestHandle)
    minsHeadersReceived.HeadersString = WININET_HTTPRawHeaders(llngHTTPOpenRequestHandle)
    
'---------------------------------------------------
'   If there is an HTTP problem with the call then
'   skip the part that gets the data
    If lintHTTPStatusCode = HTTP_STATUS_OK Then

'---------------------------------------------------
'       Return XMLResponse
        mstrDataReturned = WININET_InternetReadFile(llngHTTPOpenRequestHandle)
    
    Else
        Err.Raise vbXML_ERROR_HTTPERROR, _
                  MODULETITLE & "#SubmitRequest", _
                  ginsUtility.GetHTTPError(lintHTTPStatusCode), _
                  App.HelpFile, _
                  ginsUtility.ErrorHelpContext(vbXML_ERROR_HTTPERROR)
    End If

'---------------------------------------------------
'   Close Session
    WININET_InternetCloseHandle (llngHTTPOpenRequestHandle)
    WININET_InternetCloseHandle (llngInternetConnectHandle)
    WININET_InternetCloseHandle (llngInternetSessionHandle)
    
End Sub
