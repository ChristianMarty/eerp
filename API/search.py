# *************************************************************************************************
# FileName : search.py
# Author   : Christian Marty
# Date     : 16.02.2024
# License  : MIT
# Website  : www.christian-marty.ch
# *************************************************************************************************

class Search(object):
    def __init__(self, eerp_instance):
        self._eerp = eerp_instance

    def search(self, search: str):
        data = self._eerp.get('search', {"search": search})
        return data
