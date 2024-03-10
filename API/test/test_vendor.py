# *************************************************************************************************
# FileName : test_vendor.py
# Author   : Christian Marty
# Date     : 16.02.2024
# License  : MIT
# Website  : www.christian-marty.ch
# *************************************************************************************************
import eerp
from schema_validation_helper import validate_schema

eerp = eerp.Eerp()
eerp.login()


def test_vendor_list_schema():
    schema = {
        "$schema": "http://json-schema.org/draft-04/schema#",
        "type": "array",
        "items": [
            {
                "type": "object",
                "properties": {
                    "Id": {"type": "integer"},
                    "FullName": {"type": "string"},
                    "ShortName": {"type": ["null", "string"]},
                    "AbbreviatedName": {"type": ["null", "string"]},
                    "DisplayName": {"type": "string"},
                    "IsSupplier": {"type": "boolean"},
                    "IsManufacturer": {"type": "boolean"},
                    "IsContractor": {"type": "boolean"},
                    "IsCarrier": {"type": "boolean"},
                    "IsCustomer": {"type": "boolean"},
                    "Note": {"type": ["null", "string"]},
                    "ParentId": {"type": ["null", "integer"]}
                },
                "additionalProperties": False,
                "minProperties": 12
            }
        ]
    }

    data = eerp.vendor.list()
    validate_schema(instance=data, schema=schema)
