VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
  Persistable = 0  'NotPersistable
  DataBindingBehavior = 0  'vbNone
  DataSourceBehavior  = 0  'vbNone
  MTSTransactionMode  = 0  'NotAnMTSObject
END
Attribute VB_Name = "XMLDOMNode"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = False
Attribute VB_Exposed = True
Attribute VB_HelpID = 4000
Attribute VB_Description = "Models an XML Document Object Model Node."
'===============================================================================
'
'  Title             : Node.cls
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
'  Purpose           : XML-DOM like model of a node
'  Notes             : Parse in an Elements class and
'                      produce a DOM tree.
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
'   Enum with the three different types of element
Public Enum XML_NodeType
    XML_TAG = 1
    XML_SPECIAL = 2
    XML_VALUE = 3
End Enum

'---------------------------------------------------
'   Internal collection class for the nodes
Private mcolNodes As New Collection
Private minsElement As New XMLElement
Private minsParent As XMLDOMNode

'================================================
'   P R O P E R T I E S
'================================================

'================================================
'
'   PUBLIC Count
'
'   Returns the number of Nodes in the collection
'
Public Property Get Count() As Long
Attribute Count.VB_Description = "Number of sub-node items in the node."
Attribute Count.VB_HelpID = 4100
    Count = mcolNodes.Count
End Property

'================================================
'
'   FRIEND/PUBLIC Element
'
'   The Element object at this node. Note Set
'   method is FRIEND to make it read-only outside
'   the DLL
'
Friend Property Set Element(ByVal Element As XMLElement)
    Set minsElement = Element
End Property
Public Property Get Attributes() As XMLAttributes
Attribute Attributes.VB_Description = "Any attributes which are part of the node (only likely if a tag)."
Attribute Attributes.VB_HelpID = 4050
    Set Attributes = minsElement.Attributes
End Property
Public Property Get Value() As String
Attribute Value.VB_Description = "The value of the node - whether it be the name or some data."
Attribute Value.VB_HelpID = 4350
'    Value = minsElement.Value
    If minsElement.ElementType = XML_ELEMENTVALUE Then
        Value = ginsUtility.DecodeEscapeCodes(minsElement.Value)
    Else
        Value = minsElement.Value
    End If
End Property
Public Property Get Level() As Integer
Attribute Level.VB_Description = "The node's level in the tree."
Attribute Level.VB_HelpID = 4200
    Level = minsElement.Level
End Property
Public Property Get Position() As Long
Attribute Position.VB_Description = "The node's position in the XML file."
Attribute Position.VB_HelpID = 4300
    Position = minsElement.Position
End Property
Public Property Get NodeType() As XML_NodeType
Attribute NodeType.VB_Description = "The node's type."
Attribute NodeType.VB_HelpID = 4250
    Select Case minsElement.ElementType
    Case XML_ELEMENTSTARTTAG
        NodeType = XML_TAG
    Case XML_ELEMENTENDTAG
        Err.Raise 5
'        NodeType = XML_TAG
    Case XML_ELEMENTSPECIAL
        NodeType = XML_SPECIAL
    Case XML_ELEMENTVALUE
        NodeType = XML_VALUE
    End Select
End Property
Friend Property Set Parent(Parent As XMLDOMNode)
    Set minsParent = Parent
End Property
Friend Property Get Parent()
    Set Parent = minsParent
End Property

'================================================
'
'   FRIEND NDs
'
'   Hidden Namespacedefinitions that apply at this level
'
Friend Property Get NDs() As XMLNamespaceDefinitions
    Set NDs = minsElement.NDs
End Property
    
'================================================
'   M E T H O D S
'================================================

'================================================
'
'   Add
'
'   Add a Node to collection.
'
'   Note it is a friend to allow functions
'   within the DLL to call it but not outside.
'
Friend Sub Add(ByVal Node As XMLDOMNode)
    mcolNodes.Add Node
End Sub

'================================================
'
'   PUBLIC GetTagByName
'
'   Returns an XMLDOMNode with the passed name
'
Public Function GetTagByName(ByVal Name As String) As XMLDOMNode
Attribute GetTagByName.VB_Description = "Returns an XMLDOMNode (only XML_Tag) with the passed name."
Attribute GetTagByName.VB_HelpID = 4400
    Dim linsNode As XMLDOMNode
    
    For Each linsNode In mcolNodes
        If linsNode.Value = Name And _
           linsNode.NodeType = XML_TAG Then
            Set GetTagByName = linsNode
            Exit Function
        End If
    Next linsNode
    
    Err.Raise vbXML_ERROR_TAGNOTFOUND, _
              MODULETITLE & "#GetAttributeByName", _
              "Attribute '" & Name & "' doesn't exist.", _
              App.HelpFile, _
              ginsUtility.ErrorHelpContext(vbXML_ERROR_TAGNOTFOUND)
    
End Function

'================================================
'
'   PUBLIC TagExists
'
'   Returns True if an tag exists
'
Public Function TagExists(ByVal Name As String) As Boolean
Attribute TagExists.VB_Description = "Returns True if a tag exists in the collection of nodes."
Attribute TagExists.VB_HelpID = 4450
    Dim linsNode As XMLDOMNode
    Dim lblnMatch As Boolean
    
    For Each linsNode In mcolNodes
        If linsNode.Value = Name And _
           linsNode.NodeType = XML_TAG Then
            lblnMatch = True
        End If
    Next linsNode
    
    TagExists = lblnMatch
End Function

'================================================
'
'   PUBLIC Item
'
'   Returns a Node with the passed index
'
Public Function Item(ByVal Index As Long) As XMLDOMNode
Attribute Item.VB_Description = "Returns an item from the collection of sub-nodes."
Attribute Item.VB_HelpID = 4150
Attribute Item.VB_UserMemId = 0
    Set Item = mcolNodes.Item(Index)
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
   Set NewEnum = mcolNodes.[_NewEnum]
End Function

'================================================
'
'   FRIEND Clear
'
'   Clears out all circular references that can
'   lead to memory leaks
'
Friend Sub ClearCircularReferences()
    Dim linsNode As XMLDOMNode
    Dim lintNC As Integer
    
    For lintNC = 1 To Count
        Set linsNode = Item(lintNC)
        linsNode.ClearCircularReferences
    Next lintNC
    Set minsParent = Nothing
End Sub
