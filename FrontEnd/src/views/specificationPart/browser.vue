<template>
  <div class="app-container">
    <template>
      <el-table :data="specificationParts" style="width: 100%">
        <el-table-column label="Barcode" prop="SpecificationPartBarcode" width="150" sortable>
          <template slot-scope="{ row }">
            <router-link
              :to="'/specificationPart/item/' + row.SpecificationPartNumber"
              class="link-type"
            >
              <span> {{ row.SpecificationPartBarcode }}</span>
            </router-link>
          </template>
        </el-table-column>
        <el-table-column label="Type" prop="Type" width="150" sortable />
        <el-table-column label="Title" prop="Title" sortable />
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
