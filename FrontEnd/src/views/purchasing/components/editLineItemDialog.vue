<template>
  <div class="edit-line-item-dialog">
    <el-dialog
      title="Order Line"
      :visible.sync="isVisible"
      :close-on-click-modal="false"
      :before-close="closeDialog"
    >
      <p
        v-if="loading === true"
        v-loading="loading"
        element-loading-text="Loading Line"
      > Loading Line ... </p>

      <el-form
        v-if="loading === false"
        label-width="150px"
      >
        <el-form-item label="Line:">{{ line.LineNumber }}</el-form-item>

        <el-form-item label="Type:">
          <el-select
            v-model="line.LineType"
            placeholder="Type"
            style="min-width: 200px; margin-right: 10px;"
          >
            <el-option
              v-for="item in lineType"
              :key="item"
              :label="item"
              :value="item"
            />
          </el-select>
        </el-form-item>

        <el-form-item label="Quantity:">
          <template>
            <el-input-number
              v-model="line.QuantityOrdered"
              :controls="false"
              :min="1"
              :max="999999"
              style="width: 70pt"
            />
          </template>
        </el-form-item>

        <el-form-item label="Unit:">
          <el-select
            v-model="line.UnitOfMeasurementId"
            placeholder="UoM"
            filterable
            style="min-width: 200px; margin-right: 10px;"
          >
            <el-option v-for="item in uom" :key="item.Id" :label="item.Symbol +' - '+ item.Unit" :value="item.Id" />
          </el-select>
        </el-form-item>

        <el-form-item label="Price:">
          <el-input-number
            v-model="line.Price"
            :controls="false"
            :precision="6"
            :min="0.000000"
            :max="999999"
            style="width: 70pt"
          />
          <b>   Amount:</b> {{ calculatePrice(line).price }}
          <span :style="{margin: '10px'}"><el-button type="primary" @click="clac(line.Price = line.Price/line.QuantityOrdered)">Divide by Quantity</el-button></span>
        </el-form-item>

        <el-form-item label="VAT :">
          <el-select
            v-model="line.VatTaxId"
            placeholder="VAT"
            filterable
            style="min-width: 200px; margin-right: 10px;"
          >
            <el-option v-for="item in vat" :key="item.Id" :label="item.Value +'% - '+item.Description" :value="item.Id" />
          </el-select>
          <b>   Amount:</b> {{ calculatePrice(line).vat }}
          <span :style="{margin: '10px'}"><el-button type="primary" @click="clac(line.Price = line.Price/(1+vat.find(x => x.Id === line.VatTaxId).Value/100))">Remove VAT from Price</el-button></span>
        </el-form-item>

        <el-form-item label="Discount:">
          <el-col :span="11">
            <el-input-number
              v-model="line.Discount"
              :controls="false"
              :precision="3"
              :min="0.00"
              :max="100.00"
              style="width: 70pt"
            />
            <b>   Amount:</b> {{ calculatePrice(line).discount }}
          </el-col>
        </el-form-item>
        <el-form-item label="Total:">
          <span>{{ calculatePrice(line).total }}</span>
        </el-form-item>
        <el-form-item label="Cost Center:">
          <el-popover
            title="Select Cost Center"
            placement="left"
            width="400"
            trigger="click"
          >
            <el-table :data="costCenter" @row-click="(row) =>costCenterSelect(row.Barcode)">
              <el-table-column property="Barcode" label="Barcode" />
              <el-table-column width="120" property="Name" label="Name" />
              <el-table-column property="ProjectName" label="Project" />
            </el-table>

            <el-button slot="reference" icon="el-icon-plus" type="primary" circle size="mini" :style="{ margin: '5px'}" />
          </el-popover>
          <el-tag
            v-for="item in line.CostCenter"
            :key="item.Barcode"
            :color="getCostCenterColor(item.Barcode)"
            closable
            @close="handleRemoveCostCenter(item.Barcode)"
          >
            {{ item.Barcode }}
          </el-tag>
        </el-form-item>
        <el-form-item label="Expected Receipt:">
          <el-date-picker
            v-model="line.ExpectedReceiptDate"
            type="date"
            placeholder="Pick a day"
            value-format="yyyy-MM-dd"
          />
        </el-form-item>
        <el-form-item v-if="line.LineType == 'Specification Part'" label="Specification Part:">
          <el-select
            v-model="line.SpecificationPartCode"
            placeholder="Specification Part"
            filterable
            style="min-width: 200px; margin-right: 10px;"
          >
            <el-option
              v-for="item in specificationPart"
              :key="item.ItemCode"
              :label="item.ItemCode+' - '+item.Name"
              :value="item.ItemCode"
            />
          </el-select>

        </el-form-item>

        <el-form-item v-if="line.LineType == 'Part'" label="Part Number:">

          <el-popover placement="top" width="800" trigger="click">

            <el-table :data="partOptions" @row-click="(row, column, event) =>supplierPartSelect(line, row, column, event)">
              <el-table-column width="120" property="ManufacturerName" label="Manufacturer" />
              <el-table-column property="ManufacturerPartNumber" label="P/N" />
              <el-table-column property="SupplierPartNumber" label="SKU" />
              <el-table-column property="Note" label="Note" />
            </el-table>

            <span slot="reference">
              <el-input
                v-model="line.PartNo"
                placeholder="PartNo"
                style="width: 200px; margin-right: 10px;"
                @keyup.enter.native="getPartData(line)"
              />
              <el-button slot="reference" @click="getPartData(line)">Search</el-button>
            </span>
          </el-popover>
        </el-form-item>

        <el-form-item label="Order Reference:">
          <el-input v-model="line.OrderReference" />
        </el-form-item>
        <el-form-item v-if="line.LineType !== 'Generic'" label="Stock Part:">
          <el-checkbox v-model="line.StockPart" />
        </el-form-item>

        <el-form-item v-if="line.LineType != 'Specification Part'" label="Sku:">
          <el-input v-model="line.SupplierSku" @keyup.enter.native="searchSku(line.SupplierSku)">
            <el-button v-if="purchaseOrder.SkuSearchSupported == true" slot="append" icon="el-icon-search" @click="searchSku(line.SupplierSku)">Import</el-button>
          </el-input>
        </el-form-item>

        <el-form-item v-if="line.LineType != 'Specification Part'" label="Manufacturer:">
          <el-select
            v-model="line.ManufacturerName"
            placeholder="Manufacturer"
            filterable
            style="min-width: 200px; margin-right: 10px;"
          >
            <el-option v-for="item in partManufacturer" :key="item.Id" :label="item.DisplayName" :value="item.FullName" />
          </el-select>
        </el-form-item>

        <el-form-item v-if="line.LineType != 'Specification Part'" label="MPN:">
          <el-input v-model="line.ManufacturerPartNumber" />
        </el-form-item>
        <el-form-item label="Description:">
          <el-input v-model="line.Description" />
        </el-form-item>
        <el-form-item label="Note:">
          <el-input v-model="line.Note" type="textarea" placeholder="Note" />
        </el-form-item>
      </el-form>

      <span slot="footer" class="dialog-footer">
        <el-button type="danger" @click="visible = false, deleteLine()">Delete</el-button>
        <el-button type="primary" @click="visible = false, saveLine()">Save</el-button>
        <el-button @click="closeDialog()">Cancel</el-button>
      </span>
    </el-dialog>
  </div>
