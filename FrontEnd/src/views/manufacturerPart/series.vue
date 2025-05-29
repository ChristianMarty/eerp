<template>
  <div class="app-container">
    <el-table
      v-loading="loading"
      element-loading-text="Loading Manufacturer Part Series"
      :data="manufacturerPartSeriesData"
      style="width: 100%;"
      :cell-style="{ padding: '0', height: '20px' }"
      border
    >
      <el-table-column label="Class" prop="ClassName" sortable />
      <el-table-column label="Title" prop="Title" sortable>
        <template slot-scope="{ row }">
          <router-link :to="'/manufacturerPart/series/item/' + row.ManufacturerPartSeriesId" class="link-type">
            <span> {{ row.Title }}</span>
          </router-link>
        </template>
      </el-table-column>
      <el-table-column label="Manufacturer" prop="ManufacturerName" sortable />
      <el-table-column label="Description" prop="Description" sortable />
    </el-table>

  </div>
</template>

<script>

import ManufacturerPart from '@/api/manufacturerPart'
const manufacturerPart = new ManufacturerPart()

export default {
  name: 'PartSeriesBrowser',
  data() {
    return {
      loading: true,
      manufacturerPartSeriesData: []
    }
  },
  mounted() {
    this.getManufacturerPartSeries()
  },
  methods: {
    getManufacturerPartSeries() {
      manufacturerPart.series.search().then(response => {
        this.manufacturerPartSeriesData = response
        this.loading = false
      })
    }
  }
}
</script>
