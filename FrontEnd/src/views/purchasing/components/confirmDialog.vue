<template>
  <div class="confirm-dialog">
    <el-dialog
      title="Confirm Item Received"
      :visible.sync="visible"
      :before-close="closeDialog"
      center
      width="50%"
      @open="onOpen()"
    >
      <el-form size="mini" label-width="220px">
        <el-form-item label="Sku:">{{ line.SupplierSku }}</el-form-item>
        <template v-if="line.Type === 'Part'">
          <el-form-item label="Production Part No:">{{ line.PartNo }}</el-form-item>
          <el-form-item label="Manufacturer Name:">{{ line.ManufacturerName }}</el-form-item>
          <el-form-item label="Manufacturer Part Number:">{{ line.ManufacturerPartNumber }}</el-form-item>
        </template>
        <el-form-item label="Description:">{{ line.Description }}</el-form-item>
        <el-form-item label="Order Reference:">{{ line.OrderReference }}</el-form-item>
        <el-form-item label="Note:">{{ line.Note }}</el-form-item>

        <el-form-item label="Ordered Quantity:">{{ line.QuantityOrdered }}</el-form-item>
        <el-form-item label="Received Quantity:">
          <el-input-number
            v-model="data.quantity"
            :min="1"
            :max="maxConfirmQuantity"
          >Received Quantity</el-input-number>
        </el-form-item>
        <el-form-item label="Receiving Date:">
          <el-date-picker v-model="data.date" type="date" placeholder="Pick a day" value-format="yyyy-MM-dd" />
        </el-form-item>
      </el-form>

      <span slot="footer" class="dialog-footer">
        <el-button v-if="line.StockPart === true" type="primary" @click="confirm(true)">Confirm and add to stock</el-button>
        <el-button type="primary" @click="confirm(false)">Confirm</el-button>
        <el-button @click="closeDialog()">Cancel</el-button>
      </span>
    </el-dialog>
  </div>
</template>
<script>

const receivedItemData = {
  SupplierSku: '',
  Type: null,
  PartNo: '',
  SupplierName: '',
  ManufacturerPartNumber: '',
  Description: '',
  OrderReference: '',
  Note: '',
  QuantityOrdered: 0,
  QuantityReceived: 0
}

const saveData = {
  quantity: 0,
  date: ''
}

import Purchase from '@/api/purchase'
const purchase = new Purchase()

export default {
  name: 'ConfirmReceived',
  props: {
    visible: { type: Boolean, default: false },
    line: { type: Object, default: receivedItemData },
    date: { type: Date, default: null }
  },
  data() {
    return {
      data: Object.assign({}, saveData),
      maxConfirmQuantity: 0,
      addToStock: false,
      receivalId: null
    }
  },
  mounted() {
  },
  methods: {
    onOpen() {
      this.maxConfirmQuantity = this.$props.line.QuantityOrdered - this.$props.line.QuantityReceived
      this.data.quantity = this.maxConfirmQuantity
      if (this.$props.date === null) {
        this.data.date = new Date().toISOString().substring(0, 10)
      } else {
        this.data.date = new Date(this.$props.date).toISOString().substring(0, 10)
      }
    },
    confirm(addToStock = false) {
      const now = new Date()
      if (this.data.date > now) {
        this.$confirm('The selected date is in the future. Are you sure?', 'Warning', {
          confirmButtonText: 'OK',
          cancelButtonText: 'Cancel',
          type: 'warning'
        }).then(() => {
          this.saveReceiveItem(addToStock)
        }).catch(() => {
          this.$message({
            type: 'info',
            message: 'Confirmation canceled'
          })
        })
      } else {
        this.saveReceiveItem(addToStock)
      }
    },
    saveReceiveItem(addToStock = false) {
      this.addToStock = addToStock
      const confirmData = Object.assign({}, purchase.item.receive.confirmParameters)

      confirmData.ReceivedQuantity = this.data.quantity
      confirmData.ReceivedDate = this.data.date
      confirmData.LineId = this.$props.line.OrderLineId

      purchase.item.receive.confirm(confirmData).then(response => {
        if (addToStock === false) {
          this.$message({
            showClose: true,
            message: 'Changes saved successfully',
            duration: 2,
            type: 'success'
          })
        }
        this.receivalId = response.ReceivalId
        this.closeDialog()
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    closeDialog() {
      this.visible = false
      this.$emit('update:visible', this.visible)
      this.$emit('close', this.receivalId, this.data.date, this.addToStock)
    }
  }
}
</script>
