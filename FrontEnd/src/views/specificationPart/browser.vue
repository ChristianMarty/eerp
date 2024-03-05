<template>
  <div class="app-container">
    <template>
      <el-table :data="specificationParts" style="width: 100%">
        <el-table-column label="Code" prop="ItemCode" width="150" sortable>
          <template slot-scope="{ row }">
            <router-link
              :to="'/specificationPart/item/' + row.ItemCode"
              class="link-type"
            >
              <span> {{ row.ItemCode }}</span>
            </router-link>
          </template>
        </el-table-column>
        <el-table-column label="Type" prop="Type" width="150" sortable />
        <el-table-column label="Name" prop="Name" sortable />
      </el-table>
    </template>
  </div>
</template>

<script>
import SpecificationPart from '@/api/specificationPart'
const specificationPart = new SpecificationPart()

export default {
  name: 'SpecificationPartBrowser',
  components: {},
  data() {
    return {
      specificationParts: []
    }
  },
  mounted() {
    specificationPart.search(null).then(response => {
      this.specificationParts = response
    })
  },
  methods: {
  }
}
</script>
