# *************************************************************************************************
# FileName : test_stock.py
# Author   : Christian Marty
# Date     : 16.02.2024
# License  : MIT
# Website  : www.christian-marty.ch
# *************************************************************************************************
import eerp
from schema_validation_helper import validate_schema

eerp = eerp.Eerp()
eerp.login()


def test_stock_list_schema():
    schema = {
        "$schema": "http://json-schema.org/draft-04/schema#",
        "type": "array",
        "items": [{
            "type": "object",
            "properties": {
                "ItemCode": {"type": "string"},
                "StockNumber": {"type": "string"},
                "Quantity": {"type": "integer"},
                "LocationName": {"type": "string"},
                "LocationCode": {"type": "string"},
                "Description": {"type": "string"},
                "ManufacturerPartNumber": {"type": "string"},
                "ManufacturerPartItemId": {"type": "integer"},
                "ManufacturerName": {"type": "string"},
                "ManufacturerId": {"type": "integer"}
            },
            "additionalProperties": False,
            "minProperties": 10
        }]
    }

    data = eerp.stock.list()
    validate_schema(instance=data, schema=schema)
