<template>
  <div class="app-container">
    <h1>Stock Item</h1>
    <el-divider />
    <h2>Input Stock Number:</h2>
    <p>
      <el-input
        ref="itemNrInput"
        v-model="inputStockId"
        placeholder="Please input"
        @keyup.enter.native="setItem"
      >
        <el-button
          slot="append"
          icon="el-icon-search"
          @click="setItem"
        />
      </el-input>
    </p>
    <p><el-button type="danger" @click="clear">Clear</el-button></p>

    <el-card v-if="showItem">
      <h3>Part Information</h3>
      <el-divider />
      <p><b>Manufacturer: </b>{{ partData.ManufacturerName }}</p>

      <p><b>Part Number: </b>
        <router-link
          :to="'/mfrParts/partView/' + partData.ManufacturerPartId"
          class="link-type"
        >
          {{ partData.ManufacturerPartNumber }}
        </router-link>
      </p>
      <el-divider />
      <h4>Production Parts:</h4>
      <el-table :data="productionPartData" style="width: 100%">
        <el-table-column prop="PartNo" label="Part No" sortable width="100">
          <template slot-scope="{ row }">
            <router-link
              :to="'/prodParts/prodPartView/' + row.PartNo"
              class="link-type"
            >
              <span>{{ row.PartNo }}</span>
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
      <p><b>Reserved Quantity: </b>{{ partData.ReservedQuantity }}</p>
      <p><b>Last Counted: </b>{{ partData.LastCountDate }}</p>
      <span><p><b>Stock Certainty Factor: </b>{{ stockAccuracy.CertaintyFactor }}</p>
        <el-rate
          v-model="stockAccuracy.CertaintyFactor*5"
          disabled
        />
      </span>
      <el-divider v-permission="['stock.add','stock.remove','stock.count']" />
      <h4 v-permission="['stock.add','stock.remove','stock.count']">Stock Movement</h4>

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

      <el-timeline reverse="true">
        <el-timeline-item
          v-for="(line, index) in history"
          :key="index"
          :color="line.color"
          :timestamp="line.Date"
        >
          {{ line.Description }}
          <template v-if="line.WorkOrderNo != NULL">
            <span>, Work Order: </span>
            <router-link
              :to="'/workOrder/workOrderView/' + line.WorkOrderNo"
              class="link-type"
            >
              <span>{{ line.WorkOrderNo }}</span>
            </router-link>
            {{ line.Title }}
          </template>

        </el-timeline-item>
      </el-timeline>

      <h3>Reservations</h3>
      <el-table :data="reservation" style="width: 100%">
        <el-table-column prop="WorkOrderNo" label="Work Order No" sortable width="150">
          <template slot-scope="{ row }">
            <router-link
              :to="'/workOrder/workOrderView/' + row.WorkOrderNo"
              class="link-type"
            >
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

    <el-card v-if="showItem ">
      <h3>Print Label</h3>
      <el-divider />
      <template v-if="label !== null">

        <el-select v-model="selectedLabelId">
          <el-option
            v-for="item in label"
            :key="Number(item.Id)"
            :label="item.Name"
            :value="Number(item.Id)"
          />
        </el-select>

        <el-select v-model="selectedPrinterId">
          <el-option
            v-for="item in printer"
            :key="Number(item.Id)"
            :label="item.Name"
            :value="Number(item.Id)"
          />
        </el-select>
        <el-button
          type="primary"
          style="margin-left: 20px"
          @click="printDialogVisible = true"
        >Print</el-button>
      </template>
    </el-card>

    <printDialog
      :visible.sync="printDialogVisible"
      :data="partData"
      @print="print"
    />

    <addStockDialog
      :visible.sync="addStockDialogVisible"
      :item="partData"
    />
    <removeStockDialog
      :visible.sync="removeStockDialogVisible"
      :item="partData"
    />
    <countStockDialog
      :visible.sync="countStockDialogVisible"
      :item="partData"
    />

  </div>
</template>

<script>
import permission from '@/directive/permission/index.js'

import * as labelTemplate from '@/utils/labelTemplate'
import * as defaultSetting from '@/utils/defaultSetting'
import requestBN from '@/utils/requestBN'

