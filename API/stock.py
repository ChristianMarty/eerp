# *************************************************************************************************
# FileName : stock.py
# Author   : Christian Marty
# Date     : 16.02.2024
# License  : MIT
# Website  : www.christian-marty.ch
# *************************************************************************************************

class Stock(object):
    def __init__(self, eerp_instance):
        self._eerp = eerp_instance

    def list(self):
        data = self._eerp.get('stock')
        return data
