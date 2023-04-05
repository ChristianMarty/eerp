<template>
  <div class="app-container">
    <h1>Create Work Order</h1>
    <el-divider />

    <el-form label-width="150px">
      <el-form-item label="Titel:">
        <el-input v-model="form.Title" placeholder="Please input" />
      </el-form-item>

      <el-form-item label="Project:">
        <el-select v-model="form.ProjectId" filterable>
          <el-option
            v-for="item in projects"
            :key="item.Id"
            :label="item.Title"
            :value="item.Id"
          />
        </el-select>
      </el-form-item>

      <el-form-item label="Build Quantity:">
        <el-input-number v-model="form.Quantity" :min="1" :max="1000" />
      </el-form-item>

      <el-form-item>
        <el-button type="primary" @click="save">Save</el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
import WorkOrder from '@/api/workOrder'
const workOrder = new WorkOrder()

import Project from '@/api/project'
const project = new Project()

export default {
  components: {},
  data() {
    return {
      form: Object.assign({}, workOrder.createParameters),
      projects: null
    }
  },
  mounted() {
    project.search().then(response => {
      this.projects = response
    })
  },
  methods: {
    save() {
      workOrder.create(this.form).then(response => {
        this.$router.push(
          '/workOrder/workOrderView/' + response.WorkOrderNo
        )
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
