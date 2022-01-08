<template>
  <div class="app-container">
    <h1>{{ orderData.Title }}, PO-{{ orderData.PoNo }}</h1>
    <el-steps :active="orderStatus" finish-status="success" align-center>
      <el-step title="Editing" />
      <el-step title="Placed" />
      <el-step title="Confirmed" />
      <el-step title="Closed" />
    </el-steps>

    <template v-if="orderData.Status == 'Editing'">
      <el-button type="info" @click="place">Place Order</el-button>
    </template>

    <template v-if="orderData.Status == 'Placed'">
      <el-button type="info" @click="edit">Edit Order</el-button>
      <el-button type="info" @click="confirm">Confirm Order</el-button>
    </template>

    <template v-if="orderData.Status == 'Confirmed'">
      <el-button type="info" @click="close">Close Order</el-button>
    </template>

    <el-divider />
    <p>
      <b>
        {{ orderData.SupplierName }} -
        {{ orderData.PurchaseDate }}
      </b>
    </p>

    <el-input
      v-if="orderData.Status == 'Placed'"
      v-model="orderData.AcknowledgementNumber"
      style="width: 350px"
      placeholder="Supplier Order Number"
    />

    <p><b>Order Number:</b> {{ orderData.OrderNumber }}</p>
    <p><b>Acknowledgement Number:</b> {{ orderData.AcknowledgementNumber }}</p>
    <p><b>Currency:</b> {{ orderData.Currency }}</p>
    <p><b>ExchangeRate:</b> {{ orderData.ExchangeRate }}</p>
    <p><b>Description:</b> {{ orderData.Description }}</p>

    <el-button
      v-if="orderData.Status == 'Editing'"
      style="margin-top: 20px"
      type="primary"
      @click="editMeta"
    >Edit</el-button>

    <el-divider />

    <editOrder v-if="orderData.Status == 'Editing'" :order-data="orderData" />

    <placedOrder v-if="orderData.Status == 'Placed'" :order-data="orderData" />

    <confirmedOrder
      v-if="orderData.Status == 'Confirmed'"
      :order-data="orderData"
    />

    <closedOrder v-if="orderData.Status == 'Closed'" :order-data="orderData" />

    <el-dialog title="Edit Order" :visible.sync="showDialog" width="50%" center>
      <el-form size="mini" label-width="220px">
        <el-form-item label="Titel:">
          <el-input
            v-model="dialogData.Titel"
            style="width: 350px"
            placeholder="Titel"
          />
        </el-form-item>

        <el-form-item label="Supplier:">
          <el-select
            v-model="dialogData.SupplierName"
            filterable
            placeholder="Supplier"
          >
            <el-option
              v-for="item in suppliers"
              :key="item.Name"
              :label="item.Name"
              :value="item.Name"
            />
          </el-select>
        </el-form-item>

        <el-form-item label="Acknowledgement Number:">
          <el-input
            v-model="dialogData.AcknowledgementNumber"
            style="width: 350px"
            placeholder="Supplier Order Number"
          />
        </el-form-item>

        <el-form-item label="Order Date:">
          <el-date-picker
            v-model="dialogData.PurchaseDate"
            type="date"
            placeholder="Pick a day"
          />
        </el-form-item>
        <el-form-item label="Description:">
          <el-input
            v-model="dialogData.Description"
            type="textarea"
            placeholder="Description"
          />
        </el-form-item>
      </el-form>

      <span slot="footer" class="dialog-footer">
        <el-button @click="showDialog = false">Cancel</el-button>
        <el-button type="primary" @click="saveEditMeta()">Save</el-button>
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

export default {
  name: 'PurchaseOrder',
  components: { editOrder, placedOrder, confirmedOrder, closedOrder },
  data() {
    return {
      PoNo: this.$route.params.PoNo,
      orderData: null,

      orderStatus: 0,
      suppliers: null,

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
  },
  methods: {
    edit() {
      this.orderData.Status = 'Editing'
      this.saveState(this.orderData)
    },
    place() {
      this.orderData.Status = 'Placed'
      this.saveState(this.orderData)
    },
    confirm() {
      this.orderData.Status = 'Confirmed'
      this.saveState(this.orderData)
    },
    close() {
      this.orderData.Status = 'Closed'
      this.saveState(this.orderData)
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
    saveData(data) {
      let PurchaseDate = new Date(data.PurchaseDate)
      PurchaseDate = PurchaseDate.toISOString().split('T')[0]
      data.PurchaseDate = PurchaseDate
      requestBN({
        method: 'PATCH',
        url: '/purchasOrder',
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
    getSuppliers() {
      requestBN({
        url: '/supplier',
        methood: 'get'
      }).then(response => {
        this.suppliers = response.data
      })
    },
    getOrder() {
      requestBN({
        url: '/purchasOrder',
        methood: 'get',
        params: {
          PurchaseOrderNo: this.PoNo
        }
      }).then(response => {
        this.orderData = response.data[0]
        if (this.orderData.Status === 'Editing') this.orderStatus = 0
        else if (this.orderData.Status === 'Placed') this.orderStatus = 1
        else if (this.orderData.Status === 'Confirmed') this.orderStatus = 2
        else if (this.orderData.Status === 'Closed') this.orderStatus = 4
      })
    },
    setTagsViewTitle() {
      const route = Object.assign({}, this.tempRoute, {
        title: 'PO-' + `${this.$route.params.PoNo}`
      })
      this.$store.dispatch('tagsView/updateVisitedView', route)
    },
    getOrderLine() {
      requestBN({
        url: '/purchasing/item',
        methood: 'get',
        params: {
          PurchaseOrderNo: this.orderData.PoNo
        }
      }).then(response => {
        this.lines = response.data.Lines
        this.line = this.lines.length
      })
    },
  }
}
</script>
