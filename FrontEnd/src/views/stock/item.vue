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
        <router-link :to="'/vendor/view/' + partData.Part.ManufacturerId" class="link-type">
          {{ partData.Part.ManufacturerName }}
        </router-link>
      </p>
      <p><b>Part Number: </b>
        <router-link
          :to="'/manufacturerPart/partNumber/item/' + partData.Part.ManufacturerPartNumberId"
          class="link-type"
        >
          {{ partData.Part.ManufacturerPartNumber }}
        </router-link>
      </p>
      <el-divider />

      <el-row>
        <el-col :span="4">
          <p><b>Lot Number:</b></p>
          <p><b>Date Code:</b></p>
        </el-col>
        <el-col :span="8">
          <p>{{ partData.LotNumber }}</p>
          <p>{{ partData.DateCode }}</p>
        </el-col>
        <el-col :span="4">
          <p><b>Part Weight:</b></p>
          <p><b>Country of Origin:</b></p>
        </el-col>
        <el-col :span="8">
          <p>{{ partData.Part.Weight.SinglePartWeight }}{{ partData.Part.Weight.UnitOfMeasurement.Symbol }}</p>
          <p>{{ partData.CountryOfOrigin.Alpha2Code }} - {{ partData.CountryOfOrigin.Name }}</p>
        </el-col>
      </el-row>

      <el-button
        v-permission="['stock.edit']"
        size="mini"
        type="primary"
        icon="el-icon-edit"
        circle
        style="margin-top: 00px; margin-bottom: 00px"
        @click="showEditDialog()"
      />
      <el-divider />
      <h4>Production Parts:</h4>
      <el-table :data="productionPartData" style="width: 100%">
        <el-table-column prop="ItemCode" label="Part Number" sortable width="150">
          <template slot-scope="{ row }">
            <router-link :to="'/productionPart/item/' + row.ItemCode" class="link-type">
              <span>{{ row.ItemCode }}</span>
            </router-link>
          </template>
        </el-table-column>
        <el-table-column prop="Description" label="Description" sortable />
        <el-table-column prop="ApprovedUsage" label="Approved Usage" sortable width="200" />
      </el-table>
      <template v-if="partData.Purchase !== null && partData.Purchase.ProductionPartNumber !== ''">
        <p><b>Ordered as:</b> {{ partData.Purchase.ProductionPartNumber }}</p>
      </template>
    </el-card>

    <el-card v-if="showItem">
      <h3>Location</h3>
      <el-divider />
      <el-row>
        <el-col :span="4">
          <p><b>Location: </b></p>
          <p><b>Location Path: </b></p>
        </el-col>
        <el-col :span="8">
          <p>{{ partData.Location.Name }}</p>
          <p>{{ partData.Location.Path }}</p>
        </el-col>
        <el-col :span="4">
          <p><b>Home Location: </b></p>
          <p><b>Home Location Path: </b></p>
        </el-col>
        <el-col :span="8">
          <p>{{ partData.Location.HomeName }}</p>
          <p>{{ partData.Location.HomePath }}</p>
        </el-col>
      </el-row>

      <el-button v-permission="['location.transfer']" style="margin-top: 20px" @click="showLocationTransferDialog()">
        Location Transfer
      </el-button>
    </el-card>

    <el-card v-if="showItem">
      <h3>Quantity</h3>
      <el-divider />

      <el-row>
        <el-col :span="4">
          <p><b>Quantity:</b></p>
          <p><b>Last Counted:</b></p>
          <p><b>Days Since Counted:</b></p>
        </el-col>
        <el-col :span="8">
          <p>{{ partData.Quantity.Quantity }}</p>
          <p>{{ partData.Quantity.Certainty.LastStocktakingDate }}</p>
          <p>{{ partData.Quantity.Certainty.DaysSinceStocktaking }}</p>
        </el-col>
        <el-col :span="4">
          <p><b>Created Quantity: </b></p>
          <p><b>Created Date: </b></p>
          <p><b>Stock Certainty Factor: </b></p>
        </el-col>
        <el-col :span="8">
          <p>{{ partData.Quantity.CreateQuantity }}</p>
          <p>{{ partData.Quantity.CreateData }}</p>
          <p>{{ partData.Quantity.Certainty.Factor }}</p>
        </el-col>
      </el-row>
      <el-rate v-model="partData.Quantity.Certainty.Rating" disabled />

      <el-divider v-permission="['stock.add', 'stock.remove', 'stock.count']" />
      <el-button
        v-permission="['stock.add']"
        style="margin-right: 20px"
        icon="el-icon-plus"
        @click="addStockDialogVisible = true"
      >Add
      </el-button>

      <el-button
        v-permission="['stock.remove']"
        style="margin-right: 20px"
        icon="el-icon-minus"
        @click="removeStockDialogVisible = true"
      >Remove
      </el-button>

      <el-button
        v-permission="['stock.count']"
        style="margin-right: 20px"
        icon="el-icon-finished"
        @click="countStockDialogVisible = true"
      >Count
      </el-button>

      <el-button
        v-permission="['stock.count']"
        style="margin-right: 20px"
        icon="el-icon-finished"
        @click="scaleStockDialogVisible = true"
      >Count by weight
      </el-button>

      <el-divider />

      <h3>History</h3>
      <stockHistory :key="stockHistoryKey" :stock-code="inputStockId" />
    </el-card>

    <el-card v-if="showItem && partData.Purchase !== null">
      <h3>Supplier & Purchase Information</h3>
      <el-divider />

      <el-row>
        <el-col :span="4">
          <p><b>Supplier:</b></p>
          <p><b>Price:</b></p>
          <p><b>Purchase Order:</b></p>
        </el-col>
        <el-col :span="8">
          <p>{{ partData.Supplier.Name }}</p>
          <p>{{ partData.Purchase.PriceAfterDiscount }} {{ partData.Purchase.CurrencyCode }}</p>
          <p>
            <router-link :to="'/purchasing/edit/' + partData.Purchase.ItemCode" class="link-type">
              <span>{{ partData.Purchase.ItemCode }}</span>
            </router-link>
          </p>
        </el-col>
        <el-col :span="4">
          <p><b>Part Number:</b></p>
          <p><b>Date:</b></p>
          <p><b>Order Reference:</b></p>
        </el-col>
        <el-col :span="8">
          <p>{{ partData.Supplier.PartNumber }}</p>
          <p>{{ partData.Purchase.PurchaseDate }}</p>
          <p>{{ partData.Purchase.OrderReference }}</p>
        </el-col>
      </el-row>
      <p><b>Description	:</b> {{ partData.Purchase.Description }}</p>
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
        <el-button type="primary" style="margin-left: 20px" @click="openPrintDialog()">Print</el-button>
      </template>
    </el-card>

    <el-card v-if="showItem" v-permission="['stock.delete']">
      <h3>Delete Item</h3>
      <el-divider />
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

    <printDialog :visible.sync="printDialogVisible" :data="printData" @print="print" />

    <editItemDialog :visible.sync="editDialogVisible" :stock-code="partData.ItemCode" @change="loadItem()" />

    <addStockDialog :visible.sync="addStockDialogVisible" :item="partData" />
    <removeStockDialog :visible.sync="removeStockDialogVisible" :item="partData" />
    <countStockDialog :visible.sync="countStockDialogVisible" :item="partData" />
    <countByWeight :visible.sync="scaleStockDialogVisible" :item="partData.ItemCode" />

    <locationTransferDialog
      :barcode="partData.ItemCode"
      :visible.sync="locationTransferDialogVisible"
      @change="loadItem()"
    />
  </div>
