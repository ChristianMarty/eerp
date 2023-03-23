<template>
  <div class="purchase-order-receipt.-document-ingest-container">

    <el-form label-width="120px">
      <el-form-item label="Name:">
        {{ fileInfo.FileName }}
      </el-form-item>

      <el-form-item label="Inventory Number:">
        <el-input v-model="dialogData.InventoryNumber" />
      </el-form-item>

      <el-form-item label="Description:">
        <el-input v-model="dialogData.Description" type="textarea" />
      </el-form-item>

      <el-form-item label="Calibration Date:">
        <el-date-picker v-model="dialogData.Date" value-format="yyyy-MM-dd" />
      </el-form-item>

      <el-form-item label="Next Calibration Date:">
        <el-date-picker v-model="dialogData.NextDate" value-format="yyyy-MM-dd" />
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
      dialogData: Object.assign({}, document.ingest.template.inventoryHistoryCalibrationParameters)
    }
  },
  created() {
  },
  mounted() {
  },
  methods: {
    ingest() {
      this.dialogData.FileName = this.fileInfo.FileName
      document.ingest.template.inventoryHistoryCalibration(this.dialogData).then(response => {
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
