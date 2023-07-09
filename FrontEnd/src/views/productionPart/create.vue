<template>
  <div class="app-container">
    <h1>Create Production Part</h1>
    <el-divider />
    <el-form
      ref="inputForm"
      class="form-container"
      label-width="130px"
    >
      <el-form-item label="Prefix:">
        <el-select v-model="productionPartCreateParameters.PrefixId" filterable>
          <el-option
            v-for="item in prefix"
            :key="item.Id"
            :label="item.Prefix +' - '+ item.Name"
            :value="item.Id"
          />
        </el-select>
      </el-form-item>
      <el-form-item label="Description:">
        <el-input v-model="productionPartCreateParameters.Description" />
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="onCreate()">Create</el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
import ProductionPart from '@/api/productionPart'
const productionPart = new ProductionPart()

export default {
  name: 'ProductionPartCreate',
  data() {
    return {
      prefix: [],
      productionPartCreateParameters: Object.assign({}, productionPart.createParameters)
    }
  },
  mounted() {
    this.getPrefix()
  },
  created() {
  },
  methods: {
    getPrefix() {
      productionPart.prefix(false, true).then(response => {
        this.prefix = response
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
      productionPart.create(this.productionPartCreateParameters).then(response => {
        this.$router.push('/productionPart/item/' + response['ProductionPartNumber'])
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
