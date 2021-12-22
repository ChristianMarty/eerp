<template>
  <div class="print-dialog">

    <el-dialog title="Print" :visible.sync="visible" :before-close="closeDialog">
      <el-form label-width="150px">
        <el-form-item label="Manufacturer:">
          <el-input
            v-model="data.ManufacturerName"
          />
        </el-form-item>
        <el-form-item label="Part Number:">
          <el-input
            v-model="data.ManufacturerPartNumber"
          />
        </el-form-item>
        <el-form-item label="Order Reference:">
          <el-input
            v-model="data.OrderReference"
          />
        </el-form-item>
        <el-form-item label="Description:">
          <el-input
            v-model="data.Description"
          />
        </el-form-item>
        <el-form-item label="Stock No:">
          <el-input
            v-model="data.StockNo"
          />
        </el-form-item>
        <el-form-item label="Barcode:">
          <el-input
            v-model="data.Barcode"
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
  OrderReference: '',
  Description: '',
  StockNo: '',
  Barcode: ''
}

export default {
  props: { data: { type: Object, default: printData }, visible: { type: Boolean, default: false }},
  data() {
    return {

    }
  },
  mounted() {
  },
  methods: {
    closeDialog() {
      this.visible = false
      this.$emit('update:visible', this.visible)
    },
    print() {
      this.$emit('print', this.data)
      this.closeDialog()
    }
  }
}
</script>
