VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
  Persistable = 0  'NotPersistable
  DataBindingBehavior = 0  'vbNone
  DataSourceBehavior  = 0  'vbNone
  MTSTransactionMode  = 0  'NotAnMTSObject
END
Attribute VB_Name = "XMLDOMParser"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = False
Attribute VB_Exposed = True
Attribute VB_HelpID = 5000
Attribute VB_Description = "Models an XML Document Object Model Parser."
'===============================================================================
'
'  Title             : DOMParser.cls
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
'  Purpose           : XML-DOM like parser
'  Notes             : Can parse either a string or a document
'                      via HTTP and produce a DOM tree.
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

Private Const MODULETITLE = APPNAME & "#DOMParser.cls"

'---------------------------------------------------
'   Enum with validation rules
Public Enum XML_ValidationRules
    XML_DONT_VALIDATE = 0
    XML_VALIDATE_WITH_DTD_REFERENCED_IN_FILE = 1
    XML_VALIDATE_WITH_EXTERNAL_DTD = 2
End Enum

'---------------------------------------------------
'   Internal copy of Validation Rule
Private menmValidationRule As XML_ValidationRules

'---------------------------------------------------
'   Internal copy of DTD
Private minsDTD As New XMLDTD

'---------------------------------------------------
'   Internal copy of Root Element
Private mstrRootElement As String

'---------------------------------------------------
'   Internal root node of DOM
Private minsNode As New XMLDOMNode

'---------------------------------------------------
'   Internal raw document
Private mstrRawDocument As String

'---------------------------------------------------
'   SAX Parser that will issue events when
'   new elements are found in the data file
Private WithEvents minsSAXParser As XMLSAXParser
Attribute minsSAXParser.VB_VarHelpID = -1

'---------------------------------------------------
'   local Elements collection
Private minsElements As XMLElements

'---------------------------------------------------
'   Current level in the document
Private mintLevel As Integer

'---------------------------------------------------
'   Collection of Namespace declarations
Private minsNDs As XMLNamespaceDefinitions

'================================================
'   P R O P E R T I E S
'================================================

'================================================
'
'   PUBLIC RawDocument
'
'   Read-Only
'
'   Gets the raw document
'
Public Property Get RawDocument() As String
Attribute RawDocument.VB_Description = "The raw document as a long string."
Attribute RawDocument.VB_HelpID = 5500
    RawDocument = mstrRawDocument
End Property

'================================================
'
'   PUBLIC Attributes
'
'   Read-Only
'
'   Returns the Attributes in the root node
'
Public Property Get Attributes() As XMLAttributes
Attribute Attributes.VB_Description = "Attributes of the base node."
Attribute Attributes.VB_HelpID = 5050
    Set Attributes = minsNode.Attributes
End Property

'================================================
'
'   PUBLIC Value
'
'   Read-Only
'
'   Returns the Value in the root node
'
Public Property Get Value() As String
Attribute Value.VB_Description = "The value of the base node - whether it be the name or some data."
Attribute Value.VB_HelpID = 5750
    Value = minsNode.Value
End Property

'================================================
'
'   PUBLIC Level
'
'   Read-Only
'
'   Returns the Level in the root node
'
Public Property Get Level() As Integer
Attribute Level.VB_Description = "The base node's level in the tree."
Attribute Level.VB_HelpID = 5250
    Level = minsNode.Level
End Property

'================================================
'
'   PUBLIC Position

'   Read-Only
'
'   Returns the Position in the root node
'
Public Property Get Position() As Long
Attribute Position.VB_Description = "The base node's position in the XML file."
Attribute Position.VB_HelpID = 5450
    Position = minsNode.Position
End Property

'================================================
'
'   PUBLIC NodeType
'
'   Read-Only
'
'   Returns the NodeType in the root node
'
Public Property Get NodeType() As XML_NodeType
Attribute NodeType.VB_Description = "The base node's type."
Attribute NodeType.VB_HelpID = 5300
    NodeType = minsNode.NodeType
End Property

