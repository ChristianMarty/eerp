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
      <el-table-column prop="LineNo" label="Line" width="80" sortable />
      <el-table-column prop="QuantityOrderd" label="Orderd Qty" width="140" sortable />
      <el-table-column
        prop="QuantityReceived"
        label="Received Qty"
        width="140"
        sortable
      />
      <el-table-column prop="ReceivalDate" label="Receival Date" width="150" sortable />
      <el-table-column prop="ExpectedReceiptDate" label="Expected" width="150" sortable />
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
      <el-table-column label="Price" width="100">
          <template slot-scope="{ row }">
          <span>
            {{
              calcLinePrice(row)
            }}
          </span>
        </template>
      </el-table-column>

      <el-table-column label="Total" width="100">
        <template slot-scope="{ row }">
          <span>
            {{
              calcLineSum(row)
            }}
          </span>
        </template>
      </el-table-column>

      <el-table-column width="100">
        <template slot-scope="{ row }">
          <el-button
            v-if="row.ReceivalId"
            type="text"
            size="mini"
            @click="showDialog=true, trackDialogReceivalId= row.ReceivalId"
          >Track</el-button>
        </template>
      </el-table-column>
    </el-table>


    <orderTotal :lines="lines" :vat="vat" />

    <trackDialog :visible.sync="showDialog" :receival-id="trackDialogReceivalId" />

  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'
import trackDialog from './trackDialog'
import orderTotal from './orderTotal'

export default {
  components: { trackDialog, orderTotal },
  props: { orderData: { type: Object, default: null }},
  data() {
    return {
      lines: null,
      showDialog: false,
      trackDialogReceivalId: 0,
      vat:[]
    }
  },
  created() {
    this.getOrderLines()
    this.getVAT()
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
    getVAT() {
      requestBN({
        url: '/finance/tax',
        methood: 'get',
        params: {
          Type: 'VAT'
        }
      }).then(response => {
        this.vat = response.data
      })
    },
    calcLineSum(line){
       return  (Math.round( (line.QuantityOrderd * line.Price) * ((100-line.Discount)/100) * 10000) / 10000) 
    },
    calcLinePrice(line){
       return  (Math.round( line.Price * ((100-line.Discount)/100) * 10000) / 10000)  
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
