<template>
  <div class="app-container">
    <template>
      <el-table :data="boms" style="width: 100%">
        <el-table-column label="Code" prop="ItemCode" width="150" sortable>
          <template slot-scope="{ row }">
            <router-link :to="'/billOfMaterial/billOfMaterialView/' + row.ItemCode" class="link-type">
              <span>{{ row.ItemCode }}</span>
            </router-link>
          </template>
        </el-table-column>
        <el-table-column prop="Name" label="Name" width="250" sortable />
        <el-table-column prop="Description" label="Description" sortable />
      </el-table>
    </template>
  </div>
</template>

<script>
import BillOfMaterial from '@/api/billOfMaterial'
const billOfMaterial = new BillOfMaterial()

export default {
  name: 'BillOfMaterialBrowser',
  components: {},
  data() {
    return {
      boms: []
    }
  },
  async mounted() {
    this.boms = await billOfMaterial.search()
  },
  methods: {
  }
}
</script>
