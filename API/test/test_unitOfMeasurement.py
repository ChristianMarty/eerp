# *************************************************************************************************
# FileName : test_billOfMaterial.py
# Author   : Christian Marty
# Date     : 16.02.2024
# License  : MIT
# Website  : www.christian-marty.ch
# *************************************************************************************************
import eerp
from schema_validation_helper import validate_schema

eerp = eerp.Eerp()
eerp.login()


def test_billOfMaterial_list_schema():
    schema = {
        "$schema": "http://json-schema.org/draft-04/schema#",
        "type": "array",
        "items": [{
            "type": "object",
            "properties": {
                "Id": {
                    "type": "integer"
                },
                "Name": {
                    "type": "string"
                },
                "Unit": {
                    "type": ["string", "null"]
                },
                "Symbol": {
                    "type": ["string", "null"]
                },
                "Countable": {
                    "type": "boolean"
                }
            },
            "required": [
                "Name",
                "Id",
                "Unit",
                "Symbol",
                "Countable"
            ]
        }]
    }

    data = eerp.unitOfMeasurement.list()
    validate_schema(instance=data, schema=schema)
