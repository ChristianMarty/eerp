<template>
  <div class="order-import-dialog">
    <el-dialog
      title="Order Import"
      :visible.sync="visible"
      :before-close="closeDialog"
      width="70%"
    >
      <el-form ref="inputForm" :model="receivalData" class="form-container" label-width="150px">
        <el-form-item label="Order Number:" >
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

import requestBN from '@/utils/requestBN'

export default {
  name: 'AddToStock',
  props: { meat: { type: Object, default: {}}, visible: { type: Boolean, default: false }},
  data() {
    return {
      importData: [],
      OrderNumber:''
    }
  },
  mounted() {

  },
  methods: {
    loadData() {
      requestBN({
        url: '/purchasing/item/import',
        methood: 'get',
        params: { SupplierId: this.meat.SupplierId, OrderNumber: this.OrderNumber }
      }).then(response => {
        this.importData = response.data
      })
    },
    importOrder()
    {
      requestBN({
        method: 'post',
        url: '/purchasing/item/import',
        params: { PurchaseOrderNo: this.meat.PoNo, OrderNumber: this.OrderNumber }
 
      }).then(response => {
        if (response.error == null) {
          this.closeDialog()
        } else {
          this.$message({
            showClose: true,
            message: response.error,
            duration: 0,
            type: 'error'
          })
        }
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
