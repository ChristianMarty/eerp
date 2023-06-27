<template>
  <div class="app-container">
    <h1>Bulk Remove</h1>

    <el-steps :active="step" finish-status="success">
      <el-step title="Select Parts" />
      <el-step title="Checkout" />
      <el-step title="Complete" />
    </el-steps>

    <el-button v-if="step == 0" @click="loadCheckoutPage()">Checkout</el-button>
    <el-button v-if="step == 1" @click="loadSelectPartPagre()">Select Parts</el-button>
    <el-button v-if="step == 1" @click="loadCompletePage()">Finish</el-button>

    <el-divider />

    <template v-if="step == 0">
      <el-input
        ref="stockNoInput"
        v-model="stockNoInput"
        placeholder="Stock Barcode (STK-xxxx)"
        @keyup.enter.native="searchInput()"
      >
        <el-button slot="append" icon="el-icon-search" @click="searchInput()" />
      </el-input>
    </template>

    <template v-if="step == 1">
      <el-form label-width="150px">
        <el-form-item label="Work Order:">

          <el-select v-model="selectedWorkOrderNumber" filterable>
            <el-option
              v-for="wo in workOrders"
              :key="wo.WorkOrderNumber"
              :label="wo.WorkOrderBarcode + ' - ' + wo.Title"
              :value="wo.WorkOrderNumber"
            />
          </el-select>
          <el-button type="primary" @click="workOrderId = null">Clear</el-button>

        </el-form-item>
      </el-form>
    </template>

    <template v-if="step == 3">
      <p><b>Work Order:</b> {{ selectedWorkOrderData.WorkOrderBarcode }}  {{ selectedWorkOrderData.Title }}</p>
    </template>

    <h2>Items</h2>

    <template v-if="step == 3">
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
        <el-table-column prop="Barcode" label="Item Nr." width="100" />
        <el-table-column prop="ManufacturerName" label="Manufacturer" />
        <el-table-column prop="ManufacturerPartNumber" label="Part Number" />
        <el-table-column prop="DateCode" label="Date Code" width="100" />
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
        <el-form-item label="Barcode:">
          {{ currentItem.Barcode }}
        </el-form-item>
        <el-form-item label="Part:">
          {{ currentItem.ManufacturerName }} - {{ currentItem.ManufacturerPartNumber }}
        </el-form-item>
        <el-form-item label="Remove Quantity:">
          <el-input-number
            ref="stockRemoveQuantityInput"
            v-model="currentItem.RemoveQuantity"
            :min="1"
            :max="Number(currentItem.Quantity)"
            @keyup.enter.native="setQuantityAndClose(currentItem)"
          />
        </el-form-item>
        <el-form-item label="Current Stock:">
          {{ currentItem.Quantity }}
        </el-form-item>
        <el-form-item label="Note:">
          <el-input v-model="currentItem.Note" type="textarea" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="setQuantityAndClose(currentItem)">Confirm</el-button>
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

import Print from '@/api/print'
const print = new Print()

export default {
  name: 'BulkRemove',
  components: {},
  data() {
    return {
      step: 0,
      quantityDialogVisible: false,
      currentItem: {},
      quantity: 1,
      selectedPrinterId: 0,

      stockNoInput: '',
      itemList: [],

      workOrders: [],
      note: '',

      selectedWorkOrderNumber: null,
      selectedWorkOrderData: null
    }
  },
  mounted() {
    this.loadSelectPartPagre()
    this.getPrinter()
    // this.$refs.stockNoInput.focus()
    // this.itemList = Cookies.get('stock_bulkRemove_itemList')
    //
  },
  methods: {
    findWorkOrder(WorkOrderNumber) {
      if (WorkOrderNumber == null) return { WorkOrderBarcode: null, WorkOrderNumber: null, Title: null }
      return this.workOrders.find(element => element.WorkOrderNumber === WorkOrderNumber)
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
      print.printer.search().then(response => {
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
    },
    clear() {
      this.workOrderId = null
      this.stockNoInput = ''
      this.note = ''
      this.itemList = []
    },
    searchInput() {
      stock.search(true, this.stockNoInput).then(response => {
        if (response.length === 0) {
          this.$message({
            showClose: true,
            message: 'Item dose not exist!',
            type: 'warning'
          })
        } else {
          this.addToItemList(response[0])
        }
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
      this.currentItem = data
      this.$nextTick(() => {
        this.$refs.stockRemoveQuantityInput.focus()
      })
    },
    setQuantityAndClose(data) {
      this.quantityDialogVisible = false

      if (!this.itemList.some((element) => element.StockNo === data.StockNo)) {
        this.itemList.unshift(data)
      }

      this.$refs.stockNoInput.focus()
    },
    loadSelectPartPagre() {
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
        this.step = 3
        this.selectedWorkOrderData = this.findWorkOrder(this.selectedWorkOrderNumber)
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    addToItemList(data) {
      this.openSetQuantity(data)

      /* var cookieList = []
      try {
        var cookiesText = Cookies.get('invNo')
        cookieList = JSON.parse(cookiesText)
      } catch (e) {
        cookieList = []
      }

      var invNoList = []
      invNoList = invNoList.concat(cookieList)

      invNoList.push(this.inventoryData.InvNo)
      Cookies.set('invNo', invNoList)*/
    }
  }
}
</script>
