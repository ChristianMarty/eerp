<template>
  <div class="edit-container">
    <el-form v-permission="['purchasing.edit']" :inline="true" style="margin-top: 20px">
      <el-form-item>

        <el-button
          v-permission="['purchasing.edit']"
          type="primary"
          icon="el-icon-plus"
          circle
          style="margin-top: 20px"
          @click="addLine('Part')"
        />

        <!--<el-button type="primary" @click="save(lines)">Save All</el-button>

        <el-button @click="addLine('Part')">Add Part</el-button>
        <el-button @click="addLine('Generic')">Add Generic Item</el-button>
         <el-badge :value="1" type="primary"> -->
        <el-button @click="orderReqestDialogVisible = true">Order Reqests</el-button>
        <!-- </el-badge> -->
      </el-form-item>
    </el-form>

    <el-table
      ref="itemTable"
      :key="tableKey"
      :data="lines"
      border
      :cell-style="{ padding: '0', height: '20px' }"
      style="width: 100%"
      :summary-method="calcSum"
      show-summary
    >
      <el-table-column prop="LineNo" label="Line" width="70" />
      <el-table-column prop="QuantityOrderd" label="Quantity" width="80" />
      <el-table-column label="SKU" prop="SupplierSku" width="220" />
      <el-table-column label="Item">
        <template slot-scope="{ row }">
          <template v-if="row.Type == 'Generic'">{{ row.Description }}</template>
          <template v-if="row.Type == 'Part'">
            {{ row.PartNo }} - {{ row.ManufacturerName }} - {{
              row.ManufacturerPartNumber
            }} - {{ row.Description }}
          </template>
        </template>
      </el-table-column>
      <el-table-column prop="Price" label="Price" width="100" />

      <el-table-column label="Total" width="100">
        <template slot-scope="{ row }">
          <span>{{ (Math.round((row.QuantityOrderd * row.Price) * 10000) / 10000) }}</span>
        </template>
      </el-table-column>

      <template v-permission="['purchasing.edidt']">
        <el-table-column width="70">
          <template slot-scope=" { row }">
            <el-button
              type="text"
              size="mini"
              @click="orderLineEditData = JSON.parse(JSON.stringify(row)), orderLineEditDialogVisible = true"
            >
              Edit</el-button>
          </template>
        </el-table-column>
      </template>
    </el-table>

    <el-dialog title="Order Line" :visible.sync="orderLineEditDialogVisible">

      <el-form label-width="150px">
        <el-form-item label="Line:">{{ orderLineEditData.LineNo }}</el-form-item>

        <el-form-item label="Type:">
          <el-select
            v-model="orderLineEditData.Type"
            placeholder="Type"
            style="min-width: 200px; margin-right: 10px;"
          >
            <el-option value="Part">Part</el-option>
            <el-option value="Generic">Generic</el-option>
          </el-select>
        </el-form-item>

        <el-form-item label="Quantity:">
          <template slot-scope="{ row }">
            <el-input-number
              v-model="orderLineEditData.QuantityOrderd"
              :controls="false"
              :min="1"
              :max="999999"
              style="width: 70pt"
            />
          </template>
        </el-form-item>

        <el-form-item label="Price:">
          <el-input-number
            v-model="orderLineEditData.Price"
            :controls="false"
            :precision="4"
            :min="0.0000"
            :max="999999"
            style="width: 70pt"
          />
        </el-form-item>

        <el-form-item label="Total:">
          <span>{{
            (Math.round((orderLineEditData.QuantityOrderd * orderLineEditData.Price) * 100000) / 100000)
          }}</span>
        </el-form-item>

        <el-form-item label="Part Number:">
          <span>
            <el-input
              slot="reference"
              v-model="orderLineEditData.PartNo"
              placeholder="PartNo"
              style="width: 200px; margin-right: 10px;"
            />
            <el-popover placement="top" width="800" trigger="click">

              <el-table :data="partOptions" @row-click="(row, column, event) =>supplierPartSelect(orderLineEditData, row, column, event)">
                <el-table-column width="120" property="ManufacturerName" label="Manufacturer" />
                <el-table-column property="ManufacturerPartNumber" label="P/N" />
                <el-table-column property="SupplierPartNumber" label="SKU" />
                <el-table-column property="Note" label="Note" />

              </el-table>

              <el-button slot="reference" @click="getPartData(orderLineEditData)">Search</el-button>
            </el-popover>
          </span>
        </el-form-item>

        <el-form-item label="Order Reference:">
          <el-input v-model="orderLineEditData.OrderReference" />
        </el-form-item>

        <el-form-item label="Sku:">
          <el-input v-model="orderLineEditData.SupplierSku" />
        </el-form-item>

        <el-form-item label="Manufacturer:">
          <el-select
            v-model="orderLineEditData.ManufacturerName"
            placeholder="Manufacturer"
            filterable
            style="min-width: 200px; margin-right: 10px;"
          >
            <el-option v-for="item in partManufacturer" :key="item.Name" :label="item.Name" :value="item.Name" />
          </el-select>
        </el-form-item>

        <el-form-item label="MPN:">
          <el-input v-model="orderLineEditData.ManufacturerPartNumber" />
        </el-form-item>

        <el-form-item label="Description:">
          <el-input v-model="orderLineEditData.Description" />
        </el-form-item>
        <el-form-item label="Note:">
          <el-input v-model="orderLineEditData.Note" type="textarea" placeholder="Note" />
        </el-form-item>
      </el-form>

      <span slot="footer" class="dialog-footer">
        <el-button type="danger" @click="orderLineEditDialogVisible = false, deleteLine(orderLineEditData.LineNo)">
          Delete</el-button>
        <el-button type="primary" @click="orderLineEditDialogVisible = false, save([orderLineEditData])">Save
        </el-button>
        <el-button @click="orderLineEditDialogVisible = false">Cancel</el-button>
      </span>
    </el-dialog>

    <el-dialog width="85%" title="Pending Order Request" :visible.sync="orderReqestDialogVisible" @open="getOrderRequests()">
      <el-table ref="itemTable" :key="tableKey" :data="orderRequests" border style="width: 100%">
        <el-table-column prop="ManufacturerName" label="Manufacturer" width="150" />

        <el-table-column prop="ManufacturerPartNumber" label="Manufacturer Part Number" width="250">
          <template slot-scope="{ row }">
            <router-link :to="'/mfrParts/partView/' + row.ManufacturerPartId" class="link-type">
              <span>{{ row.ManufacturerPartNumber }}</span>
            </router-link>
          </template>
        </el-table-column>
        <el-table-column prop="SupplierPartNumber" label="Supplier Part Number" width="250" />
        <el-table-column prop="Quantity" label="Quantity" width="100" />
        <el-table-column prop="PartNoList" label="Production Part" />
        <el-table-column width="100">
          <template slot-scope="{ row }">
            <el-button style="float: right;" type="text" size="mini" @click="addRequestToOrder(row)">Add To Order
            </el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-dialog>  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'

