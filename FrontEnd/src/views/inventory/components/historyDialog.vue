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

import Inventory from '@/api/inventory'
const inventory = new Inventory()

export default {
  name: 'InventoryItemHistoryData',
  props: {
    inventoryNumber: { type: String, default: '' },
    visible: { type: Boolean, default: false },
    editToken: { type: String, default: null }
  },
  data() {
    return {
      historyData: Object.assign({}, inventory.history.itemReturn),
      recurring: false,
      historyTypeOptions: []
    }
  },
  mounted() {
  },
  methods: {
    async onOpen() {
      this.historyTypeOptions = await inventory.history.types()
      this.getHistoryData()
    },
    getHistoryData() {
      if (this.$props.editToken == null) return
      inventory.history.search(null, this.$props.editToken)
        .then(response => {
          this.historyData = response
          if (this.historyData.NextDate !== null) this.recurring = true
        }).catch(response => {
          this.$message({
            showClose: true,
            message: response,
            duration: 1500,
            type: 'error'
          })
        })
    },
    recurringChange(state) {
      if (!state) this.historyData.NextDate = null
    },
    save() {
      this.historyData.InventoryNumber = this.$props.inventoryNumber
      this.historyData.EditToken = this.$props.editToken
      inventory.history.save(this.historyData).then(response => {
        this.closeDialog()
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 1500,
          type: 'error'
        })
      })
    },
    closeDialog() {
      this.visible = false
      this.$emit('update:visible', this.visible)
      this.$emit('change')
    }
  }
}
</script>
