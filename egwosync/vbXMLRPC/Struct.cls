VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
  Persistable = 0  'NotPersistable
  DataBindingBehavior = 0  'vbNone
  DataSourceBehavior  = 0  'vbNone
  MTSTransactionMode  = 0  'NotAnMTSObject
END
Attribute VB_Name = "XMLRPCStruct"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = False
Attribute VB_Exposed = True
Attribute VB_HelpID = 8000
Attribute VB_Description = "Models an XML-RPC struct structure. Contains a number of AddMember methods to add members to the array. Also contains an Item method for getting a specific member in the struct and a Count method to return the number of members in the struct. Supports For ... Each."
'===============================================================================
'
'  Title             : Struct.cls
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
'  Purpose           : XML-RPC Struct Structure
'  Notes             : Models an XML-RPC struct structure.
'                      Contains a number of AddMember structures
'                      to add members to the struct. Also contains a
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

Private Const MODULETITLE = APPNAME & "#Struct.cls"

'---------------------------------------------------
'   Internal collection class for members
Private mcolMembers As New Collection

'================================================
'   P R O P E R T I E S
'================================================

'================================================
'
'   PUBLIC Count
'
'   Read-Only
'
'   Returns the number of Members in the collection
'
Public Property Get Count() As Long
Attribute Count.VB_Description = "Members in the Struct"
Attribute Count.VB_HelpID = 8450
    Count = mcolMembers.Count
End Property

'================================================
'
'   PRIVATE Add
'
'   Adds a member class to the struct
'
Private Sub Add(ByVal Name As String, ByVal Value As XMLRPCValue)
    Dim linsMember As New XMLRPCMember
    
    linsMember.Create Name, Value
    
    mcolMembers.Add linsMember
End Sub

'================================================
'
'   PUBLIC AddNil
'
'   Adds a nil to the struct
'
Public Sub AddNil(ByVal Name As String)
Attribute AddNil.VB_Description = "Adds a nil to the struct"
Attribute AddNil.VB_HelpID = 8325
    AddSimple XMLRPC_NIL, Name, vbNull
End Sub

'================================================
'
'   PUBLIC AddInteger
'
'   Adds an integer to the struct
'
Public Sub AddInteger(ByVal Name As String, ByVal Value As Long)
Attribute AddInteger.VB_Description = "Adds an integer to the struct."
Attribute AddInteger.VB_HelpID = 8300
    AddSimple XMLRPC_INT_I4, Name, Value
End Sub

'================================================
'
'   PUBLIC AddBoolean
'
'   Adds a boolean to the struct
'
Public Sub AddBoolean(ByVal Name As String, ByVal Value As Boolean)
Attribute AddBoolean.VB_Description = "Adds a boolean to the struct."
Attribute AddBoolean.VB_HelpID = 8150
    AddSimple XMLRPC_BOOLEAN, Name, Value
End Sub

'================================================
'
'   PUBLIC AddString
'
'   Adds a string to the struct
'
Public Sub AddString(ByVal Name As String, ByVal Value As String)
Attribute AddString.VB_Description = "Adds a string to the struct."
Attribute AddString.VB_HelpID = 8350
    AddSimple XMLRPC_STRING, Name, Value
End Sub

'================================================
'
'   PUBLIC AddDouble
'
'   Adds a double to the struct
'
Public Sub AddDouble(ByVal Name As String, ByVal Value As Double)
Attribute AddDouble.VB_Description = "Adds a double to the struct."
Attribute AddDouble.VB_HelpID = 8250
    AddSimple XMLRPC_DOUBLE, Name, Value
End Sub

'================================================
'
'   PUBLIC AddDateTime
'
'   Adds a datetime to the struct
'
Public Sub AddDateTime(ByVal Name As String, ByVal Value As Date)
Attribute AddDateTime.VB_Description = "Adds a datetime  to the struct."
Attribute AddDateTime.VB_HelpID = 8200
    AddSimple XMLRPC_DATETIME, Name, Value
End Sub

'================================================
'
'   PUBLIC AddBase64
'
'   Adds a base64 to the struct
'
Public Sub AddBase64(ByVal Name As String, ByVal Value As XMLRPCBase64)
Attribute AddBase64.VB_Description = "Adds a base64 to the struct."
Attribute AddBase64.VB_HelpID = 8100
    Dim linsValue As New XMLRPCValue
    linsValue.CreateAsBase64 Value
    Add Name, linsValue
End Sub

'================================================
'
'   FRIEND AddSimple
'
'   Adds a simple value to the struct.
'   Note it is a friend to allow functions
'   within the DLL to call it but not outside.
'
Friend Sub AddSimple(ByVal ValueType As XMLRPC_ValueTypes, ByVal Name As String, ByVal Value As Variant)
    Dim linsValue As New XMLRPCValue
    linsValue.CreateAsSimple ValueType, Value
    Add Name, linsValue
End Sub

'================================================
'
'   PUBLIC AddStruct
'
'   Adds a struct to the struct
'
Public Sub AddStruct(ByVal Name As String, ByVal Value As XMLRPCStruct)
Attribute AddStruct.VB_Description = "Adds a struct to the struct."
Attribute AddStruct.VB_HelpID = 8400
    Dim linsValue As New XMLRPCValue
    linsValue.CreateAsStruct Value
    Add Name, linsValue
