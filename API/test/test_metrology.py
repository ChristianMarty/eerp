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
                "ItemCode": {
                    "type": "string"
                },
                "TestSystemNumber": {
                    "type": "string"
                },
                "Name": {
                    "type": "string"
                },
                "Description": {
                    "type": "string"
                }
            },
            "required": [
                "ItemCode",
                "TestSystemNumber",
                "Name",
                "Description"
            ]
        }]
    }

    data = eerp.metrology.list()
    validate_schema(instance=data, schema=schema)
