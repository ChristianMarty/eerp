<template>
  <div class="placerd-container">
    <p><b>TO DO:</b> Add purchase order document rendering</p>
    <el-table
      ref="itemTable"
      :key="tableKey"
      :data="lines"
      border
      style="width: 100%"
      :summary-method="calcSum"
      :cell-style="{ padding: '0', height: '15px' }"
      show-summary
    >
      <el-table-column prop="LineNo" label="Line" width="70" />
      <el-table-column prop="QuantityOrderd" label="Quantity" width="120" />
      <el-table-column prop="SupplierSku" label="SKU" width="220" />

      <el-table-column label="Item">
        <template slot-scope="{ row }">
          <template v-if="row.Type == 'Generic'">
            {{ row.Description }}
          </template>

          <template v-if="row.Type == 'Part'">
            {{ row.PartNo }} - {{ row.ManufacturerName }} -
            {{ row.ManufacturerPartNumber }} - {{ row.Description }}
          </template>
        </template>
      </el-table-column>

      <el-table-column prop="Price" label="Price" width="120" />

      <el-table-column label="Total" width="120">
        <template slot-scope="{ row }">
          <span>{{
            Math.round(row.QuantityOrderd * row.Price * 100000) / 100000
          }}</span>
        </template>
      </el-table-column>
    </el-table>
  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'

export default {
  props: { orderData: { type: Object, default: null }},
  data() {
    return {
      orderData: this.$props.orderData,
      SupplierOrderNumber: '',
      lines: null
    }
  },
  mounted() {
    this.getOrderLines()
  },
  methods: {
    getOrderLines() {
      requestBN({
        url: '/purchasing/item',
        methood: 'get',
        params: {
          PurchaseOrderNo: this.$props.orderData.PoNo
        }
      }).then(response => {
        this.lines = response.data.Lines
      })
    },
    calcSum(param) {
      let total = 0
      this.data.Lines.forEach(element => {
        const line = element.QuantityOrderd * element.Price
        total += Math.round(line * 100000) / 100000
      })

      const totalLine = []
      totalLine[0] = 'Total'
      totalLine[5] = total
      return totalLine
    }
  }
}
</script>
