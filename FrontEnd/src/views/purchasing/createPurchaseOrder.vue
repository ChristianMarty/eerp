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
        <el-select
          v-model="formData.SupplierName"
          filterable
          placeholder="Select"
        >
          <el-option
            v-for="item in suppliers"
            :key="item.Name"
            :label="item.Name"
            :value="item.Name"
          />
        </el-select>
      </el-form-item>

      <el-form-item label="Purchase Date:">
        <el-date-picker
          v-model="formData.PurchaseDate"
          type="date"
          placeholder="Pick a day"
        />
      </el-form-item>

      <el-form-item label="Titel:">
        <el-input v-model="formData.Titel" />
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
import Cookies from 'js-cookie'

const emptyData = {
  Supplier: '',
  Titel: '',
  PurchaseDate: null,
  Description: ''
}

export default {
  name: 'LocationAssignment',
  components: {},
  data() {
    return {
      formData: Object.assign({}, emptyData),
      suppliers: null
    }
  },
  mounted() {
    this.getSuppliers()
    // this.getLocations();
    //  this.getManufacturers();
    // this.resetForm();
  },
  methods: {
    getLocations() {
      requestBN({
        url: '/location',
        methood: 'get'
      }).then(response => {
        this.locations = response.data
      })
    },
    getSuppliers() {
      requestBN({
        url: '/supplier',
        methood: 'get'
      }).then(response => {
        this.suppliers = response.data
      })
    },
    getManufacturers() {
      requestBN({
        url: '/part/manufacturer',
        methood: 'get'
      }).then(response => {
        this.manufacturer = response.data
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
        this.formData.PurchaseDate = new Date(this.formData.PurchaseDate)
        this.formData.PurchaseDate = this.formData.PurchaseDate.toISOString().split(
          'T'
        )[0]
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
