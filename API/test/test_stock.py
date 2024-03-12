# *************************************************************************************************
# FileName : test_stock.py
# Author   : Christian Marty
# Date     : 16.02.2024
# License  : MIT
# Website  : www.christian-marty.ch
# *************************************************************************************************
import eerp
from schema_validation_helper import validate_schema
from test_location import schema_item_location
from test_purchase import schema_item_purchase_line

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

def test_stock_item_schema():
    schema_quantity = {
        "$schema": "http://json-schema.org/draft-04/schema#",
        "type": "object",
        "properties": {
            "Quantity": {"type": "number"},
            "CreateQuantity": {"type": "number"},
            "CreateData": {"type": "string"},
            "Certainty": {
                "type": "object",
                "properties": {
                    "Factor": {"type": "number"},
                    "Rating": {"type": "integer"},
                    "DaysSinceStocktaking": {"type": "integer"},
                    "LastStocktakingDate": {"type": "string"},
                },
                "additionalProperties": False,
                "minProperties": 4
            }
        },
        "additionalProperties": False,
        "minProperties": 4
    }
    schema_part = {
        "$schema": "http://json-schema.org/draft-04/schema#",
        "type": "object",
        "properties": {
            "ManufacturerName": {"type": "string"},
            "ManufacturerId": {"type": "integer"},
            "ManufacturerPartNumber": {"type": "string"},
            "ManufacturerPartNumberId": {"type": "integer"},
            "ManufacturerPartItemId": {"type": ["integer", "null"]},
            "SpecificationPartRevisionId": {"type": ["integer", "null"]}
        },
        "additionalProperties": False,
        "minProperties": 6
    }
    schema_supplier = {
        "$schema": "http://json-schema.org/draft-04/schema#",
        "type": "object",
        "properties": {
            "Name": {"type": "string"},
            "PartNumber": {"type": "string"},
            "VendorId": {"type": "integer"}
        },
        "additionalProperties": False,
        "minProperties": 3
    }
    schema = {
        "$schema": "http://json-schema.org/draft-04/schema#",
        "type": "object",
        "properties": {
            "ItemCode": {"type": "string"},
            "StockNumber": {"type": "string"},
            "LotNumber": {"type": "string"},
            "Description": {"type": "string"},
            "Date": {"type": "string"},
            "DateCode": {"type": "string"},
            "Purchase": schema_item_purchase_line,
            "Supplier": schema_supplier,
            "Part": schema_part,
            "Quantity": schema_quantity,
            "Location": schema_item_location,
            "Deleted": {"type": "boolean"},
        },
        "additionalProperties": False,
        "minProperties": 12
    }

    data = eerp.stock.item('QGSC')
    validate_schema(instance=data, schema=schema)