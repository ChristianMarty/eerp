<template>
  <div class="app-container">
    <h1>
      {{ supplierData.Name }}
    </h1>
    <el-divider />
    <p><b>Customer Number:</b> {{ supplierData.CustomerNumber }}</p>
    <p><b>Is Supplier:</b> {{ supplierData.IsSupplier }}</p>
    <p><b>Is Manufacturer:</b> {{ supplierData.IsManufacturer }}</p>

    <el-divider />

    <h2>Purchas Orders</h2>
    <p><b>Number of orders:</b> {{ purchasOrders.length }}</p>
    <el-table
      :data="purchasOrders"
      style="width: 100%; margin-top:10px"
    >
      <el-table-column label="Po Number" sortable width="150">
        <template slot-scope="{ row }">
          <router-link :to="'/purchasing/edit/' + row.PoNo" class="link-type">
            <span>PO-{{ row.PoNo }}</span>
          </router-link>
        </template>
      </el-table-column>

      <el-table-column prop="Title" label="Title" sortable />
      <el-table-column prop="Description" label="Description" sortable />
      <el-table-column prop="PurchaseDate" label="Purchase Date" sortable width="250" />
      <el-table-column prop="OrderNumber" label="Order Number" sortable width="250" />
      <el-table-column prop="Status" label="Status" sortable width="150" />

    </el-table>
    <el-divider />

    <h2>Supplier Parts</h2>
    <p><b>Number of supplier parts:</b> {{ supplierPartData.length }}</p>
    <el-table
      :data="supplierPartData"
      style="width: 100%; margin-top:10px"
    >
      <el-table-column label="Part Number" sortable>
        <template slot-scope="{ row }">
          <a :href="row.SupplierPartLink" target="blank">
            {{ row.SupplierPartNumber }}
          </a>
        </template>
      </el-table-column>
    </el-table>
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
      supplierPartData: null,
      purchasOrders: []
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
        this.supplierData = response.data

        this.setTagsViewTitle()
        this.setPageTitle()
        this.getPurchasOrder()
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
    getPurchasOrder() {
      requestBN({
        url: '/purchasOrder',
        methood: 'get',
        params: { VendorId: this.$route.params.supplierNo }
      }).then(response => {
        this.purchasOrders = response.data
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

