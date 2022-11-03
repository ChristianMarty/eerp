<template>
  <div class="app-container">
    <template>
      <el-table :data="testSystemData.Item" style="width: 100%">
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
      testSystemData: {}

    }
  },
  mounted() {
    this.setTitle()

    testing.system.item(this.$route.params.TestSystemNumber).then(response => {
      this.testSystemData = response
    }).catch(response => {
      this.$message({
        showClose: true,
        message: response,
        duration: 0,
        type: 'error'
      })
    })
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
