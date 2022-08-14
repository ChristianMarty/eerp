<template>
  <div class="placerd-container">
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
    >
      <el-table-column prop="LineNo" label="Line" width="70" />
      <el-table-column prop="QuantityOrderd" label="Orderd Qty" width="100" />
      <el-table-column prop="QuantityReceived" label="Received Qty" width="120" />
      <el-table-column prop="ReceivalDate" label="Receival Date" width="120" />
      <el-table-column prop="ExpectedReceiptDate" label="Expected" width="120" />
      <el-table-column prop="SupplierSku" label="Supplier SKU" width="220" />
      <el-table-column label="Item">
        <template slot-scope="{ row }">
          <template v-if="row.LineType == 'Generic'">{{ row.Description }}</template>

          <template v-if="row.LineType == 'Part'">
            {{ row.PartNo }} - {{ row.ManufacturerName }} -
            <template>
              <template v-if="row.ManufacturerPartId !== null">
                <router-link
                  :to="'/mfrParts/partView/' + row.ManufacturerPartId"
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

      <el-table-column width="150px">
        <template slot-scope="{ row }">
          <el-button
            v-if="
              parseInt(row.QuantityOrderd, 10) >
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
              row.ReceivalId != NULL && row.LineType == 'Part' && row.StockPart == true
            "
            v-permission="['stock.create']"
            style="float: right;"
            type="text"
            size="mini"
            @click="openAddStockDialog(row.ReceivalId)"
          >Add Stock</el-button>
          <el-button
            v-if="
              row.ReceivalId != NULL
            "
            type="text"
            size="mini"
            @click="openTrackDialog(row)"
          >Track</el-button>
        </template>
      </el-table-column>
    </el-table>

    <el-dialog title="Confirm Item Received" :visible.sync="showConfirmDialog" width="50%" center>

      <el-form size="mini" label-width="220px">
        <el-form-item label="Sku:">{{ receiveDialog.SupplierSku }}</el-form-item>
        <template v-if="receiveDialog.Type == 'Part'">
          <el-form-item label="Production Part No:">{{ receiveDialog.PartNo }}</el-form-item>
          <el-form-item label="Manufacturer Name:">{{ receiveDialog.ManufacturerName }}</el-form-item>
          <el-form-item label="Manufacturer Part Number:">{{ receiveDialog.ManufacturerPartNumber }}</el-form-item>
        </template>
        <el-form-item label="Description:">{{ receiveDialog.Description }}</el-form-item>
        <el-form-item label="Order Reference:">{{ receiveDialog.OrderReference }}</el-form-item>
        <el-form-item label="Note:">{{ receiveDialog.Note }}</el-form-item>

        <el-form-item label="Orderd Quantity:">{{ receiveDialog.QuantityOrderd }}</el-form-item>
        <el-form-item label="Received Quantity:">
          <el-input-number
            v-model="dialogQuantityReceived"
            :min="1"
            :max="receiveDialog.QuantityOrderd - receiveDialog.QuantityReceived"
          >Received Quantety</el-input-number>
        </el-form-item>
        <el-form-item label="Receiving Date:">
          <el-date-picker v-model="dialogDateReceived" type="date" placeholder="Pick a day" value-format="yyyy-MM-dd" />
        </el-form-item>
      </el-form>

      <span slot="footer" class="dialog-footer">
        <el-button v-if="receiveDialog.StockPart == true" type="primary" @click="confirmItem(receiveDialog, true)">Confirm and add to stock</el-button>
        <el-button type="primary" @click="confirmItem(receiveDialog, false)">Confirm</el-button>
        <el-button @click="showConfirmDialog = false">Cancel</el-button>
      </span>
    </el-dialog>

    <addToStock :visible.sync="addToStockDialogVisible" :receival-data="rowReceivalData" />
    <trackDialog :visible.sync="trackDialogVisible" :receival-id="rowReceivalId" />
  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'
import addToStock from './addToStockDialog'
import trackDialog from './trackDialog'
import permission from '@/directive/permission/index.js'

