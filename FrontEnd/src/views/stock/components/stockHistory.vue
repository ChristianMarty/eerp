<template>
  <div class="stock-history">

    <template v-if="history != null">
      <el-timeline :reverse="true">
        <el-timeline-item
          v-for="(line, index) in history"
          :key="index"
          :color="line.color"
          :timestamp="line.ItemCode+', '+line.Date+', by '+line.NameInitials"
        >
          {{ line.Description }}
          <template v-if="line.WorkOrderCode != NULL">
            <span>, Work Order: </span>
            <router-link :to="'/workOrder/workOrderView/' + line.WorkOrderCode" class="link-type">
              <span>{{ line.WorkOrderCode }}</span>
            </router-link>
            {{ line.WorkOrderTitle }}
          </template>
          <template v-if="line.EditToken != NULL">
            <el-button style="margin-left: 20px" type="info" icon="el-icon-edit" circle @click="openEditDialog(line)" />
          </template>
          <el-button style="margin-left: 20px" type="primary" icon="el-icon-printer" circle @click="openPrintDialog(line)" />
          <p>{{ line.Note }}</p>
        </el-timeline-item>
      </el-timeline>
    </template>

    <editStockHistoryDialog :visible.sync="editStockHistoryDialogVisible" :stock-history-code="editLineCode" @change="getHistory()" />
    <stockHistoryPrintDialog :visible.sync="stockHistoryPrintDialogVisible" :stock-history-code="printLineCode" />
  </div>
</template>
<script>

import editStockHistoryDialog from './editStockHistoryDialog'
import stockHistoryPrintDialog from './stockHistoryPrintDialog'

import Stock from '@/api/stock'
const stock = new Stock()

export default {
  components: { editStockHistoryDialog, stockHistoryPrintDialog },
  props: {
    StockCode: { type: String, default: '' }
  },
  data() {
    return {
      history: null,
      editData: {},
      editStockHistoryDialogVisible: false,
      stockHistoryPrintDialogVisible: false,
      printLineCode: '',
      editLineCode: ''
    }
  },
  mounted() {
    this.getHistory()
  },
  methods: {
    openEditDialog(data) {
      this.editLineCode = data.ItemCode
      this.editStockHistoryDialogVisible = true
    },
    openPrintDialog(data) {
      this.printLineCode = data.ItemCode
      this.stockHistoryPrintDialogVisible = true
    },
    getHistory() {
      stock.item.history.list(this.$props.StockCode).then(response => {
        this.history = response
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
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    }
  }
}
</script>
