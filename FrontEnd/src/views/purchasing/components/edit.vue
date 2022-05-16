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

        <el-button v-if="meta.OrderImportSupported == true" @click="openOrderImport()">Import</el-button>
        <el-button @click="orderReqestDialogVisible = true">Order Reqests</el-button>

      </el-form-item>
    </el-form>
    <el-table-draggable
      v-permission="['purchasing.edit']"
      @input="reorderLines(), save(lines)"
    >
      <el-table
        ref="itemTable"
        :key="tableKey"
        row-key="OrderLineId"
        :data="lines"
        border
        :cell-style="{ padding: '0', height: '20px' }"
        style="width: 100%"
        @row-click="(row, column, event) =>openEdit(row)"
      >
        <el-table-column prop="LineNo" label="Line" width="70" />
        <el-table-column prop="QuantityOrderd" label="Quantity" width="80" />
        <el-table-column prop="UnitOfMeasurement" label="Unit" width="50" />
        <el-table-column label="SKU" prop="SupplierSku" width="220" />
        <el-table-column label="Item">
          <template slot-scope="{ row }">
            <template v-if="row.LineType == 'Generic'">{{ row.Description }}</template>
            <template v-if="row.LineType == 'Part'">
              {{ row.PartNo }} - {{ row.ManufacturerName }} - {{
                row.ManufacturerPartNumber
              }} - {{ row.Description }}
            </template>
          </template>
        </el-table-column>
        <el-table-column prop="ExpectedReceiptDate" label="Expected" width="100" />
        <el-table-column prop="LinePrice" label="Price" width="100" />
        <el-table-column prop="Total" label="Total" width="100" />
      </el-table>
    </el-table-draggable>

    <orderTotal :total="total" />

    <template v-permission="['purchasing.edit']">
      <el-dialog title="Order Line" :visible.sync="orderLineEditDialogVisible" :close-on-click-modal="false">
        <el-form label-width="150px">
          <el-form-item label="Line:">{{ orderLineEditData.LineNo }}</el-form-item>

          <el-form-item label="Type:">
            <el-select
              v-model="orderLineEditData.LineType"
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

          <el-form-item label="Unit:">
            <el-select
              v-model="orderLineEditData.UnitOfMeasurementId"
              placeholder="UoM"
              filterable
              style="min-width: 200px; margin-right: 10px;"
            >
              <el-option v-for="item in uom" :key="item.Id" :label="item.Symbol +' - '+ item.Unit" :value="item.Id" />
            </el-select>
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

          <el-form-item label="VAT :">
            <el-select
              v-model="orderLineEditData.VatTaxId"
              placeholder="VAT"
              filterable
              style="min-width: 200px; margin-right: 10px;"
            >
              <el-option v-for="item in vat" :key="item.Id" :label="item.Value +'% - '+item.Description" :value="item.Id" />
            </el-select>
          </el-form-item>

          <el-form-item label="Discount:">
            <el-input-number
              v-model="orderLineEditData.Discount"
              :controls="false"
              :precision="3"
              :min="0.00"
              :max="100.00"
              style="width: 70pt"
            />
          </el-form-item>

          <el-form-item label="Total:">
            <span>{{
              (Math.round((orderLineEditData.QuantityOrderd * orderLineEditData.Price) * 100000) / 100000)
            }}</span>
          </el-form-item>
          <el-form-item label="Expected Receipt:">
            <el-date-picker
              v-model="orderLineEditData.ExpectedReceiptDate"
              type="date"
              placeholder="Pick a day"
              value-format="yyyy-MM-dd"
            />
          </el-form-item>
          <el-form-item v-if="orderLineEditData.LineType == 'Part'" label="Part Number:">

            <el-popover placement="top" width="800" trigger="click">

              <el-table :data="partOptions" @row-click="(row, column, event) =>supplierPartSelect(orderLineEditData, row, column, event)">
                <el-table-column width="120" property="ManufacturerName" label="Manufacturer" />
                <el-table-column property="ManufacturerPartNumber" label="P/N" />
                <el-table-column property="SupplierPartNumber" label="SKU" />
                <el-table-column property="Note" label="Note" />

              </el-table>
              <span slot="reference">
                <el-input
                  v-model="orderLineEditData.PartNo"
                  placeholder="PartNo"
                  style="width: 200px; margin-right: 10px;"
                  @keyup.enter.native="getPartData(orderLineEditData)"
                />
                <el-button slot="reference" @click="getPartData(orderLineEditData)">Search</el-button>
              </span>
            </el-popover>

            </span>
          </el-form-item>

          <el-form-item label="Order Reference:">
            <el-input v-model="orderLineEditData.OrderReference" />
          </el-form-item>

          <el-form-item label="Sku:">
            <el-input v-model="orderLineEditData.SupplierSku" />
          </el-form-item>

          <el-form-item v-if="orderLineEditData.LineType == 'Part'" label="Manufacturer:">
            <el-select
              v-model="orderLineEditData.ManufacturerName"
              placeholder="Manufacturer"
              filterable
              style="min-width: 200px; margin-right: 10px;"
            >
              <el-option v-for="item in partManufacturer" :key="item.Name" :label="item.Name" :value="item.Name" />
            </el-select>
          </el-form-item>

          <el-form-item v-if="orderLineEditData.LineType == 'Part'" label="MPN:">
            <el-input v-model="orderLineEditData.ManufacturerPartNumber" />
          </el-form-item>
          <el-form-item v-if="orderLineEditData.LineType == 'Part'" label="Stock Part:">
            <el-checkbox  v-model="orderLineEditData.StockPart" />
          </el-form-item>
          <el-form-item label="Description:">
            <el-input v-model="orderLineEditData.Description" />
          </el-form-item>
          <el-form-item label="Note:">
            <el-input v-model="orderLineEditData.Note" type="textarea" placeholder="Note" />
          </el-form-item>
        </el-form>

        <span slot="footer" class="dialog-footer">
          <el-button type="danger" @click="orderLineEditDialogVisible = false, deleteLine(orderLineEditData)">
            Delete</el-button>
          <el-button type="primary" @click="orderLineEditDialogVisible = false, save([orderLineEditData])">Save
          </el-button>
          <el-button @click="orderLineEditDialogVisible = false">Cancel</el-button>
        </span>
      </el-dialog>
    </template>

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
    </el-dialog>  

    <orderImportDialog :visible.sync="importDialogVisible" :meat ="meta" @closed="getOrderLines()"/>
    
    </div>
