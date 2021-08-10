<template>
  <div class="app-container">
    <template>
      <el-table
        :data="suppliers"
        style="width: 100%;"
        :cell-style="{ padding: '0', height: '20px' }"
        row-key="Id"
        border
        :tree-props="{ children: 'Children' }"
      >
        >
        <el-table-column prop="Name" label="Name" />
        <el-table-column prop="Id" label="Id" />
      </el-table>
    </template>
  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'

export default {
  name: 'ClassBrowser',
  components: {},
  data() {
    return {
      suppliers: null
    }
  },
  mounted() {
    this.getSuppliers()
  },
  methods: {
    getSuppliers() {
      requestBN({
        url: '/supplier',
        methood: 'get'
      }).then(response => {
        this.suppliers = response.data
      })
    }
  }
}
</script>