'================================================
'
'   PUBLIC Count
'
'   Read-Only
'
'   Returns the number of Nodes in the collection.
'
Public Property Get Count() As Long
Attribute Count.VB_Description = "The number of sub-nodes in the base node."
Attribute Count.VB_HelpID = 5100
    Count = minsNode.Count
End Property

'================================================
'
'   PUBLIC ValidationRules
'
'   Returns the Validation Rule.
'
Public Property Let ValidationRule(ByVal ValidationRule As XML_ValidationRules)
Attribute ValidationRule.VB_Description = "The validation rule that is used to validate the file."
Attribute ValidationRule.VB_HelpID = 5700
    menmValidationRule = ValidationRule
End Property
Public Property Get ValidationRule() As XML_ValidationRules
    ValidationRule = menmValidationRule
End Property

'================================================
'
'   PUBLIC DTD
'
'   Read-Only
'
'   Returns the DTD.
'
Public Property Get DTD() As XMLDTD
Attribute DTD.VB_Description = "The Document Type Definition for the XML Document."
Attribute DTD.VB_HelpID = 5150
    Set DTD = minsDTD
End Property

'================================================
'
'   PUBLIC RootElement
'
'   Returns the document Root Element.
'
Public Property Get RootElement() As String
Attribute RootElement.VB_Description = "The root element for DTD validation."
Attribute RootElement.VB_HelpID = 5550
    RootElement = mstrRootElement
End Property
Public Property Let RootElement(RootElement As String)
    mstrRootElement = RootElement
End Property

'================================================
'   M E T H O D S
'================================================

'================================================
'
'   PRIVATE Class_Initialise
'
'   Internal class constructor.
'
Private Sub Class_Initialize()
    menmValidationRule = XML_VALIDATE_WITH_DTD_REFERENCED_IN_FILE
End Sub

'================================================
'
'   PUBLIC Validate
'
'   Validate the file with the DTD
'
Public Sub Validate()
Attribute Validate.VB_Description = "Validates the XML document against the DTD."
Attribute Validate.VB_HelpID = 5600

    Const METHODTITLE = MODULETITLE & "#Validate"
    
    If minsDTD.Elements.Count = 0 Then
        Err.Raise vbXML_ERROR_NODTDTOVALIDATEWITH, _
                  METHODTITLE, _
                  "DTD Empty", _
                  App.HelpFile, _
                  ginsUtility.ErrorHelpContext(vbXML_ERROR_NODTDTOVALIDATEWITH)
    End If
    
    ValidateNode minsNode

End Sub

'================================================
'
'   PRIVATE ValidateNode
'
'   Validate the Node with the DTD
'
Public Sub ValidateNode(ByRef Node As XMLDOMNode)
Attribute ValidateNode.VB_Description = "Validates a node against the DTD."
Attribute ValidateNode.VB_HelpID = 5650

    Const METHODTITLE = MODULETITLE & "#ValidateNode"
    
    Dim linsNode As XMLDOMNode
    Dim linsDTDElement As XMLDTDElement
    Dim linsDTDSubElement As XMLDTDSubElement
    Dim lblnMatch As Boolean
    Dim lstrValidNodeTypes As String
    Dim lintCount As Integer
    Dim linsDTDAttribute As XMLDTDAttribute
    Dim linsAttribute As XMLAttribute
    
'---------------------------------------------------
'   Handle Namespaces ... if the tag has a
'   namespace then check it has been declared and
'   if it has then skip the node
    If InStr(Node.Value, ":") <> 0 Then
        If Node.NDs.NameSpaceExists(Left$(Node.Value, InStr(Node.Value, ":") - 1)) Then
            Exit Sub
        Else
            Err.Raise vbXML_ERROR_UNDEFINEDNAMESPACE, _
                      METHODTITLE, _
                      "Tag '" & linsNode.Value & "' is a namespaced tag, not defined at this level.", _
                      App.HelpFile, _
                      ginsUtility.GetErrorCode(vbXML_ERROR_UNDEFINEDNAMESPACE)
        End If
    End If
    
