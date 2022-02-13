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
    <p><b>Stock Minimum:</b> {{ partData.StockMinimum }}</p>
    <p><b>Stock Warning:</b> {{ partData.StockWarning }}</p>
    <p><b>Stock Maximum:</b> {{ partData.StockMaximum }}</p>

    <h2>Purchase Orders</h2>

    <el-table
      :data="purchaseOrderData"
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
    <p><b>Total Order Quantity: </b>{{ purchaseOrder.TotalOrderQuantity }}</p>
    <p><b>Pending Order Quantity: </b>{{ purchaseOrder.PendingOrderQuantity }}</p>

  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'

export default {
  name: 'ProdPartBrowser',
  data() {
    return {
      partData: null,
      purchaseOrder: null,
      purchaseOrderData: null
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
      requestBN({
        url: '/productionPart/item',
        methood: 'get',
        params: { PartNo: this.$route.params.partNo }
      }).then(response => {
        this.partData = response.data
        this.getPurchasOrder()
      })
    },
    getPartLookup() {
      requestBN({
        url: '/productionPart/partLookup',
        methood: 'get',
        params: { PartNo: this.$route.params.partNo }
      }).then(response => {
        this.partLookup = response.data
      })
    },
    getPurchasOrder() {
      requestBN({
        url: '/purchasing/partPurchase',
        methood: 'get',
        params: { ProductionPartNo: this.$route.params.partNo }
      }).then(response => {
        this.purchaseOrder = response.data
        this.purchaseOrderData = this.purchaseOrder.PurchaseOrderData
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
