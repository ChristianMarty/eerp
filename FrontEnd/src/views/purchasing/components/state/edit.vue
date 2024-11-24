<template>
  <div class="edit-container">
    <h2>Items:</h2>
    <el-form v-permission="['purchasing.edit']" :inline="true" style="margin-top: 20px">
      <el-form-item>
        <el-button
          v-permission="['purchasing.edit']"
          type="primary"
          icon="el-icon-plus"
          circle
          style="margin-top: 20px"
          @click="addItemLine()"
        />
        <el-button v-if="apiInfo.Capability.OrderImportSupported === true" @click="openOrderImport()">Import</el-button>
        <el-button v-if="apiInfo.Capability.OrderUploadSupported === true" @click="openOrderUpload()">Upload</el-button>
        <el-button @click="orderReqestDialogVisible = true">Order Requests</el-button>
        <el-button @click="setVatVisible = true">Set VAT</el-button>
        <el-button @click="setExpectedDateVisible = true">Set Expected Date</el-button>
      </el-form-item>
    </el-form>

    <el-table-draggable
      v-permission="['purchasing.edit']"
      @input="onItemLineDrag()"
    >
      <el-table
        ref="itemTable"
        row-key="OrderLineId"
        :data="poData.Lines"
        border
        :cell-style="{ padding: '0', height: '20px' }"
        style="width: 100%"
        @row-click="(row, column, event) =>openItemLineEdit(row)"
      >
        <el-table-column prop="LineNumber" label="Line" width="70" />
        <el-table-column prop="QuantityOrdered" label="Quantity" width="80" />
        <el-table-column prop="UnitOfMeasurement" label="Unit" width="50" />
        <el-table-column prop="LineType" label="Type" width="150" />
        <el-table-column label="SKU" prop="SupplierSku" width="220" />
        <el-table-column label="Item">
          <template slot-scope="{ row }">
            <template v-if="row.LineType === 'Generic'">{{ row.Description }}</template>
            <template v-if="row.LineType === 'Part'">
              {{ row.PartNo }} - {{ row.ManufacturerName }} - {{
                row.ManufacturerPartNumber
              }} - {{ row.Description }}
            </template>
            <template v-if="row.LineType === 'Specification Part'">
              {{ row.PartNo }} - {{ row.ManufacturerName }} - {{
                row.ManufacturerPartNumber
              }} - {{ row.Description }}
            </template>
          </template>
        </el-table-column>
        <el-table-column prop="ExpectedReceiptDate" label="Expected" width="100" />
        <el-table-column prop="Discount" label="Discount" width="100" />
        <el-table-column prop="VatValue" label="VAT" width="100" />
        <el-table-column prop="LinePrice" label="Price" width="100" />
        <el-table-column prop="Total" label="Total" width="100" />
      </el-table>
    </el-table-draggable>

    <el-divider />

    <h2>Additional Charges:</h2>
    <el-button
      v-permission="['purchasing.edit']"
      type="primary"
      icon="el-icon-plus"
      circle
      style="margin-top: 20px; margin-bottom: 20px"
      @click="addAdditionalChargesLine()"
    />
    <el-table-draggable
      v-permission="['purchasing.edit']"
      @input="onAdditionalChargesLineDrag()"
    >
      <el-table
        ref="additionalChargesTable"
        row-key="AdditionalChargesLineNumber"
        :data="poData.AdditionalCharges"
        border
        :cell-style="{ padding: '0', height: '20px' }"
        style="width: 100%"
        @row-click="(row, column, event) =>openAdditionalChargesLine(row)"
      >
        <el-table-column prop="LineNumber" label="Line" width="70" />
        <el-table-column prop="Type" label="Type" width="100" />
        <el-table-column prop="Quantity" label="Quantity" width="80" />
        <el-table-column prop="Description" label="Description" />
        <el-table-column prop="VatValue" label="VAT" width="100" />
        <el-table-column prop="Price" label="Price" width="100" />
        <el-table-column prop="Total" label="Total" width="100" />
      </el-table>
    </el-table-draggable>
    <el-divider />

    <orderTotal :total="poData.Total" />

    <template v-permission="['purchasing.edit']">
      <editLineItemDialog
        :visible.sync="orderLineEditDialogVisible"
        :line-id="editOrderLineId"
        :purchase-order="orderData"
        @closed="getOrderLines()"
        @refresh="refreshPage()"
      />

      <editAdditionalChargesDialog
        :visible.sync="additionalChargesDialogVisible"
        :line="additionalChargesLine"
        :purchase-order="orderData"
        @closed="getOrderLines()"
        @refresh="refreshPage()"
      />

      <orderImportDialog
        v-if="apiInfo.Capability.OrderImportSupported === true"
        :visible.sync="importDialogVisible"
        :meat="poData.MetaData"
        @closed="getOrderLines()"
        @refresh="refreshPage()"
      />

      <orderUploadDialog
        v-if="apiInfo.Capability.OrderUploadSupported === true"
        :visible.sync="uploadDialogVisible"
        :meat="poData.MetaData"
        @closed="getOrderLines()"
        @refresh="refreshPage()"
      />

    </template>

    <el-dialog width="85%" title="Pending Order Request" :visible.sync="orderReqestDialogVisible" @open="getOrderRequests()">
      <el-table ref="itemTable" :data="orderRequests" border style="width: 100%">
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

    <el-dialog title="Set All Expected Dates" :visible.sync="setExpectedDateVisible">
      <el-date-picker
        v-model="allExpectedDate"
        type="date"
        placeholder="Pick a day"
        value-format="yyyy-MM-dd"
      />
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="setExpectedDateVisible = false; setExpectedDate(allExpectedDate);">Confirm</el-button>
        <el-button @click="setExpectedDateVisible = false">Cancel</el-button>
      </span>
    </el-dialog>

    <el-dialog title="Set All VAT" :visible.sync="setVatVisible">
      <el-select
        v-model="allVatId"
        placeholder="VAT"
        filterable
        style="min-width: 200px; margin-right: 10px;"
      >
        <el-option v-for="item in vat" :key="item.Id" :label="item.Value +'% - '+item.Description" :value="item.Id" />
      </el-select>
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="setVatVisible = false; setVat(allVatId);">Confirm</el-button>
        <el-button @click="setVatVisible = false">Cancel</el-button>
      </span>
    </el-dialog>
  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'

