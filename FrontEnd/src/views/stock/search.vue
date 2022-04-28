<template>
  <div class="app-container">
    <el-form ref="filter" :inline="true">
      <el-form-item>
        <el-input
          v-model="filter.StockNo"
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
            :key="item.Name"
            :label="item.Name"
            :value="item.Name"
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
      <el-table-column prop="StockNo" label="Stock No">
        <template slot-scope="{ row }">
          <router-link :to="'/stock/item/' + row.StockNo" class="link-type">
            <span>{{ row.StockNo }}</span>
          </router-link>
        </template>
      </el-table-column>
      <el-table-column prop="ManufacturerName" label="Manufacturer" />
      <el-table-column
        prop="ManufacturerPartNumber"
        label="Part Number"
        sortable
      />
      <el-table-column prop="Date" label="Date" sortable />
      <el-table-column prop="Quantity" label="Quantity" />
      <el-table-column prop="Location" label="Location" sortable />
    </el-table>

    <p><b>Results:</b> {{ stockItemsFilterd.length }}</p>
  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'

const FilterSettings = {
  StockNo: null,
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

        if (this.filter.StockNo != null) {
          let StockNo = this.filter.StockNo.toUpperCase()
          StockNo = StockNo.replace('STK-', '')
          if (!element.StockNo.includes(StockNo)) filterPass = false
        }

        if (filterPass === true) this.stockItemsFilterd.push(element)
      })

      if (this.stockItemsFilterd.length === 1) {
        this.$router.push('/stock/item/' + this.stockItemsFilterd[0].StockNo)
      }
    },
    onFilterReset() {
      this.filter = Object.assign({}, FilterSettings)

      this.$refs.stockTable.clearSort()
      this.stockItemsFilterd = this.stockItems
    },
    getStockItems() {
      requestBN({
        url: '/stock',
        methood: 'get',
        params: {HideEmpty: this.fliterEmpty}
      }).then(response => {
        this.stockItems = response.data
        this.stockItemsFilterd = this.stockItems
      })
    },
    getManufacturers() {
      requestBN({
        url: '/part/manufacturer',
        methood: 'get'
      }).then(response => {
        this.manufacturers = response.data
      })
    },
    getLocations() {
      requestBN({
        url: '/location',
        methood: 'get'
      }).then(response => {
        this.locations = response.data
      })
    }
  }
}
</script>
