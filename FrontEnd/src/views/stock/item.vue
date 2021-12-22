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
        @keyup.enter.native="loadItem"
      >
        <el-button
          slot="append"
          icon="el-icon-search"
          @click="loadItem"
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
      <h3>Supplier Information</h3>
      <el-divider />
      <p><b>Supplier: </b>{{ partData.SupplierName }}</p>
      <p><b>Part Number: </b>{{ partData.SupplierPartNumber }}</p>
      <p><b>OrderReference: </b>{{ partData.OrderReference }}</p>
    </el-card>

    <el-card v-if="showItem">
      <h3>Stock Information {{ partData.Barcode }}</h3>
      <el-divider />
      <p><b>Location: </b>{{ partData.Location }}</p>
      <p><b>Location Path: </b>{{ partData.LocationPath }}</p>
      <p><b>Quantity: </b>{{ partData.Quantity }}</p>
      <p><b>Last Counted: </b>{{ partData.LastCountDate }}</p>
      <el-divider  v-permission="['stock.add','stock.remove','stock.count']"/>
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
        </el-timeline-item>
      </el-timeline>
    </el-card>

    <el-card v-if="showItem">
      <h3>Print Label</h3>
      <el-divider />
      <el-radio-group v-model="labelTemplate">
        <el-radio-button :label="smallLabel">Small Label</el-radio-button>
        <el-radio-button :label="largeLabel">Large Label</el-radio-button>
      </el-radio-group>

      <el-button
        type="primary"
        style="margin-left: 20px"
        @click="printDialogVisible = true"
      >Print</el-button>

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

import requestBN from '@/utils/requestBN'
import * as print from '@/utils/printLabel'
import printDialog from './components/printDialog'
import addStockDialog from './components/addStockDialog'
import removeStockDialog from './components/removeStockDialog'
import countStockDialog from './components/countStockDialog'

const returnData = {
  StockId: '',
  Manufacturer: '',
  ManufacturerPartNumber: '',
  Date: '',
  Quantity: '',
  Location: '',
  Barcode: ''
}

export default {
  name: 'LocationAssignment',
  components: { printDialog, addStockDialog, removeStockDialog, countStockDialog  },
  directives: { permission },
  data() {
    return {
      inputStockId: null,
      history: null,
      showItem: false,
      partData: Object.assign({}, returnData),
      labelTemplate: null,
      smallLabel: null,
      largeLabel: null,
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
    loadItem() {
      this.getStockItem()
      this.getHistory()
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
        } else if (response.data.length == 0) {
          this.$message({
            showClose: true,
            message: 'Item dose not exist!',
            type: 'warning'
          })
        } else {
          this.partData = response.data[0]
          this.getProductionPartData()
          this.setTagsViewTitle(this.partData.Barcode)
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
        params: { Id: 1 }
      }).then(response => {
        this.smallLabel = response.data[0]
      })

      requestBN({
        url: '/label',
        methood: 'get',
        params: { Id: 2 }
      }).then(response => {
        this.largeLabel = response.data[0]
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

      print.printLabel(this.labelTemplate.Code, labelData)
    }
  }
}
</script>

<style>
.el-card {
  margin-top: 20px;
  }
</style>
