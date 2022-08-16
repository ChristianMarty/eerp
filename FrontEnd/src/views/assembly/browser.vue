<template>
  <div class="app-container">

    <el-table
      ref="stockTable"
      :data="assemblyData"
      style="width: 100%"
      height="82vh"
    >
      <el-table-column prop="Barcode" label="Assembly No" sortable>
        <template slot-scope="{ row }">
          <router-link :to="'/assembly/item/' + row.Barcode" class="link-type">
            <span>{{ row.Barcode }}</span>
          </router-link>
        </template>
      </el-table-column>
      <el-table-column prop="Name" label="Name" sortable/>
      <el-table-column prop="SerialNumber" label="SerialNumber" sortable/>
      <el-table-column prop="LocationName" label="Location" sortable />
    </el-table>
  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'
import siFormatter from '@/utils/siFormatter'

export default {
  name: 'AssemblyBrowser',
  data() {
    return {
      assemblyData: [],
    }
  },
  mounted() {
    this.getAssembly()
  },
  methods: {
    getAssembly() {
      requestBN({
        url: '/assembly',
        methood: 'get'
      }).then(response => {
        this.assemblyData = response.data
      })
    }
  }
}
</script>
