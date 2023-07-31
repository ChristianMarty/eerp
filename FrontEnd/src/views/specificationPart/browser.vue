<template>
  <div class="app-container">
    <template>
      <el-table :data="specificationParts" style="width: 100%">
        <el-table-column label="Type" prop="Type" width="150" sortable />
        <el-table-column label="Title" sortable>
          <template slot-scope="{ row }">
            <router-link
              :to="'/specificationPart/item/' + row.Id"
              class="link-type"
            >
              <span> {{ row.Title }}</span>
            </router-link>
          </template>
        </el-table-column>
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
