VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
  Persistable = 0  'NotPersistable
  DataBindingBehavior = 0  'vbNone
  DataSourceBehavior  = 0  'vbNone
  MTSTransactionMode  = 0  'NotAnMTSObject
END
Attribute VB_Name = "XMLNamespaceDefinition"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = False
Attribute VB_Exposed = False
'===============================================================================
'
'  Title             : NamespaceDefinition.cls
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
'  Purpose           : NamespaceDefinition
'  Notes             : A very simple class with three properties for the NamespaceDefinition
'
'- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
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

Private Const MODULETITLE = APPNAME & "#Attribute.cls"

'---------------------------------------------------
'   Module copies of NamespaceDefinition Name and Value
Private mstrParentName As String
Private mstrChildName As String
Private mstrValue As String
Private mintLevel As Integer

'================================================
'   P R O P E R T I E S
'================================================

'================================================
'
'   PUBLIC Level
'
'   The level of the namespace
'
Public Property Get Level() As Integer
    Level = mintLevel
End Property

'================================================
'
'   PUBLIC Name
'
'   The name of the namespace
'
Public Property Get Name() As String
    Name = mstrParentName & ":" & mstrChildName
End Property

'================================================
'
'   PUBLIC ParentName
'
'   The parent name of the namespace
'
Public Property Get ParentName() As String
    ParentName = mstrParentName
End Property

'================================================
'
'   PUBLIC ChildName
'
'   The child name of the namespace
'
Public Property Get ChildName() As String
    ChildName = mstrChildName
End Property

'================================================
'
'   PUBLIC Value
'
'   The value of the attribute
'
Public Property Get Value() As String
    Value = mstrValue
End Property

'================================================
'   M E T H O D S
'================================================

'================================================
'
'   FRIEND Create
'
'   Populates the class properties
'
'   Note it is a friend to allow functions
'   within the DLL to call it but not outside.
'
Friend Sub Create(ByVal Name As String, ByVal Value As String, ByVal Level As Integer)
    mintLevel = Level
    mstrParentName = Left$(Name, InStr(Name, ":") - 1)
    mstrChildName = Right$(Name, Len(Name) - InStr(Name, ":"))
    mstrValue = Value
End Sub


