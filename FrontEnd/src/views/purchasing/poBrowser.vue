<template>
  <div class="app-container">
    <template>
      <el-table :data="purchasOrders" style="width: 100%">
        <el-table-column prop="PoNo" label="PO Number" width="150" sortable>
          <template slot-scope="{ row }">
            <router-link :to="'/purchasing/edit/' + row.PoNo" class="link-type">
              <span>PO-{{ row.PoNo }}</span>
            </router-link>
          </template>
        </el-table-column>

        <el-table-column prop="Titel" label="Titel" sortable />

        <el-table-column
          prop="SupplierName"
          label="SupplierName"
          sortable
          width="220"
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
      purchasOrders: null
    }
  },
  mounted() {
    this.getPurchasOrders()
  },
  methods: {
    getPurchasOrders() {
      requestBN({
        url: '/purchasOrder',
        methood: 'get'
      }).then(response => {
        this.purchasOrders = response.data
      })
    }
  }
}
</script>
