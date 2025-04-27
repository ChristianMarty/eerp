<template>
  <div class="app-container">
    <h1>{{ partData.ProductionPartBarcode }} - {{ partData.Description }}</h1>

    <el-tabs
      type="card"
    >
      <el-tab-pane label="Part">
        <h2>Manufacturer Parts</h2>
        <el-table
          :data="partData.ManufacturerPart"
          style="width: 100%;margin-bottom: 20px;"
          border
        >
          <el-table-column
            prop="ManufacturerName"
            label="Manufacturer"
            sortable
            width="220"
          >
            <template slot-scope="{ row }">
              <router-link
                :to="'/vendor/view/' + row.ManufacturerId"
                class="link-type"
              >
                <span>{{ row.ManufacturerName }}</span>
              </router-link>
            </template>
          </el-table-column>

          <el-table-column
            prop="ManufacturerPart"
            label="Part"
            sortable
            width="220"
          >
            <template slot-scope="{ row }">
              <router-link
                :to="'/manufacturerPart/item/' + row.ManufacturerPartId"
                class="link-type"
              >
                <span>{{ row.ManufacturerPartNumberTemplate }}</span>
              </router-link>
            </template>
          </el-table-column>

          <el-table-column
            prop="ManufacturerPartNumber"
            label="Part Number"
            sortable
            width="220"
          >
            <template slot-scope="{ row }">
              <router-link
                :to="'/manufacturerPart/partNumber/item/' + row.ManufacturerPartNumberId"
                class="link-type"
              >
                <span>{{ row.ManufacturerPartNumber }}</span>
              </router-link>
            </template>
          </el-table-column>

          <el-table-column prop="Description" label="Description" />
        </el-table>

        <h3>Characteristics</h3>
        <el-table
          v-loading="loading"
          element-loading-text="Loading..."
          element-loading-spinner="el-icon-loading"
          :data="partData.Characteristics.Data"
          border
          style="width: 100%"
          :formatter="siRowFormater"
        >
          <el-table-column prop="PartNumber" label="Part" width="200" />
          <el-table-column
            v-for="attribute in partData.Characteristics.Attributes"
            :key="attribute.Name"
            :label="attribute.Name"
            :prop="attribute.Name"
          />
        </el-table>

        <h3>Stock</h3>
        <el-checkbox v-model="hideEmptyStock" @change="getStockItems()">Hide empty (Quantity = 0)</el-checkbox>
        <el-table
          :data="partData.Stock"
          style="width: 100%"
        >
          <el-table-column prop="ItemCode" label="Stock Code" width="150" sortable>
            <template slot-scope="{ row }">
              <router-link :to="'/stock/item/' + row.ItemCode" class="link-type">
                <span>{{ row.ItemCode }}</span>
              </router-link>
            </template>
          </el-table-column>
          <el-table-column prop="Date" label="Mfr. Date" sortable width="150" />
          <el-table-column prop="Lot" label="Lot" sortable width="150" />
          <el-table-column prop="Quantity" label="Quantity" sortable width="150" />
          <el-table-column prop="LocationName" label="Location" sortable />
          <!--<el-table-column prop="Certainty.LastStocktakingDate" label="Last Stocktaking" sortable width="170"/>-->
          <el-table-column prop="Certainty.DaysSinceStocktaking" label="Days Since Stocktaking" sortable width="210" />
          <el-table-column prop="Certainty.Factor" label="Stock Certainty" width="150" sortable>
            <template slot-scope="{ row }">
              <el-rate
                v-model="row.Certainty.Rating"
                disabled
              />
            </template>
          </el-table-column>
        </el-table>

        <p><b>Total Quantity:</b> {{ partData.TotalStockQuantity }}</p>
        <p><b>Total Certainty:</b> {{ partData.TotalStockCertainty }}</p>
        <p><b>Total Rating:</b> <el-rate v-model="partData.TotalStockRating" disabled /></p>

        <h3>Stock Notification</h3>
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

        <h2>Quotation</h2>
        <el-table
          :data="quotation.data"
          style="width: 100%; margin-top:10px"
        >
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
            prop="Note"
            label="Note"
          />
        </el-table>

        <h2>Purchase Orders</h2>

        <el-table
          :data="purchaseOrder.Data"
          style="width: 100%; margin-top:10px"
        >
          <el-table-column prop="ItemCode" label="PO Number" width="150" sortable>
            <template slot-scope="{ row }">
              <router-link :to="'/purchasing/edit/' + row.ItemCode" class="link-type">
                <span>{{ row.ItemCode }}</span>
              </router-link>
            </template>
          </el-table-column>
          <el-table-column prop="PurchaseDate" label="Purchase Date" width="150" sortable />
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

      </el-tab-pane>
      <el-tab-pane label="Availability">
        <availability :production-part-barcode="partData.ProductionPartBarcode" />
      </el-tab-pane>
    </el-tabs>
  </div>
</template>

<script>
import siFormatter from '@/utils/siFormatter'

import ProductionPart from '@/api/productionPart'
const productionPart = new ProductionPart()

import Purchase from '@/api/purchase'
const purchase = new Purchase()

import availability from './components/availability'

export default {
  name: 'ProdPartBrowser',
  components: { availability },
  data() {
    return {
      partData: null,
      hideEmptyStock: true,
      purchaseOrder: null,
      quotation: []
    }
  },
  mounted() {
    this.getPartData()
    this.setTitle()
  },
  created() {
    // Why need to make a copy of this.$route here?
    // Because if you enter this page and quickly switch tag, may be in the execution of the setTagsViewTitle function, this.$route is no longer pointing to the current page
    // https://github.com/PanJiaChen/vue-element-admin/issues/1221
    this.tempRoute = Object.assign({}, this.$route)
  },
  methods: {
    getStockItems() {
      this.getPartData()
    },
    getPartData() {
      productionPart.item(this.$route.params.productionPartNumber, this.hideEmptyStock).then(response => {
        this.partData = response
        this.getPurchaseOrder()
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    getPurchaseOrder() {
      purchase.productionPartPurchase(this.$route.params.productionPartNumber).then(response => {
        this.purchaseOrder = response
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    setTitle() {
      document.title = `${this.$route.params.productionPartNumber}`
      const route = Object.assign({}, this.tempRoute, {
        title: `${this.$route.params.productionPartNumber}`
      })
      this.$store.dispatch('tagsView/updateVisitedView', route)
    },
    siRowFormater(row, column, cellValue, index) {
      return siFormatter(cellValue, '')
    }
  }
}
</script>

<style>
h2 {
  margin-top: 80px;
}
</style>

