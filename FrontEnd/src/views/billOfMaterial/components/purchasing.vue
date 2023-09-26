<template>
  <div class="purchasing-container">
    <p />
    <el-checkbox v-model="authorizedOnly">Authorized Only</el-checkbox>
    <el-checkbox v-model="brokers">Include Brokers</el-checkbox>
    <el-checkbox v-model="noStock">Include No Stock</el-checkbox>
    <el-checkbox v-model="knownSuppliers">Known Suppliers Only</el-checkbox>
    <p>Quantity: <el-input-number v-model="quantity" :min="1" :max="1000000" /></p>
    <el-button type="primary" @click="getData()">Load Data</el-button>

    <el-table
      v-loading="loading"
      element-loading-text="Loading purchasing information"
      :data="bom"
      height="90vh"
      border
      style="width: 100%"
      :cell-class-name="tableAnalyzer"
      :header-cell-class-name="tableAnalyzer"
    >
      <el-table-column prop="ProductionPartNumber" label="Part No" width="120" sortable fixed>
        <template slot-scope="{ row }">
          <router-link
            :to="'/productionPart/item/' + row.ProductionPartNumber"
            class="link-type"
          >
            <span>{{ row.ProductionPartNumber }}</span>
          </router-link>
        </template>
      </el-table-column>
      <el-table-column prop="Description" label="Description" sortable fixed />
      <el-table-column prop="TotalQuantity" label="Total Quantity" width="150" sortable fixed />
      <el-table-column prop="ManufacturerName" label="Manufacturer" width="150" sortable fixed />
      <el-table-column prop="ManufacturerPartNumber" label="Part Number" width="150" sortable fixed />
      <el-table-column prop="CheapestSupplier" label="Cheapest" width="150" sortable fixed />
      <el-table-column prop="CheapestPrice" label="Price" width="150" sortable fixed />
      <el-table-column
        v-for="supplier in suppliers"
        :key="supplier"
        :label="supplier"
        :prop="supplier"
        sortable
      />

    </el-table>
  </div>
</template>

<script>
import BillOfMaterial from '@/api/billOfMaterial'
const billOfMaterial = new BillOfMaterial()

export default {
  props: { revisionId: { type: Number, default: 0 }},
  data() {
    return {
      quantity: 1,
      authorizedOnly: true,
      brokers: false,
      noStock: false,
      knownSuppliers: true,

      loading: false,
      bom: null,
      suppliers: []
    }
  },
  mounted() {
  },
  methods: {
    getData() {
      this.loading = true
      billOfMaterial.item.purchasing(this.$props.revisionId, this.quantity, this.noStock, this.knownSuppliers, this.authorizedOnly, this.brokers).then(response => {
        this.bom = this.processDataFlat(response)
        this.loading = false
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    processDataFlat(data) {
      const output = []
      data.forEach(element => {
        const temp = {}
        temp.ProductionPartNumber = element.ProductionPartNumber
        temp.Description = element.Description
        temp.ManufacturerName = element.ManufacturerName
        temp.ManufacturerPartNumber = element.ManufacturerPartNumber
        temp.TotalQuantity = element.TotalQuantity
        temp.CheapestPrice = element.CheapestPrice
        temp.CheapestSupplier = element.CheapestSupplier

        element.Data.forEach(supplier => {
          if (this.suppliers.indexOf(supplier.VendorName) === -1) {
            this.suppliers.push(supplier.VendorName)
            this.suppliers.push(supplier.VendorName + ' SKU')
            this.suppliers.push(supplier.VendorName + ' Stock')
          }

          temp[supplier.VendorName] = supplier.Prices[0].Price
          temp[supplier.VendorName + ' SKU'] = supplier.SKU
          temp[supplier.VendorName + ' Stock'] = supplier.Stock
        })
        output.push(temp)
      })

      return output
    },
    tableAnalyzer({ row, column, rowIndex, columnIndex }) {
      if (columnIndex === 5) return 'supplier-border'
      else if (columnIndex === 7) return 'supplier-border'
      else if (columnIndex > 7 && (columnIndex - 7) % 3 === 0) return 'supplier-border'
    }
  }
}
</script>

<style>
.el-table .supplier-border {
  border-left: 1px solid black;
}
</style>
