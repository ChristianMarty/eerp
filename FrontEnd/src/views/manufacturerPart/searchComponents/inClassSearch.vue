<template>
  <div
    v-loading="loading"
    element-loading-text="Loading..."
    element-loading-spinner="el-icon-loading"
    class="base-class-search-container"
  >
    <p><b>{{ manufacturerPartClass.Name }}</b></p>

    <div class="filter-container">
      <el-button type="info" plain @click="onClassSelect(manufacturerPartClass.ParentId)">Back</el-button>
      <template>
        <el-button
          v-for="row in manufacturerPartClass.Children"
          :key="row.Id"
          type="info"
          @click="onClassSelect(row.Id)"
        >{{ row.Name }}</el-button>
      </template>
    </div>

    <el-form :inline="true">
      <el-form-item>
        <el-cascader
          v-model="partFilter.VendorId"
          filterable
          :options="manufacturers"
          :props="{
            emitPath: false,
            value: 'Id',
            label: 'DisplayName',
            children: 'Children',
            checkStrictly: true
          }"
        />
      </el-form-item>

      <el-form-item>
        <el-input
          v-model="partFilter.ManufacturerPartNumber"
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

    <!--  <SearchSelectPanel
      v-for="option in filterOptions"
      :key="option.Name"
      :options="option"
      style="margin-bottom: 50px;"
    />-->

    <template>
      <el-table
        v-loading="loading"
        element-loading-text="Loading..."
        element-loading-spinner="el-icon-loading"
        :data="partData"
        border
        style="width: 100%"
      >
        <el-table-column
          prop="ManufacturerPartNumberTemplateWithoutParameters"
          sortable
          label="Manufacturer Part"
          width="220"
        >
          <template slot-scope="{ row }">
            <router-link
              :to="'/manufacturerPart/item/' + row.PartId"
              class="link-type"
            >
              <span>{{ row.ManufacturerPartNumberTemplateWithoutParameters }}</span>
            </router-link>
          </template>
        </el-table-column>

        <el-table-column
          prop="ManufacturerName"
          label="Manufacturer"
          sortable
          width="200"
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
          v-for="attribute in manufacturerPartAttribute"
          :key="attribute.Name"
          :label="getAttributeColumnName(attribute)"
          :prop="attribute.Name"
          sortable
          :formatter="siRowFormater"
          :sort-method="tableSort(attribute.Name)"
        />

        <el-table-column prop="StockQuantity" label="Stock" sortable :sort-method="tableSort('StockQuantity')" />
        <el-table-column sortable prop="Package" label="Package" width="120" />
        <el-table-column sortable prop="Status" label="Lifecycle" width="120" />
      </el-table>
    </template>
  </div>
</template>

<script>
import siFormatter from '@/utils/siFormatter'

import SearchSelectPanel from './searchSelectPanel'

import ManufacturerPart from '@/api/manufacturerPart'
const manufacturerPart = new ManufacturerPart()

import Vendor from '@/api/vendor'
const vendor = new Vendor()

import Part from '@/api/part'
const part = new Part()

// :data="manufacturerPartClass" @select="onClassSelect"

export default {
  name: 'BaseClassSearchContainer',
  components: { SearchSelectPanel },
  data() {
    return {
      filterOptions: [],
      loading: true,
      manufacturerPartClass: [],
      manufacturerPartAttribute: null,
      manufacturers: [],
      partFilter: Object.assign({}, vendor.searchParameters),
      partData: null
    }
  },
  watch: {
    '$route.query.ClassId': {
      handler(newVal) {
        this.getManufacturerPartClass(this.$route.query.ClassId)
        this.getPartData(this.$route.query.ClassId)
        this.loading = false
      }
    }
  },
  created() {
  },
  mounted() {
    this.getManufacturers()
    this.getManufacturerPartClass(this.$route.query.ClassId)
    this.getPartData(this.$route.query.ClassId)
  },
  methods: {
    handleFilter() {
      manufacturerPart.search(this.partFilter, true).then(response => {
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
    resetFilter() {
      this.partFilter = Object.assign({}, vendor.searchParameters)
    },
    getPartData(ClassId) {
      this.loading = true
      this.partFilter.ClassId = ClassId

      this.getManufacturerPartAttribute(ClassId)
      this.getManufacturerPartClass(ClassId)
      this.getFilterOption(ClassId)

      manufacturerPart.search(this.partFilter, true).then(response => {
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
    getManufacturerPartClass(ClassId) {
      if (ClassId === null) return

      part.class.list(ClassId, true).then(response => {
        this.manufacturerPartClass = response
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    getFilterOption(ClassId) {
      if (ClassId === null || ClassId === 'null') return

      manufacturerPart.class.getFilterOption(ClassId).then(response => {
        this.filterOptions = response
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    getManufacturerPartAttribute(ClassId) {
      manufacturerPart.attribute.search(ClassId, false, true).then(response => {
        this.manufacturerPartAttribute = response
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
      vendor.search(false, true, false, false, false, true).then(response => {
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
    onClassSelect(ClassId = 0) {
      if (ClassId === 0) this.$router.push({ query: { }})
      else this.$router.push({ query: { ClassId: String(ClassId) }})
    },
    getAttributeColumnName(attribute) {
      var out = attribute.Name
      if (attribute.Symbol) out += ' [' + attribute.Symbol + ']'
      return out
    },
    tableSort(property) {
      return function(a, b) {
        var valA = 0
        if (a[property] != null) valA = parseFloat(a[property])

        var valB = 0
        if (b[property] != null) valB = parseFloat(b[property])

        return valA - valB
      }
    },
    siRowFormater(row, column, cellValue, index) {
      return siFormatter(cellValue, '')
    }

  }
}
</script>
