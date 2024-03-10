# *************************************************************************************************
# FileName : country.py
# Author   : Christian Marty
# Date     : 16.02.2024
# License  : MIT
# Website  : www.christian-marty.ch
# *************************************************************************************************
import eerp
from schema_validation_helper import validate_schema

eerp = eerp.Eerp()
eerp.login()


def test_country_list_schema():
    schema = {
        "$schema": "http://json-schema.org/draft-04/schema#",
        "type": "array",
        "items": [{
            "type": "object",
            "properties": {
                "Alpha2Code": {"type": "string"},
                "Alpha3Code": {"type": ["string", "null"]},
                "NumericCode": {"type": ["integer", "null"]},
                "ShortName": {"type": "string"},
                "PhonePrefix": {"type": "string"}
            },
            "additionalProperties": False,
            "minProperties": 5
        }]
    }

    data = eerp.country.list()
    validate_schema(instance=data, schema=schema)
