<template>
  <div class="app-container">
    <h1>{{ projectData.Name }}</h1>
    <p>{{ projectData.Description }}</p>
  </div>
</template>

<script>
import Project from '@/api/project'
const project = new Project()

export default {
  name: 'ProjectView',
  data() {
    return {
      projectData: null
    }
  },
  mounted() {
    this.getProjectData()
  },
  created() {
    // Why need to make a copy of this.$route here?
    // Because if you enter this page and quickly switch tag, may be in the execution of the setTagsViewTitle function, this.$route is no longer pointing to the current page
    // https://github.com/PanJiaChen/vue-element-admin/issues/1221
    this.tempRoute = Object.assign({}, this.$route)
  },
  methods: {
    setPageTitle() {
      const route = Object.assign({}, this.tempRoute, {
        title: `${this.projectData.ProjectBarcode}`
      })
      this.$store.dispatch('tagsView/updateVisitedView', route)
      document.title = `${this.projectData.ProjectBarcode}`
    },
    getProjectData() {
      project.item.get(this.$route.params.ProjectNumber).then(response => {
        this.projectData = response
        this.loading = false
        this.setPageTitle()
      })
    }
  }
}
</script>
