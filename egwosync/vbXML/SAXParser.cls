VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
  Persistable = 0  'NotPersistable
  DataBindingBehavior = 0  'vbNone
  DataSourceBehavior  = 0  'vbNone
  MTSTransactionMode  = 0  'NotAnMTSObject
END
Attribute VB_Name = "XMLSAXParser"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = False
Attribute VB_Exposed = True
Attribute VB_HelpID = 13000
Attribute VB_Description = "Models an XML SAX Parser."
'===============================================================================
'
'  Title             : SAXParser.cls
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
'  Purpose           : SAX-like Parser
'  Notes             : This is a (very !!!) light implementation of a
'                      SAX XML Parser. It expects a (very !!!) well-formed
'                      XML File. Raises the following events:
'
'                      StartTag(Name)
'                        - when a start tag is found
'                      EndTag(Name)
'                        - when an end tag is found
'                      Value(Value)
'                        - when a value (not a tag) is found
'                      OtherTag(Content)
'                        - when an unknown tag is found
'                      DocumentEnd
'                        - when the end of the document is reached
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

Private Const MODULETITLE = APPNAME & "#Parser.cls"

'---------------------------------------------------
'   The module level copy of the data to PARSE
Private mstrData As String

'---------------------------------------------------
'   The module level copy of the position of the element
Private mlngThisPosition As Long

'---------------------------------------------------
'   The module level copy of the position of the next element
Private mlngNextPosition As Long

'---------------------------------------------------
'   The module level copy of the position in the string
Private mlngPosition As Long

'---------------------------------------------------
'   The module level open tags stack, used to check for well-formedness
Private mcolOpenTagStack As Collection '

'---------------------------------------------------
'   Flag used to check for more than one root element
Private mblnHadFirstTag As Boolean

'================================================
'   E V E N T   S T U B S
'================================================

'---------------------------------------------------
'   Comment is fired when a comment Tag
'   i.e. '<!-- abc -->' is found
Public Event CommentTag(ByVal Comment As String, ByVal StartPosition As Long)
Attribute CommentTag.VB_Description = "Fired when a comment tag is found in the XML document."
Attribute CommentTag.VB_HelpID = 13050

'---------------------------------------------------
'   StartTag is fired when a Start Tag i.e. '<tag>'
'   is found
Public Event StartTag(ByVal Name As String, ByVal Attributes As XMLAttributes, ByVal StartPosition As Long)
Attribute StartTag.VB_Description = "Fired when an start tag is found in the XML document."
Attribute StartTag.VB_HelpID = 13550

'---------------------------------------------------
'   NamespaceDefinition is fired when a namespace i.e. '<tag>'
'   is found
Public Event NamespaceDefinition(ByVal Name As String, ByVal Value As String, ByVal StartPosition As Long)
Attribute NamespaceDefinition.VB_Description = "Fired when an namespace definition is found in the XML document."
Attribute NamespaceDefinition.VB_HelpID = 13700

'---------------------------------------------------
'   EndTag is fired when an End Tag i.e. '</tag>'
'   is found
Public Event EndTag(ByVal Name As String, ByVal StartPosition As Long)
Attribute EndTag.VB_Description = "Fired when an end tag is found in the XML document."
Attribute EndTag.VB_HelpID = 13300

'---------------------------------------------------
'   EmptyTag is fired when an Empty Tag i.e. '<tag/>'
'   is found
Public Event EmptyTag(ByVal Name As String, ByVal Attributes As XMLAttributes, ByVal StartPosition As Long)
Attribute EmptyTag.VB_Description = "Fired when an empty tag is found in the XML document."
Attribute EmptyTag.VB_HelpID = 13275

'---------------------------------------------------
'   StartTag is fired when a value i.e. 'Hello World!'
'   is found
Public Event Value(ByVal Value As String, ByVal StartPosition As Long)
Attribute Value.VB_Description = "Fired when a value is found in the XML document."
Attribute Value.VB_HelpID = 13600

'---------------------------------------------------
'   StartTag is fired when a Special Tag
'   e.g. '<?xml version="1.0"?>' is found
Public Event OtherTag(ByVal Content As String, ByVal StartPosition As Long)
Attribute OtherTag.VB_Description = "Fired when an unhandled ""<?"" or ""<!"" is found in the XML document."
Attribute OtherTag.VB_HelpID = 13350

