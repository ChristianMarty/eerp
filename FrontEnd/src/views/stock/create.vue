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
          <el-option v-for="item in manufacturer" :key="item.Id" :label="item.DisplayName" :value="item.Id" />
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
          <el-option v-for="item in suppliers" :key="item.Id" :label="item.DisplayName" :value="item.Id" />
        </el-select>
      </el-form-item>

      <el-form-item label="Supplier SKU:" prop="SupplierPartNumber">
        <el-input v-model="createParameter.SupplierPartNumber" placeholder="Please input" />
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
          v-model="createParameter.ItemCode"
          :options="locations"
          :props="{
            emitPath: false,
            value: 'ItemCode',
            label: 'Name',
            children: 'Children',
            checkStrictly: true
          }"
        />
      </el-form-item>

      <el-form-item>
        <el-button type="primary" @click="save()">Save</el-button>
        <el-button type="danger" @click="resetForm()">Clear</el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
import Vendor from '@/api/vendor'
const vendor = new Vendor()

import Stock from '@/api/stock'
const stock = new Stock()

import Location from '@/api/location'
const location = new Location()

import ManufacturerPart from '@/api/manufacturerPart'
const manufacturerPart = new ManufacturerPart()

export default {
  name: 'CreateStock',
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
          { required: false, message: 'Please select the year and week', trigger: 'change' }
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
      createParameter: Object.assign({}, stock.item.createParameter)
    }
  },
  async mounted() {
    this.suppliers = await vendor.search(true, false, false, false, false, true)
    this.locations = await location.search()
    this.manufacturer = await vendor.search(false, true, false, false, false)

    this.resetForm()
  },
  methods: {
    searchManufacturerPartNumber(queryString, cb) {
      const out = []
      this.partOptions.forEach(element => {
        out.push({ value: element.ManufacturerPartNumber })
      })
      if (queryString === null) {
        cb(out)
      } else {
        cb(
          out.filter(
            element =>
              element.value.toLowerCase().indexOf(queryString.toLowerCase()) === 0
          )
        )
      }
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
          message: 'Invalid input',
          duration: 3,
          type: 'error'
        })
      } else {
        stock.item.create(this.createParameter).then(response => {
          this.$router.push('/stock/item/' + response.ItemCode)
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
    }
  }
}
</script>
