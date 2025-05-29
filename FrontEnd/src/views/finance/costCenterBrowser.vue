<template>
  <div class="app-container">
    <template>
      <el-table
        v-loading="loading"
        :data="costCenter"
        style="width: 100%"
        element-loading-text="Loading Cost Centers"
      >
        <el-table-column label="Cost Center Number" width="200" sortable>
          <template slot-scope="{ row }">
            <router-link :to="'/finance/costCenter/item/' + row.CostCenterNumber" class="link-type">
              <span> {{ row.Barcode }}</span>
            </router-link>
          </template>
        </el-table-column>
        <el-table-column label="Name" prop="Name" sortable />
        <el-table-column label="Description" prop="Description" sortable />
      </el-table>
    </template>
  </div>
</template>

<script>
import Finance from '@/api/finance'
const finance = new Finance()

export default {
  name: 'CostCenterBrowser',
  components: {},
  data() {
    return {
      loading: true,
      costCenter: null
    }
  },
  mounted() {
    this.getProjects()
  },
  methods: {
    getProjects() {
      finance.costCenter.list().then(response => {
        this.costCenter = response
        this.loading = false
      })
    }
  }
}
</script>