'---------------------------------------------------
'   DTDElementTag is fired when a <!ELEMENT ....>
'   tag is found
Public Event DTDElementTag(ByVal Value As String, ByVal StartPosition As Long)
Attribute DTDElementTag.VB_Description = "Fired when a DTD ELEMENT tag is found in the DTD."
Attribute DTDElementTag.VB_HelpID = 13250

'---------------------------------------------------
'   DTDElementTag is fired when a <!ATTLIST ....>
'   tag is found
Public Event DTDAttlistTag(ByVal Value As String, ByVal StartPosition As Long)
Attribute DTDAttlistTag.VB_Description = "Fired when a DTD ATTLIST tag is found in the DTD."
Attribute DTDAttlistTag.VB_HelpID = 13650

'---------------------------------------------------
'   DocTypeTag is fired when a <!DOCTYPE ....>
'   tag is found
Public Event DocTypeTag(ByVal RootElement As String, ByVal SystemDocType As Boolean, ByVal DTDData As String, ByVal DTDDataIsURL As Boolean, ByVal DTDName As String)
Attribute DocTypeTag.VB_Description = "Fired when a DOCTYPE tag is found in the XML document."
Attribute DocTypeTag.VB_HelpID = 13100

'---------------------------------------------------
'   DocumentStart is fired when the document starts
Public Event DocumentStart()
Attribute DocumentStart.VB_Description = "Fired when the start of the XML document is reached."
Attribute DocumentStart.VB_HelpID = 13200

'---------------------------------------------------
'   DocumentEnd is fired when the document ends
Public Event DocumentEnd()
Attribute DocumentEnd.VB_Description = "Fired when the end of the XML document is reached."
Attribute DocumentEnd.VB_HelpID = 13150

'================================================
'   P R O P E R T I E S
'================================================

'================================================
'
'   PUBLIC RawDocument
'
'   Gets the raw document
'
Public Property Get RawDocument() As String
Attribute RawDocument.VB_Description = "The raw document as a long string."
Attribute RawDocument.VB_HelpID = 13500
    RawDocument = mstrData
End Property

'================================================
'   M E T H O D S
'================================================

'================================================
'
'   PUBLIC ParseFile
'
'   Gets the document and parses off to ParseString
'
Public Sub ParseFile(ByVal FileName As String, _
                     Optional ByVal CheckWellFormed As Boolean = True)
Attribute ParseFile.VB_Description = "Parses a document read in from a local file."
Attribute ParseFile.VB_HelpID = 13750
    Dim lintFileNumber As Integer
    Dim lstrLine As String
    Dim lstrDocument As String
    
    lintFileNumber = FreeFile
    Open FileName For Input As #lintFileNumber
    Do While Not EOF(lintFileNumber)
        Line Input #lintFileNumber, lstrLine
        lstrDocument = lstrDocument & lstrLine
    Loop
    Close #lintFileNumber
    
    ParseString lstrDocument, CheckWellFormed
End Sub

'================================================
'
'   PUBLIC ParseString
'
'   Parses the passed data string raising
'   events as necessary as various elements
'   in the file are encountered
'
Public Sub ParseString(ByVal Data As String, _
                       Optional ByVal CheckWellFormed As Boolean = True)
Attribute ParseString.VB_Description = "Parses a string which contains the contents of the document."
Attribute ParseString.VB_HelpID = 13400
    
    Const METHODTITLE = MODULETITLE & "#ParseString"
    
    Dim lstrData As String
    Dim lstrTemp As String
    Dim lblnEmptyTag As Boolean
    
'--------------------------------------------
'   Set the module level data string
    mstrData = Data
    mlngThisPosition = 1
    mlngNextPosition = 1
    mlngPosition = 1
    Set mcolOpenTagStack = New Collection
    mblnHadFirstTag = False

'--------------------------------------------
'   Tell the user we're starting
    RaiseEvent DocumentStart
    
'--------------------------------------------
'   While there are still tags,
'   parse the data
'    Do While InStr(mstrData, "<") <> 0
    Do While InStr(mlngPosition, mstrData, "<") <> 0
    
'--------------------------------------------
'       Remove any white space from the
'       front of the data string
        RemoveWhiteSpace

'--------------------------------------------
'       Get the first tag or value in the
'       data string
        lstrData = strGetData
        
'--------------------------------------------
'       IF the start of the string is a <
'       Must be an element
        If Left$(lstrData, 1) = "<" Then
        
            Select Case Mid$(lstrData, 2, 1)

