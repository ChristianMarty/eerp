<template>
  <div class="app-container">
    <h1>{{ orderData.Title }}, {{ orderData.ItemCode }}</h1>
    <el-steps :active="orderStatus" finish-status="success" align-center>
      <el-step title="Edit" />
      <el-step title="Review" />
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
      <el-button type="info" @click="place">Place Order</el-button>
      <el-button type="info" @click="close">Close Order</el-button>
    </template>

    <template v-if="orderData.Status == 'Closed'" v-permission="['purchasing.edit']">
      <el-button type="info" @click="confirm">Reopen Order</el-button>
    </template>

    <template>
      <el-button style="float: right" icon="el-icon-document" @click="openPoDoc()">Export Purchase Order</el-button>
      <el-select
        v-model="rendererSelected"
        placeholder="Select Document"
        style="min-width: 200px; margin-right: 10px; float: right;"
      >
        <el-option
          v-for="item in rendererList"
          :key="item.Id"
          :label="item.Name"
          :value="item.Code"
        />
      </el-select>
    </template>

    <el-divider />
    <p>
      <b>
        <router-link :to="'/vendor/view/' + orderData.SupplierId" class="link-type">
          <span>{{ orderData.SupplierName }}</span>
        </router-link> -
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
    <p>
      <b>Payment Terms:</b>
      {{ orderData.PaymentTerms }}
    </p>
    <p>
      <b>Incoterms:</b>
      {{ orderData.InternationalCommercialTerms }}
    </p>
    <p>
      <b>Carrier:</b>
      {{ orderData.Carrier }}
    </p>
    <p>
      <b>Head Note:</b>
      {{ orderData.HeadNote }}
    </p>
    <p>
      <b>Foot Note:</b>
      {{ orderData.FootNote }}
    </p>

    <el-button
      v-if="orderData.Status == 'Editing'"
      v-permission="['purchasing.edit']"
      style="margin-top: 20px"
      type="primary"
      @click="openEditOrderMetaDialog()"
    >Edit</el-button>

    <el-divider />

    <editOrder v-if="orderData.Status == 'Editing'" :order-data="orderData" />
    <reviewOrder v-if="orderData.Status == 'Placed'" :order-data="orderData" />
    <confirmedOrder v-if="orderData.Status == 'Confirmed'" :order-data="orderData" />
    <closedOrder v-if="orderData.Status == 'Closed'" :order-data="orderData" />

    <el-divider />
    <h3>Documents</h3>
    <editDocumentsList
      v-if="orderData.Status != 'Closed'"
      attach="PurchaseOrderDocument"
      :barcode="orderData.ItemCode"
      @change="getOrder()"
    />
    <documentsList :documents="documents" />

    <editOrderMetaDialog
      :visible.sync="showOrderMetaEditDialog"
      :purchase-order-number="orderData.PurchaseOrderNumber"
      @refresh="refreshPage()"
    />
  </div>
</template>

<script>
import permission from '@/directive/permission/index.js'

import editOrder from './components/edit'
import reviewOrder from './components/review'
import confirmedOrder from './components/confirmed'
import closedOrder from './components/closed'

import editOrderMetaDialog from './components/editOrderMetaDialog'

import editDocumentsList from '@/views/document/components/editDocumentsList'
import documentsList from '@/views/document/components/documentsList'

import Purchase from '@/api/purchase'
const purchase = new Purchase()

import Print from '@/api/print'
const print = new Print()

export default {
  name: 'PurchaseOrder',
  components: { editOrder, reviewOrder, confirmedOrder, closedOrder, editDocumentsList, documentsList, editOrderMetaDialog },
  directives: { permission },
  data() {
    return {
      orderData: {},
      orderStatus: 0,
      documents: [],
      showOrderMetaEditDialog: false,

      rendererList: [],
      rendererSelected: null
    }
  },
  created() {
    // Why need to make a copy of this.$route here?
    // Because if you enter this page and quickly switch tag, may be in the execution of the setTagsViewTitle function, this.$route is no longer pointing to the current page
    // https://github.com/PanJiaChen/vue-element-admin/issues/1221
    this.tempRoute = Object.assign({}, this.$route)
  },
  mounted() {
    print.label.search('PurchaseOrder').then(response => {
      this.rendererList = response
      this.rendererSelected = this.rendererList[0].Code
    }).catch(response => {
      this.showErrorMessage(response)
    })

    this.getOrder()
  },
  methods: {
    edit() {
      this.saveState('Editing')
    },
    place() {
      this.saveState('Placed')
    },
    confirm() {
      this.saveState('Confirmed')
    },
    close() {
      this.saveState('Closed')
    },
    openEditOrderMetaDialog() {
      this.showOrderMetaEditDialog = true
    },
    openPoDoc() {
      const path = process.env.VUE_APP_BLUENOVA_BASE + '/renderer.php/' + this.rendererSelected + '?PurchaseOrderNumber=' + this.orderData.PurchaseOrderNumber
      window.open(path, '_blank').focus()
    },
    setTagsViewTitle() {
      const route = Object.assign({}, this.tempRoute, {
        title: 'PO-' + `${this.orderData.PurchaseOrderNumber}`
      })
      this.$store.dispatch('tagsView/updateVisitedView', route)
    },
    showSuccessMessage() {
      this.$message({
        showClose: true,
        message: 'Changes saved successfully',
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
    saveLine() {
      purchase.line.save(this.orderData.PurchaseOrderNumber, [this.line]).then(response => {
        this.showSuccessMessage()
      }).catch(response => {
        this.showErrorMessage(response)
      })
    },
    saveState(newState) {
      purchase.item.updateState(this.orderData.PurchaseOrderNumber, newState).then(response => {
        this.showSuccessMessage()
        this.getOrder()
      }).catch(response => {
        this.showErrorMessage(response)
      })
    },
    refreshPage() {
      this.getOrder()
    },
    getOrder() {
      purchase.item.search(this.$route.params.PurchaseOrderNumber).then(response => {
        this.orderData = response.MetaData
        if (this.orderData.Status === 'Editing') this.orderStatus = 0
        else if (this.orderData.Status === 'Placed') this.orderStatus = 1
        else if (this.orderData.Status === 'Confirmed') this.orderStatus = 2
        else if (this.orderData.Status === 'Closed') this.orderStatus = 4
        this.documents = response.Documents
        this.setTagsViewTitle()
      }).catch(response => {
        this.showErrorMessage(response)
      })
    }
  }
}
</script>
