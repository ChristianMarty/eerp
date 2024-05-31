# *************************************************************************************************
# FileName : test_purchase.py
# Author   : Christian Marty
# Date     : 16.02.2024
# License  : MIT
# Website  : www.christian-marty.ch
# *************************************************************************************************
import eerp
from schema_validation_helper import validate_schema

eerp = eerp.Eerp()
eerp.login()

schema_item_purchase_line = {
    "$schema": "http://json-schema.org/draft-04/schema#",
    "type": "object",
    "properties": {
        "ItemCode": {"type": "string"},
        "PurchaseOrderNumber": {"type": "integer"},
        "LineNumber": {"type": "integer"},
        "Price": {"type": "number"},
        "PriceAfterDiscount": {"type": "number"},
        "ProductionPartNumber": {"type": "string"},
        "Discount": {"type": "number"},
        "CurrencyCode": {"type": "string"},
        "SupplierName": {"type": "string"},
        "SupplierId": {"type": "integer"},
        "PurchaseDate": {"type": "string"},
        "OrderReference": {"type": "string"},
        "Quantity": {"type": ["integer", "number"]},
        "Description": {"type": "string"},

        # Following is optional
        "CostType": {"type": "string"}
    },
    "additionalProperties": False,
    "minProperties": 14
}

def test_purchase_list_schema():
    schema = {
        "$schema": "http://json-schema.org/draft-04/schema#",
        "type": "array",
        "items": [{
            "type": "object",
            "properties": {
                "ItemCode": {"type": "string"},
                "PurchaseOrderNumber": {"type": "integer"},
                "Title": {"type": "string"},
                "Description": {"type": "string"},
                "PurchaseDate": {"type": "string"},
                "OrderNumber": {"type": ["null", "string"]},
                "AcknowledgementNumber": {"type": ["null", "string"]},
                "QuotationNumber": {"type": ["null", "string"]},
                "Status": {"type": "string"},
                "SupplierName": {"type": "string"},
                "SupplierId": {"type": "integer"},
                "ReceiveProgress": {"type": "integer"},
                "ExpectedCompletionDate": {"type": "string"},
            },
            "additionalProperties": False,
            "minProperties": 13
        }]
    }

    data = eerp.purchase.list()
    validate_schema(instance=data, schema=schema)