'---------------------------------------------------
'   Get the DTD definition for the element
    Set linsDTDElement = minsDTD.GetElement(Node.Value)
    
    Select Case linsDTDElement.ContentRule
    Case XMLDTD_ANY
        ' Do no further checking
    Case XMLDTD_EMPTY
        If Node.Count <> 0 Then
            Err.Raise vbXML_ERROR_INVALIDXML, _
                      METHODTITLE, _
                      "'" & Node.Value & "' should be empty at postion " & Node.Position & ".", _
                      App.HelpFile, _
                      ginsUtility.ErrorHelpContext(vbXML_ERROR_INVALIDXML)
        End If
        Exit Sub
    Case XMLDTD_ZERO_OR_MORE_FROM_THE_LIST, _
         XMLDTD_MUST_HAVE_ALL_ELEMENTS_IN_LIST, _
         XMLDTD_ONLY_ONE_FROM_THE_LIST, _
         XMLDTD_ONE_ELEMENT

'---------------------------------------------------
'       Check for unknown elements
        If linsDTDElement.SubElements.Count = 1 Then
            lstrValidNodeTypes = "node is '" & linsDTDElement.SubElements(1).Name & "'"
        Else
            lstrValidNodeTypes = "nodes are "
            For lintCount = 1 To linsDTDElement.SubElements.Count
                lstrValidNodeTypes = lstrValidNodeTypes & "'" & _
                                     linsDTDElement.SubElements(lintCount).Name & "'"
                Select Case lintCount
                Case linsDTDElement.SubElements.Count - 1
                    lstrValidNodeTypes = lstrValidNodeTypes & " or "
                Case linsDTDElement.SubElements.Count
                Case Else
                    lstrValidNodeTypes = lstrValidNodeTypes & ", "
                End Select
            Next lintCount
        End If
    
        For Each linsNode In Node
            If linsNode.Value = "" And linsNode.NodeType = XML_VALUE Then
            ElseIf InStr(linsNode.Value, ":") <> 0 And linsNode.NodeType = XML_TAG Then
                If Not Node.NDs.NameSpaceExists(Left$(linsNode.Value, InStr(linsNode.Value, ":") - 1)) Then
                    Err.Raise vbXML_ERROR_UNDEFINEDNAMESPACE, _
                              METHODTITLE, _
                              "Tag '" & linsNode.Value & "' is a namespaced tag, not defined at this level.", _
                              App.HelpFile, _
                              ginsUtility.GetErrorCode(vbXML_ERROR_UNDEFINEDNAMESPACE)
                End If
            Else
                lblnMatch = False
                For Each linsDTDSubElement In linsDTDElement.SubElements
                    Select Case linsNode.NodeType
                    Case XML_VALUE
                        If linsDTDSubElement.Variety = XMLDTD_PCDATA Then
                            lblnMatch = True
                            Exit For
                        End If
                    Case XML_TAG
                        If linsDTDSubElement.Variety = XMLDTD_CHILDNODE And _
                           linsDTDSubElement.Name = linsNode.Value Then
                            lblnMatch = True
                            Exit For
                        End If
                    End Select
                Next linsDTDSubElement
                If Not lblnMatch Then
                    Err.Raise vbXML_ERROR_INVALIDXML, _
                              METHODTITLE, _
                              "'" & Node.Value & "' has unexpected node '" & linsNode.Value & "'. Valid " & lstrValidNodeTypes & ". At postion " & Node.Position & ".", _
                              App.HelpFile, _
                              ginsUtility.ErrorHelpContext(vbXML_ERROR_INVALIDXML)
                End If
            End If
        Next linsNode
    
'---------------------------------------------------
'       Check expected elements are there
        Select Case linsDTDElement.ContentRule
        Case XMLDTD_MUST_HAVE_ALL_ELEMENTS_IN_LIST, _
             XMLDTD_ONE_ELEMENT
            For Each linsDTDSubElement In linsDTDElement.SubElements
                ValidateSubNodes Node, linsDTDSubElement
            Next linsDTDSubElement
        End Select
        Select Case linsDTDElement.ContentRule
        Case XMLDTD_ONE_ELEMENT, _
             XMLDTD_ONLY_ONE_FROM_THE_LIST
            For lintCount = 1 To Node.Count - 1
                If Node(lintCount).Value <> Node(lintCount + 1).Value Then
                    Err.Raise vbXML_ERROR_INVALIDXML, _
                              METHODTITLE, _
                              "'" & Node.Value & "' has more than one type of sub-node. At postion " & Node.Position & ".", _
                              App.HelpFile, _
                              ginsUtility.ErrorHelpContext(vbXML_ERROR_INVALIDXML)
                End If
            Next lintCount
        End Select

