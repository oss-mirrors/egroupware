VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
  Persistable = 0  'NotPersistable
  DataBindingBehavior = 0  'vbNone
  DataSourceBehavior  = 0  'vbNone
  MTSTransactionMode  = 0  'NotAnMTSObject
END
Attribute VB_Name = "XMLRPCValue"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = False
Attribute VB_Exposed = True
Attribute VB_HelpID = 10000
Attribute VB_Description = "Models an XML-RPC value structure. Contains properties for accessing the different kinds of value."
'===============================================================================
'
'  Title             : Value.cls
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
'  Purpose           : XML-RPC Value Structure
'  Notes             : Models an XML-RPC value structure.
'                      Contains some FRIEND subs to Create
'                      new values and some property gets
'                      to get the data. Also contains a
'                      method for decoding and encoding XML.
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

Private Const MODULETITLE = APPNAME & "#Value.cls"

'---------------------------------------------------
'   Module level copy of the Value Type,
'   set by the CreateASxxxxx method read
'   by a property get.
Private menmValueType As XMLRPC_ValueTypes

'---------------------------------------------------
'   Module level copy of the Value as a simple,
'   set by the CreateASxxxxx method read
'   by a property get.
Private mvarSimpleValue As Variant

'---------------------------------------------------
'   Module level copy of the Value as a struct,
'   set by the CreateASxxxxx method read
'   by a property get.
Private minsStructValue As XMLRPCStruct

'---------------------------------------------------
'   Module level copy of the Value as a array,
'   set by the CreateASxxxxx method read
'   by a property get.
Private minsArrayValue As XMLRPCArray

'---------------------------------------------------
'   Module level copy of the Value as a Base64,
'   set by the CreateASxxxxx method read
'   by a property get.
Private minsBase64Value As XMLRPCBase64

'================================================
'
'   FRIEND CreateAsSimple
'
'   Sets the Value up as a simple (i.e. not an
'   Array or Struct). VB doesn't have
'   constructors like Java so the object has
'   to be created and then this method used
'   by an Array, Params or Struct object to
'   set the value up.
'
'   Note it is a friend to allow functions
'   within the DLL to call it but not outside.
'
Friend Sub CreateAsSimple(ByVal ValueType As XMLRPC_ValueTypes, _
                          ByVal SimpleValue As Variant)
    menmValueType = ValueType
    mvarSimpleValue = SimpleValue
End Sub

'================================================
'
'   FRIEND CreateAsStruct
'
'   Sets the Value up as a Struct.
'   VB doesn't have constructors like Java so
'   the object has to be created and then this
'   method used by an Array, Params or Struct
'   object to set the value up.
'
'   Note it is a friend to allow functions
'   within the DLL to call it but not outside.
'
Friend Sub CreateAsStruct(ByVal StructValue As XMLRPCStruct)
    menmValueType = XMLRPC_STRUCT
    Set minsStructValue = StructValue
End Sub

'================================================
'
'   FRIEND CreateAsArray
'
'   Sets the Value up as an Array.
'   VB doesn't have constructors like Java so
'   the object has to be created and then this
'   method used by an Array, Params or Struct
'   object to set the value up.
'
'   Note it is a friend to allow functions
'   within the DLL to call it but not outside.
'
Friend Sub CreateAsArray(ByVal ArrayValue As XMLRPCArray)
    menmValueType = XMLRPC_ARRAY
    Set minsArrayValue = ArrayValue
End Sub

'================================================
'
'   FRIEND CreateAsBase64
'
'   Sets the Value up as a Base 64.
'   VB doesn't have constructors like Java so
'   the object has to be created and then this
'   method used by an Array, Params or Struct
'   object to set the value up.
'
'   Note it is a friend to allow functions
'   within the DLL to call it but not outside.
'
Friend Sub CreateAsBase64(ByVal Base64Value As XMLRPCBase64)
    menmValueType = XMLRPC_BASE64
    Set minsBase64Value = Base64Value
End Sub

'================================================
'
'   PUBLIC ValueType
'
'   Read-Only.
'
'   The Value type.
'
Public Property Get ValueType() As XMLRPC_ValueTypes
Attribute ValueType.VB_Description = "The value type."
Attribute ValueType.VB_HelpID = 10450
    ValueType = menmValueType
End Property

