VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
  Persistable = 0  'NotPersistable
  DataBindingBehavior = 0  'vbNone
  DataSourceBehavior  = 0  'vbNone
  MTSTransactionMode  = 0  'NotAnMTSObject
END
Attribute VB_Name = "XMLAttribute"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = False
Attribute VB_Exposed = True
Attribute VB_HelpID = 2000
Attribute VB_Description = "Models an XML tag attribute."
'===============================================================================
'
'  Title             : Attribute.cls
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
'  Purpose           : Attribute
'  Notes             : A very simple class with two properties for the attribute
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
'   Module copies of Attribute Name and  Value
Private mstrName As String
Private mstrValue As String

'================================================
'   P R O P E R T I E S
'================================================

'================================================
'
'   PUBLIC Name
'
'   The name of the attribute
'
Public Property Get Name() As String
Attribute Name.VB_Description = "The name of the attribute"
Attribute Name.VB_HelpID = 2050
    Name = mstrName
End Property

'================================================
'
'   PUBLIC Value
'
'   The value of the attribute
'
Public Property Get Value() As String
Attribute Value.VB_Description = "The value of the attribute."
Attribute Value.VB_HelpID = 2100
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
Friend Sub Create(ByVal Name As String, ByVal Value As String)
    mstrName = Name
    mstrValue = Value
End Sub

'================================================
'
'   FRIEND Clone
'
'   Returns a clean unreferenced copy of the object
'
'   Note it is a friend to allow functions
'   within the DLL to call it but not outside.
'
Friend Function Clone() As XMLAttribute
    Dim linsAttribute As New XMLAttribute
    linsAttribute.Create mstrName, mstrValue
    Set Clone = linsAttribute
End Function

