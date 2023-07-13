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
          <span class="link-type" @click="redirect(row)">{{ row.Item }}</span>
        </template>
      </el-table-column>
      <el-table-column prop="Category" label="Category" width="200" />
      <el-table-column prop="Description" label="Description" />
      <el-table-column prop="LocationPath" label="Current Location" />
    </el-table>  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'

export default {
  name: 'SearchResult',
  components: {},
  props: {
    isEdit: {
      type: Boolean,
      default: false
    }
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
  created() {
    // Why need to make a copy of this.$route here?
    // Because if you enter this page and quickly switch tag, may be in the execution of the setTagsViewTitle function, this.$route is no longer pointing to the current page
    // https://github.com/PanJiaChen/vue-element-admin/issues/1221
    //  this.tempRoute = Object.assign({}, this.$route)
  },
  methods: {
    /*
      setTagsViewTitle() {
        const route = Object.assign({}, this.tempRoute, {
          title: `${this.supplierData.Name}`
        })
        this.$store.dispatch('tagsView/updateVisitedView', route)
      },
      setPageTitle() {
        const title = 'Part View'
        document.title = `${title} - ${this.supplierData.Name}`
      }*/

    redirect(item) {
      this.$store.dispatch('tagsView/delView', this.$route) // close search view

      switch (item.Category) {
        case 'Location': this.$router.push('/location/summary/' + item.RedirectCode)
          break
        case 'Stock': this.$router.push('/stock/item/' + item.RedirectCode)
          break
        case 'Inventory': this.$router.push('/Inventory/inventoryView/' + item.RedirectCode)
          break
        case 'PurchaseOrder': this.$router.push('/purchasing/edit/' + item.RedirectCode)
          break
        case 'WorkOrder': this.$router.push('/workOrder/workOrderView/' + item.RedirectCode)
          break
        case 'Vendor': this.$router.push('/vendor/view/' + item.RedirectCode)
          break
        case 'ProductionPart': this.$router.push('/productionPart/item/' + item.RedirectCode)
          break
        case 'AssemblyUnit': this.$router.push('/assembly/unit/item/' + item.RedirectCode)
          break
        case 'ManufacturerPartItem': this.$router.push('/manufacturerPart/item/' + item.RedirectCode)
          break
        case 'ManufacturerPartNumber': this.$router.push('/manufacturerPart/partNumber/item/' + item.RedirectCode)
          break
      }
    },
    search(term) {
      this.loading = true
      this.searchTerm = term
      requestBN({
        url: '/search',
        methood: 'get',
        params: { search: term }
      }).then(response => {
        this.result = response.data
        this.loading = false

        if (this.result.length === 1) {
          this.redirect(this.result[0])
        }
      })
    }
  }
}
</script>

