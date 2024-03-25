# *************************************************************************************************
# FileName : test_renderer.py
# Author   : Christian Marty
# Date     : 16.02.2024
# License  : MIT
# Website  : www.christian-marty.ch
# *************************************************************************************************
import eerp
from schema_validation_helper import validate_schema

eerp = eerp.Eerp()
eerp.login()


def test_renderer_list_schema():
    schema = {
        "$schema": "http://json-schema.org/draft-04/schema#",
        "type": "array",
        "items": [{
            "type": "object",
            "properties": {
                "Id": {"type": "integer"},
                "Name": {"type": "string"},
                "Description": {"type": "string"},
                "Render": {"type": "string"},
                "Language": {"type": "string"},
                "Code": {"type": "string"},
                "MediumName": {"type": "string"},
                "DatasetName": {"type": "string"}
            },
            "additionalProperties": False,
            "minProperties": 8
        }]
    }

    data = eerp.renderer.list()
    validate_schema(instance=data, schema=schema)
