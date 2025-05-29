<template>
  <div>
    <el-dialog
      title="Add Stock"
      :visible.sync="visible"
      :before-close="close"
      @open="onOpen"
    >
      <el-form label-width="150px">
        <el-form-item label="Stock Quantity:">
          {{ item.Quantity.Quantity }}
        </el-form-item>

        <el-form-item label="Add Quantity:">
          <el-input-number v-model="formData.AddQuantity" :min="1" :max="100000" />
        </el-form-item>
        <el-form-item label="Note">
          <el-input v-model="formData.Note" type="textarea" />
        </el-form-item>
      </el-form>

      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="addStock">Add</el-button>
        <el-button @click="close">Close</el-button>
      </span>
    </el-dialog>
  </div>
</template>
<script>

import Stock from '@/api/stock'
const stock = new Stock()

export default {
  props: {
    item: { type: Object, default: stock.item.itemDataEmpty },
    visible: { type: Boolean, default: false }
  },
  data() {
    return {
      formData: Object.assign({}, stock.item.history.addDataEmpty)
    }
  },
  methods: {
    onOpen() {
      this.formData = Object.assign({}, stock.item.history.addDataEmpty)
      this.formData.ItemCode = this.$props.item.ItemCode
    },
    close() {
      this.visible = false
      this.$emit('update:visible', this.visible)
    },
    addStock() {
      stock.item.history.add(this.formData).then(response => {
        this.$message({
          message: 'Quantity updated successfully',
          type: 'success'
        })
        this.close()
      })
    }
  }
}
</script>
