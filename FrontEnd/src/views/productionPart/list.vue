<template>
  <div class="app-container">
    <el-form :inline="true">
      <el-form-item>
        <el-input
          v-model="ProdPartNoFilter"
          class="filter-item"
          placeholder="Production Part No"
          style="width: 220px;"
          @keydown.enter.native="handleFilter"
        />
      </el-form-item>

      <el-form-item>
        <el-button
          class="filter-item"
          type="primary"
          icon="el-icon-search"
          @click="handleFilter"
        >
          Search
        </el-button>
      </el-form-item>
      <el-form-item>
        <el-button type="info" plain @click="resetFilter">Reset</el-button>
      </el-form-item>

      <p><el-checkbox v-model="fliterNoMfp" @change="getPartData()">Hide entries without assigned manufacturer parts</el-checkbox>
      </p>
      <p>Use MySQL LIKE syntax for search queries</p>
    </el-form>

    <el-table
      v-loading="loading"
      element-loading-text="Loading Production Parts ..."
      element-loading-spinner="el-icon-loading"
      :data="data"
      :default-sort="{ prop: 'Package', order: 'descending' }"
      height="80vh"
      border
      style="width: 100%"
    >
      <el-table-column prop="ItemCode" sortable label="Code" width="120">
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
      <el-table-column prop="ApprovedUsage" label="Approved Usage" sortable width="170" />
      <el-table-column prop="BillOfMaterial_TotalQuantityUsed" label="Total use in BoM" width="170" sortable />
      <el-table-column prop="BillOfMaterial_NumberOfOccurrence" label="Number of BoMs" width="170" sortable />
    </el-table>
  </div>
</template>

<script>
import ProductionPart from '@/api/productionPart'
const productionPart = new ProductionPart()

export default {
  name: 'ProdPartBrowser',
  data() {
    return {
      loading: true,
      data: [],
      ProdPartNoFilter: '',
      fliterNoMfp: true
    }
  },
  mounted() {
    this.getPartData()
  },
  methods: {
    getPartData() {
      this.loading = true
      let partFilter = this.ProdPartNoFilter.trim()
      if (partFilter === '') partFilter = null

      productionPart.search(partFilter, null, null, this.fliterNoMfp).then(response => {
        this.loading = false
        this.data = response
      })
    },
    handleFilter() {
      this.getPartData()
    },
    resetFilter() {
      this.ProdPartNoFilter = ''
      this.getPartData()
    }
  }
}
</script>
