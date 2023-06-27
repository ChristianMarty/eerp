<template>
  <div class="app-container" />
</template>

<script>
import permission from '@/directive/permission/index.js'

export default {
  name: 'LocationAssignment',
  components: { },
  directives: { permission },
  data() {
    return {

    }
  },
  mounted() {

  },
  created() {
    // Why need to make a copy of this.$route here?
    // Because if you enter this page and quickly switch tag, may be in the execution of the setTagsViewTitle function, this.$route is no longer pointing to the current page
    // https://github.com/PanJiaChen/vue-element-admin/issues/1221
    this.tempRoute = Object.assign({}, this.$route)
  },
  methods: {

    setTitle(title) {
      const route = Object.assign({}, this.tempRoute, {
        title: `${title}`
      })
      this.$store.dispatch('tagsView/updateVisitedView', route)
      document.title = `${this.partData.Barcode} - ${this.partData.ManufacturerPartNumber}`
    }

  }
}
</script>

<style>
.el-card {
  margin-top: 20px;
}
</style>
