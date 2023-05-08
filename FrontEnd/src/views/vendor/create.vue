<template>
  <div class="app-container">
    <h1>Create Vendor</h1>
    <el-divider />

    <el-form label-width="150px">

      <el-form-item label="Vendor Name:">
        <el-input v-model="vendorData.Name" />
      </el-form-item>
      <el-form-item label="Is Supplier:">
        <el-checkbox v-model="vendorData.IsSupplier" />
      </el-form-item>
      <el-form-item label="Is Manufacturer:">
        <el-checkbox v-model="vendorData.IsManufacturer" />
      </el-form-item>
      <el-form-item label="Is Contractor:">
        <el-checkbox v-model="vendorData.IsContractor" />
      </el-form-item>

      <el-form-item>
        <el-button type="primary" @click="save">Save</el-button>
      </el-form-item>
    </el-form>  </div>
</template>

<script>
import Vendor from '@/api/vendor'
const vendor = new Vendor()

export default {
  components: {},
  data() {
    return {
      vendorData: Object.assign({}, vendor.createParameters)
    }
  },
  mounted() {
  },
  methods: {
    save() {
      vendor.create(this.vendorData).then(response => {
        this.$router.push('/vendor/view/' + response.VendorId)
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    }
  }
}
</script>
