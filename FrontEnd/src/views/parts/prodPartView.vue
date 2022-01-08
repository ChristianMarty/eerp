<template>
  <div class="app-container">
    <h1>{{ $route.params.partNo }}</h1>

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
      :data="partData.Stock"
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

    <h3>Stock Notification</h3>
    <p><b>Stock Minimum:</b> {{ partData.StockMinimum }}</p>
    <p><b>Stock Warning:</b> {{ partData.StockWarning }}</p>
    <p><b>Stock Maximum:</b> {{ partData.StockMaximum }}</p>

  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'

export default {
  name: 'ProdPartBrowser',
  data() {
    return {
      partData: null
    }
  },
  mounted() {
    this.getPartData()
    this.setTagsViewTitle()
    this.setPageTitle()
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
