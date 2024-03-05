# *************************************************************************************************
# FileName : test_search.py
# Author   : Christian Marty
# Date     : 16.02.2024
# License  : MIT
# Website  : www.christian-marty.ch
# *************************************************************************************************
import eerp
from schema_validation_helper import validate_schema

eerp = eerp.Eerp()
eerp.login()


def test_search_list_schema():
    schema = {
        "$schema": "http://json-schema.org/draft-04/schema#",
        "type": "array",
        "items": [{
            "type": "object",
            "properties": {
                "Category": {"type": "string"},
                "Item": {"type": "string"},
                "RedirectCode": {"type": ["integer", "string"]},
                "Description": {"type": "string"},
                "LocationPath": {"type": "string"}
            },
            "additionalProperties": False,
            "minProperties": 5
        }]
    }

    data = eerp.search.search("")
    validate_schema(instance=data, schema=schema)