</template>

<script>
import requestBN from '@/utils/requestBN'

import permission from '@/directive/permission/index.js'
import ElTableDraggable from 'el-table-draggable'
import * as defaultSetting from '@/utils/defaultSetting'

import orderTotal from './orderTotal'
import orderImportDialog from './orderImportDialog'

export default {
  name: 'PurchaseOrderEdit',
  directives: { permission },
  components: { ElTableDraggable, orderTotal, orderImportDialog },
  props: { orderData: { type: Object, default: null }},
  data() {
    return {
      lines: [],
      total: {},
      meta: {},
      tableKey: 0,
      line: 0,
      orderStatus: 0,
      orderRequests: null,
      orderReqestDialogVisible: false,
      orderLineEditDialogVisible: false,
      importDialogVisible: false,
      orderLineEditData: {},
      partManufacturer: [],
      partOptions: [],
      vat: [],
      uom: []
    }
  },
  mounted() {
    this.getManufacturers()
    this.getVAT()
    this.getUOM()
    this.getOrderLines()
  },
  methods: {
    openEdit(row) {
      this.reorderLines()
      this.partOptions = []
      this.orderLineEditData = JSON.parse(JSON.stringify(row))
      this.orderLineEditDialogVisible = true
    },
    supplierPartSelect(data, row, column, event) {
      data.SupplierPartId = row.SupplierPartId
      data.SupplierSku = row.SupplierPartNumber
      data.ManufacturerName = row.ManufacturerName
      data.Note = row.Note
      data.Description = row.Description
      data.ManufacturerPartNumber = row.ManufacturerPartNumber
    },
    openOrderImport()
    {
      if(this.lines.length > 0)
      {
        this.$alert('To import an order, the order can not contain any lines. Please remove all lines and try again.', 'Cannot Import Order', {
          confirmButtonText: 'OK'
        });
      }
      else{
        this.importDialogVisible = true
      }
    },
    save(lines) {
      requestBN({
        method: 'post',
        url: '/purchasing/item/edit',
        data: { data: { Action: 'save', Lines: lines, PoNo: this.$props.orderData.PoNo }}
      }).then(response => {
        if (response.error == null) {
          this.lines = response.data.Lines
          this.total = response.data.Total
          this.line = this.lines.length
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
    deleteLine(orderLineData) {
      this.$confirm('This will permanently delete line ' + orderLineData.LineNo + '. Continue?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning'
      }).then(() => {
        requestBN({
          method: 'post',
          url: '/purchasing/item/edit',
          data: { data: { Action: 'delete', OrderLineId: orderLineData.OrderLineId, PoNo: this.$props.orderData.PoNo }}
        }).then(response => {
          if (response.error == null) {
            this.lines = response.data.Lines
            this.line = this.lines.length
            this.reorderLines()
            this.save(this.lines)
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
    reorderLines() {
      this.line = 1
      this.lines.forEach(element => {
        element.LineNo = this.line
        this.line++
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
        StockPart: true,
        Price: 0,
        Discount: 0,
        VatTaxId: Number(defaultSetting.defaultSetting().PurchasOrder.VAT),
        UnitOfMeasurementId: Number(defaultSetting.defaultSetting().PurchasOrder.UoM),
        LineType: 'Part',
        OrderReference: null,
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
        Description: '',
        StockPart: true,
        Price: 0,
        Discount: 0,
        VatTaxId: Number(defaultSetting.defaultSetting().PurchasOrder.VAT),
        UnitOfMeasurementId: Number(defaultSetting.defaultSetting().PurchasOrder.UoM),
        LineType: lineType,
        OrderReference: null,
        PartNo: null,
        ManufacturerName: null,
        ManufacturerPartNumber: '',
        ExpectedReceiptDate: null,

        Note: null
      }
      this.openEdit(row)
    },
    getOrderLines() {
      requestBN({
        url: '/purchasing/item',
        methood: 'get',
        params: {
          PurchaseOrderNo: this.$props.orderData.PoNo
        }
      }).then(response => {
        this.meta = response.data.MetaData
        this.lines = response.data.Lines
        this.total = response.data.Total
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
    getUOM() {
      requestBN({
        url: '/unitOfMeasurement',
        methood: 'get',
        params: {
          Countable: true
        }
      }).then(response => {
        this.uom = response.data
      })
    },
    getPartData(row) {
      if (row.PartNo === null) return
      requestBN({
        url: '/supplier/supplierPart',
        methood: 'get',
        params: { ProductionPartNo: row.PartNo, SupplierId: this.$props.orderData.SupplierId }
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
