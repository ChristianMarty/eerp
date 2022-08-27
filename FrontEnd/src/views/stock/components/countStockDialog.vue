<template>
  <div class="count-stock-dialog">

    <el-dialog
      title="Count Stock"
      :visible.sync="visible"
      :before-close="closeDialog"
    >
      <el-form label-width="150px">
        <el-form-item label="Stock Quantity:">
          {{ item.Quantity }}
        </el-form-item>

        <el-form-item label="Counted Quantity:">
          <el-input-number
            v-model="newQuantity"
            :min="0"
            :max="1000000"
          />
        </el-form-item>
        <el-form-item label="Note">
          <el-input v-model="note" type="textarea" />
        </el-form-item>
      </el-form>

      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="saveStock">Save</el-button>
        <el-button @click="closeDialog">Close</el-button>
      </span>
    </el-dialog>
  </div>
</template>
<script>

const itemData = {
  StockNo: '',
  Quantity: 0,
  Note: ''
}

import requestBN from '@/utils/requestBN'

export default {
  props: { item: { type: Object, default: itemData }, visible: { type: Boolean, default: false }},
  data() {
    return {
      newQuantity: 0,
      note: ''
    }
  },
  mounted() {
  },
  methods: {
    closeDialog() {
      this.visible = false
      this.$emit('update:visible', this.visible)
    },
    saveStock() {
      requestBN({
        method: 'post',
        url: '/stock/history/item',
        data: {
          StockNo: this.item.StockNo,
          Quantity: this.newQuantity,
          Note: this.note
        }
      }).then(response => {
        if (response.error != null) {
          this.$message({
            showClose: true,
            message: response.error,
            duration: 0,
            type: 'error'
          })
        } else {
          this.$message({
            message: 'Quantity updated successfully',
            type: 'success'
          })

          this.closeDialog()
        }
      })
    }
  }
}
</script>
