<template>
  <div class="app-container">
    <h1>Create New Purchase Order</h1>

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
          filterable
          :options="suppliers"
          :props="{
            emitPath: false,
            value: 'Id',
            label: 'DisplayName',
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
        <el-button @click="dateToday()">Today</el-button>
      </el-form-item>

      <el-form-item label="Title:">
        <el-input v-model="formData.Title" />
      </el-form-item>

      <el-form-item label="Description:">
        <el-input v-model="formData.Description" type="textarea" />
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="save">Create</el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
import Purchase from '@/api/purchase'
const purchase = new Purchase()

import Vendor from '@/api/vendor'
const vendor = new Vendor()

export default {
  name: 'CreatePO',
  components: {},
  data() {
    return {
      formData: Object.assign({}, purchase.createParameters),
      suppliers: []
    }
  },
  async mounted() {
    this.suppliers = await vendor.search(true, false, false, false, false, true)
  },
  methods: {
    dateToday() {
      this.formData.PurchaseDate = new Date().toISOString().slice(0, 10)
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
      if (this.isValid() === false) {
        this.$message({
          showClose: true,
          message: 'Input Invalid',
          duration: 3,
          type: 'error'
        })
      } else {
        purchase.create(this.formData).then(response => {
          this.$router.push('/purchasing/edit/' + response.PurchaseOrderNo)
        })
      }
    }
  }
}
</script>
