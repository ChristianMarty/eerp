<template>
  <div class="app-container">
    <template>
      <el-table
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
import requestBN from '@/utils/requestBN'

export default {
  name: 'ClassBrowser',
  components: {},
  data() {
    return {
      classes: null
    }
  },
  mounted() {
    this.getClasses()
  },
  methods: {
    getClasses() {
      requestBN({
        url: '/part/class',
        methood: 'get'
      }).then(response => {
        this.classes = response.data
      })
    }
  }
}
</script>
