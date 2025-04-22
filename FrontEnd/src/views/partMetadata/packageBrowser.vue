<template>
  <div class="app-container">
    <template>
      <el-table
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
import requestBN from '@/utils/requestBN'

export default {
  name: 'PackageBrowser',
  components: {},
  data() {
    return {
      packages: null
    }
  },
  mounted() {
    this.getPackages()
  },
  methods: {
    getPackages() {
      requestBN({
        url: '/part/package',
        method: 'get'
      }).then(response => {
        this.packages = response.data
      })
    }
  }
}
</script>
