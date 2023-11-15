<template>
  <div class="location-edit-dialog">
    <el-dialog
      title="Location Edit"
      :visible.sync="visible"
      :before-close="closeDialog"
      center
      width="75%"
      @open="onOpen()"
    >
      <el-form ref="form" label-width="150px">
        <el-form-item label="Name">
          <el-input v-model="locationData.Name" />
        </el-form-item>
        <el-form-item label="Title">
          <el-input v-model="locationData.Title" />
        </el-form-item>
        <el-form-item label="Description">
          <el-input v-model="locationData.Description" />
        </el-form-item>
        <el-form-item label="Movable">
          <el-checkbox v-model="locationData.Movable" />
        </el-form-item>
        <el-form-item label="ESD">
          <el-checkbox v-model="locationData.ESD" />
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

import Location from '@/api/location'
const location = new Location()

export default {
  name: 'VendorEdit',
  props: {
    LocationNumber: { type: Number, default: 0 },
    visible: { type: Boolean, default: false }
  },
  data() {
    return {
      locationData: {}
    }
  },
  mounted() {
  },
  methods: {
    async onOpen() {
      location.item.get(this.$props.LocationNumber, false).then(response => {
        this.locationData = response
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    save() {
      const saveParameters = {
        LocationNumber: this.$props.LocationNumber,
        Name: this.locationData.Name,
        Title: this.locationData.Title,
        Description: this.locationData.Description,
        Movable: this.locationData.Movable,
        ESD: this.locationData.ESD
      }

      location.item.save(saveParameters).then(response => {
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
      this.$emit('change')
    }
  }
}
</script>