import printDialog from './components/printDialog'
import addStockDialog from './components/addStockDialog'
import removeStockDialog from './components/removeStockDialog'
import countStockDialog from './components/countStockDialog'

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
  components: { printDialog, addStockDialog, removeStockDialog, countStockDialog },
  directives: { permission },
  data() {
    return {
      inputStockId: null,
      history: null,
      showItem: false,
      partData: Object.assign({}, partDataEmpty),
      reservation: null,
      purchaseInformation: Object.assign({}, purchaseInformationData),
      stockAccuracy: Object.assign({}, stockAccuracyData),
      label: null,
      printer: {},
      selectedPrinterId: 0,
      selectedLabelId: 0,
      productionPartData: null,
      printDialogVisible: false,
      addStockDialogVisible: false,
      removeStockDialogVisible: false,
      countStockDialogVisible: false
    }
  },
  watch: {
    addStockDialogVisible: function() { this.loadItem() },
    removeStockDialogVisible: function() { this.loadItem() },
    countStockDialogVisible: function() { this.loadItem() }
  },
  mounted() {
    this.clear()

    if (this.$route.params.StockNo != null) {
      this.inputStockId = this.$route.params.StockNo
      this.loadItem()
    }

    this.getLabel()
    this.getPrinter()
    print.loadPrinter()
  },
  created() {
    // Why need to make a copy of this.$route here?
    // Because if you enter this page and quickly switch tag, may be in the execution of the setTagsViewTitle function, this.$route is no longer pointing to the current page
    // https://github.com/PanJiaChen/vue-element-admin/issues/1221
    this.tempRoute = Object.assign({}, this.$route)
  },
  methods: {
    setTagsViewTitle(title) {
      const route = Object.assign({}, this.tempRoute, {
        title: `${title}`
      })
      this.$store.dispatch('tagsView/updateVisitedView', route)
    },
    setPageTitle() {
      document.title = `${this.partData.Barcode} - ${this.partData.ManufacturerPartNumber}`
    },
    setItem() {
      this.$router.push('/stock/item/' + this.inputStockId)
    },
    loadItem() {
      this.getStockItem()
      this.getHistory()
      this.getReservation()
      this.getPurchaseInformation()
      this.getStockAccuracy()
      this.showItem = true
    },
    getStockItem() {
      requestBN({
        url: '/stock',
        methood: 'get',
        params: { StockNo: this.inputStockId }
      }).then(response => {
        if (response.error != null) {
          this.$message({
            showClose: true,
            message: response.error,
            duration: 0,
            type: 'error'
          })
        } else if (response.data.length === 0) {
          this.$message({
            showClose: true,
            message: 'Item dose not exist!',
            type: 'warning'
          })
        } else {
          this.partData = response.data[0]
          this.getProductionPartData()
          this.setTagsViewTitle(this.partData.Barcode)
          this.setPageTitle()
        }
      })
    },
    getHistory() {
      requestBN({
        url: '/stock/history',
        methood: 'get',
        params: { StockNo: this.inputStockId }
      }).then(response => {
        if (response.error != null) {
          this.$message({
            showClose: true,
            message: response.error,
            duration: 0,
            type: 'error'
          })
        } else {
          this.history = response.data
          this.history.forEach(element => {
            switch (element.Type) {
              case 'remove':
                element.color = '#67C23A'
                break
              case 'add':
                element.color = '#E6A23C'
                break
              case 'set':
              case 'create':
                element.color = '#409EFF'
                break
            }
          })
        }
      })
    },
    getStockAccuracy() {
      requestBN({
        url: '/stock/accuracy',
        methood: 'get',
        params: { StockNo: this.inputStockId }
      }).then(response => {
        if (response.error != null) {
          this.$message({
            showClose: true,
            message: response.error,
            duration: 0,
            type: 'error'
          })
        } else {
          this.stockAccuracy = response.data
        }
      })
    },
    getReservation() {
      requestBN({
        url: '/stock/reservation',
        methood: 'get',
        params: { StockNo: this.inputStockId }
      }).then(response => {
        if (response.error != null) {
          this.$message({
            showClose: true,
            message: response.error,
            duration: 0,
            type: 'error'
          })
        } else {
          this.reservation = response.data
        }
      })
    },
    getPurchaseInformation() {
      requestBN({
        url: '/stock/purchaseInformation',
        methood: 'get',
        params: { StockNo: this.inputStockId }
      }).then(response => {
        this.purchaseInformation = response.data
      })
    },
    getProductionPartData() {
      requestBN({
        url: '/productionPart',
        methood: 'get',
        params: { ManufacturerPartId: this.partData.ManufacturerPartId }
      }).then(response => {
        this.productionPartData = response.data
      })
    },
    clear() {
      this.inputStockId = null
      this.showItem = false
      this.$refs.itemNrInput.focus()
      this.printDialogVisible = false
      this.setTagsViewTitle('Stock Item')
    },
    getLabel() {
      requestBN({
        url: '/label',
        methood: 'get',
        params: { Tag: 'Stock' }
      }).then(response => {
        this.label = response.data
      })
    },
    getPrinter() {
      requestBN({
        url: '/printer',
        methood: 'get'
      }).then(response => {
        this.selectedPrinterId = defaultSetting.defaultSetting().StockLabelPrinter
        this.selectedLabelId = defaultSetting.defaultSetting().StockLabel

        this.printer = response.data
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

      requestBN({
        method: 'post',
        url: '/print/print',
        data: {
          Driver: 'raw',
          Language: labelTemplateObject.Language,
          PrinterId: this.selectedPrinterId,
          Data: labelCode
        }
      }).then(response => {

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
