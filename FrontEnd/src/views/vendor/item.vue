<template>
  <div class="app-container">
    <h1>
      {{ vendorData.Name }}
    </h1>
    <el-divider />
    <p><b>Short Name:</b> {{ vendorData.ShortName }}</p>
    <p><b>Customer Number:</b> {{ vendorData.CustomerNumber }}</p>
    <p><b>Is Supplier:</b> {{ vendorData.IsSupplier }}</p>
    <p><b>Is Manufacturer:</b> {{ vendorData.IsManufacturer }}</p>
    <p><b>Is Contractor:</b> {{ vendorData.IsContractor }}</p>

    <template v-if="checkPermission(['vendor.edit'])">
      <el-button
        size="mini"
        type="primary"
        icon="el-icon-edit"
        circle
        style="margin-top: 00px; margin-bottom: 00px"
        @click="showEditDialog()"
      />
    </template>

    <el-divider />
    <h2>Aliases</h2>
    <template v-if="checkPermission(['vendor.edit'])">
      <el-button
        size="mini"
        type="primary"
        icon="el-icon-plus"
        circle
        style="margin-top: 00px; margin-bottom: 00px"
        @click="showAliasDialog(null)"
      />
    </template>
    <el-table
      :data="vendorData.Alias"
      style="width: 100%; margin-top:10px"
    >
      <el-table-column prop="Name" label="Name" sortable width="150" />
      <el-table-column prop="Note" label="Note" sortable />
      <template v-if="checkPermission(['vendor.edit'])">
        <el-table-column width="50">
          <template slot-scope="{ row }">
            <el-button
              size="mini"
              type="primary"
              icon="el-icon-edit"
              circle
              style="margin-top: 5px; margin-bottom: 5px"
              @click="showAliasDialog(row.Id)"
            />
          </template>
        </el-table-column>
      </template>
    </el-table>

    <el-divider />
    <h2>Children</h2>
    <!--<template v-if="checkPermission(['vendor.edit'])">
      <el-button
        size="mini"
        type="primary"
        icon="el-icon-plus"
        circle
        style="margin-top: 00px; margin-bottom: 00px"
        @click="showChildDialog()"
      />
    </template>-->
    <el-table
      :data="vendorData.Children"
      style="width: 100%; margin-top:10px"
    >
      <el-table-column prop="Name" label="Name" sortable width="300">
        <template slot-scope="{ row }">
          <router-link :to="'/vendor/view/' + row.Id" class="link-type">
            <span>{{ row.Name }}</span>
          </router-link>
        </template>
      </el-table-column>
      <el-table-column prop="Customer Number" label="Customer Number" sortable />
    </el-table>

    <el-divider />
    <h2>Addresses</h2>
    <template v-if="checkPermission(['vendor.edit'])">
      <el-button
        size="mini"
        type="primary"
        icon="el-icon-plus"
        circle
        style="margin-top: 00px; margin-bottom: 00px"
        @click="showAddressDialog(null)"
      />
    </template>
    <el-table
      :data="vendorData.Address"
      style="width: 100%; margin-top:10px"
    >
      <el-table-column prop="Street" label="Street" sortable />
      <el-table-column prop="PostalCode" label="PostalCode" sortable />
      <el-table-column prop="City" label="City" sortable />
      <el-table-column prop="CountryName" label="Country" sortable />
      <el-table-column prop="CountryCode" label="Country Code" sortable />
      <!--<el-table-column prop="PhonePrefix" label="Phone Prefix" sortable />-->
      <el-table-column prop="VatTaxNumber" label="Vat Tax Number" sortable />
      <el-table-column prop="CustomsAccountNumber" label="Customs Account Number" sortable />
      <template v-if="checkPermission(['vendor.edit'])">
        <el-table-column width="50">
          <template slot-scope="{ row }">
            <el-button
              size="mini"
              type="primary"
              icon="el-icon-edit"
              circle
              style="margin-top: 5px; margin-bottom: 5px"
              @click="showAddressDialog(row.Id)"
            />
          </template>
        </el-table-column>
      </template>
    </el-table>

    <el-divider />
    <h2>Contacts</h2>
    <template v-if="checkPermission(['vendor.edit'])">
      <el-button
        size="mini"
        type="primary"
        icon="el-icon-plus"
        circle
        style="margin-top: 00px; margin-bottom: 00px"
        @click="showContactDialog(null)"
      />
    </template>
    <el-table
      :data="vendorData.Contact"
      style="width: 100%; margin-top:10px"
    >
      <el-table-column prop="Gender" label="Gender" sortable />
      <el-table-column prop="FirstName" label="First Name" sortable />
      <el-table-column prop="LastName" label="Last Name" sortable />
      <el-table-column prop="JobTitle" label="Job Title" sortable />
      <el-table-column prop="Language" label="Language" sortable />
      <el-table-column prop="Phone" label="Phone" sortable />
      <el-table-column prop="EMail" label="E-Mail" sortable />
      <template v-if="checkPermission(['vendor.edit'])">
        <el-table-column width="50">
          <template slot-scope="{ row }">
            <el-button
              size="mini"
              type="primary"
              icon="el-icon-edit"
              circle
              style="margin-top: 5px; margin-bottom: 5px"
              @click="showContactDialog(row.Id)"
            />
          </template>
        </el-table-column>
      </template>
    </el-table>

    <el-divider />
    <h2>Purchas Orders</h2>
    <p><b>Number of orders:</b> {{ purchasOrders.length }}</p>
    <el-table
      :data="purchasOrders"
      style="width: 100%; margin-top:10px"
    >
      <el-table-column prop="PoNo" label="Po Number" sortable width="150">
        <template slot-scope="{ row }">
          <router-link :to="'/purchasing/edit/' + row.PoNo" class="link-type">
            <span>PO-{{ row.PoNo }}</span>
          </router-link>
        </template>
      </el-table-column>

      <el-table-column prop="Title" label="Title" sortable />
      <el-table-column prop="Description" label="Description" sortable />
      <el-table-column prop="PurchaseDate" label="Purchase Date" sortable width="250" />
      <el-table-column prop="OrderNumber" label="Order Number" sortable width="250" />
      <el-table-column prop="Status" label="Status" sortable width="150" />

    </el-table>

    <el-divider />
    <h2>Manufacturerart Part Numbers</h2>
    <p><b>Number of Manufacturerart Part Numbers:</b> {{ manufacturerartPartData.length }}</p>
    <el-table
      :data="manufacturerartPartData"
      style="width: 100%; margin-top:10px"
    >
      <el-table-column label="Part Number" sortable width="250">
        <template slot-scope="{ row }">
          <router-link :to="'/manufacturerPart/partNumber/item/' + row.PartNumberId" class="link-type">
            <span>{{ row.PartNumber }}</span>
          </router-link>
        </template>
      </el-table-column>
      <el-table-column
        prop="ManufacturerPartNumberTemplate"
        label="Part"
        sortable
        width="220"
      >
        <template slot-scope="{ row }">
          <router-link
            :to="'/manufacturerPart/item/' + row.PartId"
            class="link-type"
          >
            <span>{{ row.ManufacturerPartNumberTemplate }}</span>
          </router-link>
        </template>
      </el-table-column>

      <el-table-column
        prop="SeriesTitle"
        label="Series"
        sortable
        width="220"
      >
        <template slot-scope="{ row }">
          <router-link
            :to="'/manufacturerPart/series/item/' + row.SeriesId"
            class="link-type"
          >
            <span>{{ row.SeriesTitle }}</span>
          </router-link>
        </template>
      </el-table-column>
      <el-table-column prop="Description" label="Description" sortable />
    </el-table>

    <el-divider />
    <h2>Supplier Parts</h2>
    <p><b>Number of supplier parts:</b> {{ supplierPartData.length }}</p>
    <el-table
      :data="supplierPartData"
      style="width: 100%; margin-top:10px"
    >
      <el-table-column label="Part Number" sortable>
        <template slot-scope="{ row }">
          <a :href="row.SupplierPartLink" target="blank">
            {{ row.SupplierPartNumber }}
          </a>
        </template>
      </el-table-column>
    </el-table>

    <editDialog
      :vendor-id="Number($route.params.vendorNo)"
      :visible.sync="editDialogVisible"
      @change="update()"
    />

    <aliasDialog
      :alias-id="Number(editAliasId)"
      :vendor-id="Number(vendorData.Id)"
      :visible.sync="editAliasVisible"
      @change="update()"
    />

    <addressDialog
      :address-id="Number(editAddressId)"
      :vendor-id="Number(vendorData.Id)"
      :visible.sync="editAddressVisible"
      @change="update()"
    />

    <contactDialog
      :contact-id="Number(editContactId)"
      :vendor-id="Number(vendorData.Id)"
      :visible.sync="editContactVisible"
      @change="update()"
    />

  </div>
