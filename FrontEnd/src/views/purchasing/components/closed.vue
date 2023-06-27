<template>
  <div class="placerd-container">
    <h2>Items:</h2>
    <el-table
      ref="itemTable"
      :data="lines"
      border
      style="width: 100%"
      row-key="lineKey"
      :cell-style="{ padding: '0', height: '30px' }"
      :tree-props="{ children: 'Received' }"
      @cell-click="(row, column, cell, event) =>openViewLineItemDialog(row, column, cell, event)"
    >
      <el-table-column prop="LineNo" label="Line" width="80" sortable />
      <el-table-column prop="QuantityOrderd" label="Orderd Qty" width="140" sortable />
      <el-table-column
        prop="QuantityReceived"
        label="Received Qty"
        width="140"
        sortable
      />
      <el-table-column prop="ReceivalDate" label="Receival Date" width="150" sortable />
      <el-table-column prop="ExpectedReceiptDate" label="Expected" width="150" sortable />
      <el-table-column prop="SupplierSku" label="Supplier SKU" width="220" sortable />

      <el-table-column label="Item">
        <template slot-scope="{ row }">
          <template v-if="row.LineType == 'Generic'">
            {{ row.Description }}
          </template>

          <template v-if="row.LineType == 'Part'">
            {{ row.PartNo }} - {{ row.ManufacturerName }} -
            {{ row.ManufacturerPartNumber }} - {{ row.Description }}
          </template>
        </template>
      </el-table-column>
      <el-table-column prop="LinePrice" label="Price" width="100" />
      <el-table-column prop="VatValue" label="VAT" width="100" />
      <el-table-column prop="Discount" label="Discount" width="100" />
      <el-table-column prop="Total" label="Total" width="100" />

      <el-table-column width="100">
        <template slot-scope="{ row }">
          <el-button
            v-if="row.ReceivalId"
            type="text"
            size="mini"
          >Details</el-button>
        </template>
      </el-table-column>
    </el-table>

    <h2>Additional Charges:</h2>

    <el-table
      ref="additionalChargesTable"
      row-key="AdditionalChargesLineNo"
      :data="additionalCharges"
      border
      :cell-style="{ padding: '0', height: '20px' }"
      style="width: 100%"
    >
      <el-table-column prop="LineNo" label="Line" width="70" />
      <el-table-column prop="Type" label="Type" width="100" />
      <el-table-column prop="Quantity" label="Quantity" width="80" />
      <el-table-column prop="Description" label="Description" />
      <el-table-column prop="Price" label="Price" width="100" />
      <el-table-column prop="VatValue" label="VAT" width="100" />
      <el-table-column prop="Total" label="Total" width="100" />
    </el-table>

    <el-divider />

    <orderTotal :total="total" />
    <viewLineItemDialog :visible.sync="showLineDialog" :line="viewLine" />

  </div>
</template>

<script>
import Purchase from '@/api/purchase'
const purchase = new Purchase()

import orderTotal from './orderTotal'

import viewLineItemDialog from './viewLineItemDialog'

export default {
  components: { orderTotal, viewLineItemDialog },
  props: { orderData: { type: Object, default: null }},
  data() {
    return {
      lines: [],
      additionalCharges: [],
      total: {},
      showDialog: false,
      trackDialogReceivalId: 0,
      showLineDialog: false,
      viewLine: {}
    }
  },
  created() {
    this.getOrderLines()
  },
  mounted() {},
  methods: {
    openViewLineItemDialog(row, column, cell, event) {
      this.showLineDialog = true
      this.viewLine = row
    },
    getOrderLines() {
      purchase.item.search(this.$props.orderData.PoNo).then(response => {
        this.lines = response.Lines
        this.total = response.Total
        this.additionalCharges = response.AdditionalCharges
        this.prepairLines(this.lines)
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    prepairLines(data) {
      data.forEach(line => {
        line.lineKey = line.LineNo
        line.Total = Math.round(line.QuantityOrdered * line.Price * 100000) / 100000

        if ('Received' in line) {
          if (line.Received.length === 1) {
            line.ReceivalDate = line.Received[0].ReceivalDate
            line.ReceivalId = line.Received[0].ReceivalId
            delete line.Received
          } else {
            let i = 0
            line.Received.forEach(subLine => {
              i++
              subLine.lineKey = line.lineKey + '.' + i
            })
          }
        }
      })
    }
  }
}
</script>
