<template>
  <div class="app-container">
    <h1>{{ orderData.Title }}, PO-{{ orderData.PoNo }}</h1>
    <el-steps :active="orderStatus" finish-status="success" align-center>
      <el-step title="Edit" />
      <el-step title="Place" />
      <el-step title="Confirme" />
      <el-step title="Closed" />
    </el-steps>

    <template v-if="orderData.Status == 'Editing'" v-permission="['purchasing.edit']">
      <el-button type="info" @click="place">Place Order</el-button>
    </template>

    <template v-if="orderData.Status == 'Placed'" v-permission="['purchasing.edit']">
      <el-button type="info" @click="edit">Edit Order</el-button>
      <el-button type="info" @click="confirm">Confirm Order</el-button>
    </template>

    <template v-if="orderData.Status == 'Confirmed'" v-permission="['purchasing.edit']">
      <el-button type="info" @click="edit">Edit Order</el-button>
      <el-button type="info" @click="confirm">Confirm Order</el-button>
      <el-button type="info" @click="close">Close Order</el-button>
    </template>

    <el-button  @click="openPoDoc()" style="float: right" icon="el-icon-document">Export Purchase Order</el-button>
    <el-divider />
    <p>
      <b>
        {{ orderData.SupplierName }} -
        {{ orderData.PurchaseDate }}
      </b>
    </p>
    <p>
      <b>Order Number:</b>
      {{ orderData.OrderNumber }}
    </p>
    <p>
      <b>Acknowledgement Number:</b>
      {{ orderData.AcknowledgementNumber }}
    </p>
    <p>
      <b>Quotation Number:</b>
      {{ orderData.QuotationNumber }}
    </p>
    <p>
      <b>Currency:</b>
      {{ orderData.CurrencyCode }}
    </p>
    <p>
      <b>Exchange Rate:</b>
      {{ orderData.ExchangeRate }}
    </p>
    <p>
      <b>Description:</b>
      {{ orderData.Description }}
    </p>

    <el-button
      v-if="orderData.Status == 'Editing'"
      v-permission="['purchasing.edit']"
      style="margin-top: 20px"
      type="primary"
      @click="editMeta"
    >Edit</el-button>

    <el-divider />

    <editOrder v-if="orderData.Status == 'Editing'" :order-data="orderData" />

    <placedOrder v-if="orderData.Status == 'Placed'" :order-data="orderData" />

    <confirmedOrder v-if="orderData.Status == 'Confirmed'" :order-data="orderData" />

    <closedOrder v-if="orderData.Status == 'Closed'" :order-data="orderData" />

    <el-divider />
    <h3>Documents</h3>
    <documentsList v-if="orderData.Status != 'Closed'" :documents="documents" :edit="true" />
    <documentsList v-if="orderData.Status == 'Closed'" :documents="documents" :edit="false" />

    <el-dialog title="Edit Order" :visible.sync="showDialog" width="50%" center>
      <el-form size="mini" label-width="220px">
        <el-form-item label="Titel:">
          <el-input v-model="dialogData.Title" style="width: 350px" placeholder="Titel" />
        </el-form-item>

        <el-form-item label="Supplier:">
          <el-cascader
            v-model="dialogData.SupplierId"
            :options="suppliers"
            filterable
            placeholder="Supplier"
            :props="{
              emitPath: false,
              value: 'Id',
              label: 'Name',
              children: 'Children',
              checkStrictly: true
            }"
          />
        </el-form-item>

        <el-form-item label="Supplier Address:">
          <el-select v-model="dialogData.VendorAddressId" placeholder="Currency" filterable>
            <el-option v-for="item in supplierAddress" :key="item.Id" :label="item.Street+', '+item.PostalCode+' '+item.City+', '+item.CountryName" :value="item.Id" />
          </el-select>
        </el-form-item>

        <el-form-item label="Supplier Contact:">
          <el-select v-model="dialogData.VendorContactId" placeholder="Currency" filterable>
            <el-option v-for="item in supplierContact" :key="item.Id" :label="item.FirstName+' '+item.LastName" :value="item.Id" />
          </el-select>
        </el-form-item>

        <el-form-item label="Order Number:">
          <el-input
            v-model="dialogData.OrderNumber"
            style="width: 350px"
            placeholder="Supplier Order Number"
          />
        </el-form-item>

        <el-form-item label="Acknowledgement Number:">
          <el-input
            v-model="dialogData.AcknowledgementNumber"
            style="width: 350px"
            placeholder="Supplier Order Number"
          />
        </el-form-item>

        <el-form-item label="Quotation Number:">
          <el-input
            v-model="dialogData.QuotationNumber"
            style="width: 350px"
            placeholder="Supplier Quotation Number"
          />
        </el-form-item>

        <el-form-item label="Order Date:">
          <el-date-picker
            v-model="dialogData.PurchaseDate"
            type="date"
            placeholder="Pick a day"
            value-format="yyyy-MM-dd"
          />
        </el-form-item>

        <el-form-item label="Currency">
          <el-select v-model="dialogData.CurrencyId" placeholder="Currency" filterable>
            <el-option v-for="item in currencies" :key="item.Id" :label="item.CurrencyCode+' - '+item.Name" :value="item.Id" />
          </el-select>
        </el-form-item>

        <el-form-item label="Exchange Rate">
          <el-input-number
            v-model="dialogData.ExchangeRate"
            :controls="false"
            :precision="4"
            :min="0.0000"
            :max="999999"
            style="width: 70pt"
            filterable
          />
          <span :style="{margin: '10px'}"><el-button type="primary" @click="getExchangeRate()">Get Rate</el-button><br> Data provided by the European Central Bank</span>
        </el-form-item>

        <el-form-item label="Description:">
          <el-input v-model="dialogData.Description" type="textarea" placeholder="Description" />
        </el-form-item>
      </el-form>

      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="saveEditMeta()">Save</el-button>
        <el-button @click="showDialog = false">Cancel</el-button>
      </span>
    </el-dialog>
  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'
