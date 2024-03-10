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
                "InventoryNumber": {"type": "string"},
                "PicturePath": {"type": "string"},
                "Title": {"type": "string"},
                "ManufacturerName": {"type": "string"},
                "Type": {"type": "string"},
                "SerialNumber": {"type": "string"},
                "Status": {"type": "string"},
                "LocationName": {"type": "string"}
            },
            "additionalProperties": False,
            "minProperties": 9
        }]
    }

    data = eerp.inventory.list()
    validate_schema(instance=data, schema=schema)
