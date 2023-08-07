<template>
  <div class="app-container">
    <template>
      <el-checkbox v-model="hideClosed" @change="getWorkOrders()">Hide complete orders</el-checkbox>
      <el-table
        v-loading="loading"
        :data="workOrders"
        style="width: 100%"
        element-loading-text="Loading Work Orders"
      >
        <el-table-column label="Work Order No" prop="WorkOrderNumber" width="150" sortable>
          <template slot-scope="{ row }">
            <router-link
              :to="'/workOrder/workOrderView/' + row.WorkOrderNumber"
              class="link-type"
            >
              <span> {{ row.WorkOrderBarcode }}</span>
            </router-link>
          </template>
        </el-table-column>
        <el-table-column label="Title" prop="Title" sortable />
        <el-table-column label="Project" prop="ProjectTitle" sortable />
        <el-table-column label="Build Quantity" prop="Quantity" sortable />
        <el-table-column label="Status" prop="Status" sortable />
      </el-table>
    </template>
  </div>
</template>

<script>
import WorkOrder from '@/api/workOrder'
const workOrder = new WorkOrder()

export default {
  name: 'WorkOrderBrowser',
  components: {},
  data() {
    return {
      loading: true,
      hideClosed: true,
      workOrders: null
    }
  },
  mounted() {
    this.getWorkOrders()
  },
  methods: {
    getWorkOrders() {
      this.loading = true
      workOrder.search(null, this.hideClosed).then(response => {
        this.workOrders = response
        this.loading = false
      })
    }
  }
}
</script>
