<template>
  <div class="app-container">
    <template>
      <el-table :data="boms" style="width: 100%">
        <el-table-column prop="BillOfMaterialNumber" label="Bill Of Material Number" width="250" sortable>
          <template slot-scope="{ row }">
            <router-link :to="'/billOfMaterial/billOfMaterialView/' + row.BillOfMaterialBarcode" class="link-type">
              <span>{{ row.BillOfMaterialBarcode }}</span>
            </router-link>
          </template>
        </el-table-column>
        <el-table-column prop="Title" label="Title" sortable />
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
