<template>
  <div>
    <el-dialog
      title="Remove Stock"
      :visible.sync="visible"
      :before-close="close"
      @open="onOpen"
    >
      <el-form label-width="150px">
        <el-form-item label="Stock Quantity:">{{ item.Quantity.Quantity }}</el-form-item>

        <el-form-item label="Remove Quantity:">
          <el-input-number v-model="formData.RemoveQuantity" :min="1" :max="item.Quantity.Quantity" />
        </el-form-item>

        <el-form-item label="Work Order:">
          <el-select v-model="formData.WorkOrderNumber" filterable>
            <el-option
              v-for="wo in workOrders"
              :key="wo.WorkOrderNumber"
              :label="wo.ItemCode + ' - ' + wo.Name"
              :value="wo.ItemCode"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="Note:">
          <el-input v-model="formData.Note" type="textarea" />
        </el-form-item>
      </el-form>

      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="removeStock">Remove</el-button>
        <el-button @click="close">Close</el-button>
      </span>
    </el-dialog>
  </div>
</template>
<script>

import WorkOrder from '@/api/workOrder'
const workOrder = new WorkOrder()

import Stock from '@/api/stock'
const stock = new Stock()

export default {
  props: {
    item: { type: Object, default: stock.item.itemDataEmpty },
    visible: { type: Boolean, default: false }
  },
  data() {
    return {
      formData: Object.assign({}, stock.item.history.removeDataEmpty),
      workOrders: []
    }
  },
  methods: {
    async onOpen() {
      this.workOrders = await workOrder.search('InProgress')
      this.formData = Object.assign({}, stock.item.history.removeDataEmpty)
      this.formData.ItemCode = this.$props.item.ItemCode
    },
    close() {
      this.visible = false
      this.$emit('update:visible', this.visible)
    },
    removeStock() {
      stock.item.history.remove(this.formData).then(response => {
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
