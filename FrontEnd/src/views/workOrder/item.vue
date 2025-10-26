<template>
  <div class="app-container">
    <h1>
      {{ workOrderData.ItemCode }} --- {{ workOrderData.Title }} ---
      {{ workOrderData.ProjectTitle }}
    </h1>
    <el-divider />
    <p><b>Build Quantity:</b> {{ workOrderData.Quantity }}</p>
    <p><b>Status:</b> {{ workOrderData.Status }}</p>

    <el-select
      v-model="statusSelected"
      v-permission="['WorkOrder_Edit']"
      placeholder="Status"
      @change="updateStatus()"
    >
      <el-option
        v-for="item in statusOptions"
        :key="item"
        :label="item"
        :value="item"
      />
    </el-select>

    <h2>Used Parts</h2>

    <el-table
      :data="workOrderData.PartsUsed"
      style="width: 100%"
      :summary-method="calcSum"
      show-summary
    >
      <el-table-column prop="StockNumber" label="Stock No" width="120" sortable>
        <template slot-scope="{ row }">
          <router-link :to="'/stock/item/' + row.StockNumber" class="link-type">
            <span>{{ row.StockNumber }}</span>
          </router-link>
        </template>
      </el-table-column>

      <el-table-column prop="ProductionPartNumber" label="Production Part Number" sortable width="240">
        <template slot-scope="{ row }">
          <template v-for="part in row.ProductionPartNumber">
            <router-link
              :to="('/productionPart/item/' + part)"
              class="link-type"
            >
              <span>{{ part }} </span>
            </router-link>
          </template>
        </template>
      </el-table-column>

      <el-table-column prop="ManufacturerDisplayName" label="Manufacturer" sortable />

      <el-table-column
        prop="ManufacturerPartNumber"
        sortable
        label="Manufacturer Part No"
        width="220"
      >
        <template slot-scope="{ row }">
          <router-link
            :to="'/manufacturerPart/partNumber/item/' + row.ManufacturerPartNumberId"
            class="link-type"
          >
            <span>{{ row.ManufacturerPartNumber }}</span>
          </router-link>
        </template>
      </el-table-column>

      <el-table-column prop="Quantity" label="Quantity" sortable />
      <el-table-column prop="RemovalDate" label="Remove Date" sortable />
      <el-table-column prop="Price" label="Price" sortable />
      <el-table-column label="Total" width="120">
        <template slot-scope="{ row }">
          <span>{{ (Math.round((Math.abs(row.Quantity)*row.Price) * 100000)/100000) }}</span>
        </template>
      </el-table-column>
    </el-table>
  </div>
</template>

<script>
import permission from '@/directive/permission/index.js'

import WorkOrder from '@/api/workOrder'
const workOrder = new WorkOrder()

export default {
  name: 'WorkOrderView',
  components: {},
  directives: { permission },
  data() {
    return {
      workOrderData: {},
      statusOptions: [],
      statusSelected: null
    }
  },
  async mounted() {
    this.getWorkOrderData()
    this.statusOptions = await workOrder.status()
  },
  created() {
    // Why need to make a copy of this.$route here?
    // Because if you enter this page and quickly switch tag, may be in the execution of the setTagsViewTitle function, this.$route is no longer pointing to the current page
    // https://github.com/PanJiaChen/vue-element-admin/issues/1221
    this.tempRoute = Object.assign({}, this.$route)
  },
  methods: {
    getWorkOrderData() {
      workOrder.item(this.$route.params.workOrderNo).then(response => {
        this.workOrderData = response
        this.setTitle()
      })
    },
    updateStatus() {
      workOrder.updateStatus(this.$route.params.workOrderNo, this.statusSelected).then(response => {
        this.getWorkOrderData()
      })
    },
    setTitle() {
      const route = Object.assign({}, this.tempRoute, {
        title: `${this.$route.params.workOrderNo}`
      })
      document.title = `${this.$route.params.workOrderNo}`
      this.$store.dispatch('tagsView/updateVisitedView', route)
    },
    calcSum(param) {
      let total = 0
      let totalQuantity = 0
      this.workOrderData.PartsUsed.forEach(element => {
        const line = Math.abs(element.Quantity) * element.Price
        total += line
        totalQuantity += Math.abs(element.Quantity)
      })

      const totalLine = []
      totalLine[0] = 'Total'
      totalLine[4] = totalQuantity * -1
      totalLine[7] = Math.round(total * 100000) / 100000
      return totalLine
    }
  }
}
</script>
