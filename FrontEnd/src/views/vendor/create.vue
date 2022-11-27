<template>
  <div class="app-container">
    <h1>Create Vendor</h1>
    <el-divider />

    <el-form label-width="150px">

      <el-form-item label="Vendor Name:">
        <el-input v-model="vendorName" />
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
      vendorName: ''
    }
  },
  mounted() {
  },
  methods: {
    save() {
      vendor.create(this.vendorName).then(response => {
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
