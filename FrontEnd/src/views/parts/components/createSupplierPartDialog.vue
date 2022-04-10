<template>
  <div class="create-supplier-part-dialog">
    <el-dialog
      title="Create Supplier Part"
      :visible.sync="visible"
      :before-close="closeDialog"
      @open="loadData()"
    >
      <el-form ref="inputForm" :model="receivalData" class="form-container" label-width="150px">
        <el-form-item label="Supplier:" prop="suppliers">
          <el-cascader
            v-model="supplierPartData.SupplierId"
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
        <el-form-item label="Part Number:" prop="SupplierPartNumber">
          <el-input v-model="supplierPartData.SupplierPartNumber" />
        </el-form-item>
        <el-form-item label="Link:" prop="SupplierPartLink">
          <el-input v-model="supplierPartData.SupplierPartLink" />
        </el-form-item>
        <el-form-item label="Note:">
          <el-input v-model="supplierPartData.Note" type="textarea" placeholder="Note" />
        </el-form-item>
      </el-form>

      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="save()">Save</el-button>
        <el-button @click="closeDialog">Close</el-button>
      </span>
    </el-dialog>
  </div>
</template>

<script>

import requestBN from '@/utils/requestBN'

export default {
  name: 'CreateSupplierPart',
  props: { manufacturerPartId: { type: Number, default: 0 }, visible: { type: Boolean, default: false } },
  data() {
    return {
      supplierPartData: {},
      suppliers: {}
    }
  },
  mounted() {
    this.getSuppliers();
  },
  methods: {
    loadData() {

    },
    getSuppliers() {
      requestBN({
        url: '/supplier',
        methood: 'get'
      }).then(response => {
        this.suppliers = response.data
      })
    },
    save() {

      this.supplierPartData.ManufacturerPartId = this.$props.manufacturerPartId;

      requestBN({
        method: 'post',
        url: '/supplier/supplierPart',
        data: { data: this.supplierPartData }
      }).then(response => {
        if (response.error == null) {
          this.closeDialog()
        } else {
          this.$message({
            showClose: true,
            message: response.error,
            duration: 0,
            type: 'error'
          })
        }
      })
    },
    closeDialog() {
      this.visible = false
      this.$emit('update:visible', this.visible)
    }
  }
}
</script>
