VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
  Persistable = 0  'NotPersistable
  DataBindingBehavior = 0  'vbNone
  DataSourceBehavior  = 0  'vbNone
  MTSTransactionMode  = 0  'NotAnMTSObject
END
Attribute VB_Name = "XMLAttributes"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = False
Attribute VB_Exposed = True
Attribute VB_HelpID = 3000
Attribute VB_Description = "A collection class for XMLAttribute objects."
'===============================================================================
'
'  Title             : Attributes.cls
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
'  Purpose           : Attributes collection class
'  Notes             : A standard collection class for attribute classes
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

Private Const MODULETITLE = APPNAME & "#Node.cls"

'---------------------------------------------------
'   Module level collection
Private mcolAttributes As New Collection

'================================================
'   P R O P E R T I E S
'================================================

'================================================
'
'   PUBLIC Count
'
'   Returns the number of Attributes in the
'   collection
'
Public Property Get Count() As Long
Attribute Count.VB_Description = "A long containing the number of items in the collection."
Attribute Count.VB_HelpID = 3050
    Count = mcolAttributes.Count
End Property

'================================================
'   M E T H O D S
'================================================

'================================================
'
'   FRIEND Add
'
'   Add a Attribute to collection.
'
'   Note it is a friend to allow functions
'   within the DLL to call it but not outside.
'
Friend Function Add(ByVal Name As String, ByVal Value As String) As String
    Dim linsAttribute As New XMLAttribute
    
    linsAttribute.Create Name, Value
    
    mcolAttributes.Add linsAttribute
End Function

'================================================
'
'   PUBLIC GetAttributeByName
'
'   Returns an Attribute with the passed name
'
Public Function GetAttributeByName(ByVal Name As String) As XMLAttribute
Attribute GetAttributeByName.VB_Description = "Returns an Attribute with the passed name"
Attribute GetAttributeByName.VB_HelpID = 3200
    Dim linsAttribute As XMLAttribute
    
    For Each linsAttribute In mcolAttributes
        If linsAttribute.Name = Name Then
            Set GetAttributeByName = linsAttribute.Clone
            Exit Function
        End If
    Next linsAttribute
    
    Err.Raise vbXML_ERROR_ATTRIBUTENOTFOUND, _
              MODULETITLE & "#GetAttributeByName", _
              "Attribute '" & Name & "' doesn't exist.", _
              App.HelpFile, _
              ginsUtility.ErrorHelpContext(vbXML_ERROR_ATTRIBUTENOTFOUND)
    
End Function

'================================================
'
'   PUBLIC AttributeExists
'
'   Returns True if an Attribute exists
'
Public Function AttributeExists(ByVal Name As String) As Boolean
Attribute AttributeExists.VB_Description = "Returns True if an attribute exists"
Attribute AttributeExists.VB_HelpID = 3150
    Dim linsAttribute As XMLAttribute
    Dim lblnMatch As Boolean
    
    For Each linsAttribute In mcolAttributes
        If linsAttribute.Name = Name Then
            lblnMatch = True
        End If
    Next linsAttribute
    
    AttributeExists = lblnMatch
End Function

'================================================
'
'   PUBLIC Item
'
'   Returns an Attribute with the passed index
'
Public Function Item(ByVal Index As Long) As XMLAttribute
Attribute Item.VB_Description = "Returns the attribute from the collection specified by the index."
Attribute Item.VB_HelpID = 3100
Attribute Item.VB_UserMemId = 0
    Set Item = mcolAttributes.Item(Index)
End Function

'================================================
'
'   PUBLIC NewEnum
'
'   Hidden NewEnum method. Allows For ... Each
'
Public Function NewEnum() As IUnknown
Attribute NewEnum.VB_UserMemId = -4
Attribute NewEnum.VB_MemberFlags = "40"
   Set NewEnum = mcolAttributes.[_NewEnum]
End Function

'================================================
'
'   FRIEND Clone
'
'   Returns a clean unreferenced copy of the object
'
'   Note it is a friend to allow functions
'   within the DLL to call it but not outside.
'
Friend Function Clone() As XMLAttributes
    Dim linsAttributes As New XMLAttributes
    Dim lintCount As Integer
    
    For lintCount = 1 To Count
        linsAttributes.Add Item(lintCount).Name, Item(lintCount).Value
    Next lintCount
    
    Set Clone = linsAttributes

End Function
