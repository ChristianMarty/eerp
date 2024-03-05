<template>
  <div class="app-container">
    <el-table
      :data="processes"
      :default-sort="{ prop: 'Package', order: 'descending' }"
      border
      style="width: 100%"
    >
      <el-table-column prop="Name" sortable label="Process" width="240">
        <template slot-scope="{ row }">
          <el-button @click="run(row)">
            {{ row.Name }}
          </el-button>
        </template>
      </el-table-column>
      <el-table-column prop="Description" label="Description" sortable />
    </el-table>
  </div>
</template>

<script>
import Process from '@/api/process'
const process = new Process()

export default {
  name: 'Processes',
  data() {
    return {
      processes: null
    }
  },
  async mounted() {
    this.processes = await process.list()
  },
  methods: {
    run(process) {
      window.open(process.Path, '_blank').focus()
    }
  }
}
</script>