'================================================
'
'   PUBLIC IntegerValue
'
'   Read-Only.
'
'   The Value as an integer.
'
Public Property Get IntegerValue() As Long
Attribute IntegerValue.VB_Description = "Returns an integer if the ValueType property is XMLRPC_INT_I4."
Attribute IntegerValue.VB_HelpID = 10300
    If menmValueType <> XMLRPC_INT_I4 Then
        Err.Raise _
            vbXMLRPC_ERROR_UNEXPECTEDVALUETYPE, _
            MODULETITLE & "#Get IntegerValue", _
            ginsUtility.GetErrorCode(vbXMLRPC_ERROR_UNEXPECTEDVALUETYPE) & " Type is '" & ginsUtility.GetXMLRPCType(menmValueType) & "' expecting '" & ginsUtility.GetXMLRPCType(XMLRPC_INT_I4) & "'", _
            App.HelpFile, _
            ginsUtility.ErrorHelpContext(vbXMLRPC_ERROR_UNEXPECTEDVALUETYPE)
    Else
        IntegerValue = CLng(mvarSimpleValue)
    End If
End Property

'================================================
'
'   PUBLIC BooleanValue
'
'   Read-Only.
'
'   The Value as a boolean.
'
Public Property Get BooleanValue() As Boolean
Attribute BooleanValue.VB_Description = "Returns a boolean if the ValueType property is XMLRPC_BOOLEAN."
Attribute BooleanValue.VB_HelpID = 10150
    If menmValueType <> XMLRPC_BOOLEAN Then
        Err.Raise _
            vbXMLRPC_ERROR_UNEXPECTEDVALUETYPE, _
            MODULETITLE & "#Get BooleanValue", _
            ginsUtility.GetErrorCode(vbXMLRPC_ERROR_UNEXPECTEDVALUETYPE) & " Type is '" & ginsUtility.GetXMLRPCType(menmValueType) & "' expecting '" & ginsUtility.GetXMLRPCType(XMLRPC_BOOLEAN) & "'", _
            App.HelpFile, _
            ginsUtility.ErrorHelpContext(vbXMLRPC_ERROR_UNEXPECTEDVALUETYPE)
    Else
        BooleanValue = CBool(mvarSimpleValue)
    End If
End Property

'================================================
'
'   PUBLIC StringValue
'
'   Read-Only.
'
'   The Value as a string.
'
Public Property Get StringValue() As String
Attribute StringValue.VB_Description = "Returns a string if the ValueType property is XMLRPC_STRING."
Attribute StringValue.VB_HelpID = 10350
    If menmValueType <> XMLRPC_STRING Then
        Err.Raise _
            vbXMLRPC_ERROR_UNEXPECTEDVALUETYPE, _
            MODULETITLE & "#Get StringValue", _
            ginsUtility.GetErrorCode(vbXMLRPC_ERROR_UNEXPECTEDVALUETYPE) & " Type is '" & ginsUtility.GetXMLRPCType(menmValueType) & "' expecting '" & ginsUtility.GetXMLRPCType(XMLRPC_STRING) & "'", _
            App.HelpFile, _
            ginsUtility.ErrorHelpContext(vbXMLRPC_ERROR_UNEXPECTEDVALUETYPE)
    Else
        StringValue = CStr(mvarSimpleValue)
    End If
End Property

'================================================
'
'   PUBLIC DoubleValue
'
'   Read-Only.
'
'   The Value as a double.
'
Public Property Get DoubleValue() As Double
Attribute DoubleValue.VB_Description = "Returns a double if the ValueType property is XMLRPC_DOUBLE."
Attribute DoubleValue.VB_HelpID = 10250
    If menmValueType <> XMLRPC_DOUBLE Then
        Err.Raise _
            vbXMLRPC_ERROR_UNEXPECTEDVALUETYPE, _
            MODULETITLE & "#Get DoubleValue", _
            ginsUtility.GetErrorCode(vbXMLRPC_ERROR_UNEXPECTEDVALUETYPE) & " Type is '" & ginsUtility.GetXMLRPCType(menmValueType) & "' expecting '" & ginsUtility.GetXMLRPCType(XMLRPC_DOUBLE) & "'", _
            App.HelpFile, _
            ginsUtility.ErrorHelpContext(vbXMLRPC_ERROR_UNEXPECTEDVALUETYPE)
    Else
        DoubleValue = CDbl(mvarSimpleValue)
    End If
End Property

