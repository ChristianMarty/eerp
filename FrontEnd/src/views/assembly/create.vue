<template>
  <div class="app-container">
    <h1>Create Assembly Item</h1>
    <el-divider />

    <el-form label-width="150px">

      <el-form-item label="Name:">
        <el-input v-model="assemblyData.Name" />
      </el-form-item>

      <el-form-item label="Description:">
        <el-input v-model="assemblyData.Description" />
      </el-form-item>

      <el-form-item>
        <el-button type="primary" @click="save()">Save</el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
import Assembly from '@/api/assembly'
const assembly = new Assembly()

export default {
  components: {},
  data() {
    return {
      assemblyData: Object.assign({}, assembly.assemblyCreateParameters)
    }
  },
  mounted() {
  },
  methods: {
    save() {
      assembly.create(this.assemblyData).then(response => {
        this.$router.push('/assembly/item/' + response)
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
