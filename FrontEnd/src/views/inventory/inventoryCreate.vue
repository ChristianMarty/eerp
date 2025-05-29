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
          v-model="postForm.ManufacturerName"
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
          v-model="postForm.CategoryId"
          :options="categories"
          :props="{
            emitPath: false,
            value: 'Id',
            label: 'Name',
            children: 'Children',
            checkStrictly: true
          }"
        />
      </el-form-item>
      <el-form-item label="Location:">
        <el-cascader-panel
          v-model="postForm.LocationNumber"
          :options="locations"
          :props="{
            emitPath: false,
            value: 'LocationNumber',
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
import Inventory from '@/api/inventory'
const inventory = new Inventory()

import Location from '@/api/location'
const location = new Location()

export default {
  components: {},
  data() {
    return {
      postForm: Object.assign({}, inventory.createParameters),
      locations: Object.assign({}, location.searchReturn),
      categories: Object.assign({}, inventory.categoriesReturn)
    }
  },
  async mounted() {
    this.categories = await inventory.categories(this.filter)
    this.locations = await location.search()
    if (this.$route.params.invNo) this.getInventoryData()
  },
  methods: {
    getInventoryData() {
      inventory.item(this.$route.params.invNo).then(response => {
        this.postForm.Title = response.Title
        this.postForm.ManufacturerName = response.ManufacturerName
        this.postForm.Type = response.Type
        this.postForm.SerialNumber = ''
        this.postForm.LocationNumber = response.LocationNumber
        this.postForm.CategoryId = response.CategoryId
      })
    },
    onSubmit() {
      inventory.create(this.postForm).then(response => {
        this.$router.push('/inventory/item/' + response.ItemCode)
      })
    }
  }
}
</script>
