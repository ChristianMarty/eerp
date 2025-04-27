<template>
  <div class="edit-stock-history-dialog">
    <el-dialog
      title="Edit Stock History"
      :visible.sync="visible"
      :before-close="close"
      @open="onOpen"
    >
      {{ data.Date }}
      <el-form label-width="150px">

        <el-form-item label="Quantity:">
          <el-input-number v-model="formData.Quantity" />
        </el-form-item>

        <el-form-item v-if="formData.Type === 'Remove'" label="Work Order:">
          <el-select v-model="formData.WorkOrderCode" filterable>
            <el-option
              v-for="wo in workOrders"
              :key="wo.ItemCode"
              :label="wo.ItemCode + ' - ' + wo.Name"
              :value="wo.ItemCode"
            />
          </el-select>
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

import WorkOrder from '@/api/workOrder'
const workOrder = new WorkOrder()

import Stock from '@/api/stock'
const stock = new Stock()

export default {
  props: {
    stockHistoryCode: { type: String, default: '' },
    visible: { type: Boolean, default: false }
  },
  emits: ['change'],
  data() {
    return {
      workOrders: [],
      formData: Object.assign({}, stock.item.history.updateDateEmpty),
      data: {}
    }
  },
  methods: {
    async onOpen() {
      this.workOrders = await workOrder.search('InProgress')
      stock.item.history.item(this.$props.stockHistoryCode).then(response => {
        this.formData.EditToken = response.EditToken
        this.formData.Quantity = response.Quantity
        this.formData.WorkOrderCode = response.WorkOrderCode
        this.formData.Note = response.Note
        this.formData.Type = response.Type
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    close() {
      this.visible = false
      this.$emit('update:visible', this.visible)
      this.$emit('change')
    },
    save() {
      stock.item.history.update(this.formData).then(response => {
        this.close()
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    }
  }
}
</script>
