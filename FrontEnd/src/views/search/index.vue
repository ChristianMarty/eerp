<template>
  <div class="app-container">
    <h1> Search Result for : {{ searchTerm }}</h1>
    <el-input ref="searchInput" v-model="searchInput" placeholder="Search" @keyup.enter.native="search(searchInput)">
      <el-button slot="append" icon="el-icon-search" @click="search(searchInput)" />
    </el-input>
    <p>Use SQL LIKE syntax for MPN search.</p>

    <el-table
      v-loading="loading"
      element-loading-text="Searching..."
      :data="result"
      border
      style="width: 100%"
    >
      <el-table-column prop="Item" label="Item Nr." width="200">
        <template slot-scope="{ row }">
          <router-link :to="row.link" class="link-type">
            <span @click="close()">{{ row.Item }}</span>
          </router-link>
        </template>
      </el-table-column>
      <el-table-column prop="Category" label="Category" width="200" />
      <el-table-column prop="Description" label="Description" />
      <el-table-column prop="LocationPath" label="Current Location" />
    </el-table>
  </div>
</template>

<script>

import Search from '@/api/search'
const search = new Search()

export default {
  name: 'SearchResult',
  components: {},
  props: {
    isEdit: { type: Boolean, default: false }
  },
  data() {
    return {
      loading: true,
      searchInput: '',
      searchTerm: '',
      result: []
    }
  },
  mounted() {
    if (this.$route.params.Search != null) {
      this.search(this.$route.params.Search)
    }
  },
  methods: {
    setTitle(title) {
      title = 'Search: ' + title
      const route = Object.assign({}, this.$route, {
        title: title
      })
      this.$store.dispatch('tagsView/updateVisitedView', route)
      document.title = title
    },
    close() {
      this.$store.dispatch('tagsView/delView', this.$route) // close search view
    },
    search(term) {
      this.loading = true
      this.searchTerm = term
      this.searchInput = term
      this.setTitle(term)

      search.search(term).then(response => {
        this.loading = false
        this.result = response
        this.result.forEach((item) => {
          switch (item.Category) {
            case 'Document': item.link = '/document/item/'
              break
            case 'Location': item.link = '/location/item/'
              break
            case 'Stock': item.link = '/stock/item/'
              break
            case 'Inventory': item.link = '/inventory/item/'
              break
            case 'PurchaseOrder': item.link = '/purchasing/edit/'
              break
            case 'WorkOrder': item.link = '/workOrder/workOrderView/'
              break
            case 'Vendor': item.link = '/vendor/view/'
              break
            case 'ProductionPart': item.link = '/productionPart/item/'
              break
            case 'AssemblyUnit': item.link = '/assembly/unit/item/'
              break
            case 'ManufacturerPartItem': item.link = '/manufacturerPart/item/'
              break
            case 'ManufacturerPartNumber': item.link = '/manufacturerPart/partNumber/item/'
              break
            case 'SupplierPartNumber': item.link = '/manufacturerPart/partNumber/item/'
              break
          }
          item.link += item.RedirectCode
        })
        if (this.result.length === 1) {
          this.$router.push(this.result[0].link)
          this.close()
        }
      })
    }
  }
}
</script>

