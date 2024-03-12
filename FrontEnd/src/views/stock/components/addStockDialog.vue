<template>
  <div class="add-stock-dialog">

    <el-dialog title="Add Stock" :visible.sync="visible" :before-close="closeDialog">
      <el-form label-width="150px">
        <el-form-item label="Stock Quantity:">
          {{ item.Quantity.Quantity }}
        </el-form-item>

        <el-form-item label="Add Quantity:">
          <el-input-number v-model="addQuantity" :min="1" :max="100000" />
        </el-form-item>
        <el-form-item label="Note">
          <el-input v-model="note" type="textarea" />
        </el-form-item>
      </el-form>

      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="addStock">Add</el-button>
        <el-button @click="closeDialog">Close</el-button>
      </span>
    </el-dialog>
  </div>
</template>
<script>

const itemData = {
  ItemCode: '',
  Quantity: 0
}

import requestBN from '@/utils/requestBN'

export default {
  props: { item: { type: Object, default: itemData }, visible: { type: Boolean, default: false }},
  data() {
    return {
      addQuantity: 0,
      workOrders: null,
      workOrderId: null,
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
    addStock() {
      requestBN({
        method: 'post',
        url: '/stock/history/item',
        data: {
          StockNumber: this.item.ItemCode,
          AddQuantity: this.addQuantity,
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
