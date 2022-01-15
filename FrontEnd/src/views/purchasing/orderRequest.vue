<template>
  <div class="app-container">
    <template>
      <el-table :data="orderRequests" style="width: 100%">
        <!-- <el-table-column prop="PoNo" label="PO Number" width="150" sortable>
          <template slot-scope="{ row }">
            <router-link :to="'/purchasing/edit/' + row.PoNo" class="link-type">
              <span>PO-{{ row.PoNo }}</span>
            </router-link>
          </template>
        </el-table-column>

        <el-table-column prop="Title" label="Title" sortable />-->

        <el-table-column
          prop="SupplierName"
          label="Supplier"
          sortable
          width="150"
        />

        <el-table-column
          prop="SupplierPartNumber"
          label="Part Number"
          sortable
          width="220"
        >
          <template slot-scope="{ row }">
            <a :href="row.SupplierPartLink" target="blank">
              {{ row.SupplierPartNumber }}
            </a>
          </template>
        </el-table-column>

        <el-table-column
          prop="Description"
          label="Description"
          sortable
        />
        <el-table-column
          prop="Quantity"
          label="Quantity"
          width="120"
          sortable
        />

        <el-table-column
          prop="CreationDate"
          label="Creation Date"
          width="170"
          sortable
        />

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
      orderRequests: null
    }
  },
  mounted() {
    this.getOrderRequests()
  },
  methods: {
    getOrderRequests() {
      requestBN({
        url: '/purchasing/orderRequest',
        methood: 'get'
      }).then(response => {
        this.orderRequests = response.data
      })
    }
  }
}
</script>
