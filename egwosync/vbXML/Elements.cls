VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
  Persistable = 0  'NotPersistable
  DataBindingBehavior = 0  'vbNone
  DataSourceBehavior  = 0  'vbNone
  MTSTransactionMode  = 0  'NotAnMTSObject
END
Attribute VB_Name = "XMLElements"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = False
Attribute VB_Exposed = False
'===============================================================================
'
'  Title             : Elements.cls
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
'  Purpose           : XML Element Collection Class
'  Notes             : A collection class for Elements.
'                      Takes the XML document and parses it,
'                      populating the collection as it goes.
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

Private Const MODULETITLE = APPNAME & "#Elements.cls"

'---------------------------------------------------
'   Internal collection class for elements
Private mcolElements As New Collection

'================================================
'   P R O P E R T I E S
'================================================

'================================================
'
'   PUBLIC Count
'
'   Returns the number of Elements in the collection
'
Public Property Get Count() As Long
    Count = mcolElements.Count
End Property

'================================================
'   M E T H O D S
'================================================

'================================================
'
'   FRIEND Add
'
'   Add an Element to collection.
'
'   Note it is a friend to allow functions
'   within the DLL to call it but not outside.
'
Friend Function Add(ByVal Value As String, _
                    ByVal Level As Integer, _
                    ByVal Position As Long, _
                    ByVal ElementType As XML_ElementType, _
                    ByVal NDs As XMLNamespaceDefinitions, _
                    Optional ByVal Attributes As XMLAttributes = Nothing) As XMLElement
    Dim linsElement As New XMLElement
    
    linsElement.Create Value, _
                       Level, _
                       Position, _
                       ElementType, _
                       NDs, _
                       Attributes
    
    Set Add = linsElement
    mcolElements.Add linsElement
End Function

'================================================
'
'   PUBLIC Item
'
'   Returns an Element with the passed index
'
Public Function Item(ByVal Index As Long) As XMLElement
Attribute Item.VB_UserMemId = 0
    Set Item = mcolElements.Item(Index)
End Function

'================================================
'
'   PUBLIC Clear
'
'   Returns an Element with the passed index
'
Public Sub Clear()
    Set mcolElements = New Collection
End Sub

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
