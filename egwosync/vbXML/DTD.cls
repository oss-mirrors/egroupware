VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
  Persistable = 0  'NotPersistable
  DataBindingBehavior = 0  'vbNone
  DataSourceBehavior  = 0  'vbNone
  MTSTransactionMode  = 0  'NotAnMTSObject
END
Attribute VB_Name = "XMLDTD"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = False
Attribute VB_Exposed = True
Attribute VB_HelpID = 6000
Attribute VB_Description = "Models an XML Document Type Definition."
'===============================================================================
'
'  Title             : DTD.cls
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
'  Purpose           : Base Class for a model of an XML Document Type Definition (DTD)
'  Notes             : Contains Elements and methods to parse a DTD
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

Private Const MODULETITLE = APPNAME & "#DTD.cls"

Private minsElements As New XMLDTDElements
Private WithEvents minsSAXParser As XMLSAXParser
Attribute minsSAXParser.VB_VarHelpID = -1

'================================================
'   P R O P E R T I E S
'================================================

'================================================
'
'   PUBLIC Elements
'
'   Read-Only
'
'   The elements list that contains the
'   definition of the XML File Elements.
'
Public Property Get Elements() As XMLDTDElements
Attribute Elements.VB_Description = "The elements stored in the DTD."
Attribute Elements.VB_HelpID = 5050
    Set Elements = minsElements
End Property

'================================================
'   M E T H O D S
'================================================

'================================================
'
'   PUBLIC ParseString
'
'   Decodes the passed string to form a DTD.
'
Public Sub ParseString(ByVal Data As String)
Attribute ParseString.VB_Description = "Parses a DTD in a string into the DTD object."
Attribute ParseString.VB_HelpID = 5200
    Set minsSAXParser = New XMLSAXParser
    minsSAXParser.ParseString Data, False
End Sub

'================================================
'
'   PUBLIC ParseWeb
'
'   Gets the document from an HTTP server
'   then parses it.
'
Public Sub ParseWeb(ByVal HTTPCall As XMLHTTPCall)
Attribute ParseWeb.VB_Description = "Parses a DTD in a web document into the DTD object."
Attribute ParseWeb.VB_HelpID = 5250
    HTTPCall.SubmitRequest
    ParseString HTTPCall.DataReturned
End Sub

'================================================
'
'   PUBLIC ParseFile
'
'   Gets the document from a file and parses it.
'
Public Sub ParseFile(ByVal PathName As String)
Attribute ParseFile.VB_Description = "Parses a DTD in a file on the local machine into the DTD object."
Attribute ParseFile.VB_HelpID = 5150
    Dim lstrFile As String
    Dim lstrFragment As String
    Dim lintFileNumber As Integer
    
    lintFileNumber = FreeFile
    
    Open PathName For Binary Access Read As #lintFileNumber
    Do While Not EOF(lintFileNumber)
        lstrFragment = String$(64, " ")
        Get #lintFileNumber, , lstrFragment
        lstrFile = lstrFile & lstrFragment
    Loop
    Close #lintFileNumber
    ParseString lstrFile
End Sub

'================================================
'
'   PRIVATE minsSAXParser_DTDAttlistTag
'
'   An event handler from the SAXParser that
'   is processing the DTD.
'
Private Sub minsSAXParser_DTDAttlistTag(ByVal Value As String, ByVal StartPosition As Long)
    Dim linsAttlist As New XMLDTDAttlist
    Dim lstrElement As String
    Value = SuperTrim(Mid$(Value, 10, Len(Value) - 10))
    lstrElement = SuperTrim(Left$(Value, InStr(Value, " ") - 1))
    Value = SuperTrim(Right$(Value, Len(Value) - Len(lstrElement)))
    
    minsElements.GetElementByName(lstrElement).Attlist.Add Value
End Sub

'================================================
'
'   PRIVATE minsSAXParser_DTDElementTag
'
'   An event handler from the SAXParser that
'   is processing the DTD.
'
Private Sub minsSAXParser_DTDElementTag(ByVal Value As String, ByVal StartPosition As Long)
    minsElements.Add Value
End Sub

'================================================
'
'   PUBLIC GetElement
'
'   Gets the element definition from the parsed string
'
Public Function GetElement(ByVal ElementName As String) As XMLDTDElement
Attribute GetElement.VB_Description = "Returns an element from the DTD."
Attribute GetElement.VB_HelpID = 5100

    Const METHODTITLE = MODULETITLE & "#GetElement"
    
    Dim linsElement As XMLDTDElement
    
    For Each linsElement In minsElements
    
        If linsElement.Name = ElementName Then
            Set GetElement = linsElement
            Exit Function
        End If
    
    Next linsElement
    
    Err.Raise vbXML_ERROR_INVALIDXML, _
              METHODTITLE, _
              "Element '" & ElementName & "' doesn't exist in DTD.", _
              App.HelpFile, _
              ginsUtility.ErrorHelpContext(vbXML_ERROR_INVALIDXML)

End Function


