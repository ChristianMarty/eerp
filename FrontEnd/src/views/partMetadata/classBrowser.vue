<template>
  <div class="app-container">
    <template>
      <el-table
        v-loading="loading"
        element-loading-text="Loading Part Classes ..."
        element-loading-spinner="el-icon-loading"
        :data="classes"
        style="width: 100%;"
        :cell-style="{ padding: '0', height: '20px' }"
        row-key="Id"
        border
        :tree-props="{ children: 'Children' }"
      >
        >
        <el-table-column prop="Name" label="Name" />
        <el-table-column prop="Prefix" label="Prefix" />
        <el-table-column prop="Id" label="Id" />
        </el-table-column></el-table></template>
  </div>
</template>

<script>
import Part from '@/api/part'
const part = new Part()

export default {
  name: 'ClassBrowser',
  components: {},
  data() {
    return {
      classes: [],
      loading: true
    }
  },
  mounted() {
    this.getClasses()
  },
  methods: {
    getClasses() {
      part.class.list(0, false, true).then(response => {
        this.classes = response
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
