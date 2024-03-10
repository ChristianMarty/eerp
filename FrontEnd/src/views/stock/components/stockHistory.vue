<template>
  <div class="stock-history">

    <template v-if="history != null">
      <el-timeline :reverse="true">
        <el-timeline-item v-for="(line, index) in history" :key="index" :color="line.color" :timestamp="line.Date">
          {{ line.Description }}
          <template v-if="line.WorkOrderBarcode != NULL">
            <span>, Work Order: </span>
            <router-link :to="'/workOrder/workOrderView/' + line.WorkOrderBarcode" class="link-type">
              <span>{{ line.WorkOrderBarcode }}</span>
            </router-link>
            {{ line.WorkOrderTitle }}
          </template>
          <template v-if="line.EditToken != NULL">
            <el-button style="margin-left: 20px" type="primary" icon="el-icon-edit" circle @click="openEditDialog(line)" />
          </template>
          <p>{{ line.Note }}</p>
        </el-timeline-item>
      </el-timeline>
    </template>

    <editStockHistoryDialog :visible.sync="editStockHistoryDialogVisible" :data="editData" @change="getHistory()" />
  </div>
</template>
<script>

import requestBN from '@/utils/requestBN'
import editStockHistoryDialog from './editStockHistoryDialog'

export default {
  components: { editStockHistoryDialog },
  props: {
    StockCode: { type: String, default: '' }
  },
  data() {
    return {
      history: null,
      editData: {},
      editStockHistoryDialogVisible: false
    }
  },
  mounted() {
    this.getHistory()
  },
  methods: {
    openEditDialog(data) {
      this.editData = data
      this.editStockHistoryDialogVisible = true
    },
    getHistory() {
      requestBN({
        url: '/stock/history',
        methood: 'get',
        params: { StockCode: this.$props.StockCode }
      }).then(response => {
        if (response.error != null) {
          this.$message({
            showClose: true,
            message: response.error,
            duration: 0,
            type: 'error'
          })
        } else {
          this.history = response.data
          this.history.forEach(element => {
            switch (element.Type) {
              case 'remove':
                element.color = '#67C23A'
                break
              case 'add':
                element.color = '#E6A23C'
                break
              case 'count':
              case 'create':
                element.color = '#409EFF'
                break
            }
          })
        }
      })
    }

  }
}
</script>
