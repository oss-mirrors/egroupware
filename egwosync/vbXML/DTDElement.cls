VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
  Persistable = 0  'NotPersistable
  DataBindingBehavior = 0  'vbNone
  DataSourceBehavior  = 0  'vbNone
  MTSTransactionMode  = 0  'NotAnMTSObject
END
Attribute VB_Name = "XMLDTDElement"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = False
Attribute VB_Exposed = True
Attribute VB_HelpID = 7000
Attribute VB_Description = "Models an XML DTD element declaration."
'===============================================================================
'
'  Title             : DTDElement.cls
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
'  Purpose           : Class that models a DTD Element
'  Notes             : Contains the Sub Elements that are valid for an element
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

Private Const MODULETITLE = APPNAME & "#DTDElement.cls"

'---------------------------------------------------
'   The different types of ContentRules
Public Enum XML_DTDElementContentRules
    XMLDTD_UNKNOWNCONTENTRULE = 0
    XMLDTD_ONLY_ONE_FROM_THE_LIST = 1
    XMLDTD_ZERO_OR_MORE_FROM_THE_LIST = 2
    XMLDTD_MUST_HAVE_ALL_ELEMENTS_IN_LIST = 3
    XMLDTD_ONE_ELEMENT = 4
    XMLDTD_EMPTY = 5
    XMLDTD_ANY = 6
End Enum

'---------------------------------------------------
'   The internal content rule
Private menmContentRule As XML_DTDElementContentRules

'---------------------------------------------------
'   The internal name of the element
Private mstrName As String

'---------------------------------------------------
'   The internal DTD Sub Elements
Private minsSubElements As New XMLDTDSubElements

'---------------------------------------------------
'   The internal DTD attribute list
Private minsAttlist As New XMLDTDAttlist

'================================================
'
'   PUBLIC ContentRule
'
'   Read-Only
'
'   The rules for a group of sub-nodes in a DTD Element.
'
Public Property Get ContentRule() As XML_DTDElementContentRules
Attribute ContentRule.VB_Description = "The rules for a group of sub-nodes in a DTD Element."
Attribute ContentRule.VB_HelpID = 7050
    ContentRule = menmContentRule
End Property

'================================================
'
'   PUBLIC SubElements
'
'   Read-Only
'
'   The sub-elements in the element.
'
Public Property Get SubElements() As XMLDTDSubElements
Attribute SubElements.VB_Description = "The sub-elements in the element."
Attribute SubElements.VB_HelpID = 7150
    Set SubElements = minsSubElements
End Property

'================================================
'
'   PUBLIC Name
'
'   Read-Only
'
'   The name of the element
'
Public Property Get Name() As String
Attribute Name.VB_Description = "The name of the element."
Attribute Name.VB_HelpID = 7100
    Name = mstrName
End Property

'================================================
'
'   PUBLIC Name
'
'   Read-Only because the setter is friend
'
'   The list of allowable attributes
'
Public Property Get Attlist() As XMLDTDAttlist
Attribute Attlist.VB_Description = "The list of allowable attributes."
Attribute Attlist.VB_HelpID = 7200
    Set Attlist = minsAttlist
End Property
Friend Property Set Attlist(Attlist As XMLDTDAttlist)
    Set minsAttlist = Attlist
End Property

'================================================
'   M E T H O D S
'================================================

'================================================
'
'   PRIVATE Class_Initialize
'
'   Class constructor, used to set up
'   DTD for validation
'
Private Sub Class_Initialize()
    menmContentRule = XMLDTD_UNKNOWNCONTENTRULE
End Sub

'================================================
'
'   FRIEND Create
'
'   Creates the Attlist
'
'   Note it is a friend to allow functions
'   within the DLL to call it but not outside.
'
Friend Sub Create(ByVal ElementData As String)

    Const METHODTITLE = MODULETITLE & "#Create"
    
    Dim lintPosition As Integer

'---------------------------------------------------
'   Strip out the <!ELEMENT and >
    If Left$(ElementData, 9) = "<!ELEMENT" And _
       Right$(ElementData, 1) = ">" Then
        ElementData = SuperTrim(Mid$(ElementData, 10, Len(ElementData) - 10))
    Else
        Err.Raise vbXML_ERROR_MALFORMEDDTD, _
                  METHODTITLE, _
                  "Element '" & ElementData & "' is malformed.", _
                  App.HelpFile, _
                  ginsUtility.ErrorHelpContext(vbXML_ERROR_MALFORMEDDTD)
    End If

