<template>
  <div class="add-stock-dialog">

    <el-dialog
      title="Remove Stock"
      :visible.sync="visible"
      :before-close="closeDialog"
    >
      <el-form label-width="150px">
        <el-form-item label="Stock Quantity:">
          {{ item.Quantity }}
        </el-form-item>

        <el-form-item label="Remove Quantity:">
          <el-input-number
            v-model="removeQuantity"
            :min="1"
            :max="item.Quantity"
          />
        </el-form-item>
      </el-form>

      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="removeStock">Remove</el-button>
        <el-button @click="closeDialog">Close</el-button>
      </span>
    </el-dialog>
  </div>
</template>
<script>

const itemData = {
  StockNo: '',
  Quantity: 0
}

import requestBN from '@/utils/requestBN'

export default {
  props: { item: { type: Object, default: itemData }, visible: { type: Boolean, default: false }},
  data() {
    return {
      removeQuantity: 0
    }
  },
  mounted() {
  },
  methods: {
    closeDialog() {
      this.visible = false
      this.$emit('update:visible', this.visible)
    },
    removeStock() {
      requestBN({
        method: 'patch',
        url: '/stock',
        params: { StockNo: this.inputItemNr },
        data: { RemoveQuantity: this.removeQuantity }
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
