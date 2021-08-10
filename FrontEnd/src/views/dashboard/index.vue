<template>
  <div class="dashboard-container">
    <el-card class="box-card">
      <h3 style="text-align:center">Week Number</h3>
      <p style="text-align:center">
        {{ weeknumber }}
      </p>
    </el-card>
  </div>
</template>

<script>
import getNumberOfWeek from '@/utils/weekNumber'

export default {
  name: 'Dashboard',
  data() {
    return {
      weeknumber: 0
    }
  },
  computed: {},
  created() {},
  mounted() {
    this.weeknumber = getNumberOfWeek()
  },
  methods: {}
}
</script>

<style>
.box-card {
  width: 200px;
  margin: 20px;
}
</style>
