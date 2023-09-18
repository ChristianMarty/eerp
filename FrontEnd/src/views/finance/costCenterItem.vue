<template>
  <div class="app-container">
    <template>
      <h2>{{ data.Barcode }} - {{ data.Name }}</h2>
      <el-table
        :data="data.PurchaseItem"
        style="width: 100%"
        :cell-style="{ padding: '0', height: '15px' }"
        border
      >
        <el-table-column prop="PurchaseOrderBarcode" label="Po No" width="140" sortable>
          <template slot-scope="{ row }">
            <router-link
              :to="'/purchasing/edit/' + row.PurchaseOrderBarcode"
              class="link-type"
            >
              <span>{{ row.PurchaseOrderBarcode }}</span>
            </router-link>
          </template>
        </el-table-column>
        <el-table-column label="Supplier" prop="Supplier" sortable />
        <el-table-column label="Manufacturer" prop="ManufacturerName" sortable />
        <el-table-column label="Part Number" prop="ManufacturerPartNumber" sortable />
        <el-table-column label="Production Part Number" prop="ProductionPartNumber" sortable />
        <el-table-column label="Order Reference" prop="OrderReference" sortable />
        <el-table-column label="Description" prop="Description" sortable />
        <el-table-column label="Quantity" prop="Quantity" width="110" sortable />
        <el-table-column label="Price" prop="Price" width="100" sortable />
        <el-table-column label="Total" prop="LineTotal" width="100" sortable />
        <el-table-column label="Currency" prop="Currency" width="110" sortable />
        <el-table-column label="Exchange" prop="ExchangeRate" width="120" sortable />
        <el-table-column label="Purchase Date" prop="PurchaseDate" width="150" sortable />
        <el-table-column label="Expected Date" prop="ExpectedReceiptDate" width="150" sortable />
      </el-table>
    </template>
  </div>
</template>

<script>
import Finance from '@/api/finance'
const finance = new Finance()

export default {
  name: 'CostCenterBrowser',
  components: {},
  data() {
    return {
      loading: true,
      data: null
    }
  },
  mounted() {
    this.getData()
  },
  methods: {
    getData() {
      finance.costCenter.item(this.$route.params.CostCenterNumber).then(response => {
        this.data = response
        this.loading = false
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
