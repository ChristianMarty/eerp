<template>
  <div class="inventory-accessory-dialog">

    <el-dialog
      title="Accessory"
      :visible.sync="visible"
      :before-close="closeDialog"
      center
      @open="onOpen()"
    >
      <el-form :model="accessoryData" label-width="200px">

        <el-form-item label="Inventory Number:">
          {{ inventoryNumber }}

        </el-form-item>

        <el-form-item label="Accessory Number:">
          {{ accessoryData.AccessoryNumber }}
        </el-form-item>

        <el-form-item label="Description:">
          <el-input v-model="accessoryData.Description" />
        </el-form-item>

        <el-form-item label="Note:">
          <el-input v-model="accessoryData.Note" />
        </el-form-item>

        <el-form-item label="Labeled:">
          <el-checkbox v-model="accessoryData.Labeled" />
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
  name: 'InventoryAccessoryDialog',
  props: {
    inventoryNumber: { type: String, default: '' },
    accessoryNumber: { type: Number, default: 0 },
    visible: { type: Boolean, default: false }
  },
  data() {
    return {
      accessoryData: Object.assign({}, inventory.accessory.itemReturn)
    }
  },
  mounted() {
  },
  methods: {
    async onOpen() {
      this.accessoryData = Object.assign({}, inventory.accessory.itemReturn)
      if (this.$props.accessoryNumber !== null) {
        this.accessoryData = await inventory.accessory.search(this.$props.inventoryNumber + '-' + this.$props.accessoryNumber)
      }
    },
    save() {
      this.accessoryData.ItemCode = this.$props.inventoryNumber
      if (this.$props.accessoryNumber !== 0) this.accessoryData.ItemCode = this.accessoryData.ItemCode + '-' + this.$props.accessoryNumber

      inventory.accessory.save(this.accessoryData).then(response => {
        this.closeDialog()
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
