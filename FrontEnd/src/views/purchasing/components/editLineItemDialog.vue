<template>
  <div class="edit-line-item-dialog">
    <el-dialog title="Order Line" :visible.sync="visible" :close-on-click-modal="false" :before-close="closeDialog">
      <el-form label-width="150px">
        <el-form-item label="Line:">{{ line.LineNo }}</el-form-item>

        <el-form-item label="Type:">
          <el-select
            v-model="line.LineType"
            placeholder="Type"
            style="min-width: 200px; margin-right: 10px;"
          >
            <el-option value="Part">Part</el-option>
            <el-option value="Generic">Generic</el-option>
          </el-select>
        </el-form-item>

        <el-form-item label="Quantity:">
          <template slot-scope="{ row }">
            <el-input-number
              v-model="line.QuantityOrderd"
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
          <span :style="{margin: '10px'}"><el-button type="primary" @click="line.Price = line.Price/line.QuantityOrderd">Divide by Quantity</el-button></span>
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
          <span :style="{margin: '10px'}"><el-button type="primary" @click="line.Price = line.Price/(1+vat.find(x => x.Id === line.VatTaxId).Value/100)">Remove VAT from Price</el-button></span>
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
        <el-form-item label="Expected Receipt:">
          <el-date-picker
            v-model="line.ExpectedReceiptDate"
            type="date"
            placeholder="Pick a day"
            value-format="yyyy-MM-dd"
          />
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
        <el-form-item v-if="line.LineType == 'Part'" label="Stock Part:">
          <el-checkbox v-model="line.StockPart" />
        </el-form-item>

        <el-form-item label="Sku:">
          <el-input v-model="line.SupplierSku" @keyup.enter.native="searchSku(line.SupplierSku)">
            <el-button v-if="po.SkuSearchSupported == true" slot="append" icon="el-icon-search"  @click="searchSku(line.SupplierSku)">Import</el-button>
          </el-input>
        </el-form-item>

        <el-form-item label="Manufacturer:">
          <el-select
            v-model="line.ManufacturerName"
            placeholder="Manufacturer"
            filterable
            style="min-width: 200px; margin-right: 10px;"
          >
            <el-option v-for="item in partManufacturer" :key="item.Name" :label="item.Name" :value="item.Name" />
          </el-select>
        </el-form-item>

        <el-form-item label="MPN:">
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

import requestBN from '@/utils/requestBN'

export default {
  name: 'EditLineItemDialog',
  props: {
    line: { type: Object, default: null },
    supplierId: { type: Number, default: 0 },
    visible: { type: Boolean, default: false },
    po: { type: String, default: '' }
  },
  data() {
    return {
      partOptions: [],
      uom: [],
      vat: [],
      partManufacturer: []
    }
  },
  mounted() {
    this.getVAT()
    this.getUOM()
    this.getManufacturers()
  },
  methods: {
    calculatePrice(line) {
      const price = line.Price * line.QuantityOrderd
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
      requestBN({
        url: '/supplier/supplierPart',
        methood: 'get',
        params: { ProductionPartNo: row.PartNo, SupplierId: this.$props.supplierId }
      }).then(response => {
        this.partOptions =
          response.data
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
      requestBN({
        url: '/purchasing/item/skuSearch',
        methood: 'get',
        params: { SupplierId: this.$props.supplierId, SKU: sku }
      }).then(response => {
        const skuData = response.data

        this.line.Description = skuData.Description
        this.line.ManufacturerPartNumber = skuData.ManufacturerPartNumber
        this.line.ManufacturerName = skuData.ManufacturerName

        skuData.Pricing.forEach(price => {
          if (this.line.QuantityOrderd >= price.MinimumQuantity) {
            this.line.Price = price.Price
          }
        })
      })
    },
    getManufacturers() {
      requestBN({
        url: '/part/manufacturer',
        methood: 'get'
      }).then(response => {
        this.partManufacturer = response.data
      })
    },
    getVAT() {
      requestBN({
        url: '/finance/tax',
        methood: 'get',
        params: {
          Type: 'VAT'
        }
      }).then(response => {
        this.vat = response.data
      })
    },
    getUOM() {
      requestBN({
        url: '/unitOfMeasurement',
        methood: 'get',
        params: {
          Countable: true
        }
      }).then(response => {
        this.uom = response.data
      })
    },
    refresh() {
      this.$emit('refresh')
    },
    closeDialog() {
      this.visible = false
      this.$emit('update:visible', this.visible)
    },
    saveLine() {
      requestBN({
        method: 'post',
        url: '/purchasing/item/edit',
        data: { data: { Action: 'save', Lines: [this.$props.line], PoNo: this.$props.po.PoNo }}
      }).then(response => {
        if (response.error == null) {
          this.$message({
            showClose: true,
            message: 'Changes saved successfully',
            duration: 1500,
            type: 'success'
          })
        } else {
          this.$message({
            showClose: true,
            message: response.error,
            duration: 0,
            type: 'error'
          })
        }
        this.refresh()
        this.closeDialog()
      })
    },
    deleteLine() {
      this.$confirm('This will permanently delete line ' + this.line.LineNo + '. Continue?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning'
      }).then(() => {
        requestBN({
          method: 'post',
          url: '/purchasing/item/edit',
          data: { data: { Action: 'delete', OrderLineId: this.line.OrderLineId, PoNo: this.$props.po.PoNo }}
        }).then(response => {
          if (response.error != null) {
            this.$message({
              showClose: true,
              message: response.error,
              duration: 0,
              type: 'error'
            })
          }
          this.refresh()
          this.closeDialog()
        })
      }).catch(() => {
        this.$message({
          type: 'info',
          message: 'Delete canceled'
        })
      })
    }
  }
}
</script>
