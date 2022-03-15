<template>
  <div class="app-container">
    <div class="filter-container">
      <template v-if="parrentClassId.length">
        <el-button type="info" plain @click="handlePartClassBack()">
          Back</el-button>
      </template>
      <template v-else>
        <el-button disabled type="info" plain @click="handlePartClassBack()">
          Back</el-button>
      </template>

      <template>
        <el-button
          v-for="row in partsClasses"
          :key="row.Id"
          type="info"
          @click="handlePartClass(row.Id)"
        >{{ row.Name }}</el-button>
      </template>
    </div>

    <el-form :inline="true" class="demo-form-inline">
      <el-form-item>
        <el-input
          v-model="MfrPartNoFilter"
          class="filter-item"
          placeholder="Manufacturer Part No"
          style="width: 220px;"
        />
      </el-form-item>
      <el-form-item>
        <el-select
          v-model="manufacturerFlter"
          filterable
          placeholder="Manufacturer"
          style="width: 180px;"
        >
          <el-option
            v-for="item in manufacturers"
            :key="item.Name"
            :label="item.Name"
            :value="item.Name"
          />
        </el-select>
      </el-form-item>

      <el-form-item>
        <el-button
          class="filter-item"
          type="primary"
          icon="el-icon-search"
          @click="handleFilter"
        >Search</el-button>
      </el-form-item>
      <el-form-item>
        <el-button type="info" plain @click="resetFilter">Reset</el-button>
      </el-form-item>
    </el-form>

    <el-table
      :data="partData"
      height="80vh"
      border
      style="width: 100%"
    >
      <el-table-column
        prop="ManufacturerPartNumber"
        sortable
        label="Manufacturer Part No"
        width="220"
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

      <el-table-column
        prop="ManufacturerName"
        label="Manufacturer"
        sortable
        width="200"
      />

      <el-table-column
        v-for="attribute in partAttributes"
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
  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'
import siFormatter from '@/utils/siFormatter'

export default {
  name: 'PartBrowser',
  data() {
    return {
      partData: null,
      manufacturers: null,
      manufacturerFlter: '',
      MfrPartNoFilter: '',
      partsClasses: null,
      partAttributes: null,
      classId: 0,
      parrentClassId: [],
      tempRoute: {}
    }
  },
  mounted() {
    this.getClassData('0')
    // this.getPartData();
    this.getManufacturers()
  },
  methods: {
    getAttributeValue(value) {
      if (value === null) return ''

      if (typeof value === 'object') {
        var out = value.Minimum
        if (value.Typical) out += ' - ' + value.Typical
        out += ' - ' + value.Maximum
        return out
      } else {
        return value
      }
    },
    getAttributeColumnName(attribute) {
      var out = attribute.Name
      if (attribute.Unit) out += ' [' + attribute.Unit + ']'
      return out
    },
    getPartData() {
      var filterList = {}
      if (this.MfrPartNoFilter.length !== 0) {
        this.$set(
          filterList,
          'ManufacturerPartNumber',
          '%' + this.MfrPartNoFilter + '%'
        )
      }

      if (this.manufacturerFlter.length !== 0) {
        this.$set(filterList, 'ManufacturerName', this.manufacturerFlter)
      }

      if (this.classId !== 0) {
        this.$set(filterList, 'classId', this.classId)
      }

      requestBN({
        url: '/part',
        methood: 'get',
        params: filterList
      }).then(response => {
        this.partData = this.flattenPartData(response.data)
      })
    },
    flattenPartData(data) {
      data.forEach(item => {
        item.PartData.forEach(element => {
          Object.defineProperty(item, element.Name, {
            value: this.getAttributeValue(element.Value),
            writable: true,
            enumerable: true,
            configurable: true
          })
        })
      })
      return data
    },
    getManufacturers() {
      requestBN({
        url: '/part/manufacturer',
        methood: 'get'
      }).then(response => {
        this.manufacturers = response.data
      })
    },
    getClassData(Id) {
      requestBN({
        url: '/part/class',
        methood: 'get',
        params: { classId: Id }
      }).then(response => {
        this.partsClasses = response.data
        this.partAttributes = response.data.Attributes
      })

      requestBN({
        url: '/part/attribute',
        methood: 'get',
        params: { classId: Id, children: false, parents: true }
      }).then(response => {
        this.partAttributes = response.data
        this.getPartData()
      })
    },
    handlePartClass(id) {
      this.parrentClassId.push(id)
      this.classId = id
      this.getClassData(id)
    },
    handlePartClassBack() {
      this.parrentClassId.pop()

      if (!this.parrentClassId.length) {
        this.parrentClassId = []
        this.classId = 0
      } else this.classId = this.parrentClassId[this.parrentClassId.length - 1]

      this.getClassData(this.classId)
      this.getPartData()
    },
    handleFilter() {
      this.getPartData()
    },
    resetFilter() {
      this.manufacturerFlter = ''
      this.MfrPartNoFilter = ''
      this.getPartData()
    },
    tableSort(property) {
      return function(a, b) {
        if (a.[property] === undefined || b.[property] === undefined) return -1

        var valA = 0
        if (a.[property] != null) valA = parseFloat(a.[property])

        var valB = 0
        if (b.[property] != null) valB = parseFloat(b.[property])

        return valA - valB
      }
    },
    siRowFormater(row, column, cellValue, index) {
      return siFormatter(cellValue, '')
    }
  }
}
</script>
