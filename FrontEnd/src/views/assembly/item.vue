<template>
  <div class="app-container">
    <h1>{{ assemblyData.AssemblyBarcode }}, {{ assemblyData.Name }}</h1>
    <p>{{ assemblyData.Description }}</p>

    <el-divider />

    <el-button
      v-permission="['assembly.unit.add']"
      type="primary"
      icon="el-icon-plus"
      circle
      style="margin-top: 20px; margin-bottom: 20px"
      @click="addUnit()"
    />

    <el-table
      :data="assemblyData.Unit"
      style="width: 100%"
      height="82vh"
    >
      <el-table-column prop="AssemblyUnitBarcode" label="Assembly Unit No" sortable width="180">
        <template slot-scope="{ row }">
          <router-link :to="'/assembly/unit/item/' + row.AssemblyUnitBarcode" class="link-type">
            <span>{{ row.AssemblyUnitBarcode }}</span>
          </router-link>
        </template>
      </el-table-column>
      <el-table-column prop="SerialNumber" label="SerialNumber" sortable />
      <el-table-column prop="LocationName" label="Location" sortable />
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
              :key="wo.Id"
              :label="wo.WorkOrderBarcode + ' -- ' + wo.Title"
              :value="wo.WorkOrderNo"
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
      unitCreateVisible: false
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
  },
  methods: {
    setTitle() {
      const route = Object.assign({}, this.tempRoute, {
        title: `${this.$route.params.AssemblyNumber}`
      })
      this.$store.dispatch('tagsView/updateVisitedView', route)
      document.title = `${this.$route.params.AssemblyNumber}`
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
        }).catch(response => {
          this.$message({
            showClose: true,
            message: response,
            duration: 0,
            type: 'error'
          })
        })
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    }
  }

}
</script>
