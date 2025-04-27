<template>
  <div>
    <el-dialog
      title="Count Stock"
      :visible.sync="visible"
      :before-close="close"
      @open="onOpen"
    >
      <el-form label-width="150px">
        <el-form-item label="Stock Quantity:">
          {{ item.Quantity.Quantity }}
        </el-form-item>

        <el-form-item label="Counted Quantity:">
          <el-input-number
            v-model="formData.NewQuantity"
            :min="0"
            :max="1000000"
          />
        </el-form-item>
        <el-form-item label="Note">
          <el-input v-model="formData.Note" type="textarea" />
        </el-form-item>
      </el-form>

      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="save">Save</el-button>
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
      formData: Object.assign({}, stock.item.history.countDataEmpty)
    }
  },
  methods: {
    async onOpen() {
      this.formData = Object.assign({}, stock.item.history.countDataEmpty)
      this.formData.ItemCode = this.$props.item.ItemCode
    },
    close() {
      this.visible = false
      this.$emit('update:visible', this.visible)
    },
    save() {
      stock.item.history.count(this.formData).then(response => {
        this.$message({
          message: 'Quantity updated successfully',
          type: 'success'
        })
        this.close()
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response.error,
          duration: 0,
          type: 'error'
        })
      })
    }
  }
}
</script>
