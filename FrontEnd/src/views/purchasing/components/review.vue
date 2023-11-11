<template>
  <div class="placerd-container">

    <h2>Items:</h2>
    <span>
      <el-button v-permission="['purchasing.edit']" type="primary" @click="createMatch()">Match</el-button>
      <el-button v-permission="['purchasing.edit']" type="info" @click="getOrderLines()">Reload</el-button>
    </span>
    <p />
    <h3>Manufacturer Parts:</h3>
    <el-table
      ref="itemTable"
      :key="tableKey"
      v-loading="loading"
      element-loading-text="Loading Order Lines"
      :data="matchedData.Lines"
      border
      :cell-style="{ padding: '0', height: '20px' }"
      style="width: 100%"
      :cell-class-name="tableAnalyzer"
    >
      <el-table-column prop="LineNo" label="Line" width="70" />
      <el-table-column prop="SupplierPartNumber" label="Supplier Part Number" width="220" />
      <el-table-column prop="ManufacturerName" label="Manufacturer" width="200">
        <template slot-scope="{ row }">
          <router-link
            v-if="row.ManufacturerId !== null"
            :to="'/vendor/view/' + row.ManufacturerId"
            class="link-type"
          >
            <span>{{ row.ManufacturerName }}</span>
          </router-link>
          <span v-else>{{ row.ManufacturerName }}</span>
        </template>
      </el-table-column>
      <el-table-column prop="ManufacturerPartNumber" label="Manufacturer Part Number" width="220">
        <template slot-scope="{ row }">
          <router-link
            v-if="row.ManufacturerPartNumberId !== null"
            :to="'/manufacturerPart/partNumber/item/' + row.ManufacturerPartNumberId"
            class="link-type"
          >
            <span>{{ row.ManufacturerPartNumber }}</span>
          </router-link>
          <span v-else>{{ row.ManufacturerPartNumber }}</span>
        </template>
      </el-table-column>
      <el-table-column prop="Description" label="Description" />
    </el-table>
    <h3>Specification Parts:</h3>
    <el-table
      :key="tableKey"
      v-loading="loading"
      element-loading-text="Loading Order Lines"
      :data="specificationPartLines"
      border
      :cell-style="{ padding: '0', height: '20px' }"
      style="width: 100%"
    >
      <el-table-column prop="LineNo" label="Line" width="70" />
      <el-table-column prop="Description" label="Description" />
    </el-table>
    <h3>Generic:</h3>
    <el-table
      :key="tableKey"
      v-loading="loading"
      element-loading-text="Loading Order Lines"
      :data="genericLines"
      border
      :cell-style="{ padding: '0', height: '20px' }"
      style="width: 100%"
    >
      <el-table-column prop="LineNo" label="Line" width="70" />
      <el-table-column prop="SupplierPartNumber" label="Supplier Part Number" width="220" />
      <el-table-column prop="ManufacturerName" label="Manufacturer" width="200" />
      <el-table-column prop="ManufacturerPartNumber" label="Manufacturer Part Number" width="220" />
      <el-table-column prop="Description" label="Description" />
    </el-table>
  </div>
</template>

<script>
import permission from '@/directive/permission/index.js'

import Purchase from '@/api/purchase'
const purchase = new Purchase()

export default {
  name: 'PurchaseOrderReview',
  directives: { permission },
  props: {
    orderData: { type: Object, default: null }
  },
  data() {
    return {
      matchedData: {},
      genericLines: [],
      specificationPartLines: [],

      SupplierOrderNumber: '',
      poData: {},
      matchData: null,
      matchDialogVisible: false,
      loading: true
    }
  },
  mounted() {
    this.getMatchLines()
    this.getOrderLines()
  },
  methods: {
    getOrderLines() {
      purchase.item.search(this.$props.orderData.PurchaseOrderNumber).then(response => {
        response.Lines.forEach(element => {
          if (element.LineType === 'Generic') {
            this.genericLines.push(element)
          } else if (element.LineType === 'Specification Part') {
            this.specificationPartLines.push(element)
          }
        })
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    getMatchLines() {
      this.loading = true
      purchase.item.match.get(this.$props.orderData.PurchaseOrderNumber).then(response => {
        this.matchedData = response
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
    createMatch() {
      this.loading = true
      const LineIdList = []
      this.matchedData.Lines.forEach(element => {
        LineIdList.push(element.OrderLineId)
      })

      purchase.item.match.create(
        this.$props.orderData.PurchaseOrderNumber,
        LineIdList
      ).then(response => {
        this.getMatchLines()
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    tableAnalyzer({ row, column, rowIndex, columnIndex }) {
      if (row.SupplierPartId === null && columnIndex === 1) return 'error-cell'
      if (row.ManufacturerId === null && columnIndex === 2) return 'error-cell'
      if (row.ManufacturerPartNumberId === null && columnIndex === 3) return 'error-cell'
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
