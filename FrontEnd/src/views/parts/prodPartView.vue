<template>
  <div class="app-container">
    <h1>{{ $route.params.partNo }} - {{ partData.Description }}</h1>

    <h2>Manufacturer Parts</h2>
    <el-table
      :data="partLookup"
      style="width: 100%;margin-bottom: 20px;"
      border
    >
      <el-table-column
        prop="ManufacturerName"
        label="Manufacturer"
        sortable
        width="200"
      />
      <el-table-column
        prop="ManufacturerPartNumber"
        label="Part Number"
        sortable
        width="200"
      />
      <el-table-column prop="Description" label="Description" />
    </el-table>

    <h2>Stock List</h2>
    <el-table
      :data="partData.ManufacturerParts"
      style="width: 100%;margin-bottom: 20px;"
      row-key="PartId"
      border
      :tree-props="{ children: 'Stock' }"
      default-expand-all
    >
      <el-table-column
        prop="ManufacturerName"
        label="Manufacturer"
        sortable
        width="200"
      />
      <el-table-column
        prop="ManufacturerPartNumber"
        label="Part Number"
        sortable
        width="200"
      >
        <template slot-scope="{ row }">
          <router-link
            :to="'/mfrParts/partView/' + row.PartId"
            class="link-type"
          >
            <span>{{ row.ManufacturerPartNumber }}</span>
          </router-link>
        </template>
      </el-table-column>
      <el-table-column prop="StockNo" label="Stock No">
        <template slot-scope="{ row }">
          <router-link :to="'/stock/item/' + row.StockNo" class="link-type">
            <span>{{ row.StockNo }}</span>
          </router-link>
        </template>
      </el-table-column>
      <el-table-column prop="Date" label="Date" />
      <el-table-column prop="Quantity" label="Quantity" />
      <el-table-column prop="LocationName" label="Location" />
    </el-table>

    <p><b>Total Stock Quantety:</b> {{ partData.TotalStockQuantity }}</p>

    <h2>Stock Notification</h2>
    <table>
      <tr>
        <td><b>Minimum:</b></td>
        <td>{{ partData.StockMinimum }}</td>
      </tr>
      <tr>
        <td><b>Maximum:</b></td>
        <td>{{ partData.StockMaximum }}</td>
      </tr>
      <tr>
        <td><b>Warning:</b></td>
        <td>{{ partData.StockWarning }}</td>
      </tr>
    </table>

    <h2>Lead Time Reference</h2>
    <el-table
      :data="leadTime.Data"
      style="width: 100%; margin-top:10px"
    >
      <el-table-column
        prop="LeadTime"
        label="Lead Time"
        sortable
        width="120"
      />
      <el-table-column
        prop="Weight"
        label="Weight"
        sortable
        width="100"
      />
      <el-table-column
        prop="InformationSource"
        label="Information Source"
        sortable
        width="200"
      />
      <el-table-column
        prop="InformationDate"
        label="Information Date"
        sortable
        width="200"
      />
      <el-table-column
        prop="SuppierName"
        label="Suppier"
        sortable
        width="100"
      >
        <template slot-scope="{ row }">
          <router-link
            :to="'/vendor/view/' + row.SuppierId"
            class="link-type"
          >
            <span>{{ row.SuppierId }}</span>
          </router-link>
        </template>
      </el-table-column>
      <el-table-column
        prop="ManufacturerPart"
        label="Manufacturer Part"
        sortable
        width="200"
      >
        <template slot-scope="{ row }">
          <router-link
            :to="'/mfrParts/partView/' + row.ManufacturerPartId"
            class="link-type"
          >
            <span>{{ row.ManufacturerPart }}</span>
          </router-link>
        </template>
      </el-table-column>
      <el-table-column
        prop="Note"
        label="Note"
      />
    </el-table>
    <p />
    <!--  <table>
      <tr>
        <td><b>Minimum:</b></td>
        <td>{{ leadTime.Statistics.Minimum }}</td>
      </tr>
      <tr>
        <td><b>Maximum:</b></td>
        <td>{{ leadTime.Statistics.Maximum }}</td>
      </tr>
      <tr>
        <td><b>Average:</b></td>
        <td>{{ leadTime.Statistics.Average }}</td>
      </tr>
      <tr>
        <td><b>Weighted Average:</b></td>
        <td>{{ leadTime.Statistics.WeightedAverage }}</td>
      </tr>
    </table> -->

    <h1>Question: Should price and lead time be combined? -> Rename to Quotation?</h1>

    <h2>Price Reference</h2>
    <el-table
      :data="price.Data"
      style="width: 100%; margin-top:10px"
    >
      <el-table-column
        prop="Price"
        label="Price"
        sortable
        width="120"
      />
      <el-table-column
        prop="MinimumOrderQuantity"
        label="MOQ"
        sortable
        width="100"
      />
      <el-table-column
        prop="IncrementalOrderQuantity"
        label="IOQ"
        sortable
        width="100"
      />
      <el-table-column
        prop="Weight"
        label="Weight"
        sortable
        width="100"
      />
      <el-table-column
        prop="InformationSource"
        label="Information Source"
        sortable
        width="200"
      />
      <el-table-column
        prop="InformationDate"
        label="Information Date"
        sortable
        width="200"
      />
      <el-table-column
        prop="SuppierName"
        label="Suppier"
        sortable
        width="100"
      >
        <template slot-scope="{ row }">
          <router-link
            :to="'/vendor/view/' + row.SuppierId"
            class="link-type"
          >
            <span>{{ row.SuppierId }}</span>
          </router-link>
        </template>
      </el-table-column>

      <el-table-column
        prop="ManufacturerPart"
        label="Manufacturer Part"
        sortable
        width="200"
      >
        <template slot-scope="{ row }">
          <router-link
            :to="'/mfrParts/partView/' + row.ManufacturerPartId"
            class="link-type"
          >
            <span>{{ row.ManufacturerPart }}</span>
          </router-link>
        </template>
      </el-table-column>
      <el-table-column
        prop="Note"
        label="Note"
      />
    </el-table>
    <p />
    <!--<table>
      <tr>
        <td><b>Minimum:</b></td>
        <td>{{ price.Statistics.Minimum }}</td>
      </tr>
      <tr>
        <td><b>Maximum:</b></td>
        <td>{{ price.Statistics.Maximum }}</td>
      </tr>
      <tr>
        <td><b>Average:</b></td>
        <td>{{ price.Statistics.Average }}</td>
      </tr>
      <tr>
        <td><b>Weighted Average:</b></td>
        <td>{{ price.Statistics.WeightedAverage }}</td>
      </tr>
    </table>-->

    <h2>Purchase Orders</h2>

    <el-table
      :data="purchaseOrder.Data"
      style="width: 100%; margin-top:10px"
    >
      <el-table-column prop="PoNo" label="PO Number" width="150" sortable>
        <template slot-scope="{ row }">
          <router-link :to="'/purchasing/edit/' + row.PoNo" class="link-type">
            <span>PO-{{ row.PoNo }}</span>
          </router-link>
        </template>
      </el-table-column>
      <el-table-column prop="Title" label="PO Title" sortable />
      <el-table-column prop="Sku" label="Sku" sortable />
      <el-table-column
        prop="Quantity"
        label="Quantity"
        sortable
        width="120"
      />
      <el-table-column
        prop="Price"
        label="Price"
        sortable
        width="100"
      />
      <el-table-column
        prop="Status"
        label="Status"
        sortable
        width="100"
      />

    </el-table>
    <p />
    <table>
      <tr>
        <td><b>Total Order Quantity:</b></td>
        <td>{{ purchaseOrder.Statistics.Quantity.Ordered }}</td>
      </tr>
      <tr>
        <td><b>Pending Order Quantity:</b></td>
        <td>{{ purchaseOrder.Statistics.Quantity.Pending }}</td>
      </tr>
      <tr>
        <td><b>Received Order Quantity:</b></td>
        <td>{{ purchaseOrder.Statistics.Quantity.Received }}</td>
      </tr>
      <tr>
        <td><b>Minimum Price:</b></td>
        <td>{{ purchaseOrder.Statistics.Price.Minimum }}</td>
      </tr>
      <tr>
        <td><b>Maximum Price:</b></td>
        <td>{{ purchaseOrder.Statistics.Price.Maximum }}</td>
      </tr>
      <tr>
        <td><b>Average:</b></td>
        <td>{{ purchaseOrder.Statistics.Price.Average }}</td>
      </tr>
      <tr>
        <td><b>Weighted Average:</b></td>
        <td>{{ purchaseOrder.Statistics.Price.WeightedAverage }}</td>
      </tr>
    </table>

  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'