'---------------------------------------------------
'   Assign the name and strip this out
    mstrName = Left$(ElementData, InStr(ElementData, " ") - 1)
    ElementData = SuperTrim(Right$(ElementData, Len(ElementData) - InStr(ElementData, " ")))

'---------------------------------------------------
'   First carry out some checks to clear up
'   the type and some obvious errors
    If InStr(ElementData, ",") <> 0 And _
       InStr(ElementData, "|") = 0 And _
       InStr(ElementData, "(") <> 0 And _
       InStr(ElementData, ")") <> 0 Then
        menmContentRule = XMLDTD_MUST_HAVE_ALL_ELEMENTS_IN_LIST
    ElseIf InStr(ElementData, ",") = 0 And _
           InStr(ElementData, "|") <> 0 And _
           InStr(ElementData, "(") <> 0 And _
           InStr(ElementData, ")") <> 0 Then
        If Right$(ElementData, 2) = ")*" Then
            ElementData = Left$(ElementData, Len(ElementData) - 1)
            menmContentRule = XMLDTD_ZERO_OR_MORE_FROM_THE_LIST
        Else
            menmContentRule = XMLDTD_ONLY_ONE_FROM_THE_LIST
        End If
    ElseIf InStr(ElementData, ",") = 0 And _
           InStr(ElementData, "|") = 0 And _
           InStr(ElementData, "(") <> 0 And _
           InStr(ElementData, ")") <> 0 Then
        menmContentRule = XMLDTD_ONE_ELEMENT
    ElseIf InStr(ElementData, "(") = 0 And _
           InStr(ElementData, ")") = 0 And _
           InStr(ElementData, "EMPTY") <> 0 Then
        menmContentRule = XMLDTD_EMPTY
    ElseIf InStr(ElementData, "(") = 0 And _
           InStr(ElementData, ")") = 0 And _
           InStr(ElementData, "ANY") <> 0 Then
        menmContentRule = XMLDTD_ANY
    ElseIf InStr(ElementData, ",") <> 0 And _
           InStr(ElementData, "|") <> 0 Then
        Err.Raise vbXML_ERROR_UNSUPPORTED, _
                  METHODTITLE, _
                  "Complex DTD sub-element rules not supported in this release. '" & ElementData & "'", _
                  App.HelpFile, _
                  ginsUtility.ErrorHelpContext(vbXML_ERROR_UNSUPPORTED)
    End If
    
    If menmContentRule = XMLDTD_UNKNOWNCONTENTRULE Then
        Err.Raise vbXML_ERROR_MALFORMEDDTD, _
                  METHODTITLE, _
                  "Element '" & ElementData & "' is malformed.", _
                  App.HelpFile, _
                  ginsUtility.ErrorHelpContext(vbXML_ERROR_MALFORMEDDTD)
    End If
    
'---------------------------------------------------
'   Strip out the ..... from the ( ..... )
    If Left$(ElementData, 1) = "(" And _
       Right$(ElementData, 1) = ")" Then
        ElementData = SuperTrim(Mid$(ElementData, 2, Len(ElementData) - 2))
    ElseIf ElementData = "EMPTY" Or _
           ElementData = "ANY" Then
        Exit Sub
    Else
        Err.Raise vbXML_ERROR_MALFORMEDDTD, _
                  METHODTITLE, _
                  "Element '" & ElementData & "' is malformed.", _
                  App.HelpFile, _
                  ginsUtility.ErrorHelpContext(vbXML_ERROR_MALFORMEDDTD)
    End If
    
'---------------------------------------------------
'   Strip out each element and pass it up the tree
    Do While InStr(ElementData, "|") <> 0 Or _
             InStr(ElementData, ",") <> 0
        lintPosition = IIf(InStr(ElementData, "|") <> 0, InStr(ElementData, "|"), InStr(ElementData, ","))
        minsSubElements.Add SuperTrim(Left$(ElementData, lintPosition - 1))
        ElementData = Right$(ElementData, Len(ElementData) - lintPosition)
    Loop
    minsSubElements.Add SuperTrim(ElementData)
    
End Sub