'================================================
'
'   PUBLIC DateTimeValue
'
'   Read-Only.
'
'   The Value as a date time.
'
Public Property Get DateTimeValue() As Date
Attribute DateTimeValue.VB_Description = "Returns a datetime if the ValueType property is XMLRPC_DATETIME."
Attribute DateTimeValue.VB_HelpID = 10200
    If menmValueType <> XMLRPC_DATETIME Then
        Err.Raise _
            vbXMLRPC_ERROR_UNEXPECTEDVALUETYPE, _
            MODULETITLE & "#Get DateTimeValue", _
            ginsUtility.GetErrorCode(vbXMLRPC_ERROR_UNEXPECTEDVALUETYPE) & " Type is '" & ginsUtility.GetXMLRPCType(menmValueType) & "' expecting '" & ginsUtility.GetXMLRPCType(XMLRPC_DATETIME) & "'", _
            App.HelpFile, _
            ginsUtility.ErrorHelpContext(vbXMLRPC_ERROR_UNEXPECTEDVALUETYPE)
    Else
        DateTimeValue = CDate(mvarSimpleValue)
    End If
End Property

'================================================
'
'   PUBLIC Base64Value
'
'   Read-Only.
'
'   The Value as a Base64 (basically a string).
'
Public Property Get Base64Value() As XMLRPCBase64
Attribute Base64Value.VB_Description = "Returns a base64 if the ValueType property is XMLRPC_BASE64."
Attribute Base64Value.VB_HelpID = 10100
    If menmValueType <> XMLRPC_BASE64 Then
        Err.Raise _
            vbXMLRPC_ERROR_UNEXPECTEDVALUETYPE, _
            MODULETITLE & "#Get Base64Value", _
            ginsUtility.GetErrorCode(vbXMLRPC_ERROR_UNEXPECTEDVALUETYPE) & " Type is '" & ginsUtility.GetXMLRPCType(menmValueType) & "' expecting '" & ginsUtility.GetXMLRPCType(XMLRPC_BASE64) & "'", _
            App.HelpFile, _
            ginsUtility.ErrorHelpContext(vbXMLRPC_ERROR_UNEXPECTEDVALUETYPE)
    Else
        Set Base64Value = minsBase64Value
    End If
End Property

'================================================
'
'   PUBLIC StructValue
'
'   Read-Only.
'
'   The Value as a struct.
'
Public Property Get StructValue() As XMLRPCStruct
Attribute StructValue.VB_Description = "Returns an struct if the ValueType property is XMLRPC_STRUCT."
Attribute StructValue.VB_HelpID = 10400
    If menmValueType <> XMLRPC_STRUCT Then
        Err.Raise _
            vbXMLRPC_ERROR_UNEXPECTEDVALUETYPE, _
            MODULETITLE & "#Get StructValue", _
            ginsUtility.GetErrorCode(vbXMLRPC_ERROR_UNEXPECTEDVALUETYPE) & " Type is '" & ginsUtility.GetXMLRPCType(menmValueType) & "' expecting '" & ginsUtility.GetXMLRPCType(XMLRPC_STRUCT) & "'", _
            App.HelpFile, _
            ginsUtility.ErrorHelpContext(vbXMLRPC_ERROR_UNEXPECTEDVALUETYPE)
    Else
        Set StructValue = minsStructValue
    End If
End Property

'================================================
'
'   PUBLIC ArrayValue
'
'   Read-Only.
'
'   The Value as an array.
'
Public Property Get ArrayValue() As XMLRPCArray
Attribute ArrayValue.VB_Description = "Returns an array if the ValueType property is XMLRPC_ARRAY."
Attribute ArrayValue.VB_HelpID = 10050
    If menmValueType <> XMLRPC_ARRAY Then
        Err.Raise _
            vbXMLRPC_ERROR_UNEXPECTEDVALUETYPE, _
            MODULETITLE & "#Get ArrayValue", _
            ginsUtility.GetErrorCode(vbXMLRPC_ERROR_UNEXPECTEDVALUETYPE) & " Type is '" & ginsUtility.GetXMLRPCType(menmValueType) & "' expecting '" & ginsUtility.GetXMLRPCType(XMLRPC_ARRAY) & "'", _
            App.HelpFile, _
            ginsUtility.ErrorHelpContext(vbXMLRPC_ERROR_UNEXPECTEDVALUETYPE)
    Else
        Set ArrayValue = minsArrayValue
    End If
End Property

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
'   <value><string>nickname</string></value>
'
Friend Function DecodeXML(ByVal Node As XMLDOMNode) As String
    Const METHODTITLE = MODULETITLE & "#DecodeXML"

    Dim lintCount As Integer           ' Loop counter
    Dim linsNode As XMLDOMNode         ' temporary store for an XML Node
    Dim linsStruct As New XMLRPCStruct ' temporary store for an XMLRPC Struct
    Dim linsArray As New XMLRPCArray   ' temporary store for an XMLRPC Array
    Dim lstrReturn As String           ' temporary store for the status of
                                       ' the Value XML parsing
    Dim linsBase64 As New XMLRPCBase64 ' Base64 class

