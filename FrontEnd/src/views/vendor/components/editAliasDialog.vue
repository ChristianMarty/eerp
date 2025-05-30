<template>
  <div class="alias-edit-dialog">
    <el-dialog
      title="Alias Edit"
      :visible.sync="visible"
      :before-close="closeDialog"
      center
      width="75%"
      @open="onOpen()"
    >
      <el-form ref="form" label-width="150px">
        <el-form-item label="Name">
          <el-input v-model="aliasData.Name" />
        </el-form-item>
        <el-form-item label="Note">
          <el-input v-model="aliasData.Note" type="textarea" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="save()">Save</el-button>
          <el-button type="danger" @click="deleteAlias()">Delete</el-button>
          <el-button @click="closeDialog()">Cancel</el-button>
        </el-form-item>
      </el-form>

    </el-dialog>
  </div>
</template>

<script>

import Vendor from '@/api/vendor'
const vendor = new Vendor()

export default {
  name: 'VendorEdit',
  props: {
    aliasId: { type: Number, default: 0 },
    vendorId: { type: Number, default: 0 },
    visible: { type: Boolean, default: false }
  },
  data() {
    return {
      aliasData: Object.assign({}, vendor.alias.searchReturn)
    }
  },
  mounted() {
  },
  methods: {
    onOpen() {
      if (this.$props.aliasId === 0) {
        this.aliasData = Object.assign({}, vendor.alias.searchReturn)
      } else {
        vendor.alias.search(this.$props.aliasId).then(response => {
          this.aliasData = response
        })
      }
    },
    save() {
      let aliasId = this.$props.aliasId
      if (aliasId === 0) aliasId = null

      const saveParameters = {
        AliasId: aliasId,
        VendorId: this.$props.vendorId,
        Name: this.aliasData.Name,
        Note: this.aliasData.Note
      }

      vendor.alias.save(saveParameters).then(response => {
        this.closeDialog()
      })
    },
    deleteAlias() {
      this.$confirm('This will permanently delete this vendor alias. Continue?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning'
      }).then(() => {
        vendor.alias.delete(this.$props.aliasId).then(response => {
          this.closeDialog()
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
