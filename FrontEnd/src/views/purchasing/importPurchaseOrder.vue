<template>
  <div class="app-container">
    <h1>Import Purchas Order</h1>

    <el-divider />
    <el-form
      ref="inputForm"
      :model="formData"
      :rules="rules"
      class="form-container"
      label-width="130px"
    >
      <el-form-item label="Title:">
        <el-input v-model="formData.Title" />
      </el-form-item>

      <el-form-item label="Description:">
        <el-input v-model="formData.Description" type="textarea" />
      </el-form-item>

      <el-form-item label="Supplier:" prop="suppliers">
        <el-cascader
          v-model="formData.SupplierId"
          :options="suppliers"
          filterable
          :props="{
            emitPath: false,
            value: 'Id',
            label: 'Name',
            children: 'Children',
            checkStrictly: true
          }"
        />
      </el-form-item>

      <el-form-item label="Order Number:">
        <el-input v-model="formData.OrderNumber" />
      </el-form-item>

      <el-form-item v-if="orderData === null">
        <el-button type="primary" @click="loadData">Load</el-button>
      </el-form-item>
    </el-form>

    <template v-if="orderData !== null">
      <el-table ref="itemTable" :key="tableKey" :data="orderData.Lines" border style="width: 100%">
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
        {{ orderData.OrderDate }}
      </p>
      <p>
        <b>Currency:</b>
        {{ orderData.CurrencyCode }}
      </p>
      <p>
        <b>Merchandise Cost:</b>
        {{ orderData.MerchandisePrice }}
      </p>
      <p>
        <b>Shipping Cost:</b>
        {{ orderData.ShippingPrice }}
      </p>
      <p>
        <b>VAT Cost:</b>
        {{ orderData.VatPrice }}
      </p>
      <p>
        <b>Total Cost:</b>
        {{ orderData.TotalPrice }}
      </p>

      <el-button type="primary" @click="createPo">Create Purchas Order</el-button>
    </template>
  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'

const emptyData = {
  SupplierId: '',
  Title: '',
  Description: '',
  OrderNumber: ''
}

export default {
  name: 'CreatePO',
  components: {},
  data() {
    return {
      formData: Object.assign({}, emptyData),
      suppliers: null,
      orderData: null
    }
  },
  mounted() {
    this.getSuppliers()
  },
  methods: {
    getSuppliers() {
      requestBN({
        url: '/supplier',
        methood: 'get',
        params: { OrderImportSupported: true }
      }).then(response => {
        this.suppliers = response.data
      })
    },
    loadData() {
      requestBN({
        url: '/purchasing/item/import',
        methood: 'get',
        params: { SupplierId: this.formData.SupplierId, OrderNumber: this.formData.OrderNumber }
      }).then(response => {
        this.orderData = response.data
      })
    },
    createPo() {
      requestBN({
        method: 'post',
        url: '/purchasing/item/import',
        data: {
          SupplierId: this.formData.SupplierId,
          OrderNumber: this.formData.OrderNumber,
          Title: this.formData.Title,
          Description: this.formData.Description
        }
      }).then(response => {
        if (response.error == null) {
          this.$router.push(
            '/purchasing/edit/' + response.data.PurchaseOrderNo
          )
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
  }
}
</script>
