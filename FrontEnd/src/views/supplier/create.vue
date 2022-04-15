<template>
  <div class="app-container">
    <h1>Create Supplier</h1>
    <el-divider />

    <el-form label-width="150px">

      <el-form-item label="Supplier Name:">
        <el-input v-model="supplierName" />
      </el-form-item>

      <el-form-item>
        <el-button type="primary" @click="save">Save</el-button>
      </el-form-item>
    </el-form>  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'

export default {
  components: {},
  data() {
    return {
      supplierName: ''
    }
  },
  mounted() {
  },
  methods: {
    save() {
      requestBN({
        method: 'post',
        url: '/supplier',
        data: { SupplierName: this.supplierName }
      }).then(response => {
        if (response.error !== null) {
          this.$message({
            showClose: true,
            message: response.error,
            duration: 0,
            type: 'error'
          })
        } else {
          this.$router.push('/supplier/supplierView/' + response.data.SupplierId)
        }
      })
    }
  }
}
</script>
