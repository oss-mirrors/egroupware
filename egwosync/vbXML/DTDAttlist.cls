VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
  Persistable = 0  'NotPersistable
  DataBindingBehavior = 0  'vbNone
  DataSourceBehavior  = 0  'vbNone
  MTSTransactionMode  = 0  'NotAnMTSObject
END
Attribute VB_Name = "XMLDTDAttlist"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = False
Attribute VB_Exposed = True
Attribute VB_HelpID = 17000
Attribute VB_Description = "Models an XML DTD Attlist declaration."
'===============================================================================
'
'  Title             : DTDAttlist.cls
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
'  Purpose           : Models a DTD Attributes list
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

Private mcolAttributes As New Collection

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
Attribute Count.VB_HelpID = 17100
    Count = mcolAttributes.Count
End Property

'================================================
'   M E T H O D S
'================================================

'================================================
'
'   FRIEND Add
'
'   Adds to the Attlist
'
'   Note it is a friend to allow functions
'   within the DLL to call it but not outside.
'
Friend Sub Add(ByVal AttListData As String)

    Const METHODTITLE = MODULETITLE & "#Add"

    Dim linsAttr As XMLDTDAttribute
    Dim lstrData As String
    Dim lstrTemp As String
    
    Dim lstrName As String
    Dim lenmVariety As XML_DTDAttributeVarieties
    Dim lenmModifier As XML_DTDAttributeModifiers
    Dim lstrDefault As String
    Dim linsEnumList As XMLDTDAttributeEnum
    
    lstrData = AttListData
    
    Do While Len(lstrData) <> 0
        Set linsAttr = New XMLDTDAttribute
        lenmVariety = XMLDTD_NODTDATTRIBUTEVARIETY
        lenmModifier = XMLDTD_NODTDATTRIBUTEMODIFIER
        lstrDefault = ""
        Set linsEnumList = New XMLDTDAttributeEnum
        
'       First strip out attribute name
        lstrName = GetUpTo(lstrData)
        lstrData = TrimBack(lstrData, lstrName)
        
'       Second strip out attribute type (variety)
        If Left$(lstrData, 1) = "(" Then
            lenmVariety = XMLDTD_ENUMERATED
            lstrTemp = GetUpTo(lstrData, ")")
            lstrData = TrimBack(lstrData, lstrTemp)
            lstrTemp = SuperTrim(Mid$(lstrTemp, 2, Len(lstrTemp) - 2))
            linsEnumList.Create lstrTemp
        Else
            lstrTemp = GetUpTo(lstrData)
            lstrData = TrimBack(lstrData, lstrTemp)
            Select Case SuperTrim(lstrTemp)
            Case "CDATA"
                lenmVariety = XMLDTD_CDATA
            Case "ENTITY"
                lenmVariety = XMLDTD_ENTITY
            Case "ENTITIES"
                lenmVariety = XMLDTD_ENTITIES
            Case "ID"
                lenmVariety = XMLDTD_ID
            Case "IDREF"
                lenmVariety = XMLDTD_IDREF
            Case "IDREFS"
                lenmVariety = XMLDTD_IDREFS
            Case "MMTOKEN"
                lenmVariety = XMLDTD_MMTOKEN
            Case "MMTOKENS"
                lenmVariety = XMLDTD_MMTOKENS
            Case "NOTATION"
                lenmVariety = XMLDTD_NOTATION
            Case Else
                Err.Raise vbXML_ERROR_UNSUPPORTED, _
                          METHODTITLE, _
                          "DTD Attribute Type '" & lstrTemp & "' is not supported", _
                          App.HelpFile, _
                          ginsUtility.ErrorHelpContext(vbXML_ERROR_UNSUPPORTED)
            End Select
        End If
       
'       Third handle everything else
        Select Case Left$(lstrData, 1)
        Case "#"
            lstrTemp = SuperTrim(GetUpTo(lstrData))
            lstrData = TrimBack(lstrData, lstrTemp)
            Select Case lstrTemp
            Case "#FIXED"
                lenmModifier = XMLDTD_FIXED
                If Left$(lstrData, 1) = """" Then
                    lstrTemp = SuperTrim(GetUpTo(lstrData))
                    lstrData = TrimBack(lstrData, lstrTemp)
                    lstrDefault = Mid$(lstrTemp, 2, Len(lstrTemp) - 2)
                Else
                    Err.Raise vbXML_ERROR_MALFORMEDDTD, _
                              METHODTITLE, _
                              "Expecting """"..."""" after a #FIXED. See Attlist - " & AttListData, _
                              App.HelpFile, _
                              ginsUtility.ErrorHelpContext(vbXML_ERROR_MALFORMEDDTD)
                End If
            Case "#IMPLIED"
                lenmModifier = XMLDTD_IMPLIED
            Case "#REQUIRED"
                lenmModifier = XMLDTD_REQUIRED
            Case Else
                Err.Raise vbXML_ERROR_UNSUPPORTED, _
                          METHODTITLE, _
                          "DTD Attribute Modifier '" & lstrTemp & "' is not supported", _
                          App.HelpFile, _
                          ginsUtility.ErrorHelpContext(vbXML_ERROR_UNSUPPORTED)
            End Select
        Case """"
            lstrTemp = SuperTrim(GetUpTo(lstrData))
            lstrData = TrimBack(lstrData, lstrTemp)
            lstrDefault = Mid$(lstrTemp, 2, Len(lstrTemp) - 2)
        Case Else
        End Select
        
        linsAttr.Create lstrName, _
                        lenmVariety, _
                        lenmModifier, _
                        lstrDefault, _
                        linsEnumList
                        
        mcolAttributes.Add linsAttr
    
    Loop
    
End Sub

'================================================
'
'   PUBLIC AttributeExists
'
'   Returns True if an Attribute exists
'
Public Function AttributeExists(ByVal Name As String) As Boolean
Attribute AttributeExists.VB_Description = "Returns a boolean set to True if the passed value is the same as a name of an attribute in the collection, otherwise returns False."
Attribute AttributeExists.VB_HelpID = 17050
    Dim linsAttribute As XMLDTDAttribute
    Dim lblnMatch As Boolean
    
    For Each linsAttribute In mcolAttributes
        If linsAttribute.Name = Name Then
            AttributeExists = True
            Exit Function
        End If
    Next linsAttribute
    
    AttributeExists = False
End Function

'================================================
'
'   PUBLIC Item
'
'   Returns an DTDElement with the passed index
'
Public Function Item(ByVal Index As Long) As XMLDTDAttribute
Attribute Item.VB_Description = "Returns an item from the collection."
Attribute Item.VB_HelpID = 17150
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




