# *************************************************************************************************
# FileName : metrology.py
# Author   : Christian Marty
# Date     : 16.02.2024
# License  : MIT
# Website  : www.christian-marty.ch
# *************************************************************************************************

class TestSystem(object):
    def __init__(self, metrology_instance):
        self._metrology = metrology_instance

    def list(self):
        data = self._metrology.get('metrology/testSystem')
        return data

    def item(self, test_system_code: str | int):
        data = self._metrology.get('metrology/testSystem/item', {"TestSystemNumber": test_system_code})
        return data


class Metrology(object):
    def __init__(self, eerp_instance):
        self._eerp = eerp_instance
        self.testSystem = TestSystem(eerp_instance)
