# *************************************************************************************************
# FileName : document.py
# Author   : Christian Marty
# Date     : 16.02.2024
# License  : MIT
# Website  : www.christian-marty.ch
# *************************************************************************************************

class Document(object):
    def __init__(self, eerp_instance):
        self._eerp = eerp_instance
        self.ingest = self.Ingest(self)

    def list(self):
        data = self._eerp.get('document')
        return data

    def types(self):
        data = self._eerp.get('document/type')
        return data

    def item(self, document_code: str | int):
        data = self._eerp.get('document/item', {"DocumentNumber": document_code})
        return data

    class Ingest(object):
        def __init__(self, document_instance):
            self._document = document_instance

        def list(self):
            data = self._document.get('document/ingest/list')
            return data
