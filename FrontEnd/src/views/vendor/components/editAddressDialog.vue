<template>
  <div class="address-edit-dialog">
    <el-dialog
      title="Address Edit"
      :visible.sync="visible"
      :before-close="closeDialog"
      center
      width="75%"
      @open="onOpen()"
    >
      <el-form ref="form" label-width="200px">
        <el-form-item label="Street">
          <el-input v-model="addressData.Street" />
        </el-form-item>
        <el-form-item label="City">
          <el-input v-model="addressData.City" />
        </el-form-item>
        <el-form-item label="Postal Code">
          <el-input v-model="addressData.PostalCode" />
        </el-form-item>
        <el-form-item label="Country">
          <el-select v-model="addressData.CountryNumericCode" placeholder="Country" filterable>
            <el-option
              v-for="item in countries"
              :key="item.NumericCode"
              :label="item.Alpha2Code + ' - '+item.ShortName"
              :value="item.NumericCode"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="Vat Tax Number">
          <el-input v-model="addressData.VatTaxNumber" />
        </el-form-item>
        <el-form-item label="Customs Account Number">
          <el-input v-model="addressData.CustomsAccountNumber" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="save()">Save</el-button>
          <el-button @click="closeDialog()">Cancel</el-button>
        </el-form-item>
      </el-form>

    </el-dialog>
  </div>
</template>

<script>

import Country from '@/api/country'
const country = new Country()

import Vendor from '@/api/vendor'
const vendor = new Vendor()

export default {
  name: 'VendorAddressEdit',
  props: {
    addressId: { type: Number, default: 0 },
    vendorId: { type: Number, default: 0 },
    visible: { type: Boolean, default: false }
  },
  data() {
    return {
      addressData: Object.assign({}, vendor.address.itemReturn),
      countries: []
    }
  },
  mounted() {
  },
  methods: {
    async onOpen() {
      this.countries = await country.search()

      if (this.$props.addressId === 0) {
        this.addressData = Object.assign({}, vendor.address.searchReturn)
      } else {
        vendor.address.item(this.$props.addressId).then(response => {
          this.addressData = response
        }).catch(response => {
          this.$message({
            showClose: true,
            message: response,
            duration: 0,
            type: 'error'
          })
        })
      }
    },
    save() {
      let addressId = this.$props.addressId
      if (addressId === 0) addressId = null

      const saveParameters = {
        AddressId: addressId,
        VendorId: this.$props.vendorId,
        CountryNumericCode: this.addressData.CountryNumericCode,
        PostalCode: this.addressData.PostalCode,
        City: this.addressData.City,
        Street: this.addressData.Street,
        VatTaxNumber: this.addressData.VatTaxNumber,
        CustomsAccountNumber: this.addressData.CustomsAccountNumber
      }

      vendor.address.save(saveParameters).then(response => {
        this.closeDialog()
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    closeDialog() {
      this.visible = false
      this.$emit('update:visible', this.visible)
      this.$emit('change')
    }
  }
}
</script>
