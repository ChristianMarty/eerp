<template>
  <div class="app-container">

    <el-table
      ref="stockTable"
      :data="assemblyData"
      style="width: 100%"
      height="82vh"
    >
      <el-table-column prop="AssemblyItemBarcode" label="Assembly Item No" sortable>
        <template slot-scope="{ row }">
          <router-link :to="'/assembly/item/' + row.AssemblyItemBarcode" class="link-type">
            <span>{{ row.AssemblyItemBarcode }}</span>
          </router-link>
        </template>
      </el-table-column>
      <el-table-column prop="AssemblyBarcode" label="Assembly No" sortable />
      <el-table-column prop="Name" label="Name" sortable />
      <el-table-column prop="Description" label="Description" sortable />
      <el-table-column prop="SerialNumber" label="SerialNumber" sortable />
      <el-table-column prop="LocationName" label="Location" sortable />
    </el-table>
  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'

export default {
  name: 'AssemblyBrowser',
  data() {
    return {
      assemblyData: []
    }
  },
  mounted() {
    this.getAssembly()
  },
  methods: {
    getAssembly() {
      requestBN({
        url: '/assemblyItem',
        methood: 'get'
      }).then(response => {
        this.assemblyData = response.data
      })
    }
  }
}
</script>
