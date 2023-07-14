<template>
  <div class="edit-stock-history-dialog">
    <el-dialog title="Edit Stock History" :visible.sync="visible" :before-close="closeDialog">
      {{ data.Date }}
      <el-form label-width="150px">

        <el-form-item label="Quantity:">
          <el-input-number v-model="data.Quantity" />
        </el-form-item>

        <el-form-item v-if="data.Type == 'remove'" label="Work Order:">
          <el-select v-model="data.WorkOrderNumber" filterable>
            <el-option
              v-for="wo in workOrders"
              :key="wo.WorkOrderBarcode"
              :label="wo.WorkOrderBarcode + ' - ' + wo.Title"
              :value="wo.WorkOrderBarcode"
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

const itemData = {
  Quantity: 0,
  WorkOrderNumber: null,
  Note: ''
}

import requestBN from '@/utils/requestBN'

export default {
  props: {
    data: { type: Object, default: itemData },
    visible: { type: Boolean, default: false }
  },
  emits: ['change'],
  data() {
    return {
      workOrders: null,
      workOrderNumber: null

    }
  },
  mounted() {
    this.getWorkOrders()
  },
  methods: {
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
          WorkOrderNumber: this.data.WorkOrderNumber,
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
        methood: 'get',
        params: { Status: 'InProgress' }
      }).then(response => {
        this.workOrders = response.data
      })
    }
  }
}
</script>
