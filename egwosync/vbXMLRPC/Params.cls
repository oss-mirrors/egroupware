VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
  Persistable = 0  'NotPersistable
  DataBindingBehavior = 0  'vbNone
  DataSourceBehavior  = 0  'vbNone
  MTSTransactionMode  = 0  'NotAnMTSObject
END
Attribute VB_Name = "XMLRPCParams"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = False
Attribute VB_Exposed = True
Attribute VB_HelpID = 5000
Attribute VB_Description = "Models an XML-RPC params structure. Contains a number of AddElement methods to add values to the params. Also contains an Item method for getting a specific value in the params and a Count method to return the number of values in the params. Supports For ... Each."
'===============================================================================
'
'  Title             : Param.cls
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
'  Purpose           : XML-RPC Parameters
'  Notes             : Models an XML-RPC params structure.
'                      Contains a number of AddElement structures
'                      to add elements to the params collection.
'                      Also contains a method for decoding and encoding XML.
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

Private Const MODULETITLE = APPNAME & "#Params.cls"

'---------------------------------------------------
'   Internal collection class for values
Private mcolValues As New Collection

'================================================
'   P R O P E R T I E S
'================================================

'================================================
'
'   PUBLIC Count
'
'   Read-Only
'
'   Returns the number of XMLRPCValues in the collection
'
Public Property Get Count() As Long
Attribute Count.VB_Description = "Values in the Params"
Attribute Count.VB_HelpID = 5450
    Count = mcolValues.Count
End Property

'================================================
'   M E T H O D S
'================================================

'================================================
'
'   PRIVATE Add
'
'   Add a XMLRPCValue to the params
'
Private Sub Add(ByVal Value As XMLRPCValue)
    mcolValues.Add Value
End Sub

'================================================
'
'   PUBLIC AddNil
'
'   Adds a nil to the params
'
Public Sub AddNil()
Attribute AddNil.VB_Description = "Adds a Nil to the Params."
Attribute AddNil.VB_HelpID = 5325
    AddSimple XMLRPC_NIL, vbNull
End Sub

'================================================
'
'   PUBLIC AddInteger
'
'   Adds an integer to the params
'
Public Sub AddInteger(ByVal Value As Long)
Attribute AddInteger.VB_Description = "Adds an integer to the params"
Attribute AddInteger.VB_HelpID = 5300
    AddSimple XMLRPC_INT_I4, Value
End Sub

'================================================
'
'   PUBLIC AddBoolean
'
'   Adds a boolean to the params
'
Public Sub AddBoolean(ByVal Value As Boolean)
Attribute AddBoolean.VB_Description = "Adds a boolean to the params"
Attribute AddBoolean.VB_HelpID = 5150
    AddSimple XMLRPC_BOOLEAN, Value
End Sub

'================================================
'
'   PUBLIC AddString
'
'   Adds a string to the params
'
Public Sub AddString(ByVal Value As String)
Attribute AddString.VB_Description = "Adds a string to the params"
Attribute AddString.VB_HelpID = 5350
    AddSimple XMLRPC_STRING, Value
End Sub

'================================================
'
'   PUBLIC AddDouble
'
'   Adds a double to the params
'
Public Sub AddDouble(ByVal Value As Double)
Attribute AddDouble.VB_Description = "Adds a double to the params"
Attribute AddDouble.VB_HelpID = 5250
    AddSimple XMLRPC_DOUBLE, Value
End Sub

'================================================
'
'   PUBLIC AddDateTime
'
'   Adds a datetime to the params
'
Public Sub AddDateTime(ByVal Value As Date)
Attribute AddDateTime.VB_Description = "Adds a datetime to the params"
Attribute AddDateTime.VB_HelpID = 5200
    AddSimple XMLRPC_DATETIME, Value
End Sub

'================================================
'
'   PUBLIC AddBase64
'
'   Adds a base64 to the params
'
Public Sub AddBase64(ByVal Value As XMLRPCBase64)
Attribute AddBase64.VB_Description = "Adds a base64 to the params"
Attribute AddBase64.VB_HelpID = 5100
    Dim linsValue As New XMLRPCValue
    linsValue.CreateAsBase64 Value
    mcolValues.Add linsValue
