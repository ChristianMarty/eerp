<template>
  <div class="app-container">
    <h1>Create Assembly Item</h1>
    <el-divider />

    <el-form label-width="150px">

      <el-form-item label="Assembly:">
        <el-select v-model="assemblyData.AssemblyNumber" filterable style="width: 100%">
          <el-option
            v-for="item in assembly"
            :key="item.AssemblyNo"
            :label="item.Name + ' -- ' + item.Description"
            :value="item.AssemblyNo"
          />
        </el-select>
      </el-form-item>

      <el-form-item label="Serial Number:">
        <el-input v-model="assemblyData.SerialNumber" />
      </el-form-item>

      <el-form-item label="Work Order:">
        <el-select v-model="assemblyData.WorkOrderNumber" filterable style="width: 100%">
          <el-option
            v-for="wo in workOrders"
            :key="wo.Id"
            :label="wo.WorkOrderBarcode + ' -- ' + wo.Title"
            :value="wo.WorkOrderNo"
          />
        </el-select>
      </el-form-item>

      <el-form-item>
        <el-button type="primary" @click="save()">Save</el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'

const assemblyDataEmpty = {
  SerialNumber: '',
  AssemblyNumber: '',
  WorkOrderNumber: ''
}

export default {
  components: {},
  data() {
    return {
      assemblyData: Object.assign({}, assemblyDataEmpty),
      supplierName: '',
      assembly: [],
      workOrders: []
    }
  },
  mounted() {
    this.getAssembly()
    this.getWorkOrders()
  },
  methods: {
    getAssembly() {
      requestBN({
        url: '/assembly',
        methood: 'get'
      }).then(response => {
        this.assembly = response.data
      })
    },
    getWorkOrders() {
      requestBN({
        url: '/workOrder',
        methood: 'get',
        params: { Status: 'InProgress' }
      }).then(response => {
        this.workOrders = response.data
      })
    },
    save() {
      requestBN({
        method: 'post',
        url: '/assemblyItem',
        data: this.assemblyData
      }).then(response => {
        if (response.error !== null) {
          this.$message({
            showClose: true,
            message: response.error,
            duration: 0,
            type: 'error'
          })
        } else {
          this.$router.push('/assembly/item/' + response.data)
        }
      })
    }
  }
}
</script>
