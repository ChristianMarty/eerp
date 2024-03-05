# *************************************************************************************************
# FileName : assembly.py
# Author   : Christian Marty
# Date     : 16.02.2024
# License  : MIT
# Website  : www.christian-marty.ch
# *************************************************************************************************

class AssemblyUnitHistory(object):
    def __init__(self, eerp_instance):
        self._eerp = eerp_instance

    def item(self, assembly_unit_history_code: str | int):
        data = self._eerp.get('assembly/unit/history/item', {"AssemblyUnitHistoryNumber": assembly_unit_history_code})
        return data

    def type(self):
        data = self._eerp.get('assembly/unit/history/type')
        return data


class AssemblyUnit(object):
    def __init__(self, eerp_instance):
        self._eerp = eerp_instance
        self.history = AssemblyUnitHistory(eerp_instance)

    def item(self, assembly_unit_code: str | int):
        data = self._eerp.get('assembly/unit/item', {"AssemblyUnitNumber": assembly_unit_code})
        return data


class Assembly(object):
    def __init__(self, eerp_instance):
        self._eerp = eerp_instance
        self.unit = AssemblyUnit(eerp_instance)

    def list(self):
        data = self._eerp.get('assembly')
        return data

    def item(self, assembly_code: str | int):
        data = self._eerp.get('assembly/item', {"AssemblyNumber": assembly_code})
        return data
