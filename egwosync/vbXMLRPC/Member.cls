VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
  Persistable = 0  'NotPersistable
  DataBindingBehavior = 0  'vbNone
  DataSourceBehavior  = 0  'vbNone
  MTSTransactionMode  = 0  'NotAnMTSObject
END
Attribute VB_Name = "XMLRPCMember"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = False
Attribute VB_Exposed = True
Attribute VB_HelpID = 4000
Attribute VB_Description = "Models an XML-RPC member structure. Members are part of the struct structure."
'===============================================================================
'
'  Title             : Member.cls
'  Program           : vbXMLRPC
'  Version           : 0.9
'  Copyright         : © EnAppSys Ltd
'  Date              : 14th Oct, 2002
'  Author            : Phil Hewitt
'  Contact Address   : 52 Byelands Street,
'                      Middlesbrough,
'                      Cleveland. TS4 2HP
'                      United Kingdom
'  Contact e-mail    : support@enappsys.com
'  Technical Reviewer:
'
'  Purpose           : XML-RPC Member Structure
'  Notes             : Members are part of the struct structure
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

Private Const MODULETITLE = APPNAME & "#Member.cls"

'---------------------------------------------------
'   Module level copy of the name
Private mstrName As String
'---------------------------------------------------
'   Module level copy of the value
Private minsValue As New XMLRPCValue

'================================================
'   P R O P E R T I E S
'================================================

'================================================
'
'   PUBLIC Name
'
'   Read Only.
'   The Name.
'
Public Property Get Name() As String
Attribute Name.VB_Description = "The member struct name"
Attribute Name.VB_HelpID = 4100
    Name = mstrName
End Property

'================================================
'
'   PUBLIC Value
'
'   Read Only.
'   The Value.
'
Public Property Get Value() As XMLRPCValue
Attribute Value.VB_Description = "The member struct value"
Attribute Value.VB_HelpID = 4200
    Set Value = minsValue
End Property

'================================================
'   M E T H O D S
'================================================

'================================================
'
'   FRIEND Create
'
'   Sets the Name and Value
'
'   Note it is a friend to allow functions
'   within the DLL to call it but not outside.
'
Friend Sub Create(ByVal Name As String, ByVal Value As XMLRPCValue)
    mstrName = Name
    Set minsValue = Value
End Sub

'================================================
'
'   FRIEND DecodeXML
'
'   Decodes the passed XML DOM-Like Tree Fragment.
'   Note it is a friend to allow functions
'   within the DLL to call it but not outside.
'
'   If there is a problem it will pass back the
'   problem as a string. If the string is empty
'   then there has been no problem
'
'   Effectively the passed DOM tree models
'   the following fragment of XML:
'
'   <member>
'       <name>PostCode</name>
'       <value><string>B91 1AE</string></value>
'   </member>
'
Friend Function DecodeXML(ByVal Node As XMLDOMNode) As String
    Const METHODTITLE = MODULETITLE & "#DecodeXML"
    
    Dim lintCount As Integer ' Counter
    Dim lstrReturn As String ' temporary store for the status of
                             ' the Value XML parsing
    
    For lintCount = 1 To 2

'--------------------------------------------
'       Free up the processor
        DoEvents

'---------------------------------------------------
'       Check the node is a <name> tag
        If Node(lintCount).Value = "name" Then

            mstrName = Node(lintCount)(1).Value

'---------------------------------------------------
'       Check the node is a <value> tag
        ElseIf Node(lintCount).Value = "value" Then

'---------------------------------------------------
'           pass control to XMLRPCValue to
'           process the value node
            lstrReturn = minsValue.DecodeXML(Node(lintCount))
            
'---------------------------------------------------
'           IF there is an error, pass it back
            If lstrReturn <> "" Then
                DecodeXML = lstrReturn
                Exit Function
            End If

'---------------------------------------------------
'       ELSE not a <name> or <value> tag
        Else
            DecodeXML = ginsUtility.XMLErrorName("Expecting a <name> or <value> tag", Node(lintCount).Position)
            Exit Function
        End If
        
    Next lintCount
        
'---------------------------------------------------
'   No errors pass back ""
    DecodeXML = ""
End Function

'================================================
'
'   FRIEND EncodeXML
'
'   Encodes the structure using nested calls.
'   Returns the encoded XML as a string.
'   Level is the indent required to aid debugging.
'
'   Note it is a friend to allow functions
'   within the DLL to call it but not outside.
'
Friend Function EncodeXML(ByVal Level As Integer) As String
    Dim lstrReturn As String ' temp store for the XML fragment

'--------------------------------------------
'   Free up the processor
    DoEvents

'---------------------------------------------------
'   Add the <name> details
    lstrReturn = Indent(Level) & "<name>" & mstrName & "</name>" & vbCrLf

'---------------------------------------------------
'   Add the <value> details
    lstrReturn = lstrReturn & minsValue.EncodeXML(Level)

'---------------------------------------------------
'   Return the XML fragment
    EncodeXML = lstrReturn

End Function


