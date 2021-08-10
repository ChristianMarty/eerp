<template>
  <div class="app-container">
    <template>
      <el-table
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
import requestBN from '@/utils/requestBN'

export default {
  name: 'AttributeBrowser',
  components: {},
  data() {
    return {
      attributes: null
    }
  },
  mounted() {
    this.getAttributes()
  },
  methods: {
    getAttributes() {
      requestBN({
        url: '/part/attribute',
        methood: 'get'
      }).then(response => {
        this.attributes = response.data
      })
    }
  }
}
</script>
