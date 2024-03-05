<template>
  <div class="app-container">
    <el-table
      :data="reports"
      border
      style="width: 100%"
    >
      <el-table-column prop="Name" sortable label="Report" width="240">
        <template slot-scope="{ row }">
          <a :href="row.Path" target="blank">
            <el-button>
              {{ row.Name }}
            </el-button>
          </a>
        </template>
      </el-table-column>
      <el-table-column prop="Description" label="Description" sortable />
    </el-table>

  </div>
</template>

<script>
import Report from '@/api/report'
const report = new Report()

export default {
  name: 'Reports',
  data() {
    return {
      reports: null
    }
  },
  async mounted() {
    this.reports = await report.list()
  },
  methods: {
  }
}
</script>