'--------------------------------------------
'   Free up the processor
    DoEvents

'---------------------------------------------------
'   Check the sub-node if its a value
'   then assume a string
    Set linsNode = Node(1)
    If linsNode.NodeType = XML_VALUE Then
        CreateAsSimple XMLRPC_STRING, linsNode.Value
    
'---------------------------------------------------
'   Find which of the nine the sub-node is
    Else
        
'---------------------------------------------------
'       Its a struct, pass control to an XMLRPCStruct
        Select Case linsNode.Value
        Case "struct"
            lstrReturn = linsStruct.DecodeXML(linsNode)

'---------------------------------------------------
'           IF there are no errors then
'           create as a struct
            If lstrReturn = "" Then
                CreateAsStruct linsStruct
            
'---------------------------------------------------
'           ELSE there is a problem
'           Pass the status back
            Else
                DecodeXML = lstrReturn
                Exit Function
            End If

'---------------------------------------------------
'       Its an array, pass control to an XMLRPCArray
        Case "array"
            lstrReturn = linsArray.DecodeXML(linsNode)
            
'---------------------------------------------------
'           IF there are no errors then
'           create as an array
            If lstrReturn = "" Then
                CreateAsArray linsArray
            
'---------------------------------------------------
'           ELSE there is a problem
'           Pass the status back
            Else
                DecodeXML = lstrReturn
                Exit Function
            End If
        
'---------------------------------------------------
'       Its a Base 64
        Case "base64"
        
            linsBase64.Encoded = linsNode(1).Value
            CreateAsBase64 linsBase64
            
'---------------------------------------------------
'       Its a simple type
        Case "i4", "int", "boolean", "string", _
             "double", "dateTime.iso8601", "nil"
            
'---------------------------------------------------
'           Its an integer, check its in range
'           and has a '.' and create it.
            Select Case linsNode.Value
            Case "i4", "int"
                If IsNumeric(linsNode(1).Value) And _
                   InStr(linsNode(1).Value, ".") = 0 Then
                    If Val(linsNode(1).Value) >= -2147483648# And _
                       Val(linsNode(1).Value) <= 2147483647 Then
                        CreateAsSimple XMLRPC_INT_I4, Val(linsNode(1).Value)
                    Else
                        DecodeXML = ginsUtility.XMLErrorName("Number out of bounds", linsNode(1).Position)
                        Exit Function
                    End If
                Else
                    DecodeXML = ginsUtility.XMLErrorName("Expecting integer value", linsNode(1).Position)
                    Exit Function
                End If
                
'---------------------------------------------------
'           Its a boolean, check its OK and create it.
            Case "boolean"
                If linsNode(1).Value = "1" Or _
                   linsNode(1).Value = "0" Then
                    CreateAsSimple XMLRPC_BOOLEAN, (linsNode(1).Value = "1")
                Else
                    DecodeXML = ginsUtility.XMLErrorName("Expecting '1' or '0'", linsNode(1).Position)
                    Exit Function
                End If
                
'---------------------------------------------------
'           Its a string, create as a string
            Case "string"
                CreateAsSimple XMLRPC_STRING, linsNode(1).Value
            
'---------------------------------------------------
'           Its a double, create as a double
            Case "double"
                If IsNumeric(linsNode(1).Value) Then
                    CreateAsSimple XMLRPC_DOUBLE, linsNode(1).Value
                Else
                    DecodeXML = ginsUtility.XMLErrorName("Expecting double value", linsNode(1).Position)
                    Exit Function
                End If
                
'---------------------------------------------------
'           Its a datetime, check its has
'           the correct bits in the correct
'           place and create it.
            Case "dateTime.iso8601"
                If Len(linsNode(1).Value) = 17 Then
                    If IsNumeric(Left$(linsNode(1).Value, 8)) And _
                       IsNumeric(Mid$(linsNode(1).Value, 10, 2)) And _
                       IsNumeric(Mid$(linsNode(1).Value, 13, 2)) And _
                       IsNumeric(Mid$(linsNode(1).Value, 16, 2)) And _
                       Mid$(linsNode(1).Value, 9, 1) = "T" And _
                       Mid$(linsNode(1).Value, 12, 1) = ":" And _
                       Mid$(linsNode(1).Value, 15, 1) = ":" Then
                        CreateAsSimple XMLRPC_DATETIME, _
                            DateSerial(Val(Left$(linsNode(1).Value, 4)), _
                                       Val(Mid$(linsNode(1).Value, 5, 2)), _
                                       Val(Mid$(linsNode(1).Value, 7, 2))) + _
                            TimeSerial(Val(Mid$(linsNode(1).Value, 10, 2)), _
                                       Val(Mid$(linsNode(1).Value, 13, 2)), _
                                       Val(Mid$(linsNode(1).Value, 16, 2)))
                    Else
                        DecodeXML = ginsUtility.XMLErrorName("Expecting dateTime.iso8601 value", linsNode(1).Position)
                        Exit Function
                    End If
                Else
                    DecodeXML = ginsUtility.XMLErrorName("Expecting dateTime.iso8601 value", linsNode(1).Position)
                    Exit Function
                End If
                
