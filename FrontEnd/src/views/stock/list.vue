<template>
  <div class="app-container">
    <el-form ref="filter" :inline="true">
      <el-form-item>
        <el-input
          v-model="filter.StockNumber"
          placeholder="Stock No"
          @keyup.enter.native="onFilterChange"
        />
      </el-form-item>
      <el-form-item>
        <el-select
          v-model="filter.Manufacturer"
          filterable
          placeholder="Manufacturer"
        >
          <el-option
            v-for="item in manufacturers"
            :key="item.FullName"
            :label="item.FullName"
            :value="item.FullName"
          />
        </el-select>
      </el-form-item>
      <el-form-item>
        <el-input
          v-model="filter.PartNumber"
          placeholder="Part Number"
          @keyup.enter.native="onFilterChange"
        />
      </el-form-item>

      <el-form-item>
        <el-cascader
          v-model="filter.Location"
          placeholder="Location"
          :options="locations"
          :props="{
            emitPath: false,
            value: 'Id',
            label: 'Name',
            children: 'Children',
            checkStrictly: true
          }"
        />
      </el-form-item>

      <el-form-item>
        <el-button type="primary" @click="onFilterChange">Filter</el-button>
        <el-button type="info" plain @click="onFilterReset">Reset</el-button>
      </el-form-item>

    </el-form>
    <el-checkbox v-model="fliterEmpty" @change="getStockItems()">Hide empty (Quantity 0)</el-checkbox>
    <el-table
      ref="stockTable"
      :data="stockItemsFilterd"
      style="width: 100%"
      height="82vh"
    >
      <el-table-column prop="StockNumber" label="Stock No" width="120">
        <template slot-scope="{ row }">
          <router-link :to="'/stock/item/' + row.StockNumber" class="link-type">
            <span>{{ row.StockNumber }}</span>
          </router-link>
        </template>
      </el-table-column>
      <el-table-column prop="ManufacturerName" label="Manufacturer" width="250">
        <template slot-scope="{ row }">
          <router-link :to="'/vendor/view/' + row.ManufacturerId" class="link-type">
            <span>{{ row.ManufacturerName }}</span>
          </router-link>
        </template>
      </el-table-column>
      <el-table-column
        prop="ManufacturerPartNumber"
        label="Part Number"
        width="300"
        sortable
      >
        <template slot-scope="{ row }">
          <router-link :to="'/manufacturerPart/item/' + row.ManufacturerPartItemId" class="link-type">
            <span>{{ row.ManufacturerPartNumber }}</span>
          </router-link>
        </template>
      </el-table-column>
      <el-table-column prop="Quantity" label="Quantity" width="120" />
      <el-table-column prop="Description" label="Description" />
      <el-table-column prop="LocationName" label="Location" width="220" sortable>
        <template slot-scope="{ row }">
          <router-link :to="'/location/item/' + row.LocationCode" class="link-type">
            <span>{{ row.LocationName }}</span>
          </router-link>
        </template>
      </el-table-column>
    </el-table>

    <p><b>Results:</b> {{ stockItemsFilterd.length }}</p>
  </div>
</template>

<script>
import Location from '@/api/location'
const location = new Location()

import Vendor from '@/api/vendor'
const vendor = new Vendor()

import Stock from '@/api/stock'
const stock = new Stock()

const FilterSettings = {
  StockNumber: null,
  Manufacturer: null,
  PartNumber: null,
  Location: null
}

export default {
  name: 'LocationAssignment',
  components: {},
  data() {
    return {
      filter: Object.assign({}, FilterSettings),
      stockItems: null,
      stockItemsFilterd: null,
      manufacturers: null,
      locations: null,
      fliterEmpty: true
    }
  },
  mounted() {
    this.getStockItems()
    this.getManufacturers()
    this.getLocations()

    this.filter.Location = Set(this.stockItems.Location)
  },
  methods: {
    onFilterChange() {
      this.stockItemsFilterd = []

      this.stockItems.forEach(element => {
        let filterPass = true
        if (
          this.filter.Manufacturer != null &&
          element.ManufacturerName != this.filter.Manufacturer
        ) { filterPass = false }

        if (
          this.filter.Location != null &&
          element.LocationId !== this.filter.Location
        ) { filterPass = false }

        if (this.filter.PartNumber != null) {
          const PartNumber = this.filter.PartNumber.toUpperCase()
          if (
            !element.ManufacturerPartNumber.toUpperCase().includes(PartNumber)
          ) { filterPass = false }
        }

        if (this.filter.StockNumber != null) {
          let StockNumber = this.filter.StockNumber.toUpperCase()
          StockNumber = StockNumber.replace('STK-', '')
          if (!element.StockNumber.includes(StockNumber)) filterPass = false
        }

        if (filterPass === true) this.stockItemsFilterd.push(element)
      })

      if (this.stockItemsFilterd.length === 1) {
        this.$router.push('/stock/item/' + this.stockItemsFilterd[0].StockNumber)
      }
    },
    onFilterReset() {
      this.filter = Object.assign({}, FilterSettings)

      this.$refs.stockTable.clearSort()
      this.stockItemsFilterd = this.stockItems
    },
    getStockItems() {
      stock.search(this.fliterEmpty).then(response => {
        this.stockItems = response
        this.stockItemsFilterd = this.stockItems
      })
    },
    getManufacturers() {
      vendor.search(false, true, false, false, false).then(response => {
        this.manufacturers = response
      })
    },
    getLocations() {
      location.search().then(response => {
        this.locations = response
      })
    }
  }
}
</script>
