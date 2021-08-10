<template>
  <div class="placerd-container">

    <p><b>Stock Availability:</b><el-progress :percentage="stockBom.StockItemsAvailability" /></p>
    <p><b>Total Number Of Components:</b> {{ stockBom.TotalNumberOfComponents }}</p>
    <el-table
      :data="stockBom.Bom"
      :cell-style="{ padding: '0', height: '15px' }"
      style="width: 100%"
      :row-class-name="tableAnalyzer"
    >
      <el-table-column prop="ProductionPartNo" label="Part No" sortable>
        <template slot-scope="{ row }">
          <router-link
            :to="'/prodParts/prodPartView/' + row.ProductionPartNo"
            class="link-type"
          >
            <span>{{ row.ProductionPartNo }}</span>
          </router-link>
        </template>
      </el-table-column>
      <el-table-column prop="Quantity" label="Quantity" width="150" sortable />
      <el-table-column prop="StockQuantity" label="On Stock" width="150" sortable />
      <el-table-column prop="Availability" label="Availability" width="300" sortable>
        <template slot-scope="{ row }">
          <el-progress :percentage="row.Availability" />
        </template>
      </el-table-column>
    </el-table>
  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'

export default {
  props: { projectNo: { type: Number, default: 0 }},
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
        url: '/project/stock',
        methood: 'get',
        params: {
          ProjectId: this.$props.projectNo
        }
      }).then(response => {
        this.stockBom = response.data
      })
    }
  }
}
</script>
