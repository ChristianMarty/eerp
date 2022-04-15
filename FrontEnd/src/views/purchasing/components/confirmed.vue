<template>
  <div class="placerd-container">
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
      <el-table-column prop="QuantityOrderd" label="Orderd Qty" width="120" />
      <el-table-column prop="QuantityReceived" label="Received Qty" width="120" />
      <el-table-column prop="ReceivalDate" label="Receival Date" width="120" />

      <el-table-column prop="SupplierSku" label="Supplier SKU" width="220" />

      <el-table-column label="Item">
        <template slot-scope="{ row }">
          <template v-if="row.Type == 'Generic'">{{ row.Description }}</template>

          <template v-if="row.Type == 'Part'">
            {{ row.PartNo }} - {{ row.ManufacturerName }} -
            {{ row.ManufacturerPartNumber }} - {{ row.Description }}
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
            @click="openDialog(row)"
          >Confirm
          </el-button>

          <el-button
            v-if="
              row.ReceivalId != NULL
            "
            v-permission="['stock.create']"
            style="float: right;"
            type="text"
            size="mini"
            @click="addToStockDialogReceivalId = row.ReceivalId, addToStockDialogVisible = true"
          >Add Stock</el-button>
        </template>
      </el-table-column>
    </el-table>

    <el-dialog title="Confirm Item Received" :visible.sync="showDialog" width="50%" center>
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
        <el-button type="primary" @click="receiveItem(receiveDialog, false)">Confirm</el-button>
        <el-button @click="showDialog = false">Cancel</el-button>

      </span>
    </el-dialog>

    <addToStock :visible.sync="addToStockDialogVisible" :receival-id="addToStockDialogReceivalId" />  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'
import addToStock from './addToStockDialog'
import permission from '@/directive/permission/index.js'

export default {
  components: { addToStock },
  directives: { permission },
  props: { orderData: { type: Object, default: null }},
  data() {
    return {
      lines: null,

      showDialog: false,
      receiveDialog: false,
      dialogQuantityReceived: null,
      dialogDateReceived: null,
      addToStockDialogVisible: false,
      addToStockDialogReceivalId: 0
    }
  },
  created() {
    this.getOrderLines()
    this.dialogDateReceived = new Date().toISOString().substring(0, 10)
  },
  mounted() { },
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
        this.prepairLines(this.lines)
      })
    },
    openDialog(dialogData) {
      this.showDialog = true
      this.receiveDialog = dialogData

      this.dialogQuantityReceived =
        this.receiveDialog.QuantityOrderd - this.receiveDialog.QuantityReceived
      this.receiveDialog.ReceivedDate = new Date()
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
            })
          }
        }
      })
    },
    receiveItem(received, addToStock = false) {
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
        this.showDialog = false
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
            this.addToStockDialogVisible = true
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
    }
  }
}
</script>
