<template>
  <div class="app-container">
    <h1>{{ assemblyData.Barcode }} - {{ assemblyData.Name }}</h1>
    <p><b>Serial Number: </b>{{ assemblyData.SerialNumber }}</p>
    <p><b>Location: </b>{{ assemblyData.LocationName }}</p>

    <el-divider />
    <h2>History</h2>
    <el-button
      v-permission="['purchasing.edit']"
      type="primary"
      icon="el-icon-plus"
      circle
      style="margin-top: 20px; margin-bottom: 20px"
      @click="addHistoryVisible = true"
    />
    <el-timeline reverse="true">
      <el-timeline-item
        v-for="(line, index) in assemblyData.History"
        :key="index"
        :color="line.color"
        :timestamp="line.Date"
        placement="top"
      >
        <el-card>
          <b>{{ line.Title }}</b>
          <p>{{ line.Description }}</p>
        </el-card>
      </el-timeline-item>
    </el-timeline>

    <el-dialog title="Add History Item" :visible.sync="addHistoryVisible">
      <el-form label-width="120px">
        <el-form-item label="Title">
          <el-input v-model="addHistoryData.Title" />
        </el-form-item>
        <el-form-item label="Description">
          <el-input v-model="addHistoryData.Description" type="textarea" />
        </el-form-item>
        <el-form-item label="Data">
          <el-input v-model="addHistoryData.Data" type="textarea" />
        </el-form-item>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="addHistoryVisible = false; addHistoryItem();">Save</el-button>
        <el-button @click="addHistoryVisible = false">Cancel</el-button>
      </span>
    </el-dialog>

  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'

export default {
  name: 'AssemblyView',
  components: {},
  data() {
    return {
      assemblyData: {},
      addHistoryData: {},
      addHistoryVisible: false
    }
  },
  created() {
    // Why need to make a copy of this.$route here?
    // Because if you enter this page and quickly switch tag, may be in the execution of the setTagsViewTitle function, this.$route is no longer pointing to the current page
    // https://github.com/PanJiaChen/vue-element-admin/issues/1221
    this.tempRoute = Object.assign({}, this.$route)
  },
  mounted() {
    this.getAssembly()
    this.setTagsViewTitle()
    this.setPageTitle()
  },
  methods: {
    setTagsViewTitle() {
      const route = Object.assign({}, this.tempRoute, {
        title: `${this.$route.params.AssemblyNo}`
      })
      this.$store.dispatch('tagsView/updateVisitedView', route)
    },
    setPageTitle() {
      document.title = `${this.$route.params.AssemblyNo}`
    },
    addHistoryItem() {
      requestBN({
        method: 'post',
        url: '/assembly/history/add',
        data: {
          AssemblyNo: this.$route.params.AssemblyNo,
          Title: this.addHistoryData.Title,
          Description: this.addHistoryData.Description,
          Data: this.addHistoryData.Data
        }
      }).then(response => {
        this.addHistoryData = {}
        this.getAssembly()
      })
    },
    getAssembly() {
      requestBN({
        url: '/assembly/item',
        methood: 'get',
        params: { AssemblyNo: this.$route.params.AssemblyNo }
      }).then(response => {
        this.assemblyData = response.data
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
