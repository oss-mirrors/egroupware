Attribute VB_Name = "Global"
'===============================================================================
'
'  Title             : Global.bas
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
'  Purpose           : Global definitions for whole DLL
'  Notes             :
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

Public Const APPNAME = "vbXML.dll"
Private Const MODULETITLE = APPNAME & "#Global.bas"

'---------------------------------------------------
'   The global utility object
Public ginsUtility As New XMLUtility

Public Function GetUpTo(ByVal Data As String, Optional ByVal Search As String = " ") As String
    If InStr(Data, Search) = 0 Then
        GetUpTo = Data
    Else
        GetUpTo = Left$(Data, InStr(Data, Search) - Len(Search))
    End If
End Function

Public Function TrimBack(ByVal Data As String, ByVal Extract As String) As String
    TrimBack = SuperTrim(Right$(Data, Len(Data) - Len(Extract)))
End Function

Public Function SuperTrim(ByVal Data As String)

    Do While Left$(Data, 1) = Chr$(32) Or _
             Left$(Data, 1) = Chr$(10) Or _
             Left$(Data, 1) = Chr$(13)
        Data = Right$(Data, Len(Data) - 1)
    Loop
    
    Do While Right$(Data, 1) = Chr$(32) Or _
             Right$(Data, 1) = Chr$(10) Or _
             Right$(Data, 1) = Chr$(13)
        Data = Left$(Data, Len(Data) - 1)
    Loop
    
    SuperTrim = Data
    
End Function
