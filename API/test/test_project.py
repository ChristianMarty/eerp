# *************************************************************************************************
# FileName : test_project.py
# Author   : Christian Marty
# Date     : 16.02.2024
# License  : MIT
# Website  : www.christian-marty.ch
# *************************************************************************************************
import eerp
from schema_validation_helper import validate_schema

eerp = eerp.Eerp()
eerp.login()


def test_project_list_schema():
    schema = {
        "$schema": "http://json-schema.org/draft-04/schema#",
        "type": "array",
        "items": [{
            "type": "object",
            "properties": {
                "ItemCode": {
                    "type": "string"
                },
                "ProjectNumber": {
                    "type": "integer"
                },
                "Name": {
                    "type": "string"
                },
                "Description": {
                    "type": "string"
                }
            },
            "required": [
                "ProjectNumber",
                "ItemCode",
                "Name",
                "Description"
            ]
        }]
    }

    data = eerp.project.list()
    validate_schema(instance=data, schema=schema)
