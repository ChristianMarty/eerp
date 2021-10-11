<template>
  <div class="app-container">
    <h1>Create Work Order</h1>
    <el-divider />

    <el-form label-width="150px">
      <el-form-item label="Titel:">
        <el-input v-model="form.Titel" placeholder="Please input" />
      </el-form-item>

      <el-form-item label="Project:">
        <el-select v-model="form.ProjectId" filterable>
          <el-option
            v-for="item in projects"
            :key="item.Id"
            :label="item.Titel"
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
import requestBN from '@/utils/requestBN'

const defaultForm = {
  Titel: '',
  ProjectId: '',
  Quantity: ''
}

export default {
  components: {},
  data() {
    return {
      form: Object.assign({}, defaultForm),
      projects: null
    }
  },
  mounted() {
    this.getProjects()
  },
  methods: {
    getProjects() {
      requestBN({
        url: '/project',
        methood: 'get'
      }).then(response => {
        this.projects = response.data
      })
    },
    save() {
      requestBN({
        method: 'post',
        url: '/workOrder',
        data: { data: this.form }
      }).then(response => {
        if (response.error !== null) {
          this.$message({
            showClose: true,
            message: response.error,
            duration: 0,
            type: 'error'
          })
        } else {
          this.$router.push(
            '/workOrder/workOrderView/' + response.data.WorkOrderId
          )
        }
      })
    }
  }
}
</script>
