<template>
  <div class="availability-container">

    <p><b>Stock Availability:</b><el-progress :percentage="stockBom.StockItemsAvailability" /></p>
    <p><b>Number Of Unique Components:</b> {{ stockBom.NumberOfUniqueComponents }}</p>
    <p><b>Total Number Of Components:</b> {{ stockBom.TotalNumberOfComponents }}</p>
    <el-table
      :data="stockBom.Bom"
      :cell-style="{ padding: '0', height: '15px' }"
      style="width: 100%"
      :row-class-name="tableAnalyzer"
    >
      <el-table-column prop="ProductionPartNumber" label="Part No" width="100" sortable>
        <template slot-scope="{ row }">
          <router-link
            :to="'/prodParts/prodPartView/' + row.ProductionPartNumber"
            class="link-type"
          >
            <span>{{ row.ProductionPartNumber }}</span>
          </router-link>
        </template>
      </el-table-column>¨
      <el-table-column prop="Description" label="Description" sortable />
      <el-table-column prop="Quantity" label="Quantity" width="150" sortable />
      <el-table-column prop="StockQuantity" label="On Stock" width="150" sortable />
      <el-table-column prop="Availability" label="Availability" width="300" sortable>
        <template slot-scope="{ row }">
          <el-progress :percentage="row.Availability" />
        </template>
      </el-table-column>
      <el-table-column prop="StockCertainty" label="Stock Certainty" width="150" sortable>
        <template slot-scope="{ row }">
          <el-rate
            v-model="row.StockCertaintyFactor*5"
            disabled
          />
        </template>
      </el-table-column>
    </el-table>
  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'

export default {
  props: { revisionId: { type: Number, default: 0 }},
  data() {
    return {
      stockBom: null
    }
  },
  mounted() {
    this.getBomStock()
  },
  methods: {
    getBomStock() {
      requestBN({
        url: '/billOfMaterial/availability',
        methood: 'get',
        params: {
          RevisionId: this.$props.revisionId
        }
      }).then(response => {
        this.stockBom = response.data
      })
    }
  }
}
</script>
