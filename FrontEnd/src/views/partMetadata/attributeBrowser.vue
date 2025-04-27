<template>
  <div class="app-container">
    <template>
      <el-table
        v-loading="loading"
        element-loading-text="Loading Part Attributes ..."
        element-loading-spinner="el-icon-loading"
        :data="attributes"
        style="width: 100%;"
        :cell-style="{ padding: '0', height: '20px' }"
        row-key="Id"
        border
        :tree-props="{ children: 'Children' }"
      >
        >
        <el-table-column prop="Name" label="Name" />
        <el-table-column prop="Unit" label="Unit" />
        <el-table-column prop="Symbol" label="Symbol" />
        <el-table-column prop="Id" label="Id" />
        <el-table-column prop="Type" label="Type" />
        <el-table-column prop="Scale" label="Scale" /></el-table></template>
  </div>
</template>

<script>

import Part from '@/api/part'
const part = new Part()

export default {
  name: 'AttributeBrowser',
  components: {},
  data() {
    return {
      attributes: [],
      loading: true
    }
  },
  mounted() {
    this.getAttributes()
  },
  methods: {
    getAttributes() {
      part.attribute.list().then(response => {
        this.attributes = response
        this.loading = false
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
