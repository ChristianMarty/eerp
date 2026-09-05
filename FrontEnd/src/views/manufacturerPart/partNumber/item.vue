<template>
  <div class="app-container">
    <h1> {{ data.ManufacturerName }} - {{ data.PartNumber }}</h1>
    <p><b>Part:</b>
      <template v-if="data.PartId !== null">
        <router-link
          :to="'/manufacturerPart/item/' + data.PartId"
          class="link-type"
        >
          <span>{{ data.Number }}</span>
        </router-link>
      </template>
      <template v-else>
        <el-button
          type="primary"
          icon="el-icon-plus"
          circle
          @click="addManufacturerPart()"
        />
      </template>
    </p>
    <p><b>Series:</b>
      <router-link :to="'/manufacturerPart/series/item/' + data.SeriesId" class="link-type">
        <span> {{ data.SeriesTitle }}</span> - {{ data.SeriesDescription }}
      </router-link>
    </p>
    <p><b>Description:</b> {{ data.PartNumberDescription }}</p>
    <p><b>Package: </b>{{ data.PackageName }}</p>


    <h3>Production Parts</h3>
    <template v-permission="['ProductionPart_Edit']">
      <el-button
        type="primary"
        icon="el-icon-edit"
        circle
        @click="showProductionPartDialog()"
      />
    </template>
    <el-table :data="productionPartData" style="width: 100%">
      <el-table-column prop="ItemCode" label="Part Number" sortable width="150">
        <template slot-scope="{ row }">
          <router-link
            :to="'/productionPart/item/' + row.ItemCode"
            class="link-type"
          >
            <span>{{ row.ItemCode }}</span>
          </router-link>
        </template>
      </el-table-column>
      <el-table-column prop="Description" label="Description" sortable />
      <el-table-column prop="ApprovedUsage" label="Approved Usage" sortable width="200" />
    </el-table>

    <el-divider />
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
      <el-table-column prop="ManufacturerName" label="Manufacturer" width="200" sortable />
      <el-table-column prop="ManufacturerPartNumber" label="Part Number" sortable />
      <el-table-column prop="Date" label="Date" width="150" sortable />
      <el-table-column prop="Quantity" label="Quantity" width="150" sortable />
      <el-table-column prop="LocationName" label="Location" width="150">
        <template slot-scope="{ row }">
          <router-link :to="'/location/item/' + row.LocationCode" class="link-type">
            <span>{{ row.LocationName }}</span>
          </router-link>
        </template>
      </el-table-column>
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
      <el-table-column prop="ItemCode" label="PO Number" width="150" sortable>
        <template slot-scope="{ row }">
          <router-link :to="'/purchasing/edit/' + row.ItemCode" class="link-type">
            <span>{{ row.ItemCode }}</span>
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

    <productionPartDialog
      :manufacturer-part-id="data.PartNumberId"
      :visible.sync="productionPartDialogVisible"
      @change="getProductionPart(data.PartNumberId)"
    />

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

import productionPartDialog from './components/productionPartDialog'

export default {
  name: 'PartSeriesItem',
  components: { productionPartDialog },
  data() {
    return {
      loading: true,
      expandedAllRows: false,
      supplierPartData: [],
      purchaseOrderData: [],
      productionPartData: [],
      stockData: [],
      filterEmptyStock: true,
      data: {},
      productionPartDialogVisible: false
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
    showProductionPartDialog() {
      this.productionPartDialogVisible = true
    },
    getManufacturerPartNumberItem() {
      manufacturerPart.PartNumber.get(this.$route.params.ManufacturerPartNumberId).then(response => {
        this.data = response
        this.getSupplierPart(this.data.PartNumberId)
        this.getPurchaseOrder(this.data.PartNumberId)
        this.getProductionPart(this.data.PartNumberId)
        this.getStockItems()
        this.setTitle()
      })
    },
    getSupplierPart(ManufacturerPartNumberId) {
      supplierPart.search(null, null, ManufacturerPartNumberId).then(response => {
        this.supplierPartData = response
      })
    },
    getPurchaseOrder(ManufacturerPartNumberId) {
      purchase.partPurchase(ManufacturerPartNumberId).then(response => {
        this.purchaseOrderData = response
      })
    },
    getProductionPart(ManufacturerPartNumberId) {
      productionPart.search(null, ManufacturerPartNumberId).then(response => {
        this.productionPartData = response
      })
    },
    getStockItems() {
      stock.search(this.filterEmptyStock, null, this.data.PartNumberId).then(response => {
        this.stockData = response
        this.loading = false
      })
    },
    addManufacturerPart() {
      manufacturerPart.item.createFromPartNumber(this.data.PartNumberId).then(response => {
      })
    }
  }
}
</script>
