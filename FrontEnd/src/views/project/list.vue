<template>
  <div class="app-container">
    <template>
      <el-table
        v-loading="loading"
        :data="projects"
        style="width: 100%"
        element-loading-text="Loading Projects"
      >
        <el-table-column label="Code" prop="ItemCode" width="150" sortable>
          <template slot-scope="{ row }">
            <router-link :to="'/project/item/' + row.ProjectNumber" class="link-type">
              <span> {{ row.ItemCode }}</span>
            </router-link>
          </template>
        </el-table-column>
        <el-table-column label="Name" prop="Name" width="250" sortable />
        <el-table-column label="Description" prop="Description" sortable />
      </el-table>
    </template>
  </div>
</template>

<script>
import Project from '@/api/project'
const project = new Project()

export default {
  name: 'ProjectBrowser',
  components: {},
  data() {
    return {
      loading: true,
      projects: []
    }
  },
  mounted() {
    this.getProjects()
  },
  methods: {
    getProjects() {
      this.loading = true
      project.search().then(response => {
        this.projects = response
        this.loading = false
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