</template>

<script>
import checkPermission from '@/utils/permission'

import editDialog from './components/editDialog'
import aliasDialog from './components/editAliasDialog'
import addressDialog from './components/editAddressDialog'
import contactDialog from './components/editContactDialog'

import Vendor from '@/api/vendor'
const vendor = new Vendor()

import Purchase from '@/api/purchase'
const purchase = new Purchase()

import Part from '@/api/part'
const part = new Part()

import ManufacturerPart from '@/api/manufacturerPart'
const manufacturerPart = new ManufacturerPart()

export default {
  name: 'PartDetail',
  components: { editDialog, aliasDialog, addressDialog, contactDialog },
  props: {
    isEdit: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      vendorData: {},
      editDialogVisible: false,

      supplierPartData: null,
      purchasOrders: [],
      manufacturerartPartData: null,

      editAliasVisible: false,
      editAliasId: null,

      editAddressVisible: false,
      editAddressId: null,

      editContactVisible: false,
      editContactId: null

    }
  },
  mounted() {
    this.update()
  },
  created() {
    // Why need to make a copy of this.$route here?
    // Because if you enter this page and quickly switch tag, may be in the execution of the setTagsViewTitle function, this.$route is no longer pointing to the current page
    // https://github.com/PanJiaChen/vue-element-admin/issues/1221
    this.tempRoute = Object.assign({}, this.$route)
  },
  methods: {
    checkPermission,
    update() {
      const vendorId = this.$route.params.vendorNo

      vendor.item(vendorId).then(response => {
        this.vendorData = response
        this.setTitle()
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })

      purchase.get(vendorId).then(response => {
        this.purchasOrders = response
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })

      const searchParameters = Object.assign({}, manufacturerPart.PartNumber.searchParameters)
      searchParameters.VendorId = vendorId
      manufacturerPart.PartNumber.search(searchParameters).then(response => {
        this.manufacturerartPartData = response
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })

      part.searchSupplierPart(vendorId).then(response => {
        this.supplierPartData = response
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    showEditDialog() {
      this.editDialogVisible = true
    },
    showAliasDialog(AliasId) {
      this.editAliasId = AliasId
      this.editAliasVisible = true
    },
    showAddressDialog(AddressId) {
      this.editAddressId = AddressId
      this.editAddressVisible = true
    },
    showContactDialog(ContactId) {
      this.editContactId = ContactId
      this.editContactVisible = true
    },
    setTitle() {
      const title = 'Part View'
      document.title = `${title} - ${this.vendorData.Name}`

      const route = Object.assign({}, this.tempRoute, {
        title: `${this.vendorData.Name}`
      })
      this.$store.dispatch('tagsView/updateVisitedView', route)
    }
  }
}
</script>

