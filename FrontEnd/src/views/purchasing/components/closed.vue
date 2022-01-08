<template>
  <div class="placerd-container">
    <el-table
      ref="itemTable"
      :data="lines"
      border
      show-summary
      :summary-method="calcSum"
      style="width: 100%"
      row-key="lineKey"
      :cell-style="{ padding: '0', height: '30px' }"
      :tree-props="{ children: 'Received' }"
    >
      <el-table-column prop="LineNo" label="Line" width="80" sortable />
      <el-table-column prop="QuantityOrderd" label="Orderd Qty" width="140" sortable />
      <el-table-column
        prop="QuantityReceived"
        label="Received Qty"
        width="140"
        sortable
      />
      <el-table-column prop="ReceivalDate" label="Receival Date" width="150" sortable />
      <el-table-column prop="SupplierSku" label="Supplier SKU" width="220" sortable />

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
      <el-table-column prop="Price" label="Price" width="120" sortable />
      <el-table-column prop="Total" label="Total" width="120" sortable />
      <el-table-column width="100">
        <template slot-scope="{ row }">
          <el-button
            v-if="row.ReceivalId"
            type="text"
            size="mini"
            @click="openTrackDialog(row.ReceivalId)"
          >Track</el-button>
        </template>
      </el-table-column>
    </el-table>

    <el-dialog
      title="Track Item"
      :visible.sync="showDialog"
      width="50%"
      center
    >
      <el-table
        ref="itemTable"
        :data="trackData"
        border
        style="width: 100%"
        :cell-style="{ padding: '0', height: '30px' }"
      >
        <el-table-column prop="Type" label="Type" width="120" sortable />

        <el-table-column label="Reference" sortable>
          <template slot-scope="{ row }">
            <template v-if="row.Type == 'Part Stock'">
              <router-link :to="'/stock/item/' + row.StockNo" class="link-type">
                <span>STK-{{ row.StockNo }}</span>
              </router-link>
            </template>
            <template v-if="row.Type == 'Inventory'">
              <router-link
                :to="'/inventory/inventoryView/' + row.InvNo"
                class="link-type"
              >
                <span>Inv-{{ row.InvNo }}</span>
              </router-link>
            </template>
          </template>
        </el-table-column>
      </el-table>
    </el-dialog>

  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'

export default {
  props: { orderData: { type: Object, default: null }},
  data() {
    return {
      lines: null,
      showDialog: false,
      trackData: null
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
    getTrackData(ReceivalId) {
      requestBN({
        url: '/purchasing/item/track',
        methood: 'get',
        params: {
          ReceivalId: ReceivalId
        }
      }).then(response => {
        this.trackData = response.data
      })
    },
    openTrackDialog(ReceivalId) {
      this.showDialog = true
      this.getTrackData(ReceivalId)
    },
    calcSum(param) {
      let total = 0
      this.lines.forEach(element => {
        total += element.Total
      })

      const totalLine = []
      totalLine[0] = 'Total'
      totalLine[7] = Math.round(total * 100000) / 100000
      return totalLine
    },
    prepairLines(data) {
      data.forEach(line => {
        line.lineKey = line.LineNo
        line.Total = Math.round(line.QuantityOrderd * line.Price * 100000) / 100000

        if ('Received' in line) {
          if (line.Received.length == 1) {
            line.ReceivalDate = line.Received[0].ReceivalDate
            line.ReceivalId = line.Received[0].ReceivalId
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
