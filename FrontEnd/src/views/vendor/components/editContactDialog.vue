<template>
  <div class="contact-edit-dialog">
    <el-dialog
      title="Contact Edit"
      :visible.sync="visible"
      :before-close="closeDialog"
      center
      width="75%"
      @open="onOpen()"
    >
      <el-form ref="form" label-width="200px">
        <el-form-item label="Gender">
          <el-select v-model="contactData.Gender" placeholder="Gender" filterable>
            <el-option
              v-for="item in genders"
              :key="item"
              :label="item"
              :value="item"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="First Name">
          <el-input v-model="contactData.FirstName" />
        </el-form-item>
        <el-form-item label="Last Name">
          <el-input v-model="contactData.LastName" />
        </el-form-item>
        <el-form-item label="Language">
          <el-select v-model="contactData.Language" placeholder="Gender" filterable>
            <el-option
              v-for="item in languages"
              :key="item"
              :label="item"
              :value="item"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="Phone">
          <el-input v-model="contactData.Phone" />
        </el-form-item>
        <el-form-item label="E-Mail">
          <el-input v-model="contactData.EMail" />
        </el-form-item>
        <el-form-item label="Address">
          <el-select v-model="contactData.AddressId" placeholder="Address" filterable>
            <el-option
              v-for="item in addresses"
              :key="item.Id"
              :label="item.Street+', '+item.PostalCode+' '+item.City+', '+item.CountryName"
              :value="item.Id"
            />
          </el-select>
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

import Vendor from '@/api/vendor'
const vendor = new Vendor()

export default {
  name: 'VendorContactEdit',
  props: {
    contactId: { type: Number, default: 0 },
    vendorId: { type: Number, default: 0 },
    visible: { type: Boolean, default: false }
  },
  data() {
    return {
      contactData: Object.assign({}, vendor.contact.itemReturn),
      languages: [],
      genders: [],
      addresses: []
    }
  },
  mounted() {
  },
  methods: {
    async onOpen() {
      this.languages = await vendor.contact.language()
      this.genders = await vendor.contact.gender()
      this.addresses = await vendor.address.search(this.$props.vendorId)

      if (this.$props.contactId === 0) {
        this.contactData = Object.assign({}, vendor.contact.itemReturn)
      } else {
        vendor.contact.item(this.$props.contactId).then(response => {
          this.contactData = response
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
      let contactId = this.$props.contactId
      if (contactId === 0) contactId = null

      const saveParameters = {
        ContactId: contactId,
        VendorId: this.$props.vendorId,
        AddressId: this.contactData.AddressId,
        Gender: this.contactData.Gender,
        FirstName: this.contactData.FirstName,
        LastName: this.contactData.LastName,
        Language: this.contactData.Language,
        Phone: this.contactData.Phone,
        EMail: this.contactData.EMail
      }

      vendor.contact.save(saveParameters).then(response => {
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
