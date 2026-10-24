<template>
  <div class="app-container">
    <h1>{{ partData.ManufacturerName }} - {{ partData.DisplayPartNumber }}</h1>
    <!--<h2>{{ partData.Description }} </h2>-->
    <template v-if="partData.SeriesId != null">
      <p><b>Series:</b>
        <router-link :to="'/manufacturerPart/series/item/' + partData.SeriesId" class="link-type">
          <span> {{ partData.SeriesTitle }}</span> - {{ partData.SeriesDescription }}
        </router-link>
      </p>
    </template>

    <!--<el-tabs type="card">
      <el-tab-pane label="Engineering">TBD Engineering</el-tab-pane>
      <el-tab-pane label="Purchasing">TBD Purchasing</el-tab-pane>
      <el-tab-pane label="Production">TBD Production</el-tab-pane>
      <el-tab-pane label="Stock">TBD Stock</el-tab-pane>
    </el-tabs>-->

    <el-card>
      <h3>Part Characteristics</h3>
      <template v-permission="['ManufacturerPart_Edit']">
        <el-button
          type="primary"
          icon="el-icon-edit"
          circle
          @click="showPartCharacteristicsDialog()"
        />
      </template>
      <p><b>Class: </b>{{ partData.PartClassPath }}</p>
      <p><b>Package: </b>{{ partData.Package }}</p>
      <el-table
        :data="partData.Attribute"
        style="width: 100%"
      >
        <el-table-column prop="Name" width="200px" />
        <el-table-column prop="Value.Minimum" label="Minimum" align="center" />
        <el-table-column prop="Value.Typical" label="Typical" align="center" />
        <el-table-column prop="Value.Maximum" label="Maximum" align="center" />
        <el-table-column prop="Symbol" label="Unit" />
      </el-table>
    </el-card>

    <el-card>
      <h3>Part Number</h3>
      <p><b>Part Number Template:</b> {{ partData.PartNumber }}</p>
      <p><b>Part Number Description:</b> {{ partData.PartNumberDescription }}</p>
      <el-table
        :data="partData.PartNumberItem"
        style="width: 100%"
        row-key="key"
        :tree-props="{ children: 'ProductionPart' }"
      >
        <el-table-column label="Manufacturer Part Number" sortable width="250">
          <template slot-scope="{ row }">
            <router-link :to="'/manufacturerPart/partNumber/item/' + row.ManufacturerPartNumberId" class="link-type">
              <span>{{ row.ManufacturerPartNumber }}</span>
            </router-link>
          </template>
        </el-table-column>
        <el-table-column label="Production Part Number" sortable width="250">
          <template slot-scope="{ row }">
            <router-link
              :to="'/productionPart/item/' + row.ProductionPartItemCode"
              class="link-type"
            >
              <span>{{ row.ProductionPartItemCode }}</span>
            </router-link>
          </template>
        </el-table-column>
        <el-table-column prop="ProductionPartDescription" label="Description" />
      </el-table>
    </el-card>

    <el-card>
      <h3>Documents</h3>
      <editDocumentsList
        attach="ManufacturerPartItemDocument"
        :barcode="partData.PartId"
        @change="getManufacturerPartItem()"
      />
      <documentsList :documents="partData.Documents" />
    </el-card>

    <el-card>
      <h3>Stock</h3>

      <el-checkbox v-model="filterEmptyStock" @change="getStockItems()">Hide empty (Quantity = 0)</el-checkbox>
      <el-table :data="stockData" style="width: 100%">
        <el-table-column prop="ItemCode" label="Stock Code" width="150" sortable>
          <template slot-scope="{ row }">
            <router-link :to="'/stock/item/' + row.ItemCode" class="link-type">
              <span>{{ row.ItemCode }}</span>
            </router-link>
          </template>
        </el-table-column>
        <el-table-column prop="ManufacturerPartNumber" label="Manufacturer Part Number" sortable />
        <el-table-column prop="Quantity" label="Quantity" width="150" sortable />
        <el-table-column prop="LocationPath" label="Location Path" />
        <el-table-column prop="LocationName" label="Location" width="150">
          <template slot-scope="{ row }">
            <router-link :to="'/location/item/' + row.LocationCode" class="link-type">
              <span>{{ row.LocationName }}</span>
            </router-link>
          </template>
        </el-table-column>
      </el-table>

      <p>
        <b>Total Stock Quantity:</b>
        {{ totalStockQuantity }}
      </p>
    </el-card>

    <el-card>
      <h3>Purchase Order</h3>
      <el-table
        :data="purchaseOrderData"
        style="width: 100%; margin-top:10px"
      >
        <el-table-column prop="ItemCode" label="Number" width="150" sortable>
          <template slot-scope="{ row }">
            <router-link :to="'/purchasing/edit/' + row.ItemCode" class="link-type">
              <span>{{ row.ItemCode }}</span>
            </router-link>
          </template>
        </el-table-column>
        <el-table-column prop="Title" label="Title" sortable />
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
        <el-table-column prop="Sku" label="Sku" sortable />
        <el-table-column prop="Quantity" label="Quantity" sortable width="120" />
        <el-table-column prop="Price" label="Price" sortable width="100" />
        <el-table-column prop="Status" label="Status" sortable width="100" />
      </el-table>
    </el-card>

    <partCharacteristicsDialog
      :part-id="partData.PartId"
      :visible.sync="partCharacteristicsDialogVisible"
      @change="getManufacturerPartItem()"
    />

  </div>
