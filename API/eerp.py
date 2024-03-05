# *************************************************************************************************
# FileName : eerp.py
# Author   : Christian Marty
# Date     : 16.02.2024
# License  : MIT
# Website  : www.christian-marty.ch
# *************************************************************************************************
import os

import requests
from dotenv import load_dotenv

import assembly
import billOfMaterial
import country
import document
import inventory
import location
import metrology
import peripheral
import process
import productionPart
import project
import purchase
import renderer
import report
import search
import specificationPart
import stock
import unitOfMeasurement
import vendor
import workOrder


class Eerp:
    def __init__(self):
        load_dotenv()
        self._base_path = "http://localhost/api.php/"
        self._session = None
        self._idempotency = None
        self._username = os.getenv("eerp_username")
        self._password = os.getenv("eerp_password")

        self.assembly = assembly.Assembly(self)
        self.billOfMaterial = billOfMaterial.BillOfMaterial(self)
        self.country = country.Country(self)
        self.document = document.Document(self)
        self.inventory = inventory.Inventory(self)
        self.location = location.Location(self)
        self.metrology = metrology.Metrology(self)
        self.peripheral = peripheral.Peripheral(self)
        self.process = process.Process(self)
        self.productionPart = productionPart.ProductionPart(self)
        self.project = project.Project(self)
        self.purchase = purchase.Purchase(self)
        self.renderer = renderer.Renderer(self)
        self.report = report.Report(self)
        self.search = search.Search(self)
        self.specificationPart = specificationPart.SpecificationPart(self)
        self.stock = stock.Stock(self)
        self.unitOfMeasurement = unitOfMeasurement.UnitOfMeasurement(self)
        self.vendor = vendor.Vendor(self)
        self.workOrder = workOrder.WorkOrder(self)


    def login(self):
        loginInfo = {"username": self._username, "password": self._password}
        self._session = requests.Session()
        r = self._session.post(self._base_path + '/user/login', json=loginInfo)
        self.handle_response(r)

        return r.ok

    def logout(self):
        r = self._session.post(self._base_path + '/user/logout')
        self._idempotency = None
        return r.ok

    def get(self, url, parameter=None):
        r = self._session.get(self._base_path + url, params=parameter)
        return self.handle_response(r)

    def post(self, url, data):
        headers = {'Idempotency-Key': self._idempotency}
        r = self._session.post(self._base_path + url, json=data, headers=headers)
        return self.handle_response(r)

    def handle_response(self, response):
        assert response.status_code == 200, "response error"
        payload = response.json()
        assert payload['error'] is None, payload['error']
        assert payload['loggedin'] is True, "Not logged in"
        assert self._idempotency is None or payload['idempotency'] == self._idempotency, "idempotency token error"

        return payload['data']
