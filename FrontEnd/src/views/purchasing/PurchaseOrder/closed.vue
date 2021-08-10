<template>
  <div class="placerd-container">
    <el-table
      ref="itemTable"
      :data="lines"
      border
      style="width: 100%"
      row-key="lineKey"
      :cell-style="{ padding: '0', height: '30px' }"
      :tree-props="{ children: 'Received' }"
    >
      <el-table-column prop="LineNo" label="Line" width="70" />
      <el-table-column prop="QuantityOrderd" label="Orderd Qty" width="120" />
      <el-table-column
        prop="QuantityReceived"
        label="Received Qty"
        width="120"
      />
      <el-table-column prop="ReceivalDate" label="Receival Date" width="120" />

      <el-table-column prop="SupplierSku" label="Supplier SKU" width="220" />

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
    </el-table>
  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'

export default {
  props: { orderData: { type: Object, default: null }},
  data() {
    return {
      lines: null
    }
  },
  created() {
    this.getOrderLines()
  },
  mounted() {},
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
        this.prepairLines(this.lines)
      })
    },
    prepairLines(data) {
      data.forEach(line => {
        line.lineKey = line.LineNo

        if ('Received' in line) {
          if (line.Received.length == 1) {
            line.ReceivalDate = line.Received[0].ReceivalDate
            delete line.Received
          } else {
            let i = 0
            line.Received.forEach(subLine => {
              i++
              subLine.lineKey = line.lineKey + '.' + i
            })
          }
        }
      })
    }
  }
}
</script>