'--------------------------------------------
'           IF second character is ? or !
'              its a special tag
            Case "?", "!"
                If Left$(lstrData, 4) = "<!--" Then
                    RaiseEvent CommentTag(SuperTrim(Mid$(lstrData, 5, Len(lstrData) - 7)), mlngThisPosition)
                ElseIf Left$(lstrData, 9) = "<![CDATA[" Then
                    RaiseEvent Value(Mid$(lstrData, 10, Len(lstrData) - 12), mlngThisPosition)
                ElseIf Left$(lstrData, 9) = "<!ELEMENT" Then
                    RaiseEvent DTDElementTag(lstrData, mlngThisPosition)
                ElseIf Left$(lstrData, 9) = "<!ATTLIST" Then
                    RaiseEvent DTDAttlistTag(lstrData, mlngThisPosition)
                ElseIf Left$(lstrData, 9) = "<!DOCTYPE" Then
                    DecodeDocTypeTag lstrData
                Else
                    RaiseEvent OtherTag(lstrData, mlngThisPosition)
                End If

'--------------------------------------------
'           IF second character is a /
'              must be an end element
            Case "/"
                lstrTemp = SuperTrim(Mid$(lstrData, 3, Len(lstrData) - 3))
                RaiseEvent EndTag(lstrTemp, mlngThisPosition)
                
                If CheckWellFormed Then
                    If mcolOpenTagStack.Count > 0 Then
                        If mcolOpenTagStack.Item(mcolOpenTagStack.Count) = lstrTemp Then
                            mcolOpenTagStack.Remove mcolOpenTagStack.Count
                        Else
                            Err.Raise vbXML_ERROR_MALFORMEDXML, _
                                      METHODTITLE, _
                                      "Expecting '</" & mcolOpenTagStack.Item(mcolOpenTagStack.Count) & ">' found '</" & lstrTemp & ">' at character " & mlngThisPosition, _
                                      App.HelpFile, _
                                      ginsUtility.ErrorHelpContext(vbXML_ERROR_MALFORMEDXML)
                        End If
                    Else
                        Err.Raise vbXML_ERROR_MALFORMEDXML, _
                                  METHODTITLE, _
                                  "Tag </" & lstrTemp & "> found at but no start tags at character " & mlngThisPosition, _
                                  App.HelpFile, _
                                  ginsUtility.ErrorHelpContext(vbXML_ERROR_MALFORMEDXML)
                    End If
                End If

'--------------------------------------------
'           ELSE Must be a start element.
'                Strip the front < and
'                back > and any whitespace and
'                save it in a temp variable
            Case Else
                lstrTemp = SuperTrim(Mid$(lstrData, 2, Len(lstrData) - 2))

'--------------------------------------------
'               If the last character of
'               the temp variable is / then
'               this is an empty element
'               and flag it and remove the /
                If Right$(lstrTemp, 1) = "/" Then
                    lblnEmptyTag = True
                    lstrTemp = SuperTrim(Left$(lstrTemp, Len(lstrTemp) - 1))
                Else
                    lblnEmptyTag = False
                End If

'--------------------------------------------
'               If there is a space in the
'               temp variable then it must
'               have attributes so get the
'               first bit of the string up
'               to the space to get the tag
'               name.
                If InStr(lstrTemp, " ") <> 0 Then
                    lstrTemp = SuperTrim(Left$(lstrTemp, InStr(lstrTemp, " ") - 1))
                End If

'--------------------------------------------
'               Raise an event with
'               the tag name
                If lblnEmptyTag Then
                    RaiseEvent EmptyTag(lstrTemp, AddAttributesFromTag(lstrData, mlngThisPosition), mlngThisPosition)
                Else
                    RaiseEvent StartTag(lstrTemp, AddAttributesFromTag(lstrData, mlngThisPosition), mlngThisPosition)
                End If
                
'--------------------------------------------
'               if we're checking for
'               well-formedness
                If CheckWellFormed Then
                    If Not lblnEmptyTag Then
'                       put the tag on the stack
                        mcolOpenTagStack.Add lstrTemp
                    End If
'                   If its the lowest level element then
'                   bugout with a duplicate level 1 element bug
                    If mblnHadFirstTag And _
                       mcolOpenTagStack.Count < 2 And _
                       Not lblnEmptyTag Then
                            Err.Raise vbXML_ERROR_MALFORMEDXML, _
                                      METHODTITLE, _
                                      "More than one root node found at character " & mlngThisPosition, _
                                      App.HelpFile, _
                                      ginsUtility.ErrorHelpContext(vbXML_ERROR_MALFORMEDXML)
                    End If
                    mblnHadFirstTag = True
                End If
            End Select
        Else

