<template>
  <div class="app-container">
    <h1>Create Stock Item</h1>
    <el-divider />

    <el-form ref="inputForm" :model="createParameter" :rules="rules" class="form-container" label-width="150px">
      <el-form-item label="Manufacturer:" prop="ManufacturerId">
        <el-select
          v-model="createParameter.ManufacturerId"
          filterable
          placeholder="Select"
          @change="getParts(createParameter.ManufacturerId)"
        >
          <el-option v-for="item in manufacturer" :key="item.Id" :label="item.Name" :value="item.Id" />
        </el-select>
      </el-form-item>

      <el-form-item label="MPN:" prop="ManufacturerPartNumber">
        <el-autocomplete
          v-model="createParameter.ManufacturerPartNumber"
          style="width: 100%;"
          placeholder="Please input"
          :fetch-suggestions="searchManufacturerPartNumber"
          autosize
        />
      </el-form-item>

      <el-form-item label="Supplier:" prop="Supplier">
        <el-select v-model="createParameter.SupplierId" filterable placeholder="Select">
          <el-option v-for="item in suppliers" :key="item.Id" :label="item.Name" :value="item.Id" />
        </el-select>
      </el-form-item>

      <el-form-item label="Supplier SKU:" prop="SupplierPartNumber">
        <el-input v-model="createParameter.SupplierPartNumber" placeholder="Please input" />
      </el-form-item>

      <el-form-item label="Order Reference:" prop="OrderReference">
        <el-input v-model="createParameter.OrderReference" placeholder="Please input" />
      </el-form-item>

      <el-form-item label="Quantity:" prop="Quantity">
        <el-input-number v-model="createParameter.Quantity" placeholder="Please input" :controls="false" />
      </el-form-item>

      <el-form-item label="Date:" prop="Date">
        <el-date-picker v-model="createParameter.Date" type="week" format="yyyy Week WW" value-format="yyyy-MM-dd">
          >
        </el-date-picker>
      </el-form-item>

      <el-form-item label="Lot Number:" prop="LotNumber">
        <el-input v-model="createParameter.LotNumber" placeholder="Please input" />
      </el-form-item>

      <el-form-item label="Location" prop="Location">
        <el-input ref="locNrInput" v-model="createParameter.LocationCode" placeholder="Please input" />
        <el-cascader-panel
          v-model="createParameter.LocationCode"
          :options="locations"
          :props="{
            emitPath: false,
            value: 'LocNr',
            label: 'Name',
            children: 'Children',
            checkStrictly: true
          }"
        />
      </el-form-item>

      <el-form-item label="Print settings:">

        <el-select v-model="selectedLabelId">
          <el-option v-for="item in label" :key="Number(item.Id)" :label="item.Name" :value="Number(item.Id)" />
        </el-select>

        <el-select v-model="selectedPrinterId">
          <el-option v-for="item in printer" :key="Number(item.Id)" :label="item.Name" :value="Number(item.Id)" />
        </el-select>

        <el-checkbox v-model="autoPrint" style="margin-left: 20px">Print after Save</el-checkbox>
      </el-form-item>

      <el-form-item>
        <el-button type="primary" @click="save">Save</el-button>

        <el-button type="danger" @click="resetForm">Clear</el-button>
      </el-form-item>
    </el-form>

    <printDialog :visible.sync="printDialogVisible" :data="partData" @print="printHandler" />  </div>
</template>

<script>
import * as labelTemplate from '@/utils/labelTemplate'
import * as defaultSetting from '@/utils/defaultSetting'

import printDialog from './components/printDialog'

import Vendor from '@/api/vendor'
const vendor = new Vendor()

import Stock from '@/api/stock'
const stock = new Stock()

import Location from '@/api/location'
const location = new Location()

import ManufacturerPart from '@/api/manufacturerPart'
const manufacturerPart = new ManufacturerPart()

import Print from '@/api/print'
const print = new Print()

export default {
  name: 'LocationAssignment',
  components: { printDialog },
  data() {
    return {

      partData: Object.assign({}, stock.item.createResponse),

      rules: {
        ManufacturerPartNumber: [
          { required: true, message: 'Please input the Manufacturer Part Number', trigger: 'change' }
        ],
        ManufacturerId: [
          { required: true, message: 'Please select the Manufacturer', trigger: 'change' }
        ],
        Date: [
          { required: true, message: 'Please select the year and week', trigger: 'change' }
        ],
        Quantity: [
          { required: true, message: 'Please input the quantity', trigger: 'change' }
        ],
        LocationCode: [
          { required: true, message: 'Please select the location', trigger: 'change' }
        ]
      },
      autoPrint: false,
      inputError: false,
      manufacturer: null,
      locations: null,
      partOptions: null,
      suppliers: null,

      label: null,
      printer: {},
      selectedPrinterId: 0,
      selectedLabelId: 0,

      printDialogVisible: false,

      createParameter: Object.assign({}, stock.item.createParameter)
    }
  },
  async mounted() {
    this.suppliers = await vendor.search(true, false, false)
    this.locations = await location.search()
    this.manufacturer = await vendor.search(false, true, false)

    this.label = await print.label.search('Stock')

    this.selectedPrinterId = defaultSetting.defaultSetting().StockLabelPrinter
    this.selectedLabelId = defaultSetting.defaultSetting().StockLabel
    this.printer = await print.printer.search()

    this.resetForm()
  },
  methods: {
    searchManufacturerPartNumber(queryString, cb) {
      const options = this.partOptions
      const out = []
      options.forEach(element => {
        out.push({ value: element.ManufacturerPartNumber })
      })
      cb(
        out.filter(
          element =>
            element.value.toLowerCase().indexOf(queryString.toLowerCase()) === 0
        )
      )
    },
    getParts(ManufacturerId) {
      const searchParameters = Object.assign({}, manufacturerPart.searchParameters)
      searchParameters.VendorId = ManufacturerId
      manufacturerPart.search(searchParameters).then(response => {
        this.partOptions = response
      })
    },
    resetForm() {
      this.createParameter = Object.assign({}, stock.item.createParameter)
    },
    isValid() {
      this.$refs.inputForm.validate(valid => {
        if (valid) {
          return true
        } else {
          return false
        }
      })
    },
    save() {
      if (this.isValid() === false) {
        this.$message({
          showClose: true,
          message: 'Invalide Input',
          duration: 3,
          type: 'error'
        })
      } else {
        stock.item.create(this.createParameter).then(response => {
          this.partData = response
          if (this.autoPrint === true) {
            this.print(this.partData)
          } else {
            this.printDialogVisible = true
          }
        }).catch(response => {
          this.$message({
            showClose: true,
            message: response,
            duration: 0,
            type: 'error'
          })
        })
      }
    },
    closeDialog() {
      this.printDialogVisible = false
      this.resetForm()
    },
    printHandler(printData) {
      var labelData = {
        $Barcode: printData.Barcode,
        $StockId: printData.StockNo,
        $Mfr: printData.ManufacturerName,
        $MPN: printData.ManufacturerPartNumber,
        $PartNo: printData.OrderReference,
        $Description: printData.Description
      }
      var labelTemplateObject = this.label.find(element => { return Number(element.Id) === this.selectedLabelId })
      var labelCode = labelTemplate.labelTemplate(labelTemplateObject.Code, labelData)

      print.print('raw', labelTemplateObject.Language, this.selectedPrinterId, labelCode).then(response => {
        this.resetForm()
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    }
  }
}
</script>