'---------------------------------------------------
'       Check at least one element
        Select Case linsDTDElement.ContentRule
        Case XMLDTD_MUST_HAVE_ALL_ELEMENTS_IN_LIST, _
             XMLDTD_ONLY_ONE_FROM_THE_LIST, _
             XMLDTD_ONE_ELEMENT
            If Node.Count = 0 Then
                Err.Raise vbXML_ERROR_INVALIDXML, _
                          METHODTITLE, _
                          "'" & Node.Value & "' is empty, valid " & lstrValidNodeTypes & ". At postion " & Node.Position & ".", _
                          App.HelpFile, _
                          ginsUtility.ErrorHelpContext(vbXML_ERROR_INVALIDXML)
            End If
        End Select

    End Select

'---------------------------------------------------
'   Check attributes
'   - First check this test is needed
    If linsDTDElement.Attlist.Count <> 0 Or _
       Node.Attributes.Count <> 0 Then

'       - Second check if all required attributes are there
        For Each linsDTDAttribute In linsDTDElement.Attlist
        
'           - If required then check is there
            If linsDTDAttribute.Modifier = XMLDTD_REQUIRED Then
                If Not Node.Attributes.AttributeExists(linsDTDAttribute.Name) Then
                    Err.Raise vbXML_ERROR_INVALIDXML, _
                              METHODTITLE, _
                              "'" & Node.Value & "' is missing required attribute '" & linsDTDAttribute.Name & "'. At postion " & Node.Position & ".", _
                              App.HelpFile, _
                              ginsUtility.ErrorHelpContext(vbXML_ERROR_INVALIDXML)
                End If
'           - ElseIf fixed then check is there and correct value
            ElseIf linsDTDAttribute.Modifier = XMLDTD_FIXED Then
                If Not Node.Attributes.AttributeExists(linsDTDAttribute.Name) Then
                    Err.Raise vbXML_ERROR_INVALIDXML, _
                              METHODTITLE, _
                              "'" & Node.Value & "' is missing fixed attribute '" & linsDTDAttribute.Name & "'. At postion " & Node.Position & ".", _
                              App.HelpFile, _
                              ginsUtility.ErrorHelpContext(vbXML_ERROR_INVALIDXML)
                ElseIf Node.Attributes.GetAttributeByName(linsDTDAttribute.Name).Value <> linsDTDAttribute.Default Then
                    Err.Raise vbXML_ERROR_INVALIDXML, _
                              METHODTITLE, _
                              "'" & Node.Value & "' has incorrect value ('" & linsDTDAttribute.Default & "') for  attribute '" & linsDTDAttribute.Name & "'. At postion " & Node.Position & ".", _
                              App.HelpFile, _
                              ginsUtility.ErrorHelpContext(vbXML_ERROR_INVALIDXML)
                End If
            End If
        Next linsDTDAttribute

'       - Third check if no unknown attributes are there
        For Each linsAttribute In Node.Attributes
            If Not linsDTDElement.Attlist.AttributeExists(linsAttribute.Name) Then
                Err.Raise vbXML_ERROR_INVALIDXML, _
                          METHODTITLE, _
                          "'" & Node.Value & "' has unexpected attribute '" & linsAttribute.Name & "'. At postion " & Node.Position & ".", _
                          App.HelpFile, _
                          ginsUtility.ErrorHelpContext(vbXML_ERROR_INVALIDXML)
            End If
        Next linsAttribute

    End If

'---------------------------------------------------
'   Call again for sub nodes
    For Each linsNode In Node
        If linsNode.NodeType = XML_TAG Then
            ValidateNode linsNode
        End If
    Next linsNode

End Sub

