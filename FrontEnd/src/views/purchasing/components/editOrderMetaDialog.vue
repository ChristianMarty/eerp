<template>
  <div class="edit-order-meta-dialog">
    <el-dialog
      title="Edit Order"
      :visible.sync="isVisible"
      :close-on-click-modal="false"
      :before-close="closeDialog"
    >
      <p
        v-if="loading === true"
        v-loading="loading"
        element-loading-text="Loading Line"
      > Loading Line ... </p>

      <el-form
        v-else
        size="mini"
        label-width="220px"
      >
        <el-form-item label="Title:">
          <el-input v-model="dialogData.Title" style="width: 350px" placeholder="Title" />
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
              label: 'DisplayName',
              children: 'Children',
              checkStrictly: true
            }"
          />
        </el-form-item>

        <el-form-item label="Supplier Address:">
          <el-select v-model="dialogData.VendorAddressId" placeholder="Select Address" filterable>
            <el-option
              v-for="item in supplierAddress"
              :key="item.AddressId"
              :label="item.Street+', '+item.PostalCode+' '+item.City+', '+item.CountryName"
              :value="item.AddressId"
            />
          </el-select>
        </el-form-item>

        <el-form-item label="Supplier Contact:">
          <el-select v-model="dialogData.VendorContactId" placeholder="Select Contact" filterable>
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

        <el-form-item label="Payment Terms:">
          <el-input
            v-model="dialogData.PaymentTerms"
            style="width: 350px"
            placeholder="Payment Terms"
          />
        </el-form-item>

        <el-form-item label="Incoterms:">
          <el-input
            v-model="dialogData.InternationalCommercialTerms"
            style="width: 350px"
            placeholder="International Commercial Terms"
          />
        </el-form-item>

        <el-form-item label="Carrier:">
          <el-input
            v-model="dialogData.Carrier"
            style="width: 350px"
            placeholder="Carrier"
          />
        </el-form-item>

        <el-form-item label="Head Note:">
          <el-input v-model="dialogData.HeadNote" type="textarea" placeholder="Head Note" />
        </el-form-item>

        <el-form-item label="Foot Note:">
          <el-input v-model="dialogData.FootNote" type="textarea" placeholder="Foot Note" />
        </el-form-item>
      </el-form>

      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="saveData()">Save</el-button>
        <el-button @click="closeDialog()">Cancel</el-button>
      </span>
    </el-dialog>
  </div>
</template>

<script>
import Finance from '@/api/finance'
const finance = new Finance()
import Vendor from '@/api/vendor'
const vendor = new Vendor()
import Purchase from '@/api/purchase'
const purchase = new Purchase()

export default {
  name: 'EditOrderMetaDialog',
  props: {
    visible: { type: Boolean, required: true, default: false },
    purchaseOrderNumber: { type: Number, required: true }
  },
  data() {
    return {
      isVisible: false,
      loading: true,

      suppliers: [],
      currencies: [],

      supplierAddress: [],
      supplierContact: [],
      dialogData: {}
    }
  },
  watch: {
    '$props.visible': {
      handler(newVal) {
        if (newVal === true) {
          this.isVisible = true
          this.loadData()
        }
      }
    }
  },
  async mounted() {
    this.suppliers = await vendor.search(true, false, false, false, false, true)
    this.currencies = await finance.currency.list()
  },
  methods: {
    loadData() {
      this.loading = true
      purchase.item.meta.get(this.$props.purchaseOrderNumber).then(response => {
        this.dialogData = response
        this.getSupplierAddress(this.dialogData.SupplierId)
        this.getSupplierContact(this.dialogData.SupplierId)
        this.loading = false
      }).catch(response => {
        this.closeDialog()
      })
    },
    saveData() {
      purchase.item.meta.save(this.$props.purchaseOrderNumber, this.dialogData).then(response => {
        this.closeDialog()
      })
    },
    getExchangeRate() {
      finance.currency.exchangeRate(this.dialogData.CurrencyId).then(response => {
        this.exchangeRateData = response
        this.dialogData.ExchangeRate = this.exchangeRateData.ExchangeRate
      })
    },
    getSupplierAddress(SupplierId) {
      vendor.address.search(SupplierId).then(response => {
        this.supplierAddress = response
      })
    },
    getSupplierContact(SupplierId) {
      vendor.contact.search(SupplierId).then(response => {
        this.supplierContact = response
      })
    },
    closeDialog() {
      this.isVisible = false
      this.$emit('refresh')
      this.$emit('update:visible', false)
    }
  }
}
</script>
