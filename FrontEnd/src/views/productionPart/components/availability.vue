<template>
  <div class="production_part-availability-container">

    <el-checkbox v-model="availabilityAuthorizedOnly">Authorized Only</el-checkbox>
    <el-checkbox v-model="availabilityBrokers">Include Brokers</el-checkbox>
    <el-button type="primary" @click="getAvailability">Load Data</el-button>
    <template v-if="availabilityData != null">
      <p>
        Data provided by Octopart, {{ availabilityDataRaw.Timestamp }}
      </p>
      <el-checkbox v-model="flat" @change="processAvailabilityData()">Flat View</el-checkbox> <br>
      <template v-if="flat">
        <el-checkbox v-model="knownSuppliers" @change="processAvailabilityData()">Known Suppliers Only</el-checkbox> <br>
        <p>
          Minimum Quantity: <el-input-number v-model="minimumQuantity" :min="1" :max="1000000" @change="processAvailabilityData()" />
          Maximum Quantity: <el-input-number v-model="maximumQuantity" :min="1" :max="1000000" @change="processAvailabilityData()" />
        </p>
      </template>
      <el-table
        v-loading="availabilityLoading"
        element-loading-text="Loading Availability Data"
        :data="availabilityData"
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
        <el-table-column v-if="flat == false" prop="Quantity" label="Quantity" width="120" sortable />
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
  name: 'ProductionPartAvailability',
  directives: { permission },

  props: {
    productionPartBarcode: { type: String, default: null }
  },
  data() {
    return {
      availabilityData: null,
      availabilityDataRaw: null,
      availabilityAuthorizedOnly: true,
      availabilityBrokers: false,
      flat: true,
      knownSuppliers: true,
      minimumQuantity: 0,
      maximumQuantity: 1000000
    }
  },
  mounted() {
  },
  methods: {
    processAvailabilityData() {
      const temp = structuredClone(this.availabilityDataRaw)
      if (this.flat === true) this.availabilityData = this.processAvailabilityDataFlat(temp)
      else this.availabilityData = this.processAvailabilityDataNotFlat(temp)
    },
    getAvailability() {
      productionPart.availability(this.$props.productionPartBarcode, this.availabilityAuthorizedOnly, this.availabilityBrokers).then(response => {
        this.availabilityDataRaw = response
        this.processAvailabilityData()
      })
    },
    processAvailabilityDataNotFlat(data) {
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
      return data.Data
    },
    processAvailabilityDataFlat(data) {
      let rowKey = 1
      const output = []
      data.Data.forEach(element => {
        if (element.Prices.length !== 0) {
          element.Prices.forEach(element2 => {
            rowKey++
            element.rowKey = String(rowKey)
            element.MinimumOrderQuantity = element2.Quantity
            if (
              (!this.knownSuppliers || (this.knownSuppliers && element.VendorId !== null)) &&
              (this.minimumQuantity <= element.MinimumOrderQuantity && element.MinimumOrderQuantity <= this.maximumQuantity)
            ) {
              element.Currency = element2.Currency
              element.Price = element2.Price
              const temp = structuredClone(element)
              delete temp.Prices
              output.push(temp)
            }
          })
        }
      })
      return output
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
