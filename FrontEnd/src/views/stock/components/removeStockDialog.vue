<template>
  <div class="add-stock-dialog">
    <el-dialog title="Remove Stock" :visible.sync="visible" :before-close="closeDialog">
      <el-form label-width="150px">
        <el-form-item label="Stock Quantity:">{{ item.Quantity }}</el-form-item>

        <el-form-item label="Remove Quantity:">
          <el-input-number v-model="removeQuantity" :min="1" :max="item.Quantity" />
        </el-form-item>

        <el-form-item label="Work Order:">
          <el-select v-model="workOrderId" filterable>
            <el-option
              v-for="wo in workOrders"
              :key="wo.Id"
              :label="'WO-' + wo.WorkOrderNo + ' - ' + wo.Title"
              :value="wo.Id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="Note">
          <el-input v-model="note" type="textarea" />
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
  props: {
    item: { type: Object, default: itemData },
    visible: { type: Boolean, default: false }
  },
  data() {
    return {
      removeQuantity: 0,
      workOrders: null,
      workOrderId: null,
      note: ''
    }
  },
  mounted() {
    this.getWorkOrders()
  },
  methods: {
    closeDialog() {
      this.visible = false
      this.$emit('update:visible', this.visible)
    },
    getWorkOrders() {
      requestBN({
        url: '/workOrder',
        methood: 'get',
        params: { Status: 'InProgress' }
      }).then(response => {
        this.workOrders = response.data
      })
    },
    removeStock() {
      requestBN({
        method: 'post',
        url: '/stock/history/item',
        data: {
          StockNo: this.item.StockNo,
          RemoveQuantity: this.removeQuantity,
          WorkOrderId: this.workOrderId,
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
