<template>
  <div class="app-container">

    <el-form
      ref="inputForm"
      class="form-container"
      label-width="130px"
    >
      <el-form-item label="Manufacturer:">
        <el-cascader
          v-model="vendorId"
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

      <el-form-item label="Part Number:">
        <el-input v-model="partNumber" />
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="onAnalyze()">Analyze Number</el-button>
      </el-form-item>

      <template v-if="partSeriesData !== null ">
        <el-form-item>
          <table>

            <p v-if="partSeriesData.SeriesData !== null">
              <b>{{ partSeriesData.SeriesData.VendorName }} - {{ partSeriesData.SeriesData.Title }}</b>
            </p>
            <tr>
              <th style="text-align: left;">Series Description:</th>
              <td v-if="partSeriesData.SeriesData !== null">{{ partSeriesData.SeriesData.Description }}</td>
            </tr>
            <tr>
              <th style="text-align: left;">Series Part Number Template:</th>
              <td v-if="partSeriesData.SeriesData !== null">{{ partSeriesData.SeriesData.NumberTemplate }}</td>
            </tr>
            <tr>
              <th style="text-align: left;">Series Part Number Description:</th>
              <td v-if="partSeriesData.SeriesData !== null">{{ partSeriesData.SeriesData.PartNumberDescription }}</td>
            </tr>

            <tr v-if="partSeriesData.ItemData !== null">
              <th style="text-align: left;">Item Part Number Template:</th>
              <td>{{ partSeriesData.ItemData.Number }}</td>
            </tr>

            <tr v-if="partSeriesData.PartNumberData != null">
              <th style="text-align: left; color:red;">This part number already exists:</th>
              <td>
                <router-link :to="'/manufacturerPart/partNumber/item/' + partSeriesData.PartNumberData.ManufacturerPartNumberId" class="link-type">
                  {{ partSeriesData.PartNumberData.Number }}
                </router-link>
              </td>
            </tr>

          </table>

          <el-button v-if="partSeriesData.PartNumberPreExisting === false" type="primary" @click="onCreate()">Create Number</el-button>

        </el-form-item>

      <!--

      <el-descriptions
        :title="partSeriesData.VendorName+' - '+partSeriesData.Title+' Series'"
        direction="horizontal"
        column="1"
      >
        <el-descriptions-item label="Series Description">{{ partSeriesData.Description }} </el-descriptions-item>
        <el-descriptions-item label="Part Number Template">{{ partSeriesData.NumberTemplate }} </el-descriptions-item>
        <el-descriptions-item label="Part Number Description">{{ partSeriesData.PartNumberDescription }} </el-descriptions-item>
      </el-descriptions>-->

      </template>

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
      vendorId: 0,
      partNumber: '',
      loading: true,
      manufacturers: {},
      partSeriesData: null
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
        // this.setTitle()
      })
    },
    onAnalyze() {
      manufacturerPart.PartNumber.analyze(this.vendorId, this.partNumber).then(response => {
        this.partSeriesData = response
        // this.setTitle()
      }).catch(response => {
        this.partSeriesData = null
      })
    },
    onCreate() {
      manufacturerPart.PartNumber.create(this.vendorId, this.partNumber).then(response => {
        this.$router.push('/manufacturerPart/partNumber/item/' + response['ManufacturerPartNumberId'])
      })
    }
  }
}
</script>