</template>

<script>

import ManufacturerPart from '@/api/manufacturerPart'
const manufacturerPart = new ManufacturerPart()

import documentsList from '@/views/document/components/documentsList'
import editDocumentsList from '@/views/document/components/editDocumentsList'

import partCharacteristicsDialog from './components/partCharacteristicsDialog'

import Stock from '@/api/stock'
const stock = new Stock()

export default {
  name: 'PartSeriesBrowser',
  components: { partCharacteristicsDialog, documentsList, editDocumentsList },
  data() {
    return {
      partCharacteristicsDialogVisible: false,
      loading: true,
      partData: {},
      purchaseOrderData: [],
      filterEmptyStock: true,
      stockData: [],
      totalStockQuantity: 0
    }
  },
  mounted() {
    this.getManufacturerPartItem()
  },
  created() {
    // Why need to make a copy of this.$route here?
    // Because if you enter this page and quickly switch tag, may be in the execution of the setTagsViewTitle function, this.$route is no longer pointing to the current page
    // https://github.com/PanJiaChen/vue-element-admin/issues/1221
    this.tempRoute = Object.assign({}, this.$route)
  },
  methods: {
    setTitle() {
      const title = this.partData.ManufacturerName + ' - ' + this.partData.DisplayPartNumber
      const route = Object.assign({}, this.tempRoute, {
        title: title
      })
      this.$store.dispatch('tagsView/updateVisitedView', route)
      document.title = title
    },
    showPartCharacteristicsDialog() {
      this.partCharacteristicsDialogVisible = true
    },
    getManufacturerPartItem() {
      manufacturerPart.item.get(this.$route.params.ManufacturerPartItemId).then(response => {
        // Add key needed for table rendering
        let key = 1
        response.PartNumberItem.forEach(element => {
          element.key = key
          key++
          if (element.ProductionPart.length > 1) {
            element.ProductionPart.forEach(element2 => {
              element2.key = key
              key++
              this.purchaseOrderData.push(...element2.PurchaseOrder)
            })
          } else if (element.ProductionPart.length > 0) {
            const part = element.ProductionPart[0]
            element.ProductionPartItemCode = part.ProductionPartItemCode
            element.ProductionPartDescription = part.ProductionPartDescription
            element.ManufacturerPartNumberDescription = part.ManufacturerPartNumberDescription
            element.ProductionPart = []
            this.purchaseOrderData.push(...part.PurchaseOrder)
          }
        })
        this.partData = response
        this.loading = false
        this.partData.Attribute.forEach(element => {
          const valArr = { Minimum: null, Maximum: null, Typical: null }
          if (typeof element.Value !== 'object') {
            valArr.Typical = element.Value
            element.Value = valArr
          }
        })

        this.setTitle()
        this.getStockItems()
      })
    },
    getStockItems() {
      stock.search(this.filterEmptyStock, null, null, this.partData.PartId).then(response => {
        this.stockData = response
        this.loading = false

        this.totalStockQuantity = this.stockData.reduce((accumulator, currentValue) => {
          return accumulator + currentValue.Quantity
        }, 0)
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
