<template>
  <div class="order-upload-dialog">
    <el-dialog
      title="Order Upload"
      :visible.sync="visible"
      :before-close="closeDialog"
      width="70%"
    >

      <el-form class="form-container">
        <el-form-item>
          <el-upload
            ref="upload"
            :action="docUrl"
            :file-list="fileList"
            drag
            multiple
            :auto-upload="false"
            :on-success="onUploadSuccess"
            :on-error="onUploadError()"
          >
            <i class="el-icon-upload" />
            <div class="el-upload__text">
              Drop file here or <em>click to upload</em>
            </div>
          </el-upload>
        </el-form-item>

        <el-form-item>
          <el-button type="primary" @click="onUpload()">Upload</el-button>
        </el-form-item>
      </el-form>

      <template v-if="importData.length !== 0">
        <el-table ref="itemTable" :key="tableKey" :data="importData.Lines" border style="width: 100%">
          <el-table-column prop="LineNumber" label="Line" width="70" />
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

import Purchase from '@/api/purchase'
const purchase = new Purchase()

export default {
  name: 'OrderUpload',
  props: { meat: { type: Object, default: {}}, visible: { type: Boolean, default: false }},
  data() {
    return {
      docUrl: process.env.VUE_APP_BLUENOVA_API + '/purchasing/item/upload?PurchaseOrderNo=' + this.meat.PurchaseOrderBarcode,
      importData: {},
      OrderNumber: '',
      ApiInfo: {}
    }
  },
  mounted() {
    this.getImportApiInfo()
  },
  methods: {
    onUploadSuccess(response, file, fileList) {
      this.importData = response.data
      this.OrderNumber = this.importData.OrderNumber
      /*
      this.closeDialog()
      if (response.error === null) {
        this.$message({
          showClose: true,
          message: response.data.message,
          duration: 2,
          type: 'success'
        })
      } else {
        this.$message({
          showClose: true,
          duration: 0,
          message: response.data.message,
          type: 'error'
        })
      }*/
    },
    onUploadError(err, file, fileList) {
    },
    onUpload() {
      this.$refs.upload.submit()
    },
    importOrder() {
      purchase.item.import.upload(this.meat.PurchaseOrderBarcode, this.importData).then(response => {
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
