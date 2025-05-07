<template>
  <div class="app-container">
    <p><b>Open Requests:</b> {{ stockItems.length }}</p>
    <el-table
      ref="stockTable"
      :data="stockItems"
      style="width: 100%"
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
      <el-table-column prop="CountingRequest" label="Requested" width="130" sortable />
      <el-table-column prop="CountingRequestDate" label="Date" width="160" sortable />
    </el-table>
  </div>
</template>

<script>
import Location from '@/api/location'
const location = new Location()

import Vendor from '@/api/vendor'
const vendor = new Vendor()

import Stock from '@/api/stock'
const stock = new Stock()

export default {
  name: 'LocationAssignment',
  components: {},
  data() {
    return {
      stockItems: []
    }
  },
  mounted() {
    this.getItems()
  },
  methods: {
    getItems() {
      stock.countingRequest().then(response => {
        this.stockItems = response
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    }
  }
}
</script>