import ProductionPart from '@/api/productionPart'
const productionPart = new ProductionPart()

export default {
  name: 'ProdPartBrowser',
  data() {
    return {
      partData: null,
      purchaseOrder: null,
      leadTime: null,
      price: null
    }
  },
  mounted() {
    this.getPartData()
    this.setTagsViewTitle()
    this.setPageTitle()
    this.getPartLookup()
  },
  created() {
    // Why need to make a copy of this.$route here?
    // Because if you enter this page and quickly switch tag, may be in the execution of the setTagsViewTitle function, this.$route is no longer pointing to the current page
    // https://github.com/PanJiaChen/vue-element-admin/issues/1221
    this.tempRoute = Object.assign({}, this.$route)
  },
  methods: {
    getPartData() {
      productionPart.item(this.$route.params.partNo).then(response => {
        this.partData = response
        this.getPurchasOrder()
        this.getLeadTime()
        this.getPrice()
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    getPartLookup() {
      this.partLookup = []
      /* requestBN({
        url: '/productionPart/partLookup',
        methood: 'get',
        params: { ProductionPartNumber: this.$route.params.partNo }
      }).then(response => {
        this.partLookup = response.data
      })*/
    },
    getLeadTime() {
      this.leadTime = []
      /* requestBN({
        url: '/productionPart/leadTime',
        methood: 'get',
        params: { ProductionPartNumber: this.$route.params.partNo }
      }).then(response => {
        this.leadTime = response.data
      })*/
    },
    getPrice() {
      this.price = []
      /* requestBN({
        url: '/productionPart/price',
        methood: 'get',
        params: { ProductionPartNumber: this.$route.params.partNo }
      }).then(response => {
        this.price = response.data
      })*/
    },
    getPurchasOrder() {
      requestBN({
        url: '/purchasing/partPurchase',
        methood: 'get',
        params: { ProductionPartNumber: this.$route.params.partNo }
      }).then(response => {
        this.purchaseOrder = response.data
      })
    },
    setTagsViewTitle() {
      const route = Object.assign({}, this.tempRoute, {
        title: `${this.$route.params.partNo}`
      })
      this.$store.dispatch('tagsView/updateVisitedView', route)
    },
    setPageTitle() {
      document.title = `${this.$route.params.partNo}`
    }
  }
}
</script>

<style>
h2 {
  margin-top: 80px;
}
</style>

