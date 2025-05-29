<template>
  <div class="app-container">
    <h1>{{ testSystemData.ItemCode }} - {{ testSystemData.Name }}</h1>
    <p><b>Description: </b>{{ testSystemData.Description }}</p>

    <el-divider />
    <h2>Instruments</h2>
    <template>
      <el-table
        :data="testSystemData.Item"
        style="width: 100%"
        border
        :cell-style="{ padding: '0', height: '20px' }"
        row-key="lineKey"
        :tree-props="{ children: 'Equipment'}"
        :default-expand-all="true"
      >

        <el-table-column label="Name" prop="Name" sortable />
        <el-table-column label="Description" prop="Description" sortable />

        <el-table-column prop="InventoryCode" label="Inventory No" width="140" sortable>
          <template slot-scope="{ row }">
            <router-link :to="'/inventory/item/' + row.ItemCode" class="link-type">
              <span> {{ row.ItemCode }}</span>
            </router-link>
          </template>
        </el-table-column>

        <el-table-column label="Manufacturer" prop="ManufacturerName" sortable />
        <el-table-column label="Type" prop="Type" sortable />
        <el-table-column label="SerialNumber" prop="SerialNumber" sortable />
        <el-table-column label="Added" prop="AddedDate" sortable />
        <el-table-column label="Removed" prop="RemovedDate" sortable />
        <el-table-column label="Calibration" prop="CalibrationDate" sortable />
        <el-table-column label="Calibration Exp." prop="CalibrationExpirationDate" sortable />
      </el-table>
    </template>
  </div>
</template>

<script>

import Metrology from '@/api/metrology'
const metrology = new Metrology()

export default {
  name: 'TestingView',
  components: { },
  data() {
    return {
      testSystemData: {}
    }
  },
  mounted() {
    this.setTitle()
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
    prepareLines(data) {
      let i = 0
      data.forEach(line => {
        line.lineKey = i
        i++
        if (line.Equipment.length === 1) {
          Object.assign(line, line, line.Equipment[0])
          delete line.Equipment
        } else {
          line.Equipment.forEach(subLine => {
            subLine.lineKey = i
            i++
          })
        }
      })
    },
    getData() {
      metrology.testSystem.item(this.$route.params.TestSystemNumber, this.date).then(response => {
        this.testSystemData = response
        this.prepareLines(this.testSystemData.Item)
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
