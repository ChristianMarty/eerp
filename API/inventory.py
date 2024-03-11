# *************************************************************************************************
# FileName : inventory.py
# Author   : Christian Marty
# Date     : 16.02.2024
# License  : MIT
# Website  : www.christian-marty.ch
# *************************************************************************************************

class Inventory(object):
    def __init__(self, eerp_instance):
        self._eerp = eerp_instance

    def list(self):
        data = self._eerp.get('inventory')
        return data

    def item(self, inventory_number: str | int):
        data = self._eerp.get('inventory/item', {"InventoryNumber": inventory_number})
        return data
