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
                @click="getInventory()"
              >Filter</el-button>
              <el-button
                type="info"
                plain
                @click="onFilterReset"
              >Reset</el-button>
            </el-form-item>
          </el-form>
        </el-collapse-item>
      </el-collapse>

      <el-collapse>
        <el-collapse-item name="2">
          <template slot="title">
            <b>Group Action</b>
          </template>
          <el-button type="primary" @click="addPrint">Print Label</el-button>
        </el-collapse-item>
      </el-collapse>
      <p><b>Number of Results: </b>{{ inventory.length }}</p>
      <el-table :data="inventory" style="width: 100%">
        <el-table-column prop="GroupSelect" label="Select" width="70">
          <template slot-scope="scope">
            <el-checkbox v-model="scope.row.GroupSelect" />
          </template>
        </el-table-column>
        <el-table-column prop="InventoryNumber" label="Inventory No" width="140" sortable>
          <template slot-scope="{ row }">
            <router-link
              :to="'/inventory/inventoryView/' + row.InventoryBarcode"
              class="link-type"
            >
              <span>{{ row.InventoryBarcode }}</span>
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
        <el-table-column prop="Status" label="Status" sortable />
      </el-table>
    </template>
  </div>
</template>

<script>

import Cookies from 'js-cookie'

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
      categories: Object.assign({}, inventory.categoriesReturn),

      selected: null
    }
  },
  async mounted() {
    this.onFilterReset()

    this.locations = await location.search()
    this.categories = await inventory.categories()
  },
  methods: {
    onFilterReset() {
      this.filter = Object.assign({}, inventory.searchParameters)
      this.getInventory()
    },
    async getInventory() {
      this.inventory = await inventory.search(this.filter)
    },
    addPrint() {
      var cookieList = []
      try {
        var cookiesText = Cookies.get('invNo')
        cookieList = JSON.parse(cookiesText)
      } catch (e) {
        cookieList = []
      }

      var invNoList = []
      invNoList = invNoList.concat(cookieList)

      this.inventory.forEach(element => {
        if (typeof element.GroupSelect !== 'undefined') {
          if (element.GroupSelect === true) {
            invNoList.push(element.InventoryNumber)
          }
        }
      })

      Cookies.set('invNo', invNoList)

      this.$router.push({ path: '/inventory/inventoryLabel' })
    }
  }
}
</script>
