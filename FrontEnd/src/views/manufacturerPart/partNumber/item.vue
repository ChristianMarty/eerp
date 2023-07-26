<template>
  <div class="app-container">
    <h1> {{ data.ManufacturerName }} - {{ data.PartNumber }}</h1>
    <p><b>Part:</b>
      <router-link
        :to="'/manufacturerPart/item/' + data.PartId"
        class="link-type"
      >
        <span>{{ data.NumberTemplate }}</span>
      </router-link>
    </p>
    <p><b>Series:</b>
      <router-link :to="'/manufacturerPart/series/item/' + data.SeriesId" class="link-type">
        <span> {{ data.SeriesTitle }}</span> - {{ data.SeriesDescription }}
      </router-link>
    </p>
    <p><b>Description:</b> {{ data.PartNumberDescription }}</p>
    <p><b>Package: </b>{{ data.PackageName }}</p>

    <el-tabs
      type="card"
    >
      <el-tab-pane label="Part">
        <h3>Production Parts</h3>
        <el-table :data="productionPartData" style="width: 100%">
          <el-table-column prop="ProductionPartNumber" label="Part Number" sortable width="150">
            <template slot-scope="{ row }">
              <router-link
                :to="'/productionPart/item/' + row.ProductionPartNumber"
                class="link-type"
              >
                <span>{{ row.ProductionPartNumber }}</span>
              </router-link>
            </template>
          </el-table-column>
          <el-table-column prop="Description" label="Description" sortable />
        </el-table>

        <el-divider />
        <h3>Stock</h3>
        <el-checkbox v-model="fliterEmptyStock" @change="getStockItems()">Hide empty (Quantity = 0)</el-checkbox>
        <el-table :data="stockData" style="width: 100%">
          <el-table-column prop="StockBarcode" label="Stock Code" width="150" sortable>
            <template slot-scope="{ row }">
              <router-link :to="'/stock/item/' + row.StockNumber" class="link-type">
                <span>{{ row.StockBarcode }}</span>
              </router-link>
            </template>
          </el-table-column>
          <el-table-column prop="ManufacturerName" label="Manufacturer" width="200" sortable />
          <el-table-column prop="ManufacturerPartNumber" label="Part Number" width="200" sortable />
          <el-table-column prop="Date" label="Date" sortable />
          <el-table-column prop="Quantity" label="Quantity" sortable />
          <el-table-column prop="Location" label="Location" sortable />
        </el-table>

        <el-divider />
        <h3>Purchase Orders</h3>
        <p>
          <b>Number of orders:</b>
          {{ purchaseOrderData.Data.length }}
        </p>
        <el-table :data="purchaseOrderData.Data" style="width: 100%; margin-top:10px">
          <el-table-column label="Supplier" width="150" sortable>
            <template slot-scope="{ row }">
              <router-link
                :to="'/vendor/view/' + row.SupplierId"
                class="link-type"
              >
                <span>{{ row.SupplierName }}</span>
              </router-link>
            </template>
          </el-table-column>
          <el-table-column prop="PoNo" label="PO Number" width="150" sortable>
            <template slot-scope="{ row }">
              <router-link :to="'/purchasing/edit/' + row.PurchaseOrderNumber" class="link-type">
                <span>{{ row.PurchaseOrderBarcode }}</span>
              </router-link>
            </template>
          </el-table-column>
          <el-table-column prop="Title" label="PO Title" sortable />
          <el-table-column prop="Sku" label="Sku" sortable />
          <el-table-column prop="Quantity" label="Quantity" sortable width="120" />
          <el-table-column prop="Price" label="Price" sortable width="100" />
          <el-table-column prop="Status" label="Status" sortable width="100" />
        </el-table>

        <p>
          <b>Total Order Quantity:</b>
          {{ purchaseOrderData.Statistics.Quantity.Ordered }}
        </p>
        <p>
          <b>Pending Order Quantity:</b>
          {{ purchaseOrderData.Statistics.Quantity.Pending }}
        </p>

        <el-divider />
        <h3>Supplier Parts</h3>
        <p><b>Number of supplier parts:</b> {{ supplierPartData.length }}</p>
        <el-table
          :data="supplierPartData"
          style="width: 100%; margin-top:10px"
        >
          <el-table-column label="Supplier" width="150" sortable>
            <template slot-scope="{ row }">
              <router-link
                :to="'/vendor/view/' + row.SupplierId"
                class="link-type"
              >
                <span>{{ row.SupplierName }}</span>
              </router-link>
            </template>
          </el-table-column>
          <el-table-column label="Supplier Part Number" sortable>
            <template slot-scope="{ row }">
              <a :href="row.SupplierPartLink" target="blank">
                {{ row.SupplierPartNumber }}
              </a>
            </template>
          </el-table-column>
        </el-table>

      </el-tab-pane>
      <el-tab-pane label="Availability">
        <el-checkbox v-model="availabilityAuthorizedOnly">Authorized Only</el-checkbox>
        <el-checkbox v-model="availabilityBrokers">Include Brokers</el-checkbox>
        <el-button type="primary" @click="getAvailabilityData">Load Data</el-button>
        <template v-if="availabilityData != null">
          <p>
            Data provided by Octopart, {{ availabilityData.Timestamp }}
          </p>
          <el-checkbox v-model="expandedAllRows" @change="chnageAvailabilityExpandedRows()">Expanded all rows</el-checkbox>
          <el-table
            v-loading="availabilityLoading"
            element-loading-text="Loading Availability Data"
            :data="availabilityData.Data"
            :expand-row-keys="availabilityExpandedRows"
            border
            style="width: 100%; margin-top:10px"
            row-key="rowKey"
            :tree-props="{ children: 'Prices' }"
          >
            <el-table-column prop="VendorName" label="Distributor" width="250" sortable>
              <template slot-scope="{ row }">
                <template v-if="row.VendorId">
                  <router-link :to="'/vendor/view/' + row.VendorId" class="link-type">
                    <span>{{ row.VendorName }}</span>
                  </router-link>
                </template>
                <template v-else>
                  <span>{{ row.VendorName }}</span>
                </template>
              </template>
            </el-table-column>
            <el-table-column prop="SKU" label="SKU" sortable>
              <template slot-scope="{ row }">
                <span>{{ row.SKU }}</span>
                <template v-if="row.SKU">
                  <a :href="row.URL" target="blank" class="link-type">
                    <el-button type="primary" icon="el-icon-shopping-cart-full" style="float: right;" size="mini">Buy</el-button>
                  </a>
                </template>
              </template>
            </el-table-column>
            <el-table-column prop="Stock" label="Stock" width="100" sortable />
            <el-table-column
              prop="MinimumOrderQuantity"
              label="MOQ"
              width="100"
              sortable
            />
            <el-table-column prop="LeadTime" label="LeadTime" width="120" sortable />
            <el-table-column prop="Price" label="Price" width="120" sortable />
            <el-table-column prop="Quantity" label="Quantity" width="120" sortable />
            <el-table-column prop="Currency" label="Currency" width="120" sortable />
          </el-table>
        </template>
      </el-tab-pane>
    </el-tabs>

  </div>
