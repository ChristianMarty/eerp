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
                "ItemCode": {"type": "string"},
                "BillOfMaterialNumber": {"type": "string"},
                "Name": {"type": "string"},
                "Description": {"type": "string"}
            },
            "additionalProperties": False,
            "minProperties": 4
        }]
    }

    data = eerp.billOfMaterial.list()
    validate_schema(instance=data, schema=schema)
