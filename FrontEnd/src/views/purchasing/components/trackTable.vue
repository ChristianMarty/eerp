<template>
  <el-table
    ref="itemTable"
    :data="data"
    border
    style="width: 100%"
    :cell-style="{ padding: '0', height: '30px' }"
  >
    <el-table-column prop="Type" label="Type" width="120" sortable />

    <el-table-column label="Reference" sortable>
      <template slot-scope="{ row }">
        <template v-if="row.Type == 'Part Stock'">
          <router-link :to="'/stock/item/' + row.ItemCode" class="link-type">
            <span>{{ row.ItemCode }} </span>
          </router-link>
          <span style="float: right;"> Original Quantity: {{ row.CreateQuantity }} </span>
        </template>
        <template v-if="row.Type == 'Inventory'">
          <router-link
            :to="'/inventory/item/' + row.ItemCode"
            class="link-type"
          >
            <span>{{ row.ItemCode }} </span>
          </router-link>
          <span style="float: right;"> {{ row.Description }} </span>
        </template>
      </template>
    </el-table-column>
  </el-table>
</template>

<script>

import Purchase from '@/api/purchase'
const purchase = new Purchase()

export default {
  name: 'TrackTable',
  props: { data: { type: Array, default: () => [purchase.trackDataItem] }},
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
