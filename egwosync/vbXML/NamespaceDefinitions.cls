VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
  Persistable = 0  'NotPersistable
  DataBindingBehavior = 0  'vbNone
  DataSourceBehavior  = 0  'vbNone
  MTSTransactionMode  = 0  'NotAnMTSObject
END
Attribute VB_Name = "XMLNamespaceDefinitions"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = False
Attribute VB_Exposed = False
'===============================================================================
'
'  Title             : NamespaceDefinitions.cls
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
'  Purpose           : A collection class for Namespace Definitions
'  Notes             : A collection class for Namespace Definitions
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

Private Const MODULETITLE = APPNAME & "#NamespaceDefinitions.cls"

'---------------------------------------------------
'   Module level collection
Private mcolNDs As New Collection

'================================================
'   P R O P E R T I E S
'================================================

'================================================
'
'   PUBLIC Count
'
'   Read-Only
'
'   Returns the number of Namespace Definitions
'   in the collection
'
Public Property Get Count() As Long
    Count = mcolNDs.Count
End Property

'================================================
'   M E T H O D S
'================================================

'================================================
'
'   FRIEND Add
'
'   Add a Namespace Definition to collection.
'
'   Note it is a friend to allow functions
'   within the DLL to call it but not outside.
'
Friend Function Add(ByVal Name As String, ByVal Value As String, ByVal Level As Integer) As XMLNamespaceDefinition
    Dim linsND As New XMLNamespaceDefinition
    
    linsND.Create Name, Value, Level
    
    mcolNDs.Add linsND
    Set Add = linsND
End Function

'================================================
'
'   PUBLIC Item
'
'   Returns an Namespace Definition with the
'   passed index
'
Public Function Item(ByVal Index As Long) As XMLNamespaceDefinition
Attribute Item.VB_UserMemId = 0
    Set Item = mcolNDs.Item(Index)
End Function

'================================================
'
'   PUBLIC NewEnum
'
'   Hidden NewEnum method. Allows For ... Each
'
Public Function NewEnum() As IUnknown
Attribute NewEnum.VB_MemberFlags = "40"
   Set NewEnum = mcolNDs.[_NewEnum]
End Function

'================================================
'
'   PUBLIC NameSpaceExists
'
'   Returns an whether a namespace exists
'
Public Function NameSpaceExists(ByVal ChildName As String) As Boolean
    Dim linsND As XMLNamespaceDefinition

    For Each linsND In mcolNDs
        If linsND.ChildName = ChildName Then
            NameSpaceExists = True
            Exit Function
        End If
    Next linsND
    
    NameSpaceExists = False
    
End Function

'================================================
'
'   PUBLIC RemoveAboveThisLevel
'
'   Returns an whether a namespace exists
'
Public Sub RemoveAboveThisLevel(ByVal Level As Integer)
    Dim lintCounter As Integer
    
    lintCounter = Count
    Do While lintCounter > 0
        If Item(lintCounter).Level > Level Then
            mcolNDs.Remove lintCounter
        End If
        lintCounter = lintCounter - 1
    Loop
    
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
Friend Function Clone() As XMLNamespaceDefinitions
    Dim lintCount As Integer
    Dim linsNDs As New XMLNamespaceDefinitions
    
    For lintCount = 1 To Count
        linsNDs.Add Item(lintCount).Name, _
                    Item(lintCount).Value, _
                    Item(lintCount).Level
    Next lintCount
        
    Set Clone = linsNDs

End Function