'================================================
'
'   PRIVATE ValidateSubNodes
'
'   Validate the Node with the DTDSubElement
'
Private Function ValidateSubNodes(ByVal Node As XMLDOMNode, ByVal DTDSubElement As XMLDTDSubElement) As Integer

    Const METHODTITLE = MODULETITLE & "#ValidateSubNodes"
    
    Dim lintSubNodeCount As Integer
    Dim linsSubNode As XMLDOMNode
    
    If DTDSubElement.Rule <> XMLDTD_ZERO_OR_MORE Then
    
        For Each linsSubNode In Node
            If linsSubNode.NodeType = XML_VALUE And _
               DTDSubElement.Variety = XMLDTD_PCDATA Then
                lintSubNodeCount = lintSubNodeCount + 1
            ElseIf linsSubNode.NodeType = XML_TAG And _
                   linsSubNode.Value = DTDSubElement.Name Then
                lintSubNodeCount = lintSubNodeCount + 1
            End If
        Next linsSubNode
        
        Select Case DTDSubElement.Rule
        Case XMLDTD_MINIMUM_ONE
            If lintSubNodeCount = 0 Then
                Err.Raise vbXML_ERROR_INVALIDXML, _
                METHODTITLE, _
                "Expected to find at least one '" & DTDSubElement.Name & "' element but only found none below '" & Node.Value & "' at position " & Node.Position & ".", _
                App.HelpFile, _
                ginsUtility.ErrorHelpContext(vbXML_ERROR_INVALIDXML)
            End If
        Case XMLDTD_ONLY_ONE
            If lintSubNodeCount <> 1 Then
                Err.Raise vbXML_ERROR_INVALIDXML, _
                METHODTITLE, _
                "Expected to find just one '" & DTDSubElement.Name & "' element but found " & lintSubNodeCount & " below '" & Node.Value & "' at position " & Node.Position & ".", _
                App.HelpFile, _
                ginsUtility.ErrorHelpContext(vbXML_ERROR_INVALIDXML)
            End If
        Case DTDSubElement.Rule = XMLDTD_ZERO_OR_ONE
            If lintSubNodeCount > 1 Then
                Err.Raise vbXML_ERROR_INVALIDXML, _
                METHODTITLE, _
                "Expected to find none or one '" & DTDSubElement.Name & "' elements but found " & lintSubNodeCount & " below '" & Node.Value & "' at position " & Node.Position & ".", _
                App.HelpFile, _
                ginsUtility.ErrorHelpContext(vbXML_ERROR_INVALIDXML)
            End If
        End Select
        
    End If
    
    ValidateSubNodes = lintSubNodeCount

End Function

'================================================
'
'   PUBLIC ParseFile
'
'   Gets the document and populates the object
'   model by passed File Name
'
Public Sub ParseFile(ByVal FileName As String, _
                     Optional ByVal CheckWellFormed As Boolean = True)
Attribute ParseFile.VB_Description = "Parses a document read in from a local file"
Attribute ParseFile.VB_HelpID = 5800
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
'   Clears the Node, parses the passed string,
'   then populates the DOM.
'
Public Sub ParseString(ByVal Data As String, _
                       Optional CheckWellFormed As Boolean = True)
Attribute ParseString.VB_Description = "Parses a string which contains the contents of the document."
Attribute ParseString.VB_HelpID = 5350
    
    Const METHODTITLE = MODULETITLE & "#Validate"
    Dim lblnNotFirstTime As Boolean
    Dim linsLastElement As XMLElement
    Dim linsThisElement As XMLElement
    Dim linsNewElement As New XMLElement
    Dim linsElements As XMLElements
    
'---------------------------------------------------
'   Clear the variables that hold the DOM Node List
    Set minsNode = New XMLDOMNode
    mstrRawDocument = Data
    
'---------------------------------------------------
'   Reset the SAX parser, elements variable and namespaces
    Set minsElements = New XMLElements
    Set linsElements = New XMLElements
    Set minsNDs = New XMLNamespaceDefinitions
    mintLevel = 0
    Set minsSAXParser = New XMLSAXParser
    minsSAXParser.ParseString Data, CheckWellFormed
    
