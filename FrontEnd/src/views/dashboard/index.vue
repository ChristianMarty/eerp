<template>
  <div class="dashboard-container">

    <el-card class="mid-box">
      <h3 style="text-align:center">Search</h3>
      <template style="text-align:center">
        <el-input ref="searchInput" v-model="searchInput" placeholder="Search" @keyup.enter.native="search()">
          <el-button slot="append" icon="el-icon-search" @click="search()" />
        </el-input>
        <p>Use SQL LIKE syntax for MPN search.</p>
      </template>
    </el-card>
    <el-card class="small-box">
      <h3 style="text-align:center">Week Number</h3>
      <p style="text-align:center">
        {{ weekNumber }}
      </p>
    </el-card>
    <el-card class="mid-box">
      <h3 style="text-align:center">Pending Orders</h3>
      <template>
        <el-table :data="pendingOrders" style="width: 100%">
          <el-table-column prop="ItemCode" label="PO Number" width="150" sortable>
            <template slot-scope="{ row }">
              <router-link :to="'/purchasing/edit/' + row.ItemCode" class="link-type">
                <span>{{ row.ItemCode }}</span>
              </router-link>
            </template>
          </el-table-column>
          <el-table-column prop="Title" label="Title" sortable />
          <el-table-column prop="SupplierName" label="Supplier Name" sortable width="160">
            <template slot-scope="{ row }">
              <router-link :to="'/vendor/view/' + row.SupplierId" class="link-type">
                <span>{{ row.SupplierName }}</span>
              </router-link>
            </template>
          </el-table-column>
          <el-table-column prop="OrderNumber" label="Order Number" sortable width="160" />
          <el-table-column prop="PurchaseDate" label="Purchase Date" width="160" sortable />
        </el-table>
      </template>
    </el-card>  </div>
</template>

<script>
import Various from '@/api/various'
const various = new Various()

import Purchase from '@/api/purchase'
const purchase = new Purchase()

export default {
  name: 'Dashboard',
  data() {
    return {
      weekNumber: 0,
      searchInput: '',
      pendingOrders: null
    }
  },
  computed: {},
  created() { },
  mounted() {
    this.getWeekNumber()
    this.getPurchaseOrders()
    this.$refs.searchInput.focus()
  },
  updated() {

  },
  methods: {
    getWeekNumber() {
      various.WeekNumber().then(response => {
        this.weekNumber = response.WeekNumber
      })
    },
    getPurchaseOrders() {
      purchase.listOpenOrders().then(response => {
        this.pendingOrders = response
      })
    },
    search() {
      this.$router.push('/search/' + encodeURI(this.searchInput))
      this.searchInput = ''
    }
  }
}
</script>

<style>
.small-box {
  float: left;
  width: 220px;
  margin: 10px;
}

.mid-box {
  float: left;
  width: 910px;
  margin: 10px;
}
</style>
