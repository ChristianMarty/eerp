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

      <p><el-checkbox v-model="fliterNoMfp">Hide entries without assigned manufacturer parts</el-checkbox>
      </p>
      <p>Use MySQL LIKE syntax for search queries</p>
    </el-form>

    <el-table
      :data="partData"
      :default-sort="{ prop: 'Package', order: 'descending' }"
      height="80vh"
      border
      style="width: 100%"
    >
      <el-table-column prop="PartNo" sortable label="Part No" width="120">
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
  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'

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
      var filterList = {}

      if (this.ProdPartNoFilter.length !== 0) {
        this.$set(filterList, 'ProductionPartNo', this.ProdPartNoFilter)
      }
      filterList.HideNoManufacturerPart = this.fliterNoMfp

      requestBN({
        url: '/productionPart',
        methood: 'get',
        params: filterList
      }).then(response => {
        this.partData = response.data
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
