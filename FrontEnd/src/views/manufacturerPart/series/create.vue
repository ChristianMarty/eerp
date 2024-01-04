<template>
  <div class="app-container">
    <el-form
      ref="inputForm"
      class="form-container"
      label-width="130px"
    >
      <el-form-item label="Manufacturer:">
        <el-cascader
          v-model="seriesCreateParameters.VendorId"
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

      <el-form-item label="Title:">
        <el-input v-model="seriesCreateParameters.Title" />
      </el-form-item>

      <el-form-item label="Description:">
        <el-input v-model="seriesCreateParameters.Description" />
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="onCreate()">Create</el-button>
      </el-form-item>

    </el-form>

  </div>
</template>

<script>
import Vendor from '@/api/vendor'
const vendor = new Vendor()

import ManufacturerPart from '@/api/manufacturerPart'
const manufacturerPart = new ManufacturerPart()

export default {
  name: 'PartSeriesBrowser',
  data() {
    return {
      manufacturers: {},
      seriesCreateParameters: Object.assign({}, manufacturerPart.series.seriesCreateParameters)
    }
  },
  mounted() {
    this.getManufactures()
  },
  created() {
  },
  methods: {
    getManufactures() {
      vendor.search(false, true, false, false, false).then(response => {
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
    onCreate() {
      manufacturerPart.series.create(this.seriesCreateParameters).then(response => {
        this.$router.push('/manufacturerPart/series/item/' + response['ManufacturerPartSeriesId'])
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    }
  }
}
</script>
