<template>
  <div class="app-container">
    <h1>Stock Notification</h1>
    <el-table
      :data="partData"
      :default-sort="{ prop: 'Package', order: 'descending' }"
      height="80vh"
      border
      style="width: 100%"
      :row-class-name="tableAnalyzer"
    >
      <el-table-column prop="ProductionPartNumber" sortable label="Part No" width="120">
        <template slot-scope="{ row }">
          <router-link
            :to="'/prodParts/prodPartView/' + row.ProductionPartNumber"
            class="link-type"
          >
            <span>{{ row.ProductionPartNumber }}</span>
          </router-link>
        </template>
      </el-table-column>
      <el-table-column prop="Description" label="Description" sortable />
      <el-table-column prop="StockQuantity" label="Quantity" sortable width="120" />
      <el-table-column prop="StockMinimum" label="Minimum" sortable width="120" />
      <el-table-column prop="StockWarning" label="Warning" sortable width="120" />
      <el-table-column prop="StockMaximum" label="Maximum" sortable width="120" />

    </el-table>
  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'

export default {
  name: 'ProdPartBrowser',
  data() {
    return {
      partData: null,
      ProdPartNoFilter: ''
    }
  },
  mounted() {
    this.getPartNotificationData()
  },
  methods: {
    tableAnalyzer({ row, rowIndex }) {
      if (row.Status === 'Warning') {
        return 'warning-row'
      } else if (row.Status === 'Minimum') {
        return 'minimum-row'
      } else if (row.Status === 'Maximum') {
        return 'maximum-row'
      }
      return ''
    },
    getPartNotificationData() {
      requestBN({
        url: '/productionPart/notification',
        methood: 'get'
      }).then(response => {
        this.partData = response.data
      })
    }
  }
}
</script>

<style>
.el-table .warning-row {
  background: oldlace;
}
.el-table .minimum-row {
  background: Lavenderblush;
}
.el-table .maximum-row {
  background: AliceBlue;
}
</style>
