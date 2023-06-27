<template>
  <div class="app-container">
    <template>
      <el-table :data="workOrders" style="width: 100%">
        <el-table-column label="Work Order No" width="150" sortable>
          <template slot-scope="{ row }">
            <router-link
              :to="'/workOrder/workOrderView/' + row.WorkOrderNo"
              class="link-type"
            >
              <span> {{ row.WorkOrderNo }}</span>
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


export default {
  name: 'SpecificationPartBrowser',
  components: {},
  data() {
    return {

    }
  },
  mounted() {
    workOrder.search(null, this.hideClosed).then(response => {
      this.workOrders = response
    })
  },
  methods: {
  }
}
</script>
