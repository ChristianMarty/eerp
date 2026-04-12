<template>
  <div class="app-container">
    <h1>{{ partData.ItemCode }} - {{ partData.Type }} - {{ partData.Name }}</h1>
    <p>{{ partData.Description }}</p>

    <template v-for="revision in partData.Revision">
      <h2>Revision {{ revision.RevisionCode }}</h2>
      <h3>Production Part</h3>
      <el-table
        :data="revision.ProductionPart"
        style="width: 100%;"
        :cell-style="{ padding: '0', height: '20px' }"
        border
      >
        <el-table-column label="Production Part" prop="ItemCode" width="250px">
          <template slot-scope="{ row }">
            <router-link
              :to="'/productionPart/item/' + row.ItemCode"
              class="link-type"
            >
              <span>{{ row.ItemCode }}</span>
            </router-link>
          </template>
        </el-table-column>
        <el-table-column prop="Description" label="Description" />
      </el-table>
    </template>
  </div>
</template>

<script>
import permission from '@/directive/permission/index.js'

import SpecificationPart from '@/api/specificationPart'
import ProductionPartCreate from '@/views/productionPart/create.vue'
const specificationPart = new SpecificationPart()

export default {
  name: 'SpecificationPartItem',
  components: { ProductionPartCreate },
  directives: { permission },
  data() {
    return {
      partData: {}
    }
  },
  mounted() {
    this.getSpecificationPart()
  },
  created() {
    // Why need to make a copy of this.$route here?
    // Because if you enter this page and quickly switch tag, may be in the execution of the setTagsViewTitle function, this.$route is no longer pointing to the current page
    // https://github.com/PanJiaChen/vue-element-admin/issues/1221
    this.tempRoute = Object.assign({}, this.$route)
  },
  methods: {

    setTitle() {
      const title = `${this.partData.Type} - ${this.partData.Name}`
      const route = Object.assign({}, this.tempRoute, {
        title: `${title}`
      })
      this.$store.dispatch('tagsView/updateVisitedView', route)
      document.title = title
    },
    getSpecificationPart() {
      specificationPart.item(this.$route.params.SpecificationPartBarcode).then(response => {
        this.partData = response
        this.setTitle()
      })
    }
  }
}
</script>

<style>
.el-card {
  margin-top: 20px;
}
</style>
