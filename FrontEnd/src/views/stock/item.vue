<template>
  <div class="app-container">
    <h2>Stock</h2>

    <p>
      <el-input
        ref="itemNrInput"
        v-model="inputStockId"
        placeholder="Please Input Stock Number"
        @keyup.enter.native="setItem"
      >
        <el-button slot="append" icon="el-icon-search" @click="setItem" />
      </el-input>
    </p>
    <p>
      <el-button type="danger" @click="clear()">Clear</el-button>
    </p>

    <el-card v-if="showItem">
      <h3 v-if="partData.Deleted" style="color:red">Part Information - This item has been marked to be deleted!</h3>
      <h3 v-else>Part Information</h3>
      <p>{{ partData.Description }}</p>
      <el-divider />
      <p><b>Manufacturer: </b>
        <router-link :to="'/vendor/view/' + partData.ManufacturerId" class="link-type">
          {{ partData.ManufacturerName }}
        </router-link>
      </p>
      <p><b>Part Number: </b>
        <router-link :to="'/manufacturerPart/item/' + partData.ManufacturerPartItemId" class="link-type">
          {{ partData.ManufacturerPartNumber }}
        </router-link>
      </p>
      <el-divider />
      <h4>Production Parts:</h4>
      <el-table :data="productionPartData" style="width: 100%">
        <el-table-column prop="ProductionPartBarcode" label="Part Number" sortable width="150">
          <template slot-scope="{ row }">
            <router-link :to="'/productionPart/item/' + row.ProductionPartBarcode" class="link-type">
              <span>{{ row.ProductionPartBarcode }}</span>
            </router-link>
          </template>
        </el-table-column>
        <el-table-column prop="Description" label="Description" sortable />
      </el-table>
    </el-card>

    <el-card v-if="showItem">
      <h3>Stock Information {{ partData.Barcode }}</h3>
      <el-divider />
      <p><b>Location: </b>{{ partData.Location }}</p>
      <p><b>Location Path: </b>{{ partData.LocationPath }}</p>
      <p><b>Home Location: </b>{{ partData.HomeLocation }}</p>
      <p><b>Home Location Path: </b>{{ partData.HomeLocationPath }}</p>
      <p><b>Quantity: </b>{{ partData.Quantity }}</p>
      <p><b>Lot Number: </b>{{ partData.LotNumber }}</p>
      <p><b>Date Code: </b>{{ partData.DateCode }}</p>
      <p><b>Reserved Quantity: </b>{{ partData.ReservedQuantity }}</p>
      <p><b>Last Counted: </b>{{ partData.LastCountDate }}</p>
      <span>
        <p><b>Stock Certainty Factor: </b>{{ stockAccuracy.CertaintyFactor }}</p>
        <el-rate v-model="stockAccuracy.CertaintyFactorRating" disabled />
      </span>
      <el-button v-permission="['location.transfer']" style="margin-top: 20px" @click="showLocationTransferDialog()">Location Transfer</el-button>
      <el-divider v-permission="['stock.add', 'stock.remove', 'stock.count']" />
      <h4 v-permission="['stock.add', 'stock.remove', 'stock.count']">Stock Movement</h4>

      <el-button
        v-permission="['stock.add']"
        style="margin-right: 20px"
        icon="el-icon-plus"
        @click="addStockDialogVisible = true"
      >Add</el-button>

      <el-button
        v-permission="['stock.remove']"
        style="margin-right: 20px"
        icon="el-icon-minus"
        @click="removeStockDialogVisible = true"
      >Remove</el-button>

      <el-button
        v-permission="['stock.count']"
        style="margin-right: 20px"
        icon="el-icon-finished"
        @click="countStockDialogVisible = true"
      >Count</el-button>
      <el-divider />

      <h3>History</h3>
      <stockHistory :key="stockHistoryKey" :stock-no="inputStockId" @change="historyEdit" />

      <h3>Reservations</h3>
      <el-table :data="reservation" style="width: 100%">
        <el-table-column prop="WorkOrderNo" label="Work Order No" sortable width="150">
          <template slot-scope="{ row }">
            <router-link :to="'/workOrder/workOrderView/' + row.WorkOrderNo" class="link-type">
              <span>{{ row.WorkOrderNo }}</span>
            </router-link>
          </template>
        </el-table-column>
        <el-table-column prop="Title" label="Title" sortable />
        <el-table-column prop="Quantity" label="Quantity" sortable />
      </el-table>
    </el-card>

    <el-card v-if="showItem">
      <h3>Supplier Information</h3>
      <el-divider />
      <p><b>Supplier: </b>{{ partData.SupplierName }}</p>
      <p><b>Part Number: </b>{{ partData.SupplierPartNumber }}</p>
      <p><b>Order Reference: </b>{{ partData.OrderReference }}</p>
    </el-card>

    <el-card v-if="showItem">
      <h3>Purchase Information</h3>
      <el-divider />
      <p><b>PO No: </b>
        <router-link :to="'/purchasing/edit/' + purchaseInformation.PoNo" class="link-type">
          <span>{{ purchaseInformation.PoNo }}</span>
        </router-link>
      </p>
      <p><b>Price: </b>{{ purchaseInformation.Price }} {{ purchaseInformation.Currency }}</p>
      <p><b>Date: </b>{{ purchaseInformation.PurchaseDate }}</p>
    </el-card>

    <el-card v-if="showItem">
      <h3>Print Label</h3>
      <el-divider />
      <template v-if="label !== null">

        <el-select v-model="selectedLabelId">
          <el-option v-for="item in label" :key="Number(item.Id)" :label="item.Name" :value="Number(item.Id)" />
        </el-select>

        <el-select v-model="selectedPrinterId">
          <el-option v-for="item in printer" :key="Number(item.Id)" :label="item.Name" :value="Number(item.Id)" />
        </el-select>
        <el-button type="primary" style="margin-left: 20px" @click="printDialogVisible = true">Print</el-button>
      </template>
    </el-card>

    <el-card v-if="showItem" v-permission="['stock.delete']">
      <h3>Delete Item</h3>
      <el-button type="danger" @click="openDeleteDialog()">Delete</el-button>
    </el-card>

    <el-dialog
      title="Delete Item ?"
      :visible.sync="deleteDialogVisible"
    >
      <p><b>Note:</b></p>
      <el-input v-model="deleteNote" type="textarea" />
      <p />
      <el-button type="danger" @click="deleteStockItem()">Confirm Deletion</el-button>
      <el-button @click="deleteDialogVisible = false">Cancel</el-button>

    </el-dialog>

    <printDialog :visible.sync="printDialogVisible" :data="partData" @print="print" />

    <addStockDialog :visible.sync="addStockDialogVisible" :item="partData" />
    <removeStockDialog :visible.sync="removeStockDialogVisible" :item="partData" />
    <countStockDialog :visible.sync="countStockDialogVisible" :item="partData" />

    <locationTransferDialog :barcode="partData.Barcode" :visible.sync="locationTransferDialogVisible" @change="loadItem()" />
  </div>