'--------------------------------------------
'           Must be a value, raise an
'           event
            RaiseEvent Value(lstrData, mlngThisPosition)
        End If
    
    Loop
    
'--------------------------------------------
'   Tell the user we've reached the end
    RaiseEvent DocumentEnd
    
'--------------------------------------------
'   Check the document is well-formed
'   i.e. all tags are closed
    If CheckWellFormed And mcolOpenTagStack.Count <> 0 Then
        Err.Raise vbXML_ERROR_MALFORMEDXML, _
                  METHODTITLE, _
                  "All tags not closed at the end of the document.", _
                  App.HelpFile, _
                  ginsUtility.ErrorHelpContext(vbXML_ERROR_MALFORMEDXML)
    End If
    
End Sub

'================================================
'
'   PUBLIC ParseWeb
'
'   Gets the document from an HTTP server
'   then parses it.
'
Public Sub ParseWeb(ByVal HTTPCall As XMLHTTPCall, _
                    Optional CheckWellFormed As Boolean = True)
Attribute ParseWeb.VB_Description = "Parses a document downloaded from an HTTP server."
Attribute ParseWeb.VB_HelpID = 13450
                    
    HTTPCall.SubmitRequest
    
    ParseString HTTPCall.DataReturned, CheckWellFormed
End Sub

'================================================
'
'   PRIVATE RemoveWhiteSpace
'
'   Removes any whitespace from the
'   front of the data string
'
Private Sub RemoveWhiteSpace()

'--------------------------------------------
'   If there are any spaces, CRs, TABs
'   or LFs then remove them
'    Do While Left$(mstrData, 1) = Chr$(9) Or _
'             Left$(mstrData, 1) = Chr$(10) Or _
'             Left$(mstrData, 1) = Chr$(13) Or _
'             Left$(mstrData, 1) = " "
'        mstrData = Right$(mstrData, Len(mstrData) - 1)
    Do While Mid$(mstrData, mlngPosition, 1) = Chr$(9) Or _
             Mid$(mstrData, mlngPosition, 1) = Chr$(10) Or _
             Mid$(mstrData, mlngPosition, 1) = Chr$(13) Or _
             Mid$(mstrData, mlngPosition, 1) = " "
        mlngPosition = mlngPosition + 1
        mlngNextPosition = mlngNextPosition + 1
        mlngThisPosition = mlngNextPosition
    Loop
End Sub

'================================================
'
'   PRIVATE strGetData
'
'   gets the next element from the data string
'
Private Function strGetData()
    
    Const METHODTITLE = MODULETITLE & "#ParseString"
    
    Dim llngLastCharPos As Long
    Dim lstrData As String
    
'--------------------------------------------
'   Free up the processor
    DoEvents
    
'--------------------------------------------
'   Check if a comment
'   Comments are defined in XML 1.0 as
'   <!-- abc -->
'
'   The following are invalid:
'   <!--- at the start
'   ---> at the end
'   -- in the middle
'   anything else goes
'    If Left$(mstrData, 4) = "<!--" Then
    If Mid$(mstrData, mlngPosition, 4) = "<!--" Then

'--------------------------------------------
'       check not '<!---'
'        If Left$(mstrData, 5) = "<!---" Then
        If Mid$(mstrData, mlngPosition, 5) = "<!---" Then
            Err.Raise vbXML_ERROR_MALFORMEDXML, _
                      METHODTITLE, _
                      "Malformed comment, '<!---' not allowed at position " & mlngNextPosition, _
                      App.HelpFile, _
                      ginsUtility.ErrorHelpContext(vbXML_ERROR_MALFORMEDXML)
    
'--------------------------------------------
'       check ends in '-->'
        Else
'            llngLastCharPos = InStr(mstrData, "-->") + 2
            llngLastCharPos = InStr(mlngPosition, mstrData, "-->") + 2
            If llngLastCharPos <> 0 Then
'                lstrData = Left$(mstrData, llngLastCharPos)
                lstrData = Mid$(mstrData, mlngPosition, llngLastCharPos)
                
'--------------------------------------------
'               check not '--->'
                If Right$(lstrData, 4) <> "--->" Then
                
