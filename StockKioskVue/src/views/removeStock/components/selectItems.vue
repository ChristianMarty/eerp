<template>
  <el-table
      :data="data"
      border
      style="width: 100%"
  >
    <el-table-column prop="ItemCode" label="Item Code" width="100"/>
    <el-table-column label="Description">
      <template #default="scope">
        <span> {{ scope.row.ManufacturerName }} {{ scope.row.ManufacturerPartNumber }} </span>
      </template>
    </el-table-column>
    <el-table-column prop="Quantity" label="Removed Quantity" width="150"/>
  </el-table>
</template>

<script lang="ts">
import {BulkRemoveResult} from '../../../api/stock'
export default {
  name: 'SelectItems',
  props: { data: Array as Array<BulkRemoveResult>},
  components: {},
  data() {
    return {
    }
  },
  mounted() {
  },
  methods: {
  }
}
</script>
