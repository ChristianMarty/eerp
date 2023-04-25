<template>
  <div class="order-import-dialog">
    <el-dialog
      title="Order Import"
      :visible.sync="visible"
      :before-close="closeDialog"
      width="70%"
    >
      <p v-if="ApiInfo.Authenticated == false">Click "Authenticate" and follow the instructions on the new page. Afterwards use the "Reload" button to reload the import dialogue.</p>
      <el-button v-if="ApiInfo.Authenticated == false" type="primary" @click="authenticate()">Authenticate</el-button>
      <el-button v-if="ApiInfo.Authenticated == false" type="primary" @click="getImportApiInfo()">Reload</el-button>

      <el-form v-if="ApiInfo.Authenticated == true" ref="inputForm" :model="receivalData" class="form-container" label-width="150px">
        <el-form-item label="Order Number:">
          <el-input v-model="OrderNumber" />
        </el-form-item>
        <el-form-item v-if="importData.length == 0">
          <el-button type="primary" @click="loadData()">Load</el-button>
        </el-form-item>
      </el-form>

      <template v-if="importData.length != 0">
        <el-table ref="itemTable" :key="tableKey" :data="importData.Lines" border style="width: 100%">
          <el-table-column prop="LineNo" label="Line" width="70" />
          <el-table-column prop="Quantity" label="Quantity" width="100" />
          <el-table-column prop="SupplierPartNumber" label="SKU" width="220" />
          <el-table-column prop="ManufacturerName" label="Manufacturer" width="200" />
          <el-table-column prop="OrderReference" label="Order Reference" width="150" />
          <el-table-column prop="SupplierDescription" label="Description" />
          <el-table-column prop="Price" label="Price" width="100" />
          <el-table-column prop="TotalPrice" label="TotalPrice" width="100" />
        </el-table>

        <p>
          <b>Order Date:</b>
          {{ importData.OrderDate }}
        </p>
        <p>
          <b>Currency:</b>
          {{ importData.CurrencyCode }}
        </p>
        <p>
          <b>Merchandise Cost:</b>
          {{ importData.MerchandisePrice }}
        </p>
        <p>
          <b>Shipping Cost:</b>
          {{ importData.ShippingPrice }}
        </p>
        <p>
          <b>VAT Cost:</b>
          {{ importData.VatPrice }}
        </p>
        <p>
          <b>Total Cost:</b>
          {{ importData.TotalPrice }}
        </p>

        <el-button type="primary" @click="importOrder()">Import Order</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script>

import Vendor from '@/api/vendor'
const vendor = new Vendor()

import Purchase from '@/api/purchase'
const purchase = new Purchase()

export default {
  name: 'AddToStock',
  props: { meat: { type: Object, default: {}}, visible: { type: Boolean, default: false }},
  data() {
    return {
      importData: [],
      OrderNumber: '',
      ApiInfo: {}
    }
  },
  mounted() {
    this.getImportApiInfo()
  },
  methods: {
    authenticate() {
      window.open(this.ApiInfo.AuthenticationUrl, '_blank').focus()
    },
    getImportApiInfo() {
      vendor.api.information(this.meat.SupplierId).then(response => {
        this.ApiInfo = response.Authentication
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    loadData() {
      purchase.item.import.load(this.meat.SupplierId, this.OrderNumber).then(response => {
        this.importData = response
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    importOrder() {
      purchase.item.import.save(this.meat.PoNo, this.OrderNumber).then(response => {
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
    closeDialog() {
      this.visible = false
      this.$emit('update:visible', this.visible)
      this.$emit('closed')
    }
  }
}
</script>
