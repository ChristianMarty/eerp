<template>
  <div class="app-container">
    <h1>
      {{ supplierData.Name }}
    </h1>

    <el-table
      :data="supplierPartData"
      style="width: 100%; margin-top:10px"
    >

      <el-table-column

        label="Part Number"
        sortable
      >
        <template slot-scope="{ row }">
          <a :href="row.SupplierPartLink" target="blank">
            {{ row.SupplierPartNumber }}
          </a>
        </template>
      </el-table-column>
    </el-table>
  </div>
  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'

export default {
  name: 'PartDetail',
  components: { },
  props: {
    isEdit: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      supplierData: null,
      supplierPartData: null
    }
  },
  mounted() {
    this.getSupplier()
    this.getSupplierPart()
  },
  created() {
    // Why need to make a copy of this.$route here?
    // Because if you enter this page and quickly switch tag, may be in the execution of the setTagsViewTitle function, this.$route is no longer pointing to the current page
    // https://github.com/PanJiaChen/vue-element-admin/issues/1221
    this.tempRoute = Object.assign({}, this.$route)
  },
  methods: {
    getSupplier() {
      requestBN({
        url: '/supplier/item',
        methood: 'get',
        params: { SupplierId: this.$route.params.supplierNo }
      }).then(response => {
        this.supplierData = response.data[0]

        this.setTagsViewTitle()
        this.setPageTitle()
      })
    },
    getSupplierPart() {
      requestBN({
        url: '/supplier/supplierPart',
        methood: 'get',
        params: { SupplierId: this.$route.params.supplierNo }
      }).then(response => {
        this.supplierPartData = response.data
      })
    },
    setTagsViewTitle() {
      const route = Object.assign({}, this.tempRoute, {
        title: `${this.supplierData.Name}`
      })
      this.$store.dispatch('tagsView/updateVisitedView', route)
    },
    setPageTitle() {
      const title = 'Part View'
      document.title = `${title} - ${this.supplierData.Name}`
    }
  }
}
</script>

