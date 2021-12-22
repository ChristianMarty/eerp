<template>
  <div class="dashboard-container">
    <el-card class="box-card">
      <h3 style="text-align:center">Week Number</h3>
      <p style="text-align:center">
        {{ weeknumber }}
      </p>
    </el-card>

    <el-card class="box-card">
      <h3 style="text-align:center">Stock Notifications</h3>
      <p style="text-align:center">{{ StockNotification.Minimum }} / {{ StockNotification.Warning }} </p>

    </el-card>

  </div>
</template>

<script>
import getNumberOfWeek from '@/utils/weekNumber'
import requestBN from '@/utils/requestBN'

export default {
  name: 'Dashboard',
  data() {
    return {
      weeknumber: 0,
      StockNotification: 0
    }
  },
  computed: {},
  created() {},
  mounted() {
    this.weeknumber = getNumberOfWeek()
    this.getStockNotification()
  },
  methods: {
    getStockNotification() {
      requestBN({
        url: '/productionPart/notification/summary',
        methood: 'get'
      }).then(response => {
        this.StockNotification = response.data
      })
    }
  }
}
</script>

<style>
.box-card {
  float: left;
  width: 220px;
  margin: 20px;
}
</style>
