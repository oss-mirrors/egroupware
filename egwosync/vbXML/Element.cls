VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
  Persistable = 0  'NotPersistable
  DataBindingBehavior = 0  'vbNone
  DataSourceBehavior  = 0  'vbNone
  MTSTransactionMode  = 0  'NotAnMTSObject
END
Attribute VB_Name = "XMLElement"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = False
Attribute VB_Exposed = False
'===============================================================================
'
'  Title             : Element.cls
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
'  Purpose           : Contains data on an XML Element
'  Notes             : Very simple, just contains public
'                      properties and a clone method.
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

Private Const MODULETITLE = APPNAME & "#Element.cls"

'---------------------------------------------------
'   Enum with the three different types of element
Public Enum XML_ElementType
    XML_ELEMENTSTARTTAG = 1
    XML_ELEMENTENDTAG = 2
    XML_ELEMENTVALUE = 3
    XML_ELEMENTSPECIAL = 4
    XML_ELEMENTEMPTYTAG = 5
End Enum

'---------------------------------------------------
'   Module level copy of the Value property
Private mstrValue As String

'---------------------------------------------------
'   Module level copy of the Level property
Private mintLevel As Integer

'---------------------------------------------------
'   Module level copy of the Position property
Private mlngPosition As Long

'---------------------------------------------------
'   Module level copy of the ElementType property
Private menmElementType As XML_ElementType

'---------------------------------------------------
'   Module level copy of the Attributes property
Private minsAttributes As New XMLAttributes

'---------------------------------------------------
'   Module level copy of the Namespacedefinitions
Private minsNDs As New XMLNamespaceDefinitions

'================================================
'   P R O P E R T I E S
'================================================

'================================================
'
'   FRIEND NDs
'
'   Hidden Namespacedefinitions that apply at this level
'
Friend Property Get NDs() As XMLNamespaceDefinitions
    Set NDs = minsNDs
End Property

'================================================
'
'   PUBLIC Value
'
'   Read-Only
'
'   The Value of the element.
'   For a start tag it is the bit between the < and >
'   For an end tag it is the bit between the </ and >
'   For a value or it is everthing
Public Property Get Value() As String
    Value = mstrValue
End Property

'================================================
'
'   PUBLIC Level
'
'   Read-Only
'
'   The Level in the document
'   e.g. a node nested under the root node has a level of 2
Public Property Get Level() As Integer
    Level = mintLevel
End Property

'================================================
'
'   PUBLIC Position
'
'   Read-Only
'
'   The Position in the document
'   The actual byte where the tag starts
Public Property Get Position() As Long
    Position = mlngPosition
End Property

'================================================
'
'   PUBLIC ElementType
'
'   Read-Only
'
'   The Element Type
'   An element can either be a start tag, end tag or value
Public Property Get ElementType() As XML_ElementType
    ElementType = menmElementType
End Property

'================================================
'
'   PUBLIC Attributes
'
'   Read-Only
'
'   The Attributes
'   A collection of attributes
Public Property Get Attributes() As XMLAttributes
    Set Attributes = minsAttributes
End Property

'================================================
'   M E T H O D S
'================================================

'================================================
'
'   FRIEND Create
'
'   Creates a new copy of the object
'
'   Note it is a friend to allow functions
'   within the DLL to call it but not outside.
'
Friend Sub Create(ByVal Value As String, _
                  ByVal Level As Integer, _
                  ByVal Position As Long, _
                  ByVal ElementType As XML_ElementType, _
                  ByVal NDs As XMLNamespaceDefinitions, _
                  Optional ByVal Attributes As XMLAttributes = Nothing)

    mstrValue = Value
    mintLevel = Level
    mlngPosition = Position
    menmElementType = ElementType
    Set minsNDs = NDs
    Set minsAttributes = Attributes

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
Friend Function Clone() As XMLElement
    Dim linsElement As New XMLElement

    linsElement.Create mstrValue, _
                       mintLevel, _
                       mlngPosition, _
                       menmElementType, _
                       minsNDs, _
                       minsAttributes.Clone

    Set Clone = linsElement

End Function

