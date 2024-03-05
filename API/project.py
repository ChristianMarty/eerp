# *************************************************************************************************
# FileName : project.py
# Author   : Christian Marty
# Date     : 16.02.2024
# License  : MIT
# Website  : www.christian-marty.ch
# *************************************************************************************************

class Project(object):
    def __init__(self, eerp_instance):
        self._eerp = eerp_instance

    def list(self):
        data = self._eerp.get('project')
        return data
