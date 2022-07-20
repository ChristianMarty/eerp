<template>
  <div class="app-container">
    <template>
      <el-checkbox v-model="hideClosed" @change="getPurchasOrders()">Hide closed orders</el-checkbox>
      <el-table :data="purchasOrders" style="width: 100%">
        <el-table-column prop="PoNo" label="PO Number" width="150" sortable>
          <template slot-scope="{ row }">
            <router-link :to="'/purchasing/edit/' + row.PoNo" class="link-type">
              <span>PO-{{ row.PoNo }}</span>
            </router-link>
          </template>
        </el-table-column>

        <el-table-column prop="Title" label="Title" sortable />

        <el-table-column prop="SupplierName" label="Supplier Name" sortable width="180">
          <template slot-scope="{ row }">
            <router-link :to="'/supplier/supplierView/' + row.SupplierId" class="link-type">
              <span>{{ row.SupplierName }}</span>
            </router-link>
          </template>
        </el-table-column>

        <el-table-column
          prop="OrderNumber"
          label="Order Number"
          sortable
          width="180"
        />
        <el-table-column
          prop="AcknowledgementNumber"
          label="Acknowledgement Number"
          sortable
          width="240"
        />

        <el-table-column
          prop="PurchaseDate"
          label="Purchase Date"
          width="170"
          sortable
        />
        <el-table-column prop="Status" label="Status" width="100" sortable />
      </el-table>
    </template>
  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'

export default {
  name: 'DocumentBrowser',
  components: {},
  data() {
    return {
      hideClosed: true,
      purchasOrders: []
    }
  },
  mounted() {
    this.getPurchasOrders()
  },
  methods: {
    getPurchasOrders() {
      requestBN({
        url: '/purchasOrder',
        methood: 'get',
        params: { HideClosed: this.hideClosed }
      }).then(response => {
        this.purchasOrders = response.data
      })
    }
  }
}
</script>