</template>

<script>
import permission from '@/directive/permission/index.js'

import * as labelTemplate from '@/utils/labelTemplate'
import * as defaultSetting from '@/utils/defaultSetting'

import printDialog from './components/printDialog'
import addStockDialog from './components/addStockDialog'
import removeStockDialog from './components/removeStockDialog'
import countStockDialog from './components/countStockDialog'
import stockHistory from './components/stockHistory'

import locationTransferDialog from '@/components/Location/locationTransferDialog'

import Stock from '@/api/stock'
const stock = new Stock()

import Print from '@/api/print'
const print = new Print()

import ProductionPart from '@/api/productionPart'
const productionPart = new ProductionPart()

const partDataEmpty = {
  StockId: '',
  Manufacturer: '',
  ManufacturerPartNumber: '',
  Date: '',
  Quantity: '',
  Location: '',
  Barcode: ''
}

const purchaseInformationData = {
  PoNo: '',
  Price: '',
  Currency: ''
}

const stockAccuracyData = {
  CertaintyFactor: 0,
  DaysSinceStocktaking: '',
  LastStocktakingDate: ''
}

export default {
  name: 'LocationAssignment',
  components: { printDialog, addStockDialog, removeStockDialog, countStockDialog, stockHistory, locationTransferDialog },
  directives: { permission },
  data() {
    return {
      inputStockId: null,
      showItem: false,
      partData: Object.assign({}, partDataEmpty),
      reservation: null,
      purchaseInformation: Object.assign({}, purchaseInformationData),
      stockAccuracy: Object.assign({}, stockAccuracyData),
      label: null,
      printer: {},
      selectedPrinterId: 0,
      selectedLabelId: 0,
      productionPartData: [],
      printDialogVisible: false,
      addStockDialogVisible: false,
      removeStockDialogVisible: false,
      countStockDialogVisible: false,
      editStockHistoryDialogVisible: false,
      stockHistoryKey: 0,
      locationTransferDialogVisible: false,
      deleteDialogVisible: false,
      deleteNote: ''
    }
  },
  watch: {
    addStockDialogVisible: function() { this.loadItem() },
    removeStockDialogVisible: function() { this.loadItem() },
    countStockDialogVisible: function() { this.loadItem() }
  },
  mounted() {
    this.reset()

    if (this.$route.params.StockNo != null) {
      this.inputStockId = this.$route.params.StockNo
      this.loadItem()
    } else {
      this.$refs.itemNrInput.focus()
    }

    this.getLabel()
    this.getPrinter()
  },
  created() {
    // Why need to make a copy of this.$route here?
    // Because if you enter this page and quickly switch tag, may be in the execution of the setTagsViewTitle function, this.$route is no longer pointing to the current page
    // https://github.com/PanJiaChen/vue-element-admin/issues/1221
    this.tempRoute = Object.assign({}, this.$route)
  },
  methods: {
    clear() {
      this.$refs.itemNrInput.focus()
      this.reset()
    },
    setTitle(title) {
      const route = Object.assign({}, this.tempRoute, {
        title: `${title}`
      })
      this.$store.dispatch('tagsView/updateVisitedView', route)
      document.title = `${this.partData.Barcode} - ${this.partData.ManufacturerPartNumber}`
    },
    showLocationTransferDialog() {
      this.locationTransferDialogVisible = true
    },
    setItem() {
      this.$router.push('/stock/item/' + this.inputStockId)
    },
    loadItem() {
      this.getStockItem()
      this.getReservation()
      this.getPurchaseInformation()
      this.getStockAccuracy()
      this.showItem = true
      this.stockHistoryKey++
    },
    getStockItem() {
      stock.item.get(this.inputStockId).then(response => {
        if (response.length === 0) {
          this.$message({
            showClose: true,
            message: 'Item dose not exist!',
            type: 'warning'
          })
        } else {
          this.partData = response
          this.getProductionPartData()
          this.setTitle(this.partData.Barcode)
        }
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    getStockAccuracy() {
      stock.item.accuracy(this.inputStockId).then(response => {
        this.stockAccuracy = response
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    getReservation() {
      stock.item.reservation(this.inputStockId).then(response => {
        this.reservation = response
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    getPurchaseInformation() {
      stock.item.purchaseInformation(this.inputStockId).then(response => {
        this.purchaseInformation = response
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    getProductionPartData() {
      if (this.partData.ManufacturerPartNumberId === null) return

      productionPart.search(null, this.partData.ManufacturerPartNumberId).then(response => {
        this.productionPartData = response
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    reset() {
      this.inputStockId = null
      this.showItem = false
      this.printDialogVisible = false
      this.setTitle('Stock Item')
    },
    getLabel() {
      print.label.search('Stock').then(response => {
        this.label = response
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
        this.selectedPrinterId = defaultSetting.defaultSetting().StockLabelPrinter
        this.selectedLabelId = defaultSetting.defaultSetting().StockLabel
        this.printer = response
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    print(printData) {
      var labelData = {
        $Barcode: printData.Barcode,
        $StockId: printData.StockNo,
        $Mfr: printData.ManufacturerName,
        $MPN: printData.ManufacturerPartNumber,
        $PartNo: printData.OrderReference,
        $Description: printData.Description
      }

      var labelTemplateObject = this.label.find(element => { return Number(element.Id) === this.selectedLabelId })
      var labelCode = labelTemplate.labelTemplate(labelTemplateObject.Code, labelData)

      print.print('raw', labelTemplateObject.Language, this.selectedPrinterId, labelCode).then(response => {
        this.selectedPrinterId = defaultSetting.defaultSetting().StockLabelPrinter
        this.selectedLabelId = defaultSetting.defaultSetting().StockLabel
        this.printer = response
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    openDeleteDialog() {
      this.deleteDialogVisible = true
    },
    deleteStockItem() {
      stock.item.delete(this.inputStockId, this.deleteNote).then(response => {
        this.deleteDialogVisible = false
        this.deleteNote = ''
        this.loadItem()
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

<style>
.el-card {
  margin-top: 20px;
}
</style>
