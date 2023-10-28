<template>
  <div class="vendor-edit-dialog">
    <el-dialog
      title="Vendor Edit"
      :visible.sync="visible"
      :before-close="closeDialog"
      center
      width="75%"
      @open="onOpen()"
    >
      <el-form ref="form" :model="vendorData" label-width="150px">
        <el-form-item label="Full Name">
          <el-input v-model="vendorData.FullName" />
        </el-form-item>
        <el-form-item label="Short Name">
          <el-input v-model="vendorData.ShortName" />
        </el-form-item>
        <el-form-item label="Abbreviated Name">
          <el-input v-model="vendorData.AbbreviatedName" />
        </el-form-item>
        <el-form-item label="Customer Number">
          <el-input v-model="vendorData.CustomerNumber" />
        </el-form-item>
        <el-form-item label="Is Supplier">
          <el-checkbox v-model="vendorData.IsSupplier" />
        </el-form-item>
        <el-form-item label="Is Manufacturer">
          <el-checkbox v-model="vendorData.IsManufacturer" />
        </el-form-item>
        <el-form-item label="Is Contractor">
          <el-checkbox v-model="vendorData.IsContractor" />
        </el-form-item>
        <el-form-item label="Has Parent">
          <el-checkbox v-model="hasParent" />
          <br>
          <el-cascader
            v-model="parentId"
            :disabled="!hasParent"
            filterable
            :options="suppliers"
            :props="{
              emitPath: false,
              value: 'Id',
              label: 'Name',
              children: 'Children',
              checkStrictly: true
            }"
          />
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

import Vendor from '@/api/vendor'
const vendor = new Vendor()

export default {
  name: 'VendorEdit',
  props: {
    VendorId: { type: Number, default: 0 },
    visible: { type: Boolean, default: false }
  },
  data() {
    return {
      vendorData: {},
      suppliers: [],
      parentId: null,
      hasParent: false
    }
  },
  mounted() {
  },
  methods: {
    async onOpen() {
      this.vendorData = await vendor.item(this.$props.VendorId)

      vendor.search().then(response => {
        this.suppliers = response
        this.parentId = this.vendorData.ParentId
        if (this.parentId == null) this.hasParent = false
        else this.hasParent = true
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
      let parentId = null
      if (this.hasParent) parentId = Number(this.parentId)

      const saveParameters = {
        VendorId: this.$props.VendorId,
        ParentId: parentId,
        FullName: this.vendorData.FullName,
        ShortName: this.vendorData.ShortName,
        AbbreviatedName: this.vendorData.AbbreviatedName,
        CustomerNumber: this.vendorData.CustomerNumber,
        IsSupplier: this.vendorData.IsSupplier,
        IsManufacturer: this.vendorData.IsManufacturer,
        IsContractor: this.vendorData.IsContractor
      }

      vendor.save(saveParameters).then(response => {
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