End Sub

'================================================
'
'   PUBLIC AddArray
'
'   Adds an array to the struct
'
Public Sub AddArray(ByVal Name As String, ByVal Value As XMLRPCArray)
Attribute AddArray.VB_Description = "Adds an array to the struct."
Attribute AddArray.VB_HelpID = 8050
    Dim linsValue As New XMLRPCValue
    linsValue.CreateAsArray Value
    Add Name, linsValue
End Sub

'================================================
'
'   PUBLIC Item
'
'   Returns a Member with the passed index
'
Public Function Item(ByVal Index As Long) As XMLRPCMember
Attribute Item.VB_Description = "Returns the member in the struct indicated by the index."
Attribute Item.VB_HelpID = 8500
Attribute Item.VB_UserMemId = 0
    Set Item = mcolMembers.Item(Index)
End Function

'================================================
'
'   PUBLIC GetValueByName
'
'   Returns a value with the passed name
'
Public Function GetValueByName(ByVal Name As String) As XMLRPCValue
Attribute GetValueByName.VB_Description = "Returns a value with the passed name."
Attribute GetValueByName.VB_HelpID = 8475
    Const METHODTITLE = MODULETITLE & "#GetValueByName"
    
    Dim lintCount As Integer
    
    For lintCount = 1 To Count
        If Item(lintCount).Name = Name Then
            Set GetValueByName = Item(lintCount).Value
            Exit Function
        End If
    Next lintCount
    
    Err.Raise vbXMLRPC_ERROR_MEMBERNOTFOUND, _
              METHODTITLE, _
              "Member named '" & Name & "' not found in struct", _
              App.HelpFile, _
              ginsUtility.ErrorHelpContext(vbXMLRPC_ERROR_MEMBERNOTFOUND)

End Function

'================================================
'
'   PUBLIC MemberExists
'
'   Returns whether named member exists in struct
'
Public Function MemberExists(ByVal Name As String) As Boolean
Attribute MemberExists.VB_Description = "Returns whether named member exists in struct"
Attribute MemberExists.VB_HelpID = 8525
    Const METHODTITLE = MODULETITLE & "#MemberExists"
    
    Dim lintCount As Integer
    
    For lintCount = 1 To Count
        If Item(lintCount).Name = Name Then
            MemberExists = True
            Exit Function
        End If
    Next lintCount
    
    MemberExists = False
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
   Set NewEnum = mcolMembers.[_NewEnum]
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
'   <struct>
'       <member>
'           <name>nickname</name>
'           <value>Ev.</value>
'       </member>
'       <member>
'           <name>userid</name>
'           <value>1</value>
'       </member>
'       <member>
'           <name>url</name>
'           <value>http://www.evhead.com</value>
'       </member>
'       <member>
'           <name>email</name>
'           <value>ev@pyra.com</value>
'       </member>
'       <member>
'           <name>lastname</name>
'           <value>Williams</value>
'       </member>
'       <member>
'           <name>firstname</name>
'           <value>Evan</value>
'       </member>
'   </struct>
'
Friend Function DecodeXML(ByVal Node As XMLDOMNode) As String
    Const METHODTITLE = MODULETITLE & "#DecodeXML"
    
    Dim lintCount As Integer       ' Loop counter
    Dim linsMember As XMLRPCMember ' temporary store for an XMLRPC Member
    Dim lstrReturn As String       ' temporary store for the status of
                                   ' the Value XML parsing
   
'---------------------------------------------------
'   Check for empty <struct></struct>
    If Node.Count = 1 Then
        If Node(1).NodeType = XML_VALUE Then
            DecodeXML = ""
            Exit Function
        End If
    End If

'---------------------------------------------------
'   Go through each node checking for <member> tags
    For lintCount = 1 To Node.Count

'--------------------------------------------
'       Free up the processor
        DoEvents

'---------------------------------------------------
'       Pass control to an XMLRPCMember
        Set linsMember = New XMLRPCMember
        lstrReturn = linsMember.DecodeXML(Node(lintCount))
        
'---------------------------------------------------
'       IF there are no errors then add
'       it to the collection of members
        If lstrReturn = "" Then
            mcolMembers.Add linsMember
        
'---------------------------------------------------
'       ELSE there is a problem
'       Pass the status back
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
'   Go through each member class adding <member>
'   tags then passing off to the XMLRPCMember
'   class for the details of the member and then
'   a closing </member> tag
    For lintCount = 1 To Count

'--------------------------------------------
'       Free up the processor
        DoEvents

        lstrReturn = lstrReturn & Indent(Level) & "<member>" & vbCrLf
        lstrReturn = lstrReturn & Me.Item(lintCount).EncodeXML(Level + 1)
        lstrReturn = lstrReturn & Indent(Level) & "</member>" & vbCrLf
    Next lintCount

'---------------------------------------------------
'   Return the XML fragment
    EncodeXML = lstrReturn

End Function