'---------------------------------------------------
'   Fill in <tag></tag> to form :
'   <tag>*</tag> where * is an empty value element
    For Each linsThisElement In minsElements

'--------------------------------------------
'       Free up the processor
        DoEvents

        If Not lblnNotFirstTime Then
            lblnNotFirstTime = True
        Else
            If linsThisElement.ElementType = XML_ELEMENTENDTAG And _
               linsLastElement.ElementType = XML_ELEMENTSTARTTAG And _
               linsLastElement.Value = linsThisElement.Value And _
               linsLastElement.Level = linsThisElement.Level Then
                linsElements.Add "", _
                                 linsThisElement.Level + 1, _
                                 linsLastElement.Position, _
                                 XML_ELEMENTVALUE, _
                                 linsThisElement.NDs.Clone
            End If
        End If
        If linsThisElement.ElementType = XML_ELEMENTEMPTYTAG Then
            linsElements.Add linsThisElement.Value, _
                             linsThisElement.Level, _
                             linsThisElement.Position, _
                             XML_ELEMENTSTARTTAG, _
                             linsThisElement.NDs.Clone, _
                             linsThisElement.Attributes.Clone
            linsElements.Add linsThisElement.Value, _
                             linsThisElement.Level, _
                             linsThisElement.Position, _
                             XML_ELEMENTENDTAG, _
                             linsThisElement.NDs.Clone
        Else
            linsElements.Add linsThisElement.Value, _
                             linsThisElement.Level, _
                             linsThisElement.Position, _
                             linsThisElement.ElementType, _
                             linsThisElement.NDs.Clone, _
                             linsThisElement.Attributes.Clone
        End If
        Set linsLastElement = linsThisElement.Clone
    Next linsThisElement
    
'---------------------------------------------------
'   Parse the SAX Elements list
    Dim linsElement As XMLElement
    Dim linsNode As New XMLDOMNode
    Dim linsNewNode As New XMLDOMNode
    Dim linsParentNode As XMLDOMNode
    Dim lintEC As Integer
    Dim lintLC As Integer

'---------------------------------------------------
'   set the base element
    Set minsNode.Element = linsElements(1)
    Set linsNode = minsNode

'---------------------------------------------------
'   Go through the passed elements in turn
    For lintEC = 2 To linsElements.Count

'--------------------------------------------
'       Free up the processor
        DoEvents
        
        If linsElements(lintEC).ElementType <> XML_ELEMENTENDTAG Then
            Set linsNewNode = New XMLDOMNode
            Set linsNewNode.Element = linsElements(lintEC)
            If linsNewNode.Level = linsNode.Level + 1 Then
                Set linsParentNode = linsNode
            ElseIf linsNewNode.Level = linsNode.Level Then
                Set linsParentNode = linsNode.Parent
            ElseIf linsNewNode.Level < linsNode.Level Then
                Set linsParentNode = linsNode.Parent
                For lintLC = linsNewNode.Level To linsNode.Level - 1
                    Set linsParentNode = linsParentNode.Parent
                Next lintLC
            Else
                Err.Raise 5
            End If
            Set linsNewNode.Parent = linsParentNode
            linsParentNode.Add linsNewNode
            Set linsNode = linsNewNode
        End If
    Next lintEC

'---------------------------------------------------
'   Validate the document
    Select Case menmValidationRule
    Case XML_VALIDATE_WITH_DTD_REFERENCED_IN_FILE, XML_VALIDATE_WITH_EXTERNAL_DTD
        Validate
    Case XML_DONT_VALIDATE
    End Select
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
Attribute ParseWeb.VB_HelpID = 5400
                    
    HTTPCall.SubmitRequest
    
    ParseString HTTPCall.DataReturned, CheckWellFormed
End Sub

'================================================
'
'   PUBLIC Item
'
'   Returns a Node with the passed index.
'
Public Function Item(ByVal Index As Long) As XMLDOMNode
Attribute Item.VB_Description = "Returns an item from the collection of sub-nodes."
Attribute Item.VB_HelpID = 5200
Attribute Item.VB_UserMemId = 0
    Set Item = minsNode.Item(Index)
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
   Set NewEnum = minsNode.NewEnum