export default {
  components: { addToStock, trackDialog },
  directives: { permission },
  props: { orderData: { type: Object, default: null }},
  data() {
    return {
      lines: null,
      showConfirmDialog: false,
      receiveDialog: false,
      dialogQuantityReceived: null,
      dialogDateReceived: null,
      addToStockDialogVisible: false,
      trackDialogVisible: false,
      rowReceivalId: 0,
      searchInput: '',
      rowReceivalData: {}

    }
  },
  created() {
    this.getOrderLines()
    this.dialogDateReceived = new Date().toISOString().substring(0, 10)
  },
  mounted() {
    this.$refs.searchInput.focus()
  },
  methods: {
    openConfirmDialog(dialogData) {
      this.showConfirmDialog = true
      this.receiveDialog = dialogData
      this.dialogQuantityReceived = (this.receiveDialog.QuantityOrderd - this.receiveDialog.QuantityReceived)
      this.receiveDialog.ReceivedDate = new Date()
    },
    openAddStockDialog(receivalId) {
      requestBN({
        url: 'purchasing/item/received',
        methood: 'get',
        params: {
          ReceivalId: receivalId
        }
      }).then(response => {
        if (response.data.SupplierPartId === 0) {
          this.$message({
            type: 'error',
            message: 'The item is not matched with the database!'
          })
        } else {
          this.rowReceivalData = response.data
          this.addToStockDialogVisible = true
        }
      })
    },
    openTrackDialog(dialogData) {
      this.rowReceivalId = dialogData.ReceivalId
      this.trackDialogVisible = true
    },
    getOrderLines() {
      requestBN({
        url: '/purchasing/item',
        methood: 'get',
        params: {
          PurchaseOrderNo: this.$props.orderData.PoNo
        }
      }).then(response => {
        this.lines = response.data.Lines
        this.prepairLines(this.lines)
      })
    },
    prepairLines(data) {
      data.forEach(line => {
        line.lineKey = line.LineNo

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
              subLine.StockPart = line.StockPart
              subLine.LineType = line.LineType
            })
          }
        }
      })
    },
    confirmItem(received, addToStock = false) {
      const now = new Date()
      const receivedDate = new Date(this.dialogDateReceived)

      if (receivedDate > now) {
        this.$confirm('The selected date is in the future. Are you sure?', 'Warning', {
          confirmButtonText: 'OK',
          cancelButtonText: 'Cancel',
          type: 'warning'
        }).then(() => {
          this.saveReceiveItem(received, addToStock)
        }).catch(() => {
          this.$message({
            type: 'info',
            message: 'Confirmation canceled'
          })
        })
      } else {
        this.saveReceiveItem(received, addToStock)
      }
    },
    saveReceiveItem(received, addToStock = false) {
      const receivedOrderData = {
        ReceivedQuantity: this.dialogQuantityReceived,
        ReceivedDate: this.dialogDateReceived,
        LineId: received.OrderLineId,
        LineNo: received.LineNo,
        PurchasOrderId: received.PurchasOrderId
      }

      requestBN({
        method: 'post',
        url: '/purchasing/item/received',
        data: { data: receivedOrderData }
      }).then(response => {
        this.showConfirmDialog = false
        this.getOrderLines()

        if (response.error == null) {
          if (addToStock === false) {
            this.$message({
              showClose: true,
              message: 'Changes saved successfully',
              duration: 2,
              type: 'success'
            })
          } else {
            this.openAddStockDialog(response.data.ReceivalId)
          }
        } else {
          this.$message({
            showClose: true,
            message: response.error,
            duration: 0,
            type: 'error'
          })
        }
      })
    },
    skuSearch() {
      const line = this.lines.find(element => element.SupplierSku.trim() == this.searchInput.trim())

      if (line === undefined) {
        this.$message({
          type: 'warning',
          message: 'SKU not found'
        })
      } else {
        if (parseInt(line.QuantityOrderd, 10) > parseInt(line.QuantityReceived, 10)) {
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
