<template>
  <div class="app-container">
    <h1>Create New Purchas Order</h1>

    <el-divider />
    <el-form
      ref="inputForm"
      :model="formData"
      :rules="rules"
      class="form-container"
      label-width="130px"
    >
      <el-form-item label="Supplier:" prop="suppliers">
        <el-cascader
          v-model="formData.SupplierId"
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

      <el-form-item label="Purchase Date:">
        <el-date-picker
          v-model="formData.PurchaseDate"
          type="date"
          placeholder="Pick a day"
          value-format="yyyy-MM-dd"
        />
      </el-form-item>

      <el-form-item label="Title:">
        <el-input v-model="formData.Title" />
      </el-form-item>

      <el-form-item label="Description:">
        <el-input v-model="formData.Description" type="textarea" />
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="save">Create</el-button>
        <el-button>Cancel</el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'

const emptyData = {
  SupplierId: '',
  Title: '',
  PurchaseDate: null,
  Description: ''
}

export default {
  name: 'CreatePO',
  components: {},
  data() {
    return {
      formData: Object.assign({}, emptyData),
      suppliers: null
    }
  },
  mounted() {
    this.getSuppliers()
  },
  methods: {
    getSuppliers() {
      requestBN({
        url: '/supplier',
        methood: 'get'
      }).then(response => {
        this.suppliers = response.data
      })
    },
    isValid() {
      /* this.$refs.inputForm.validate(valid => {
        if (valid) {
          return true;
        } else {
          return false;
        }*/
      return true
      //  });
    },
    save() {
      if (this.isValid() == false) {
        this.$message({
          showClose: true,
          message: 'Input Invalide',
          duration: 3,
          type: 'error'
        })
      } else {
        requestBN({
          method: 'post',
          url: '/purchasOrder',
          data: { data: this.formData }
        }).then(response => {
          if (response.error == null) {
            this.$router.push(
              '/purchasing/edit/' + response.data.PurchaseOrderNo
            )
          } else {
            this.$message({
              showClose: true,
              message: response.error,
              duration: 0,
              type: 'error'
            })
          }
        })
      }
    }
  }
}
</script>
