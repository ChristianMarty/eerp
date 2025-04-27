<template>
  <div class="app-container">
    <template>
      <el-table
        v-loading="loading"
        element-loading-text="Loading Part Packages ..."
        element-loading-spinner="el-icon-loading"
        :data="packages"
        style="width: 100%;"
        :cell-style="{ padding: '0', height: '20px' }"
        row-key="Id"
        border
        :tree-props="{ children: 'Children' }"
      >
        >
        <el-table-column prop="Name" label="Name" />
        <el-table-column prop="SMD" label="SMD">
          <template slot-scope="scope">
            <span v-if="scope.row.SMD == true">
              Yes
            </span>
            <span v-if="scope.row.SMD == false">
              No
            </span>
          </template>
        </el-table-column>

        <el-table-column prop="PinCount" label="Pin Count" />
      </el-table>
    </template>
  </div>
</template>

<script>
import Part from '@/api/part'
const part = new Part()

export default {
  name: 'PackageBrowser',
  components: {},
  data() {
    return {
      packages: [],
      loading: true
    }
  },
  mounted() {
    this.getPackages()
  },
  methods: {
    getPackages() {
      part.package.list().then(response => {
        this.packages = response
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
