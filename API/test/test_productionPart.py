# *************************************************************************************************
# FileName : test_productionPart.py
# Author   : Christian Marty
# Date     : 16.02.2024
# License  : MIT
# Website  : www.christian-marty.ch
# *************************************************************************************************
import eerp
from schema_validation_helper import validate_schema

eerp = eerp.Eerp()
eerp.login()


def test_production_part_list_schema():
    schema = {
        "$schema": "http://json-schema.org/draft-04/schema#",
        "type": "array",
        "items": [{
            "type": "object",
            "properties": {
                "Prefix": {
                    "type": "string"
                },
                "Number": {
                    "type": "string"
                },
                "ItemCode": {
                    "type": "string"
                },
                "Description": {
                    "type": "string"
                },
                "BillOfMaterial_TotalQuantityUsed": {
                    "type": ["integer", "null"]
                },
                "BillOfMaterial_NumberOfOccurrence": {
                    "type": ["integer", "null"]
                },
            },
            "required": [
                "Prefix",
                "Number",
                "ItemCode",
                "Description",
                "BillOfMaterial_TotalQuantityUsed",
                "BillOfMaterial_NumberOfOccurrence"
            ]
        }]
    }

    data = eerp.productionPart.list()
    validate_schema(instance=data, schema=schema)