import editOrder from './components/edit'
import placedOrder from './components/placed'
import confirmedOrder from './components/confirmed'
import closedOrder from './components/closed'

import permission from '@/directive/permission/index.js'
import documentsList from '@/views/documents/components/listDocuments'

export default {
  name: 'PurchaseOrder',
  components: { editOrder, placedOrder, confirmedOrder, closedOrder, documentsList },
  directives: { permission },
  data() {
    return {
      PoNo: this.$route.params.PoNo,
      orderData: null,
      exchangeRateData: null,
      orderStatus: 0,
      suppliers: {},
      currencies: {},
      documents: {},
      supplierAddress: {},
      supplierContact: {},

      showDialog: false,
      dialogData: { type: Object, default: this.orderData }
    }
  },
  created() {
    // Why need to make a copy of this.$route here?
    // Because if you enter this page and quickly switch tag, may be in the execution of the setTagsViewTitle function, this.$route is no longer pointing to the current page
    // https://github.com/PanJiaChen/vue-element-admin/issues/1221
    this.tempRoute = Object.assign({}, this.$route)
  },
  mounted() {
    this.getOrder()
    this.setTagsViewTitle()
    this.getCurrency()
  },
  methods: {
    edit() {
      this.orderData.Status = 'Editing'
      this.saveStatus(this.orderData.Status)
    },
    place() {
      this.orderData.Status = 'Placed'
      this.saveStatus(this.orderData.Status)
    },
    confirm() {
      this.orderData.Status = 'Confirmed'
      this.saveStatus(this.orderData.Status)
    },
    close() {
      this.orderData.Status = 'Closed'
      this.saveStatus(this.orderData.Status)
    },
    editMeta() {
      this.getSuppliers()
      this.dialogData = Object.assign({}, this.orderData)
      this.showDialog = true
    },
    saveEditMeta() {
      this.showDialog = false
      this.saveData(this.dialogData)
    },
    saveState(data) {
      this.saveData(data)
    },
    openPoDoc(){
      let path = process.env.VUE_APP_BLUENOVA_BASE+"/renderer/purchaseOrder.php?PurchaseOrderNo="+this.orderData.PoNo;
      window.open(path, '_blank').focus()
    },
    saveStatus(status){
      requestBN({
        method: 'PATCH',
        url: '/purchasing/item/status',
        params: { PurchaseOrderNo: this.PoNo },
        data: { Status: status },
      }).then(response => {
        if (response.error == null) {
          this.$message({
            showClose: true,
            message: 'Changes saved successfully',
            duration: 1500,
            type: 'success'
          })
          this.getOrder()
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
    saveData(data) {
      requestBN({
        method: 'PATCH',
        url: '/purchasOrder',
        params: { PurchaseOrderNo: this.PoNo },
        data: { data: data }
      }).then(response => {
        if (response.error == null) {
          this.$message({
            showClose: true,
            message: 'Changes saved successfully',
            duration: 1500,
            type: 'success'
          })
          this.getOrder()
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
    getExchangeRate() {
      requestBN({
        url: '/finance/exchangeRate',
        methood: 'get',
        params: {
          CurrencyId: this.dialogData.CurrencyId
        }
      }).then(response => {
        if (response.error == null) {
          this.exchangeRateData = response.data
          this.dialogData.ExchangeRate = this.exchangeRateData.ExchangeRate
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
    getSuppliers() {
      requestBN({
        url: '/supplier',
        methood: 'get'
      }).then(response => {
        this.suppliers = response.data
      })
    },
    getSupplierAddress() {
      requestBN({
        url: '/vendor/address',
        methood: 'get',
        params: {
          VendorId: this.orderData.SupplierId
        }
      }).then(response => {
        this.supplierAddress = response.data
      })
    },
     getSupplierContact() {
      requestBN({
        url: '/vendor/contact',
        methood: 'get',
        params: {
          VendorId: this.orderData.SupplierId
        }
      }).then(response => {
        this.supplierContact = response.data
      })
    },
    getCurrency() {
      requestBN({
        url: '/finance/currency',
        methood: 'get'
      }).then(response => {
        this.currencies = response.data
      })
    },
    setTagsViewTitle() {
      const route = Object.assign({}, this.tempRoute, {
        title: 'PO-' + `${this.$route.params.PoNo}`
      })
      this.$store.dispatch('tagsView/updateVisitedView', route)
    },
    getOrder() {
      requestBN({
        url: '/purchasing/item',
        methood: 'get',
        params: {
          PurchaseOrderNo: this.PoNo
        }
      }).then(response => {
        this.orderData = response.data.MetaData
        this.dialogData = this.orderData
        if (this.orderData.Status === 'Editing') this.orderStatus = 0
        else if (this.orderData.Status === 'Placed') this.orderStatus = 1
        else if (this.orderData.Status === 'Confirmed') this.orderStatus = 2
        else if (this.orderData.Status === 'Closed') this.orderStatus = 4
        this.documents = response.data.Documents
        this.lines = response.data.Lines
        this.line = this.lines.length

        this.getSupplierAddress()
        this.getSupplierContact()
      })
    }
  }
}
</script>
