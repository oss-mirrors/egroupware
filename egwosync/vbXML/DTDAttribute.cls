VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
  Persistable = 0  'NotPersistable
  DataBindingBehavior = 0  'vbNone
  DataSourceBehavior  = 0  'vbNone
  MTSTransactionMode  = 0  'NotAnMTSObject
END
Attribute VB_Name = "XMLDTDAttribute"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = False
Attribute VB_Exposed = True
Attribute VB_HelpID = 16000
Attribute VB_Description = "Models an individual attribute definition from a DTD ATTLIST declaration."
'===============================================================================
'
'  Title             : DTDAttribute.cls
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
'  Purpose           : Models a DTD Attribute definition
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

Private Const MODULETITLE = APPNAME & "#DTDAttlist.cls"

'---------------------------------------------------
'   The different types of Attributes
Public Enum XML_DTDAttributeVarieties
    XMLDTD_NODTDATTRIBUTEVARIETY = 0
    XMLDTD_CDATA = 1
    XMLDTD_ENUMERATED = 2
    XMLDTD_ENTITY = 3
    XMLDTD_ENTITIES = 4
    XMLDTD_ID = 5
    XMLDTD_IDREF = 6
    XMLDTD_IDREFS = 7
    XMLDTD_MMTOKEN = 8
    XMLDTD_MMTOKENS = 9
    XMLDTD_NOTATION = 10
End Enum

'---------------------------------------------------
'   Attribute Modifiers
Public Enum XML_DTDAttributeModifiers
    XMLDTD_NODTDATTRIBUTEMODIFIER = 0
    XMLDTD_REQUIRED = 1
    XMLDTD_IMPLIED = 2
    XMLDTD_FIXED = 3
End Enum

'---------------------------------------------------
'   The name of the attribute
Private mstrName As String

'---------------------------------------------------
'   The different types of Attributes
Private menmVariety As XML_DTDAttributeVarieties

'---------------------------------------------------
'   The attribute Modifier
Private menmModifier As XML_DTDAttributeModifiers

'---------------------------------------------------
'   The default value for the attribute
Private mstrDefault As String

'---------------------------------------------------
'   Attribute Enumerated list
Private minsEnumList As New XMLDTDAttributeEnum

'================================================
'   P R O P E R T I E S
'================================================

'================================================
'
'   PUBLIC Name
'
'   Read-Only
'
'   Returns the attribute name
'
Public Property Get Name() As String
Attribute Name.VB_Description = "The name of an attribute."
Attribute Name.VB_HelpID = 16200
    Name = mstrName
End Property

'================================================
'
'   PUBLIC Variety
'
'   Read-Only
'
'   Returns the attribute type
'
Public Property Get Variety() As XML_DTDAttributeVarieties
Attribute Variety.VB_Description = "A value from the XML_DTDAttributeVarieties enumerated list containing the type of an attribute."
Attribute Variety.VB_HelpID = 16250
    Variety = menmVariety
End Property

'================================================
'
'   PUBLIC Modifier
'
'   Read-Only
'
'   Returns the attribute modifier setting
'
Public Property Get Modifier() As XML_DTDAttributeModifiers
Attribute Modifier.VB_Description = "A value from the XML_DTDAttributeModifiers enumerated list containing the modification rule of an attribute."
Attribute Modifier.VB_HelpID = 16150
    Modifier = menmModifier
End Property

'================================================
'
'   PUBLIC Default
'
'   Read-Only
'
'   Returns the default value for the attribute
'
Public Property Get Default() As String
Attribute Default.VB_Description = "The default or fixed value of an attribute."
Attribute Default.VB_HelpID = 16050
    Default = mstrDefault
End Property

'================================================
'
'   PUBLIC EnumList
'
'   Read-Only
'
'   Returns the Attribute Enumerated list
'
Public Property Get EnumList() As XMLDTDAttributeEnum
Attribute EnumList.VB_Description = "An XMLDTDAttributeEnum object containing the list of allowable values for the attribute."
Attribute EnumList.VB_HelpID = 16100
    Set EnumList = minsEnumList
End Property

'================================================
'   M E T H O D S
'================================================

'================================================
'
'   FRIEND Create
'
'   Creates the Attlist
'
'   Note it is a friend to allow functions
'   within the DLL to call it but not outside.
'
Friend Function Create(ByVal Name As String, _
                       ByVal Variety As XML_DTDAttributeVarieties, _
                       ByVal Modifier As XML_DTDAttributeModifiers, _
                       ByVal Default As String, _
                       ByVal EnumList As XMLDTDAttributeEnum) As XMLDTDAttribute
    mstrName = Name
    menmVariety = Variety
    menmModifier = Modifier
    mstrDefault = Default
    Set minsEnumList = EnumList
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
Friend Function Clone() As XMLDTDAttribute
    Dim linsAttr As New XMLDTDAttribute
    
    linsAttr.Create mstrName, _
                    menmVariety, _
                    menmModifier, _
                    mstrDefault, _
                    minsEnumList.Clone
    
    Set Clone = linsAttr
End Function



