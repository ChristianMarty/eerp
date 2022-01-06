<template>
  <div class="app-container">
    <template>
      <el-table :data="projects" style="width: 100%">
        <el-table-column label="Project No" width="150" sortable>
          <template slot-scope="{ row }">
            <router-link :to="'/project/projectView/' + row.ProjectNo" class="link-type">
              <span> {{ row.ProjectNo }}</span>
            </router-link>
          </template>
        </el-table-column>
        <el-table-column label="Title" prop="Title" sortable />
      </el-table>
    </template>
  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'

export default {
  name: 'ProjectBrowser',
  components: {},
  data() {
    return {
      projects: null
    }
  },
  mounted() {
    this.getProjects()
  },
  methods: {
    getProjects() {
      requestBN({
        url: '/project',
        methood: 'get'
      }).then(response => {
        this.projects = response.data
      })
    }
  }
}
</script>
