# *************************************************************************************************
# FileName : test_location.py
# Author   : Christian Marty
# Date     : 16.02.2024
# License  : MIT
# Website  : www.christian-marty.ch
# *************************************************************************************************
import eerp
from schema_validation_helper import validate_schema

eerp = eerp.Eerp()
eerp.login()

schema_item_location = {
    "$schema": "http://json-schema.org/draft-04/schema#",
    "type": "object",
    "properties": {
        "ItemCode": {"type": "string"},
        "LocationNumber": {"type": "integer"},
        "Name": {"type": "string"},
        "Path": {"type": "string"},
        "HomeName": {"type": "string"},
        "HomePath": {"type": "string"}
    },
    "additionalProperties": False,
    "minProperties": 6
}


def test_location_list_schema():
    schema = {
        "$schema": "http://json-schema.org/draft-04/schema#",
        "type": "array",
        "items": [{
            "type": "object",
            "properties": {
                "Name": {"type": "string"},
                "LocationNumber": {"type": "integer"},
                "ItemCode": {"type": "string"},
                "Title": {"type": ["string", "null"]},
                "Description": {"type": ["string", "null"]},
                "Attribute": {
                    "type": "object",
                    "properties": {
                        "EsdSave": {"type": "boolean"},
                    }
                },
                "Children": {"type": "array"}
            },
            "additionalProperties": False,
            "minProperties": 7
        }]
    }

    data = eerp.location.list()
    validate_schema(instance=data, schema=schema)