'--------------------------------------------
'                   check not '--' in comment
                    If InStr(Mid$(lstrData, 5, Len(lstrData) - 7), "--") <> 0 Then
                        Err.Raise vbXML_ERROR_MALFORMEDXML, _
                                  METHODTITLE, _
                                  "Malformed comment, '--' not allowed inside comment starting at position " & mlngNextPosition, _
                                  App.HelpFile, _
                                  ginsUtility.ErrorHelpContext(vbXML_ERROR_MALFORMEDXML)
                    End If
                Else
                    Err.Raise vbXML_ERROR_MALFORMEDXML, _
                              METHODTITLE, _
                              "Malformed comment, '--->' not allowed at end of comment at position " & (mlngNextPosition + Len(lstrData) - 4), _
                              App.HelpFile, _
                              ginsUtility.ErrorHelpContext(vbXML_ERROR_MALFORMEDXML)
                End If
            Else
                Err.Raise vbXML_ERROR_MALFORMEDXML, _
                          METHODTITLE, _
                          "Malformed comment, '-->' not found after '<!--' at position " & mlngNextPosition, _
                          App.HelpFile, _
                          ginsUtility.ErrorHelpContext(vbXML_ERROR_MALFORMEDXML)
            End If
        End If

'--------------------------------------------
'   Check if a <![CDATA[...]]>
'    ElseIf Left$(mstrData, 9) = "<![CDATA[" Then
    ElseIf Mid$(mstrData, mlngPosition, 9) = "<![CDATA[" Then
    
'--------------------------------------------
'       check ends in ']]>'
'        llngLastCharPos = InStr(mstrData, "]]>") + 2
        llngLastCharPos = InStr(mlngPosition, mstrData, "]]>") + 2
        If llngLastCharPos = 0 Then
            Err.Raise vbXML_ERROR_MALFORMEDXML, _
                      METHODTITLE, _
                      "Malformed <![CDATA[...]]>, ]]>' not found after '<![CDATA[' at position " & mlngNextPosition, _
                      App.HelpFile, _
                      ginsUtility.ErrorHelpContext(vbXML_ERROR_MALFORMEDXML)
        End If

'--------------------------------------------
'   Check if a tag
'    ElseIf Left$(mstrData, 1) = "<" Then
    ElseIf Mid$(mstrData, mlngPosition, 1) = "<" Then
'        llngLastCharPos = InStr(mstrData, ">")
        llngLastCharPos = InStr(mlngPosition, mstrData, ">")
        
'--------------------------------------------
'   Treat as PCDATA value
    Else
'        llngLastCharPos = InStr(mstrData, "<") - 1
        llngLastCharPos = InStr(mlngPosition, mstrData, "<") - 1
    End If

'--------------------------------------------
'   Return the tag or value and
'   remove it from the data string
'    lstrData = Left$(mstrData, llngLastCharPos)
    lstrData = Mid$(mstrData, mlngPosition, llngLastCharPos - mlngPosition + 1)
'Debug.Print "'" + lstrData + "'", "'" + Mid$(mstrData, mlngPosition, 30) + "'"
    mlngThisPosition = mlngNextPosition
'    mstrData = Right$(mstrData, Len(mstrData) - llngLastCharPos)
    mlngPosition = mlngPosition + Len(lstrData)
    mlngNextPosition = mlngNextPosition + Len(lstrData)
    strGetData = lstrData
    
End Function

'================================================
'
'   PRIVATE DecodeDocTypeTag
'
'   Decodes the contents of a <!DOCTYPE tag
'
Private Sub DecodeDocTypeTag(ByVal DocTypeTag As String)

    Const METHODTITLE = MODULETITLE & "#ParseString"

    Dim lstrDocTypeTag As String
    Dim lintPosition As Integer
    Dim lstrRootElement As String
    Dim lblnSYSTEMDocType As Boolean
    Dim lstrParam1 As String
    Dim lstrParam2 As String
    Dim lstrDTDData As String
    Dim lstrDTDName As String
    
'--------------------------------------------
'   Take from and back off tag
    lstrDocTypeTag = SuperTrim(Mid$(DocTypeTag, 10, Len(DocTypeTag) - 10))
    
'--------------------------------------------
'   Get Root Element Name
    lintPosition = InStr(lstrDocTypeTag, " ")
    lstrRootElement = SuperTrim(Left$(lstrDocTypeTag, lintPosition))
    lstrDocTypeTag = SuperTrim(Right$(lstrDocTypeTag, Len(lstrDocTypeTag) - lintPosition))
    