</template>

<script>
import permission from '@/directive/permission/index.js'

import * as defaultSetting from '@/utils/defaultSetting'

import editItemDialog from './components/editItemDialog.vue'

import printDialog from './components/printDialog'
import addStockDialog from './components/addStockDialog'
import removeStockDialog from './components/removeStockDialog'
import countStockDialog from './components/countStockDialog'
import stockHistory from './components/stockHistory'
import countByWeight from './components/countByWeight'

import locationTransferDialog from '@/components/Location/locationTransferDialog'

import Stock from '@/api/stock'
const stock = new Stock()

import ProductionPart from '@/api/productionPart'
const productionPart = new ProductionPart()

import Renderer from '@/api/renderer'
const renderer = new Renderer()

import Print from '@/api/print'
const print = new Print()

import Peripheral from '@/api/peripheral'
const peripheral = new Peripheral()

export default {
  name: 'LocationAssignment',
  components: {
    printDialog,
    addStockDialog,
    removeStockDialog,
    countStockDialog,
    stockHistory,
    locationTransferDialog,
    countByWeight,
    editItemDialog
  },
  directives: { permission },
  data() {
    return {
      inputStockId: null,
      showItem: false,
      partData: Object.assign({}, stock.item.itemDataEmpty),
      reservation: null,
      label: null,
      printer: {},
      printData: Object.assign({}, printDialog.printData),
      selectedPrinterId: 0,
      selectedLabelId: 0,
      productionPartData: [],
      printDialogVisible: false,
      addStockDialogVisible: false,
      removeStockDialogVisible: false,
      countStockDialogVisible: false,
      scaleStockDialogVisible: false,
      editStockHistoryDialogVisible: false,
      stockHistoryKey: 0,
      locationTransferDialogVisible: false,
      deleteDialogVisible: false,
      deleteNote: '',
      editDialogVisible: false
    }
  },
  watch: {
    addStockDialogVisible: function() {
      this.loadItem()
    },
    removeStockDialogVisible: function() {
      this.loadItem()
    },
    countStockDialogVisible: function() {
      this.loadItem()
    },
    scaleStockDialogVisible: function() {
      this.loadItem()
    }
  },
  mounted() {
    this.reset()

    if (this.$route.params.StockNumber != null) {
      this.inputStockId = this.$route.params.StockNumber
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
    setTitle() {
      const route = Object.assign({}, this.tempRoute, {
        title: this.partData.ItemCode
      })
      this.$store.dispatch('tagsView/updateVisitedView', route)
      document.title = this.partData.ItemCode
    },
    showLocationTransferDialog() {
      this.locationTransferDialogVisible = true
    },
    showEditDialog() {
      this.editDialogVisible = true
    },
    setItem() {
      this.$router.push('/stock/item/' + this.inputStockId)
    },
    loadItem() {
      this.getStockItem()
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
    getProductionPartData() {
      if (this.partData.Part.ManufacturerPartNumberId === null && this.partData.Part.SpecificationPartRevisionId === null) return

      productionPart.search(null, this.partData.Part.ManufacturerPartNumberId, this.partData.Part.SpecificationPartRevisionId).then(response => {
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
      renderer.list(true, renderer.Dataset.Stock).then(response => {
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
    openPrintDialog() {
      this.printData.ManufacturerName = this.partData.Part.ManufacturerName
      this.printData.ManufacturerPartNumber = this.partData.Part.ManufacturerPartNumber
      this.printData.ProductionPartNumber = ''
      this.printData.Description = this.partData.Description
      this.printData.StockNumber = this.partData.StockNumber
      this.printData.ItemCode = this.partData.ItemCode

      if (this.partData.Purchase !== null) {
        this.printData.ProductionPartNumber = this.partData.Purchase.ProductionPartNumber
      }

      if (this.printData.ProductionPartNumber === '') {
        this.printData.ProductionPartNumber = this.partData.OrderReference
      }

      if (this.productionPartData.length) {
        this.printData.ProductionPartNumber = this.productionPartData[0].ItemCode
        this.printData.Description = this.productionPartData[0].Description
      }

      this.printDialogVisible = true
    },
    getPrinter() {
      peripheral.list(peripheral.Type.Printer).then(response => {
        this.selectedPrinterId = defaultSetting.defaultSetting().Stock.Renderer.Item.PeripheralId
        this.selectedLabelId = defaultSetting.defaultSetting().Stock.Renderer.Item.RendererId
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
    async print(printData) {
      var labelData = {
        $Barcode: printData.ItemCode,
        $StockId: printData.StockNumber,
        $Mfr: printData.ManufacturerName,
        $MPN: printData.ManufacturerPartNumber,
        $PartNo: printData.ProductionPartNumber,
        $Description: printData.Description
      }

      // this.selectedPrinterId = defaultSetting.defaultSetting().StockLabelPrinter
      // this.selectedLabelId = defaultSetting.defaultSetting().StockLabel

      print.print(this.selectedLabelId, this.selectedPrinterId, labelData).then(response => {
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
