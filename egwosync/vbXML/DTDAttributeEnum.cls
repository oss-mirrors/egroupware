VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
  Persistable = 0  'NotPersistable
  DataBindingBehavior = 0  'vbNone
  DataSourceBehavior  = 0  'vbNone
  MTSTransactionMode  = 0  'NotAnMTSObject
END
Attribute VB_Name = "XMLDTDAttributeEnum"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = False
Attribute VB_Exposed = True
Attribute VB_HelpID = 15000
Attribute VB_Description = "Models an enumerated list of valid attributes in an XML DTD Attlist declaration."
'===============================================================================
'
'  Title             : AttributeEnum.cls
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
'  Purpose           : Models a DTD Attribute enumerated list
'  Notes             :
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

Private Const MODULETITLE = APPNAME & "#AttributeEnum.cls"

Private mcolEnumList As New Collection

'================================================
'   P R O P E R T I E S
'================================================

'================================================
'
'   PUBLIC Count
'
'   Read-Only
'
'   Returns the number of /items in the
'   enumerated list
'
Public Property Get Count() As Long
Attribute Count.VB_Description = "The number of items in the collection."
Attribute Count.VB_HelpID = 15050
    Count = mcolEnumList.Count
End Property

'================================================
'   M E T H O D S
'================================================

'================================================
'
'   FRIEND Create
'
'   Creates the collection
'
'   Note it is a friend to allow functions
'   within the DLL to call it but not outside.
'
Friend Function Create(ByVal EnumList As String) As String
    Dim lstrTemp As String
    
    Do While Len(EnumList) <> 0
        lstrTemp = GetUpTo(EnumList, "|")
        EnumList = TrimBack(EnumList, lstrTemp)
        lstrTemp = SuperTrim(lstrTemp)
        If Left$(lstrTemp, 1) = "|" Then
            lstrTemp = Right$(lstrTemp, Len(lstrTemp) - 1)
            lstrTemp = SuperTrim(lstrTemp)
        End If
        mcolEnumList.Add lstrTemp
    Loop
End Function

'================================================
'
'   FRIEND Add
'
'   Ads an item to the collection
'
'   Note it is a friend to allow functions
'   within the DLL to call it but not outside.
'
Friend Function Add(ByVal Value As String) As String
    mcolEnumList.Add Value
    Add = Value
End Function

'================================================
'
'   PUBLIC Item
'
'   Returns a value from the list
'
Public Function Item(ByVal Index As Long) As String
Attribute Item.VB_Description = "Returns an item from the collection."
Attribute Item.VB_HelpID = 15150
Attribute Item.VB_UserMemId = 0
    Item = mcolEnumList.Item(Index)
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
   Set NewEnum = mcolEnumList.[_NewEnum]
End Function

'================================================
'
'   PUBLIC IsInList
'
'   Returns True if the value is in the list
'
Public Function IsInList(ByVal Value As String) As Boolean
Attribute IsInList.VB_Description = "Returns true if the passed value is in the list of allowed attributes."
Attribute IsInList.VB_HelpID = 15100
    Dim lintCount As Integer
    For lintCount = 1 To Count
        If Value = Item(lintCount) Then
            IsInList = True
            Exit Function
        End If
    Next lintCount
    IsInList = False
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
Friend Function Clone() As XMLDTDAttributeEnum
    Dim linsAttrEnum As New XMLDTDAttributeEnum
    Dim lintCount As Integer
    
    For lintCount = 1 To Count
        linsAttrEnum.Add Item(lintCount)
    Next lintCount
    
    Set Clone = linsAttrEnum
End Function




