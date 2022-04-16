<template>
  <div class="placerd-container">
    <el-button
      v-permission="['purchasing.edit']"
      type="primary"
      @click="match()"
    >Match parts against Database</el-button>
    <p />
    <el-table
      ref="itemTable"
      :key="tableKey"
      :data="lines"
      border
      :cell-style="{ padding: '0', height: '20px' }"
      style="width: 100%"
      :summary-method="calcSum"
      show-summary
    >
      <el-table-column prop="LineNo" label="Line" width="70" />
      <el-table-column prop="QuantityOrderd" label="Quantity" width="120" />
      <el-table-column prop="SupplierSku" label="SKU" width="220" />

      <el-table-column label="Item">
        <template slot-scope="{ row }">
          <template v-if="row.Type == 'Generic'">{{ row.Description }}</template>

          <template v-if="row.Type == 'Part'">
            {{ row.PartNo }} - {{ row.ManufacturerName }} -
            {{ row.ManufacturerPartNumber }} - {{ row.Description }}
          </template>
        </template>
      </el-table-column>
      <el-table-column label="Date" prop="ExpectedReceiptDate" width="100" />
      <el-table-column prop="Price" label="Price" width="120" />

      <el-table-column label="Total" width="120">
        <template slot-scope="{ row }">
          <span>
            {{
              Math.round(row.QuantityOrderd * row.Price * 100000) / 100000
            }}
          </span>
        </template>
      </el-table-column>
    </el-table>

    <el-dialog title="Match Parts" :visible.sync="matchDialogVisible" width="80%">
      <el-table
        v-if="matchData !== null"
        :key="LineNo"
        :data="matchData.Lines"
        border
        :cell-style="{ padding: '0', height: '15px' }"
        style="width: 100%"
        :cell-class-name="tableAnalyzer"
      >
        <el-table-column prop="LineNo" label="Line" width="70" />
        <el-table-column prop="ManufacturerName" label="Manufacturer" width="200" />

        <el-table-column prop="ManufacturerPartNumber" label="Manufacturer Part Number" width="220">
          <template slot-scope="{ row }">
            <router-link
              v-if="row.ManufacturerPartId !== null"
              :to="'/mfrParts/partView/' + row.ManufacturerPartId"
              class="link-type"
            >
              <span>{{ row.ManufacturerPartNumber }}</span>
            </router-link>
            <span v-else>{{ row.ManufacturerPartNumber }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="Sku" label="Supplier Part Number" width="220" />
      </el-table>
      <span>
        <el-button type="primary" @click="createMatch()">Create</el-button>
        <el-button type="primary" @click="match()">Reload</el-button>
        <el-button type="primary" @click="saveMatch()">Save</el-button>
      </span>
    </el-dialog>
  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'
import permission from '@/directive/permission/index.js'

export default {
  directives: { permission },
  props: { orderData: { type: Object, default: null }},
  data() {
    return {
      orderData: this.$props.orderData,
      SupplierOrderNumber: '',
      lines: null,
      matchData: null,
      matchDialogVisible: false
    }
  },
  mounted() {
    this.getOrderLines()
  },
  methods: {
    getOrderLines() {
      requestBN({
        url: '/purchasing/item',
        methood: 'get',
        params: {
          PurchaseOrderNo: this.$props.orderData.PoNo
        }
      }).then(response => {
        this.lines = response.data.Lines
      })
    },
    match() {
      requestBN({
        url: '/purchasing/item/match',
        methood: 'get',
        params: {
          PurchaseOrderNo: this.$props.orderData.PoNo
        }
      }).then(response => {
        this.matchData = response.data
        this.matchDialogVisible = true
      })
    },
    saveMatch() {
      requestBN({
        method: 'post',
        url: '/purchasing/item/match',
        data: {
          PurchaseOrderNo: this.$props.orderData.PoNo,
          Command: 'Save'
        }
      }).then(response => {

      })
    },
    createMatch() {
      requestBN({
        method: 'post',
        url: '/purchasing/item/match',
        data: {
          PurchaseOrderNo: this.$props.orderData.PoNo,
          Command: 'Create'
        }
      }).then(response => {

      })
    },
    tableAnalyzer({ row, column, rowIndex, columnIndex }) {
      if (row.PartManufacturerId === null && columnIndex === 1) return 'error-cell'
      if (row.ManufacturerPartId === null && columnIndex === 2) return 'error-cell'
      if (row.SupplierPartId === null && columnIndex === 3) return 'error-cell'
    },
    calcSum(param) {
      let total = 0
      this.lines.forEach(element => {
        const line = element.QuantityOrderd * element.Price
        total += line
      })

      const totalLine = []
      totalLine[0] = 'Total'
      totalLine[5] = Math.round(total * 100000) / 100000
      return totalLine
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