'--------------------------------------------
'   Get SYSTEM or PUBLIC doctype
    Select Case Left$(lstrDocTypeTag, 6)
    Case "SYSTEM"
        lblnSYSTEMDocType = True
    Case "PUBLIC"
        lblnSYSTEMDocType = False
    Case Else
        Err.Raise vbXML_ERROR_MALFORMEDXML, _
                  METHODTITLE, _
                  "Expecting 'PUBLIC' or 'SYSTEM' instead found '" & Left$(lstrDocTypeTag, 6) & "' in DOCTYPE tag '" & DocTypeTag & "'", _
                  App.HelpFile, _
                  ginsUtility.ErrorHelpContext(vbXML_ERROR_MALFORMEDXML)
    End Select
    lstrDocTypeTag = SuperTrim(Right$(lstrDocTypeTag, Len(lstrDocTypeTag) - InStr(lstrDocTypeTag, " ")))
    
'--------------------------------------------
'   Get PUBLIC DTD name from tag
    If lblnSYSTEMDocType = False Then
        lstrParam1 = SuperTrim(Left$(lstrDocTypeTag, InStr(2, lstrDocTypeTag, Chr$(34))))
        lstrParam2 = SuperTrim(Right$(lstrDocTypeTag, Len(lstrParam1)))
        If Left$(lstrParam1, 2) = """-" Then
            lstrDTDName = lstrParam1
            lstrDocTypeTag = lstrParam2
        Else
            lstrDTDName = lstrParam2
            lstrDocTypeTag = lstrParam1
        End If
        lstrDTDName = Mid$(lstrDTDName, 2, Len(lstrDTDName) - 2)
    Else
    End If

'--------------------------------------------
'   Get Data from tag
    lstrDTDData = SuperTrim(Left$(lstrDocTypeTag, InStr(2, lstrDocTypeTag, Chr$(34))))
    lstrDTDData = Mid$(lstrDTDData, 2, Len(lstrDTDData) - 2)
        
    RaiseEvent DocTypeTag(lstrRootElement, lblnSYSTEMDocType, lstrDTDData, (Left$(lstrDTDData, 4) = "http"), lstrDTDName)
    
End Sub

'================================================
'
'   AddAttributesFromTag
'
'   Adds the attributes out of a passed start tag
'
Private Function AddAttributesFromTag(ByVal Tag As String, ByVal Position As String) As XMLAttributes

    Dim lintPosition1 As Integer
    Dim lintPosition2 As Integer
    Dim lstrName As String
    Dim lstrValue As String
    Dim linsAttributes As New XMLAttributes

'--------------------------------------------
'   Check the tag does contain
'   some attributes
'--------------------------------------------
    If InStr(Tag, Chr$(34)) <> 0 Then

'--------------------------------------------
'       Strip out the '<tag name'
'--------------------------------------------
        Tag = Right$(Tag, Len(Tag) - InStr(Tag, " "))

'--------------------------------------------
'       Strip out the '>' and any characters up to the last '"'
'--------------------------------------------
        Do While InStr(lintPosition1 + 1, Tag, Chr$(34)) <> 0
            lintPosition1 = InStr(lintPosition1 + 1, Tag, Chr$(34))
        Loop
        Tag = Left$(Tag, lintPosition1)

'--------------------------------------------
'       Go through the tag and collect names and values
'--------------------------------------------
        Do While Len(Tag) <> 0
'--------------------------------------------
'           Get the name
'--------------------------------------------
            lstrName = SuperTrim(LTrim(Left$(Tag, InStr(Tag, "=") - 1)))
'--------------------------------------------
'           SuperTrim the tag back
'--------------------------------------------
            Tag = Right$(Tag, Len(Tag) - InStr(Tag, "="))
'--------------------------------------------
'           Get the Value
'--------------------------------------------
            lintPosition1 = InStr(Tag, Chr$(34)) + 1
            lintPosition2 = InStr(lintPosition1, Tag, Chr$(34))
            lstrValue = Mid$(Tag, lintPosition1, lintPosition2 - lintPosition1)
'--------------------------------------------
'           SuperTrim the tag back
'--------------------------------------------
            Tag = Right$(Tag, Len(Tag) - lintPosition2)
'--------------------------------------------
'           Add to the collection
'           class of attributes
'--------------------------------------------
            If InStr(lstrName, ":") <> 0 Then
                RaiseEvent NamespaceDefinition(lstrName, lstrValue, Position)
            Else
                linsAttributes.Add lstrName, ginsUtility.DecodeEscapeCodes(lstrValue)
            End If
        Loop
        
    End If
    
'--------------------------------------------
'   Return the attributes collection class
'--------------------------------------------
    Set AddAttributesFromTag = linsAttributes
    
End Function
