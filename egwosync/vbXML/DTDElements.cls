VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
  Persistable = 0  'NotPersistable
  DataBindingBehavior = 0  'vbNone
  DataSourceBehavior  = 0  'vbNone
  MTSTransactionMode  = 0  'NotAnMTSObject
END
Attribute VB_Name = "XMLDTDElements"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = False
Attribute VB_Exposed = True
Attribute VB_HelpID = 8000
Attribute VB_Description = "A collection class for XMLDTDElement objects."
'===============================================================================
'
'  Title             : DTDElements.cls
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
'  Purpose           : A collection class for DTD Elements
'  Notes             : A collection class for DTD Elements
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

Private Const MODULETITLE = APPNAME & "#DTDElements.cls"

'---------------------------------------------------
'   Module level collection
Private mcolElements As New Collection

'================================================
'   P R O P E R T I E S
'================================================

'================================================
'
'   PUBLIC Count
'
'   Read-Only
'
'   Returns the number of DTDElement in the
'   collection
'
Public Property Get Count() As Long
Attribute Count.VB_Description = "The number of items in the collection."
Attribute Count.VB_HelpID = 8050
    Count = mcolElements.Count
End Property

'================================================
'   M E T H O D S
'================================================

'================================================
'
'   FRIEND Add
'
'   Add a DTDElement to collection.
'
'   Note it is a friend to allow functions
'   within the DLL to call it but not outside.
'
Friend Function Add(ByVal Element As String) As XMLDTDElement
    Dim linsElement As New XMLDTDElement
    
    linsElement.Create Element
    
    mcolElements.Add linsElement
    Set Add = linsElement
End Function

'================================================
'
'   PUBLIC Item
'
'   Returns an DTDElement with the passed index
'
Public Function Item(ByVal Index As Long) As XMLDTDElement
Attribute Item.VB_Description = "Returns an item from the collection."
Attribute Item.VB_HelpID = 8100
Attribute Item.VB_UserMemId = 0
    Set Item = mcolElements.Item(Index)
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
   Set NewEnum = mcolElements.[_NewEnum]
End Function

'================================================
'
'   PUBLIC Item
'
'   Returns an DTDElement with the passed name
'
Public Function GetElementByName(ByVal Name As String) As XMLDTDElement
Attribute GetElementByName.VB_Description = "Returns a specific XMLDTDElement of the object by using the passed name to match with the objects in the collection."
Attribute GetElementByName.VB_HelpID = 8150
    Dim linsElement As XMLDTDElement

    For Each linsElement In mcolElements
        If linsElement.Name = Name Then
            Set GetElementByName = linsElement
            Exit Function
        End If
    Next linsElement
    
    Err.Raise vbXML_ERROR_DTDELEMENTNOTFOUND, _
              MODULETITLE & "#GetElementByName", _
              "DTD Element '" & Name & "' not found.", _
              App.HelpFile, _
              ginsUtility.ErrorHelpContext(vbXML_ERROR_DTDELEMENTNOTFOUND)

End Function
