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
      <el-form>
        <el-form-item label="Manufacturer:">
          {{ partData.ManufacturerName }}
        </el-form-item>
        <el-form-item label="Manufacturer Part Number:">
          <router-link
            :to="'/mfrParts/partView/' + partData.ManufacturerPartId"
            class="link-type"
          >
            {{ partData.ManufacturerPartNumber }}
          </router-link>
        </el-form-item>
        <el-form-item label="Supplier:">
          {{ partData.SupplierName }}
        </el-form-item>
        <el-form-item label="Supplier Part Number:">
          {{ partData.SupplierPartNumber }}
        </el-form-item>
        <el-form-item label="Order Reference:">
          {{ partData.OrderReference }}
        </el-form-item>
        <el-form-item label="Description:">
          {{ partData.Description }}
        </el-form-item>
        <el-form-item label="Quantity:">
          {{ partData.Quantity }}
        </el-form-item>
        <el-form-item label="Date Code:">
          {{ partData.DateCode }}
        </el-form-item>
        <el-form-item label="Location:">
          {{ partData.Location }}
        </el-form-item>
        <el-form-item label="Location Path:">
          {{ partData.LocationPath }}
        </el-form-item>
        <el-form-item label="Stock No:"> {{ partData.StockNo }}</el-form-item>
      </el-form>

      <el-radio-group v-model="labelTemplate">
        <el-radio-button :label="smallLabel">Small Label</el-radio-button>
        <el-radio-button :label="largeLabel">Large Label</el-radio-button>
      </el-radio-group>

      <el-button
        type="primary"
        style="margin-left: 20px"
        @click="printDialogVisible = true"
      >Print</el-button>

      <h3>Production Parts</h3>
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

    <printDialog
      :visible.sync="printDialogVisible"
      :data="partData"
      @print="print"
    />

  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'
import * as print from '@/utils/printLabel'
import printDialog from './components/printDialog'

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
  components: { printDialog },
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
      printDialogVisible: false
    }
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
    setTagsViewTitle() {
      const route = Object.assign({}, this.tempRoute, {
        title: `${this.partData.Barcode}`
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
          this.setTagsViewTitle()
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
        $PartNo: printData.PartNo,
        $Description: printData.Description
      }

      print.printLabel(this.labelTemplate.Code, labelData)
    }
  }
}
</script>
