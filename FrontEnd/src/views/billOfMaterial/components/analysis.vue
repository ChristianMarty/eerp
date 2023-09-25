<template>
  <div class="analysis-container">

    <p><b>Number Of Unique Components:</b> {{ stockBom.NumberOfUniqueComponents }}</p>
    <p><b>Total Number Of Components:</b> {{ stockBom.TotalNumberOfComponents }}</p>
    <p><b>Total Average Purchase Price:</b> {{ stockBom.Cost.TotalAveragePurchasePrice }}</p>
    <el-table
      :data="stockBom.Bom"
      :cell-style="{ padding: '0', height: '15px' }"
      style="width: 100%"
    >
      <el-table-column prop="ProductionPartNumber" label="Part No" width="120" sortable>
        <template slot-scope="{ row }">
          <router-link
            :to="'/productionPart/item/' + row.ProductionPartNumber"
            class="link-type"
          >
            <span>{{ row.ProductionPartNumber }}</span>
          </router-link>
        </template>
      </el-table-column>¨
      <el-table-column prop="Description" label="Description" sortable />
      <el-table-column prop="Quantity" label="Quantity" width="150" sortable />
      <el-table-column prop="PurchasePrice.Average" label="Avg Purchase Price" width="200" sortable />
      <el-table-column prop="NumberOfManufacturers" label="Manufacturers" width="150" sortable />
      <el-table-column prop="NumberOfParts" label="Parts" width="150" sortable />
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
      stockBom: null
    }
  },
  mounted() {
    this.getData()
  },
  methods: {
    getData() {
      billOfMaterial.item.analysis(this.$props.revisionId).then(response => {
        this.stockBom = response
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