'---------------------------------------------------
'           Its a nil, create as a nil
            Case "nil"
                CreateAsSimple XMLRPC_NIL, vbNull
            End Select
            
'---------------------------------------------------
'           It should be one of the nine XML-RPC
'           types but it isn't !!!
        Case Else
            DecodeXML = ginsUtility.XMLErrorName("Found '" & linsNode.Value & "' expecting 'struct', 'array', 'i4', 'int', 'boolean', 'string', 'double', 'dateTime.iso8601', 'base64' or 'nil'", linsNode.Position)
            Exit Function
        End Select
    End If

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
    Dim lstrReturn As String           ' temp store for the XML fragment
    Dim lstrTag As String              ' the text in the tag
    Dim linsBase64 As New XMLRPCBase64 ' Base64 class

'--------------------------------------------
'   Free up the processor
    DoEvents

'---------------------------------------------------
'   Add the <value> tag
    lstrReturn = Indent(Level) & "<value>" & vbCrLf

'---------------------------------------------------
'   Work out the tag name
    Select Case menmValueType
    Case XMLRPC_ARRAY
        lstrTag = "array"
    Case XMLRPC_BASE64
        lstrTag = "base64"
    Case XMLRPC_BOOLEAN
        lstrTag = "boolean"
    Case XMLRPC_DATETIME
        lstrTag = "dateTime.iso8601"
    Case XMLRPC_DOUBLE
        lstrTag = "double"
    Case XMLRPC_INT_I4
        lstrTag = "i4"
    Case XMLRPC_STRING
        lstrTag = "string"
    Case XMLRPC_STRUCT
        lstrTag = "struct"
    End Select

'---------------------------------------------------
'   Add the <tag> tag
    lstrReturn = lstrReturn & Indent(Level + 1) & "<" & lstrTag & ">"

'---------------------------------------------------
'   Work out the the contents of the value
    Select Case menmValueType
    Case XMLRPC_ARRAY
        lstrReturn = lstrReturn & vbCrLf & minsArrayValue.EncodeXML(Level + 2)
        lstrReturn = lstrReturn & Indent(Level + 1)
    Case XMLRPC_BASE64
        lstrReturn = lstrReturn & minsBase64Value.Encoded
    Case XMLRPC_BOOLEAN
        lstrReturn = lstrReturn & IIf(mvarSimpleValue, "1", "0")
    Case XMLRPC_DATETIME
        lstrReturn = lstrReturn & _
                     Format(mvarSimpleValue, "YYYYMMDD") & _
                     "T" & _
                     Format(mvarSimpleValue, "HH:MM:SS")
    Case XMLRPC_DOUBLE
        lstrReturn = lstrReturn & _
                     Trim(Format$(mvarSimpleValue, "#####################################################################################################################################################################0.0#####################################################################################################################################################################"))
    Case XMLRPC_INT_I4
        lstrReturn = lstrReturn & Format$(mvarSimpleValue)
    Case XMLRPC_STRING
        lstrReturn = lstrReturn & EncodeString(mvarSimpleValue)
    Case XMLRPC_STRUCT
        lstrReturn = lstrReturn & vbCrLf & minsStructValue.EncodeXML(Level + 2)
        lstrReturn = lstrReturn & Indent(Level + 1)
    End Select

'---------------------------------------------------
'   Add the </tag> tag
    lstrReturn = lstrReturn & "</" & lstrTag & ">" & vbCrLf
    
'---------------------------------------------------
'   Add the </value> tag
    lstrReturn = lstrReturn & Indent(Level) & "</value>" & vbCrLf

'---------------------------------------------------
'   Return the XML fragment
    EncodeXML = lstrReturn

End Function

'================================================
'
'   PRIVATE EncodeString
'
'   Encodes the string to standard.
'
Private Function EncodeString(ByVal StringToEncode As String)
    Dim linsXMLUtility As New XMLUtility
    EncodeString = linsXMLUtility.EncodeEscapeCodes(StringToEncode)
End Function
