<template>
  <div class="production_part-availability-container">

    <el-checkbox v-model="availabilityAuthorizedOnly">Authorized Only</el-checkbox>
    <el-checkbox v-model="availabilityBrokers">Include Brokers</el-checkbox>
    <el-button type="primary" @click="getAvailability">Load Data</el-button>
    <template v-if="availabilityData != null">
      <p>
        Data provided by Octopart, {{ availabilityData.Timestamp }}
      </p>
      <el-checkbox v-model="expandedAllRows" @change="chnageAvailabilityExpandedRows()">Expanded all rows</el-checkbox>
      <el-table
        v-loading="availabilityLoading"
        element-loading-text="Loading Availability Data"
        :data="availabilityData.Data"
        :expand-row-keys="availabilityExpandedRows"
        border
        style="width: 100%; margin-top:10px"
        row-key="rowKey"
        :tree-props="{ children: 'Prices' }"
      >
        <el-table-column prop="VendorName" label="Distributor" width="250" sortable>
          <template slot-scope="{ row }">
            <template v-if="row.VendorId">
              <router-link :to="'/vendor/view/' + row.VendorId" class="link-type">
                <span>{{ row.VendorName }}</span>
              </router-link>
            </template>
            <template v-else>
              <span>{{ row.VendorName }}</span>
            </template>
          </template>
        </el-table-column>
        <el-table-column prop="ManufacturerPartNumber" label="Part Number" sortable />
        <el-table-column prop="SKU" label="SKU" sortable>
          <template slot-scope="{ row }">
            <span>{{ row.SKU }}</span>
            <template v-if="row.SKU">
              <a :href="row.URL" target="blank" class="link-type">
                <el-button type="primary" icon="el-icon-shopping-cart-full" style="float: right;" size="mini">Buy</el-button>
              </a>
            </template>
          </template>
        </el-table-column>
        <el-table-column prop="Stock" label="Stock" width="100" sortable />
        <el-table-column
          prop="MinimumOrderQuantity"
          label="MOQ"
          width="100"
          sortable
        />
        <el-table-column prop="LeadTime" label="LeadTime" width="120" sortable />
        <el-table-column prop="Price" label="Price" width="120" sortable />
        <el-table-column prop="Quantity" label="Quantity" width="120" sortable />
        <el-table-column prop="Currency" label="Currency" width="120" sortable />
      </el-table>
    </template>
  </div>
</template>

<script>
import permission from '@/directive/permission/index.js'

import ProductionPart from '@/api/productionPart'
const productionPart = new ProductionPart()

export default {
  name: 'ProducktionPartAvailability',
  directives: { permission },

  props: {
    productionPartBarcode: { type: String, default: null }
  },
  data() {
    return {
      availabilityData: null,
      availabilityAuthorizedOnly: true,
      availabilityBrokers: false
    }
  },
  mounted() {
  },
  methods: {
    getAvailability() {
      productionPart.availability(this.$props.productionPartBarcode, this.availabilityAuthorizedOnly, this.availabilityBrokers).then(response => {
        this.availabilityData = this.processAvailabilityData(response)
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    processAvailabilityData(data) {
      let rowKey = 1
      data.Data.forEach(element => {
        element.rowKey = String(rowKey)
        rowKey++
        if (element.Prices.length === 0) {
          delete element.Values
        } else if (element.Prices.length === 1) {
          element.Quantity = element.Prices[0].Quantity
          element.Currency = element.Prices[0].Currency
          element.Price = element.Prices[0].Price
          delete element.Prices
        } else {
          let rowKey2 = 1
          element.Prices.forEach(element2 => {
            element2.rowKey = element.rowKey + '.' + String(rowKey2)
            rowKey2++
          })
        }
      })
      return data
    }
  }
}
</script>

<style>
.el-table .warning-cell {
  background: oldlace;
}
.el-table .error-cell {
  background: Lavenderblush;
}
</style>
