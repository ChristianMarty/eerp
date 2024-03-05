# *************************************************************************************************
# FileName : test_document.py
# Author   : Christian Marty
# Date     : 16.02.2024
# License  : MIT
# Website  : www.christian-marty.ch
# *************************************************************************************************
import eerp
from schema_validation_helper import validate_schema

eerp = eerp.Eerp()
eerp.login()


def test_document_list_schema():
    schema = {
        "$schema": "http://json-schema.org/draft-04/schema#",
        "type": "array",
        "items": [{
            "type": "object",
            "properties": {
                "DocumentNumber": {"type": "integer"},
                "Path": {"type": "string"},
                "File": {"type": "string"},
                "Name": {"type": "string"},
                "Type": {"type": "string"},
                "LinkType": {"type": "string"},
                "Hash": {"type": "string"},
                "CreationDate": {"type": "string"},
                "ItemCode": {"type": "string"}
            },
            "required": [
                "DocumentNumber",
                "Path",
                "File",
                "Name",
                "Type",
                "LinkType",
                "Hash",
                "CreationDate",
                "ItemCode"
            ]
        }]
    }

    data = eerp.document.list()
    validate_schema(instance=data, schema=schema)


def test_document_item_schema():
    schema = {
        "$schema": "http://json-schema.org/draft-04/schema#",
        "type": "object",
        "properties": {
            "DocumentNumber": {"type": "integer"},
            "Path": {"type": "string"},
            "Name": {"type": "string"},
            "Note": {"type": ["string", "null"]},
            "Type": {"type": "string"},
            "LinkType": {"type": "string"},
            "Description": {"type": "string"},
            "Hash": {"type": "string"},
            "CreatedBy": {"type": "string"},
            "CreationDate": {"type": "string"},
            "ItemCode": {"type": "string"},
            "Citations": {
                "type": "array",
                "items": [{
                    "type": "object",
                    "properties": {
                        "Category": {"type": "string"},
                        "ItemCode": {"type": "string"},
                        "Description": {"type": "string"}
                    }
                }]
            },
        },
        "required": [
            "DocumentNumber",
            "Path",
            "Name",
            "Note",
            "Type",
            "LinkType",
            "Hash",
            "CreatedBy",
            "CreationDate",
            "ItemCode",
            "Citations"
        ]
    }

    data = eerp.document.item(67491)
    validate_schema(instance=data, schema=schema)
