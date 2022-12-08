<template>
  <div class="assembly-location-transfer-dialog">
    <el-dialog
      title="Assembly Location Transfer History"
      :visible.sync="visible"
      :before-close="closeDialog"
      center
      @open="onOpen()"
    >

      <h2>New Location</h2>
      <p>Select the location to which the Items should be transfered.</p>

      <p>
        <el-cascader-panel
          v-model="inputLocNr"
          :options="locations"
          :props="{
            emitPath: false,
            value: 'LocNr',
            label: 'Name',
            children: 'Children',
            checkStrictly: true
          }"
        />
      </p>

      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="transfer();">Transfer</el-button>
        <el-button @click="closeDialog()">Cancel</el-button>
      </span>

    </el-dialog>
  </div>
</template>

<script>

import Location from '@/api/location'
const location = new Location()

export default {
  name: 'AssemblyItemHistoryData',
  props: { barcode: { type: String, default: '' }, visible: { type: Boolean, default: false }},
  data() {
    return {
      locations: Object.assign({}, location.searchReturn),
      inputLocNr: ''
    }
  },
  mounted() {
  },
  methods: {
    async onOpen() {
      this.locations = await location.search()
    },
    transfer() {
      if (!this.inputLocNr) {
        this.$message({
          showClose: true,
          message: 'No Location specified',
          duration: 3000,
          type: 'warning'
        })
        return
      }

      location.transfer(this.inputLocNr, [this.$props.barcode]).then(response => {
        this.$message({
          showClose: true,
          message: 'Assembly Transfer Successful',
          type: 'success'
        })
        this.$emit('change')
        this.closeDialog()
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
    }
  }
}
</script>
