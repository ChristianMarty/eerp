<template>
  <div class="app-container">
    <h1>{{ partData.SpecificationPartBarcode }} - {{ partData.Type }} - {{ partData.Title }}</h1>
    <p>{{ partData.Description }}</p>

    <h3>Production Parts</h3>
    <el-table :data="partData.ProductionParts" style="width: 100%">
      <el-table-column prop="ProductionPartBarcode" label="Part Number" sortable width="150">
        <template slot-scope="{ row }">
          <router-link
            :to="'/productionPart/item/' + row.ProductionPartBarcode"
            class="link-type"
          >
            <span>{{ row.ProductionPartBarcode }}</span>
          </router-link>
        </template>
      </el-table-column>
      <el-table-column prop="ProductionPartDescription" label="Description" sortable />
    </el-table>
  </div>
</template>

<script>
import permission from '@/directive/permission/index.js'

import SpecificationPart from '@/api/specificationPart'
const specificationPart = new SpecificationPart()

export default {
  name: 'LocationAssignment',
  components: { },
  directives: { permission },
  data() {
    return {
      partData: {}
    }
  },
  mounted() {
    this.getSpecificationPart()
  },
  created() {
    // Why need to make a copy of this.$route here?
    // Because if you enter this page and quickly switch tag, may be in the execution of the setTagsViewTitle function, this.$route is no longer pointing to the current page
    // https://github.com/PanJiaChen/vue-element-admin/issues/1221
    this.tempRoute = Object.assign({}, this.$route)
  },
  methods: {

    setTitle() {
      const title = `${this.partData.Type} - ${this.partData.Title}`
      const route = Object.assign({}, this.tempRoute, {
        title: `${title}`
      })
      this.$store.dispatch('tagsView/updateVisitedView', route)
      document.title = title
    },
    getSpecificationPart() {
      specificationPart.item(this.$route.params.SpecificationPartBarcode).then(response => {
        this.partData = response
        this.setTitle()
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
.el-card {
  margin-top: 20px;
}
</style>
