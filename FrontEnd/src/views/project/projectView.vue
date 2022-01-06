<template>
  <div class="app-container">
    <h1>{{ projectData.Title }}</h1>
    <p>{{ projectData.Description }}</p>
    <el-button type="primary" @click="upload">Upload</el-button>

    <el-menu
      default-active="availability"
      class="el-menu-demo"
      mode="horizontal"
      @select="handleSelect"
    >
      <el-menu-item index="availability">Availability</el-menu-item>
      <el-menu-item index="placement">Placement</el-menu-item>
    </el-menu>

    <availability v-if="activeIndex == 'availability'" :project-id="projectData.Id" />
    <placement v-if="activeIndex == 'placement'" :project-id="projectData.Id" />

    <el-dialog title="Bom Upload" :visible.sync="showUploadDialog">
      <bomUpload :project-id="projectData.Id" />
    </el-dialog>
  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'
import availability from './components/availability'
import placement from './components/placement'
import bomUpload from './components/upload'

export default {
  name: 'ProjectView',
  components: { availability, placement, bomUpload },
  data() {
    return {
      csv: null,
      bom: null,
      buildQuantity: 1,
      projectData: null,
      showUploadDialog: false,
      activeIndex: 'availability'
    }
  },
  mounted() {
    this.getProjectData()
    this.setTagsViewTitle()
    
  },
  created() {
    // Why need to make a copy of this.$route here?
    // Because if you enter this page and quickly switch tag, may be in the execution of the setTagsViewTitle function, this.$route is no longer pointing to the current page
    // https://github.com/PanJiaChen/vue-element-admin/issues/1221
    this.tempRoute = Object.assign({}, this.$route)
  },
  methods: {
    setTagsViewTitle() {
      const route = Object.assign({}, this.tempRoute, {
        title: `${this.$route.params.projectNo}`
      })
      this.$store.dispatch('tagsView/updateVisitedView', route)
    },
    setPageTitle() {
      document.title = `${this.projectData.Title}`
    },
    handleSelect(key, keyPath) {
      this.activeIndex = key
    },
    getProjectData() {
      requestBN({
        url: '/project/item',
        methood: 'get',
        params: { ProjectNo: this.$route.params.projectNo }
      }).then(response => {
        this.projectData = response.data
        this.setPageTitle()
      })
    },
    tableAnalyzer({ row, rowIndex }) {
      if (row.PartNo.includes('Unknown')) {
        return 'error-row'
      }
      if (row.TotalQuantity > row.Stock) {
        return 'warning-row'
      }
      return ''
    },
    onQuantityChange() {
      this.bom.forEach(
        row => (row.TotalQuantity = row.Quantity * this.buildQuantity)
      )
    },
    onSubmit() {
      requestBN({
        method: 'post',
        url: '/productionPart/bomView',
        data: { csv: this.csv, BuildQuantity: this.buildQuantity }
      }).then(response => {
        this.bom = response.data.bom
        this.onQuantityChange()
      })
    },
    upload() {
      this.showUploadDialog = true
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
