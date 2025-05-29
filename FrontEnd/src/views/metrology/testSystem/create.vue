<template>
  <div class="app-container">
    <h1>Create Test System</h1>
    <el-divider />

    <el-form label-width="150px">

      <el-form-item label="Name:">
        <el-input v-model="testSystemData.Name" />
      </el-form-item>

      <el-form-item label="Description:">
        <el-input v-model="testSystemData.Description" />
      </el-form-item>

      <el-form-item>
        <el-button type="primary" @click="save()">Save</el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>

import Metrology from '@/api/metrology'
const metrology = new Metrology()

export default {
  components: {},
  data() {
    return {
      testSystemData: Object.assign({}, metrology.testingCreateParameters)
    }
  },
  async mounted() {

  },
  methods: {
    save() {
      metrology.create(this.testSystemData).then(response => {
        this.$router.push('/metrology/item/' + response.TestSystemBarcode)
      })
    }
  }
}
</script>
