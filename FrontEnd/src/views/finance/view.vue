<template>
  <div class="app-container">
    <h1>Finance</h1>

    <h2>Purchase Order</h2>

    <el-date-picker
      v-model="year"
      type="year"
      placeholder="Pick a year"
      @change="getPoData(year)"
    />
    <p />
    <el-table ref="poTable" :data="financeData" style="width: 100%" border :cell-style="{ padding: '0', height: '20px' }">
      <el-table-column prop="type" />
      <el-table-column prop="1" label="January" />
      <el-table-column prop="2" label="February" />
      <el-table-column prop="3" label="March" />
      <el-table-column prop="4" label="April" />
      <el-table-column prop="5" label="May" />
      <el-table-column prop="6" label="June" />
      <el-table-column prop="7" label="July" />
      <el-table-column prop="8" label="August" />
      <el-table-column prop="9" label="September" />
      <el-table-column prop="10" label="October" />
      <el-table-column prop="11" label="November" />
      <el-table-column prop="12" label="December" />
    </el-table>

    <p><b>Total Merchandise:</b> {{ data.TotalMerchandise }}</p>
    <p><b>Total Shipping:</b> {{ data.TotalShipping }}</p>
    <p><b>Total VAT:</b> {{ data.TotalVAT }}</p>
    <p><b>Total Total:</b> {{ data.TotalTotal }}</p>
  </div>
</template>

<script>

import Finance from '@/api/finance'
const finance = new Finance()

export default {
  name: 'Finance',
  components: {},
  data() {
    return {
      data: {},
      financeData: null,
      year: String(new Date().getFullYear())
    }
  },
  mounted() {
    this.setTitle()
    this.getPoData(this.year)
  },
  created() {
    // Why need to make a copy of this.$route here?
    // Because if you enter this page and quickly switch tag, may be in the execution of the setTagsViewTitle function, this.$route is no longer pointing to the current page
    // https://github.com/PanJiaChen/vue-element-admin/issues/1221
    this.tempRoute = Object.assign({}, this.$route)
  },
  methods: {
    setTitle() {
      const route = Object.assign({}, this.tempRoute, {
        title: `Finance`
      })
      this.$store.dispatch('tagsView/updateVisitedView', route)
      document.title = `Finance`
    },
    getPoData(year) {
      year = String(new Date(year).getFullYear())
      this.financeData = null
      finance.purchaseOrder.summary(year).then(response => {
        this.data = response
        const temp = [{}, {}, {}, {}]
        Object.values(response.MonthTotal).forEach(element => {
          temp[0][String(element.Month)] = String(element.Merchandise)
          temp[1][String(element.Month)] = String(element.Shipping)
          temp[2][String(element.Month)] = String(element.VAT)
          temp[3][String(element.Month)] = String(element.Total)
        })
        temp[0]['type'] = 'Merchandise'
        temp[1]['type'] = 'Shipping'
        temp[2]['type'] = 'VAT'
        temp[3]['type'] = 'Total'

        this.financeData = temp
      })
    }
  }
}
</script>
