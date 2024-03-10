# *************************************************************************************************
# FileName : test_process.py
# Author   : Christian Marty
# Date     : 16.02.2024
# License  : MIT
# Website  : www.christian-marty.ch
# *************************************************************************************************
import eerp
from schema_validation_helper import validate_schema

eerp = eerp.Eerp()
eerp.login()


def test_process_list_schema():
    schema = {
        "$schema": "http://json-schema.org/draft-04/schema#",
        "type": "array",
        "items": [{
            "type": "object",
            "properties": {
                "Name": {"type": "string"},
                "Description": {"type": "string"},
                "Path": {"type": "string"}
            },
            "additionalProperties": False,
            "minProperties": 3
        }]
    }

    data = eerp.process.list()
    validate_schema(instance=data, schema=schema)