</template>

<script>
import Finance from '@/api/finance'
const finance = new Finance()
import Vendor from '@/api/vendor'
const vendor = new Vendor()
import Purchase from '@/api/purchase'
const purchase = new Purchase()
import SupplierPart from '@/api/supplierPart'
const supplierPart = new SupplierPart()
import SpecificationPart from '@/api/specificationPart'
const specificationPart = new SpecificationPart()
import UnitOfMeasurement from '@/api/unitOfMeasurement'
const unitOfMeasurement = new UnitOfMeasurement()

import * as defaultSetting from '@/utils/defaultSetting'

export default {
  name: 'EditLineItemDialog',
  props: {
    visible: { type: Boolean, default: false },
    lineId: { type: Number, default: 0 },
    purchaseOrder: { type: Object, default: null }
  },
  data() {
    return {
      isVisible: false,
      loading: true,
      lineType: [],
      partOptions: [],
      uom: [],
      vat: [],
      costCenter: [],
      partManufacturer: [],
      specificationPart: [],
      line: Object.assign({}, purchase.item.line.emptyLine)
    }
  },
  watch: {
    '$props.visible': {
      handler(newVal) {
        if (newVal === true) {
          this.isVisible = true
          this.loadLine()
        }
      }
    }
  },
  async mounted() {
    this.vat = await finance.tax.list('VAT')
    this.uom = await unitOfMeasurement.list(true)
    this.partManufacturer = await vendor.search(false, true, false, false, false)
    this.costCenter = await finance.costCenter.list()
    this.specificationPart = await specificationPart.search()
    this.getType()
  },
  methods: {
    calculatePrice(line) {
      const price = line.Price * line.QuantityOrdered
      const discount = price / 100 * line.Discount
      const vat = Math.round((price - discount) * (this.vat.find(x => x.Id === line.VatTaxId).Value / 100) * 1000000) / 1000000

      const data = {
        price: price,
        vat: vat,
        discount: discount,
        total: (price - discount) + vat
      }
      return data
    },
    getPartData(row) {
      if (row.PartNo === null) return
      supplierPart.search(row.PartNo, this.$props.purchaseOrder.SupplierId).then(response => {
        this.partOptions = response
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    supplierPartSelect(data, row, column, event) {
      data.SupplierPartId = row.SupplierPartId
      data.SupplierSku = row.SupplierPartNumber
      data.ManufacturerName = row.ManufacturerName
      data.Note = row.Note
      data.Description = row.Description
      data.ManufacturerPartNumber = row.ManufacturerPartNumber
    },
    searchSku(sku) {
      purchase.item.skuSearch(this.$props.purchaseOrder.SupplierId, sku).then(response => {
        const skuData = response

        this.line.Description = skuData.Description
        this.line.ManufacturerPartNumber = skuData.ManufacturerPartNumber
        this.line.ManufacturerName = skuData.ManufacturerName

        skuData.Pricing.forEach(price => {
          if (this.line.QuantityOrdered >= price.MinimumQuantity) {
            this.line.Price = price.Price
          }
        })
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    getCostCenterColor(CostCenterBarcode) {
      const result = this.costCenter.filter(obj => {
        return obj.Barcode === CostCenterBarcode
      })
      if (result.length === 0) return '#FFFFFF'
      return result[0].Color
    },
    handleRemoveCostCenter(CostCenterBarcode) {
      this.line.CostCenter = this.line.CostCenter.filter(obj => {
        return obj.Barcode !== CostCenterBarcode
      })
    },
    costCenterSelect(CostCenterBarcode) {
      const addData = {
        Barcode: CostCenterBarcode,
        Quota: 1
      }
      this.line.CostCenter.push(addData)
    },
    loadLine() {
      this.loading = true
      this.line = {}
      if (this.$props.lineId === 0) { // in case of new Line
        this.line = Object.assign({}, purchase.item.line.emptyLine)
        this.line.UnitOfMeasurementId = Number(defaultSetting.defaultSetting().PurchaseOrder.UoM)
        this.line.VatTaxId = Number(defaultSetting.defaultSetting().PurchaseOrder.VAT)
        this.loading = false
        return
      }
      purchase.item.line.get(this.$props.lineId).then(response => {
        this.line = response
        this.loading = false
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
        this.closeDialog()
      })
    },
    saveLine() {
      purchase.item.line.save(this.$props.purchaseOrder.PurchaseOrderNumber, [this.line]).then(response => {
        this.$message({
          showClose: true,
          message: 'Changes saved successfully',
          duration: 1500,
          type: 'success'
        })
        this.closeDialog()
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    deleteLine() {
      this.$confirm('This will permanently delete line ' + this.line.LineNumber + '. Continue?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning'
      }).then(() => {
        purchase.item.line.delete(this.$props.purchaseOrder.PurchaseOrderNumber, this.line.OrderLineId).then(response => {
          this.$message({
            showClose: true,
            message: 'Changes saved successfully',
            duration: 1500,
            type: 'success'
          })
          this.closeDialog()
        }).catch(response => {
          this.$message({
            showClose: true,
            message: response,
            duration: 0,
            type: 'error'
          })
        })
      }).catch(() => {
        this.$message({
          type: 'info',
          message: 'Delete canceled'
        })
      })
    },
    getType() {
      purchase.item.line.type().then(response => {
        this.lineType = response
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    closeDialog() {
      this.isVisible = false
      this.$emit('refresh')
      this.$emit('update:visible', false)
    }
  }
}
</script>
