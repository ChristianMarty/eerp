<template>
  <div class="inventory-history-dialog">
    <el-dialog
      title="Inventory History"
      :visible.sync="visible"
      :before-close="closeDialog"
      center
      @open="onOpen()"
    >

      <el-form :model="historyData" label-width="120px">
        <el-form-item label="Type:">
          <el-select v-model="historyData.Type" filterable>
            <el-option
              v-for="item in historyTypeOptions"
              :key="item"
              :label="item"
              :value="item"
            />
          </el-select>
        </el-form-item>

        <el-form-item label="Description:">
          <el-input v-model="historyData.Description" />
        </el-form-item>

        <el-form-item label="Date:">
          <el-date-picker v-model="historyData.Date" type="date" placeholder="Pick a date" value-format="yyyy-MM-dd" style="width: 100%;" />
        </el-form-item>

        <el-form-item label="Recurring:">
          <el-switch v-model="recurring" @change="recurringChange()" />
        </el-form-item>

        <el-form-item label="Next Date:">
          <el-date-picker v-model="historyData.NextDate" type="date" placeholder="Pick a date" :disabled="!recurring" value-format="yyyy-MM-dd" style="width: 100%;" />
        </el-form-item>

        <el-form-item>
          <el-button type="primary" @click="save()">Save</el-button>
          <el-button @click="closeDialog()">Cancel</el-button>
        </el-form-item>

      </el-form>

    </el-dialog>
  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'
import * as defaultSetting from '@/utils/defaultSetting'

const historyData_empty = {
  InventoryNumber: null,
  Description: '',
  Type: '',
  Date: '',
  NextDate: null,
  EditToken: null
}

export default {
  name: 'InventoryItemHistoryData',
  props: { inventoryNumber: { type: String, default: '' }, visible: { type: Boolean, default: false }, editToken: { type: String, default: null }},
  data() {
    return {
      historyData: Object.assign({}, historyData_empty),
      recurring: false,
      historyTypeOptions: []
    }
  },
  mounted() {
    // this.getPrintTemplate()
  //  this.getPrinter()
  },
  methods: {
    onOpen() {
      this.getHistoryTypes()
      this.getHistoryData()
    },
    getHistoryTypes() {
      requestBN({
        url: '/inventory/history/type',
        methood: 'get'
      }).then(response => {
        this.historyTypeOptions = response.data
      })
    },
    getHistoryData() {
      this.historyData = historyData_empty
      if (this.$props.editToken == null) return

      requestBN({
        url: '/inventory/history/item',
        methood: 'get',
        params: { EditToken: this.$props.editToken }
      }).then(response => {
        this.historyData = response.data

        if (this.historyData.NextDate !== null) this.recurring = true
      })
    },
    recurringChange(state) {
      if (!state) this.historyData.NextDate = null
    },
    save() {
      if (this.$props.editToken == null) {
        this.historyData.InventoryNumber = this.$props.inventoryNumber
        requestBN({
          method: 'post',
          url: '/inventory/history/item',
          data: this.historyData
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
      } else {
        this.historyData.EditToken = this.$props.editToken
        requestBN({
          method: 'patch',
          url: '/inventory/history/item',
          data: this.historyData
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
      }
    },
    closeDialog() {
      this.visible = false
      this.$emit('update:visible', this.visible)
      this.$emit('change')
    }
  }
}
</script>
