<template>
  <div class="dashboard-container">
    <el-table
      :data="data"
      :default-sort="{ prop: 'Package', order: 'descending' }"
      height="80vh"
      border
      style="width: 100%"
    >
      <el-table-column prop="Title" sortable label="Process" width="240">
        <template slot-scope="{ row }">
          <a :href="row.Path" target="blank">
            <el-button>
              {{ row.Titel }}
            </el-button>
          </a>
          </router-link>
        </template>
      </el-table-column>
      <el-table-column prop="Description" label="Description" sortable />
    </el-table>

  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'

export default {
  name: 'Processes',
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
        url: '/process',
        methood: 'get'
      }).then(response => {
        this.data = response.data
      })
    }
  }
}
</script>
