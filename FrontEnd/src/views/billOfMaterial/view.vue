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
      <el-menu-item index="stockAvailability">Stock Availability</el-menu-item>
      <el-menu-item index="analysis">Analysis</el-menu-item>
      <el-menu-item index="placement">Placement</el-menu-item>
    </el-menu>

    <availability v-if="activeIndex === 'stockAvailability'" :revision-id="projectData.Revisions[0].Id" />
    <analysis v-if="activeIndex === 'analysis'" :revision-id="projectData.Revisions[0].Id" />
    <placement v-if="activeIndex === 'placement'" :revision-id="projectData.Revisions[0].Id" />

    <el-dialog title="Bom Upload" :visible.sync="showUploadDialog">
      <bomUpload :revision-id="projectData.Revisions[0].Id" />
    </el-dialog>
  </div>
</template>

<script>

import availability from './components/availability'
import placement from './components/placement'
import analysis from './components/analysis'
import bomUpload from './components/upload'

import BillOfMaterial from '@/api/billOfMaterial'
const billOfMaterial = new BillOfMaterial()

export default {
  name: 'ProjectView',
  components: { availability, placement, bomUpload, analysis },
  data() {
    return {
      csv: null,
      bom: null,
      buildQuantity: 1,
      projectData: null,
      showUploadDialog: false,
      activeIndex: 'stockAvailability'
    }
  },
  mounted() {
    this.getProjectData()
    this.setTitle()
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
        title: `${this.$route.params.BillOfMaterialNumber}`
      })
      this.$store.dispatch('tagsView/updateVisitedView', route)
      document.title = `${this.projectData.Title}`
    },
    handleSelect(key, keyPath) {
      this.activeIndex = key
    },
    getProjectData() {
      billOfMaterial.item.get(this.$route.params.BillOfMaterialNumber).then(response => {
        this.projectData = response
      })
    },
    tableAnalyzer({ row, rowIndex }) {
      if (row.PartNo.includes('Unknown')) {
        return 'error-row'
      } else if (row.TotalQuantity > row.Stock) {
        return 'warning-row'
      } else {
        return ''
      }
    },
    onQuantityChange() {
      this.bom.forEach(
        row => (row.TotalQuantity = row.Quantity * this.buildQuantity)
      )
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
