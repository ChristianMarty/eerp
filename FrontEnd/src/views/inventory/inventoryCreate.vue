<template>
  <div class="app-container">
    <el-form
      ref="postForm"
      :model="postForm"
      class="form-container"
      label-width="130px"
    >
      <el-form-item label="Title:">
        <el-input
          v-model="postForm.Title"
          placeholder="Please input"
        />
      </el-form-item>
      <el-form-item label="Manufacturer:">
        <el-input
          v-model="postForm.Manufacturer"
          placeholder="Please input"
        />
      </el-form-item>
      <el-form-item label="Type:">
        <el-input v-model="postForm.Type" placeholder="Please input" />
      </el-form-item>
      <el-form-item label="Serial Number:">
        <el-input
          v-model="postForm.SerialNumber"
          placeholder="Please input"
        />
      </el-form-item>
      <el-form-item label="Category:">
        <el-cascader-panel
          v-model="postForm.InventoryCategory"
          :options="inventoryCategories"
          :props="{
            emitPath: false,
            value: 'Name',
            label: 'Name',
            children: 'Children',
            checkStrictly: true
          }"
        />
      </el-form-item>
      <el-form-item label="Purchase Date:">
        <el-date-picker
          v-model="postForm.PurchaseDate"
          type="date"
          placeholder="Pick a day"
          value-format="yyyy-MM-dd"
        />
      </el-form-item>
      <el-form-item label="Purchase Price:">
        <el-input-number
          v-model="postForm.PurchasePrice"
          :precision="2"
          :controls="false"
        />
      </el-form-item>
      <el-row>
        <el-form-item label="Supplier:">
          <el-select v-model="postForm.Supplier" filterable>
            <el-option
              v-for="item in suppliers"
              :key="item.Name"
              :label="item.Name"
              :value="item.Name"
            />
          </el-select>
        </el-form-item>
      </el-row>
      <el-form-item label="Location:">
        <el-cascader-panel
          v-model="postForm.Location"
          :options="locations"
          :props="{
            emitPath: false,
            value: 'Name',
            label: 'Name',
            children: 'Children',
            checkStrictly: true
          }"
        />
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="onSubmit">Create</el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'

const defaultForm = {
  Title: '',
  Manufacturer: '',
  Type: '',
  SerialNumber: '',
  Supplier: '',
  Location: '',
  InventoryCategory: '',
  PurchaseDate: '',
  PurchasePrice: '',
  Description: '',
  Note: ''
}

export default {
  components: {},
  data() {
    return {
      postForm: Object.assign({}, defaultForm),
      input: null,
      locations: null,
      suppliers: null,
      inventoryCategories: null,
      submitResponse: null
    }
  },
  mounted() {
    this.getLocations()
    this.getSuppliers()
    this.getInventoryCategories()
    if (this.$route.params.invNo) this.getInventoryData()
  },
  methods: {
    getInventoryData() {
      requestBN({
        url: '/inventory/item',
        methood: 'get',
        params: { InvNo: this.$route.params.invNo }
      }).then(response => {
        this.postForm.Title = response.data.Title
        this.postForm.Manufacturer = response.data.Manufacturer
        this.postForm.Type = response.data.Type
        this.postForm.Supplier = response.data.SupplierName
        this.postForm.Location = response.data.LocationName
        this.postForm.InventoryCategory = response.data.CategorieName
        this.postForm.PurchaseDate = response.data.PurchaseDate
        this.postForm.PurchasePrice = response.data.PurchasePrice
        this.postForm.Description = response.data.Description
        this.postForm.Note = response.data.Note
      })
    },
    onSubmit() {
      requestBN({
        method: 'post',
        url: '/inventory/item',
        data: { data: this.postForm }
      }).then(response => {
        this.submitResponse = response
        if (response.error == null) {
          this.$router.push('/inventory/inventoryView/' + response.data.InvNo)
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
    getInventoryCategories() {
      requestBN({
        url: '/inventory/category',
        methood: 'get'
      }).then(response => {
        this.inventoryCategories = response.data
      })
    }
  }
}
</script>
