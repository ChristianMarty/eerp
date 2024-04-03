<template>
  <div class="print-dialog">

    <el-dialog
      title="Print"
      :visible.sync="visible"
      :before-close="closeDialog"
      @open="onOpen"
    >
      <el-form label-width="200px">
        <el-form-item label="Manufacturer:">
          <el-input
            v-model="formData.ManufacturerName"
          />
        </el-form-item>
        <el-form-item label="Part Number:">
          <el-input
            v-model="formData.ManufacturerPartNumber"
          />
        </el-form-item>
        <el-form-item label="Production Part Number:">
          <el-input
            v-model="formData.ProductionPartNumber"
          />
        </el-form-item>
        <el-form-item label="Description:">
          <el-input
            v-model="formData.Description"
          />
        </el-form-item>
        <el-form-item label="Stock Number:">
          <el-input
            v-model="formData.StockNumber"
          />
        </el-form-item>
        <el-form-item label="Item Code:">
          <el-input
            v-model="formData.ItemCode"
          />
        </el-form-item>
      </el-form>

      <span slot="footer" class="dialog-footer">
        <el-button @click="closeDialog">Close</el-button>
        <el-button type="primary" @click="print">Print</el-button>
      </span>
    </el-dialog>
  </div>
</template>
<script>

const printData = {
  ManufacturerName: '',
  ManufacturerPartNumber: '',
  ProductionPartNumber: '',
  Description: '',
  StockNumber: '',
  ItemCode: ''
}

export default {
  props: { data: { type: Object, default: printData }, visible: { type: Boolean, default: false }},
  data() {
    return {
      formData: Object.assign({}, printData)
    }
  },
  mounted() {
  },
  methods: {
    onOpen() {
      this.formData = structuredClone(this.$props.data)
    },
    closeDialog() {
      this.visible = false
      this.$emit('update:visible', this.visible)
    },
    print() {
      this.$emit('print', this.formData)
      this.closeDialog()
    }
  }
}
</script>
