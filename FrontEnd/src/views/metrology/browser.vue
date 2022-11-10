<template>
  <div class="app-container">
    <template>
      <el-table :data="testSystem" style="width: 100%">
        <el-table-column label="Test System No" width="150" sortable>
          <template slot-scope="{ row }">
            <router-link :to="'/metrology/item/' + row.TestSystemBarcode" class="link-type">
              <span> {{ row.TestSystemBarcode }}</span>
            </router-link>
          </template>
        </el-table-column>
        <el-table-column label="Name" prop="Name" sortable />
        <el-table-column label="Description" prop="Description" sortable />
      </el-table>
    </template>
  </div>
</template>

<script>
import Metrology from '@/api/metrology'
const metrology = new Metrology()

export default {
  name: 'TestSystemBrowser',
  components: {},
  data() {
    return {
      testSystem: []
    }
  },
  async mounted() {
    this.testSystem = await metrology.search()
  },
  methods: {
  }
}
</script>
