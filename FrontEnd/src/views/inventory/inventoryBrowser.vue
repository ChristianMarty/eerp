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
              <el-input v-model="filter.InventoryNo" placeholder="" clearable />
            </el-form-item>
            <el-form-item label="Category">
              <el-cascader-panel
                v-model="filter.Category"
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
            <el-form-item label="Locaton">
              <el-cascader-panel
                v-model="filter.Location"
                :options="locations"
                :props="{
                  emitPath: false,
                  value: 'LocNr',
                  label: 'Name',
                  children: 'Children',
                  checkStrictly: true
                }"
              />
            </el-form-item>
            <el-form-item>
              <el-button
                type="primary"
                @click="onFilterChange"
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

      <el-table :data="inventory" style="width: 100%">
        <el-table-column prop="GroupSelect" label="Select" width="70">
          <template slot-scope="scope">
            <el-checkbox v-model="scope.row.GroupSelect" />
          </template>
        </el-table-column>
        <el-table-column prop="InvNo" label="Inventory No" width="120">
          <template slot-scope="{ row }">
            <router-link
              :to="'/inventory/inventoryView/' + row.InvNo"
              class="link-type"
            >
              <span>{{ row.InvNo }}</span>
            </router-link>
          </template>
        </el-table-column>

        <el-table-column prop="PicturePath" label="Picture">
          <template slot-scope="{ row }">
            <el-image style="width: 100px;" :src="row.PicturePath" :fit="fit" />
          </template>
        </el-table-column>
        <el-table-column prop="Titel" label="Titel" />
        <el-table-column prop="Manufacturer" label="Manufacturer" />
        <el-table-column prop="Type" label="Type" />
        <el-table-column prop="SerialNumber" label="Serial Number" />
        <el-table-column prop="LocationName" label="Location" />
        <el-table-column prop="PurchasePrice" label="Purchase Price" />
        <el-table-column prop="PurchaseDate" label="Purchase Date" />
        <el-table-column prop="Status" label="Status" />
      </el-table>
    </template>
  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'
import Cookies from 'js-cookie'

const FilterSettings = {
  InventoryNo: null,
  Location: null,
  Category: null
}

export default {
  name: 'InventoryBrowser',
  components: {},
  data() {
    return {
      filter: Object.assign({}, FilterSettings),
      inventory: null,
      locations: null,
      categories: null,
      selected: null
    }
  },
  mounted() {
    this.getLocations()
    this.getInventoryCategories()
    this.onFilterReset()
  },
  methods: {
    onFilterChange() {
      this.getInventory()
    },
    onFilterReset() {
      this.filter.InventoryNo = null
      this.filter.Location = null
      this.filter.Category = null
      this.getInventory()
    },
    getInventory() {
      requestBN({
        url: '/inventory',
        methood: 'get',
        params: {
          InvNo: this.filter.InventoryNo,
          LocNr: this.filter.Location,
          Category: this.filter.Category
        }
      }).then(response => {
        this.inventory = response.data
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
          if (element.GroupSelect == true) {
            invNoList.push(element.InvNo)
          }
        }
      })

      Cookies.set('invNo', invNoList)

      this.$router.push({ path: '/inventory/inventoryLabel' })
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
