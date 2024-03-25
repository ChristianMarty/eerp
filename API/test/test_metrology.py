# *************************************************************************************************
# FileName : test_metrology.py
# Author   : Christian Marty
# Date     : 16.02.2024
# License  : MIT
# Website  : www.christian-marty.ch
# *************************************************************************************************
import eerp
from schema_validation_helper import validate_schema

eerp = eerp.Eerp()
eerp.login()


def test_metrology_list_schema():
    schema = {
        "$schema": "http://json-schema.org/draft-04/schema#",
        "type": "array",
        "items": [{
            "type": "object",
            "properties": {
                "ItemCode": {"type": "string"},
                "TestSystemNumber": {"type": "string"},
                "Name": {"type": "string"},
                "Description": {"type": "string"}
            },
            "additionalProperties": False,
            "minProperties": 4
        }]
    }

    data = eerp.metrology.list()
    validate_schema(instance=data, schema=schema)

def test_metrology_item_schema():
    schema_item = {
        "$schema": "http://json-schema.org/draft-04/schema#",
        "type": "array",
        "items": [{
            "type": "object",
            "properties": {
                "InventoryNumber": {"type": "integer"},
                "InventoryCode": {"type": "string"},
                "Title": {"type": "string"},
                "ManufacturerName": {"type": "string"},
                "Type": {"type": "string"},
                "SerialNumber": {"type": "string"},
                "Usage": {"type": "string"},
                "CalibrationRequired": {"type": "boolean"},
                "CalibrationDate": {"type": ["string", "null"]},
                "NextCalibrationDate": {"type": ["string", "null"]},
            },
            "additionalProperties": False,
            "minProperties": 10
        }]
    }
    schema = {
        "$schema": "http://json-schema.org/draft-04/schema#",
        "type": "object",
        "properties": {
            "ItemCode": {"type": "string"},
            "TestSystemNumber": {"type": "integer"},
            "Name": {"type": "string"},
            "Description": {"type": "string"},
            "Item": schema_item
        },
        "additionalProperties": False,
        "minProperties": 5
    }

    data = eerp.metrology.item("TSY-97645")
    validate_schema(instance=data, schema=schema)

    data = eerp.metrology.item("TSY-97645", "2024-03-15")
    validate_schema(instance=data, schema=schema)
