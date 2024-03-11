<template>
  <div class="app-container">
    <template>
      <el-collapse>
        <el-collapse-item name="1">
          <template slot="title">
            <b>Filter</b>
          </template>
          <el-form ref="filter" :model="filter" label-width="130px">
            <el-form-item label="Inventory No:">
              <el-input v-model="filter.InventoryNumber" placeholder="" clearable />
            </el-form-item>
            <el-form-item label="Category">
              <el-cascader-panel
                v-model="filter.CategoryId"
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
            <el-form-item label="Locaton">
              <el-cascader-panel
                v-model="filter.LocationNumber"
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
              <el-button
                type="primary"
                @click="onFilter()"
              >Filter</el-button>
              <el-button
                type="info"
                plain
                @click="onFilterReset()"
              >Reset</el-button>
            </el-form-item>
          </el-form>
        </el-collapse-item>
      </el-collapse>

      <p><b>Number of Results: </b>{{ inventory.length }}</p>
      <el-table :data="inventory" style="width: 100%">
        <el-table-column prop="ItemCode" label="Inventory No" width="140" sortable>
          <template slot-scope="{ row }">
            <router-link
              :to="'/inventory/item/' + row.ItemCode"
              class="link-type"
            >
              <span>{{ row.ItemCode }}</span>
            </router-link>
          </template>
        </el-table-column>

        <el-table-column prop="PicturePath" label="Picture">
          <template slot-scope="{ row }">
            <el-image style="width: 100px;" :src="row.PicturePath" :fit="fit" />
          </template>
        </el-table-column>
        <el-table-column prop="Title" label="Title" sortable />
        <el-table-column prop="ManufacturerName" label="Manufacturer" sortable />
        <el-table-column prop="Type" label="Type" sortable />
        <el-table-column prop="SerialNumber" label="Serial Number" sortable />
        <el-table-column prop="CategoryName" label="Category" sortable />
        <el-table-column prop="LocationName" label="Location" sortable />
      </el-table>
    </template>
  </div>
</template>

<script>

import Inventory from '@/api/inventory'
const inventory = new Inventory()

import Location from '@/api/location'
const location = new Location()

export default {
  name: 'InventoryBrowser',
  components: {},
  data() {
    return {
      filter: Object.assign({}, inventory.searchParameters),
      inventory: Object.assign({}, inventory.searchReturn),

      locations: Object.assign({}, location.searchReturn),
      categories: Object.assign({}, inventory.categoriesReturn)
    }
  },
  watch: {
    '$route.query': {
      handler(newVal) {
        this.filterFromParameter()
        this.getInventory()
      }
    }
  },
  async mounted() {
    this.filterFromParameter()

    this.locations = await location.search()
    this.categories = await inventory.categories()
    this.getInventory()
  },
  methods: {
    filterFromParameter() {
      this.filter.CategoryId = this.$route.query.CategoryId
      this.filter.LocationNumber = this.$route.query.LocationNumber
      this.filter.InventoryNumber = this.$route.query.InventoryNumber
    },
    filterToParameter() {
      if (this.filter.InventoryNumber === '') this.filter.InventoryNumber = null

      const filter = {}
      if (this.filter.CategoryId !== null) filter.CategoryId = this.filter.CategoryId
      if (this.filter.LocationNumber !== null) filter.LocationNumber = this.filter.LocationNumber
      if (this.filter.InventoryNumber !== null) filter.InventoryNumber = this.filter.InventoryNumber

      this.$router.push({ query: filter })
    },
    onFilter() {
      this.filterToParameter()
      this.getInventory()
    },
    onFilterReset() {
      this.filter = Object.assign({}, inventory.searchParameters)
      this.filterToParameter()
      this.getInventory()
    },
    async getInventory() {
      this.inventory = await inventory.search(this.filter)
    }
  }
}
</script>
