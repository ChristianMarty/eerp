# *************************************************************************************************
# FileName : test_inventory.py
# Author   : Christian Marty
# Date     : 16.02.2024
# License  : MIT
# Website  : www.christian-marty.ch
# *************************************************************************************************
import eerp
from schema_validation_helper import validate_schema

eerp = eerp.Eerp()
eerp.login()


def test_inventory_list_schema():
    schema = {
        "$schema": "http://json-schema.org/draft-04/schema#",
        "type": "array",
        "items": [{
            "type": "object",
            "properties": {
                "ItemCode": {"type": "string"},
                "InventoryNumber": {"type": "integer"},
                "PicturePath": {"type": "string"},
                "Title": {"type": "string"},
                "ManufacturerName": {"type": "string"},
                "Type": {"type": "string"},
                "SerialNumber": {"type": "string"},
                "Status": {"type": "string"},
                "CategoryName": {"type": "string"},
                "LocationName": {"type": "string"}
            },
            "additionalProperties": False,
            "minProperties": 9
        }]
    }

    data = eerp.inventory.list()
    validate_schema(instance=data, schema=schema)


def test_inventory_item_schema():
    schema = {
        "$schema": "http://json-schema.org/draft-04/schema#",
        "type": "object",
        "properties": {
            "ItemCode": {"type": "string"},
            "InventoryNumber": {"type": "integer"},
            "PicturePath": {"type": ["string", "null"]},
            "Title": {"type": "string"},
            "Description": {"type": "string"},
            "Note": {"type": "string"},
            "ManufacturerName": {"type": "string"},
            "Type": {"type": "string"},
            "SerialNumber": {"type": "string"},
            "Status": {"type": "string"},
            "Attribute": {"type": "array"},
            "CategoryName": {"type": "string"},
            "Location": {"type": "object"},
            "PurchaseInformation": {"type": "object"},
            "Accessory": {"type": "array"},
            "Documents": {"type": "array"},
            "History": {"type": "array"}
        },
        "additionalProperties": False,
        "minProperties": 17
    }

    data = eerp.inventory.item('Inv-53489')
    validate_schema(instance=data, schema=schema)
