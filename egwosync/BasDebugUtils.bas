Attribute VB_Name = "basDebugUtils"
'#################################################################################################
'# BasUtilities.bas
'# Various multipurpose functions for the egwosync module of eGroupWare
'#
'# Please visit egroupware.org for more information
'# This software is distributed under the GPL and is provided as-is. I assume no responsibility
'# for its usage or losses of any kind that may ensue thereof or otherwise. Feedback is nice:
'# heisters[at]0x09.com
'#################################################################################################

'***********************************************************************************************
' This is useful as a debug subroutine: it will print any XMLRPC Value without the developer
' knowing what type the value is.
'***********************************************************************************************
Public Sub PrintXMLRPCValue(vItem As XMLRPCValue)
    'linsValue holds each element of an array for recursive calling
    Dim linsValue As XMLRPCValue
    'linsMember holds each member of a structure for recursive calling
    Dim linsMember As XMLRPCMember
    
    'Define a printing process for each XMLRPC data type
    Select Case vItem.ValueType
        'Arrays
        Case XMLRPC_ARRAY
            Debug.Print "--Array Start: "
            For Each linsValue In vItem.ArrayValue
                PrintXMLRPCValue linsValue
            Next
            Debug.Print "--Array End"
        'Base 64
        Case XMLRPC_BASE64
            Debug.Print "Base 64: " & vItem.Base64Value
        'Booleans
        Case XMLRPC_BOOLEAN
            Debug.Print "Boolean: " & vItem.BooleanValue
        'Dates and times
        Case XMLRPC_DATETIME
            Debug.Print "Date/time: " & vItem.DateTimeValue
        'Double numbers
        Case XMLRPC_DOUBLE
            Debug.Print "Double: " & vItem.DoubleValue
            'Debug.Print vItem.DoubleValue
        'Integers
        Case XMLRPC_INT_I4
            Debug.Print "XMLRPC Integer: " & vItem.IntegerValue
        'Nil values
        Case XMLRPC_NIL
            Debug.Print "Nil value"
        'Strings
        Case XMLRPC_STRING
            Debug.Print "String: " & vItem.StringValue
        'Structures
        Case XMLRPC_STRUCT
            Debug.Print "--Structure Start: "
            For Each linsMember In vItem.StructValue
                PrintXMLRPCValue linsMember.Value
            Next
            Debug.Print "--Structure End"
        'If the value type is unrecognized
        Case Else
            Debug.Print "Attempt to print XMLRPCValue failed: ValueType not recognized."
        End Select
End Sub