import permission from '@/directive/permission/index.js'

export default {
  name: 'PurchaseOrderEdit',
  directives: { permission },
  props: { orderData: { type: Object, default: null }},
  data() {
    return {
      lines: [],
      tableKey: 0,
      line: 0,
      orderStatus: 0,
      orderRequests: null,
      orderReqestDialogVisible: false,
      orderLineEditDialogVisible: false,
      orderLineEditData: {},
      partManufacturer: [],
      partOptions: []
    }
  },
  mounted() {
    this.getManufacturers()
    this.getOrderLines()
  },
  methods: {
    supplierPartSelect(data, row, column, event) {
      data.SupplierPartId = row.SupplierPartId
      data.SupplierSku = row.SupplierPartNumber
      data.ManufacturerName = row.ManufacturerName
      data.Note = row.Note
      data.Description = row.Description
      data.ManufacturerPartNumber = row.ManufacturerPartNumber
    },
    save(lines) {
      requestBN({
        method: 'post',
        url: '/purchasing/item/edit',
        data: { data: { Action: 'save', Lines: lines, PoNo: this.$props.orderData.PoNo }}
      }).then(response => {
        if (response.error == null) {
          this.getOrderLines()
          this.$message({
            showClose: true,
            message: 'Changes saved successfully',
            duration: 1500,
            type: 'success'
          })
        } else {
          this.$message({
            showClose: true,
            message: response.error,
            duration: 0,
            type: 'error'
          })
        }
      })
    },
    deleteLine(lineNo) {
      this.$confirm('This will permanently delete line ' + lineNo + '. Continue?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning'
      }).then(() => {
        requestBN({
          method: 'post',
          url: '/purchasing/item/edit',
          data: { data: { Action: 'delete', LineNo: lineNo, PoNo: this.$props.orderData.PoNo }}
        }).then(response => {
          if (response.error == null) {
            this.getOrderLines()
            this.$message({
              showClose: true,
              message: 'Changes saved successfully',
              duration: 1500,
              type: 'success'
            })
          } else {
            this.$message({
              showClose: true,
              message: response.error,
              duration: 0,
              type: 'error'
            })
          }
        })
      }).catch(() => {
        this.$message({
          type: 'info',
          message: 'Delete canceled'
        })
      })
    },
    updateTable() {
      this.tableKey += 1
      this.$forceUpdate()
    },
    addRequestToOrder(orderRequestData) {
      this.line++

      const row = {
        OrderLineId: 0,
        LineNo: this.line,
        QuantityOrderd: orderRequestData.Quantity,
        SupplierSku: orderRequestData.SupplierPartNumber,
        Description: '',
        Price: 0,
        Type: 'Part',

        PartNo: orderRequestData.PartNoList,
        ManufacturerName: orderRequestData.ManufacturerName,
        ManufacturerPartNumber: orderRequestData.ManufacturerPartNumber,

        Note: null
      }

      this.lines.push(row)
      this.save([row])
    },
    addLine(lineType) {
      this.line++

      const row = {
        OrderLineId: 0,
        LineNo: this.line,
        QuantityOrderd: 1,
        SupplierSku: null,
        Description: ' ',
        Price: 0,
        Type: lineType,

        PartNo: null,
        ManufacturerName: null,
        ManufacturerPartNumber: '',

        Note: null
      }

      this.lines.push(row)
    },
    calcSum(param) {
      let total = 0
      this.lines.forEach(element => {
        const line = element.QuantityOrderd * element.Price
        total += line
      })

      const totalLine = []
      totalLine[0] = 'Total'
      totalLine[5] = Math.round(total * 10000) / 10000
      return totalLine
    },
    getOrderLines() {
      requestBN({
        url: '/purchasing/item',
        methood: 'get',
        params: {
          PurchaseOrderNo: this.$props.orderData.PoNo
        }
      }).then(response => {
        this.lines = response.data.Lines
        this.line = this.lines.length
      })
    },
    getManufacturers() {
      requestBN({
        url: '/part/manufacturer',
        methood: 'get'
      }).then(response => {
        this.partManufacturer = response.data
      })
    },
    getPartData(row) {
      if (row.PartNo === null) return
      requestBN({
        url: '/supplier/supplierPart',
        methood: 'get',
        params: { ProductionPartNo: row.PartNo }
      }).then(response => {
        this.partOptions =
          response.data
      })
    },
    getOrderRequests() {
      requestBN({
        url: '/purchasing/orderRequest',
        methood: 'get',
        params: { SupplierId: this.$props.orderData.SupplierId }
      }).then(response => {
        this.orderRequests = response.data
      })
    }
  }
}
</script>
