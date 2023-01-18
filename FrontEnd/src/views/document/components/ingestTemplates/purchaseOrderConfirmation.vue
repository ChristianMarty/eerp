<template>
  <div class="purchase-order-confirmation-document-ingest-container">

    <el-form label-width="120px">
      <el-form-item label="Name:">
        {{ fileInfo.FileName }}
      </el-form-item>

      <el-form-item label="PO-Number:">
        <el-input v-model="dialogData.PurchaseOrderNumber" />
      </el-form-item>

      <el-form-item label="Note:">
        <el-input v-model="dialogData.Note" type="textarea" />
      </el-form-item>
    </el-form>

  </div>
</template>

<script>

import Document from '@/api/document'
const document = new Document()

export default {
  props: {
    fileInfo: { type: Object, default: null }
  },
  data() {
    return {
      dialogData: Object.assign({}, document.ingest.template.purchaseOrderParameters)
    }
  },
  created() {
  },
  mounted() {
  },
  methods: {
    ingest() {
      this.dialogData.FileName = this.fileInfo.FileName
      document.ingest.template.purchaseOrderConfirmation(this.dialogData).then(response => {
        this.$message({
          showClose: true,
          message: 'Changes saved successfully',
          duration: 1500,
          type: 'success'
        })
        this.$emit('success')
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