import permission from '@/directive/permission'
import ElTableDraggable from 'el-table-draggable'

import orderTotal from '../orderTotal.vue'
import orderImportDialog from '../orderImportDialog.vue'
import orderUploadDialog from '../orderUploadDialog.vue'
import editLineItemDialog from '../editLineItemDialog.vue'
import editAdditionalChargesDialog from '../editAdditionalChargesDialog.vue'

import Purchase from '@/api/purchase'
const purchase = new Purchase()

import Vendor from '@/api/vendor'
const vendor = new Vendor()

import Finance from '@/api/finance'
const finance = new Finance()

import * as defaultSetting from '@/utils/defaultSetting'

const emptyAdditionalChargesLine = {
  AdditionalChargesLineId: 0,
  LineNumber: 0,
  Type: 'Shipping',
  Quantity: 1,
  Price: 0,
  VatTaxId: Number(defaultSetting.defaultSetting().PurchaseOrder.VAT),
  Description: ''
}

export default {
  name: 'PurchaseOrderEdit',
  directives: { permission },
  components: { ElTableDraggable, orderTotal, orderImportDialog, orderUploadDialog, editLineItemDialog, editAdditionalChargesDialog },
  props: {
    orderData: { type: Object, default: Object }
  },
  data() {
    return {
      poData: {},
      apiInfo: Object.assign({}, vendor.api.informationReturn),

      itemLineIndex: 0,
      additionalChargesLineIndex: 0,
      orderStatus: 0,
      orderRequests: null,

      editOrderLineId: 0,
      allExpectedDate: null,
      allVatId: Number(defaultSetting.defaultSetting().PurchaseOrder.VAT),
      additionalChargesLine: {},

      additionalChargesDialogVisible: false,
      orderLineEditDialogVisible: false,
      setVatVisible: false,
      importDialogVisible: false,
      uploadDialogVisible: false,
      orderReqestDialogVisible: false,
      setExpectedDateVisible: false,

      vat: []
    }
  },
  async mounted() {
    this.getOrderLines()
    this.vat = await finance.tax.list('VAT')
  },
  methods: {
    onItemLineDrag() {
      this.reorderItemLines()
      this.saveLines()
    },
    openItemLineEdit(row) {
      this.reorderItemLines()
      this.editOrderLineId = row.OrderLineId
      this.orderLineEditDialogVisible = true
    },
    addItemLine() {
      this.reorderItemLines()
      this.editOrderLineId = 0
      this.orderLineEditDialogVisible = true
    },
    onAdditionalChargesLineDrag() {
      this.reorderAdditionalChargesLines()
      this.saveAdditionalCharges()
    },
    openAdditionalChargesLine(row) {
      this.reorderAdditionalChargesLines()
      this.additionalChargesLine = JSON.parse(JSON.stringify(row))
      this.additionalChargesDialogVisible = true
    },
    addAdditionalChargesLine() {
      this.additionalChargesLine = Object.assign({}, emptyAdditionalChargesLine)
      this.additionalChargesLine.LineNumber = this.additionalChargesLineIndex
      this.additionalChargesDialogVisible = true
    },
    openOrderUpload() {
      if (this.poData.Lines.length > 0) {
        this.$alert('To import an order, the order can not contain any lines. Please remove all lines and try again.', 'Cannot Import Order', {
          confirmButtonText: 'OK'
        })
      } else {
        this.uploadDialogVisible = true
      }
    },
    openOrderImport() {
      if (this.poData.Lines.length > 0) {
        this.$alert('To import an order, the order can not contain any lines. Please remove all lines and try again.', 'Cannot Import Order', {
          confirmButtonText: 'OK'
        })
      } else {
        this.importDialogVisible = true
      }
    },
    setExpectedDate(date) {
      this.poData.Lines.forEach(element => { element.ExpectedReceiptDate = date })
      this.saveLines()
    },
    setVat(vatId) {
      const vatValue = this.vat.find(x => x.Id === vatId)
      this.poData.Lines.forEach(element => { element.VatTaxId = vatId; element.VatValue = vatValue.Value })
      this.saveLines()
    },
    showSuccessMessage(message) {
      this.$message({
        showClose: true,
        message: message,
        duration: 1500,
        type: 'success'
      })
    },
    showErrorMessage(message) {
      this.$message({
        showClose: true,
        message: message,
        duration: 0,
        type: 'error'
      })
    },
    saveLines() {
      purchase.item.line.save(this.$props.orderData.ItemCode, this.poData.Lines).then(response => {
        this.showSuccessMessage()
      }).catch(response => {
        this.showErrorMessage(response)
      })
    },
    saveAdditionalCharges() {
      requestBN({
        method: 'post',
        url: '/purchasing/additionalCharge/edit',
        data: { data: { Action: 'save', Lines: this.poData.AdditionalCharges, PoNo: this.$props.orderData.ItemCode }}
      }).then(response => {
        if (response.error == null) {
          this.poData = response.data
          this.itemLineIndex = this.poData.Lines.length
          this.additionalChargesLineIndex = this.AdditionalCharges.length
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
    reorderItemLines() {
      this.itemLineIndex = 1
      this.poData.Lines.forEach(element => {
        element.LineNumber = this.itemLineIndex
        this.itemLineIndex++
      })
    },
    reorderAdditionalChargesLines() {
      this.additionalChargesLineIndex = 1
      this.poData.AdditionalCharges.forEach(element => {
        element.LineNumber = this.additionalChargesLineIndex
        this.additionalChargesLineIndex++
      })
    },
    /* addRequestToOrder(orderRequestData) {
      this.itemLineIndex++
      const newLine = Object.assign({}, emptyOrderLine)
      newLine.QuantityOrderd = orderRequestData.Quantity
      newLine.SupplierSku = orderRequestData.SupplierPartNumber
      newLine.PartNo = orderRequestData.PartNoList
      newLine.ManufacturerName = orderRequestData.ManufacturerName
      newLine.ManufacturerPartNumber = orderRequestData.ManufacturerPartNumber

      this.poData.Lines.push(newLine)
      this.save([newLine])
    },*/
    refreshPage() {
      this.getOrderLines()
    },
    getOrderLines() {
      purchase.item.search(this.$props.orderData.ItemCode).then(response => {
        this.poData = response
        this.itemLineIndex = this.poData.Lines.length
        this.additionalChargesLineIndex = this.poData.AdditionalCharges.length
        this.itemLineIndex++
        this.additionalChargesLineIndex++
        this.getImportApiInfo()
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
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
    },
    getImportApiInfo() {
      vendor.api.information(this.$props.orderData.SupplierId).then(response => {
        this.apiInfo = response
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
