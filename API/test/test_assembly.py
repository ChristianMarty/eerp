# *************************************************************************************************
# FileName : test_assembly.py
# Author   : Christian Marty
# Date     : 16.02.2024
# License  : MIT
# Website  : www.christian-marty.ch
# *************************************************************************************************
import eerp
from schema_validation_helper import validate_schema


def test_schema_assembly_list(db_connection, eerp_connection):
    schema_assembly_list = {
        "$schema": "http://json-schema.org/draft-04/schema#",
        "type": "array",
        "items": [{
            "type": "object",
            "properties": {
                "ItemCode": {"type": "string"},
                "AssemblyNumber": {"type": "integer"},
                "ProductionPartCode": {"type": ["null", "string"]},
                "Name": {"type": "string"},
                "Description": {"type": "string"}
            },
            "required": [
                "ItemCode",
                "AssemblyNumber",
                "ProductionPartCode",
                "Name",
                "Description"
            ]
        }]
    }

    data = eerp_connection.assembly.list()
    validate_schema(instance=data, schema=schema_assembly_list)


def test_schema_assembly_unit_history_type(db_connection, eerp_connection):
    schema_assembly_unit_history_type = {
        "$schema": "http://json-schema.org/draft-04/schema#",
        "type": "array",
        "items": {
            "type": "string"
        },
        "additionalProperties": False,
    }

    data = eerp_connection.assembly.unit.history.type()
    validate_schema(instance=data, schema=schema_assembly_unit_history_type)


def test_schema_assembly_item(db_connection, eerp_connection):
    schema_assembly_unit_item = {
        "type": "array",
        "items": [{
            "type": "object",
            "properties": {
                "AssemblyUnitNumber": {"type": "integer"},
                "ItemCode": {"type": "string"},
                "SerialNumber": {"type": ["null", "string"]},
                "Note": {"type": "string"},
                "Description": {"type": "string"},
                "LocationName": {"type": "string"},
                "LocationCode": {"type": "string"},
                "WorkOrderName": {"type": "string"},
                "WorkOrderCode": {"type": "string"},
                "ShippingProhibited": {"type": "boolean"},
                "ShippingClearance": {"type": "boolean"},
                "LastHistoryTitle": {"type": ["null", "string"]},
                "LastHistoryType": {"type": ["null", "string"]},
                "LastTestPass": {"type": ["null", "boolean"]},
                "LastInspectionPass": {"type": ["null", "boolean"]},
                "Test": {"type": ["null", "string"]},
                "Inspection": {"type": ["null", "string"]},
            }
        }],
        "additionalProperties": False,
        "minProperties": 17
    }

    schema_assembly_item = {
        "$schema": "http://json-schema.org/draft-04/schema#",
        "type": "object",
        "properties": {
            "ItemCode": {"type": "string"},
            "AssemblyNumber": {"type": "integer"},
            "ProductionPartCode": {"type": ["null", "string"]},
            "Name": {"type": "string"},
            "Description": {"type": "string"},
            "Unit": schema_assembly_unit_item
        },
        "required": [
            "ItemCode",
            "AssemblyNumber",
            "ProductionPartCode",
            "Name",
            "Description",
            "Unit"
        ],
        "additionalProperties": False
    }

    data = eerp_connection.assembly.item(98795)
    validate_schema(instance=data, schema=schema_assembly_item)


def test_schema_assembly_unit_item(db_connection, eerp_connection):
    schema_history_item = {
        "type": "object",
        "properties": {
            "ItemCode": {"type": "string"},
            "AssemblyUnitHistoryNumber": {"type": "integer"},
            "Title": {"type": "string"},
            "Description": {"type": "string"},
            "Type": {"type": "string"},
            "ShippingProhibited": {"type": "boolean"},
            "ShippingClearance": {"type": "boolean"},
            "EditToken": {"type": "string"},
            "Date": {"type": "string"},
        },
        "additionalProperties": False,
        "minProperties": 9
    }

    schema = {
        "$schema": "http://json-schema.org/draft-04/schema#",
        "type": "object",
        "properties": {
            "ItemCode": {"type": "string"},
            "AssemblyUnitNumber": {"type": "integer"},
            "SerialNumber": {"type": ["null", "string"]},
            "Note": {"type": "string"},
            "AssemblyCode": {"type": "string"},
            "AssemblyName": {"type": "string"},
            "LocationName": {"type": "string"},
            "LocationCode": {"type": "string"},
            "WorkOrderName": {"type": "string"},
            "WorkOrderCode": {"type": "string"},
            "ShippingProhibited": {"type": "boolean"},
            "ShippingClearance": {"type": "boolean"},
            "History": {
                "type": "array",
                "items": schema_history_item
            }
        },
        "additionalProperties": False
    }

    data = eerp_connection.assembly.unit.item(47958)
    validate_schema(instance=data, schema=schema)


def test_schema_assembly_unit_history_item(db_connection, eerp_connection):
    schema_history_item = {
        "type": "object",
        "properties": {
            "ItemCode": {"type": "string"},
            "AssemblyUnitHistoryNumber": {"type": "integer"},
            "Title": {"type": "string"},
            "Description": {"type": "string"},
            "Type": {"type": "string"},
            "ShippingProhibited": {"type": "boolean"},
            "ShippingClearance": {"type": "boolean"},
            "Date": {"type": "string"},
            "Data": {"type": ["null", "object"]},
            "EditToken": {"type": "string"},
            "AssemblyUnitCode": {"type": "string", },
        },
        "additionalProperties": False,
        "minProperties": 11
    }
    data = eerp_connection.assembly.unit.history.item(44512)
    validate_schema(instance=data, schema=schema_history_item)
