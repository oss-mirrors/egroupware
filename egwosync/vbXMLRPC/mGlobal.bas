Attribute VB_Name = "mGlobal"
'===============================================================================
'
'  Title             : mGlobal.bas
'  Program           : vbXMLRPC
'  Version           : 0.9
'  Copyright         : © EnAppSys Ltd
'  Date              : 31st Jul, 2002
'  Author            : Phil Hewitt
'  Contact Address   : 52 Byelands Street,
'                      Middlesbrough,
'                      Cleveland. TS4 2HP
'                      United Kingdom
'  Contact e-mail    : support@enappsys.com
'  Technical Reviewer:
'
'  Purpose           : Global defs appropriate to a module
'  Notes             : Contains some odds and sods that need to be
'                      available all over the DLL and are better
'                      initialised at the start.
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

'---------------------------------------------------
'   Constants for error tracking
Global Const APPNAME = "vbXMLRPC.dll"
Private Const MODULETITLE = APPNAME & "#mGlobal.bas"

'---------------------------------------------------
'   Size of the idents in outputed XML
Global Const INDENTSIZE = 2

'---------------------------------------------------
'   Global utility class for use throughout
Global ginsUtility As New XMLRPCUtility

'================================================
'
'   Indent
'
'   Returns a string of spaces for
'   an indent for outputed XML
'
Public Function Indent(ByVal Level As Integer) As String
    Indent = String$(Level * INDENTSIZE, " ")
End Function
