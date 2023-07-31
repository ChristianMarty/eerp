<template>
  <div class="app-container">
    <h1>{{ partData.Type }} - {{ partData.Title }}</h1>
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
