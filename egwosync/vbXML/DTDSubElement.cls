VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
  Persistable = 0  'NotPersistable
  DataBindingBehavior = 0  'vbNone
  DataSourceBehavior  = 0  'vbNone
  MTSTransactionMode  = 0  'NotAnMTSObject
END
Attribute VB_Name = "XMLDTDSubElement"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = False
Attribute VB_Exposed = True
Attribute VB_HelpID = 9000
Attribute VB_Description = "Models an XML DTD sub-element."
'===============================================================================
'
'  Title             : DTDSubElement.cls
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
'  Purpose           : Class that models a DTD Sub-Element
'  Notes             : Class that models a DTD Sub-Element
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

Private Const MODULETITLE = APPNAME & "#DTDSubElement.cls"

'---------------------------------------------------
'   The different types of sub-element
Public Enum XML_DTDSubElementVarieties
    XMLDTD_PCDATA = 1
'    XMLDTD_CDATA = 2
    XMLDTD_CHILDNODE = 3
End Enum

'---------------------------------------------------
'   The different rules applying to a sub-element
Public Enum XML_DTDSubElementRules
    XMLDTD_ONLY_ONE = 1
    XMLDTD_MINIMUM_ONE = 2
    XMLDTD_ZERO_OR_MORE = 3
    XMLDTD_ZERO_OR_ONE = 4
End Enum

'---------------------------------------------------
'   Module level copy of Name
Private mstrName As String

'---------------------------------------------------
'   Module level copy of Type
Private menmVariety As XML_DTDSubElementVarieties

'---------------------------------------------------
'   Module level copy of Rule
Private menmRule As XML_DTDSubElementRules

'================================================
'   P R O P E R T I E S
'================================================

'================================================
'
'   PUBLIC Name
'
'   Read-Only
'
'   Returns the name of the sub-element
'
Public Property Get Name() As String
Attribute Name.VB_Description = "The sub-element name."
Attribute Name.VB_HelpID = 9050
    Name = mstrName
End Property

'================================================
'
'   PUBLIC Variety
'
'   Read-Only
'
'   Returns the type of the sub-element
'   (Type is a reserved word)
'
Public Property Get Variety() As XML_DTDSubElementVarieties
Attribute Variety.VB_Description = "The sub-element type."
Attribute Variety.VB_HelpID = 9150
    Variety = menmVariety
End Property

'================================================
'
'   PUBLIC Rule
'
'   Read-Only
'
'   Returns the rule of the sub-element
'
Public Property Get Rule() As XML_DTDSubElementRules
Attribute Rule.VB_Description = "The sub-element validation rule."
Attribute Rule.VB_HelpID = 9100
    Rule = menmRule
End Property

'================================================
'   M E T H O D S
'================================================

'================================================
'
'   FRIEND Add
'
'   Create the DTDSubElement to collection.
'
'   Note it is a friend to allow functions
'   within the DLL to call it but not outside.
'
Friend Sub Create(ByVal Variety As XML_DTDSubElementVarieties, _
                  ByVal Rule As XML_DTDSubElementRules, _
                  ByVal Name As String)
    menmVariety = Variety
    menmRule = Rule
    mstrName = Name
End Sub
