<template>
  <div class="app-container">
    <h1>Create Stock Item</h1>
    <el-divider />

    <el-form
      ref="inputForm"
      :model="formData"
      :rules="rules"
      class="form-container"
      label-width="150px"
    >
      <el-form-item label="Manufacturer:" prop="ManufacturerName">
        <el-select
          v-model="formData.ManufacturerName"
          filterable
          placeholder="Select"
          @change="getParts(formData.Manufacturer)"
        >
          <el-option
            v-for="item in manufacturer"
            :key="item.Name"
            :label="item.Name"
            :value="item.Name"
          />
        </el-select>
      </el-form-item>

      <el-form-item label="MPN:" prop="ManufacturerPartNumber">
        <el-autocomplete
          v-model="formData.ManufacturerPartNumber"
          style="width: 100%;"
          placeholder="Please input"
          :fetch-suggestions="searchManufacturerPartNumber"
          autosize
        />
      </el-form-item>

      <el-form-item label="Supplier:" prop="Supplier">
        <el-select
          v-model="formData.Supplier"
          filterable
          placeholder="Select"
        >
          <el-option
            v-for="item in suppliers"
            :key="item.Name"
            :label="item.Name"
            :value="item.Name"
          />
        </el-select>
      </el-form-item>

      <el-form-item label="Supplier SKU:" prop="SupplierPartNumber">
        <el-input
          v-model="formData.SupplierPartNumber"
          placeholder="Please input"
        />
      </el-form-item>

      <el-form-item label="Order Reference:" prop="OrderReference">
        <el-input
          v-model="formData.OrderReference"
          placeholder="Please input"
        />
      </el-form-item>

      <el-form-item label="Quantity:" prop="Quantity">
        <el-input-number
          v-model="formData.Quantity"
          placeholder="Please input"
          :controls="false"
        />
      </el-form-item>

      <el-form-item label="Date:" prop="Date">
        <el-date-picker
          v-model="formData.Date"
          type="week"
          format="yyyy Week WW"
        >
          >
        </el-date-picker>
      </el-form-item>

      <el-form-item label="Location" prop="Location">
        <el-input
          ref="locNrInput"
          v-model="formData.Location"
          placeholder="Please input"
        />
        <el-cascader-panel
          v-model="formData.Location"
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
        <el-radio-group v-model="labelTemplate">
          <el-radio-button
            :label="smallLabel"
          >Small Label</el-radio-button>
          <el-radio-button
            :label="largeLabel"
          >Large Label</el-radio-button>
        </el-radio-group>
        <el-checkbox
          v-model="autoPrint"
          style="margin-left: 20px"
        >Print after Save</el-checkbox>
      </el-form-item>

      <el-form-item>
        <el-button type="primary" @click="save">Save</el-button>

        <el-button type="danger" @click="resetForm">Clear</el-button>
      </el-form-item>
    </el-form>

    <printDialog
      :visible.sync="printDialogVisible"
      :data="partData"
      @print="print"
    />
  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'
import * as print from '@/utils/printLabel'
import printDialog from './components/printDialog'

const emptyData = {
  ManufacturerName: '',
  ManufacturerPartNumber: '',
  Supplier: '',
  SupplierPartNumber: '',
  OrderReference: '',
  Date: '',
  Quantity: '',
  Location: 'Loc-00000'
}

const returnData = {
  StockId: '',
  ManufacturerName: '',
  Supplier: '',
  ManufacturerPartNumber: '',
  Date: '',
  Quantity: '',
  Location: '',
  Barcode: '',
  SupplierName: '',
  SupplierPartNumber: ''
}

export default {
  name: 'LocationAssignment',
  components: { printDialog },
  data() {
    return {
      formData: Object.assign({}, emptyData),
      partData: Object.assign({}, returnData),

      rules: {
        GctNo: [
          { min: 6, max: 6, message: 'Must be 6 characters', trigger: 'change' }
        ],
        ManufacturerPartNumber: [
          {
            required: true,
            message: 'Please input the Manufacturer Part Number',
            trigger: 'change'
          }
        ],
        ManufacturerName: [
          {
            required: true,
            message: 'Please select the Manufacturer',
            trigger: 'change'
          }
        ],
        Date: [
          {
            required: true,
            message: 'Please select the year and week',
            trigger: 'change'
          }
        ],
        Quantity: [
          {
            required: true,
            message: 'Please input the quantity',
            trigger: 'change'
          }
        ],
        Location: [
          {
            required: true,
            message: 'Please select the location',
            trigger: 'change'
          }
        ]
      },
      autoPrint: false,
      labelTemplate: 'Small Label',
      inputError: false,
      manufacturer: null,
      locations: null,
      smallLabel: null,
      largeLabel: null,
      partOptions: null,
      suppliers: null,

      printDialogVisible: false
    }
  },
  mounted() {
    this.getLocations()
    this.getManufacturers()
    this.resetForm()
    this.getLabel()
    this.getSuppliers()
    print.loadPrinter()
  },
  methods: {
    getLocations() {
      requestBN({
        url: '/location',
        methood: 'get'
      }).then(response => {
        this.locations = response.data
      })
    },
    getManufacturers() {
      requestBN({
        url: '/part/manufacturer',
        methood: 'get'
      }).then(response => {
        this.manufacturer = response.data
      })
    },
    getSuppliers() {
      requestBN({
        url: '/supplier',
        methood: 'get'
      }).then(response => {
        this.suppliers = response.data
      })
    },
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
    getParts(ManufacturerName) {
      requestBN({
        url: '/part',
        methood: 'get',
        params: { ManufacturerName: ManufacturerName }
      }).then(response => {
        this.partOptions = response.data
      })
    },
    resetForm() {
      this.formData.ManufacturerName = ''
      this.formData.ManufacturerPartNumber = ''
      this.formData.SupplierName = ''
      this.formData.SupplierPartNumber = ''
      this.formData.OrderReference = ''
      this.formData.Date = ''
      this.formData.Quantity = ''
      this.formData.Location = 'Loc-00000'
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
          message: 'Input Invalide',
          duration: 3,
          type: 'error'
        })
      } else {
        this.formData.Date = new Date(this.formData.Date)
        this.formData.Date = this.formData.Date.toISOString().split('T')[0]

        requestBN({
          method: 'post',
          url: '/stock',
          data: { data: this.formData }
        }).then(response => {
          if (response.error == null) {
            this.partData = response.data
            if (this.autoPrint === true) {
              this.print()
            } else {
              this.printDialogVisible = true
            }
          } else {
            this.$message({
              showClose: true,
              message: response.error,
              duration: 0,
              type: 'error'
            })
          }
        })
      }
    },
    closeDialog() {
      this.printDialogVisible = false
      this.resetForm()
    },
    getLabel() {
      requestBN({
        url: '/label',
        methood: 'get',
        params: { Id: 1 }
      }).then(response => {
        this.smallLabel = response.data[0]
      })

      requestBN({
        url: '/label',
        methood: 'get',
        params: { Id: 2 }
      }).then(response => {
        this.largeLabel = response.data[0]
      })
    },
    print(printData) {
      var labelData = {
        $Barcode: printData.Barcode,
        $StockId: printData.StockNo,
        $Mfr: printData.ManufacturerName,
        $MPN: printData.ManufacturerPartNumber,
        $PartNo: printData.OrderReference,
        $Description: printData.Description
      }

      print.printLabel(this.labelTemplate.Code, labelData)

      this.resetForm()
    }
  }
}
</script>
