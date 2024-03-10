# *************************************************************************************************
# FileName : test_peripheral.py
# Author   : Christian Marty
# Date     : 16.02.2024
# License  : MIT
# Website  : www.christian-marty.ch
# *************************************************************************************************
import eerp
from schema_validation_helper import validate_schema

eerp = eerp.Eerp()
eerp.login()


def test_peripheral_list_schema():
    schema = {
        "$schema": "http://json-schema.org/draft-04/schema#",
        "type": "array",
        "items": [{
            "type": "object",
            "properties": {
                "Id": {"type": "integer"},
                "Name": {"type": "string"},
                "DeviceType": {"type": "string"},
                "Ip": {"type": "string"},
                "Port": {"type": "integer"},
                "Language": {"type": "string"},
                "Type": {"type": "string"},
                "Description": {"type": "string"},
                "Driver": {"type": ["string", "null"]}
            },
            "additionalProperties": False,
            "minProperties": 9
        }]
    }

    data = eerp.peripheral.list()
    validate_schema(instance=data, schema=schema)
