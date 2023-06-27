<template>
  <div class="app-container">
    <el-form :inline="true">
      <el-form-item>
        <el-cascader
          v-model="searchParameters.VendorId"
          filterable
          :options="manufacturers"
          :props="{
            emitPath: false,
            value: 'Id',
            label: 'Name',
            children: 'Children',
            checkStrictly: true
          }"
        />
      </el-form-item>

      <el-form-item>
        <el-input
          v-model="searchParameters.ManufacturerPartNumber"
          class="filter-item"
          placeholder="Manufacturer Part No"
          style="width: 220px;"
          @keyup.enter.native="handleFilter()"
        />
      </el-form-item>

      <el-form-item>
        <el-button
          class="filter-item"
          type="primary"
          icon="el-icon-search"
          @click="handleFilter()"
        >Search</el-button>
      </el-form-item>
      <el-form-item>
        <el-button type="info" plain @click="resetFilter()">Reset</el-button>
      </el-form-item>
    </el-form>

    <el-table
      v-loading="loading"
      element-loading-text="Loading Manufacturer Parts ..."
      element-loading-spinner="el-icon-loading"
      :data="manufacturerParts"
      border
      height="85vh"
      style="width: 100%"
      :cell-style="{ padding: '0', height: '20px' }"
    >
      <el-table-column
        prop="PartNumber"
        sortable
        label="Part Number"
        width="220"
      >
        <template slot-scope="{ row }">
          <template v-if="row.PartNumberId != 0">
            <router-link
              :to="'/manufacturerPart/partNumber/item/' + row.PartNumberId"
              class="link-type"
            >
              <span>{{ row.PartNumber }}</span>
            </router-link>
          </template>
          <template v-else>
            <span>{{ row.PartNumber }}</span>
          </template>
        </template></el-table-column>

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
        prop="ManufacturerPartNumberTemplate"
        label="Part"
        sortable
        width="220"
      >
        <template slot-scope="{ row }">
          <router-link
            :to="'/manufacturerPart/item/' + row.PartId"
            class="link-type"
          >
            <span>{{ row.ManufacturerPartNumberTemplate }}</span>
          </router-link>
        </template>
      </el-table-column>

      <el-table-column
        prop="SeriesTitle"
        label="Series"
        sortable
        width="220"
      >
        <template slot-scope="{ row }">
          <router-link
            :to="'/manufacturerPart/series/item/' + row.SeriesId"
            class="link-type"
          >
            <span>{{ row.SeriesTitle }}</span>
          </router-link>
        </template>
      </el-table-column>
      <el-table-column sortable prop="MarkingCode" label="Marking Code" width="150" />
      <el-table-column sortable prop="Description" label="Description" />
    </el-table>
  </div>
</template>

<script>

import ManufacturerPart from '@/api/manufacturerPart'
const manufacturerPart = new ManufacturerPart()

import Vendor from '@/api/vendor'
const vendor = new Vendor()

export default {
  name: 'ManufacturerPartSearch',
  components: {},
  data() {
    return {
      loading: true,
      manufacturerParts: [], // Object.assign({}, manufacturerPart.series.seriesCreateParameters)
      searchParameters: Object.assign({}, manufacturerPart.PartNumber.searchParameters),
      manufacturers: []
    }
  },
  watch: {
    '$route.query': {
      handler(newVal) {
        this.getManufacturerPart()
      }
    }
  },
  mounted() {
    this.getManufacturerPart()
    this.getManufacturers()
  },
  methods: {
    getManufacturerPart() {
      this.loading = true
      if (this.$route.query.VendorId) this.searchParameters.VendorId = this.$route.query.VendorId
      if (this.$route.query.ManufacturerPartNumber) this.searchParameters.ManufacturerPartNumber = this.$route.query.ManufacturerPartNumber

      manufacturerPart.PartNumber.search(this.searchParameters).then(response => {
        this.manufacturerParts = response
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
    getManufacturers() {
      vendor.search(false, true, false).then(response => {
        this.manufacturers = response
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
      if (!this.searchParameters.VendorId) this.searchParameters.VendorId = null
      if (!this.searchParameters.ManufacturerPartNumber) this.searchParameters.ManufacturerPartNumber = null

      this.$router.push({ query: this.searchParameters })

      this.getManufacturerPart()
    },
    resetFilter() {
      this.searchParameters = Object.assign({}, manufacturerPart.PartNumber.searchParameters)
      this.$router.push({ query: { }})
    }
  }
}
</script>
