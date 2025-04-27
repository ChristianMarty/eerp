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
      :data="partData"
      height="80vh"
      border
      style="width: 100%"
    >
      <el-table-column prop="ProductionPartNumber" sortable label="Part No" width="120">
        <template slot-scope="{ row }">
          <router-link
            :to="'/prodParts/prodPartView/' + row.ProductionPartNumber"
            class="link-type"
          >
            <span>{{ row.ProductionPartNumber }}</span>
          </router-link>
        </template>
      </el-table-column>
      <el-table-column prop="Description" label="Description" sortable />
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
      partData: null,
      ProdPartNoFilter: '',
      fliterNoMfp: true
    }
  },
  mounted() {
    this.getPartData()
  },
  methods: {
    getPartData() {
      productionPart.search(this.ProdPartNoFilter, null, null, this.fliterNoMfp).then(response => {
        this.partData = response
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    handleFilter() {
      this.getPartData()
    },
    resetFilter() {
      this.ProdPartNoFilter = ''
    }
  }
}
</script>
