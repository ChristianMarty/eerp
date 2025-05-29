<template>
  <div class="app-container">
    <template>
      <el-checkbox v-model="hideClosed" @change="getPurchaseOrders()">Hide closed orders</el-checkbox>
      <el-table
        v-loading="loading"
        element-loading-text="Loading Purchase Orders "
        :data="PurchaseOrders"
        style="width: 100%"
      >
        <el-table-column prop="ItemCode" label="PO Number" width="150" sortable>
          <template slot-scope="{ row }">
            <router-link :to="'/purchasing/edit/' + row.ItemCode" class="link-type">
              <span>{{ row.ItemCode }}</span>
            </router-link>
          </template>
        </el-table-column>

        <el-table-column prop="Title" label="Title" sortable />

        <el-table-column prop="SupplierName" label="Supplier Name" sortable width="180">
          <template slot-scope="{ row }">
            <router-link :to="'/vendor/view/' + row.SupplierId" class="link-type">
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
        <el-table-column prop="Status" label="Status" width="140" sortable>
          <template slot-scope="{ row }">
            <span v-if="row.Status === 'Confirm'">{{ row.Status }} ({{ row.ReceiveProgress }}%)</span>
            <span v-else>{{ row.Status }}</span>
          </template>
        </el-table-column>
        <el-table-column
          prop="ExpectedCompletionDate"
          label="Exp. Completion"
          width="170"
          sortable
        />
      </el-table>
    </template>
  </div>
</template>

<script>

import Purchase from '@/api/purchase'
const purchase = new Purchase()

export default {
  name: 'DocumentBrowser',
  components: {},
  data() {
    return {
      hideClosed: true,
      loading: true,
      PurchaseOrders: []
    }
  },
  mounted() {
    this.getPurchaseOrders()
  },
  methods: {
    getPurchaseOrders() {
      this.loading = true
      purchase.list(this.hideClosed).then(response => {
        this.PurchaseOrders = response
        this.loading = false
      })
    }
  }
}
</script>
