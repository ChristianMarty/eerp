<template>
  <div class="app-container">
    <h1>Bulk Remove</h1>

    <el-steps :active="step" finish-status="success">
      <el-step title="Select Parts" />
      <el-step title="Checkout" />
      <el-step title="Complete" />
    </el-steps>

    <el-button v-if="step === 0" @click="loadCheckoutPage()">Checkout</el-button>
    <el-button v-if="step === 1" @click="loadSelectPartPagre()">Select Parts</el-button>
    <el-button v-if="step === 1" @click="loadCompletePage()">Finish</el-button>

    <el-divider />

    <template v-if="step === 0">
      <el-input
        ref="stockNoInput"
        v-model="stockNoInput"
        placeholder="Stock Barcode (STK-xxxx)"
        @keyup.enter.native="searchInput()"
      >
        <el-button slot="append" icon="el-icon-search" @click="searchInput()" />
      </el-input>
    </template>

    <template v-if="step === 1">
      <el-form label-width="150px">
        <el-form-item label="Work Order:">

          <el-select v-model="selectedWorkOrderNumber" filterable>
            <el-option
              v-for="wo in workOrders"
              :key="wo.WorkOrderNumber"
              :label="wo.ItemCode + ' - ' + wo.Name"
              :value="wo.WorkOrderNumber"
            />
          </el-select>
          <el-button type="primary" @click="workOrderId = null">Clear</el-button>

        </el-form-item>
      </el-form>
    </template>

    <template v-if="step === 3">
      <p><b>Work Order:</b> {{ selectedWorkOrderData.ItemCode }}  {{ selectedWorkOrderData.Name }}</p>
    </template>

    <h2>Items</h2>

    <template v-if="step === 3">
      <el-select v-model="selectedPrinterId">
        <el-option v-for="item in printer" :key="Number(item.Id)" :label="item.Name" :value="Number(item.Id)" />
      </el-select>
      <el-button @click="printReceipt()">Print Receipt</el-button>
      <el-button @click="printAllNotes()">Print All Part Notes </el-button>
    </template>

    <p>
      <el-table
        :data="itemList"
        border
        style="width: 100%"
        @row-click="(row, column, event) =>openSetQuantity(row)"
      >
        <el-table-column prop="ItemCode" label="Item Code" width="100" />
        <el-table-column prop="ManufacturerName" label="Manufacturer" />
        <el-table-column prop="ManufacturerPartNumber" label="Part Number" />
        <el-table-column prop="Quantity" label="Stock Quantity" width="130" />
        <el-table-column prop="RemoveQuantity" label="Remove Quantity" width="150" />
      </el-table>
    </p>

    <el-dialog
      title="Remove Quantity"
      :visible.sync="quantityDialogVisible"
      width="50%"
    >
      <el-form label-width="150px">
        <el-form-item label="Item Code:">
          {{ dialogData.ItemCode }}
        </el-form-item>
        <el-form-item label="Part:">
          {{ dialogData.ManufacturerName }} - {{ dialogData.ManufacturerPartNumber }}
        </el-form-item>
        <el-form-item label="Remove Quantity:">
          <el-input-number
            ref="stockRemoveQuantityInput"
            v-model="dialogData.RemoveQuantity"
            :min="1"
            :max="Number(dialogData.Quantity)"
            @keyup.enter.native="setQuantityAndClose(dialogData)"
          />
        </el-form-item>
        <el-form-item label="Current Stock:">
          {{ dialogData.Quantity }}
        </el-form-item>
        <el-form-item label="Note:">
          <el-input v-model="dialogData.Note" type="textarea" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="setQuantityAndClose(dialogData)">Confirm</el-button>
          <el-button @click="quantityDialogVisible = false">Cancel</el-button>
        </el-form-item>
      </el-form>
    </el-dialog>
  </div>
</template>

<script>
import * as defaultSetting from '@/utils/defaultSetting'

import WorkOrder from '@/api/workOrder'
const workOrder = new WorkOrder()

import Stock from '@/api/stock'
const stock = new Stock()

import Peripheral from '@/api/peripheral'
const peripheral = new Peripheral()

const dialogDataEmpty = {
  ItemCode: '',
  ManufacturerName: '',
  ManufacturerPartNumber: '',
  Note: '',
  Quantity: 0,
  RemoveQuantity: 0
}

export default {
  name: 'BulkRemove',
  components: {},
  data() {
    return {
      dialogData: Object.assign({}, dialogDataEmpty),
      step: 0,
      quantityDialogVisible: false,
      selectedPrinterId: 0,

      stockNoInput: '',
      itemList: [],
      workOrders: [],

      selectedWorkOrderNumber: null,
      selectedWorkOrderData: {}
    }
  },
  mounted() {
    this.loadSelectPartPage()
    this.getPrinter()
  },
  methods: {
    findWorkOrder(WorkOrderNumber) {
      if (WorkOrderNumber == null) return { ItemCode: null, WorkOrderNumber: null, Name: null }
      return this.workOrders.find(element => element.WorkOrderNumber === WorkOrderNumber)
    },
    clear() {
      this.workOrderId = null
      this.stockNoInput = ''
      this.itemList = []
    },
    searchInput() {
      stock.item.get(this.stockNoInput).then(response => {
        this.openSetQuantity(response)
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
      this.stockNoInput = ''
      this.$refs.stockNoInput.focus()
    },
    openSetQuantity(data) {
      if (this.step !== 0) return

      this.quantityDialogVisible = true

      this.dialogData.ItemCode = data.ItemCode
      this.dialogData.ManufacturerName = data.Part.ManufacturerName
      this.dialogData.ManufacturerPartNumber = data.Part.ManufacturerPartNumber
      this.dialogData.Note = ''
      this.dialogData.Quantity = data.Quantity.Quantity
      this.dialogData.RemoveQuantity = 1

      this.$nextTick(() => {
        this.$refs.stockRemoveQuantityInput.focus()
      })
    },
    setQuantityAndClose(data) {
      this.quantityDialogVisible = false

      // if not already in list
      if (!this.itemList.some((element) => element.ItemCode === data.ItemCode)) {
        this.itemList.unshift(data)
      } else {
        this.$message({
          showClose: true,
          message: 'This item is already in the list.',
          type: 'warning'
        })
      }

      this.$refs.stockNoInput.focus()
    },
    loadSelectPartPage() {
      this.step = 0
      this.$refs.stockNoInput.focus()
    },
    async loadCheckoutPage() {
      this.workOrders = await workOrder.search('InProgress')
      this.step = 1
    },
    loadCompletePage() {
      stock.bulkRemove(this.itemList, this.selectedWorkOrderNumber).then(response => {
        this.$message({
          message: 'Quantity updated successfully',
          type: 'success'
        })
        this.selectedWorkOrderData = this.findWorkOrder(this.selectedWorkOrderNumber)
        this.step = 3
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    printReceipt() {
      print.template.partReceipt(this.selectedPrinterId, this.itemList, this.selectedWorkOrderNumber).then(response => {
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    printAllNotes() {
      print.template.partNote(this.selectedPrinterId, this.itemList, this.selectedWorkOrderNumber).then(response => {
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    getPrinter() {
      peripheral.list(peripheral.Type.Printer).then(response => {
        this.printer = response
        this.selectedPrinterId = defaultSetting.defaultSetting().PartReceiptPrinter
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    }
  }
}
</script>
