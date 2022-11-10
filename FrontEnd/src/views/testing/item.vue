<template>
  <div class="app-container">
    <h1>{{ testSystemData.TestSystemBarcode }} - {{ testSystemData.Name }}</h1>
    <p><b>Description: </b>{{ testSystemData.Description }}</p>

    <p><b>Test Date: </b><el-date-picker
      v-model="date"
      type="date"
      placeholder="Pick a day"
      value-format="yyyy-MM-dd"
      @change="getData()"
    /></p>
    <p>Select the date of testing to verify the calibration.</p>

    <el-divider />
    <h2>Equipment List</h2>
    <template>
      <el-table :data="testSystemData.Item" style="width: 100%" border :cell-style="{ padding: '0', height: '20px' }">
        <el-table-column prop="InventoryNumber" label="Inventory No" width="140" sortable>
          <template slot-scope="{ row }">
            <router-link :to="'/inventory/inventoryView/' + row.InventoryBarcode" class="link-type">
              <span> {{ row.InventoryBarcode }}</span>
            </router-link>
          </template>
        </el-table-column>
        <el-table-column label="Title" prop="Title" sortable />
        <el-table-column label="Manufacturer" prop="Manufacturer" sortable />
        <el-table-column label="Type" prop="Type" sortable />
        <el-table-column label="SerialNumber" prop="SerialNumber" sortable />
        <el-table-column label="Usage" prop="Usage" sortable />
        <el-table-column label="Calibration Required" prop="CalibrationRequired" sortable>
          <template slot-scope="{ row }">
            {{ row.CalibrationRequired }}
          </template>
        </el-table-column>
        <el-table-column label="Calibration Date" prop="CalibrationDate" sortable />
        <el-table-column label="Next Calibration" prop="NextCalibrationDate" sortable />
      </el-table>
    </template>
  </div>
</template>

<script>

import Testing from '@/api/testing'
const testing = new Testing()

export default {
  name: 'TestingView',
  components: { },
  data() {
    return {
      testSystemData: {},
      date: ''

    }
  },
  mounted() {
    this.setTitle()
    this.date = new Date().toISOString().slice(0, 10)
    this.getData()
  },
  created() {
    // Why need to make a copy of this.$route here?
    // Because if you enter this page and quickly switch tag, may be in the execution of the setTagsViewTitle function, this.$route is no longer pointing to the current page
    // https://github.com/PanJiaChen/vue-element-admin/issues/1221
    this.tempRoute = Object.assign({}, this.$route)
  },
  methods: {
    setTitle() {
      const route = Object.assign({}, this.tempRoute, {
        title: `${this.$route.params.TestSystemNumber}`
      })
      this.$store.dispatch('tagsView/updateVisitedView', route)
      document.title = `${this.$route.params.TestSystemNumber}`
    },
    getData() {
      testing.system.item(this.$route.params.TestSystemNumber, this.date).then(response => {
        this.testSystemData = response
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

<style>
.el-table .warning-row {
  background: oldlace;
}
.el-table .error-row {
  background: Lavenderblush;
}
</style>
