VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
  Persistable = 0  'NotPersistable
  DataBindingBehavior = 0  'vbNone
  DataSourceBehavior  = 0  'vbNone
  MTSTransactionMode  = 0  'NotAnMTSObject
END
Attribute VB_Name = "XMLRPCFault"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = False
Attribute VB_Exposed = True
Attribute VB_HelpID = 3000
Attribute VB_Description = "Models an XML-RPC fault structure. Contains the fault information from an unsuccessfull call to an XML-RPC server."
'===============================================================================
'
'  Title             : Fault.cls
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
'  Purpose           : XML-RPC Fault Structure
'  Notes             : Contains the fault information from an
'                      unsuccessfull call to an XML-RPC server
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

Private Const MODULETITLE = APPNAME & "#Fault.cls"

'---------------------------------------------------
'   The XMLRPCvalue that contains the
'   struct with the fault data in it
Private minsValue As New XMLRPCValue

'================================================
'   P R O P E R T I E S
'================================================

'================================================
'
'   PUBLIC faultCode
'
'   Read Only.
'   Extracts the fault Code from the fault struct
'
Public Property Get faultCode() As String
Attribute faultCode.VB_Description = "numeric fault code"
Attribute faultCode.VB_HelpID = 3100
    faultCode = minsValue.StructValue.GetValueByName("faultCode").IntegerValue
End Property

'================================================
'
'   PUBLIC faultString
'
'   Read Only.
'   Extracts the fault String from the fault struct
'
Public Property Get faultString() As String
Attribute faultString.VB_Description = "A descrition of the fault"
Attribute faultString.VB_HelpID = 3200
    faultString = minsValue.StructValue.GetValueByName("faultString").StringValue
End Property

'================================================
'   M E T H O D S
'================================================

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
'   <fault>
'       <value>
'           <struct>
'               <member>
'                   <name>faultCode</name>
'                   <value><int>4</int></value>
'               </member>
'               <member>
'                   <name>faultString</name>
'                   <value><string>Too many parameters.</string></value>
'               </member>
'           </struct>
'       </value>
'   </fault>
'
Friend Function DecodeXML(ByVal Node As XMLDOMNode) As String
    Const METHODTITLE = MODULETITLE & "#DecodeXML"
    
    Dim lstrReturn As String       ' temporary store for the status of
                                   ' the Value XML parsing

'--------------------------------------------
'   Free up the processor
    DoEvents

'---------------------------------------------------
'   pass control to XMLRPCValue to
'   process the value node
    Set minsValue = New XMLRPCValue
    lstrReturn = minsValue.DecodeXML(Node(1))
            
'---------------------------------------------------
'   IF there are no errors then
'   check the structure is correct
    If lstrReturn = "" Then
        If minsValue.ValueType = XMLRPC_STRUCT Then
            If minsValue.StructValue.Count = 2 Then
            
                If (minsValue.StructValue(1).Name = "faultCode" And _
                    minsValue.StructValue(1).Value.ValueType = XMLRPC_INT_I4 And _
                    minsValue.StructValue(2).Name = "faultString" And _
                    minsValue.StructValue(2).Value.ValueType = XMLRPC_STRING) Or _
                   (minsValue.StructValue(2).Name = "faultCode" And _
                    minsValue.StructValue(2).Value.ValueType = XMLRPC_INT_I4 And _
                    minsValue.StructValue(1).Name = "faultString" And _
                    minsValue.StructValue(1).Value.ValueType = XMLRPC_STRING) Then
                    DecodeXML = ""
                Else
                    DecodeXML = ginsUtility.XMLErrorName("Fault struct contains incorrect members", -1)
                End If
            Else
                DecodeXML = ginsUtility.XMLErrorName("Fault struct should have two elements", -1)
            End If
        Else
            DecodeXML = ginsUtility.XMLErrorName("Expecting 'fault' 'struct', found '" & ginsUtility.GetXMLRPCType(minsValue.ValueType) & "'", -1)
        End If

'---------------------------------------------------
'   ELSE there are errors pass them back
    Else
        DecodeXML = lstrReturn
    End If
        
End Function
