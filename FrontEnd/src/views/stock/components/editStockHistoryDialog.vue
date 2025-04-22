<template>
  <div class="edit-stock-history-dialog">
    <el-dialog
      title="Edit Stock History"
      :visible.sync="visible"
      :before-close="closeDialog"
      @open="onOpen"
    >
      {{ data.Date }}
      <el-form label-width="150px">

        <el-form-item label="Quantity:">
          <el-input-number v-model="data.Quantity" />
        </el-form-item>

        <el-form-item v-if="data.Type == 'Remove'" label="Work Order:">
          <el-select v-model="data.WorkOrderCode" filterable>
            <el-option
              v-for="wo in workOrders"
              :key="wo.ItemCode"
              :label="wo.ItemCode + ' - ' + wo.Name"
              :value="wo.ItemCode"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="Note">
          <el-input v-model="data.Note" type="textarea" />
        </el-form-item>
      </el-form>

      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="save">Save</el-button>
        <el-button @click="closeDialog">Close</el-button>
      </span>
    </el-dialog>
  </div>
</template>
<script>

import requestBN from '@/utils/requestBN'

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
      workOrders: null,
      workOrderNumber: null,
      data: {}

    }
  },
  mounted() {
    this.getWorkOrders()
  },
  methods: {
    onOpen() {
      stock.item.history.item(this.$props.stockHistoryCode).then(response => {
        this.data = response
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    closeDialog() {
      this.visible = false
      this.$emit('update:visible', this.visible)
      this.$emit('change')
    },
    save() {
      requestBN({
        method: 'patch',
        url: '/stock/history/item',
        data: {
          EditToken: this.data.EditToken,
          Quantity: this.data.Quantity,
          WorkOrderNumber: this.data.WorkOrderCode,
          Note: this.data.Note,
          Type: this.data.Type
        }
      }).then(response => {
        if (response.error !== null) {
          this.$message({
            showClose: true,
            message: response.error,
            duration: 1500,
            type: 'error'
          })
        } else {
          this.closeDialog()
        }
      })
    },
    getWorkOrders() {
      requestBN({
        url: '/workOrder',
        method: 'get',
        params: { Status: 'InProgress' }
      }).then(response => {
        this.workOrders = response.data
      })
    }
  }
}
</script>
