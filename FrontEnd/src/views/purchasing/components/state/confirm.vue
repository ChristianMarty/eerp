<template>
  <div class="confirm-container">
    <el-input ref="searchInput" v-model="searchInput" placeholder="SKU Search" @keyup.enter.native="skuSearch()">
      <el-button slot="append" icon="el-icon-search" @click="skuSearch()" />
    </el-input>
    <p />

    <el-table
      ref="itemTable"
      :data="lines"
      border
      style="width: 100%"
      row-key="lineKey"
      :cell-style="{ padding: '0', height: '15px' }"
      :tree-props="{ children: 'Received' }"
      :cell-class-name="tableAnalyzer"
    >
      <el-table-column prop="LineNumber" label="Line" width="70" />
      <el-table-column prop="QuantityOrdered" label="Orderd Qty" width="100" />
      <el-table-column prop="QuantityReceived" label="Received Qty" width="120" />
      <el-table-column prop="AddedStockQuantity" label="Add Stock Qty" width="120" />
      <el-table-column prop="ReceivalDate" label="Receival Date" width="120" />
      <el-table-column prop="ExpectedReceiptDate" label="Expected" width="120" />
      <el-table-column prop="SupplierSku" label="Supplier SKU" width="220" />
      <el-table-column label="Item">
        <template slot-scope="{ row }">
          <template v-if="row.LineType === 'Generic'">{{ row.Description }}</template>
          <template v-if="row.LineType === 'Specification Part'">
            {{ row.SpecificationPartRevisionCode	}} - {{ row.Description }}
          </template>
          <template v-if="row.LineType === 'Part'">
            {{ row.PartNo }} - {{ row.ManufacturerName }} -
            <template>
              <template v-if="row.ManufacturerPartId !== null">
                <router-link
                  v-if="row.ManufacturerPartNumberId !== null"
                  :to="'/manufacturerPart/partNumber/item/' + row.ManufacturerPartNumberId"
                  class="link-type"
                >
                  <span>{{ row.ManufacturerPartNumber }}</span>
                </router-link>
              </template>
              <template v-else>
                {{ row.ManufacturerPartNumber }}
              </template>
            </template>
            - {{ row.Description }}
          </template>

        </template>
      </el-table-column>

      <el-table-column width="100">
        <template slot-scope="{ row }">
          <el-button
            type="text"
            size="mini"
            @click="openViewLineItemDialog(row)"
          >Details</el-button>
        </template>
      </el-table-column>

      <el-table-column width="150px">
        <template slot-scope="{ row }">
          <el-button
            v-if="
              parseInt(row.QuantityOrdered, 10) >
                parseInt(row.QuantityReceived, 10)
            "
            v-permission="['purchasing.confirm']"
            type="text"
            size="mini"
            @click="openConfirmDialog(row)"
          >Confirm
          </el-button>

          <el-button
            v-if="
              row.ReceivalId != null && row.StockPart === true
            "
            v-permission="['stock.create']"
            style="float: right;"
            type="text"
            size="mini"
            @click="openAddStockDialog(row.ReceivalId)"
          >Add Stock</el-button>
          <el-button
            v-if="
              row.ReceivalId != null
            "
            type="text"
            size="mini"
            @click="openTrackDialog(row)"
          >Track</el-button>
        </template>
      </el-table-column>
    </el-table>

    <viewLineItemDialog :visible.sync="showLineDialog" :line="viewLine" />
    <confirmDialog :visible.sync="confirmDialogVisible" :line="viewLine" :date="defaultReceivalDate" @close="onCloseConfirmDialog" />
    <addToStock :visible.sync="addToStockDialogVisible" :receival-data="rowReceivalData" />
    <trackDialog :visible.sync="trackDialogVisible" :receival-id="rowReceivalId" />
  </div>
</template>

<script>
import Purchase from '@/api/purchase'
const purchase = new Purchase()

