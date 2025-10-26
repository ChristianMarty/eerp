<template>
  <div class="app-container">
    <h1>{{ assemblyData.ItemCode }}, {{ assemblyData.Name }}</h1>
    <p>{{ assemblyData.Description }}</p>

    <router-link :to="'/productionPart/item/' + assemblyData.ProductionPartCode" class="link-type">
      <span>{{ assemblyData.ProductionPartCode }}</span>
    </router-link>

    <el-divider />
    <el-input ref="serialNumberSearchInput" v-model="serialNumberSearchInput" placeholder="Serial Number Search" @keyup.enter.native="serialNumberSearch(serialNumberSearchInput)">
      <el-button slot="append" icon="el-icon-search" @click="serialNumberSearch(serialNumberSearchInput)" />
    </el-input>

    <el-button
      v-permission="['Assembly_Unit_Create']"
      type="primary"
      icon="el-icon-plus"
      circle
      style="margin-top: 20px; margin-bottom: 20px"
      @click="addUnit()"
    />

    <el-table
      :data="assemblyData.Unit"
      style="width: 100%"
      border
      :cell-style="{ padding: '0', height: '20px' }"
    >
      <el-table-column prop="ItemCode" label="Assembly Unit" sortable width="150">
        <template slot-scope="{ row }">
          <router-link :to="'/assembly/unit/item/' + row.ItemCode" class="link-type">
            <span>{{ row.ItemCode }}</span>
          </router-link>
        </template>
      </el-table-column>
      <el-table-column prop="SerialNumber" label="Serial Number" sortable width="250" />
      <el-table-column prop="WorkOrderCode" label="Work Order" sortable width="300">
        <template slot-scope="{ row }">
          <router-link
            :to="'/workOrder/item/' + row.WorkOrderCode"
            class="link-type"
          >
            <span>{{ row.WorkOrderCode }}</span>
          </router-link>
          <span> - {{ row.WorkOrderName }}</span>
        </template>
      </el-table-column>
      <el-table-column prop="LocationCode" label="Location" sortable width="300">
        <template slot-scope="{ row }">
          <router-link
            :to="'/location/item/' + row.LocationCode"
            class="link-type"
          >
            <span>{{ row.LocationCode }}</span>
          </router-link>
          <span> - {{ row.LocationName }}</span>
        </template>
      </el-table-column>
      <el-table-column prop="LastHistoryTitle" label="Last History Title" sortable />
      <el-table-column prop="LastHistoryType" label="Last History Type" sortable width="180" />
      <el-table-column prop="LastInspectionPass" label="Last Inspection" sortable width="160">
        <template slot-scope="{ row }">
          <span v-if="row.LastInspectionPass === true" class="pass">Pass</span>
          <span v-if="row.LastInspectionPass === false" class="fail">Fail</span>
        </template>
      </el-table-column>
      <el-table-column prop="LastTestResult" label="Last Test" sortable width="160">
        <template slot-scope="{ row }">
          <span v-if="row.LastTestPass === true" class="pass">Pass</span>
          <span v-if="row.LastTestPass === false" class="fail">Fail</span>
        </template>
      </el-table-column>
      <el-table-column prop="ShippingClearance" label="Shipping Clearance" sortable width="200">
        <template slot-scope="{ row }">
          <span v-if="row.ShippingProhibited === true" class="fail">Prohibited</span>
          <span v-else-if="row.ShippingClearance === true" class="pass">Approved</span>
        </template>
      </el-table-column>
    </el-table>

    <el-dialog title="Add History Item" :visible.sync="unitCreateVisible">
      <el-form label-width="120px">
        <el-form-item label="Serial Number:">
          <el-input ref="serialNumberInput" v-model="assemblyCreateData.SerialNumber" />
        </el-form-item>
        <el-form-item label="Work Order:">
          <el-select v-model="assemblyCreateData.WorkOrderNumber" filterable style="width: 100%">
            <el-option
              v-for="wo in workOrders"
              :key="wo.WorkOrderNumber"
              :label="wo.WorkOrderBarcode + ' -- ' + wo.Name"
              :value="wo.WorkOrderNumber"
            />
          </el-select>
        </el-form-item>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="createUnit();">Save</el-button>
        <el-button @click="unitCreateVisible = false">Cancel</el-button>
      </span>
    </el-dialog>

  </div>
</template>

<script>
import permission from '@/directive/permission/index.js'

import Assembly from '@/api/assembly'
const assembly = new Assembly()

import WorkOrder from '@/api/workOrder'
const workOrder = new WorkOrder()

export default {
  name: 'AssemblyView',
  components: { },
  directives: { permission },
  data() {
    return {
      assemblyData: {},
      assemblyCreateData: Object.assign({}, assembly.assemblyCreate),
      unitCreateVisible: false,
      serialNumberSearchInput: ''
    }
  },
  created() {
    // Why need to make a copy of this.$route here?
    // Because if you enter this page and quickly switch tag, may be in the execution of the setTagsViewTitle function, this.$route is no longer pointing to the current page
    // https://github.com/PanJiaChen/vue-element-admin/issues/1221
    this.tempRoute = Object.assign({}, this.$route)
  },
  async mounted() {
    this.assemblyData = await assembly.item(this.$route.params.AssemblyNumber)
    this.workOrders = await workOrder.search('InProgress')

    this.setTitle()

    this.$refs.serialNumberSearchInput.focus()
  },
  methods: {
    setTitle() {
      const route = Object.assign({}, this.tempRoute, {
        title: `${this.$route.params.AssemblyNumber}`
      })
      this.$store.dispatch('tagsView/updateVisitedView', route)
      document.title = `${this.$route.params.AssemblyNumber}`
    },
    serialNumberSearch(SerialNumber) {
      console.log('lll')
      console.log(SerialNumber)
      try {
        const AssemblyUnitBarcode = this.assemblyData.Unit.find(unit => unit.SerialNumber === SerialNumber).AssemblyUnitBarcode
        this.$router.push('/assembly/unit/item/' + AssemblyUnitBarcode)
      } catch (e) {
        this.$message({
          showClose: true,
          message: 'Serial Number does not exist',
          duration: 3000,
          type: 'warning'
        })
      }
    },
    addUnit() {
      this.unitCreateVisible = true
      this.$refs.serialNumberInput.focus()
    },
    createUnit() {
      this.assemblyCreateData.AssemblyNumber = this.assemblyData.AssemblyNumber

      assembly.unit.create(this.assemblyCreateData).then(response => {
        assembly.item(this.$route.params.AssemblyNumber).then(response => {
          this.assemblyData = response
          this.assemblyCreateData = Object.assign({}, assembly.assemblyCreate)
          this.unitCreateVisible = false
        })
      })
    }
  }

}
</script>

<style scoped>
.pass {
  color:green;
}

.fail {
  color: red;
}

</style>