</template>

<script>
import ManufacturerPart from '@/api/manufacturerPart'
const manufacturerPart = new ManufacturerPart()

import SupplierPart from '@/api/supplierPart'
const supplierPart = new SupplierPart()

import ProductionPart from '@/api/productionPart'
const productionPart = new ProductionPart()

import Purchase from '@/api/purchase'
const purchase = new Purchase()

import Stock from '@/api/stock'
const stock = new Stock()

export default {
  name: 'PartSeriesItem',

  data() {
    return {
      loading: true,
      availabilityLoading: false,
      availabilityExpandedRows: [],
      expandedAllRows: false,
      supplierPartData: [],
      purchaseOrderData: [],
      productionPartData: [],
      stockData: [],
      fliterEmptyStock: true,

      data: {},
      availabilityData: null,
      availabilityAuthorizedOnly: true,
      availabilityBrokers: false
    }
  },
  mounted() {
    this.getManufacturerPartNumberItem()
  },
  created() {
    // Why need to make a copy of this.$route here?
    // Because if you enter this page and quickly switch tag, may be in the execution of the setTagsViewTitle function, this.$route is no longer pointing to the current page
    // https://github.com/PanJiaChen/vue-element-admin/issues/1221
    this.tempRoute = Object.assign({}, this.$route)
  },
  methods: {
    setTitle() {
      const route = Object.assign({}, this.tempRoute, {
        title: this.data.ManufacturerName + ' - ' + this.data.PartNumber
      })
      this.$store.dispatch('tagsView/updateVisitedView', route)
      document.title = this.data.ManufacturerName + ' - ' + this.data.PartNumber
    },
    onTabChnage(TabPaneName) {
      console.log(TabPaneName)
    },
    getManufacturerPartNumberItem() {
      manufacturerPart.PartNumber.get(this.$route.params.ManufacturerPartNumberId).then(response => {
        this.data = response
        this.getSupplierPart(this.data.PartNumberId)
        this.getPurchaseOrder(this.data.PartNumberId)
        this.getProductionPart(this.data.PartNumberId)
        this.getStockItems()
        this.setTitle()
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    getSupplierPart(ManufacturerPartNumberId) {
      supplierPart.search(null, null, ManufacturerPartNumberId).then(response => {
        this.supplierPartData = response
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    getPurchaseOrder(ManufacturerPartNumberId) {
      purchase.partPurchase(ManufacturerPartNumberId).then(response => {
        this.purchaseOrderData = response
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    getProductionPart(ManufacturerPartNumberId) {
      productionPart.search(null, ManufacturerPartNumberId).then(response => {
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
    getStockItems() {
      stock.search(this.fliterEmptyStock, null, this.data.PartNumberId).then(response => {
        this.stockData = response
        this.loading = false
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    getAvailabilityData() {
      this.availabilityLoading = true
      manufacturerPart.PartNumber.availability(this.$route.params.ManufacturerPartNumberId, this.availabilityAuthorizedOnly, this.availabilityBrokers).then(response => {
        this.availabilityData = this.processAvailabilityData(response)
        this.availabilityLoading = false
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    processAvailabilityData(data) {
      let rowKey = 1
      data.Data.forEach(element => {
        element.rowKey = String(rowKey)
        rowKey++
        if (element.Prices.length === 0) {
          delete element.Values
        } else if (element.Prices.length === 1) {
          element.Quantity = element.Prices[0].Quantity
          element.Currency = element.Prices[0].Currency
          element.Price = element.Prices[0].Price
          delete element.Prices
        } else {
          let rowKey2 = 1
          element.Prices.forEach(element2 => {
            element2.rowKey = element.rowKey + '.' + String(rowKey2)
            rowKey2++
          })
        }
      })
      return data
    },
    chnageAvailabilityExpandedRows(state) {
      if (this.expandedAllRows) {
        this.availabilityData.Data.forEach(element => {
          this.availabilityExpandedRows.push(element.rowKey)
        })
      } else {
        this.availabilityExpandedRows.length = 0
      }
    }

  }
}
</script>