End Sub

'================================================
'
'   FRIEND AddSimple
'
'   Adds a simple value to the params.
'   Note it is a friend to allow functions
'   within the DLL to call it but not outside.
'
Friend Sub AddSimple(ByVal ValueType As XMLRPC_ValueTypes, ByVal Value As Variant)
    Dim linsValue As New XMLRPCValue
    linsValue.CreateAsSimple ValueType, Value
    mcolValues.Add linsValue
End Sub

'================================================
'
'   PUBLIC AddStruct
'
'   Adds a struct to the params
'
Public Sub AddStruct(ByVal Value As XMLRPCStruct)
Attribute AddStruct.VB_Description = "Adds a struct to the params"
Attribute AddStruct.VB_HelpID = 5400
    Dim linsValue As New XMLRPCValue
    linsValue.CreateAsStruct Value
    mcolValues.Add linsValue
End Sub

'================================================
'
'   PUBLIC AddArray
'
'   Adds an array to the params
'
Public Sub AddArray(ByVal Value As XMLRPCArray)
Attribute AddArray.VB_Description = "Adds an array to the params"
Attribute AddArray.VB_HelpID = 5050
    Dim linsValue As New XMLRPCValue
    linsValue.CreateAsArray Value
    mcolValues.Add linsValue
End Sub

'================================================
'
'   PUBLIC Item
'
'   Returns a XMLRPCValue with the passed index
'
Public Function Item(ByVal Index As Long) As XMLRPCValue
Attribute Item.VB_Description = "Returns the value in the collection indicated by the index"
Attribute Item.VB_HelpID = 5500
Attribute Item.VB_UserMemId = 0
    Set Item = mcolValues.Item(Index)
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
   Set NewEnum = mcolValues.[_NewEnum]
End Function

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
'   <params>
'       <param>
'           <value><string>South Dakota</string></value>
'       </param>
'   </params>
'
Friend Function DecodeXML(ByVal Node As XMLDOMNode) As String
    Const METHODTITLE = MODULETITLE & "#DecodeXML"

    Dim lintCount As Integer     ' Loop counter
    Dim linsValue As XMLRPCValue ' temporary store for an XMLRPC Value
    Dim lstrReturn As String     ' temporary store for the status of
                                 ' the Value XML parsing
'---------------------------------------------------
'   Check for empty <params></params>
    If Node.Count = 1 Then
        If Node(1).NodeType = XML_VALUE Then
            DecodeXML = ""
            Exit Function
        End If
    End If
    
'---------------------------------------------------
'   Cycle through each node below
'   the root <params> node
    For lintCount = 1 To Node.Count

'--------------------------------------------
'       Free up the processor
        DoEvents

'---------------------------------------------------
'       pass control to XMLRPCValue to
'       process the value node
        Set linsValue = New XMLRPCValue
        lstrReturn = linsValue.DecodeXML(Node(lintCount)(1))
        
'---------------------------------------------------
'       IF there are no errors then add
'       it to the collection of params
        If lstrReturn = "" Then
            Add linsValue
        
'---------------------------------------------------
'       ELSE there are errors, pass them back
        Else
            DecodeXML = lstrReturn
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
    Dim lintCount As Integer ' A Counter
    
'---------------------------------------------------
'   Add the <params> tag
    lstrReturn = Indent(Level) & "<params>" & vbCrLf

'---------------------------------------------------
'   Add the <param><value> branch for each value in turn
    For lintCount = 1 To Count

'--------------------------------------------
'       Free up the processor
        DoEvents

        lstrReturn = lstrReturn & Indent(Level + 1) & "<param>" & vbCrLf
        lstrReturn = lstrReturn & Me.Item(lintCount).EncodeXML(Level + 2)
        lstrReturn = lstrReturn & Indent(Level + 1) & "</param>" & vbCrLf
    Next lintCount
    
'---------------------------------------------------
'   Add the </params> tag
    lstrReturn = lstrReturn & Indent(Level) & "</params>" & vbCrLf
    
'---------------------------------------------------
'   Return the XML fragment
    EncodeXML = lstrReturn

End Function