import addToStock from '../addToStockDialog.vue'
import trackDialog from '../trackDialog.vue'
import permission from '@/directive/permission'
import viewLineItemDialog from '../viewLineItemDialog.vue'
import confirmDialog from '../confirmDialog.vue'

export default {
  components: { addToStock, trackDialog, viewLineItemDialog, confirmDialog },
  directives: { permission },
  props: { orderData: { type: Object, default: null }},
  data() {
    return {
      lines: null,

      addToStockDialogVisible: false,
      trackDialogVisible: false,
      confirmDialogVisible: false,

      rowReceivalId: 0,
      searchInput: '',
      rowReceivalData: {},

      defaultReceivalDate: null,

      showLineDialog: false,
      viewLine: {}
    }
  },
  created() {
    this.getOrderLines()
  },
  mounted() {
    this.$refs.searchInput.focus()
  },
  methods: {
    tableAnalyzer({ row, column, rowIndex, columnIndex }) {
      if (columnIndex === 2) {
        if (row.QuantityReceived >= row.QuantityOrdered) {
          return 'success-row'
        } else {
          return 'warning-row'
        }
      } else if (columnIndex === 3) {
        if (row.AddedStockQuantity >= row.QuantityOrdered) {
          return 'success-row'
        }
      } else {
        return ''
      }
    },
    openViewLineItemDialog(lineData) {
      this.showLineDialog = true
      this.viewLine = lineData
    },
    openConfirmDialog(lineData) {
      this.confirmDialogVisible = true
      this.viewLine = lineData
    },
    onCloseConfirmDialog(receivalId, date, addToStock = false) {
      this.defaultReceivalDate = date
      if (addToStock === true) {
        this.openAddStockDialog(receivalId)
      }
      this.getOrderLines()
    },
    openAddStockDialog(receivalId) {
      purchase.item.receive.get(receivalId).then(response => {
        if (response.SupplierPartId === 0) {
          this.$message({
            type: 'error',
            message: 'The item is not matched with the database!'
          })
        } else {
          this.rowReceivalData = response
          this.addToStockDialogVisible = true
        }
      })
    },
    openTrackDialog(dialogData) {
      this.rowReceivalId = dialogData.ReceivalId
      this.trackDialogVisible = true
    },
    getOrderLines() {
      purchase.item.search(this.$props.orderData.ItemCode).then(response => {
        this.lines = response.Lines
        this.prepairLines(this.lines)
      })
    },
    prepairLines(data) {
      data.forEach(line => {
        line.lineKey = line.LineNumber

        if ('Received' in line) {
          if (line.Received.length === 1) {
            line.ReceivalDate = line.Received[0].ReceivalDate
            line.ReceivalId = line.Received[0].ReceivalId
            line.AddedStockQuantity = line.Received[0].AddedStockQuantity
            delete line.Received
          } else {
            let i = 0
            line.Received.forEach(subLine => {
              i++
              subLine.lineKey = line.lineKey + '.' + i
              subLine.StockPart = line.StockPart
              subLine.LineType = line.LineType
            })
          }
        }
      })
    },
    skuSearch() {
      const line = this.lines.find(element => element.SupplierSku.trim() === this.searchInput.trim())

      if (line === undefined) {
        this.$message({
          type: 'warning',
          message: 'SKU not found'
        })
      } else {
        if (parseInt(line.QuantityOrdered, 10) > parseInt(line.QuantityReceived, 10)) {
          this.openConfirmDialog(line)
        } else {
          this.$message({
            type: 'warning',
            message: 'SKU already confirmed'
          })
        }
      }

      this.searchInput = ''
      this.$refs.searchInput.focus()
    }
  }
}
</script>

<style>
.el-table .warning-row {
  background: oldlace;
}
.el-table .error-row {
  background: Lavenderblush;
}

.el-table .success-row {
  background: Honeydew;
}

</style>
