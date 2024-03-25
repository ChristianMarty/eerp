# *************************************************************************************************
# FileName : metrology.py
# Author   : Christian Marty
# Date     : 16.02.2024
# License  : MIT
# Website  : www.christian-marty.ch
# *************************************************************************************************

class Metrology(object):
    def __init__(self, eerp_instance):
        self._eerp = eerp_instance

    def list(self):
        data = self._eerp.get('metrology')
        return data

    def item(self, test_system_code: str | int, test_date: str | None = None):
        data = self._eerp.get('metrology/item', {"TestSystemNumber": test_system_code, "TestDate": test_date})
        return data
