# *************************************************************************************************
# FileName : test_unitOfMeasurement.py
# Author   : Christian Marty
# Date     : 16.02.2024
# License  : MIT
# Website  : www.christian-marty.ch
# *************************************************************************************************
import eerp
from schema_validation_helper import validate_schema

eerp = eerp.Eerp()
eerp.login()


def test_workOrder_list_schema():
    schema = {
        "$schema": "http://json-schema.org/draft-04/schema#",
        "type": "array",
        "items": [{
            "type": "object",
            "properties": {
                "ItemCode": {"type": "string"},
                "WorkOrderNumber": {"type": "integer"},
                "Name": {"type": "string"},
                "Quantity": {"type": "integer"},
                "Status": {"type": "string"},
                "ProjectName": {"type": ["string", "null"]},
                "ProjectItemCode": {"type": ["string", "null"]}
            },
            "required": [
                "Name",
                "WorkOrderNumber",
                "ItemCode",
                "ProjectName",
                "ProjectNumber",
                "ProjectItemCode",
                "Quantity",
                "Status"
            ]
        }]
    }

    data = eerp.workOrder.list()
    validate_schema(instance=data, schema=schema)