End Function

'================================================
'
'   PRIVATE Class_Terminate
'
'   Clears out the circular references to objects
'   in the DOM Tree to prevent memory leaks
'
Private Sub Class_Terminate()
'---------------------------------------------------
'   Always catch errors in Class_Terminate otherwise
'   you can BSOD
    On Error Resume Next
    minsNode.ClearCircularReferences
    On Error GoTo 0
End Sub

'================================================
'
'   PRIVATE minsSAXParser_DocTypeTag
'
'   Processes a parser DocTypeTag event
'
Private Sub minsSAXParser_DocTypeTag(ByVal RootElement As String, ByVal SystemDocType As Boolean, ByVal DTDData As String, ByVal DTDDataIsURL As Boolean, ByVal DTDName As String)

    Dim linsHTTPCall As New XMLHTTPCall
    
'---------------------------------------------------
'   set up the DTD if the flag says to
    If menmValidationRule = XML_VALIDATE_WITH_DTD_REFERENCED_IN_FILE Then
        mstrRootElement = RootElement
        Set minsDTD = New XMLDTD
        If DTDDataIsURL Then
            linsHTTPCall.URL = DTDData
            minsDTD.ParseWeb linsHTTPCall
        Else
            minsDTD.ParseString DTDData
        End If
    End If
End Sub

'================================================
'
'   PRIVATE minsSAXParser_EndTag
'
'   Processes a parser EndTag event
'
Private Sub minsSAXParser_EndTag(ByVal Name As String, ByVal StartPosition As Long)
    
'---------------------------------------------------
'   add the element
    minsElements.Add Name, _
                     mintLevel, _
                     StartPosition, _
                     XML_ELEMENTENDTAG, _
                     minsNDs.Clone
    
'---------------------------------------------------
'   reduce the level by one, this was an end tag
    mintLevel = mintLevel - 1
    minsNDs.RemoveAboveThisLevel mintLevel
        
End Sub


'================================================
'
'   PRIVATE minsSAXParser_NamespaceDefinition
'
'   Processes a parser NamespaceDefinition event
'
Private Sub minsSAXParser_NamespaceDefinition(ByVal Name As String, ByVal Value As String, ByVal StartPosition As Long)
    minsNDs.Add Name, Value, mintLevel + 1
End Sub

'================================================
'
'   PRIVATE minsSAXParser_StartTag
'
'   Processes a parser StartTag event
'
Private Sub minsSAXParser_StartTag(ByVal Name As String, ByVal Attributes As XMLAttributes, ByVal StartPosition As Long)
    
'---------------------------------------------------
'   Increase the level by one, this is a start tag
    mintLevel = mintLevel + 1
        
'---------------------------------------------------
'   add the element
    minsElements.Add Name, _
                     mintLevel, _
                     StartPosition, _
                     XML_ELEMENTSTARTTAG, _
                     minsNDs.Clone, _
                     Attributes
        
End Sub

'================================================
'
'   PRIVATE minsSAXParser_EmptyTag
'
'   Processes a parser EmptyTag event
'
Private Sub minsSAXParser_EmptyTag(ByVal Name As String, ByVal Attributes As XMLAttributes, ByVal StartPosition As Long)
    Dim linsElement As New XMLElement ' Element to add to the collection
    
'---------------------------------------------------
'   add the element
    minsElements.Add Name, _
                     mintLevel + 1, _
                     StartPosition, _
                     XML_ELEMENTEMPTYTAG, _
                     minsNDs.Clone, _
                     Attributes
        
End Sub

'================================================
'
'   PRIVATE minsSAXParser_Value
'
'   Processes a parser Value event
'
Private Sub minsSAXParser_Value(ByVal Value As String, ByVal StartPosition As Long)
    
'---------------------------------------------------
'   add the element
    minsElements.Add Value, _
                     mintLevel + 1, _
                     StartPosition, _
                     XML_ELEMENTVALUE, _
                     minsNDs.Clone
    
End Sub

'================================================
'
'   PRIVATE NewParser
'
'   A non recursive parser
'
Private Sub Parse()


End Sub



