<template>
  <div class="app-container">
    <h1>
      WO-{{ workOrderData.WorkOrderNo }} --- {{ workOrderData.Titel }} ---
      {{ workOrderData.ProjectTitel }}
    </h1>
    <el-divider />
    <p><b>Build Quantity:</b> {{ workOrderData.Quantity }}</p>
    <p><b>Status:</b> {{ workOrderData.Status }}</p>
    <h2>Used Parts</h2>

    <el-table :data="workOrderData.PartsUsed" style="width: 100%">
      <el-table-column prop="StockNo" label="Stock No" width="120" sortable>
        <template slot-scope="{ row }">
          <router-link :to="'/stock/item/' + row.StockNo" class="link-type">
            <span>{{ row.StockNo }}</span>
          </router-link>
        </template>
      </el-table-column>
      <el-table-column prop="ManufacturerName" label="Manufacturer" sortable />
      <el-table-column
        prop="ManufacturerPartNumber"
        sortable
        label="Manufacturer Part No"
        width="220"
      >
        <template slot-scope="{ row }">
          <router-link
            :to="'/mfrParts/partView/' + row.ManufacturerPartId"
            class="link-type"
          >
            <span>{{ row.ManufacturerPartNumber }}</span>
          </router-link>
        </template>
      </el-table-column>

      <el-table-column prop="Quantity" label="Quantity" sortable />
      <el-table-column prop="RemovalDate" label="Remove Date" sortable />
    </el-table>
  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'

export default {
  name: 'WorkOrderView',
  components: {},
  data() {
    return {
      workOrderData: null
    }
  },
  mounted() {
    this.getWorkOrderData()
    // this.setTagsViewTitle();
  },
  created() {
    // Why need to make a copy of this.$route here?
    // Because if you enter this page and quickly switch tag, may be in the execution of the setTagsViewTitle function, this.$route is no longer pointing to the current page
    // https://github.com/PanJiaChen/vue-element-admin/issues/1221
    this.tempRoute = Object.assign({}, this.$route)
  },
  methods: {
    getWorkOrderData() {
      requestBN({
        url: '/workOrder/item',
        methood: 'get',
        params: { WorkOrderNo: this.$route.params.workOrderNo }
      }).then(response => {
        this.workOrderData = response.data
      })
    },
    setTagsViewTitle() {
      const route = Object.assign({}, this.tempRoute, {
        title: `${this.$route.params.projectNo}`
      })
      this.$store.dispatch('tagsView/updateVisitedView', route)
    }
  }
}
</script>
