<template>
  <div class="placerd-container">
    <el-button
      v-permission="['purchasing.edit']"
      type="primary"
      @click="match()"
    >Match parts against Database</el-button>
    <p />
    <h2>Items:</h2>
    <el-table
      ref="itemTable"
      :key="tableKey"
      :data="poData.Lines"
      border
      :cell-style="{ padding: '0', height: '20px' }"
      style="width: 100%"
    >
      <el-table-column prop="LineNo" label="Line" width="70" />
      <el-table-column prop="QuantityOrderd" label="Quantity" width="80" />
      <el-table-column prop="SupplierSku" label="Supplier Part Number" width="220" />
      <el-table-column prop="LineType" label="Type" width="80" />
      
      <el-table-column label="Item">
        <template slot-scope="{ row }">
          <template v-if="row.LineType == 'Generic'">{{ row.Description }}</template>

          <template v-if="row.LineType == 'Part'">
            {{ row.PartNo }} - {{ row.ManufacturerName }} -
            <template>
            <template v-if="row.ManufacturerPartId !== null">
              <router-link
                :to="'/mfrParts/partView/' + row.ManufacturerPartId"
                class="link-type"
              >
                <span>{{ row.ManufacturerPartNumber }}</span>
              </router-link>
            </template>
             <template v-else>
              {{ row.ManufacturerPartNumber }}
            </template>
            </template>
          - {{ row.Description }}
          </template>
         
        </template>
      </el-table-column>
      <el-table-column label="Date" prop="ExpectedReceiptDate" width="100" />
      <el-table-column prop="LinePrice" label="Price" width="100" />
      <el-table-column prop="Total" label="Total" width="100" />
    </el-table>

    <h2>Additional Charges:</h2>

    <el-table
      ref="additionalChargesTable"
      row-key="AdditionalChargesLineNo"
      :data="poData.AdditionalCharges"
      border
      :cell-style="{ padding: '0', height: '20px' }"
      style="width: 100%"
      @row-click="(row, column, event) =>openAdditionalChargesLine(row)"
    >
      <el-table-column prop="LineNo" label="Line" width="70" />
      <el-table-column prop="Type" label="Type" width="100" />
      <el-table-column prop="Quantity" label="Quantity" width="80" />
      <el-table-column prop="Description" label="Description" />
      <el-table-column prop="VatValue" label="VAT" width="100" />
      <el-table-column prop="Price" label="Price" width="100" />
      <el-table-column prop="Total" label="Total" width="100" />
    </el-table>

    <el-divider />

    <orderTotal :total="poData.Total" />

    <el-dialog title="Match Parts" :visible.sync="matchDialogVisible" width="80%">
      <p>Generic items are excluded.</p>
      <el-table
        v-if="matchData !== null"
        :key="LineNo"
        :data="matchData.Lines"
        border
        :cell-style="{ padding: '0', height: '15px' }"
        style="width: 100%"
        :cell-class-name="tableAnalyzer"
      >
        <el-table-column prop="LineNo" label="Line" width="70" />
        <el-table-column prop="ManufacturerName" label="Manufacturer" width="200" />

        <el-table-column prop="ManufacturerPartNumber" label="Manufacturer Part Number" width="220">
          <template slot-scope="{ row }">
            <router-link
              v-if="row.ManufacturerPartId !== null"
              :to="'/mfrParts/partView/' + row.ManufacturerPartId"
              class="link-type"
            >
              <span>{{ row.ManufacturerPartNumber }}</span>
            </router-link>
            <span v-else>{{ row.ManufacturerPartNumber }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="SupplierName" label="Supplier" width="200" />
        <el-table-column prop="Sku" label="Supplier Part Number" />
      </el-table>
      <span>
        <el-button type="primary" @click="createMatch()">Create</el-button>
        <el-button type="primary" @click="match()">Reload</el-button>
        <el-button type="primary" @click="saveMatch()">Save</el-button>
      </span>
    </el-dialog>
  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'
import permission from '@/directive/permission/index.js'
import orderTotal from './orderTotal'

export default {
  directives: { permission },
  components: { orderTotal },
  props: { orderData: { type: Object, default: null }},
  data() {
    return {
      orderData: this.$props.orderData,
      SupplierOrderNumber: '',
      poData: {},
      matchData: null,
      matchDialogVisible: false
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
        this.poData = response.data
        this.lines = response.data.Lines
        this.total = response.data.Total
      })
    },
    match() {
      requestBN({
        url: '/purchasing/item/match',
        methood: 'get',
        params: {
          PurchaseOrderNo: this.$props.orderData.PoNo
        }
      }).then(response => {
        this.matchData = response.data
        this.matchDialogVisible = true
      })
    },
    saveMatch() {
      requestBN({
        method: 'post',
        url: '/purchasing/item/match',
        data: {
          PurchaseOrderNo: this.$props.orderData.PoNo,
          Command: 'Save'
        }
      }).then(response => {

      })
    },
    createMatch() {
      requestBN({
        method: 'post',
        url: '/purchasing/item/match',
        data: {
          PurchaseOrderNo: this.$props.orderData.PoNo,
          Command: 'Create'
        }
      }).then(response => {

      })
    },
    tableAnalyzer({ row, column, rowIndex, columnIndex }) {
      if (row.PartManufacturerId === null && columnIndex === 1) return 'error-cell'
      if (row.ManufacturerPartId === null && columnIndex === 2) return 'error-cell'
      if (row.SupplierPartId === null && columnIndex === 3) return 'error-cell'
    }
  }
}
</script>

<style>
.el-table .warning-cell {
  background: oldlace;
}
.el-table .error-cell {
  background: Lavenderblush;
}
</style>
