<template>
  <div class="app-container">

    <el-table
      ref="stockTable"
      :data="assemblyData"
      style="width: 100%"
      height="82vh"
    >
      <el-table-column prop="ItemCode" label="Assembly" sortable width="170">
        <template slot-scope="{ row }">
          <router-link :to="'/assembly/item/' + row.ItemCode" class="link-type">
            <span>{{ row.ItemCode }}</span>
          </router-link>
        </template>
      </el-table-column>
      <el-table-column prop="Name" label="Name" sortable width="250" />
      <el-table-column prop="ProductionPartCode" label="Production Part" sortable width="170">
        <template slot-scope="{ row }">
          <router-link :to="'/productionPart/item/' + row.ProductionPartCode" class="link-type">
            <span>{{ row.ProductionPartCode }}</span>
          </router-link>
        </template>
      </el-table-column>
      <el-table-column prop="Description" label="Description" sortable />
    </el-table>
  </div>
</template>

<script>
import Assembly from '@/api/assembly'
const assembly = new Assembly()

export default {
  name: 'AssemblyBrowser',
  data() {
    return {
      assemblyData: []
    }
  },
  async mounted() {
    this.assemblyData = await assembly.search()
  },
  methods: {
  }
}
</script>
