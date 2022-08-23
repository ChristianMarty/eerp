<template>
  <div class="app-container">
    <h1>{{ assemblyData.Barcode }}-{{ assemblyData.AssemblyItemNo }}, {{ assemblyData.Name }}</h1>
    <p><b>Serial Number: </b>{{ assemblyData.SerialNumber }}</p>
    <p><b>Location: </b>{{ assemblyData.LocationName }}</p>

    <el-divider />
    <h2>History</h2>
    <el-button
      v-permission="['assembly.history.add']"
      type="primary"
      icon="el-icon-plus"
      circle
      style="margin-top: 20px; margin-bottom: 20px"
      @click="showEditHistoryDialog(null)"
    />
    <el-timeline>
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
          <el-button @click.native="showHistoryDialog(line.Id)">Show Data</el-button>
          <el-button v-if="line.EditToken" v-permission="['assembly.history.edit']" type="primary" @click.native=" showEditHistoryDialog(line.Id)">Edit</el-button>
        </el-card>
      </el-timeline-item>
    </el-timeline>

    <el-dialog title="Add History Item" :visible.sync="editHistoryVisible">
      <el-form label-width="120px">
        <el-form-item label="Title">
          <el-input v-model="editHistoryData.Title" />
        </el-form-item>
        <el-form-item label="Description">
          <el-input v-model="editHistoryData.Description" type="textarea" />
        </el-form-item>
        <el-form-item label="Data (JSON)">
          <el-input v-model="editHistoryData.Data" type="textarea" />
        </el-form-item>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="editHistoryItem();">Save</el-button>
        <el-button @click="editHistoryVisible = false">Cancel</el-button>
      </span>
    </el-dialog>

    <assemblyDataDialog :id="historyId" :visible.sync="assemblyDataDialogVisible" />

  </div>
</template>

<script>
import permission from '@/directive/permission/index.js'
import requestBN from '@/utils/requestBN'
import assemblyDataDialog from './components/dataDialog'

export default {
  name: 'AssemblyView',
  components: { assemblyDataDialog },
  directives: { permission },
  data() {
    return {
      assemblyData: {},
      editHistoryData: {},
      historyItemData: {},
      editHistoryVisible: false,
      assemblyDataDialogVisible: false,
      historyId: 0
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
    showEditHistoryDialog(id) {
      if (id === null) {
        this.editHistoryData = {}
      } else {
        this.getHistoryData(id)
      }

      this.editHistoryVisible = true
    },
    showHistoryDialog(id) {
      this.historyId = id
      this.assemblyDataDialogVisible = true
    },
    editHistoryItem() {
      if (this.editHistoryData.EditToken == null) {
        this.addHistoryItem()
      } else {
        this.updateHistoryItem()
      }
    },
    addHistoryItem() {
      requestBN({
        method: 'post',
        url: '/assembly/history/item',
        data: {
          AssemblyItemNo: this.assemblyData.AssemblyItemNo,
          Title: this.editHistoryData.Title,
          Description: this.editHistoryData.Description,
          Data: this.editHistoryData.Data
        }
      }).then(response => {
        if (response.error !== null) {
          this.$message({
            showClose: true,
            message: response.error,
            duration: 1500,
            type: 'error'
          })
        } else {
          this.editHistoryVisible = false
          this.editHistoryData = {}
          this.getAssembly()
        }
      })
    },
    updateHistoryItem() {
      requestBN({
        method: 'patch',
        url: '/assembly/history/item',
        data: {
          EditToken: this.editHistoryData.EditToken,
          Title: this.editHistoryData.Title,
          Description: this.editHistoryData.Description,
          Data: this.editHistoryData.Data
        }
      }).then(response => {
        if (response.error !== null) {
          this.$message({
            showClose: true,
            message: response.error,
            duration: 1500,
            type: 'error'
          })
        } else {
          this.editHistoryVisible = false
          this.addHistoryData = {}
          this.getAssembly()
        }
      })
    },
    getHistoryData(id) {
      requestBN({
        url: '/assembly/history/item',
        methood: 'get',
        params: {
          AssemblyHistoryId: id
        }
      }).then(response => {
        this.editHistoryData = response.data
        var data = this.editHistoryData.Data
        if (data === null) this.editHistoryData.Data = ''
        else this.editHistoryData.Data = JSON.stringify(data)
        this.editHistoryToken = this.editHistoryData.EditToken
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
