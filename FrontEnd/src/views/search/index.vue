<template>
  <div class="app-container">
    <h1> Search Result for : {{ searchTerm }}</h1>

    <el-table :data="itemList" border style="width: 100%">
      <el-table-column prop="Item" label="Item Nr." width="120" />
      <el-table-column prop="Category" label="Category" width="120" />
      <el-table-column prop="Description" label="Description" />
      <el-table-column prop="Location" label="Current Location" />
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
      searchTerm: '',
      itemList: {}
    }
  },
  mounted() {
    if (this.$route.params.Search != null) {
      this.searchTerm = this.$route.params.Search

      this.search(this.searchTerm)
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

    search(term) {
      requestBN({
        url: '/search',
        methood: 'get',
        params: { search: term }
      }).then(response => {
        this.result = response.data

        this.$store.dispatch('tagsView/delView', this.$route) // close search view

        switch (this.result.Category) {
          case 'Location': this.$router.push('/location/summary/' + this.result.Code)
            break
          case 'Stock': this.$router.push('/stock/item/' + this.result.Code)
            break
          case 'Inventory': this.$router.push('/Inventory/inventoryView/' + this.result.Code)
            break
          case 'PurchaseOrder': this.$router.push('/purchasing/edit/' + this.result.Code)
            break
          case 'WorkOrder': this.$router.push('/workOrder/workOrderView/' + this.result.Code)
            break
          case 'ProductPart': this.$router.push('/prodParts/prodPartView/' + this.result.Code)
            break
          case 'AssemblyItem': this.$router.push('/assembly/item/' + this.result.Code)
            break
        }
      })
    }
  }
}
</script>

