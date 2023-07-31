<template>
  <div class="app-container">
    <h1>Create Specification Part</h1>
    <el-divider />
    <el-form
      ref="inputForm"
      class="form-container"
      label-width="130px"
    >
      <el-form-item label="Type:">
        <el-select v-model="specificationPartCreateParameters.Type" filterable>
          <el-option
            v-for="item in type"
            :key="item"
            :label="item"
            :value="item"
          />
        </el-select>
      </el-form-item>
      <el-form-item label="Title:">
        <el-input v-model="specificationPartCreateParameters.Title" />
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="onCreate()">Create</el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
import SpecificationPart from '@/api/specificationPart'
const specificationPart = new SpecificationPart()

export default {
  components: {},
  data() {
    return {
      type: [],
      specificationPartCreateParameters: Object.assign({}, specificationPart.createParameters)
    }
  },
  mounted() {
    this.getType()
  },
  methods: {
    getType() {
      specificationPart.type().then(response => {
        this.type = response
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    onCreate() {
      specificationPart.create(this.specificationPartCreateParameters).then(response => {
        this.$router.push('/specificationPart/item/' + response['Id'])
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
