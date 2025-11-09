<template>
  <div>
    <el-dialog
      title="Stock Split"
      :visible.sync="visible"
      :before-close="close"
      @open="onOpen"
    >
      <el-form label-width="170px">
        <el-form-item label="Split remove quantity:">
          <el-input-number v-model="quantity" :min="1" :max="100000" />
        </el-form-item>
      </el-form>

      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="split">Split</el-button>
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
    item: { type: String, default: '' },
    visible: { type: Boolean, default: false }
  },
  data() {
    return {
      quantity: 1
    }
  },
  methods: {
    onOpen() {
      this.quantity = 1
    },
    close() {
      this.visible = false
      this.$emit('update:visible', this.visible)
    },
    split() {
      stock.item.split(this.$props.item, this.quantity).then(response => {
        this.close()
        this.$router.push('/stock/item/' + response.ItemCode)
      })
    }
  }
}
</script>
