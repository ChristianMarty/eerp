<template>
  <div class="dashboard-container">
    <el-table
      :data="data"
      height="80vh"
      border
      style="width: 100%"
    >
      <el-table-column prop="Title" sortable label="Report" width="240">
        <template slot-scope="{ row }">
          <a :href="row.Path" target="blank">
            <el-button>
              {{ row.Title }}
            </el-button>
          </a>
        </template>
      </el-table-column>
      <el-table-column prop="Description" label="Description" sortable />
    </el-table>

  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'

export default {
  name: 'Reports',
  data() {
    return {
      data: null
    }
  },
  computed: {},
  created() {},
  mounted() {
    this.getData()
  },
  methods: {
    getData() {
      requestBN({
        url: '/report',
        methood: 'get'
      }).then(response => {
        this.data = response.data
      })
    }
  }
}
</script>
